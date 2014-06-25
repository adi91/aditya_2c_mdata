<?php
/**
 * Create group OR edit group settings.
 *
 * @copyright &copy; 2006 The Open University
 * @author N.D.Freear AT open.ac.uk
 * @author J.White AT open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package groups
 */

require_once('../config.php');
require_once('lib.php');
require_once('reschedule_lesson_form.php');


/// get url variables
$courseid = optional_param('courseid', 0, PARAM_INT);
$id       = optional_param('id', 0, PARAM_INT);

$day1       = optional_param('day', 0, PARAM_INT);
$mon1      = optional_param('mon', 0, PARAM_INT);
$year1      = optional_param('year', 0, PARAM_INT);

$delete   = optional_param('delete', 0, PARAM_BOOL);
$confirm  = optional_param('confirm', 0, PARAM_BOOL);


// This script used to support group delete, but that has been moved. In case
// anyone still links to it, let's redirect to the new script.
if($delete) {
    redirect('delete.php?courseid='.$courseid.'&groups='.$id);
}

if ($id) {
    if (!$group = $DB->get_record('groups', array('id'=>$id))) {
        print_error('invalidgroupid');
    }
    if (empty($courseid)) {
        $courseid = $group->courseid;

    } else if ($courseid != $group->courseid) {
    	
        print_error('invalidcourseid');
    }

    if (!$course = $DB->get_record('course', array('id'=>$courseid))) {
        print_error('invalidcourseid');
    }

} else {
    if (!$course = $DB->get_record('course', array('id'=>$courseid))) {
        print_error('invalidcourseid');
    }
    $group = new stdClass();
    $group->courseid = $course->id;
}

if ($id !== 0) {
    $PAGE->set_url('/group/group.php', array('id'=>$id));
} else {
    $PAGE->set_url('/group/group.php', array('courseid'=>$courseid));
}

require_login($course);
$context = get_context_instance(CONTEXT_COURSE, $course->id);
require_capability('moodle/course:managegroups', $context);

$returnurl = $CFG->wwwroot.'/group/index.php?id='.$course->id.'&group='.$id;
$previousurl = $CFG->wwwroot.'/calendar/view_centeradmin.php?view=day&cal_d='.$day1.'&cal_m='.$mon1.'&cal_y='.$year1;



// Prepare the description editor: We do support files for group descriptions
$editoroptions = array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'maxbytes'=>$course->maxbytes, 'trust'=>false, 'context'=>$context, 'noclean'=>true);
if (!empty($group->id)) {
    $group = file_prepare_standard_editor($group, 'description', $editoroptions, $context, 'group', 'description', $group->id);
} else {
    $group = file_prepare_standard_editor($group, 'description', $editoroptions, $context, 'group', 'description', null);
}

/// First create the form
$editform = new reschedule_lesson_form(null, array('editoroptions'=>$editoroptions));
$editform->set_data($group);

if ($editform->is_cancelled()) {
    redirect($previousurl);

} elseif ($data = $editform->get_data()) {
    if ($data->id) {
	    ///print_r($data);
		//die();
		$class_activities = $DB->get_records('class_activity',array(
		'course'=>$data->courseid,
		'section'=>$data->section,
		'groupid'=>$data->id));
		foreach($class_activities as $class_activity){
			//echo $class_activity->id;
			$data_section = new stdclass();
			$data_section->id = $class_activity->id;
			$data_section->availablefrom = $data->startdate;
			$DB->update_record('class_activity',$data_section,false);
		}
		
    } 	
    redirect($returnurl);
}

$strgroups = get_string('groups');
$strparticipants = get_string('participants');

if ($id) {
    $strheading = get_string('editgroupsettings', 'group');
} else {
    $strheading = get_string('creategroup', 'group');
}

$PAGE->navbar->add($strparticipants, new moodle_url('/user/index.php', array('id'=>$courseid)));
$PAGE->navbar->add($strgroups, new moodle_url('/group/index.php', array('id'=>$courseid)));
$PAGE->navbar->add($strheading);

/// Print header
$PAGE->set_title($strgroups);
$PAGE->set_heading($course->fullname . ': '.$strgroups);
echo $OUTPUT->header();

echo '<div id="grouppicture">';
if ($id) {
    print_group_picture($group, $course->id);
}
echo '</div>';
$editform->display();
echo $OUTPUT->footer();
