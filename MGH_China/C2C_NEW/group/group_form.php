<?php

/**
 * Create//edit group form.
 *
 * @copyright &copy; 2006 The Open University
 * @author N.D.Freear AT open.ac.uk
 * @author J.White AT open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package groups
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/user/lib.php');

/// get url variables
class group_form extends moodleform {

    // Define the form
    function definition () {
        global $USER, $CFG, $COURSE,$DB;
        
        $displaylist = array();
		$notused = array();
		
		make_categories_list($displaylist, $notused);
		 /* 
		if(!(is_siteadmin())){
				foreach($displaylist as $key=>$value){		
				   if(!(can_edit_in_category($key))){
				   		unset($displaylist[$key]);
				   }	
				}
		    }
       $options = $displaylist;
      
       
       foreach($options as $key => $val)
        {
        	$courses = $DB->get_records('course',array('category'=>$key),'','id,shortname');
			
        	foreach ($courses as $c => $value)
        	{
        		$displaycourse [$value->id] = $value->shortname;
         	}
         }
		*/
     	$time_arr = $this->_customdata['time'];
		$lesson=11;
		
		$displaycourse = array();
     	$courses = $DB->get_records('course',array(),'','id,shortname,category');
		foreach($courses as $course){			
			if($course->category && $category_context =  context_coursecat::instance($course->category)){
				if($CFG->theme=='mghc2c'){
					$roleid = 13;
				}else{
					$roleid = 10;
				}
				$check_role_assignment = $DB->get_record('role_assignments',array('roleid'=>$roleid,'contextid'=>$category_context->id,'userid'=>$USER->id));
			}			
			if($check_role_assignment || is_siteadmin() || is_mhescordinator()){
				$displaycourse[$course->id] = $course->shortname;
			}
		}
		
		$mform =& $this->_form;
        $editoroptions = $this->_customdata['editoroptions'];
       	$mform->addElement('select', 'courseid', 'Select Course', $displaycourse);
        $mform->addRule('courseid', get_string('required'), 'required', null, 'client');
		$mform->setType('courseid', PARAM_INT); 
		
        $mform->addElement('text','name', get_string('groupname', 'group'),'maxlength="254" size="50"');
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        $mform->setType('name', PARAM_MULTILANG);

        $mform->addElement('editor', 'description_editor', get_string('groupdescription', 'group'), null, $editoroptions);
        $mform->setType('description_editor', PARAM_RAW);
        $mform->addElement('date_selector', 'startdate', get_string('class_startdate'));
        $mform->addHelpButton('startdate', 'startdate');
        $mform->setDefault('startdate', userdate(time()));
        $mform->setType('startdate', PARAM_MULTILANG);
		        
       $dispalytime=array();
       $dispalytime[]='Select';
        for($i=0;$i<24;$i++)
        {
        	$k=$i.":00";
        	$dispalytime[$k]=$k;
        	
        	for($j=15;$j<60;)
        	{
        		$k= $i.":".$j;
         		$dispalytime[$k]=$k;
        		$j=$j+15;
        	}
        }
        foreach($time_arr as $time1)
        {
        	$properties = get_object_vars($time1);
     		$arr_time[$properties['class_day']][]=  $properties['class_time'];	
        }
       // $dispalytime = array('9'=>'9','10'=>'10','11'=>'11','12'=>'12','13'=>'13','14'=>'14','15'=>'15','16'=>'16','17'=>'17','18'=>'18');
     	$cnt_var_mon=count($arr_time['Mon']);
        $cnt_var_tue=count($arr_time['Tue']);
        $cnt_var_wed=count($arr_time['Wed']);
        $cnt_var_thu=count($arr_time['Thu']);
        $cnt_var_fri=count($arr_time['Fri']);
        $cnt_var_sat=count($arr_time['Sat']);
        $cnt_var_sun=count($arr_time['Sun']);
        //$displaydays= array("Monday"=>"Mon","Tuesday"=>"Tue","Wednesday"=>"Wed","Thursday"=>"Thu","Friday"=>"Fri","Saturday"=>"Sat","Sunday"=>"Sun");
      	///$mform->addElement('advcheckbox', 'scheduledays', 'sheduledays',array('mon','tue'),array('group' => 1),$displaydays);
       	$mform->addElement('advcheckbox', 'scheduledays[0]', 'Mon', null, array("group"=>1, "onclick" =>"selectLesson(1,'mon',$cnt_var_mon);", "id"=>"mon"),'Mon');
    		if(count($arr_time['Mon']) > 0)
			{
			$mform->setDefault('scheduledays[0]',true);
			$mform->addElement('html', '<div class="qheader" id="lesson1" >');
			}
			else
			{
			$mform->addElement('html', '<div class="qheader" id="lesson1" style="display:none;">');
			}
			
       	//$mform->addElement('html', '<div class="qheader" id="lesson1" style="display:none;">');
       		$mform->addElement('html', '<div id="mon_lesson">');
       		for($i=1;$i<$lesson;$i++){
       			$j=$i+1;
       			$k=$i-1;
            	if($i==1 || $i<=count($arr_time['Mon']))
		     	{
		     	$mform->addElement('html', '<div id="monlesson_'.$i.'">');
		     	
		     	}else
		     	{
		     	$mform->addElement('html', '<div id="monlesson_'.$i.'" style="display:none;" >');
		     	}
		       	$mform->addElement('static','','','lesson'.$i);
		       	$mform->addElement('select', 'mon_starttime['.$k.']', 'Start time', $dispalytime, array("id"=>"mon_starttime_$i", "onclick" => "getendtime(this.id,'mon_starttime_".$j."');"));
				//$mform->addRule('mon_starttime[]', get_string('required'), 'required', null, 'client');
				$mform->setDefault('mon_starttime['.$k.']', $arr_time['Mon'][$k]);
		     	$mform->setType('mon_starttime[]', PARAM_RAW);
		     	$mform->addElement('html', '</div>');
		     	if($i==1 || $i<=count($arr_time['Mon'])){
		     	$mform->addElement('html', '<div id="monbutton_'.$i.'">');
		     	}
		     	else
		     	{
		     	$mform->addElement('html', '<div id="monbutton_'.$i.'" style="display:none;">');
		     	}
		     	if($i>=1 && $i<$lesson-1)
		     	{
		     		$mform->addElement('html','<input type="button" value="Add Lesson" onclick="show_lesson(\'mon\','.$i.');">');
		     	}
	     		if($i>1){
	     			$mform->addElement('html','<input type="button" value="Remove Lesson" onclick="remove_lesson(\'mon\','.$i.');">');
	     		}
	     		$mform->addElement('html', '</div>');
       		}
			$mform->addElement('html', '</div>');
       	$mform->addElement('html', '</div>');
       	
		$mform->addElement('advcheckbox', 'scheduledays[1]', 'Tue', null, array("group"=>1, 'onclick' =>"selectLesson(2,'tue',$cnt_var_tue);show_lesson2('tue');", "id"=>"tue"),'Tue');
    		if(count($arr_time['Tue']) > 0)
			{
			$mform->setDefault('scheduledays[1]',true);
			$mform->addElement('html', '<div class="qheader" id="lesson2" >');
			}
			else
			{
			$mform->addElement('html', '<div class="qheader" id="lesson2" style="display:none;">');
			}
		
		/*$mform->addElement('select', 'tue_lesson', '', $display_less, array('class'=>'lesson_sel', "onchange"=>"genrateLesson(this.value)","id"=>"lesson2", "style"=>"display:none;"));
        $mform->setType('mon_lesson', PARAM_INT);*/
		//$mform->addElement('html', '<div class="qheader" id="lesson2" style="display:none;">');
       	$mform->addElement('html', '<div id="tue_lesson">');
    		for($i=1;$i<$lesson;$i++){
       			$j=$i+1;
       			$k=$i-1;
       		
		     	if($i==1 || $i<=count($arr_time['Tue']))
		     	{
		     	$mform->addElement('html', '<div id="tuelesson_'.$i.'">');
		     	}
		     	else
		     	{
		     	$mform->addElement('html', '<div id="tuelesson_'.$i.'" style="display:none;">');
		     	}
		     	
			       	$mform->addElement('static','','','lesson'.$i);
			       	$mform->addElement('select', 'tue_starttime['.$k.']', 'Start time', $dispalytime, array("id"=>"tue_starttime_$i", "onclick" => "getendtime(this.id,'tue_starttime_".$j."');"));
					//$mform->addRule('mon_starttime[]', get_string('required'), 'required', null, 'client');
					$mform->setDefault('tue_starttime['.$k.']', $arr_time['Tue'][$k]);
			     	$mform->setType('tue_starttime[]', PARAM_RAW);
			     	$mform->addElement('html', '</div>');
			     	if($i==1 || $i<=count($arr_time['Tue'])){
			     	$mform->addElement('html', '<div id="tuebutton_'.$i.'">');
			     	}
			     	else
			     	{
			     	$mform->addElement('html', '<div id="tuebutton_'.$i.'" style="display:none;">');
			     	}
			     	/*if($arr_time['Tue']){
				     	if(count($arr_time['Tue'])>0 && count($arr_time['Tue'])<=$i){
			     		$mform->addElement('html','<input type="button" value="Add Lesson" onclick="show_lesson(\'tue\','.$i.');">');
				     	}
			     	}*/
			     	if($i>=1 && $i<$lesson-1)
			     	{
			     	$mform->addElement('html','<input type="button" value="Add Lesson" onclick="show_lesson(\'tue\','.$i.');">');
			     	}
		     		if($i>1){
		     		$mform->addElement('html','<input type="button" value="Remove Lesson" onclick="remove_lesson(\'tue\','.$i.');">');
		     		}
		     		$mform->addElement('html', '</div>');
		     	}
       		
			$mform->addElement('html', '</div>');
       	$mform->addElement('html', '</div>');
		
		$mform->addElement('advcheckbox', 'scheduledays[2]', 'Wed', null, array("group"=>1,'onclick' =>"selectLesson(3,'wed',$cnt_var_wed);", "id"=>"wed"),'Wed');
    	if(count($arr_time['Wed']) > 0)
			{
			$mform->setDefault('scheduledays[2]',true);
			$mform->addElement('html', '<div class="qheader" id="lesson3" >');
			}
			else
			{
			$mform->addElement('html', '<div class="qheader" id="lesson3" style="display:none;">');
			}
		//$mform->addElement('html', '<div class="qheader" id="lesson3" style="display:none;">');
       	$mform->addElement('html', '<div id="wed_lesson">');
    		for($i=1;$i<$lesson;$i++){
       			$j=$i+1;
       			$k=$i-1;
       			
		     	if( $i==1 || $i<=count($arr_time['Wed']))
		     	{
		     	$mform->addElement('html', '<div id="wedlesson_'.$i.'">');
		     	}
		     	else
		     	{
		     	$mform->addElement('html', '<div id="wedlesson_'.$i.'" style="display:none;">');
		     	}
		       	$mform->addElement('static','','','lesson'.$i);
		       	$mform->addElement('select', 'wed_starttime['.$k.']', 'Start time', $dispalytime, array("id"=>"wed_starttime_$i", "onclick" => "getendtime(this.id,'wed_starttime_".$j."');"));
				//$mform->addRule('mon_starttime[]', get_string('required'), 'required', null, 'client');
				$mform->setDefault('wed_starttime['.$k.']', $arr_time['Wed'][$k]);
		     	$mform->setType('wed_starttime[]', PARAM_RAW);
		     	$mform->addElement('html', '</div>');
		     	if($i==1 || $i<=count($arr_time['Wed'])){
		     	$mform->addElement('html', '<div id="wedbutton_'.$i.'">');
		     	}
		     	else
		     	{
		     	$mform->addElement('html', '<div id="wedbutton_'.$i.'" style="display:none;">');
		     	}
		     	if($i>=1 && $i<$lesson-1)
		     	{
		     	$mform->addElement('html','<input type="button" value="Add Lesson" onclick="show_lesson(\'wed\','.$i.');">');
		     	}
	     		if($i>1){
	     		$mform->addElement('html','<input type="button" value="Remove Lesson" onclick="remove_lesson(\'wed\','.$i.');">');
	     		}
	     		$mform->addElement('html', '</div>');
       		}
			$mform->addElement('html', '</div>');
       	$mform->addElement('html', '</div>');
		
		/*$mform->addElement('select', 'wed_lesson', '', $display_less, array('class'=>'lesson_sel', "onchange"=>"genrateLesson(this.value)","id"=>"lesson3", "style"=>"display:none;"));
        $mform->setType('mon_lesson', PARAM_INT);*/
		$mform->addElement('advcheckbox', 'scheduledays[3]', 'Thu', null, array("group"=>1,'onclick' =>"selectLesson(4,'thu',$cnt_var_thu);", "id"=>"thu"),'Thu');
    	if(count($arr_time['Thu']) > 0)
			{
			$mform->setDefault('scheduledays[3]',true);
			$mform->addElement('html', '<div class="qheader" id="lesson4" >');
			}
			else
			{
			$mform->addElement('html', '<div class="qheader" id="lesson4" style="display:none;">');
			}
		//$mform->addElement('html', '<div class="qheader" id="lesson4" style="display:none;">');
       	$mform->addElement('html', '<div id="thu_lesson">');
    	for($i=1;$i<$lesson;$i++){
       			$j=$i+1;
       			$k=$i-1;
       			
		     	if($i==1 || $i<=count($arr_time['Thu']))
		     	{
		     		$mform->addElement('html', '<div id="thulesson_'.$i.'">');
		     	}
		     	else
		     	{
		     	$mform->addElement('html', '<div id="thulesson_'.$i.'" style="display:none;">');
		     	}
		       	$mform->addElement('static','','','lesson'.$i);
		       	$mform->addElement('select', 'thu_starttime['.$k.']', 'Start time', $dispalytime, array("id"=>"thu_starttime_$i", "onclick" => "getendtime(this.id,'thu_starttime_".$j."');"));
				//$mform->addRule('mon_starttime[]', get_string('required'), 'required', null, 'client');
				$mform->setDefault('thu_starttime['.$k.']', $arr_time['Thu'][$k]);
		     	$mform->setType('thu_starttime[]', PARAM_RAW);
		     	$mform->addElement('html', '</div>');
		     	if($i==1 || $i<=count($arr_time['Thu'])){
		     	$mform->addElement('html', '<div id="thubutton_'.$i.'">');
		     	}
		     	else
		     	{
		     	$mform->addElement('html', '<div id="thubutton_'.$i.'" style="display:none;">');
		     	}
		     	
		     	if($i>=1 && $i<$lesson-1)
		     	{
		     	$mform->addElement('html','<input type="button" value="Add Lesson" onclick="show_lesson(\'thu\','.$i.');">');	
		     	}
	     		if($i>1){
	     		$mform->addElement('html','<input type="button" value="Remove Lesson" onclick="remove_lesson(\'thu\','.$i.');">');
	     		}
	     		$mform->addElement('html', '</div>');
       		}
			$mform->addElement('html', '</div>');
       	$mform->addElement('html', '</div>');
		/*$mform->addElement('select', 'thu_lesson', '', $display_less, array('class'=>'lesson_sel', "onchange"=>"genrateLesson(this.value)","id"=>"lesson4", "style"=>"display:none;"));
        $mform->setType('mon_lesson', PARAM_INT);*/
		$mform->addElement('advcheckbox', 'scheduledays[4]', 'Fri', null, array("group"=>1,'onclick' =>"selectLesson(5,'fri',$cnt_var_fri);", "id"=>"fri"),'Fri');
    	if(count($arr_time['Fri']) > 0)
			{
			$mform->setDefault('scheduledays[4]',true);
			$mform->addElement('html', '<div class="qheader" id="lesson5" >');
			}
			else
			{
			$mform->addElement('html', '<div class="qheader" id="lesson5" style="display:none;">');
			}
		
       	$mform->addElement('html', '<div id="fri_lesson">');
    	for($i=1;$i<$lesson;$i++){
       			$j=$i+1;
       			$k=$i-1;
       		
		     	if($i==1 || $i<=count($arr_time['Fri']))
		     	{
		     		$mform->addElement('html', '<div id="frilesson_'.$i.'">');
		     	}
		     	else
		     	{
		     	$mform->addElement('html', '<div id="frilesson_'.$i.'" style="display:none;">');
		     	}
		       	$mform->addElement('static','','','lesson'.$i);
		       	$mform->addElement('select', 'fri_starttime['.$k.']', 'Start time', $dispalytime, array("id"=>"fri_starttime_$i", "onclick" => "getendtime(this.id,'fri_starttime_".$j."');"));
				//$mform->addRule('mon_starttime[]', get_string('required'), 'required', null, 'client');
				$mform->setDefault('fri_starttime['.$k.']', $arr_time['Fri'][$k]);
		     	$mform->setType('fri_starttime[]', PARAM_RAW);
		     	$mform->addElement('html', '</div>');
		     	if($i==1 || $i<=count($arr_time['Fri'])){
		     	$mform->addElement('html', '<div id="fributton_'.$i.'">');
		     	}
		     	else
		     	{
		     	$mform->addElement('html', '<div id="fributton_'.$i.'" style="display:none;">');
		     	}
		     	/*if($arr_time['Fri']){
			     	if(count($arr_time['Fri'])>0 && count($arr_time['Fri'])<=$i){
		     		$mform->addElement('html','<input type="button" value="Add Lesson" onclick="show_lesson(\'fri\','.$i.');">');
			     	}
		     	}*/
		     	if($i>=1 && $i<$lesson-1)
		     	{
		     	$mform->addElement('html','<input type="button" value="Add Lesson" onclick="show_lesson(\'fri\','.$i.');">');	
		     	}
	     		if($i>1){
	     		$mform->addElement('html','<input type="button" value="Remove Lesson" onclick="remove_lesson(\'fri\','.$i.');">');
	     		}
	     		$mform->addElement('html', '</div>');
       		}
			$mform->addElement('html', '</div>');
       	$mform->addElement('html', '</div>');
        
		
		
		$mform->addElement('advcheckbox', 'scheduledays[5]', 'Sat', null, array("group"=>1,'onclick' =>"selectLesson(6,'sat',$cnt_var_sat);", "id"=>"sat"),'Sat');
    	if(count($arr_time['Sat']) > 0)
			{
			$mform->setDefault('scheduledays[5]',true);
			$mform->addElement('html', '<div class="qheader" id="lesson6" >');
			}
			else
			{
			$mform->addElement('html', '<div class="qheader" id="lesson6" style="display:none;">');
			}
       	$mform->addElement('html', '<div id="sat_lesson">');
    	for($i=1;$i<$lesson;$i++){
       			$j=$i+1;
       			$k=$i-1;
       			
		     	if($i==1 || $i<=count($arr_time['Sat']))
		     	{
		     		$mform->addElement('html', '<div id="satlesson_'.$i.'">');
		     	}
		     	else
		     	{
		     	$mform->addElement('html', '<div id="satlesson_'.$i.'" style="display:none;">');
		     	}
		       	$mform->addElement('static','','','lesson'.$i);
		       	$mform->addElement('select', 'sat_starttime['.$k.']', 'Start time', $dispalytime, array("id"=>"sat_starttime_$i", "onclick" => "getendtime(this.id,'sat_starttime_".$j."');"));
				//$mform->addRule('mon_starttime[]', get_string('required'), 'required', null, 'client');
				$mform->setDefault('sat_starttime['.$k.']', $arr_time['Sat'][$k]);
		     	$mform->setType('sat_starttime[]', PARAM_RAW);
		     	$mform->addElement('html', '</div>');
		     	if($i==1 || $i<=count($arr_time['Sat'])){
		     	$mform->addElement('html', '<div id="satbutton_'.$i.'">');
		     	}
		     	else
		     	{
		     	$mform->addElement('html', '<div id="satbutton_'.$i.'" style="display:none;">');
		     	}
		     	/*if($arr_time['Sat']) {
			     	if(count($arr_time['Sat'])>0 && count($arr_time['Sat'])<=$i){
		     		$mform->addElement('html','<input type="button" value="Add Lesson" onclick="show_lesson(\'sat\','.$i.');">');
			     	}
		     	}*/
		     	if($i>=1 && $i<$lesson-1) 
		     	{
		     	$mform->addElement('html','<input type="button" value="Add Lesson" onclick="show_lesson(\'sat\','.$i.');">');
		     	}
	     		if($i>1){
	     		$mform->addElement('html','<input type="button" value="Remove Lesson" onclick="remove_lesson(\'sat\','.$i.');">');
	     		}
	     		$mform->addElement('html', '</div>');
       		}
			$mform->addElement('html', '</div>');
       	$mform->addElement('html', '</div>');
		$mform->addElement('advcheckbox', 'scheduledays[6]', 'Sun', null, array("group"=>1,'onclick' =>"selectLesson(7,'sun',$cnt_var_sun);", "id"=>"sun"),'Sun');
    	if(count($arr_time['Sun']) > 0)
			{
			$mform->setDefault('scheduledays[6]',true);
			$mform->addElement('html', '<div class="qheader" id="lesson7" >');
			}
			else
			{
			$mform->addElement('html', '<div class="qheader" id="lesson7" style="display:none;">');
			}
       	$mform->addElement('html', '<div id="sun_lesson">');
    	for($i=1;$i<$lesson;$i++){
       			$j=$i+1;
       			$k=$i-1;
		     	if($i==1 || $i<=count($arr_time['Sun'])){
		     	$mform->addElement('html', '<div id="sunlesson_'.$i.'">');
		     	}
		     	else
		     	{
		     	$mform->addElement('html', '<div id="sunlesson_'.$i.'" style="display:none;">');
		     	}
		       	$mform->addElement('static','','','lesson'.$i);
		       	$mform->addElement('select', 'sun_starttime['.$k.']', 'Start time', $dispalytime, array("id"=>"sun_starttime_$i", "onclick" => "getendtime(this.id,'sun_starttime_".$j."');"));
				//$mform->addRule('mon_starttime[]', get_string('required'), 'required', null, 'client');
				$mform->setDefault('sun_starttime['.$k.']', $arr_time['Sun'][$k]);
		     	$mform->setType('sun_starttime[]', PARAM_RAW);
		     	$mform->addElement('html', '</div>');
		     	if($i==1 || $i<=count($arr_time['Sun'])){
		     	$mform->addElement('html', '<div id="sunbutton_'.$i.'">');
		     	}
		     	else
		     	{
		     	$mform->addElement('html', '<div id="sunbutton_'.$i.'" style="display:none;">');
		     	}
		     	/*if($arr_time['Sun']){
		     	if(count($arr_time['Sun'])>0 && count($arr_time['Sun'])<=$i){
	     		$mform->addElement('html','<input type="button" value="Add Lesson" onclick="show_lesson(\'sun\','.$i.');">');
		     	}}*/
		     	if($i>=1 && $i<$lesson-1)
		     	{
		     	$mform->addElement('html','<input type="button" value="Add Lesson" onclick="show_lesson(\'sun\','.$i.');">');	
		     	}
	     		if($i>1){
	     		$mform->addElement('html','<input type="button" value="Remove Lesson" onclick="remove_lesson(\'sun\','.$i.');">');
	     		}
	     		$mform->addElement('html', '</div>');
       		}
			$mform->addElement('html', '</div>');
       	$mform->addElement('html', '</div>');	

       	
     	/*foreach($time_arr as $tim)
		{
			switch($tim->class_day)
			{
				case 'Mon': 
							$mform->setDefault('scheduledays[0]',true);
							break;
				case 'Tue':
							$mform->setDefault('scheduledays[1]',true);
							
							break;
				case 'wed':
							$mform->setDefault('scheduledays[2]',true);
							break;
				case 'Thu':
							$mform->setDefault('scheduledays[3]',true);
							break;
				case 'Fri':
							$mform->setDefault('scheduledays[4]',true);
							break;
				case 'Sat':
							$mform->setDefault('scheduledays[5]',true);
							break;
				case 'Sun':
							$mform->setDefault('scheduledays[6]',true);
							break;
			}
		}*/
       
      //  $mform->addElement('select', 'weekday1', 'Week day1', $displaydays);
     //   $mform->addRule('weekday1', get_string('required'), 'required', null, 'client');
        /******* #234  Scheduling Class times - times should be intelligent. ****** modified by Pankaj *** 01/09/2012 ***/
      //  $mform->addElement('select', 'starttime', 'Start time', $dispalytime,array("onclick"=>"getendtime('id_starttime','id_endtime')"));
		/******* #234  Scheduling Class times - times should be intelligent. ****** modified by Pankaj *** 01/09/2012 ***/
      //  $mform->addRule('starttime', get_string('required'), 'required', null, 'client');
     // 	$mform->setType('starttime', PARAM_RAW);
		
      //  $mform->addElement('select', 'endtime', 'End time', $dispalytime);
     //   $mform->addRule('endtime', get_string('required'), 'required', null, 'client');
     //   $mform->setType('endtime', PARAM_RAW);
        
      //  $mform->addElement('select', 'weekday2', 'Week day2', $displaydays);
      //  $mform->addRule('weekday2', get_string('required'), 'required', null, 'client');
        /******* #234  Scheduling Class times - times should be intelligent. ****** modified by Pankaj *** 01/09/2012 ***/
      //  $mform->addElement('select', 'starttime2', 'Start time', $dispalytime,array("onclick"=>"getendtime('id_starttime2','id_endtime2')"));
		/******* #234  Scheduling Class times - times should be intelligent. ****** modified by Pankaj *** 01/09/2012 ***/
     //   $mform->addRule('starttime2', get_string('required'), 'required', null, 'client');
      //	$mform->setType('starttime2', PARAM_RAW);
        
        //$mform->addElement('select', 'endtime2', 'End time', $dispalytime);
       // $mform->addRule('endtime2', get_string('required'), 'required', null, 'client');
       // $mform->setType('endtime2', PARAM_RAW);
        
       /* $mform->addElement('passwordunmask', 'enrolmentkey', get_string('enrolmentkey', 'group'), 'maxlength="254" size="24"', get_string('enrolmentkey', 'group'));
        $mform->addHelpButton('enrolmentkey', 'enrolmentkey', 'group');
        $mform->setType('enrolmentkey', PARAM_RAW); */

      /*  if (!empty($CFG->gdversion)) {
            $options = array(get_string('no'), get_string('yes'));
            $mform->addElement('select', 'hidepicture', get_string('hidepicture'), $options);

            $mform->addElement('filepicker', 'imagefile', get_string('newpicture', 'group'));
            $mform->addHelpButton('imagefile', 'newpicture', 'group');
        }*/

        $mform->addElement('hidden','id');
        $mform->setType('id', PARAM_INT);

     /*  $mform->addElement('hidden','courseid');
        $mform->setType('courseid', PARAM_INT);
*/
        $this->add_action_buttons(array("onclick"=>''));
    }

    function validation($data, $files) {
        global $COURSE, $DB, $CFG;

        $errors = parent::validation($data, $files);
		
        $textlib = textlib_get_instance();

        $name = trim($data['name']);
        if ($data['id'] and $group = $DB->get_record('groups', array('id'=>$data['id']))) {
            if ($textlib->strtolower($group->name) != $textlib->strtolower($name)) {
                if (groups_get_group_by_name($COURSE->id,  $name)) {
                    $errors['name'] = get_string('groupnameexists', 'group', $name);
                }
            }

            if (!empty($CFG->groupenrolmentkeypolicy) and $data['enrolmentkey'] != '' and $group->enrolmentkey !== $data['enrolmentkey']) {
                // enforce password policy only if changing password
                $errmsg = '';
                if (!check_password_policy($data['enrolmentkey'], $errmsg)) {
                    $errors['enrolmentkey'] = $errmsg;
                }
            }

        } else if (groups_get_group_by_name($COURSE->id, $name)) {
            $errors['name'] = get_string('groupnameexists', 'group', $name);
        }
    
        //$element = new MoodleQuickForm_submitlink($scheduledays, 1);
		//$element->_js = 'alert(123);';
		//$element->_onclick = 'write your onclick call here, followed by "return false;"';
        
     /* $startdate1= explode(":",$data['starttime']);
      $enddate1= explode(":",$data['endtime']);
      $satrttime = mktime($startdate1[0],$startdate1[1],0,0,0,0);
   	  $endttime = mktime($enddate1[0],$enddate1[1],0,0,0,0);
   	  
   	   $startdate2= explode(":",$data['starttime2']);
      $enddate2= explode(":",$data['endtime2']);
      $satrttime2 = mktime($startdate2[0],$startdate2[1],0,0,0,0);
   	  $endttime2 = mktime($enddate2[0],$enddate2[1],0,0,0,0);
        
        if( $endttime <= $satrttime)
        {
        		$errors['endtime'] = get_string('groupendtime');
        }
        
     	$endtime = trim($data['endtime']);
     	
     	 if( $endttime2 <= $satrttime2)
        {
        		$errors['endtime2'] = get_string('groupendtime');
        }
        
     	$endtime = trim($data['endtime2']);
     	
   		 $date_diff= date('N',strtotime(substr($data['weekday2'],0,3))) - date('N',strtotime(substr($data['weekday1'],0,3)));
   		
        if(trim($data['weekday2']) == trim($data['weekday1']))
        {
        		$errors['weekday2'] = get_string('groupclassdaysame');
        }
        if($date_diff < 0)
        {
        	//$errors['weekday2'] = get_string('groupclassday2');
        }
        */
        return $errors;
    }

    function get_editor_options() {
        return $this->_customdata['editoroptions'];
    }
    
    
}
