<?php

//  Display the course home page.

    require_once('../config.php');
    require_once('lib.php');
    require_once($CFG->dirroot.'/mod/forum/lib.php');
    require_once($CFG->libdir.'/completionlib.php');

    $id          = optional_param('id', 0, PARAM_INT);
	//$courseids   = $_GET['courseids'];
    $name        = optional_param('name', '', PARAM_RAW);
    $edit        = optional_param('edit', -1, PARAM_BOOL);
    $hide        = optional_param('hide', 0, PARAM_INT);
    $show        = optional_param('show', 0, PARAM_INT);
    $idnumber    = optional_param('idnumber', '', PARAM_RAW);
    $section     = optional_param('section', 0, PARAM_INT);
    $move        = optional_param('move', 0, PARAM_INT);
    $marker      = optional_param('marker',-1 , PARAM_INT);
    $switchrole  = optional_param('switchrole',-1, PARAM_INT);
   
if(is_institute_cordinator()){
		$params = array();
		$sql = 'select * from {role_assignments} ra 
		left join {context} ct on ra.contextid = ct.id 
		left join {course_categories} course_cat on ct.instanceid = course_cat.id 
		where roleid = 13 and userid = '.$USER->id.' and contextid > 1';
		$catids = '';
		$check_role_assignments = $DB->get_records_sql($sql);
		
		if($check_role_assignments){			
			foreach($check_role_assignments as $check_role_assignment){	
				//print_r($check_role_assignment->id);			
				//$context = context::instance_by_id($check_role_assignment->instanceid);
				$catids .= $check_role_assignment->instanceid.',';	
				if(isset($_GET['catid']) && $_GET['catid']!=''){
					$courses[$_GET['catid']] = get_courses($_GET['catid'], $sort="c.sortorder ASC", $fields="c.*");	
				}else{
					$courses[$check_role_assignment->id] = get_courses($check_role_assignment->id, $sort="c.sortorder ASC", $fields="c.*");
				}
			}
			
			$catidstring = substr($catids,0,-1);
			//echo $catidstring;
			$catsql = "SELECT * FROM {course_categories} where id in ($catidstring)";
			$course_categories = $DB->get_records_sql($catsql);
			
			//print_r($courses);
			if(isset($_GET['id']) && $_GET['id'] != ''){
				/*$get_teacher_sql = "SELECT user.id,user.username,context.instanceid 
				FROM {user} user left join {role_assignments} role on role.userid = user.id left join {context} context on role.contextid = context.id 
				where role.roleid in (3,4) 
				and user.site_fk in (SELECT id FROM {sites} where site_centerid in (".$_GET['catid'].")) 
				and context.instanceid = ".$_GET['id'];*/
				
				$get_teacher_sql = "SELECT user.id,user.username,context.instanceid FROM {role_assignments} ra 
				left join {context} context on ra.contextid = context.id 
				left join {user} user on ra.userid = user.id 
				where context.instanceid = ".$_GET['id']." and ra.roleid in (3,4)";
				$get_teachers = $DB->get_records_sql($get_teacher_sql);
			}
			if(isset($_GET['teacherid']) && $_GET['teacherid'] != ''){
				$teacherid = $_GET['teacherid'];
				$courseid = $_GET['id'];
			}elseif($get_teachers){
				foreach($get_teachers as $get_teacher){
					$teacherid = $get_teacher->id;
					$courseid = $get_teacher->instanceid;
					break;
				}
			}
			if($teacherid){
				$get_classes_sql = "SELECT groupid FROM {groups_members} 
				left join {groups} groups on groupid = groups.id  where userid = $teacherid and groups.courseid = $courseid";
				$get_classes = $DB->get_records_sql($get_classes_sql);
				//print_r($get_classes);
				if(isset($_GET['classid']) && $_GET['classid'] != ''){
					$classid = $_GET['classid'];
				}elseif($get_classes){
					foreach($get_classes as $get_class){
						$classid = $get_class->groupid;
						break;
					}
				}
			}
			//echo $courseid.'<br>'.$teacherid.'<br>'.$classid;
		
		}	
	}else {
		$redirect_string ='';
		$redirect_string .= $CFG->wwwroot;
		$redirect_string .= '/index.php';
		redirect($redirect_string);
	}
    //echo '<pre>';print_r($course_categories);echo '</pre>';
    $firstcourseid='';
    if($id!=0)
    {
    	$firstcourseid = $id;
    }
	if(count($courses) > 0)
	{	
		foreach ($courses as $c) 
		{
			if(trim($firstcourseid) =='')
			{			
				$firstcourseid = $c->id;
			}
		}
	}
    if (empty($id) && empty($name) && empty($idnumber)) {
		redirect($CFG->wwwroot.'/course/category.php?id='.$_GET['catid'].'&categoryedit=on&sesskey='.$USER->sesskey);
        print_error('unspecifycourseid', 'error');
		
    }

    if (!empty($name)) {
        if (! ($course = $DB->get_record('course', array('shortname'=>$name)))) {
            print_error('invalidcoursenameshort', 'error');
        }
    } else if (!empty($idnumber)) {
        if (! ($course = $DB->get_record('course', array('idnumber'=>$idnumber)))) {
            print_error('invalidcourseid', 'error');
        }
    } else {
        if (! ($course = $DB->get_record('course', array('id'=>$id)))) {
            print_error('invalidcourseid', 'error');
        }
    }

    $PAGE->set_url('/course/faculty_dashboard.php', array('catid'=>$course->category,'id' => $course->id,'teacherid' => $teacherid,'classid'=>$classid)); // Defined here to avoid notices on errors etc

    preload_course_contexts($course->id);
    if (!$context = get_context_instance(CONTEXT_COURSE, $course->id)) {
        print_error('nocontext');
    }

    // Remove any switched roles before checking login
    if ($switchrole == 0 && confirm_sesskey()) {
        role_switch($switchrole, $context);
    }

    require_login($course);

    // Switchrole - sanity check in cost-order...
    $reset_user_allowed_editing = false;
    if ($switchrole > 0 && confirm_sesskey() &&
        has_capability('moodle/role:switchroles', $context)) {
        // is this role assignable in this context?
        // inquiring minds want to know...
        $aroles = get_switchable_roles($context);
        if (is_array($aroles) && isset($aroles[$switchrole])) {
            role_switch($switchrole, $context);
            // Double check that this role is allowed here
            require_login($course->id);
        }
        // reset course page state - this prevents some weird problems ;-)
        $USER->activitycopy = false;
        $USER->activitycopycourse = NULL;
        unset($USER->activitycopyname);
        unset($SESSION->modform);
        $USER->editing = 0;
        $reset_user_allowed_editing = true;
    }

    //If course is hosted on an external server, redirect to corresponding
    //url with appropriate authentication attached as parameter
    if (file_exists($CFG->dirroot .'/course/externservercourse.php')) {
        include $CFG->dirroot .'/course/externservercourse.php';
        if (function_exists('extern_server_course')) {
            if ($extern_url = extern_server_course($course)) {
                redirect($extern_url);
            }
        }
    }


    require_once($CFG->dirroot.'/calendar/lib.php');    /// This is after login because it needs $USER

    add_to_log($course->id, 'course', 'view', "faculty_dashboard.php?catid=$course->category&id=$course->id", "$course->id");

    $course->format = clean_param($course->format, PARAM_ALPHA);
    if (!file_exists($CFG->dirroot.'/course/format/'.$course->format.'/format.php')) {
        $course->format = 'weeks';  // Default format is weeks
    }

    $PAGE->set_pagelayout('course');
    $PAGE->set_pagetype('course-view-' . $course->format);
    $PAGE->set_other_editing_capability('moodle/course:manageactivities');

    if ($reset_user_allowed_editing) {
        // ugly hack
        unset($PAGE->_user_allowed_editing);
    }

    if (!isset($USER->editing)) {
        $USER->editing = 0;
    }
    if ($PAGE->user_allowed_editing()) {
        if (($edit == 1) and confirm_sesskey()) {
            $USER->editing = 1;
            // Redirect to site root if Editing is toggled on frontpage
            if ($course->id == SITEID) {
                redirect($CFG->wwwroot .'/?redirect=0');
            } else {
                redirect($PAGE->url);
            }
        } else if (($edit == 0) and confirm_sesskey()) {
            $USER->editing = 0;
            if(!empty($USER->activitycopy) && $USER->activitycopycourse == $course->id) {
                $USER->activitycopy       = false;
                $USER->activitycopycourse = NULL;
            }
            // Redirect to site root if Editing is toggled on frontpage
            if ($course->id == SITEID) {
                redirect($CFG->wwwroot .'/?redirect=0');
            } else {
                redirect($PAGE->url);
            }
        }

        if (has_capability('moodle/course:update', $context)) {
            if ($hide && confirm_sesskey()) {
                set_section_visible($course->id, $hide, '0');
            }

            if ($show && confirm_sesskey()) {
                set_section_visible($course->id, $show, '1');
            }

            if (!empty($section)) {
                if (!empty($move) and confirm_sesskey()) {
                    if (move_section($course, $section, $move)) {
                        if ($course->id == SITEID) {
                            redirect($CFG->wwwroot . '/?redirect=0');
                        } else {
                            redirect($PAGE->url);
                        }
                    } else {
                        echo $OUTPUT->notification('An error occurred while moving a section');
                    }
                }
            }
        }
    } else {
        $USER->editing = 0;
    }

    $SESSION->fromdiscussion = $CFG->wwwroot .'/course/faculty_dashboard.php?catid='.$course->category.'&id='. $course->id;


    if ($course->id == SITEID) {
        // This course is not a real course.
        redirect($CFG->wwwroot .'/');
    }

    // AJAX-capable course format?
    $useajax = true;
    $formatajax = course_format_ajax_support($course->format);

    if (!empty($CFG->enablecourseajax)
            and $formatajax->capable
            and !empty($USER->editing)
            and ajaxenabled($formatajax->testedbrowsers)
            and $PAGE->theme->enablecourseajax
            and has_capability('moodle/course:manageactivities', $context)) {
        $PAGE->requires->yui2_lib('dragdrop');
        $PAGE->requires->yui2_lib('connection');
        $PAGE->requires->yui2_lib('selector');
        $PAGE->requires->js('/lib/ajax/block_classes.js', true);
        $PAGE->requires->js('/lib/ajax/section_classes.js', true);

        // Okay, global variable alert. VERY UGLY. We need to create
        // this object here before the <blockname>_print_block()
        // function is called, since that function needs to set some
        // stuff in the javascriptportal object.
        $COURSE->javascriptportal = new jsportal();
        $useajax = true;
    }

    $CFG->blocksdrag = $useajax;   // this will add a new class to the header so we can style differently

    $completion = new completion_info($course);
    if ($completion->is_enabled() && ajaxenabled()) {
        $PAGE->requires->string_for_js('completion-title-manual-y', 'completion');
        $PAGE->requires->string_for_js('completion-title-manual-n', 'completion');
        $PAGE->requires->string_for_js('completion-alt-manual-y', 'completion');
        $PAGE->requires->string_for_js('completion-alt-manual-n', 'completion');

        $PAGE->requires->js_init_call('M.core_completion.init');
    }

    // We are currently keeping the button here from 1.x to help new teachers figure out
    // what to do, even though the link also appears in the course admin block.  It also
    // means you can back out of a situation where you removed the admin block. :)
    if ($PAGE->user_allowed_editing()) {
        $buttons = $OUTPUT->edit_button(new moodle_url('/course/faculty_dashboard.php', array('id' => $course->id)));
        $PAGE->set_button($buttons);
    }

    $PAGE->set_title(get_string('course') . ': ' . $course->fullname);
    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();

    if ($completion->is_enabled() && ajaxenabled()) {
        // This value tracks whether there has been a dynamic change to the page.
        // It is used so that if a user does this - (a) set some tickmarks, (b)
        // go to another page, (c) clicks Back button - the page will
        // automatically reload. Otherwise it would start with the wrong tick
        // values.
        echo html_writer::start_tag('form', array('action'=>'.', 'method'=>'get'));
        echo html_writer::start_tag('div');
        echo html_writer::empty_tag('input', array('type'=>'hidden', 'id'=>'completion_dynamic_change', 'name'=>'completion_dynamic_change', 'value'=>'0'));
        echo html_writer::end_tag('div');
        echo html_writer::end_tag('form');
    }

    // Course wrapper start.
    echo html_writer::start_tag('div', array('class'=>'course-content'));

    $modinfo =& get_fast_modinfo($COURSE);
    get_all_mods($course->id, $mods, $modnames, $modnamesplural, $modnamesused);
    foreach($mods as $modid=>$unused) {
        if (!isset($modinfo->cms[$modid])) {
            rebuild_course_cache($course->id);
            $modinfo =& get_fast_modinfo($COURSE);
            debugging('Rebuilding course cache', DEBUG_DEVELOPER);
            break;
        }
    }

    if (! $sections = get_all_sections($course->id)) {   // No sections found
        // Double-check to be extra sure
        if (! $section = $DB->get_record('course_sections', array('course'=>$course->id, 'section'=>0))) {
            $section->course = $course->id;   // Create a default section.
            $section->section = 0;
            $section->visible = 1;
            $section->summaryformat = FORMAT_HTML;
            $section->id = $DB->insert_record('course_sections', $section);
        }
        if (! $sections = get_all_sections($course->id) ) {      // Try again
            print_error('cannotcreateorfindstructs', 'error');
        }
    }
    
    //print_r($courses);
    ?>

    <script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/yahoo/yahoo-min.js"></script> 
    <script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/event/event-min.js"></script> 
    <script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/connection/connection-min.js"></script> 
  
    <script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/dom/dom.js"></script> 
    <script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/dragdrop/dragdrop.js"></script> 

  
	<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/utilities/utilities.js"></script> 
	<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/selector/selector-min.js"></script> 
	<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/event-delegate/event-delegate-min.js"></script> 
	<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/event-mouseenter/event-mouseenter-min.js"></script> 
	<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/carousel/carousel-min.js"></script> 
	<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/connection/connection_core-min.js"></script> 
	<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/container/container-min.js"></script> 
	<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/element-delegate/element-delegate-min.js"></script> 
	<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/progressbar/progressbar-min.js"></script>
	<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/json/json-min.js"></script>
    <link href="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/carousel/assets/skins/sam/carousel.css" rel="stylesheet" type="text/css" />
    <link type="text/css" rel="stylesheet" href="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/container/assets/container.css">
    <style>
    .yui-carousel-nav
    {
  	  display:none;
    }
	.yui-carousel .yui-carousel-item-selected {
	   	border:0px solid red !important;
		margin: 4px;
		padding: 5px 0 6px 25px;    
	}
	.yui-carousel .yui-carousel-item-selected-category {
	   	border:2px solid #FF7E00 !important;
		margin: 3px 1px;
		padding: 5px 0 4px 23px;
		background: #FF7E00 none;
	}
	.yui-carousel .yui-carousel-item-selected-course {
	   	border:2px solid red !important;
		margin: 3px 1px;
		padding: 5px 0 4px 23px;
		background: #FF0000 url(../theme/mghc2c/pix/tag.png) left 5px no-repeat;;
	}
    </style>
    <input type="hidden" id="sel_course" value=""></input>
    		<div style="width:98%;clear:both;float:left;">
<?php if($CFG->theme == 'mghc2c' && $course_categories){ ?>
			<div id="carousel4">
    			<div class="bef3"><span id="prev1"><img src="../pix/icon/arrow-left.png" alt="" /></span></div>
				<div class="caro">
					<div id='mycustomscrolcategory'>
						<ul class="caromid" id="categorylistcontainer">
							<?php $catcount = 0;$catids = ''; ?>
							<?php foreach($course_categories as $category){ ?>
								<?php 
									if($_GET['catid'] == $category->id){
										$currentcatno = $catcount; 	
										$currentcatid = $category->id;
									} 
									$catcourses = $DB->get_records('course',array('category'=>$category->id));
									$courseid = '';
									foreach($catcourses as $catcourse){
										$courseid = $catcourse->id;
										break;
									}
								?>
								<li class="whitecat" id="category_<?php echo $category->id;?>" style="">
									<a title="" href="<?php echo $CFG->wwwroot; ?>/course/faculty_dashboard.php?catid=<?php echo $category->id; ?>&id=<?php echo $courseid; ?>&edit=on&sesskey=<?php echo $USER->sesskey; ?>">
										<?php 
											if(strlen($category->name)>30){
												echo wordwrap(substr($category->name,0,30),17,"<br>",true).'...';
											}else{
												echo wordwrap($category->name,16,"<br>",true);
											}
										?>
										<?php //echo $category->name; ?>
									</a>
								</li>
								<?php $catids .= $category->id.'-';?>
								<?php $catcount++; ?>
							<?php } ?>
							<?php $catids = substr($catids,0,-1).'"'; ?>
						</ul>
					</div>
				</div>
				<div class="aft3"><span id="next1"><img src="../pix/icon/arrow-right.png" alt="" /></span></div>
    		</div>
<script>				
var carouselsectioncategory    = new YAHOO.widget.Carousel("mycustomscrolcategory", {
		            	numVisible: [4,1] ,
		 	            animation: { speed: 0.5 },
		 	           	navigation:{prev:"prev1",next:"next1"},
		 	    		carouselEl: "UL" , 	
		 				isVertical: false		 				
					}); 
					carouselsectioncategory.on("navigationStateChange",function(){
				 if(carouselsectioncategory._nextEnabled)
					{
						document.getElementById("next1").style.display='block';
						document.getElementById("next1").disabled = false;
					}
					else
					{
						document.getElementById("next1").style.display='none';
						document.getElementById("next1").disabled = true;
					}
					if(carouselsectioncategory._prevEnabled)
					{
						document.getElementById("prev1").style.display='block';
						document.getElementById("prev1").disabled = false;
					}
					else
					{
						document.getElementById("prev1").style.display='none';
						document.getElementById("prev1").disabled = true;
					}
				});
				carouselsectioncategory.render();// get ready for rendering the widget
				carouselsectioncategory.show();
				var catitemno = <?php echo $currentcatno; ?>;
				var catitemid = <?php echo $currentcatid; ?>;
				carouselsectioncategory.set("selectedItem", catitemno);
				document.getElementById('category_'+catitemid).className+=' yui-carousel-item-selected-category';
</script>
<?php } ?>
    		<?php 
            if(count($courses) > 0)
            {
            ?>
    		<div id="carousel3">
    			<div class="bef3"><span id="prev"><img src="../pix/icon/arrow-left.png" alt="" /></span></div>
            		<div class="caro">
		               <div id='mycustomscrolcourse'>
		                <ul class="caromid" id="courselistcontainer">
	                  <?php $c=0;$currenttheme = $CFG->theme; $courseids = '"';
					  foreach($courses as $c1)
					  {							
					  		//if(is_centeradmin() || is_siteadmin()){							     
								foreach($c1 as $c1){ 
								  // $category = $DB->get_record('course_categories',array('id'=>$c1->category));
								   $patterns = array();
								   $replacements = array();
								   //$patterns['jamestown'] = '/Jamestown/';
								   //$replacements['jamestown'] = 'JT';
								   $tooltipname = $c1->fullname;
								   $shorten_name = preg_replace($patterns, $replacements, $c1->fullname);
									//$courseids .= substr($courseids,0,-1);
									if($_GET['id'] == $c1->id){$current = $c; }
								?>
									<li id='course_<?php echo $c1->id; ?>' class ='<?php if($CFG->theme == 'mghc2c'){echo "white";}else{echo "green";} ?>'><a href="<?php echo $CFG->wwwroot.'/course/faculty_dashboard.php?catid='.$c1->category.'&id='.$c1->id.'&edit=on&sesskey='.$USER->sesskey; ?>" title="<?php echo $tooltipname.'(center : '.$category->name.')'; ?>"><?php if($CFG->theme == 'mghc2c'){ ?><span style="padding:0px;margin:3px 0px 0 -33px;"><?php echo ($c+1); ?></span><?php } ?><?php if(strlen($shorten_name)>30){echo wordwrap(substr($shorten_name,0,30),17,"<br>",true).'...';}else{echo wordwrap($shorten_name,16,"<br>",true);} ?></a></li>
									<?php  
									$courseids .= $c1->id.'-';
									$c++; 
							  }
							//}else{
							?>
							<!--<li id='course_<?php echo $c1->id; ?>' class ='green'><a href="<?php echo $CFG->wwwroot.'/course/faculty_dashboard.php?id='.$c1->id; ?>" title="<?php echo $c1->fullname; ?>"><?php echo wordwrap(substr($c1->fullname,0,25),16,"<br>",true);?></a></li>-->
							<?php
							//$c++; 
							//}
						}
						$courseids = substr($courseids,0,-1).'"';
						?>
	                  </ul>
	                    
	                </div>
               </div>
    			<div class="aft3"><span id="next"><img src="../pix/icon/arrow-right.png" alt="" /></span></div>
    		</div>
              </div>
			<?php }
	           else
				  {
					echo get_string('nocourse');                  	
	              }
	         ?>
<script>
var itemno = <?php echo $current; ?>;
var total_item = <?php echo $c; ?>;
var courseids = <?php echo $courseids; ?>;
var mySplitResult = courseids.split("-");
	
var carouselsection    = new YAHOO.widget.Carousel("mycustomscrolcourse", {
		            	numVisible: [4,1] ,
		 	            animation: { speed: 0.5 },
		 	           	navigation:{prev:"prev",next:"next"},
		 	    		carouselEl: "UL" , 	
		 				isVertical: false		 				
					});               	
				carouselsection.on("navigationStateChange",function(){
				 if(carouselsection._nextEnabled)
					{
						document.getElementById("next").style.display='block';
						document.getElementById("next").disabled = false;
					}
					else
					{
						document.getElementById("next").style.display='none';
						document.getElementById("next").disabled = true;
					}
					if(carouselsection._prevEnabled)
					{
						document.getElementById("prev").style.display='block';
						document.getElementById("prev").disabled = false;
					}
					else
					{
						document.getElementById("prev").style.display='none';
						document.getElementById("prev").disabled = true;
					}
				});
				
				carouselsection.render();// get ready for rendering the widget
				carouselsection.show();
				
				left = 165;
				var count = 1;
				for(i = 0; i < mySplitResult.length; i++){
					//alert("course_" + mySplitResult[i]); 
					document.getElementById('course_'+mySplitResult[i]).style.border = '0px';
					var leftpos = (left*count);
					//alert(leftpos);
					if(i==itemno){
						document.getElementById('course_'+mySplitResult[i]).className+=' yui-carousel-item-selected-course';
						//document.getElementById('course_'+mySplitResult[i]).style.left =0;							
					}else if(i<itemno){
						if(i==0){
							document.getElementById('course_'+mySplitResult[i]).className+='green';
						}
						//document.getElementById('course_'+mySplitResult[i]).style.border = '0px';
						//document.getElementById('course_'+mySplitResult[i]).style.left = leftpos+'px';
					}else{
						//alert(current_theme);
						document.getElementById('course_'+mySplitResult[i]).className+='';
						//document.getElementById('course_'+mySplitResult[i]).style.backgroungcolor = '#ff0000';
					}
					count++;
				}
				
				carouselsection.set("selectedItem", itemno);
</script>
<?php
		if($get_teacher && count($get_teachers)>0 || $teacherid){
		?>
		<div id="carouselteacher">
			<div class="bef3"><span id="prevteacher"><img src="../pix/icon/arrow-left.png" alt="" /></span></div>
				<div class="caro">
				   <div id='mycustomscrolteacher'>
						<ul class="caromid" id="teacherlistcontainer">
						<?php
						$teachercount = 0;
						foreach($get_teachers as $get_teacher){
							$shorten_name = $get_teacher->username;
							$course = $DB->get_record('course',array('id'=>$get_teacher->instanceid));
							if($_GET['teacherid'] == $get_teacher->id){
								$currentteacherno = $teachercount; 	
								$currentteacherid = $get_teacher->id;
							}							
							?>
							<li id='teacher_<?php echo $get_teacher->id; ?>' class ='<?php if($CFG->theme == 'mghc2c'){echo "white";}else{echo "green";} ?>'>
								<a href="<?php echo $CFG->wwwroot.'/course/faculty_dashboard.php?catid='.$course->category.'&id='.$course->id.'&teacherid='.$get_teacher->id.'&edit=on&sesskey='.$USER->sesskey; ?>" title="<?php echo $get_teacher->username.'(Teaching '.$course->fullname.' in '.$category->name.' institute)'; ?>"><?php if($CFG->theme == 'mghc2c'){ ?><span style="padding:0px;margin:3px 0px 0 -33px;"><?php echo ($teachercount+1); ?></span><?php } ?>
									<?php if(strlen($shorten_name)>30){echo wordwrap(substr($shorten_name,0,30),17,"<br>",true).'...';}else{echo wordwrap($shorten_name,16,"<br>",true);} ?>
								</a>
							</li>
							<?php
							$teachercount++;
						}
						?>
						</ul>
					</div>
				</div>
			<div class="aft3"><span id="nextteacher"><img src="../pix/icon/arrow-right.png" alt="" /></span></div>	
		</div>
		<?php
		}else{
			die('No faculty assigned to this course');
		}
?>
<script>				
var carouselsectionteacher    = new YAHOO.widget.Carousel("mycustomscrolteacher", {
		            	numVisible: [4,1] ,
		 	            animation: { speed: 0.5 },
		 	           	navigation:{prev:"prevteacher",next:"nextteacher"},
		 	    		carouselEl: "UL" , 	
		 				isVertical: false		 				
					}); 
					carouselsectionteacher.on("navigationStateChange",function(){
				 if(carouselsectionteacher._nextEnabled)
					{
						document.getElementById("nextteacher").style.display='block';
						document.getElementById("nextteacher").disabled = false;
					}
					else
					{
						document.getElementById("nextteacher").style.display='none';
						document.getElementById("nextteacher").disabled = true;
					}
					if(carouselsectionteacher._prevEnabled)
					{
						document.getElementById("prevteacher").style.display='block';
						document.getElementById("prevteacher").disabled = false;
					}
					else
					{
						document.getElementById("prevteacher").style.display='none';
						document.getElementById("prevteacher").disabled = true;
					}
				});
				carouselsectionteacher.render();// get ready for rendering the widget
				carouselsectionteacher.show();
				var teacheritemno = <?php echo $currentteacherno; ?>;
				var teacheritemid = <?php echo $currentteacherid; ?>;
				carouselsectionteacher.set("selectedItem", teacheritemno);
				document.getElementById('teacher_'+teacheritemid).className+=' yui-carousel-item-selected-category';
</script>
<?php
		if($get_classes && count($get_classes)>0){
		//print_r($get_classes);
		?>
		<div id="carouselclass">
			<div class="bef3"><span id="prevclass"><img src="../pix/icon/arrow-left.png" alt="" /></span></div>
				<div class="caro">
				   <div id='mycustomscrolclass'>
						<ul class="caromid" id="classlistcontainer">
						<?php
						$classcount = 0;$shorten_name = '';
						foreach($get_classes as $get_class){
							
							$class = $DB->get_record('groups',array('id'=>$get_class->groupid));
							$shorten_name = $class->name;
							if($_GET['classid'] == $get_class->groupid){
								$currentclassno = $classcount; 	
								$currentclassid = $class->id;
							}							
							?>
							<li id='class_<?php echo $class->id; ?>' class ='<?php if($CFG->theme == 'mghc2c'){echo "white";}else{echo "green";} ?>'>
								<a href="<?php echo $CFG->wwwroot.'/course/faculty_dashboard.php?catid='.$course->category.'&id='.$course->id.'&teacherid='.$teacherid.'&classid='.$class->id.'&edit=on&sesskey='.$USER->sesskey; ?>" title="<?php echo $class->name; ?>"><?php if($CFG->theme == 'mghc2c'){ ?><span style="padding:0px;margin:3px 0px 0 -33px;"><?php echo ($classcount+1); ?></span><?php } ?>
									<?php if(strlen($shorten_name)>30){echo wordwrap(substr($shorten_name,0,30),17,"<br>",true).'...';}else{echo wordwrap($shorten_name,16,"<br>",true);} ?>
								</a>
							</li>
							<?php
							$classcount++;
						}
						?>
						</ul>
					</div>
				</div>
			<div class="aft3"><span id="nextclass"><img src="../pix/icon/arrow-right.png" alt="" /></span></div>	
		</div>
		<?php
		}else{
			die('no classes scheduled for this teahcer in this course');
		}
?>
<script>				
var carouselsectionclass    = new YAHOO.widget.Carousel("mycustomscrolclass", {
		            	numVisible: [4,1] ,
		 	            animation: { speed: 0.5 },
		 	           	navigation:{prev:"prevclass",next:"nextclass"},
		 	    		carouselEl: "UL" , 	
		 				isVertical: false		 				
					}); 
					carouselsectionclass.on("navigationStateChange",function(){
				 if(carouselsectionclass._nextEnabled)
					{
						document.getElementById("nextclass").style.display='block';
						document.getElementById("nextclass").disabled = false;
					}
					else
					{
						document.getElementById("nextclass").style.display='none';
						document.getElementById("nextclass").disabled = true;
					}
					if(carouselsectionteacher._prevEnabled)
					{
						document.getElementById("prevclass").style.display='block';
						document.getElementById("prevclass").disabled = false;
					}
					else
					{
						document.getElementById("prevclass").style.display='none';
						document.getElementById("prevclass").disabled = true;
					}
				});
				carouselsectionclass.render();// get ready for rendering the widget
				carouselsectionclass.show();
				var classitemno = <?php echo $currentclassno; ?>;
				var classitemid = <?php echo $currentclassid; ?>;
				carouselsectionclass.set("selectedItem", classitemno);
				document.getElementById('class_'+classitemid).className+=' yui-carousel-item-selected-course';
</script>
<?php
$eventbox = '';
$coursecontext=get_context_instance(CONTEXT_COURSE, $course->id);
$classmembers = $DB->get_records('groups_members',array('groupid'=>$classid));
$count_student = 0;
$class  = $DB->get_record('groups',array('id'=>$classid));
foreach($classmembers as $classmember){
	$student = $DB->get_records('role_assignments', array('userid'=>$classmember->userid,'contextid'=>$coursecontext->id,'roleid'=>'5'));
	if($student)
		$count_student++;
}
$sql_class = "SELECT * FROM {class_activity} WHERE groupid = $classid and section is null";
$classes_scdeduled = $DB->get_record_sql($sql_class);
$eventbox .= '<div class="classbox1wf">';
$eventbox .='<div class="le-details3">
				<div class="task-le">
					<div class="table2">
						<div style="display:block">
							<h4 class="hd1">'.$class->name;
							$eventbox .= '</h4>
						</div>
					</div>
					<div class="pr3">
						<p>'.$course->fullname.'</p>';						
							$eventbox .= '<p class="numstudent">'.$count_student.'  students are enrolled in this class </p>';	
							if($classes_scdeduled->availablefrom){
							$eventbox .= '<p>'.date('d F Y',$classes_scdeduled->availablefrom).'</p>';
							}
					$eventbox .= '</div>
				</div>
			</div>';

$eventbox .= '<div style="border: 0px solid red;float: right;margin: 10px;padding: 10px;width: 22%;">';
$eventbox .= '<p style="font-size:17px;font-weight:normal;">Course Completion</p>';
$sql_class = "SELECT * FROM {class_activity} WHERE groupid = $classid and section != ''";
$classes_scdeduled = $DB->get_records_sql($sql_class);
$countlessontotal = 0;
$countlessonpassed = 0;
$countlessoncurrent = 0;
$countlessonupcomming = 0;
foreach($classes_scdeduled as $cs_sc){
	//$eventbox .= '<p>'.$cs_sc->groupid.'->'.$cs_sc->availablefrom.'</p>';
	$countlessontotal++;
	if($cs_sc->availablefrom < time()){
		$countlessonpassed++;
	}
	if($cs_sc->availablefrom > time()){
		$countlessonupcomming++;
	}
	if($cs_sc->availablefrom == time()){
		$countlessoncurrent++;
	}
}
$completion = intval((($countlessonpassed+$countlessoncurrent)/$countlessontotal)*100);
$eventbox .= '<div style="width:94%;float:left;text-align:right;"><p style="color:#31B91F;font-size:30px;">'.$completion.'% </p>
								<div style="float:right;width:100%;background:#ff0000;"><span style="width:'.$completion.'%;background:#31B91F;float:left;height:2px;">&nbsp;</span></div></div>';
$eventbox .= '<div style="border-top: 1px dotted #000000;float: left;margin-top: 10px;padding: 10px 0;text-align: right;width: 96%;"><p>Out of '.$countlessontotal.' Lessons <br>'.($countlessonpassed+$countlessoncurrent).' Lesson Completed <br>Till today by '.date('h:i a').'</p></div>';
				
$eventbox .= '</div>';
$eventbox .= '</div>';
echo $eventbox	;
?>
<?php
    // Include the actual course format.
   require($CFG->dirroot .'/course/format/'. $course->format .'/faculty_dashboard_format.php');
    // Content wrapper end.
?>
<?php
    echo html_writer::end_tag('div');

    // Use AJAX?
    if ($useajax && has_capability('moodle/course:manageactivities', $context)) {
        // At the bottom because we want to process sections and activities
        // after the relevant html has been generated. We're forced to do this
        // because of the way in which lib/ajax/ajaxcourse.js is written.
        echo html_writer::script(false, new moodle_url('/lib/ajax/ajaxcourse.js'));
        $COURSE->javascriptportal->print_javascript($course->id);
    }


    echo $OUTPUT->footer();


