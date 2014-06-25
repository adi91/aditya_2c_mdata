<?php
  if (!file_exists('./config.php')) {
        header('Location: install.php');
        die;
    }

    require_once('config.php');
    require_once($CFG->dirroot .'/course/lib.php');
    require_once($CFG->dirroot .'/group/lib.php');
    require_once($CFG->libdir .'/filelib.php');
   	$startday=strtotime('0 day', strtotime(date('Y-m-d')));
   	$endday= $startday + (3600 *24) -1;
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
if(!empty($courseid) && $courseid > 0)
{
	$courses = $DB->get_record('course',array('id'=>$courseid));
	$firstcourseid = $courseid;
}
else
{
    global $COURSE,$USER;
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
			 
			$sections = get_all_sections($c->id);
			//$section_arr = array();
			reset($sections);
			$cntLess=0;
			$section_less="";
			foreach ($sections as $section) 
			{
				if($section->section==0)
				{
					continue;
				}
				$sectionname = get_section_name($c,$section);
				if (!is_null($sectionname)) {
	                $section_less =$OUTPUT->heading(format_string($sectionname, true, array('context' => $context)), 3, 'sectionname');
	            }
	            else
	            {
	            	 $section_less ="Lesson ".$section->section;
	            }
			  $section_perc= get_topic_completion_percentage($c->id,$section->id,$USER->id);
	          $group_act=groups_get_groupby_role($USER->id,$c->id);
	          $sect_act= get_topic_completion_activity($c->id,$section->id,$USER->id);
	          $sec_act_group=get_group_activity($group_act->id,$section->section,$c->id);
				 if($sec_act_group->availablefrom > $startday && $sec_act_group->availablefrom < $endday)
				 {
		            	$act_class="le-number-bl";
		            
						echo "<div class='box1wf'>";
		            	echo "<a href='".$CFG->wwwroot."/index_student.php?courseid=".$course->id."&sectionid=".$section->section."'><div class=".$act_class.">";
		              		echo "<span>".$section->section."</span>";
		                echo "</div>";
		             	echo "<div class='le-details'>";
		                 echo "<div class='task-le'>";
		                	echo "<div class='table'>";
		                	if(is_student()){
		                		 echo "<div style='display:block'><h4 class='hd1'>".$c->shortname." ".$section_less."</h4>";
		                	}else
		                	{
			                     echo "<div style='display:block'><h4 class='hd1'>".$c->shortname." ".$section_less."</h4>";
		                	}
			                     echo "<span class='pr1g'>".get_string('studcompletion')."</span></div>";
			                      		echo "<div class='pr1'>".$summary."</div>";
			                       		 echo "<div class='tableaside'><div class='calc'>".$section_perc."</div><div class='yel-bar'><div class='gre-bar' style='width:".$section_perc."%;'></div></div></div>";
			                    echo "</div>";
							echo "</div>";
						
				                echo "<div class='taskcomplete-le'>";
					                echo "<div class='pr1gl'>";
					                  echo "<span>".date("d M y D H:i ",$sec_act_group->availablefrom)."</span>";
					                        echo "<div class='right'>";
					                        echo "<span class='right'>".$sect_act['compcount']." ".get_string('taskcomptleted')."</span><br/>";
					                      //  echo "<span class='right'><span class='edu-icon_01'></span><span class='edu-icon_01'></span><span class='edu-icon_03'></span><span class='edu-icon_02'></span><span class='edu-icon_02'></span><span class='edu-icon_02'></span></span></div>";
					                       echo "<span class='right'>".$sect_act['act']."</span></div>";
					                  echo "</div>";
									echo "</div>";
				                echo "</div>";
							echo "</a>";
		            echo "</div>";
		            $cntLess++;
				 }
			}
		
			if($cntLess < 1){
				 	echo "<div class='box1wf' style='cursor:none;'>";
				 		echo "<div class='le-details'>";
		                 echo "<div class='task-le'>";
		                	echo "<div class='table'>";
		                		echo "<div style='display:block'><h4 class='hd1'>".get_string('nocouserlessonfortheday')."(".date('d/m/Y').") ". get_string('forcourse')." ".$c->shortname."</h4> </div>";
		                	echo "</div>";
						echo "</div>";
				       echo "</div>";
				 	echo "</div>";
			}
				 
		
		}
	}
}

echo $OUTPUT->footer();