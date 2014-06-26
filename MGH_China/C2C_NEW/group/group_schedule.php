<?php 
require_once('../config.php');
require_once('lib.php');

global $DB;

$sql ="SELECT * FROM mdl_class_activity mca
		LEFT JOIN mdl_groups  mg ON mg.id = mca.groupid  
		group by mca.groupid";
$param =array();
$rs = $DB->get_records_sql($sql,$param);

foreach ($rs as $group)
{
	$DB->delete_records('class_activity', array('course'=>$group->courseid,'groupid'=>$group->id));
	$data_course = new stdclass();
	$data_course->id = -1;
	$data_course->course = $group->courseid;
	$data_course->groupid = $group->id;
	$data_course->availablefrom = $group->startdate;
	$DB->insert_record('class_activity',$data_course,false);


	 $course_startday = date('N',$group->startdate);	
	 $lesson1_startday = date('N',strtotime($group->weekday1));
	 $lesson2_startday = date('N',strtotime($group->weekday2));
	
	
	if($course_startday < $lesson1_startday )	
		{
			$lesson1_startday1 = $lesson1_startday;
			$lesson2_startday2 = $lesson2_startday;
			$weekday1=$group->weekday1;
			$weekday2=$group->weekday2;
		}
		else
		{
			$lesson1_startday1 = $lesson2_startday;
			$lesson2_startday2 = $lesson1_startday;
			$weekday1=$group->weekday2;
			$weekday2=$group->weekday1;
		}
		$lesson1_availablefrom = ($group->startdate + (60*60*24*abs(($course_startday - $lesson1_startday1))));
		$lesson2_availablefrom = strtotime('next '.$weekday2,$lesson1_availablefrom);
		$sections = $DB->get_records('course_sections',array('course'=>$group->course));
		foreach($sections as $section){
			if($section->section > 0){
				$data_section = new stdclass();
				$data_section->id = 0;
				$data_section->course = $group->courseid;
				$data_section->section = $section->id;
				$data_section->sectionno = $section->section;
				$data_section->groupid = $group->id;
				 
				if($section->section == 1){
					$data_section->availablefrom = $lesson1_availablefrom;
				}elseif($section->section == 2){
					$data_section->availablefrom = $lesson2_availablefrom;
				}elseif($section->section > 2){
					if($section->section %2==1){
						$data_section->availablefrom = $lesson1_availablefrom + (60*60*24*7*(($section->section-1)/2));
					}else{
						$data_section->availablefrom = $lesson2_availablefrom + (60*60*24*7*(($section->section-2)/2));
					}
				}
				$DB->insert_record('class_activity',$data_section,false);
				$module_query = "SELECT *
				FROM {modules} AS modules 
				left join {course_modules} AS course_modules
				on modules.id = course_modules.module
				WHERE course_modules.course =".$group->courseid." and course_modules.section =".$section->id;
				$params=array();
				$modules = $DB->get_records_sql($module_query,$params);
				
				//echo '<pre>';echo date('d M Y',$data_section->availablefrom);echo '</pre>';
				foreach($modules as $module){
					$activity = $DB->get_record($module->name,array('id'=>$module->instance,'course'=>$module->course));
					///$class_activity = $DB->get_record('class_activity',array('course'=>$module->course,'module'=>$module->id,'section'=>$section->id,'groupid'=>$group->id));				
					$data_activity = new stdclass();				
					$data_activity->course = $group->courseid;
					$data_activity->module = $module->id;				
					$data_activity->section = $section->id;
					$data_activity->sectionno = $section->section;
					$data_activity->groupid = $group->id;		   
					$data_activity->availablefrom = $data_section->availablefrom;
					$data_activity->id = 0;
					
					$DB->insert_record('class_activity',$data_activity,false);
				}
			}
		}
	}

?>