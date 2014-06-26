<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Moodle frontpage.
 *
 * @package    core
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    if (!file_exists('./config.php')) {
        header('Location: install.php');
        die;
    }

    require_once('./config.php');
    require_once($CFG->dirroot .'/course/lib.php');
    require_once($CFG->dirroot .'/group/lib.php');
    require_once($CFG->libdir .'/filelib.php');

    redirect_if_major_upgrade_required();
	$courseid          = optional_param('courseid', 0, PARAM_INT);
	$sectionid          = optional_param('sectionid', 0, PARAM_INT);
    $urlparams = array();
    if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_MY) && optional_param('redirect', 1, PARAM_BOOL) === 0) {
        $urlparams['redirect'] = 0;
    }
    $PAGE->set_url('/', $urlparams);
    $PAGE->set_course($SITE);

    if ($CFG->forcelogin) {
        require_login();
    } else {
        user_accesstime_log();
    }

    $hassiteconfig = has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));

/// If the site is currently under maintenance, then print a message
    if (!empty($CFG->maintenance_enabled) and !$hassiteconfig) {
        print_maintenance_message();
    }

    if ($hassiteconfig && moodle_needs_upgrading()) {
        redirect($CFG->wwwroot .'/'. $CFG->admin .'/index.php');
    }

    if (get_home_page() != HOMEPAGE_SITE) {
        // Redirect logged-in users to My Moodle overview if required
        if (optional_param('setdefaulthome', false, PARAM_BOOL)) {
            set_user_preference('user_home_page_preference', HOMEPAGE_SITE);
        } else if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_MY) && optional_param('redirect', 1, PARAM_BOOL) === 1) {
            redirect($CFG->wwwroot .'/my/');
        } else if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_USER)) {
            $PAGE->settingsnav->get('usercurrentsettings')->add(get_string('makethismyhome'), new moodle_url('/', array('setdefaulthome'=>true)), navigation_node::TYPE_SETTING);
        }
    }

    if (isloggedin()) {
        add_to_log(SITEID, 'course', 'view', 'view.php?id='.SITEID, SITEID);
    }

/// If the hub plugin is installed then we let it take over the homepage here
    if (get_config('local_hub', 'hubenabled') && file_exists($CFG->dirroot.'/local/hub/lib.php')) {
        require_once($CFG->dirroot.'/local/hub/lib.php');
        $hub = new local_hub();
        $continue = $hub->display_homepage();
        //display_homepage() return true if the hub home page is not displayed
        //mostly when search form is not displayed for not logged users
        if (empty($continue)) {
            exit;
        }
    }
	
	$PAGE->set_pagetype('site-index');
    $PAGE->set_other_editing_capability('moodle/course:manageactivities');
    $PAGE->set_docs_path('');
    $PAGE->set_pagelayout('frontpage_student');
    $editing = $PAGE->user_is_editing();
    $PAGE->set_title($SITE->fullname);
    $PAGE->set_heading($SITE->fullname);
    echo $OUTPUT->header();
	
    $arr_courses= array();
    $searchcourse='';
    $firstcourseid='';
   
if($courseid > 0)
{
	$courses = $DB->get_record('course',array('id'=>$courseid));
	$firstcourseid = $courseid;
}
else
{
    global $COURSE;
  
   $courses = apt_enrol_get_users_courses($USER->id,true,'*', 'visible DESC,sortorder ASC',$searchcourse);
 
	if(count($courses) > 0)
	{	
		foreach ($courses as $c) 
		{
			if(trim($firstcourseid) =='')
			{			
				$firstcourseid = $c->id;
			}
			 $modinfo =& get_fast_modinfo($c);
			// echo "<pre>";//print_r($modinfo); echo "</pre>";
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
}
$startday=strtotime('0 day', strtotime(date('Y-m-d')));
$endday= $startday + (3600 *24);
?>
<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/yahoo/yahoo-min.js"></script> 
<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/utilities/utilities.js"></script> 
<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/selector/selector-min.js"></script> 
<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/event/event-min.js"></script> 
<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/yahoo-dom-event/yahoo-dom-event.js"></script> 
<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/event-delegate/event-delegate-min.js"></script> 
<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/event-mouseenter/event-mouseenter-min.js"></script> 
<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/carousel/carousel-min.js"></script> 
<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/connection/connection-min.js"></script> 
<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/connection/connection_core-min.js"></script> 
<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/container/container-min.js"></script> 
<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/element-delegate/element-delegate-min.js"></script> 
<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/json/json-min.js"></script>
<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/progressbar/progressbar-min.js"></script>
<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/treeview/treeview-min.js"></script>
<link href="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/carousel/assets/skins/sam/carousel.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/treeview/assets/skins/sam/treeview.css" rel="stylesheet" type="text/css" />
<style>
#sectionlessonid .yui-carousel-nav
{
	display:none;
}
#mycustomscroll3 .yui-carousel-nav
{
	display:none;
}
.yui-skin-sam .yui-carousel, .yui-skin-sam .yui-carousel-vertical
{
	border: none !important;
}


#sectionlessonid .yui-carousel .yui-carousel-item-selected {
   	border:2px solid blue !important;
}
.yui-carousel .yui-carousel-item-selected {
   	border:2px solid red !important;
}

#sectionlessonid .yui-carousel-item
{
	margin: 0 6px 0 4px;
	outline: medium none;
	/* min-width: 149px !important;
    position: static;*/
} 
#laod_assigment .continuebutton
{
display:none;
}
input.playvideo {
width:179px; height:168px;
 background: url("./pix/play.png") no-repeat scroll -5px -5px transparent;
 border:none;
 outline:none;
 z-index:999;
 }
input.playvideo:hover {
width:179px; height:168px;
background: url("./pix/play-hover.png") no-repeat scroll -5px -5px transparent;
z-index:999;
 }
#framediv{
	overflow-x:hidden;
	overflow-y:scroll;
	width:912px;
	height:525px;
	border:none;
	z-index:999;
	
}

</style>
<div id='mycustomscroll21' class='lipsum'>
	<ul class="course-list">
	<?php  
	foreach($courses as $c1)
	{	
	?>
	<span id='course_<?php echo $c1->id; ?>'><a href="#" onclick='getlessonload(<?php echo $c1->id; ?>);' ><?php // echo $c1->fullname;?></a></span>
	<?php 
	}
	if($courseid >0 || $sectionid >0)
	{
	$group2= groups_get_groupby_role($USER->id,$courseid);
	global $DB;
	$param = array($group2->id,$sectionid,$courseid);
	$sql= "SELECT * FROM {class_activity} WHERE groupid=? AND sectionno=? AND course=? AND module is NOT NULL";
	$sec_act2 = $DB->get_record_sql($sql, $param);
	//$sec_act2=get_group_les_activity($group2->id,$sectionid,$courseid);
	//$dat_for_sc= date("m-d-y", $sec_act2->availablefrom);
	?>
	<span id='course_<?php echo $courses->id; ?>'><a href="#" onclick='getlessonload(<?php echo $courses->id; ?>);' ><?php // echo $c1->fullname;?></a></span>	
	<?php }
	?>
	</ul>
	<?php 
	if(count($courses) > 0)
	{
	$today_date =date("d-M-Y");

?>
</div>


   <div class="index-rightboard" style="position: relative;" >
   	<span id="submit_button_act" style="display:none !important; left: 163px; position: absolute; top: 163px; ">
   		<input type="button"  id="submit_ifrmae" class="playvideo"></input>
   		</span>
   		<div id="laod_assigment" style="background: none repeat scroll 0 0 #FFFFFF;border-radius: 6px 6px 6px 6px; box-shadow: 0 0 2px #333333; padding:10px;font-family:Arial, Helvetica, sans-serif;width:912px;height:512px;"> 
   		<style>
		   	#laod_assigment .continuebutton
			{
			display:none;
			}
		</style>
		<div id="videobox"></div>
 	   	<h5><?php echo get_string('studenthomepagemsg')?> &#150; <span id="disp_date"><?php echo  $today_date;?></span></h5>
	  	<iframe  width="912px" height="525px" frameborder="no" id="iframe3" ></iframe>
	</div>
	  <!-- <img src="pix/fruitGame.png" alt=""  /> -->
	</div>
	<div id="carousel1">
    	<div class="bef"><span id="prev_v"><input type="image" src="./pix/icon/arrow-left.png" alt="" /></span></div>
            <div class="caro" >
            	<div id='sectionlessonid' style="width:490px;">
	                <ul class="carobig"">
	                 </ul>
                 </div>
            </div>
    	<div  class="aft"><span id="next_v"><input type="image" src="./pix/icon/arrow-right.png" alt="" /></span></div>
    </div>
	<div id="carousel2">
        <div class="bef"><span id="prev_course"><input type="image" src="./pix/icon/arrow-left.png" alt="" /></span></div>
        <div class="caro">
	        <div id='mycustomscroll3' style="width:495px !important;">
	                <ul class="carosmall">
	                 </ul>
	        </div>
        </div>
        <div class="aft"><span id="next_course"><input type="image" src="./pix/icon/arrow-right.png" alt="" /></span></div>
    </div>
    


<div class="">
	<div id='sectionlessonid'>
	<ul class="lesson-list"></ul></div>
	<div class="">
	<div  id="assessmentdiv" class="">
	<ul class="lesson-list"></ul>
	</div>
</div>
        
</div>
<div class="rgscroll2" >
	<div>
		<div class='lipsum' id='mycustomscroll3' >
		<ul class="lesson-list"></ul>
		</div>
	</div>
</div>


<form name="frm_sele" id="frm_sele" action="<?php echo $_SERVER['PHP_SELF'];?>">	
<input type="hidden" name="user_selec_course" id="user_selec_course" value=""></input>
</form>

<?php
}
else
{
echo get_string('nocourse')." ".get_string('contactcenter');
global $COURSE,$DB;
$courses = enrol_get_users_courses($USER->id,true,'*', 'visible DESC,sortorder ASC',$searchcourse);

foreach($courses as $course) {
	$contextid = get_context_instance(CONTEXT_COURSECAT, $course->category);
	 $sql= "SELECT * FROM {user} u
			JOIN {role_assignments} ra ON ra.userid= u.id 
			WHERE ra.contextid =? AND ra.roleid=?";
	$param= array($contextid->id,10);
	
	$rs=$DB->get_records_sql($sql,$param);

	foreach($rs as $user)
	{
		
			echo html_writer::start_tag('div', array('class'=>'sectionmessage'));
            echo html_writer::start_tag('h4');
            echo $course->shortname ." ". get_string('centeadminare');
            echo html_writer::start_tag('h4');	
            
            	echo html_writer::start_tag('div', array('class'=>'messagebox'));
	            		echo html_writer::start_tag('div', array('class'=>'topbx'));
	            			echo html_writer::start_tag('div', array('class'=>'topbx'));
	            				echo html_writer::start_tag('div', array('class'=>'imageborder'));
	                				print_user_picture($user->userid, $course->id, 1, 80, false, false);
	                			echo html_writer::end_tag('div');
								echo html_writer::start_tag('div', array('class'=>'topbx-ri'));
	                				echo fullname($user,true);
	                			echo html_writer::end_tag('div');
	                		echo html_writer::end_tag('div');		
	                	echo html_writer::end_tag('div');
	                	echo html_writer::start_tag('div', array('colspan'=>'2','class'=>'blline','align'=>'center'));
	                	echo "<a href='".$CFG->wwwroot."/message/index.php?id=".$user->userid."'><input name='' type='button' class='sendmessagebtn' /></a>";
	                	echo html_writer::end_tag('div');
	                echo html_writer::end_tag('div');
	     echo html_writer::end_tag('div');
	}
	
	
}

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
		//return;
	}
	if(<?php echo $sectionid;?> > 0) {
	var surl = '<?php echo $CFG->wwwroot?>/my/ajaxoperation.php?action=coursedetails&courseid='+courseid+'&sectionid=<?php echo $sectionid;?>&time=';
	}
	else {
		var surl = '<?php echo $CFG->wwwroot?>/my/ajaxoperation.php?action=coursedetails&courseid='+courseid+'&time=';
	}
	
	var coursedetails = YAHOO.util.Dom.get('mycustomscroll3');
	coursedetails.innerHTML ='loading.....';
	
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
		            	 numVisible: [11,1],
		            	 scrollIncrement:1,
		            	 animation: { speed: 0.5 },
		 	           	navigation:{prev:"prev_course",next:"next_course"},
		 				carouselEl: "UL" , 	
		 				isVertical: false
					}); 
					 
					 	
					 carouselcoursedetails.on("navigationStateChange",function(){
						 
						 if(carouselcoursedetails._nextEnabled)
							{
								document.getElementById("next_course").style.display='block';
								document.getElementById("next_course").disabled = false;
							}
							else
							{
								document.getElementById("next_course").style.display='none';
								document.getElementById("next_course").disabled = true;
							}
							if(carouselcoursedetails._prevEnabled)
							{
								document.getElementById("prev_course").style.display='block';
								document.getElementById("prev_course").disabled = false;
							}
							else
							{
								document.getElementById("prev_course").style.display='none';
								document.getElementById("prev_course").disabled = true;
							}
						});


					 carouselcoursedetails.set("selectedItem", -1);
					 arr_c_item = carouselcoursedetails.getItems();		 
					// carouselcoursedetails.selectedItem='section_2';	
					 carouselcoursedetails.render();// get ready for rendering the widget
					 carouselcoursedetails.show();
					 var  carouselcourseclassname =''    
					 for (i=0;i<arr_c_item.length;i++)
					 {
						carouselcourseclassname =  arr_c_item[i].className;
						 n=carouselcourseclassname.search("yui-carousel-item-selected"); 
						 if(n> 0)
						 {
							 carouselcoursedetails.set("selectedItem", i);
						 }
					 }
	    
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

function getsectionload(sectionid,courseid,sel_def)
{
	var assessmentdetails = YAHOO.util.Dom.get('rgscroll');
	//assessmentdetails.innerHTML ='loading.....';
	//var videodetails = YAHOO.util.Dom.get('video');
//	videodetails.innerHTML ='loading.....';
//	alert(YAHOO.util.Dom.get('sectionlessonid'));
	document.getElementById('laod_assigment').innerHTML="<h5><?php echo get_string('studenthomepagemsg')?> &#150;<span id='disp_date'></span></h5>";
	var surl = '<?php echo $CFG->wwwroot?>/my/ajaxoperation.php?action=sectiondetails&courseid='+courseid+'&sectionid='+sectionid+'&sel_def='+sel_def+'&time=';
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
					
					if(messages.jsscript != undefined &&  messages.jsscript != null && messages.jsscript !='')
					{
						eval(messages.jsscript);
					}
					if(messages.jsscript != undefined &&  messages.jsscript != null )
					{
						//assessmentdetails.innerHTML = eval(messages.jsscript);
						if(messages.jsscript !='')
						{
						 var carouselvideo    = new YAHOO.widget.Carousel("sectionlessonid", {
							 numVisible: [3,1],
							animation: { speed: 0.5 },
							scrollIncrement: 1,
						    navigation:{prev:"prev_v",next:"next_v"},
							carouselEl: "UL" , 	
							isVertical: false
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
								document.getElementById("next_v").disabled = false;
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

function act_sel(act)
{
	
	/*for(i=1;i<document.getElementsByTagName('a').length; i++)
	{
		var str=document.getElementsByTagName('a')[i].className;
		var n =str.search('act_sele');
		if(eval(n) > 1)
		{
			document.getElementsByTagName('a')[i].style.border='0px';
			document.getElementsByTagName('a')[i].style.margin='2px';
		}	
	}
	document.getElementById('sel_frm_'+act).style.border='';
	document.getElementById('sel_frm_'+act).style.margin='0px';
	document.getElementById('sel_frm_'+act).className += ' act_sele';
	return true;*/
}

function removeSectClass(sectionid)
{
/*	
	for(i=1;i<document.getElementsByTagName('a').length; i++)
	{
		var str=document.getElementsByTagName('a')[i].className;
		var n =str.search('sel');
		if(eval(n) > 1)
		{
			document.getElementsByTagName('a')[i].style.border='0px';
			document.getElementsByTagName('a')[i].style.margin='2px';
			
		}
	}
	document.getElementById('sel_'+sectionid).style.border='';
	document.getElementById('sel_'+sectionid).style.margin='0px';
	document.getElementById('sel_'+sectionid).className += ' sel';
	*/
}
function load_resou(val,val2,val3,val4)
{
	document.getElementById('laod_assigment').innerHTML='';
	var surl = unescape(val3);
	act_sel(val4);
	document.getElementById('submit_button_act').style.display="";
	if(document.getElementById('submit_ifrmae'))
	{
		//document.getElementById('submit_ifrmae').setAttribute("onclick","load_iframe('"+surl+"');");
		document.getElementById('submit_ifrmae').onclick= function() { load_iframe(surl,''); };
	}

}

function load_assessment(val)
{
	document.getElementById('laod_assigment').innerHTML='';
	var surl = '<?php echo $CFG->wwwroot;?>/mod/assignment/view.php?id='+val;
	document.getElementById('submit_button_act').style.display="";
	if(document.getElementById('submit_ifrmae'))
	{
		//document.getElementById('submit_ifrmae').setAttribute("onclick","load_iframe('"+surl+"');");
		document.getElementById('submit_ifrmae').onclick= function() { load_iframe(surl,''); };
	}
}

function load_quiz(val,val2)
{
	document.getElementById('laod_assigment').innerHTML='';
	var surl = '<?php echo $CFG->wwwroot;?>/mod/quiz/startattempt.php?id='+val+'&cmid='+val2+'&sesskey=<?php echo sesskey();?>';
	document.getElementById('submit_button_act').style.display="";
	if(document.getElementById('submit_ifrmae'))
	{
		//document.getElementById('submit_ifrmae').setAttribute("onclick","load_iframe('"+surl+"');");
		document.getElementById('submit_ifrmae').onclick= function() { load_iframe(surl,''); };
	}
	//document.getElementById('laod_assigment').innerHTML = '<iframe src="'+surl+'" width="912px" height="525px" frameborder="no"></iframe>';
}

function load_les(val,val2)
{
	document.getElementById('laod_assigment').innerHTML='';
	var surl = '<?php echo $CFG->wwwroot;?>/mod/lesson/view.php?id='+val+'&cmid='+val2+'&sesskey=<?php echo sesskey();?>';
	document.getElementById('submit_button_act').style.display="";
	if(document.getElementById('submit_ifrmae'))
	{
		//document.getElementById('submit_ifrmae').setAttribute("onclick","load_iframe('"+surl+"');");
		document.getElementById('submit_ifrmae').onclick= function() { load_iframe(surl,''); };
	}
	//document.getElementById('laod_assigment').innerHTML = '<iframe src="'+surl+'" width="912px" height="525px" frameborder="no"></iframe>';
}
function load_video(val,val2)
{
	document.getElementById('laod_assigment').innerHTML='';
	var surl = '<?php echo $CFG->wwwroot;?>/mod/resource/view.php?id='+val;
	
	document.getElementById('submit_button_act').style.display="";
	if(document.getElementById('submit_ifrmae'))
	{
		//document.getElementById('submit_ifrmae').setAttribute("onclick","load_iframe('"+surl+"');");
		document.getElementById('submit_ifrmae').onclick= function() { load_iframe(surl,''); };
	}
}
function load_imscp(val3,val4)
{
	document.getElementById('laod_assigment').innerHTML='';
	var surl =unescape(val3);
	act_sel(val4);
	document.getElementById('submit_button_act').style.display="";
	if(document.getElementById('submit_ifrmae'))
	{
		//document.getElementById('submit_ifrmae').setAttribute("onclick","load_iframe('"+surl+"');");
		document.getElementById('submit_ifrmae').onclick= function() { load_iframe(surl,'imscp'); };
	}
}

function load_url(val,val2,val3)
{
	document.getElementById('laod_assigment').innerHTML='';
	var surl = '<?php echo $CFG->wwwroot;?>/mod/url/view.php?id='+ val;
	var n = surl.match(/pdf/g);
	if(n!=null )
	{
		var surl = unescape(val2);
	}
	//var surl = '<?php echo $CFG->wwwroot;?>/mod/url/view.php?id='+ val;
	act_sel(val3);
	document.getElementById('submit_button_act').style.display="";
	if(document.getElementById('submit_ifrmae'))
	{
	//document.getElementById('submit_ifrmae').setAttribute("onclick","load_iframe('"+surl+"');"); 
		document.getElementById('submit_ifrmae').onclick= function() { load_iframe(surl,''); };
	}
	
}

function load_iframe(surl,val)
{
	document.getElementById('laod_assigment').innerHTML='';
	document.getElementById('submit_button_act').style.display="none";
	if(val =='imscp')
	{
	document.getElementById('laod_assigment').style.height="729px";
	document.getElementById('laod_assigment').innerHTML = '<iframe width="912px" height="725px" style="z-index:2;" frameborder="0" src="'+surl+'" id="iframe2"><span id="disp_date" style="visibility:hidden;"></span></iframe>';
	}
	else
	{
	document.getElementById('laod_assigment').style.height="512px";
	document.getElementById('laod_assigment').innerHTML = '<iframe width="912px" height="512px" style="z-index:2;" frameborder="0" src="'+surl+'" id="iframe2"><span id="disp_date" style="visibility:hidden;"></span></iframe>';
	}
}
<?php
if($firstcourseid != '') {
	echo "getlessonload(".$firstcourseid.");";
}
?>
</script>