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
 * A page displaying the user's contacts and messages
 *
 * @package   moodlecore
 * @copyright 2010 Andrew Davis
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../config.php');
require_once('lib.php');
require_once('send_form.php');

require_login(0, false);

if (isguestuser()) {
    redirect($CFG->wwwroot);
}

if (empty($CFG->messaging)) {
    print_error('disabled', 'message');
}

//'viewing' is the preferred URL parameter but we'll still accept usergroup in case its referenced externally
$usergroup = optional_param('usergroup', MESSAGE_VIEW_UNREAD_MESSAGES, PARAM_ALPHANUMEXT);
$viewing = optional_param('viewing', $usergroup, PARAM_ALPHANUMEXT);

$history   = optional_param('history', MESSAGE_HISTORY_SHORT, PARAM_INT);
$search    = optional_param('search', '', PARAM_CLEAN); //TODO: use PARAM_RAW, but make sure we use s() and p() properly

//the same param as 1.9 and the param we have been logging. Use this parameter.
$user1id   = optional_param('user1', $USER->id, PARAM_INT);
//2.0 shipped using this param. Retaining it only for compatibility. It should be removed.
$user1id   = optional_param('user', $user1id, PARAM_INT);

//the same param as 1.9 and the param we have been logging. Use this parameter.
$user2id   = optional_param('user2', 0, PARAM_INT);
//The class send_form supplies the receiving user id as 'id'
$user2id   = optional_param('id', $user2id, PARAM_INT);

$addcontact     = optional_param('addcontact',     0, PARAM_INT); // adding a contact
$removecontact  = optional_param('removecontact',  0, PARAM_INT); // removing a contact
$blockcontact   = optional_param('blockcontact',   0, PARAM_INT); // blocking a contact
$unblockcontact = optional_param('unblockcontact', 0, PARAM_INT); // unblocking a contact

//for search
$advancedsearch = optional_param('advanced', 0, PARAM_INT);

//if they have numerous contacts or are viewing course participants we might need to page through them
$page = optional_param('page', 0, PARAM_INT);

$url = new moodle_url('/message/index.php');

if ($user2id !== 0) {
    $url->param('user2', $user2id);
}

if ($user2id !== 0) {
    //Switch view back to contacts if:
    //1) theyve searched and selected a user
    //2) they've viewed recent messages or notifications and clicked through to a user
    if ($viewing == MESSAGE_VIEW_SEARCH || $viewing == MESSAGE_VIEW_SEARCH || $viewing == MESSAGE_VIEW_RECENT_NOTIFICATIONS) {
        $viewing = MESSAGE_VIEW_CONTACTS;
    }
}
$url->param('viewing', $viewing);

$PAGE->set_url($url);

$PAGE->set_context(get_context_instance(CONTEXT_USER, $USER->id));
$PAGE->navigation->extend_for_user($USER);
$PAGE->set_pagelayout('course');

// Disable message notification popups while the user is viewing their messages
$PAGE->set_popup_notification_allowed(false);

$context = get_context_instance(CONTEXT_SYSTEM);

$user1 = null;
$currentuser = true;
$showcontactactionlinks = true;
if ($user1id != $USER->id) {
    $user1 = $DB->get_record('user', array('id' => $user1id));
    if (!$user1) {
        print_error('invaliduserid');
    }
    $currentuser = false;//if we're looking at someone else's messages we need to lock/remove some UI elements
    $showcontactactionlinks = false;
} else {
    $user1 = $USER;
}
unset($user1id);

$user2 = null;
if (!empty($user2id)) {
    $user2 = $DB->get_record("user", array("id" => $user2id));
    if (!$user2) {
        print_error('invaliduserid');
    }
}
unset($user2id);

// Is the user involved in the conversation?
// Do they have the ability to read other user's conversations?
if (!message_current_user_is_involved($user1, $user2) && !has_capability('moodle/site:readallmessages', $context)) {
    print_error('accessdenied','admin');
}

/// Process any contact maintenance requests there may be
if ($addcontact and confirm_sesskey()) {
    add_to_log(SITEID, 'message', 'add contact', 'index.php?user1='.$addcontact.'&amp;user2='.$USER->id, $addcontact);
    message_add_contact($addcontact);
    redirect($CFG->wwwroot . '/message/index.php?viewing=contacts&id='.$addcontact);
}
if ($removecontact and confirm_sesskey()) {
    add_to_log(SITEID, 'message', 'remove contact', 'index.php?user1='.$removecontact.'&amp;user2='.$USER->id, $removecontact);
    message_remove_contact($removecontact);
}
if ($blockcontact and confirm_sesskey()) {
    add_to_log(SITEID, 'message', 'block contact', 'index.php?user1='.$blockcontact.'&amp;user2='.$USER->id, $blockcontact);
    message_block_contact($blockcontact);
}
if ($unblockcontact and confirm_sesskey()) {
    add_to_log(SITEID, 'message', 'unblock contact', 'index.php?user1='.$unblockcontact.'&amp;user2='.$USER->id, $unblockcontact);
    message_unblock_contact($unblockcontact);
}

//was a message sent? Do NOT allow someone looking at someone else's messages to send them.
$messageerror = null;
if ($currentuser && !empty($user2) && has_capability('moodle/site:sendmessage', $context)) {
    // Check that the user is not blocking us!!
    if ($contact = $DB->get_record('message_contacts', array('userid' => $user2->id, 'contactid' => $user1->id))) {
        if ($contact->blocked and !has_capability('moodle/site:readallmessages', $context)) {
            $messageerror = get_string('userisblockingyou', 'message');
        }
    }
    $userpreferences = get_user_preferences(NULL, NULL, $user2->id);

    if (!empty($userpreferences['message_blocknoncontacts'])) {  // User is blocking non-contacts
        if (empty($contact)) {   // We are not a contact!
            $messageerror = get_string('userisblockingyounoncontact', 'message');
        }
    }

    if (empty($messageerror)) {
        $mform = new send_form();
        $defaultmessage = new stdClass;
        $defaultmessage->id = $user2->id;
        $defaultmessage->message = '';

        //Check if the current user has sent a message
        $data = $mform->get_data();
        if (!empty($data) && !empty($data->message)) {
            if (!confirm_sesskey()) {
                print_error('invalidsesskey');
            }
            $messageid = message_post_message($user1, $user2, $data->message, FORMAT_MOODLE);
            if (!empty($messageid)) {
                //including the id of the user sending the message in the logged URL so the URL works for admins
                //note message ID may be misleading as the message may potentially get a different ID when moved from message to message_read
                add_to_log(SITEID, 'message', 'write', 'index.php?user='.$user1->id.'&id='.$user2->id.'&history=1#m'.$messageid, $user1->id);
                redirect($CFG->wwwroot . '/message/index.php?viewing='.$viewing.'&id='.$user2->id);
            }
        }
    }
}

$strmessages = get_string('messages', 'message');
if (!empty($user2)) {
    $user2fullname = fullname($user2);

    $PAGE->set_title("$strmessages: $user2fullname");
    $PAGE->set_heading("$strmessages: $user2fullname");
} else {
    $PAGE->set_title("{$SITE->shortname}: $strmessages");
    $PAGE->set_heading("{$SITE->shortname}: $strmessages");
}

//now the page contents
$PAGE->set_pagelayout('frontpage');
echo $OUTPUT->header();

echo $OUTPUT->box_start('message');

$countunread = 0; //count of unread messages from $user2
$countunreadtotal = 0; //count of unread messages from all users

//we're dealing with unread messages early so the contact list will accurately reflect what is read/unread
$viewingnewmessages = false;
if (!empty($user2)) {
    //are there any unread messages from $user2
    $countunread = message_count_unread_messages($user1, $user2);
    if ($countunread>0) {
        //mark the messages we're going to display as read
        message_mark_messages_read($user1->id, $user2->id);
         if($viewing == MESSAGE_VIEW_UNREAD_MESSAGES) {
             $viewingnewmessages = true;
         }
    }
}
$countunreadtotal = message_count_unread_messages($user1);

if ($countunreadtotal == 0 && $viewing == MESSAGE_VIEW_UNREAD_MESSAGES && empty($user2)) {
    //default to showing the search
    //$viewing = MESSAGE_VIEW_SEARCH;
}

$blockedusers = message_get_blocked_users($user1, $user2);
$countblocked = count($blockedusers);

list($onlinecontacts, $offlinecontacts, $strangers) = message_get_contacts($user1, $user2);

?>
<?php if(is_siteadmin() || is_centeradmin() || is_mhescordinator()){
		echo '<span style="float:right;"><a href="'.$CFG->wwwroot.'/admin/user/user_bulk.php" class="buttonstyled">Send message to multiple users</a></span>';
}
?>
<form id="userselector" name="userselector" action="" method="GET">
Send message to User<br>

<?php
if(is_siteadmin() || is_mhescordinator()){
	$userlist = $DB->get_records('user',array('deleted'=>0),'',"id,firstname,lastname");
	?>
	<select name="id" onChange="document.getElementById('userselector').submit()">
	<option value = "">Select a User</option>
	<?php
	
	foreach($userlist as $user){
		if($user->id == $USER->id){
		  continue;
		}
		?><option value = "<?php echo $user->id; ?>" <?php if($user->id == $_GET['id']){ echo 'selected';} ?>><?php echo $user->firstname.' '.$user->lastname;?></option><?php
	}
	?></select><?php
}elseif(is_parent()){
	
	global $USER,$CFG,$DB;
	
	$wards_center_sql = "Select site_fk from mdl_user where id in (SELECT user.id as id FROM `mdl_user` as user left join `mdl_user` as parent on user.parentlist = parent.username 	WHERE user.deleted = 0 and 	(user.parentlist like '".$USER->username."' 	or user.parentlist like '%*".$USER->username."' 	or user.parentlist like '".$USER->username."*%' 	or user.parentlist like '%*".$USER->username."*%'))";
	//
	$wards_centers = $DB->get_records_sql($wards_center_sql);
	foreach($wards_centers as $wards_center){
		//print_r(array_unique($wards_center));
		$wardscenter[] = $wards_center->site_fk;
		
	}
	$wardscenter[] = $USER->site_fk;
	$wardscenter = implode('*',$wardscenter);
	$wardscenter = array_unique(explode('*',$wardscenter));
	$wardscenter = implode('*',$wardscenter);
	//print_r($wardscenter);
	
	$sql= "SELECT u.id,u.firstname,u.lastname FROM {user} u
				JOIN {role_assignments} ra ON ra.userid= u.id 
				WHERE ra.contextid !=1  AND ra.roleid=10 and u.deleted = 0";
	$usercenters = explode("*",$wardscenter);
	$c=0;
	$countusercenter = count($usercenters);
	if($usercenters){
		$sql .= " AND ("; 
		foreach($usercenters as $usercenter){
			$sql .= " (u.site_fk like '$usercenter' or u.site_fk like '%*$usercenter' or u.site_fk like '$usercenter*%'  or u.site_fk like '%*$usercenter*%')";
			if($c < ($countusercenter -1)){
				$sql .= " or ";
			}
			$c++;
		}
		$sql .= ") ";
	}
//echo $sql;
	
	
		$param= array(10);
		$centeradmins=$DB->get_records_sql($sql);
	?>
	<select name="id" onChange="document.getElementById('userselector').submit()">
	<option value = "">Select a User</option>
	<?php
		foreach($centeradmins as $centeradmin){
			if(!in_array($centeradmin->id,$selet_user_list)){
				?><option value="<?php echo $centeradmin->id; ?>"><?php echo $centeradmin->firstname.' '.$centeradmin->lastname; ?>(centeradmin)</option><?php
				$selet_user_list[] = $centeradmin->id;
			}
		}
	$selet_user_list = array();
	$query1 = "SELECT user.id as id,user.firstname,user.lastname FROM `mdl_user` as user left join `mdl_user` as parent on user.parentlist = parent.username WHERE user.deleted = 0 and user.parentlist like  '".$USER->username."'";
	$params = array();
	$userlist = $DB->get_records_sql($query1,$params);	    
	foreach($userlist as $student){
		if($student->id == $USER->id){
		  continue;
		}
		$thisstudent = $DB->get_record('user', array('id' => $studentid,'deleted'=>0));
		if ($thisstudent->deleted) {
			continue;
		}
		if(!in_array($student->id,$selet_user_list)){
			?><option value = "<?php echo $student->id; ?>" <?php if($student->id == $_GET['id']){ echo 'selected';} ?>><?php echo $student->firstname.' '.$student->lastname.' (Ward)';?></option><?php
			$selet_user_list[] = $student->id;
		}
	}
	
	if($userlist){
		foreach($userlist as $student){
			$studentid = '';
			$studentid = $studentid ? $studentid : $student->id;       // Owner of the page
			$thisstudent = $DB->get_record('user', array('id' => $studentid,'deleted'=>0));
			if ($thisstudent->deleted) {
				continue;
			}			
			if (!isset($hiddenfields['mycourses'])) {
					//if ($mycourses = enrol_get_all_users_courses($studentid, true, NULL, 'visible DESC,sortorder ASC')) {
					if ($mycourses = enrol_get_users_courses($studentid, true, NULL, 'visible DESC,sortorder ASC')) {					
					    $shown=0;
						$courselisting = '';
						foreach ($mycourses as $mycourse) {					        
							if ($mycourse->category) {						   
								$class = '';
								$courseid = $mycourse->id;
								
								$contextid = get_context_instance(CONTEXT_COURSE, $courseid);
								$enrolled_users = $DB->get_records('role_assignments',array('contextid'=>$contextid->id));								
								foreach($enrolled_users as $enrolled_user){	
									 
                                     $student_classids = $DB->get_records('groups_members',array('userid'=>$studentid));
									
									 $showteacher = false;
                                     foreach($student_classids as $student_class){									    
										$teacher_classids = $DB->get_records('groups_members',array('userid'=>$enrolled_user->userid,'groupid'=>$student_class->groupid));
										if($teacher_classids){
										  $showteacher = true;
										}
									 }
									 
									if(($enrolled_user->roleid ==3 || $enrolled_user->roleid == 4) && $showteacher)
									{
									    $teacher = $DB->get_record('user', array('id'=>$enrolled_user->userid,'deleted'=>0));
									   
										if ($teacher->deleted) {
											continue;
										}
										if(!in_array($teacher->id,$selet_user_list)){
											?><option value = "<?php echo $teacher->id; ?>" <?php if($teacher->id == $_GET['id']){ echo 'selected';} ?>><?php echo $teacher->firstname.' '.$teacher->lastname.' (Instructor)';?></option><?php
											$selet_user_list[] = $teacher->id;
										}
									}								
								}			
								
							}						
						}
						//print_row(get_string('courseprofiles').':', rtrim($courselisting,', '));
						
					}
				}
		}
	}
	?></select><?php
}elseif(is_student()){
	global $USER,$CFG,$DB;
	$selet_user_list = array();
	?>
	<select name="id" onChange="document.getElementById('userselector').submit()">
	<option value = "">Select a User</option>
	<?php
			$studentid = '';
			$studentid = $studentid ? $studentid : $USER->id;       // Owner of the page			
			if (!isset($hiddenfields['mycourses'])) {
					//if ($mycourses = enrol_get_all_users_courses($studentid, true, NULL, 'visible DESC,sortorder ASC')) {
					if ($mycourses = enrol_get_users_courses($studentid, true, NULL, 'visible DESC,sortorder ASC')) {					
					    $shown=0;
						$courselisting = '';
						foreach ($mycourses as $mycourse) {					        
							if ($mycourse->category) {						   
								$class = '';
								$courseid = $mycourse->id;
								
								$contextid = get_context_instance(CONTEXT_COURSE, $courseid);
								$enrolled_users = $DB->get_records('role_assignments',array('contextid'=>$contextid->id));								
								foreach($enrolled_users as $enrolled_user){	
									 
                                     $student_classids = $DB->get_records('groups_members',array('userid'=>$studentid));
									
									 $showteacher = false;
                                     foreach($student_classids as $student_class){									    
										$teacher_classids = $DB->get_records('groups_members',array('userid'=>$enrolled_user->userid,'groupid'=>$student_class->groupid));
										if($teacher_classids){
										  $showteacher = true;
										}
									 }
									 
									if(($enrolled_user->roleid ==3 || $enrolled_user->roleid == 4) && $showteacher)
									{
									    $teacher = $DB->get_record('user', array('id'=>$enrolled_user->userid,'deleted'=>0));
									   
										if ($teacher->deleted) {
											continue;
										}
										if(!in_array($teacher->id,$selet_user_list)){
											?><option value = "<?php echo $teacher->id; ?>" <?php if($teacher->id == $_GET['id']){ echo 'selected';} ?>><?php echo $teacher->firstname.' '.$teacher->lastname.' (Instructor)';?></option><?php
											$selet_user_list[] = $teacher->id;
										}
									}									
								}			
								
							}						
						}
						//print_row(get_string('courseprofiles').':', rtrim($courselisting,', '));
						
					}
				}	
	?></select><?php
}elseif(is_teacher() || is_non_editing_teacher()){
	global $USER,$CFG,$DB;
	$selet_user_list = array();
	?>
	<select name="id" onChange="document.getElementById('userselector').submit()">
	<option value = "">Select a User</option>
	<?php
			$teacherid = '';
			$teacherid = $teacherid ? $teacherid : $USER->id;       // Owner of the page			
			if (!isset($hiddenfields['mycourses'])) {
					//if ($mycourses = enrol_get_all_users_courses($teacherid, true, NULL, 'visible DESC,sortorder ASC')) {
					if ($mycourses = enrol_get_users_courses($teacherid, true, NULL, 'visible DESC,sortorder ASC')) {					
					    $shown=0;
						$courselisting = '';
						foreach ($mycourses as $mycourse) {					        
							if ($mycourse->category) {						   
								$class = '';
								$courseid = $mycourse->id;
								$category_context =  context_coursecat::instance($mycourse->category);
								if($CFG->theme='mghc2c'){
									$roleid = 13;
									$rolename = 'Institute Coordinator';
								}else{
									$roleid = 10;
									$rolename = 'Center Administrator';
								}
								$check_role_assignments = $DB->get_records('role_assignments',array('roleid'=>$roleid,'contextid'=>$category_context->id));
								foreach($check_role_assignments as $check_role_assignment){
									$centeradmin = $DB->get_record('user',array('id'=>$check_role_assignment->userid,'deleted'=>0));
									if(!in_array($centeradmin->id,$selet_user_list) && $USER->id != $centeradmin->id){
										?><option value = "<?php echo $centeradmin->id; ?>" <?php if($centeradmin->id == $_GET['id']){ echo 'selected';} ?>><?php echo $centeradmin->firstname.' '.$centeradmin->lastname.'('.$rolename.')'; ?></option><?php
										$selet_user_list[] = $centeradmin->id;
									}
								}
								$contextid = get_context_instance(CONTEXT_COURSE, $courseid);
								$enrolled_users = $DB->get_records('role_assignments',array('contextid'=>$contextid->id));	
                                							
								foreach($enrolled_users as $enrolled_user){	
									 
                                     $teacher_classids = $DB->get_records('groups_members',array('userid'=>$teacherid));
									
									 $showstudent = false;
                                     foreach($teacher_classids as $teacher_class){									    
										$teacher_classids = $DB->get_records('groups_members',array('userid'=>$enrolled_user->userid,'groupid'=>$teacher_class->groupid));
										if($teacher_classids){
										  $showstudent = true;
										}
									 }
									 
									if(($enrolled_user->roleid ==5) && $showstudent)
									{
									    $student = $DB->get_record('user', array('id'=>$enrolled_user->userid,'deleted'=>0));
									   
										if ($student->deleted) {
											continue;
										}
										if(!in_array($student->id,$selet_user_list) && $USER->id != $student->id){
											?><option value = "<?php echo $student->id; ?>" <?php if($student->id == $_GET['id']){ echo 'selected';} ?>><?php echo $student->firstname.' '.$student->lastname.' (Student)';?></option><?php
											$selet_user_list[] = $student->id;
										}
									  
									   if($student->parentlist){
									        $parents = explode('*',$student->parentlist);
											foreach($parents as $key=>$parent){
												$student_parent = $DB->get_record('user',array('username'=>$parent,'deleted'=>0));
												if(!in_array($student_parent->id,$selet_user_list) && $USER->id != $student_parent->id){
													?><option value="<?php echo $student_parent->id; ?>" <?php if($student_parent->id == $_GET['id']){ echo 'selected';} ?>><?php echo $student_parent->firstname.' '.$student_parent->lastname.' (Parent of '.$student->firstname.' '.$student->lastname.')'; ?></option><?php
													$selet_user_list[] = $student_parent->id;
												}
											}
									   }
									}
                                    if(($enrolled_user->roleid ==4) && $showstudent)
									{
									    $non_editing_teacher = $DB->get_record('user', array('id'=>$enrolled_user->userid,'deleted'=>0));
									   
										if ($student->deleted) {
											continue;
										}
										if(!in_array($non_editing_teacher->id,$selet_user_list) && $USER->id != $non_editing_teacher->id){
											?><option value = "<?php echo $non_editing_teacher->id; ?>" <?php if($non_editing_teacher->id == $_GET['id']){ echo 'selected';} ?>><?php echo $non_editing_teacher->firstname.' '.$non_editing_teacher->lastname.' (Non Editing Instructor)';?></option><?php
											$selet_user_list[] = $non_editing_teacher->id;
										}									   
									}	
                                    if(($enrolled_user->roleid ==3) && $showstudent)
									{
									    $editing_teacher = $DB->get_record('user', array('id'=>$enrolled_user->userid,'deleted'=>0));
									   
										if ($student->deleted) {
											continue;
										}
										if(!in_array($editing_teacher->id,$selet_user_list) && $USER->id != $editing_teacher->id){
											?><option value = "<?php echo $editing_teacher->id; ?>" <?php if($editing_teacher->id == $_GET['id']){ echo 'selected';} ?>><?php echo $editing_teacher->firstname.' '.$editing_teacher->lastname.' (Instructor)'; ?></option><?php
											$selet_user_list[] = $editing_teacher->id;
										}									   
									}									
								}			
								
							}						
						}
						//print_row(get_string('courseprofiles').':', rtrim($courselisting,', '));
						
					}
				}	
	?></select><?php
	//echo '<pre>';print_r($selet_user_list);echo '</pre>';
}elseif(is_centeradmin() || is_institute_cordinator()){
	//$userlist = $DB->get_records('user',array('site_fk'=>$USER->site_fk,'deleted'=>0),'',"id,firstname,lastname");
	$usersql = "select id,firstname,lastname from {user} ";
	$usersql .= "where deleted = 0 ";
	$usercenters = explode("*",$USER->site_fk);
	$c=0;
	$centercount = count($usercenters);
	if($usercenters){
	$usersql .= " AND ";
	}
	foreach($usercenters as $usercenter){
		$usersql .= "(site_fk like '$usercenter' or site_fk like '%*$usercenter' or site_fk like '$usercenter*%'  or site_fk like '%*$usercenter*%')";
		if($c < ($centercount-1)){
			$usersql .= " or ";
		}
		$c++;
	}
	$userlist = $DB->get_records_sql($usersql);
	?>
	<select name="id" onChange="document.getElementById('userselector').submit()">
	<option value = "">Select a User</option>
	<?php
	foreach($userlist as $user){
		if($user->id == $USER->id){
		  continue;
		}
		?><option value = "<?php echo $user->id; ?>" <?php if($user->id == @$_GET['id']){ echo 'selected';} ?>><?php echo $user->firstname.' '.$user->lastname;?></option><?php
	}
	?></select><?php
}



?>
</select>
</form>
<?php
if(isset($_POST['messageboxtoclass']) && $_POST['messageboxtoclass'] != ''){
    $msg = $_POST['messageboxtoclass'];
	$classmembersql = 'SELECT user.* FROM {groups_members} left join {user}  as user on userid = user.id where groupid = '.$_POST['messagetoclassid'].' and userid != '.$USER->id;
	$rs = $DB->get_records_sql($classmembersql,array());
	foreach ($rs as $user) {
		message_post_message($USER, $user, $msg, FORMAT_HTML);
	}	
	redirect($CFG->wwwroot.'/message/index.php');
}
?>
<?php
if(is_teacher()){
$sql = 'SELECT classes.id,classes.name FROM {groups_members} as teacher left join {groups} as classes on teacher.groupid = classes.id where teacher.userid = '.$USER->id;
$teacher_classes = $DB->get_records_sql($sql,array());
?><form id="classselector" name="classselector" action="" method="GET">
Send message to a class<br>
<select name="classid"  onChange="document.getElementById('classselector').submit()">
<option value="<?php echo ''; ?>">Select a class</option><?php
foreach($teacher_classes as $teacherclass){
     ?><option value="<?php echo $teacherclass->id; ?>" <?php if($teacherclass->id == $_GET['classid']){ echo 'selected';} ?>><?php echo $teacherclass->name; ?></option><?php
}
?>
</select>
</form>
<br>
<?php if(isset($_GET['classid']) && $_GET['classid'] != ''){ ?>
<form id="sendmessagetoclass" name ="" action="" method="POST">
<textarea name="messageboxtoclass" id="messageboxtoclass" rows="10" cols="80"></textarea>
<input type="hidden" name = "messagetoclassid" value="<?php echo $_GET['classid']; ?>">
<br><input type="submit" name="submitmessagetoclass" value="Send">
</form>
<?php } ?>
<?php } ?>
<?php
message_print_contact_selector($countunreadtotal, $viewing, $user1, $user2, $blockedusers, $onlinecontacts, $offlinecontacts, $strangers, $showcontactactionlinks, $page);
echo html_writer::start_tag('div', array('class' => 'messagearea mdl-align'));
    if (!empty($user2)) {

        echo html_writer::start_tag('div', array('class' => 'mdl-left messagehistory'));

            $visible = 'visible';
            $hidden = 'hiddenelement'; //cant just use hidden as mform adds that class to its fieldset for something else

            $recentlinkclass = $recentlabelclass = $historylinkclass = $historylabelclass = $visible;
            if ($history == MESSAGE_HISTORY_ALL) {
                $displaycount = 0;

                $recentlabelclass = $historylinkclass = $hidden;
            } else if($viewingnewmessages) {
                //if user is viewing new messages only show them the new messages
                $displaycount = $countunread;

                $recentlabelclass = $historylabelclass = $hidden;
            } else {
                //default to only showing the last few messages
                $displaycount = MESSAGE_SHORTVIEW_LIMIT;

                if ($countunread>MESSAGE_SHORTVIEW_LIMIT) {
                    $displaycount = $countunread;
                }

                $recentlinkclass = $historylabelclass = $hidden;
            }

//send message form
		        if ($currentuser && has_capability('moodle/site:sendmessage', $context)) {
		            echo html_writer::start_tag('div', array('class' => 'mdl-align messagesend'));
		                if (!empty($messageerror)) {
		                    echo $OUTPUT->heading($messageerror, 3);
		                } else {
		                    $mform = new send_form();
		                    $defaultmessage = new stdClass;
		                    $defaultmessage->id = $user2->id;
		                    $defaultmessage->message = '';
		                    //$defaultmessage->messageformat = FORMAT_MOODLE;
		                    $mform->set_data($defaultmessage);
		                    $mform->display();
		                }
		            echo html_writer::end_tag('div');
		        }
            $messagehistorylink =  html_writer::start_tag('div', array('class' => 'mdl-align messagehistorytype'));
                $messagehistorylink .= html_writer::link($PAGE->url->out(false).'&history='.MESSAGE_HISTORY_ALL,
                    get_string('messagehistoryfull','message'),
                    array('class' => $historylinkclass));

                $messagehistorylink .=  html_writer::start_tag('span', array('class' => $historylabelclass));
                    $messagehistorylink .= get_string('messagehistoryfull','message');
                $messagehistorylink .= html_writer::end_tag('span');

                $messagehistorylink .= '&nbsp;|&nbsp;'.html_writer::link($PAGE->url->out(false).'&history='.MESSAGE_HISTORY_SHORT,
                    get_string('mostrecent','message'),
                    array('class' => $recentlinkclass));

                $messagehistorylink .=  html_writer::start_tag('span', array('class' => $recentlabelclass));
                    $messagehistorylink .= get_string('mostrecent','message');
                $messagehistorylink .= html_writer::end_tag('span');

                if ($viewingnewmessages) {
                    $messagehistorylink .=  '&nbsp;|&nbsp;'.html_writer::start_tag('span');//, array('class' => $historyclass)
                        $messagehistorylink .= get_string('unreadnewmessages','message',$displaycount);
                    $messagehistorylink .= html_writer::end_tag('span');
                }

            $messagehistorylink .= html_writer::end_tag('div');

            message_print_message_history($user1, $user2, $search, $displaycount, $messagehistorylink, $viewingnewmessages);
        echo html_writer::end_tag('div');

       
    } else if ($viewing == MESSAGE_VIEW_SEARCH) {
        message_print_search($advancedsearch, $user1);
    } else if ($viewing == MESSAGE_VIEW_RECENT_CONVERSATIONS) {
        message_print_recent_conversations($user1);
    } else if ($viewing == MESSAGE_VIEW_RECENT_NOTIFICATIONS) {
        message_print_recent_notifications($user1);
    }
echo html_writer::end_tag('div');

echo $OUTPUT->box_end();

echo $OUTPUT->footer();


