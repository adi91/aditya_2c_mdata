<?php

//  Display the course home page.

require_once('../config.php');
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
	
    $id  = optional_param('id', 0, PARAM_INT);
    $topic  = optional_param('topic', '-1', PARAM_INT);
  
    if (! ($course = $DB->get_record('course', array('id'=>$id)))) {
            print_error('invalidcourseid', 'error');
     }
   
    preload_course_contexts($course->id);
    if (!$context = get_context_instance(CONTEXT_COURSE, $course->id)) {
        print_error('nocontext');
    }

    require_login($course);
    // Switchrole - sanity check in cost-order...
    $reset_user_allowed_editing = false;
    $course->format = clean_param($course->format, PARAM_ALPHA);
   
    $PAGE->set_pagelayout('course');
    $PAGE->set_pagetype('course-view-' . $course->format);

    $PAGE->set_title(get_string('course') . ': ' . $course->fullname);
    $PAGE->set_heading($course->fullname);
  	echo $OUTPUT->header();
  	echo "<link href='".$CFG->wwwroot."/my/css/style.css' rel='stylesheet' type='text/css'>";
    // Course wrapper start.
    echo html_writer::start_tag('div', array('class'=>'course-content'));

    $modinfo =& get_fast_modinfo($COURSE);
    get_all_mods($course->id, $mods, $modnames, $modnamesplural, $modnamesused);
   
    require_once($CFG->libdir.'/filelib.php');
    $topic = optional_param('topic', -1, PARAM_INT); 
	$context = get_context_instance(CONTEXT_COURSE, $course->id);
	$sections = get_all_sections($course->id);
    echo "<div class='info-board-box'>";
	echo "<div class='full-w-course'><h2>Course > ".$course->fullname."</h2></div>";
	echo "<div class='full-w-course'><h4>Name:</h4><h3 class='case'>".$course->fullname."</h3>";
	

	if(count($mods) > 0)
   {	
   		//print_r($mods);
	   echo "<ul>";
	   while(list($key,$modid) = each($mods))
	   {   
			/*if($topic > 0)
			{
				if($modid->sectionnum!=$topic)
	   			{
	   				
	   				continue;
	   			}
			}*/
	   				foreach ($sections as $key_section => $section) 
								{
									$sectionname = get_section_name($course,$section);
									if (!array_key_exists($section->section, $modinfo->sections)) 
									{
										continue;
									}
									
									if($modid->sectionnum == $key_section)
									{
									 if($sectionname=="Lesson" || $sectionname=="Assessment")
									 {
									 	if($modid->id==$topic){
										echo "<li><h3 class='case2'>".$modid->name."</h3><div style='float:left;'>";
						   				if(strtolower($modid->modname) == 'scorm')
										{
											$cm = $DB->get_record("course_modules", array("id"=>$modid->id));
											$scorm = $DB->get_record("scorm", array("id"=>$modid->instance));
							             	$scoes = $DB->get_record("scorm_scoes", array("scorm"=>$cm->instance,'scormtype'=>'sco'));
											if (($scoes->organization == '') && ($scoes->launch == '')) 
											{
												$orgidentifier = $scoes->identifier;
											} 
											else 
											{
												$orgidentifier = $scoes->organization;
											}
												
								             	echo "<form name='frmPost_".$modid->id."' id='frmPost_".$modid->id."' action='".$CFG->wwwroot."/mod/".$modid->modname."/player.php' method='post'> ";
								             	echo "	<input type='hidden' name='mode' value='normal' />
														<input type='hidden' name='scoid'  value=''/>
														<input type='hidden' name='cm' value='".$cm->id."'/>
														<input type='hidden' name='currentorg' value='".$orgidentifier."' />";
								             	
											
										}
									 	
										echo "</div><div style='float: right;margin-top: -23px;position: relative;'><input type='image' src='images/launch.png'  value='Launch'></div></form></li>";
									 	}
									}
								}
							}
		echo "</ul>";
   }
   }
	echo "</div></div></div>";
	
	

  echo html_writer::end_tag('div');

   echo $OUTPUT->footer();
