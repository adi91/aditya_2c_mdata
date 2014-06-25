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

    require_once('config.php');
    require_once($CFG->dirroot .'/course/lib.php');
	require_once($CFG->dirroot .'/group/lib.php');
    require_once($CFG->libdir .'/filelib.php');
	require_once($CFG->libdir .'/completionlib.php');
	require_once $CFG->libdir.'/completion/completion_completion.php';

    redirect_if_major_upgrade_required();

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
    $PAGE->set_pagelayout('frontpage');
    $editing = $PAGE->user_is_editing();
    $PAGE->set_title($SITE->fullname);
    $PAGE->set_heading($SITE->fullname);
    echo $OUTPUT->header();	
?>
<?php
if(is_parent()){
global $USER,$CFG,$DB;

//schedule_lessons($courseid);
$query1 = "SELECT user.id as userid FROM `mdl_user` as user left join `mdl_user` as parent on user.username = parent.parentlist WHERE user.parentlist like  '".$USER->username."'";
$params = array();
$students = $DB->get_records_sql($query1,$params);
if($students){
	foreach($students as $student){
		$studentid = '';
		$studentid = $studentid ? $studentid : $student->userid;       // Owner of the page
		$thisstudent = $DB->get_record('user', array('id' => $studentid));
		if ($thisstudent->deleted) {
			    $PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
			    echo $OUTPUT->header();
			    echo $OUTPUT->heading(get_string('userdeleted'));
			    echo $OUTPUT->footer();
			    die;
		}
		//$currentuser = ($user->id == $thisstudent->id);
		$context = $usercontext = get_context_instance(CONTEXT_USER, $studentid, MUST_EXIST);
		
		
		/* to fetch this students course enrolments */
		$course_count = 0;
		if (!isset($hiddenfields['mycourses'])) {
			//if ($mycourses = enrol_get_all_users_courses($studentid, true, NULL, 'visible DESC,sortorder ASC')) {
			if ($mycourses = apt_enrol_get_users_courses($studentid, true, NULL, 'visible DESC,sortorder ASC')) {
			    $course_count = count($mycourses);			
			}
		}
		?>
	    
	    <div class="achivements" style="float: left;width: 100%;">
	    <h5 style="padding:6px 0;"><?php print_r($thisstudent->firstname.' '.$thisstudent->lastname); ?></h5>
	    	<div class="profilephoto">
	    		<!--<img src="images/profiles/pr-image1.jpg" alt="" width="82" height="81" />-->
				<?php echo $OUTPUT->user_picture($thisstudent, array('size'=>82)); ?>
	        </div>
	        <div class="sectionCA">
	        	<div class="courseL">
		            <h3>Course</h3>
		            <!--<p>First enrolement: 13 Oct. 2011</p>-->
		            <p><?php echo $course_count; ?> Course<?php if($course_count >1)echo 's'; ?></p>
		            <!--<P>Average Score:87%</P>-->
	            </div>
	            <div class="achiveR">
		            <!--<h3>Achivements</h3>
		            <p>13 star</p>
		            <p class="star-rate" style="width:168px;"></p>-->
	            </div>
	        </div>				
	    </div>
		<div class="sectionlesson">
		<?php
			$startday=strtotime('0 day', strtotime(date('Y-m-d')));
			$endday=$startday + (3599*24);
			foreach($mycourses as $mycourse){
				$modules = $DB->get_records('course_modules',array('course'=>$mycourse->id));
				$groups = groups_get_groupby_role($studentid,$mycourse->id);
				$groupid = $groups->id;
				$class_activity_course = $DB->get_record('class_activity',array('course'=>$mycourse->id,'section'=>NULL,'groupid'=>$groupid));
				$course_start_date = $class_activity_course->availablefrom;
				$sections = get_all_sections($mycourse->id);
				$lesson_due_today = false;
				foreach($sections as $secion){
					if($secion->sequence){
						$class_activity_lesson = $DB->get_record('class_activity',array('course'=>$mycourse->id,'section'=>$secion->id,'module'=>NULL,'groupid'=>$groupid));
						if($class_activity_lesson && $class_activity_lesson->availablefrom){
							$lesson_start_date = $class_activity_lesson->availablefrom;							
							$time_to_start_lesson = ($lesson_start_date-time());
							//echo timestamp_formatting($time_to_start_lesson).' i.e. will start at '.date('d M Y',$lesson_start_date).'<br>';
							
							//if($time_to_start_lesson <= (60*60*24) && $time_to_start_lesson >= -(60*60*24))
							if($class_activity_lesson->availablefrom > $startday  && $class_activity_lesson->availablefrom < $endday){
								$lesson_due_today = true;
							    $lesson = $DB->get_record('course_sections',array('id'=>$class_activity_lesson->section));
								if($lesson->name=="")
								{
									$less_name= "Lesson ".$lesson->section;
								}
								else
								{
									$less_name= $lesson->name;
								}
								
								//
								//echo $class_activity_lesson->section.'<br>';
								?>
								<div class="sectionlesson"> Lesson Due Today:
							        <div class="box1wf">
										<div class="co-number" style="background: url('pix/icon/crtoday-number.png') no-repeat scroll 0 0 transparent;"><span><?php echo $class_activity_lesson->sectionno; ?></span></div>
										<div class="co-details">
											<div class="task-le">
												<div class="table">
													<div style="display:block">
													  <h4 class="hd1"><?php echo $mycourse->fullname.' - '.$less_name; ?></h4>
													  <?php
														 $activities = get_array_of_activities($mycourse->id);
														 $taskcount = 0;
														 $lessonactivity =0;
														if($activities){
															foreach($activities as $activity){
																if($activity->sectionid == $class_activity_lesson->section){
																    $lessonactivity++;
																	if(@$completion = $DB->get_record('course_modules_completion',array('id'=>$activity->completion,'userid'=>$studentid))){
																		if(@$completion->completionstate == 1){															  
																		  $taskcount++;
																		}
																	}
																}
															}
															
															$taskcompletion = ($taskcount/$lessonactivity)*100;
														}else{
															$taskcompletion = 0;
														}
													  ?>
													 <span class="pr1g"><?php echo get_string('studcompletion');?></span>
													</div>
													<div class="pr1">
														<?php echo $lesson->summary; ?>
													</div>
													<div class="tableaside">													    
														<?php if($query_score_result){?><div class="calc"><?php echo intval($query_score_result->finalgrade).'%';?></div><?php } ?>
														<div class="yel-bar"><div class="gre-bar" style="width:<?php echo $taskcompletion; ?>%;"></div></div>
													</div>
												</div>
											</div>
											<div class="taskcomplete-le">
												<div class="pr1gl">
													<span><?php echo date('d M Y D H:i ',$lesson_start_date);?> </span>
		                                            <?php 
													
													if($activities){
													?>
													<div class="right">
													<?php
													
													?>
													</div>
													<div class="right">
														<span class="right"><?php echo $taskcount; ?> Tasks COMPLETED</span></br>											
													<?php
													$taskcount = 0;
													foreach($activities as $activity){
															if($activity->sectionid == $class_activity_lesson->section){
																$completion = @$DB->get_record('course_modules_completion',array('id'=>$activity->completion,'userid'=>$studentid));
																if(@$completion->completionstate == 1){
																  echo '<span class="edu-icon_01"></span>';
																  $taskcount++;
																}else{
																  echo '<span class="edu-icon_02"></span>';
																}
															}
														
													}
													?>
													</div>	
		                                         <?php } ?>											
												</div>
											</div>		                
										</div>
									</div>
						        </div>
								
								
								<?php
							}
						}
					}
				}
				if(!$lesson_due_today){
				
				   echo $mycourse->fullname.' - No lesson due today .<br>';
				}
				
				foreach($modules as $module){
					/*echo $module->course.'<br>';
					echo $module->module.'<br>';
					echo $module->section.'<br>';
					*/
					$class_activity_course = $DB->get_record('class_activity',array('course'=>$mycourse->id,'section'=>NULL,'groupid'=>$groupid));
					$course_start_date = $class_activity_course->availablefrom;
					$class_activity_section = $DB->get_record('class_activity',array('course'=>$mycourse->id,'section'=>$module->section,'groupid'=>$groupid));
					$section_start_date = $class_activity_section->availablefrom;
					//print_r(date('d M Y',$class_activity_section->availablefrom).'<br>');
				}
			}
		?>
		</div>
		<div class="sectionmessage">
	    	<h4><?php print_r($thisstudent->firstname); ?>'s teachers are</h4>
			<?php
				if (!isset($hiddenfields['mycourses'])) {
					//if ($mycourses = enrol_get_all_users_courses($studentid, true, NULL, 'visible DESC,sortorder ASC')) {
					if ($mycourses = apt_enrol_get_users_courses($studentid, true, NULL, 'visible DESC,sortorder ASC')) {
					    $shown=0;
						$courselisting = '';
						foreach ($mycourses as $mycourse) {					        
							if ($mycourse->category) {						   
								$class = '';
								$courseid = $mycourse->id;
								//$courselisting = "<a href=\"{$CFG->wwwroot}/user/view.php?id={$studentid}&amp;course={$mycourse->id}\" $class >" .format_string($mycourse->fullname) . "</a>, ";
								$contextid = get_context_instance(CONTEXT_COURSE, $courseid);
								$enrolled_users = $DB->get_records('role_assignments',array('contextid'=>$contextid->id));
								echo '<div style="color: #444442;font-size: 13px;font-weight: normal;padding:6px;border:0px solid;float:left;width:100%;">'.$mycourse->fullname.'\'s teachers :</div>';
								foreach($enrolled_users as $user){
                                     $student_classids = $DB->get_records('groups_members',array('userid'=>$studentid));
									 $showteacher = false;
                                     foreach($student_classids as $student_class){
									 
										$teacher_classids = $DB->get_records('groups_members',array('userid'=>$user->userid,'groupid'=>$student_class->groupid));
										if($teacher_classids){
										  $showteacher = true;
										}
									 }
									if(($user->roleid ==3 || $user->roleid == 4) && $showteacher == true)
									{
									   $teacher = $DB->get_record('user',array('id'=>$user->userid));
									   //echo $teacher->username;
									   ?>
										<!-- teacher display box start-->
										<div class="messagebox">
											<div class="topbx">
											    <div class="topbx">
														<div class="imageborder">
															<?php echo $OUTPUT->user_picture($teacher, array('size'=>82)); ?>
														</div>
									                    <div class="topbx-ri">
														    <h5><?php echo $teacher->firstname.' '.$teacher->lastname;?></h5>
									                    	<!--<p class="pr1">Nationality: British<br />
															DOB:  26 December 1971<br />
									                        TCL:  3.5</p>-->
									                    </div>
												</div>
												<div colspan="2" class="blline" align="center"> 
													<a href="<?php echo $CFG->wwwroot;?>/message/index.php?id=<?php echo $teacher->id;?>"><input name="" type="button" class="sendmessagebtn" /></a>
												</div>
											</div>
										</div>
										<!-- teacher display box ends-->
										<?php
									}								
								}
								
								
							}						
						}
						//print_row(get_string('courseprofiles').':', rtrim($courselisting,', '));
						
					}
				}
			?>
	        
	    </div>    
	    	
	<?php
		
	}
}

}else{
  redirect($CFG->wwwroot);
}
?>   
	
<?php echo $OUTPUT->footer(); ?>
