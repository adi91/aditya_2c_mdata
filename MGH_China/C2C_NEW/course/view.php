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
	
	 
   /*if(user_has_role_assignment($USER->id,3))
    {
   		redirect($CFG->wwwroot."/course/courset.php");
    }*/
    if(user_has_role_assignment($USER->id,5))
    {
   		//redirect($CFG->wwwroot);
		$courses[0] = enrol_get_users_courses($USER->id,false,'*', 'visible DESC,sortorder ASC',$searchcourse);
    }

if(is_teacher() || is_non_editing_teacher()){
   $searchcourse="";
  	$courses[0] = enrol_get_users_courses($USER->id,false,'*', 'visible DESC,sortorder ASC',$searchcourse);
	}else if(is_centeradmin()){
		
		//DEFINE('CENTERADMIN_ROLEID',10);
		$params = array();
		$sql = 'select * from {role_assignments} where roleid = '.CENTERADMIN_ROLEID.' and userid = '.$USER->id.' and contextid > 1';
	
		$check_role_assignments = $DB->get_records_sql($sql);
		if($check_role_assignments){
			foreach($check_role_assignments as $check_role_assignment){		   
				$context = context::instance_by_id($check_role_assignment->contextid);
				$course_categories = $DB->get_records('course_categories',array('id'=>$context->instanceid));
				$courses[$context->instanceid] = get_courses($context->instanceid, $sort="c.sortorder ASC", $fields="c.*");				
			}
		}		
	}elseif(is_institute_cordinator()){
		$params = array();
		$sql = 'select * from {role_assignments} where roleid = 13 and userid = '.$USER->id.' and contextid > 1';
		$catids = '';
		$check_role_assignments = $DB->get_records_sql($sql);
		if($check_role_assignments){
			foreach($check_role_assignments as $check_role_assignment){				
				$context = context::instance_by_id($check_role_assignment->contextid);
				$catids .= $context->instanceid.',';					
			}
			$catidstring = substr($catids,0,-1);
			$catsql = "SELECT * FROM {course_categories} where id in ($catidstring)";
			$course_categories = $DB->get_records_sql($catsql);
			//print_r($course_categories);
			if(isset($_GET['catid']) && $_GET['catid']!=''){
				$courses[$_GET['catid']] = get_courses($_GET['catid'], $sort="c.sortorder ASC", $fields="c.*");	
			}else{
				$courses[$context->instanceid] = get_courses($context->instanceid, $sort="c.sortorder ASC", $fields="c.*");
			}
		}	
	}else if(is_siteadmin() || is_mhescordinator() || is_institute_cordinator()){
		$course_categories = $DB->get_records('course_categories',array());
		if($CFG->theme == 'mghc2c'){
			$courses[$_GET['catid']] = get_courses($_GET['catid'], $sort="c.sortorder ASC", $fields="c.*");
		}else{		
			foreach($course_categories as $course_category){
				$courses[$course_category->id] = get_courses($course_category->id, $sort="c.sortorder ASC", $fields="c.*");
			}
		}
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

    $PAGE->set_url('/course/view.php', array('catid'=>$course->category,'id' => $course->id)); // Defined here to avoid notices on errors etc

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

    add_to_log($course->id, 'course', 'view', "view.php?catid=$course->category&id=$course->id", "$course->id");

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

    $SESSION->fromdiscussion = $CFG->wwwroot .'/course/view.php?catid='.$course->category.'&id='. $course->id;


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
        $buttons = $OUTPUT->edit_button(new moodle_url('/course/view.php', array('id' => $course->id)));
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
									<a title="" href="<?php echo $CFG->wwwroot; ?>/course/view.php?catid=<?php echo $category->id; ?>&id=<?php echo $courseid; ?>&edit=on&sesskey=<?php echo $USER->sesskey; ?>">
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
									<li id='course_<?php echo $c1->id; ?>' class ='<?php if($CFG->theme == 'mghc2c'){echo "white";}else{echo "green";} ?>'><a href="<?php echo $CFG->wwwroot.'/course/view.php?catid='.$c1->category.'&id='.$c1->id.'&edit=on&sesskey='.$USER->sesskey; ?>" title="<?php echo $tooltipname.'(center : '.$category->name.')'; ?>"><?php if($CFG->theme == 'mghc2c'){ ?><span style="padding:0px;margin:3px 0px 0 -33px;"><?php echo ($c+1); ?></span><?php } ?><?php if(strlen($shorten_name)>30){echo wordwrap(substr($shorten_name,0,30),17,"<br>",true).'...';}else{echo wordwrap($shorten_name,16,"<br>",true);} ?></a></li>
									<?php  
									$courseids .= $c1->id.'-';
									$c++; 
							  }
							//}else{
							?>
							<!--<li id='course_<?php echo $c1->id; ?>' class ='green'><a href="<?php echo $CFG->wwwroot.'/course/view.php?id='.$c1->id; ?>" title="<?php echo $c1->fullname; ?>"><?php echo wordwrap(substr($c1->fullname,0,25),16,"<br>",true);?></a></li>-->
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
    // Include the actual course format.
    require($CFG->dirroot .'/course/format/'. $course->format .'/format.php');
    // Content wrapper end.
?>
<?php if(is_mhescordinator()){ ?>
<div style="float:left;clear:both;width:98%;">
	<div><h2  style="border-top:1px dashed #FF6600;border-bottom:1px dashed #FF6600;padding:10px 0;">Reports</h2></div>
	<div class = "content" style="width:89%;margin:0 auto;">
		<div class = "summaryview report" style="border-top:2px solid #FF7E00;margin:10px 0;padding:10px;float:left;width:95%;">
			<ul>
				<li><a href = "<?php echo $CFG->wwwroot.'/report/outline/index.php?id='.$_GET['id'];?>" target="_blank">Activity Report</a></li>
				<li><a href = "<?php echo $CFG->wwwroot.'/report/participation/index.php?id='.$_GET['id'];?>" target="_blank">Course Participation Report</a></li>
				<li><a href = "<?php echo $CFG->wwwroot.'/report/log/index.php?id='.$_GET['id'];?>" target="_blank">Log Report</a></li>
			</ul>
		</div>
	</div>
</div>
<div style="float:left;clear:both;width:98%;">
	<div><h2  style="border-top:1px dashed #FF6600;border-bottom:1px dashed #FF6600;padding:10px 0;">Upload Users</h2></div>
	<div class = "content" style="width:89%;margin:0 auto;">
		<div class = "summaryview" style="border-top:2px solid #FF7E00;margin:10px 0;padding:10px;float:left;width:95%;">
			<span style="float:right;">
			<?php 	
				echo '<a href="'.$CFG->wwwroot.'/admin/download_file.php?filename=upload_user.csv" class="buttonstyled">'.get_string('uploadusertemp').'</a>';
			?></span>
			<?php
			require('user_co.php');
			?>
		</div>
	</div>
</div>
<?php } ?>
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


