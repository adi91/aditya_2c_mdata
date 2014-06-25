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
if(is_centeradmin()){
    ?><div><a href="<?php echo $CFG->wwwroot.'/course/edit.php?category=1&returnto=topcat'; ?>" class="buttonstyled">Create Course</a></div><?php
	global $USER,$CFG,$DB;	
	$params = array();
	$sql = 'select * from {role_assignments} where roleid = '.CENTERADMIN_ROLEID.' and userid = '.$USER->id.' and contextid > 1';	
	$check_role_assignments = $DB->get_records_sql($sql);
	if($check_role_assignments){
		foreach($check_role_assignments as $check_role_assignment){		   
		   $context = context::instance_by_id($check_role_assignment->contextid);
		   $category = $DB->get_record('course_categories',array('id'=>$context->instanceid));
		   echo '<div style="clear:both;"><h3><u>Courses in '.$category->name.'</u></h3></div>';
		   $courses = get_courses($context->instanceid, $sort="c.sortorder ASC", $fields="c.*");
		   if($courses){
			$c = 0;
			foreach($courses as $course){
			$class_view="";
			 $class_view=get_centeradmin_class_view($course->id);
				?>
				<div class="sectionlesson">
				 
					<div class="box1wf">
						<div class="co-number" style="background: url('pix/icon/crcomplete-number.png') no-repeat scroll 0 0 transparent;"><span><?php echo ($c+1); ?></span></div>
						<div class="co-details">
							<div class="task-le">
								<div class="table">
									<div style="display:block"><h4 class="hd1"><a href="<?php echo $CFG->wwwroot.'/course/view.php?id='.$course->id; ?>"><?php echo $course->fullname; ?></a></h4></div>
									<div class="pr1"><?php print_r($course->summary); ?></div>
								</div>
							</div>
							<div class="taskcomplete-le">
								<div class="pr1gl">
									<span><?php echo date('d M Y',$course->startdate); ?></span>
									<div class="right"><span class="right">
									<a href = "<?php echo $CFG->wwwroot; ?>/course/edit.php?id=<?php echo $course->id; ?>" class="gray_edit">
									  <?php echo '<img src="'.$CFG->wwwroot.'/pix/t/m_edit.png" class="icon" alt="Edit" />'; ?>
									</a>
									<a href = "<?php echo $CFG->wwwroot; ?>/course/delete.php?id=<?php echo $course->id; ?>" class="gray_delete">
										<?php echo '<img src="'.$CFG->wwwroot.'/pix/t/m_delete.png" class="icon" alt="Delete" />'; ?> 
									</a> 
									<a href="<?php echo $CFG->wwwroot; ?>/enrol/users.php?id=<?php echo $course->id; ?>&page=0&perpage=100&sort=lastname&dir=ASC">
										Manage Teachers and Students
									</a> | 
									<a href="<?php echo $CFG->wwwroot; ?>/group/index.php?id=<?php echo $course->id; ?>">
										Manage Classes
									</a>
									</span></div>
								</div>
								
							</div>
							<?php if($class_view) {
							echo "<div class='pr1gl'>".$class_view." ".get_string('centeradminclassnotschedule')."</div>";
							 }?>		                
						</div>
					</div>
				</div>				
				<?php
				$coursecontext = context_course::instance($course->id);
				$enrolled_users_sql = 'select * from {user} where id in (select userid from {role_assignments} where contextid = '.$coursecontext->id.' and roleid IN ('.INSTRUCTOR_ROLEID.','.NON_EDITING_INSTRUCTOR_ROLEID.'))';
				//$enrolled_users = $DB->get_records('role_assignments',array('contextid'=>$coursecontext->id,'roleid'=>' IN (3,4) '));
				$enrolled_users = $DB->get_records_sql($enrolled_users_sql);
				if($enrolled_users){
					echo '<div style="color: #444442;font-size: 13px;font-weight: normal;padding:6px;border:0px solid;float:left;width:100%;">'.$course->fullname.'\'s teachers :</div>';
					foreach($enrolled_users as $enrolled_user){
						?>
						<!-- teacher display box start-->
						<div class="messagebox">
							<div class="topbx">
								<div class="topbx">
										<div class="imageborder">
											<?php echo $OUTPUT->user_picture($enrolled_user, array('size'=>82)); ?>
										</div>
										<div class="topbx-ri">
											<h5><?php echo $enrolled_user->firstname.' '.$enrolled_user->lastname;?></h5>
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
			  $c++;
			}
		   }else{
				echo "<div style='padding-top:10px;'><h5>";
				echo get_string('centeradminnocourse');
				echo "</h5></div>"; 
		   }
		}
	}
}elseif(is_siteadmin()){
	?><div><a href="<?php echo $CFG->wwwroot.'/course/edit.php?category=1&returnto=topcat'; ?>" class="buttonstyled">Create Course</a></div><?php
	global $USER,$CFG,$DB;	
	
		   $categories = $DB->get_records('course_categories',array());
		   foreach($categories as $category){
		   echo '<div style="clear:both;"><h3><u>Courses in '.$category->name.'</u></h3></div>';
		   $courses = get_courses($category->id, $sort="c.sortorder ASC", $fields="c.*");
		   if($courses){
			foreach($courses as $course){
			?>
				<div class="sectionlesson">
				 
					<div class="box1wf">
						<div class="co-number" style="background: url('pix/icon/crcomplete-number.png') no-repeat scroll 0 0 transparent;"><span><?php echo ($c+1); ?></span></div>
						<div class="co-details">
							<div class="task-le">
								<div class="table">
									<div style="display:block"><h4 class="hd1"><a href="<?php echo $CFG->wwwroot.'/course/view.php?id='.$course->id; ?>"><?php echo $course->fullname; ?></a></h4></div>
									<div class="pr1"><?php print_r($course->summary); ?></div>
								</div>
							</div>
							<div class="taskcomplete-le">
								<div class="pr1gl">
									<span><?php echo date('d M Y',$course->startdate); ?></span>
									<div class="right"><span class="right">
									<a href = "<?php echo $CFG->wwwroot; ?>/course/edit.php?id=<?php echo $course->id; ?>" class="gray_edit">
									  <?php echo '<img src="'.$CFG->wwwroot.'/pix/t/m_edit.png" class="icon" alt="Edit" />'; ?>
									</a>
									<a href = "<?php echo $CFG->wwwroot; ?>/course/delete.php?id=<?php echo $course->id; ?>" class="gray_delete">
										<?php echo '<img src="'.$CFG->wwwroot.'/pix/t/m_delete.png" class="icon" alt="Delete" />'; ?> 
									</a> 
									<a href="<?php echo $CFG->wwwroot; ?>/enrol/users.php?id=<?php echo $course->id; ?>&page=0&perpage=100&sort=lastname&dir=ASC">
										Manage Teachers and Students
									</a> | 
									<a href="<?php echo $CFG->wwwroot; ?>/group/index.php?id=<?php echo $course->id; ?>">
										Manage Classes
									</a>
									</span></div>
								</div>
								
							</div>
									                
						</div>
					</div>
				</div>				
				<?php
				$coursecontext = context_course::instance($course->id);
				$enrolled_users_sql = 'select * from {user} where id in (select userid from {role_assignments} where contextid = '.$coursecontext->id.' and roleid IN ('.INSTRUCTOR_ROLEID.','.NON_EDITING_INSTRUCTOR_ROLEID.'))';
				//$enrolled_users = $DB->get_records('role_assignments',array('contextid'=>$coursecontext->id,'roleid'=>' IN (3,4) '));
				$enrolled_users = $DB->get_records_sql($enrolled_users_sql);
				if($enrolled_users){
					echo '<div style="color: #444442;font-size: 13px;font-weight: normal;padding:6px;border:0px solid;float:left;width:100%;">'.$course->fullname.'\'s teachers :</div>';
					foreach($enrolled_users as $enrolled_user){
						?>
						<!-- teacher display box start-->
						<div class="messagebox">
							<div class="topbx">
								<div class="topbx">
										<div class="imageborder">
											<?php echo $OUTPUT->user_picture($enrolled_user, array('size'=>82)); ?>
										</div>
										<div class="topbx-ri">
											<h5><?php echo $enrolled_user->firstname.' '.$enrolled_user->lastname;?></h5>
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
		   }else{
				echo "<div style='padding-top:10px;'><h5>";
				echo get_string('centeradminnocourse');
				echo "</h5></div>"; 
		   }
		}
}else{
	redirect($CFG->wwwroot);
}
?>   	
<?php echo $OUTPUT->footer(); ?>
