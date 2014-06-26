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
 * Allows you to edit a users profile
 *
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package user
 */

require_once('../config.php');
require_once($CFG->libdir.'/gdlib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/enrollib.php');
require_once($CFG->dirroot.'/user/editadvanced_form.php');
require_once($CFG->dirroot.'/user/editlib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/enrol/locallib.php');
require_once($CFG->dirroot.'/enrol/renderer.php');

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();

$id     = optional_param('id', $USER->id, PARAM_INT);    // user id; -1 if creating new user
$course = optional_param('course', SITEID, PARAM_INT);   // course id (defaults to Site)
if($id == -1 && is_institute_cordinator()){
		$redirect_string ='';
		$redirect_string .= $CFG->wwwroot;
		$redirect_string .= '/calendar/view.php?view=day';
		if($_GET['cal_d']){
			$redirect_string .= '&cal_d='.$_GET['cal_d'];
		}else{
			$redirect_string .= '&cal_d='.date('d');
		}
		if($_GET['cal_m']){
			$redirect_string .= '&cal_m='.$_GET['cal_m'];
		}else{
			$redirect_string .= '&cal_m='.date('m');
		}
		if($_GET['cal_y']){
			$redirect_string .= '&cal_y='.$_GET['cal_y'];
		}else{
			$redirect_string .= '&cal_y='.date('Y');
		}
		redirect($redirect_string);
}
$PAGE->set_url('/user/editadvanced.php', array('course'=>$course, 'id'=>$id));

$course = $DB->get_record('course', array('id'=>$course), '*', MUST_EXIST);

if (!empty($USER->newadminuser)) {
    $PAGE->set_course($SITE);
    $PAGE->set_pagelayout('maintenance');
} else {
    require_login($course);
    $PAGE->set_pagelayout('admin');
	$PAGE->set_pagelayout('frontpage');
}

if ($course->id == SITEID) {
    $coursecontext = get_context_instance(CONTEXT_SYSTEM);   // SYSTEM context
} else {
    $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);   // Course context
}
$systemcontext = get_context_instance(CONTEXT_SYSTEM);

if ($id == -1) {
    // creating new user
    $user = new stdClass();
    $user->id = -1;
    $user->auth = 'manual';
    $user->confirmed = 1;
    $user->deleted = 0;
    require_capability('moodle/user:create', $systemcontext);
    admin_externalpage_setup('addnewuser', '', array('id' => -1));
} else {
    // editing existing user
    require_capability('moodle/user:update', $systemcontext);
    $user = $DB->get_record('user', array('id'=>$id), '*', MUST_EXIST);
    $PAGE->set_context(get_context_instance(CONTEXT_USER, $user->id));
    if ($user->id == $USER->id) {
        if ($course->id != SITEID && $node = $PAGE->navigation->find($course->id, navigation_node::TYPE_COURSE)) {
            $node->make_active();
            $PAGE->navbar->includesettingsbase = true;
        }
    } else {
        $PAGE->navigation->extend_for_user($user);
    }
}

// remote users cannot be edited
if ($user->id != -1 and is_mnet_remote_user($user)) {
    redirect($CFG->wwwroot . "/user/view.php?id=$id&course={$course->id}");
}

if ($user->id != $USER->id and is_siteadmin($user) and !is_siteadmin($USER)) {  // Only admins may edit other admins
    print_error('useradmineditadmin');
}

if (isguestuser($user->id)) { // the real guest user can not be edited
    print_error('guestnoeditprofileother');
}

if ($user->deleted) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('userdeleted'));
    echo $OUTPUT->footer();
    die;
}

//load user preferences
useredit_load_preferences($user);

//Load custom profile fields data
profile_load_data($user);

//User interests
if (!empty($CFG->usetags)) {
    require_once($CFG->dirroot.'/tag/lib.php');
    $user->interests = tag_get_tags_array('user', $id);
}

if ($user->id !== -1) {
    $usercontext = get_context_instance(CONTEXT_USER, $user->id);
    $editoroptions = array(
        'maxfiles'   => EDITOR_UNLIMITED_FILES,
        'maxbytes'   => $CFG->maxbytes,
        'trusttext'  => false,
        'forcehttps' => false,
        'context'    => $usercontext
    );

    $user = file_prepare_standard_editor($user, 'description', $editoroptions, $usercontext, 'user', 'profile', 0);
} else {
    $usercontext = null;
    // This is a new user, we don't want to add files here
    $editoroptions = array(
        'maxfiles'=>0,
        'maxbytes'=>0,
        'trusttext'=>false,
        'forcehttps'=>false,
        'context' => $coursecontext
    );
}

//create form
$userform = new user_editadvanced_form(null, array('editoroptions'=>$editoroptions));

$userform->set_data($user);
$redirect = false;
if ($usernew = $userform->get_data()) {    
    $usernew->site_fk = implode("*",$usernew->site_fk1);	
    if (empty($usernew->auth)) {
        //user editing self
        $authplugin = get_auth_plugin($user->auth);
        unset($usernew->auth); //can not change/remove
    } else {
        $authplugin = get_auth_plugin($usernew->auth);
    }

    $usernew->timemodified = time();

    if ($usernew->id == -1) {
        //TODO check out if it makes sense to create account with this auth plugin and what to do with the password
        unset($usernew->id);
		if($usernew->site_fk){
			$usernew->institution = $usernew->site_fk;
		}
		//print_r($usernew->institution);
		//die();
        $usernew = file_postupdate_standard_editor($usernew, 'description', $editoroptions, null, 'user', 'profile', null);
        $usernew->mnethostid = $CFG->mnet_localhost_id; // always local user
        $usernew->confirmed  = 1;
        $usernew->timecreated = time();
        $usernew->password = hash_internal_user_password($usernew->newpassword);
        $usernew->id = $DB->insert_record('user', $usernew);
        $usercreated = true;
        add_to_log($course->id, 'user', 'add', "view.php?id=$usernew->id&course=$course->id", '');
		$redirect = true;

    } else {
	   
        $usernew = file_postupdate_standard_editor($usernew, 'description', $editoroptions, $usercontext, 'user', 'profile', 0);
		$existing_center = explode("*",$user->site_fk);
		$formdata_center = explode("*",$usernew->site_fk);
		$center_not_selected = array_diff($existing_center,$formdata_center);
		
		if($center_not_selected){	
			 $sites = implode(",",$center_not_selected);
			 $sql = "select * from {sites} where id in ($sites)";			 
			 $unassign_sites = $DB->get_records_sql($sql);			
			 $categoryid = array();
			 $site_name = '';
			 foreach($unassign_sites as $unassign_site){
				$categoryid[] = $unassign_site->site_centerid;
				$site_name .= '\n '.$unassign_site->site_name.' \n';			
			}
			//$centers = implode(",",$site_name);
			$courses = enrol_get_users_courses($user->id, true, NULL, 'visible DESC,sortorder ASC');
			if($courses){				
				foreach($courses as $course){
					if(in_array($course->category,$categoryid)){
						$unenrol_from_course[] = $course->id;
						$unenrol_from_course_name[] = $course->fullname;
					}
				}
				$course_name = implode(", ",$unenrol_from_course_name);
			}
			if($categoryid){				
				foreach($categoryid as $catid){
					$category_context =  context_coursecat::instance($catid);
					$check_role_assignments = $DB->get_records('role_assignments',array('roleid'=>10,'contextid'=>$category_context->id,'userid'=>$user->id));
					if($check_role_assignments){
						foreach($check_role_assignments as $check_role_assignment){
							$centeradmin_categories[] = $catid;
						}
					}
				}				
				if($centeradmin_categories){
					$centeradmin_sites_sql = "Select * from {sites} where site_centerid in (".implode(",",$centeradmin_categories).")";
					$centeradmin_sites = $DB->get_records_sql($centeradmin_sites_sql);
					foreach($centeradmin_sites as $centeradmin_site){
						$centeradmin_site_list[] = $centeradmin_site->site_name;
					}
					$centeradmin_site_list_to_be_removed = implode(",",$centeradmin_site_list);
					foreach($centeradmin_categories as $centeradmin_category){
						$category_context =  context_coursecat::instance($centeradmin_category);						
						$DB->delete_records('role_assignments', array('roleid'=>10,'contextid'=>$category_context->id,'userid'=>$user->id));
						$check_role_assignments = $DB->get_records('role_assignments',array('roleid'=>10,'userid'=>$user->id));
						if(count($check_role_assignments)==1){
							$DB->delete_records('role_assignments', array('roleid'=>10,'contextid'=>1,'userid'=>$user->id));
						}						
					}
					$redirect = true;
				}
			}		
		}		
		if($unenrol_from_course){
			foreach($unenrol_from_course as $courseid){
				$ues = $DB->get_records('user_enrolments', array('userid' => $user->id));
				//print_r($ues);
				foreach($ues as $ue){
					//print_r($ue->userid);
					//die();
					$user1 = $DB->get_record('user', array('id'=>$ue->userid), '*', MUST_EXIST);
					$instance = $DB->get_record('enrol', array('id'=>$ue->enrolid), '*', MUST_EXIST);
					$course = $DB->get_record('course', array('id'=>$instance->courseid), '*', MUST_EXIST);
					$plugin = enrol_get_plugin($instance->enrol);
					if($course->id == $courseid){
						$context = context_course::instance($course->id);
						$plugin->unenrol_user($instance, $ue->userid);
					}	
				}						
			}
			$redirect = true;
		}		
		
        $DB->update_record('user', $usernew);
        // pass a true $userold here
        if (! $authplugin->user_update($user, $userform->get_data())) {
            // auth update failed, rollback for moodle
            $DB->update_record('user', $user);
            print_error('cannotupdateuseronexauth', '', '', $user->auth);
        }
        add_to_log($course->id, 'user', 'update', "view.php?id=$user->id&course=$course->id", '');

        //set new password if specified
        if (!empty($usernew->newpassword)) {
            if ($authplugin->can_change_password()) {
                if (!$authplugin->user_update_password($usernew, $usernew->newpassword)){
                    print_error('cannotupdatepasswordonextauth', '', '', $usernew->auth);
                }
                unset_user_preference('create_password', $usernew); // prevent cron from generating the password
            }
        }

        // force logout if user just suspended
        if (isset($usernew->suspended) and $usernew->suspended and !$user->suspended) {
            session_kill_user($user->id);
        }

        $usercreated = false;
    }

    $usercontext = get_context_instance(CONTEXT_USER, $usernew->id);

    //update preferences
    useredit_update_user_preference($usernew);

    // update tags
    if (!empty($CFG->usetags) and empty($USER->newadminuser)) {
        useredit_update_interests($usernew, $usernew->interests);
    }

    //update user picture
    if (!empty($CFG->gdversion) and empty($USER->newadminuser)) {
        useredit_update_picture($usernew, $userform);
    }

    // update mail bounces
    useredit_update_bounces($user, $usernew);

    // update forum track preference
    useredit_update_trackforums($user, $usernew);

    // save custom profile fields data
    profile_save_data($usernew);

    // reload from db
    $usernew = $DB->get_record('user', array('id'=>$usernew->id));

    // trigger events
    if ($usercreated) {
        events_trigger('user_created', $usernew);
    } else {
        events_trigger('user_updated', $usernew);
    }

    if ($user->id == $USER->id) {
        // Override old $USER session variable
        foreach ((array)$usernew as $variable => $value) {
            $USER->$variable = $value;
        }
        // preload custom fields
        profile_load_custom_fields($USER);

        if (!empty($USER->newadminuser)) {
            unset($USER->newadminuser);
            // apply defaults again - some of them might depend on admin user info, backup, roles, etc.
            admin_apply_default_settings(NULL , false);
            // redirect to admin/ to continue with installation
            redirect("$CFG->wwwroot/$CFG->admin/");
        } else {
            redirect("$CFG->wwwroot/user/view.php?id=$USER->id&course=$course->id");
        }
    } else {
        session_gc(); // remove stale sessions
        redirect("$CFG->wwwroot/$CFG->admin/user.php");
		//		$redirect = true;
    }
    //never reached
}

// make sure we really are on the https page when https login required
$PAGE->verify_https_required();


/// Display page header
if ($user->id == -1 or ($user->id != $USER->id)) {
    if ($user->id == -1) {
        echo $OUTPUT->header();
    } else {
        $PAGE->set_heading($SITE->fullname);
        echo $OUTPUT->header();
        $userfullname = fullname($user, true);
        echo $OUTPUT->heading($userfullname);
    }
} else if (!empty($USER->newadminuser)) {
    $strinstallation = get_string('installation', 'install');
    $strprimaryadminsetup = get_string('primaryadminsetup');

    $PAGE->navbar->add($strprimaryadminsetup);
    $PAGE->set_title($strinstallation);
    $PAGE->set_heading($strinstallation);
    $PAGE->set_cacheable(false);

    echo $OUTPUT->header();
    echo $OUTPUT->box(get_string('configintroadmin', 'admin'), 'generalbox boxwidthnormal boxaligncenter');
    echo '<br />';
} else {
    $streditmyprofile = get_string('editmyprofile');
    $strparticipants  = get_string('participants');
    $strnewuser       = get_string('newuser');
    $userfullname     = fullname($user, true);

    $PAGE->set_title("$course->shortname: $streditmyprofile");
    $PAGE->set_heading($course->fullname);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($userfullname);
}

/// Finally display THE form


$userform->display();

/// and proper footer
echo $OUTPUT->footer();

