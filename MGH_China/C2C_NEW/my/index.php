<?php 
require_once(dirname(__FILE__) . '/../config.php');
require_once($CFG->dirroot . '/my/lib.php');
require_once($CFG->dirroot . '/message/lib.php');
require_once($CFG->libdir .'/accesslib.php');
require_once($CFG->libdir .'/datalib.php');
require_once($CFG->libdir .'/moodlelib.php');
require_once($CFG->dirroot . '/message/lib.php');
require_once($CFG->libdir . '/datalib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir.'/conditionlib.php');
require_once($CFG->dirroot.'/mod/scorm/locallib.php'); 
require_once($CFG->dirroot.'/mod/resource/locallib.php');
redirect_if_major_upgrade_required();

// TODO Add sesskey check to edit
$edit   = optional_param('edit', null, PARAM_BOOL);    // Turn editing on and off

$searchcourse   = optional_param('searchcourse', null, PARAM_RAW); 

require_login();

$strmymoodle = get_string('myhome');

if (isguestuser()) {  // Force them to see system default, no editing allowed
    $userid = NULL; 
    $USER->editing = $edit = 0;  // Just in case
    $context = get_context_instance(CONTEXT_SYSTEM);
    $PAGE->set_blocks_editing_capability('moodle/my:configsyspages');  // unlikely :)
    $header = "$SITE->shortname: $strmymoodle (GUEST)";

} else {        // We are trying to view or edit our own My Moodle page
    $userid = $USER->id;  // Owner of the page
    $context = get_context_instance(CONTEXT_USER, $USER->id);
    $PAGE->set_blocks_editing_capability('moodle/my:manageblocks');
    $header = "$SITE->shortname: $strmymoodle";
}

// Get the My Moodle page info.  Should always return something unless the database is broken.
if (!$currentpage = my_get_page($userid, MY_PAGE_PRIVATE)) {
    print_error('mymoodlesetup');
}

if (!$currentpage->userid) {
    $context = get_context_instance(CONTEXT_SYSTEM);  // So we even see non-sticky blocks
}

// Start setting up the page
$params = array();
$PAGE->set_context($context);
$PAGE->set_url('/my/index.php', $params);
$PAGE->set_pagelayout('mydashboard');
$PAGE->set_pagetype('my-index');
$PAGE->blocks->add_region('content');
$PAGE->set_subpage($currentpage->id);
$PAGE->set_title($header);
$PAGE->set_heading($header);

if (get_home_page() != HOMEPAGE_MY) {
    if (optional_param('setdefaulthome', false, PARAM_BOOL)) {
        set_user_preference('user_home_page_preference', HOMEPAGE_MY);
    } else if (!empty($CFG->defaulthomepage) && $CFG->defaulthomepage == HOMEPAGE_USER) {
        $PAGE->settingsnav->get('usercurrentsettings')->add(get_string('makethismyhome'), new moodle_url('/my/', array('setdefaulthome'=>true)), navigation_node::TYPE_SETTING);
    }
}

// Toggle the editing state and switches
if ($PAGE->user_allowed_editing()) {
    if ($edit !== null) {             // Editing state was specified
        $USER->editing = $edit;       // Change editing state
        if (!$currentpage->userid && $edit) {
            // If we are viewing a system page as ordinary user, and the user turns
            // editing on, copy the system pages as new user pages, and get the
            // new page record
            if (!$currentpage = my_copy_page($USER->id, MY_PAGE_PRIVATE)) {
                print_error('mymoodlesetup');
            }
            $context = get_context_instance(CONTEXT_USER, $USER->id);
            $PAGE->set_context($context);
            $PAGE->set_subpage($currentpage->id);
        }
    } else {                          // Editing state is in session
        if ($currentpage->userid) {   // It's a page we can edit, so load from session
            if (!empty($USER->editing)) {
                $edit = 1;
            } else {
                $edit = 0;
            }
        } else {                      // It's a system page and they are not allowed to edit system pages
            $USER->editing = $edit = 0;          // Disable editing completely, just to be safe
        }
    }

    // Add button for editing page
    $params = array('edit' => !$edit);

    if (!$currentpage->userid) {
        // viewing a system page -- let the user customise it
        $editstring = get_string('updatemymoodleon');
        $params['edit'] = 1;
    } else if (empty($edit)) {
        $editstring = get_string('updatemymoodleon');
    } else {
        $editstring = get_string('updatemymoodleoff');
    }

    $url = new moodle_url("$CFG->wwwroot/my/index.php", $params);
    $button = $OUTPUT->single_button($url, $editstring);
    $PAGE->set_button($button);

} else {
    $USER->editing = $edit = 0;
}

// HACK WARNING!  This loads up all this page's blocks in the system context
if ($currentpage->userid == 0) {
    $CFG->blockmanagerclass = 'my_syspage_block_manager';
}
echo $OUTPUT->header();
if(is_siteadmin() || user_has_role_assignment($USER->id,CENTERADMIN) || user_has_role_assignment($USER->id,SITEADMIN) || user_has_role_assignment($USER->id,3))
{
	echo $OUTPUT->blocks_for_region('content');
}
else
{
	$arr_courses= array();
	$courses = apt_enrol_get_users_courses($USER->id,false,'*', 'visible DESC,sortorder ASC',$searchcourse);
	
	$firstcourseid='';
	if(count($courses) > 0)
	{	
		foreach ($courses as $c) 
		{
			if(trim($firstcourseid) =='')
			{			
				$firstcourseid = $c->id;
			}
			 $modinfo =& get_fast_modinfo($c);
			
			get_all_mods($c->id, $mods, $modnames, $modnamesplural, $modnamesused);
			$sections = array_slice(get_all_sections($c->id), 0, $c->numsections+1, true);
			$section_arr = array();
			reset($sections);
			foreach ($sections as $key_section => $section) 
			{
				$sectionname = get_section_name($c,$section);
				if (!array_key_exists($section->section, $modinfo->sections) || $key_section == 0) 
				{
					continue;
				}
				if(!in_array($sectionname,$section_arr) )
				{
						$section_arr[$section->section] = $sectionname;
				}
			}
			if(count($section_arr) == 0  )
			{
				continue;
			}
			 
			 
			  if(count($modinfo) == 0)
			 {
			 	continue;
			 }
			$arr_courses[$c->category][]=$c;
		}
	}

?>
<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/yahoo/yahoo-min.js"></script> 
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
<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/treeview/treeview-min.js"></script>

<link href="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/carousel/assets/skins/sam/carousel.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/treeview/assets/skins/sam/treeview.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $CFG->wwwroot;?>/my/css/font.css" rel="stylesheet" type="text/css">
<link href="<?php echo $CFG->wwwroot;?>/my/css/style.css" rel="stylesheet" type="text/css">
<!-- Start main page block  -->
<div class="bodycontents">
    <div class="contentsrtucture">
    <!--Page contents Starts-->
    <div id="students-homepage">
      <div class="section_a"> 
        <!--COURSE SECTION & LESSON START-->
        <div class="course-n-lesson">
        <h2 class="course">Courses</h2>
          <h2 class="lesson">Visits</h2>
          <!-- <form action="#" method="post">
          <div class="searchboxfam">Search:<img src="<?php //echo $OUTPUT->pix_url('search_zoom', 'theme'); ?>" id="search" >
          <input type="text" name="searchcourse" class="inp-bx" value="<?php //echo $searchcourse; ?>"/><input type="submit" value="Go" class="btn-clear" /></div>
          </form> -->
          <div class="overflow-section">
            <div class="rgscroll"  style="">
              <div>
              <span style="float: left; margin-left: -26px;margin-top: 0px;"><input type="image" style="display:none;" src="<?php echo $OUTPUT->pix_url('arrow_top_grey', 'theme'); ?>" id="prev" /></span>
              <span style="float:left; margin-left: -26px; margin-top:305px;"><input type="image" style="display:none;" src="<?php echo $OUTPUT->pix_url('arrow_bottom_grey', 'theme'); ?>" id="next"/></span>  
                <div id='mycustomscroll21' class='lipsum'>
                  <ul class="course-list">
                  <?php  
				  foreach($courses as $c1)
				  {	
				  ?>
			        <li id='course_<?php echo $c1->id; ?>'><a href="#" onclick='getlessonload(<?php echo $c1->id; ?>);' ><?php echo $c1->fullname;?></a>
			        <?php if(!empty($c1->summary)) {?> 
			        <?php echo "Desc: ".substr($c1->summary, 0, 25); ?> <a href="#" onclick="viewdesc(<?php echo $c1->id?>)"> </a>
			        <?php  }?>
			        </li>
                    <?php 
					}
					?>
                  </ul>
                  <?php 
                  if(count($courses) > 0)
                  {
                  	?>
                  <script>
		            var carouselsection    = new YAHOO.widget.Carousel("mycustomscroll2", {
		            	 numVisible: [1,14] ,
		 	            animation: { speed: 0.5 },
		 	           navigation:{prev:"prev",next:"next"},
		 	    		carouselEl: "UL" , 	
		 				isVertical: true
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
                  </script>
                  <?php
                  }
                  else
                  {
					echo "No course found.";                  	
                  }
                  ?>
                  
                </div>
               
              </div>
            </div>
            <div class="rgscroll2" >
              <div>
              <span style="float: right; margin-right: -14px;margin-top: -8px;"><input type="image" style="display:none;" src="<?php echo $OUTPUT->pix_url('arrow_top_grey', 'theme'); ?>" id="prev_l"   /></span>
                <div class='lipsum' id='mycustomscroll3'>
                  <ul class="lesson-list">
                  </ul>
                </div>
               <span style="float: right; margin-right: -14px;margin-top: -8px;"><input type="image" style="display:none;" src="<?php echo $OUTPUT->pix_url('arrow_bottom_grey', 'theme'); ?>" id="next_l"   /></span>
              </div>
            </div>
          </div>
        </div>
      
		
        <!--ASSESSMENT & RESOURCES ENDS--> 
      </div>
      <div class="section_b">
 		<!--ASSESSMENT & RESOURCES START-->
        <div class="assess-n-resoure">
          <h2 class="assessment"><div id='sectionlessonid'>&nbsp;</div></h2>
          <div class="overflow-section-two">
            <div  id="assessmentdiv" class="assessment-list">
           
            
            </div>
          </div>
        </div>
        <!--ASSESSMENT & RESOURCES ENDS--> 
        
        <!-- <div id="videoid2" class="video-galbox"> <h2 class="videoplaylistheading">&nbsp;Video</h2>  <span id="lvg"></span><span id="rvg"></span> 
          <div class="video-playlist">
           
            <span style="float: right; margin-right: 0px;margin-top: -15px;"><input type="image" style="display:none" src="<?php echo $OUTPUT->pix_url('arrow_top_grey', 'theme'); ?>" id="prev_v"   /></span>
            <div id='video' style="margin-top:-12px;" >
            
            </div>
            <span style="float: right; margin-right: 0px;margin-top: -37px;"><input type="image" style="display:none" src="<?php echo $OUTPUT->pix_url('arrow_bottom_grey', 'theme'); ?>" id="next_v"   /></span>
          </div> -->
          <div  id="videoid1" class="assess-n-video" >
          <h2 class="video-heading" id="videoheading">&nbsp;</h2>
            <h4 ><span >&nbsp;</span></h4> 
          <div id="videobox" class="videor"></div>
        </div>
          <!-- <span id="lbvg"></span> <span id="rbvg"></span>  --></div>
      </div>
      
      <div class="section_c">
      <?php 	
						if(count($courses)>0) {
							foreach ($courses as $c) 
							{
									$setSectionid=0;
									$modinfo =& get_fast_modinfo($c);
									get_all_mods($c->id, $mods, $modnames, $modnamesplural, $modnamesused);
									$sections = array_slice(get_all_sections($c->id), 0, $c->numsections+1, true);
									$param = array($c->id);
									$course =$DB->get_record_select("course","id=?",$param);
									$coursename = strtoupper($c->shortname);
									//$arr_course[$c->id] = strtoupper($c->shortname);
									$arr_color = array('#F39B09','#90C31F','#000000','#7B8D8D');
									 $level = count($sections)-1;	
										$timenow = time();
									    $weekdate = $course->startdate;    // this should be 0:00 Monday of that week
									    $weekdate += 7200;                 // Add two hours to avoid possible DST problems
									    $sectionweek = 0;
									    $weekofseconds = 604800;
									    //echo "<br>course->enddate:".
									    $course->enddate = $course->startdate + ($weekofseconds * $course->numsections);
										while ($weekdate < $course->enddate) {
									        	$nextweekdate = $weekdate + ($weekofseconds);
									        	$currentweek = (($weekdate <= $timenow) && ($timenow < $nextweekdate));
									        	if($currentweek)
									        	{
													$setSectionid=$sectionweek;					        		
									        	}
												$sectionweek++;
					            				$weekdate = $nextweekdate;
												}
										
									 
									 $completed_courselevel=$setSectionid;
								
							
							$height = 48;
							$width = 680 ;
							$spacing = 20;		
							//echo create_unit_lesson_image($arr_course,$arr_course_unit_per,$arr_courselevel,$arr_color,$height,$width ,$spacing); 
						?>
      
	       						<div style="float:left; width:<?php echo $width; ?>px;  border:0px solid blue;"><?php 
								if(count($sections) > 1)
								{
									$coursename  = $coursename." (".$setSectionid."/".$level.") Weeks";
									
									echo mcg_create_unit_lesson_image($coursename,$level,$completed_courselevel,$arr_color,$height,$width,$spacing);
									// mcg_create_unit_lesson_image($arr_course,$arr_course_unit_per,$arr_courselevel,$arr_color,$height,$width,$spacing); 
								}
								echo "</div>";
							}
						}
					?>
      </div>
      </div>
      <!--Page contents end-->
      
    </div>
  </div>
<?php
}
echo $OUTPUT->footer(); ?>
<script>
var carouselcourses;
var carouselvideo;
function getlessonload(courseid)
{
	var assessmentdetails = YAHOO.util.Dom.get('assessmentdiv');
	assessmentdetails.innerHTML ="";
	var sectionlessoniddet = YAHOO.util.Dom.get('sectionlessonid');
	sectionlessoniddet.innerHTML ="";
	var courseli = YAHOO.util.Dom.get('course_'+courseid);
	var pos = courseli.className.indexOf('yui-carousel-item-selected_');
	if(pos >= 0)
	{
		return;
	}
	
	var surl = '<?php echo $CFG->wwwroot?>/my/ajaxoperation.php?action=coursedetails&courseid='+courseid+'&time=';
	var coursedetails = YAHOO.util.Dom.get('mycustomscroll3');
	coursedetails.innerHTML ='loading.....';
	var videodetails = YAHOO.util.Dom.get('videobox');
	videodetails.innerHTML ='';
	
	YUI({ filter: 'raw' }).use("io-base", "node",
			function(Y) 
			{
				
				//A function handler to use for successful requests:
				var handleSuccess = function(ioId, o)
				{
					if(o.responseText =="")
					{
							return;
					}
					var messages = [];
		            try {
		                messages = YAHOO.lang.JSON.parse(o.responseText);
		            }
		            catch (x) {
		                alert("JSON Parse failed!");
		                return;
		            }
					if(messages.htmllesson != undefined &&  messages.htmllesson != null)
					{		
						coursedetails.innerHTML = 	messages.htmllesson;
					}
					
					if(messages.jsscript != undefined &&  messages.jsscript != null)
					{
						eval(messages.jsscript);
					}
					 var carouselcoursedetails    = new YAHOO.widget.Carousel("mycustomscroll3", {
		            	 numVisible: [1,15] ,
		 	            animation: { speed: 0.5 },
		 	           	navigation:{prev:"prev_l",next:"next_l"},
		 				carouselEl: "UL" , 	
		 				isVertical: true
					}); 
					 
					 	
					 carouselcoursedetails.on("navigationStateChange",function(){
						 //alert(carouselcoursedetails._nextEnabled);
							if(carouselcoursedetails._nextEnabled)
							{
								document.getElementById("next_l").style.display='block';
								document.getElementById("next_l").disabled = false;
							}
							else
							{
								document.getElementById("next_l").style.display='none';
								document.getElementById("next_l").disabled = true;
							}
							if(carouselcoursedetails._prevEnabled)
							{
								document.getElementById("prev_l").style.display='block';
								document.getElementById("prev_l").disabled = false;
							}
							else
							{
								document.getElementById("prev_l").style.display='none';
								document.getElementById("prev_l").disabled = true;
							}
						});

						
					 carouselcoursedetails.render();// get ready for rendering the widget
					 carouselcoursedetails.show();        
			    }
				//A function handler to use for failed requests:
				var handleFailure = function(ioId, o)
				{
					
				}
				//Subscribe our handlers to IO's global custom events:
				Y.on('io:success', handleSuccess);
				Y.on('io:failure', handleFailure);
			
				var cfg = 
				{
					method: "GET",
					headers: { 'X-Transaction': 'GET Example'}
				};		
				var request = Y.io(surl, cfg);	
			}
		);
}

function getsectionload(sectionid,courseid)
{
	var assessmentdetails = YAHOO.util.Dom.get('assessmentdiv');
	assessmentdetails.innerHTML ='loading.....';
	//var videodetails = YAHOO.util.Dom.get('video');
//	videodetails.innerHTML ='loading.....';
//	alert(YAHOO.util.Dom.get('sectionlessonid'));
	
	var surl = '<?php echo $CFG->wwwroot?>/my/ajaxoperation.php?action=sectiondetails&courseid='+courseid+'&sectionid='+sectionid+'&time=';
	
	YUI({ filter: 'raw' }).use("io-base", "node",
			function(Y) 
			{
				
				//A function handler to use for successful requests:
				var handleSuccess = function(ioId, o)
				{
					if(o.responseText =="")
					{
							return;
					}
					var messages = [];
		            try {
		                messages = YAHOO.lang.JSON.parse(o.responseText);
		            }
		            catch (x) {
		                alert("JSON Parse failed!");
		                return;
		            }
					
					if(messages.htmlassessment != undefined &&  messages.htmlassessment != null && messages.htmlassessment !="" && messages.htmlassessment !='<div id="carouselcoursedetails" class="lesson-list" ></div>')
					{		
						assessmentdetails.innerHTML = 	messages.htmlassessment;
					}
					
					
					/*if(messages.htmlvideo != undefined &&  messages.htmlvideo != null )
					{
						videodetails.innerHTML = 	messages.htmlvideo;
						if(messages.htmlvideo !='')
						{
						 var carouselvideo    = new YAHOO.widget.Carousel("video", {
							 numVisible: [1,3] ,
							animation: { speed: 0.5 },
						   navigation:{prev:"prev_v",next:"next_v"},
							carouselEl: "UL" , 	
							isVertical: true
						});
						 carouselvideo.on("navigationStateChange",function(){
							
							if(carouselvideo._nextEnabled)
							{
								document.getElementById("next_v").style.display='block';
								document.getElementById("next_v").disabled = false;
							}
							else
							{
								document.getElementById("next_v").style.display='none';
								document.getElementById("next_v").disabled = true;
							}
							if(carouselvideo._prevEnabled)
							{
								document.getElementById("prev_v").style.display='block';
								document.getElementById("prev_v").disabled = false;
							}
							else
							{
								document.getElementById("prev_v").style.display='none';
								document.getElementById("prev_v").disabled = true;
							}
						});
						
						
						
						
						carouselvideo.render();// get ready for rendering the widget
						carouselvideo.show();
						
					}
				}*/
					if(messages.jsscript != undefined &&  messages.jsscript != null && messages.jsscript !='')
					{
						eval(messages.jsscript);
					}
					
				}
				var handleFailure = function(ioId, o)
				{
					
				}
				//alert(messages.htmlassessment);
				//Subscribe our handlers to IO's global custom events:
				Y.on('io:success', handleSuccess);
				Y.on('io:failure', handleFailure);
			
				var cfg = 
				{
					method: "GET",
					headers: { 'X-Transaction': 'GET Example'}
				};		
				var request = Y.io(surl, cfg);	
			}
		);	
}


function getvideoload(videoid)
{

	var videodetailsli = YAHOO.util.Dom.get('video_'+videoid);
	/*var pos = videodetailsli.className.indexOf('yui-carousel-item-selected-video');
	if(pos >= 0)
	{
		return;
	}*/
	var surl = '<?php echo $CFG->wwwroot?>/my/ajaxoperation.php?action=videoshow&videoid='+videoid;
	var videodetails = YAHOO.util.Dom.get('videobox');
	videodetails.innerHTML ='loading.....';
	YUI({ filter: 'raw'}).use("io-base", "node",
			function(Y) 
			{
				
				//A function handler to use for successful requests:
				var handleSuccess_video = function(ioId, o)
				{
					
					
					if(o.responseText =="")
					{
							return;
					}
					
					var resp = [];
		            try {
		                resp = YAHOO.lang.JSON.parse(o.responseText);
		            }
		            catch (x) {
		                alert("JSON Parse failed!");
		                return;
		            }
		            videodetails.innerHTML = 	resp.code;
		            eval(resp.jsscript);
			    }
				//A function handler to use for failed requests:
				var handleFailure_video = function(ioId, o)
				{
					
				}
				//Subscribe our handlers to IO's global custom events:
				Y.on('io:success', handleSuccess_video);
				Y.on('io:failure', handleFailure_video);
			
				var cfg = 
				{
					method: "GET",
					headers: { 'X-Transaction': 'GET Example'}
				};		
				var request = Y.io(surl, cfg);	
			}
		);
}


function viewdesc()
{
	
}
<?php
if($firstcourseid != '')
echo "getlessonload(".$firstcourseid.");";
?>
</script>