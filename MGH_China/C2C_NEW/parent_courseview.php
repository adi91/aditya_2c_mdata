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
?>
<?php
if(is_parent()){
global $USER,$CFG,$DB;
$query1 = "SELECT user.id as userid FROM `mdl_user` as user left join `mdl_user` as parent on user.username = parent.parentlist WHERE user.parentlist like  '".$USER->username."'";
$params = array();
$students = $DB->get_records_sql($query1,$params);
$startday=strtotime('0 day', strtotime(date('Y-m-d')));
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
		$context = $usercontext = get_context_instance(CONTEXT_USER, $studentid, MUST_EXIST);		
		/* to fetch this students course enrolments */	
        $course_count =0;		
		if (!isset($hiddenfields['mycourses'])) {
			//if ($mycourses = enrol_get_all_users_courses($studentid, true, NULL, 'visible DESC,sortorder ASC')) {
			if ($mycourses = apt_enrol_get_users_courses($studentid, true, NULL, 'visible DESC,sortorder ASC')) {
			    $course_count = count($mycourses);			
			}
		}
		?>
		<div style="width:100%; float:left;">
			<h5 style="padding:6px 0;"><?php echo $thisstudent->firstname.' '.$thisstudent->lastname; ?></h5>
		    <div class="achivements">
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
		
		<?php
		if (!isset($hiddenfields['mycourses'])) {
			//if ($mycourses = enrol_get_all_users_courses($studentid, true, NULL, 'visible DESC,sortorder ASC')) {
			if ($mycourses = apt_enrol_get_users_courses($studentid, true, NULL, 'visible DESC,sortorder ASC')) {
			    $c = 1;
			    foreach($mycourses as $mycourse){
					$summary = $DB->get_record('course', array('id'=>$mycourse->id));
					$group = groups_get_groupby_role($studentid,$mycourse->id);
					$per = get_course_completion($mycourse->id,$group->id);
					
					$activities = get_array_of_activities($mycourse->id);
					$group_act=groups_get_groupby_role($userid,$courseid);
					$get_act_record_count=get_group_act_no($group->id,$mycourse->id);
					$taskcount = 0;
					if($activities){
						foreach($activities as $activity){
							if($activity->section >0 && $activity->visible==true) {
								$sction_act_com= get_group_les_activity($group->id,$activity->sectionid,$mycourse->id);
								if($sction_act_com->availablefrom < $startday && $get_act_record_count >1)
								{
									$taskcount++;
								}
							}
						}
					}
					//$per_comp= intval($taskcount / count($activities)*100);
					?>
						<div class="sectionlesson"> Course :
					        <div class="box1wf">
					        <a href="<?php echo $CFG->wwwroot;?>/course/courselesson.php?id=<?php echo $mycourse->id;?>&stduser=<?php echo $studentid;?>">
								<div class="co-number" style="background: url('pix/icon/crincomplete-number.png') no-repeat scroll 0 0 transparent;"><span><?php echo $c; ?></span></div>
								<div class="co-details">
									<div class="task-le">
										<div class="table">
											<div style="display:block">
											  <h4 class="hd1"><?php echo $mycourse->fullname; ?></h4>
											  <?php
												/*  $query_score = 'SELECT * FROM {grade_grades} AS grades LEFT JOIN {grade_items} AS grade_item ON grades.itemid = grade_item.id WHERE grades.userid = '.$studentid.' AND grade_item.itemtype = "course" and grade_item.courseid = '.$mycourse->id;
												  $query_score_result = $DB->get_record_sql($query_score,array());*/												  
												?>
											  <span class="pr1g"><?php echo get_string('studcompletion');?></span>
											</div>
											<div class="pr1">
												<?php print_r($summary->summary); ?>
											</div>
											<div class="tableaside">
											    
												<div class="calc"><?php echo $per.'%';?></div>
												<div class="yel-bar"><div class="gre-bar" style="width:<?php if($per){echo $per.'%';}else{echo '0%';} ?>;"></div></div>
											</div>
										</div>
									</div>
									<div class="taskcomplete-le">
										<div class="pr1gl">
											<span><?php 
											/*$sql = 'SELECT groups.id as groupid FROM {groups} as groups left join {groups_members} as members on groups.id = members.groupid where members.userid = '.$studentid;
											$student_group = $DB->get_record_sql($sql,array());											
											$course_start = $DB->get_record('class_activity',array('course'=>$mycourse->id,'section'=>NULL,'groupid'=>$student_group->groupid));
											echo date('d M Y h:m:s',$course_start->availablefrom);*/
											?></span>
                                            <?php 
											if($activities){
											?>
											<div class="right">
											</div>
											<div class="right">
												<span class="right"><?php echo $taskcount; ?> Tasks COMPLETED</span></br>											
											<?php
											
											foreach($activities as $activity){
												if($activity->section >0){
													$sction_act_com= get_group_les_activity($group->id,$activity->sectionid,$mycourse->id);
													
													if($sction_act_com->availablefrom < $startday && $get_act_record_count >1)
													{
														 echo '<span class="edu-icon_01"></span>';
													}
													else
													{
														echo '<span class="edu-icon_02"></span>';
													}	
												}
											}
											?>
											</div>	
                                         <?php } ?>											
										</div>
										<?php 
			     						$get_act_record_count=get_group_act_no($group->id,$mycourse->id); 
		    							if($get_act_record_count<=1)
										{
											echo "<h4>".get_string('class_activity_not_preseent')."</h4>";
										}
										?>
									</div>		                
								</div>
							</div>
							</a>
				        </div>
				        
					<?php
					$c++;
                }				
			}
		}
		echo '</div>';
		?>
				
<?php
    }
}
}else{
  redirect($CFG->wwwroot);
}
?>   
	
<?php echo $OUTPUT->footer(); ?>
