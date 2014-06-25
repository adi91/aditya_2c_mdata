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
    $startday=strtotime('0 day', strtotime(date('Y-m-d')));
?>
<?php
//if(is_centeradmin()){
global $USER,$CFG,$DB;

?>
						<!-- teacher display box start-->
						<?php if($CFG->theme != 'mghc2c'){ ?>
						<div class="messagebox">
							<div class="topbx">
								<div class="topbx">
										<div class="imageborder">
											<?php echo $OUTPUT->user_picture($USER, array('size'=>82)); ?>
										</div>
										<div class="topbx-ri">
											<h5><?php echo $USER->firstname.' '.$USER->lastname;?></h5>
											<span ><a href="<?php echo $CFG->wwwroot.'/user/editadvanced.php?id='.$USER->id; ?>">Edit</a></span>
										</div>
								</div>								
							</div>
						</div>
						<?php } ?>
						<?php $categories = get_categories(); ?>
						<?php foreach($categories as $category){ ?>
							<?php $category_context =  context_coursecat::instance($category->id);
							if(is_centeradmin()){
								$roleid = 10;
							}elseif(is_institute_cordinator()){
								$roleid = 13;
							}
							$check_role_assignment = $DB->get_record('role_assignments',array('roleid'=>$roleid,'contextid'=>$category_context->id,'userid'=>$USER->id));
							if($check_role_assignment){	 ?>
								<?php 
									$calendardate = strtotime($_GET['cal_d'].'-'.$_GET['cal_m'].'-'.$_GET['cal_y'].' 00:00:00');
									$calendarenddate = strtotime($_GET['cal_d'].'-'.$_GET['cal_m'].'-'.$_GET['cal_y'].' 23:59:59');
									if($calendardate !=$startday )
									{
										$calendardate = $calendardate;
									}
									else
									{
										$calendardate = $startday;
									}
									
	                                $courses = get_courses($category->id, 'c.sortorder ASC', 'c.id,c.sortorder,c.visible,c.fullname,c.shortname,c.summary');
									foreach($courses as $course){
										$groups = groups_get_all_groups($course->id);
										if($groups){
											foreach($groups as $group){
												
												$group_members = groups_get_members($group->id);
												/* Changes magde by swapnil 24 Aug 2012 */
												$sql ="SELECT * FROM {class_activity} WHERE groupid=? AND course=? AND availablefrom>=? AND availablefrom <=? GROUP BY sectionno";
												$params= array($group->id,$course->id,$calendardate,$calendarenddate); 
												$class_events = $DB->get_records_sql($sql,$params);
												//print_r($class_events);
												//('class_activity',array('groupid'=>$group->id,'course'=>$course->id));
												foreach($class_events as $class_event){
												$res2=get_course_lesson_active($group->id,$class_event->availablefrom);
												if($CFG->theme == 'mghc2c'){
													$res2 = date("h:s a",$class_event->availablefrom);
												}
												if(!empty($class_event->sectionno) || !is_null($class_event->sectionno)) {
													//$course = $DB->get_record('course',array('id'=>$class->courseid));
													$coursecontext=get_context_instance(CONTEXT_COURSE, $course->id);
													$classmembers = $DB->get_records('groups_members',array('groupid'=>$group->id));
													$count_student = 0;
													$count_teacher = 0;
													/*foreach($classmembers as $classmember){
														$teacher = $DB->get_records('role_assignments', array('userid'=>$classmember->userid,'contextid'=>$coursecontext->id,'roleid'=>'3'));
														$student = $DB->get_records('role_assignments', array('userid'=>$classmember->userid,'contextid'=>$coursecontext->id,'roleid'=>'5'));
														if($student)
															$count_student++;
														if($teacher)
															$count_teacher++;
													}*/
													$sql ="SELECT roleid,COUNT(id) as cnt FROM mdl_role_assignments 
														WHERE roleid IN (3,5) AND contextid =? and userid in (select userid from {groups_members} where groupid =? ) 
														GROUP BY roleid";
													$role_ids_cnt = $DB->get_records_sql($sql,array($coursecontext->id,$group->id));
													
													if(array_key_exists(3,$role_ids_cnt))
														{
														$count_teacher = $role_ids_cnt[3]->cnt;
														}
													if(array_key_exists(5,$role_ids_cnt))
														{
														$count_student = $role_ids_cnt[5]->cnt;
														}
													$class_start = $DB->get_record('groups',array('id'=>$group->id));
													//echo $class_start->starttime;
													$eventbox = '<div class="classbox1wf">
															<div class="actbox-number-gr4">
															
																<h4 class="hd1">'.$res2.'</h4>
																<!--<a href="#" id="edit">edit</a>-->
															</div>
															<div class="le-details3">
																<div class="task-le">
																	<div class="table2">
																		<div style="display:block">
																			<h4 class="hd1">'.$group->name.'</h4>
																		</div>
																	</div>
																	<div class="pr3">
																		<p>'.$course->fullname.'<!-- - 11% Complete<p>
																		<div class="yel-bar6">
																			<div style="width:11%;" class="gre-bar"></div>
																		</div>
																		<p style="width:100%;">Class Average score 57%</p>-->
																		<p style="width:100%;">'.$count_student.' students and '.$count_teacher.' Teachers participating in this class<p>
																	</div>
																</div>
															</div>
														</div>';
													echo $eventbox;
												}
												
											}
											}
										}
									}
									?>
							<?php } ?>
						<?php } ?>
						</div>
<?php
//}else{
  //redirect($CFG->wwwroot);
//}
?>   
	
<?php echo $OUTPUT->footer(); ?>
