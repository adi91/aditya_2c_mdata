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
require_once('group_form.php');


/// get url variables
$courseid = optional_param('courseid', 0, PARAM_INT);
$id       = optional_param('id', 0, PARAM_INT);

$delete   = optional_param('delete', 0, PARAM_BOOL);
$confirm  = optional_param('confirm', 0, PARAM_BOOL);

$scheduledays = optional_param('scheduledays',0,PARAM_RAW);
$mon_starttime = optional_param('mon_starttime',0,PARAM_RAW);
$tue_starttime = optional_param('tue_starttime',0,PARAM_RAW);
$wed_starttime = optional_param('wed_starttime',0,PARAM_RAW);
$thu_starttime = optional_param('thu_starttime',0,PARAM_RAW);
$fri_starttime = optional_param('fri_starttime',0,PARAM_RAW);
$sat_starttime = optional_param('sat_starttime',0,PARAM_RAW);
$sun_starttime = optional_param('sun_starttime',0,PARAM_RAW);

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
    $sql="SELECT * FROM {class_schedule} WHERE groupid=? ORDER BY id";
    $param=array('groupid'=>$id);
    $time_sch= $DB->get_records_sql($sql,$param);
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

if ($id and $delete) {
    if (!$confirm) {
        $PAGE->set_title(get_string('deleteselectedgroup', 'group'));
        $PAGE->set_heading($course->fullname . ': '. get_string('deleteselectedgroup', 'group'));
        echo $OUTPUT->header();
        $optionsyes = array('id'=>$id, 'delete'=>1, 'courseid'=>$courseid, 'sesskey'=>sesskey(), 'confirm'=>1);
        $optionsno  = array('id'=>$courseid);
        $formcontinue = new single_button(new moodle_url('group.php', $optionsyes), get_string('yes'), 'get');
        $formcancel = new single_button(new moodle_url($baseurl, $optionsno), get_string('no'), 'get');
        echo $OUTPUT->confirm(get_string('deletegroupconfirm', 'group', $group->name), $formcontinue, $formcancel);
        echo $OUTPUT->footer();
        die;

    } else if (confirm_sesskey()){
        if (groups_delete_group($id)) {
			$DB->delete_records('class_activity',array('groupid'=>$id));
            redirect('index.php?id='.$course->id);
        } else {
            print_error('erroreditgroup', 'group', $returnurl);
        }
    }
}

// Prepare the description editor: We do support files for group descriptions
$editoroptions = array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'maxbytes'=>$course->maxbytes, 'trust'=>false, 'context'=>$context, 'noclean'=>true);
if (!empty($group->id)) {
    $group = file_prepare_standard_editor($group, 'description', $editoroptions, $context, 'group', 'description', $group->id);
} else {
    $group = file_prepare_standard_editor($group, 'description', $editoroptions, $context, 'group', 'description', null);
}

/// First create the form
$editform = new group_form(null, array('editoroptions'=>$editoroptions ,'time'=>$time_sch));
$editform->set_data($group);


if ($editform->is_cancelled()) {
    redirect($returnurl);
} 
elseif ($data = $editform->get_data()) {
    if ($data->id) {
        groups_update_group($data, $editform, $editoroptions);
        $DB->delete_records('class_activity', array('course'=>$data->courseid,'groupid'=>$id));
        $DB->delete_records('class_schedule', array('groupid'=>$id));
		$class_activity = $DB->get_record('class_activity',array('course'=>$data->courseid,'section'=>NULL,'sectionno'=>NULL,'module'=>NULL,'groupid'=>$data->id));
    	$data_course = new stdclass();
		$data_course->id = 0;
		$data_course->course = $data->courseid;
		$data_course->groupid = $id;
		$data_course->availablefrom = $data->startdate;
		$DB->insert_record('class_activity',$data_course,false);
	   	$course_startday = date('N',$data->startdate);
    	$course_sday = date('D',$data->startdate);
    	$class_starttime=$data->startdate;
    	//$set_starttime = new stdClass();
 		foreach($scheduledays as $sch)
    	{
    		if($sch!="")
    		$set_starttime[$sch] = strtotime("$sch",$class_starttime);
    	}
    
    	asort($set_starttime);
    	
    	foreach($set_starttime  as $key => $value)
    	{
    		$lesson_day= strtolower($key);
    		$time_arr=strtolower($key)."_starttime";
    		$time_arr=array_values(array_filter($$time_arr, "empty_val"));
    		for($i=0;$i<count($time_arr);$i++)
    		{
    			$data_time = new stdclass();
    			$data_time->id = 0;
    			$data_time->groupid = $id;
    			$data_time->class_day= $key;
    			$data_time->class_time=$time_arr[$i];
    			$DB->insert_record('class_schedule',$data_time,false);
    			$data_time=explode(":",$time_arr[$i]);
    			$set_starttime= ((intval($data_time[0]) * 60*60) + (intval($data_time[1]) * 60));
    			$lesson_arr[][$lesson_day]= $set_starttime;
    		}
    	}
    	$sections = $DB->get_records('course_sections',array('course'=>$course->id));
		$lesson_arr1=$lesson_arr;
		foreach($sections as $section)
		{
			if($section->section>0)
			{
				$section_arr[]=$section->section;
			}
		}
		//array_pop($section_arr);
		$ecnt = ceil(count($section_arr)/ count($lesson_arr));
		for($i=0;$i<$ecnt;$i++)
		{
			foreach($lesson_arr as $keya =>$less)
			{
				if(count($arr_print)<= count($section_arr))
				$arr_print[]= $less;
				
			}
		}
	
		ksort($arr_print);
		
		$weekcnt=0;
		foreach($arr_print as $key1 => $less1)
		{
			foreach ($less1 as $key => $value)
			{	
				$data_section = new stdclass();
				$data_section->id = 0;
				$data_section->course = $data->courseid;
				$sections_id = $DB->get_records('course_sections',array('course'=>$group->courseid,'section'=>$key1+1));
					if($sections_id) {
						foreach($sections_id as $sect){
						$data_section->section = $sect->id;
						}
					}
				//$data_section->section = $section->id;
				$data_section->sectionno = $key1+1;
				$data_section->groupid = $id;
				
				$lesson1_startday = date('D',strtotime($key));
				$section_week_count= $weekcnt;
				if($course_sday == $lesson1_startday ){
				$lesson_availablefrom = $class_starttime;
				$data_section->availablefrom = ($lesson_availablefrom + $value) +  ((60*60*24*7)* ($section_week_count));
				$arr_print_less[][$section->section]=date("d m Y,D",$data_section->availablefrom);
				}
				else{
				$lesson_availablefrom = strtotime('next '.$key, $class_starttime);
				$data_section->availablefrom = ($lesson_availablefrom + $value) +  ((60*60*24*7)* ($section_week_count));
				$arr_print_less[][$section->section]=date("d m Y,D",$data_section->availablefrom);
				}
				$cnt_less++;
				if(count($lesson_arr) == $cnt_less)
				{
				$cnt_less=0;
				$weekcnt= $weekcnt+1;
				}
				
				//print_r($data_section);
				if($data_section->sectionno >0 && !is_null($data_section->section)){
					$DB->insert_record('class_activity',$data_section,false);
				}
				//$DB->insert_record('class_activity',$data_section,false);
			}
			
		}
		$returnurl = $CFG->wwwroot.'/group/index.php?id='.$course->id.'&group='.$id;
 	}
    else {
    	$id = groups_create_group($data, $editform, $editoroptions);
		$data_course = new stdclass();
		$data_course->id = 0;
		$data_course->course = $data->courseid;
		$data_course->groupid = $id;
		$data_course->availablefrom = $data->startdate;
		$DB->insert_record('class_activity',$data_course,false);
	   	$course_startday = date('N',$data->startdate);
    	$course_sday = date('D',$data->startdate);
	    $class_starttime=$data->startdate;
	    //$set_starttime = new stdClass();
	 		foreach($scheduledays as $sch)
	    	{
	    		if($sch!="")
	    		$set_starttime[$sch] = strtotime("$sch",$class_starttime);
	    	}
	    	asort($set_starttime);
	    	foreach($set_starttime  as $key => $value)
	    	{
	    		$lesson_day= strtolower($key);
	    		$time_arr=strtolower($key)."_starttime";
	    		$time_arr=array_values(array_filter($$time_arr, "empty_val"));
	    		for($i=0;$i<count($time_arr);$i++)
	    		{
	    			$data_time = new stdclass();
	    			$data_time->id = 0;
	    			$data_time->groupid = $id;
	    			$data_time->class_day= $key;
	    			$data_time->class_time=$time_arr[$i];
	    			$DB->insert_record('class_schedule',$data_time,false);
	    			$data_time=explode(":",$time_arr[$i]);
	    			$set_starttime= ((intval($data_time[0]) * 60*60) + (intval($data_time[1]) * 60));
	    			$lesson_arr[][$lesson_day]= $set_starttime;
	    		}
	    	}
    	
     	$sections = $DB->get_records('course_sections',array('course'=>$course->id));
     	
		$lesson_arr1=$lesson_arr;
		foreach($sections as $section)
		{
			if($section->section>0)
			{
				$section_arr[]=$section->section;
			}
		}
		//array_pop($section_arr);
		$ecnt = ceil(count($section_arr)/ count($lesson_arr));
		for($i=0;$i<$ecnt;$i++)
		{
			foreach($lesson_arr as $keya =>$less)
			{
				if(count($arr_print)<= count($section_arr))
				$arr_print[]= $less;
				
			}
		}
		
		ksort($arr_print);
		$cnt_less=0;
		$weekcnt=0;
		foreach($arr_print as $key1 => $less1)
		{
			foreach ($less1 as $key => $value)
			{	
				$data_section = new stdclass();
				$data_section->id = 0;
				$data_section->course = $data->courseid;
				$sections_id = $DB->get_records('course_sections',array('course'=>$group->courseid,'section'=>$key1+1));
					if($sections_id) {
						foreach($sections_id as $sect){
						$data_section->section = $sect->id;
						}
					}
				$data_section->sectionno = $key1+1;
				$data_section->groupid = $id;
				
				$lesson1_startday = date('D',strtotime($key));
				$section_week_count= $weekcnt;
				if($course_sday == $lesson1_startday ){
				$lesson_availablefrom = $class_starttime;
				$data_section->availablefrom = ($lesson_availablefrom + $value) +  ((60*60*24*7)* ($section_week_count));
				$arr_print_less[][$section->section]=date("d m Y,D",$data_section->availablefrom);
				}
				else{
				$lesson_availablefrom = strtotime('next '.$key, $class_starttime);
				$data_section->availablefrom = ($lesson_availablefrom + $value) +  ((60*60*24*7)* ($section_week_count));
				$arr_print_less[][$section->section]=date("d m Y,D",$data_section->availablefrom);
				}
				$cnt_less++;
				if(count($lesson_arr) == $cnt_less)
				{
				$cnt_less=0;
				$weekcnt= $weekcnt+1;
				}
				
				//print_r($data_section);
				if($data_section->sectionno >0 && !is_null($data_section->section)){
					$DB->insert_record('class_activity',$data_section,false);
				}
			}
			
		}
		
		//print_r($arr_print_less);
		//echo "</pre>";
       	$returnurl = $CFG->wwwroot.'/group/index.php?id='.$course->id.'&group='.$id;
       
    }
    redirect($returnurl); 
}
$strgroups = get_string('groups');
$strparticipants = get_string('participants');

if ($id) {
    $strheading = get_string('editgroupsettings', 'group');
    $group_pre = $DB->get_record('groups',array('id'=>$id));
    
} else {
    $strheading = get_string('creategroup', 'group');
    $group_pre="";
}
/******* #234  Scheduling Class times - times should be intelligent. ****** modified by Pankaj *** 01/09/2012 ***/
$jsmodule = array(
    'name'     => 'group',
    'fullpath' => '/group/module.js',
    'requires' => array('base', 'io', 'node', 'json'),
    'strings' => array(
         )
);
$PAGE->requires->js_init_call('M.core_group.init_fill_call', array(), false, $jsmodule);
/******* #234  Scheduling Class times - times should be intelligent. ****** modified by Pankaj *** 01/09/2012 ***/
 
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
/*
if($id){
if(is_null($group_pre->weekday2) || $group_pre->weekday2==""){
echo '<div>';
echo '<h2>'.get_string('class_single_day_schedule').'</h2>';
echo '</div>';
}
}*/
$editform->display();
echo $OUTPUT->footer();

function empty_val($val)
    {
    	if($val !="" || $val!=0)
    	{
    		return $val;
    	}
    }
