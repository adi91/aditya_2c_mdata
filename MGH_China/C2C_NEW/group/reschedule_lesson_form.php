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
class reschedule_lesson_form extends moodleform {

    // Define the form
    function definition () {
        global $USER, $CFG, $COURSE,$DB;
        
        $displaylist = array();
		//$notused = array();
		
		//make_categories_list($displaylist, $notused);
		  /*  
		    if(!(is_siteadmin())){
				foreach($displaylist as $key=>$value){		
				   if(!(can_edit_in_category($key))){
				   		unset($displaylist[$key]);
				   }	
				}
		    }*/
       // $options = $displaylist;
      
       
        /*foreach($options as $key => $val)
		        {
		        	$courses = $DB->get_records('course',array('category'=>$key),'','id,shortname');
					
		        	foreach ($courses as $c => $value)
		        	{
		        		$displaycourse [$value->id] = $value->shortname;
		         	}
		         }*/
	
        $mform =& $this->_form;
        $editoroptions = $this->_customdata['editoroptions'];
		$id       = optional_param('id', 0, PARAM_INT);
		$classname = $DB->get_record('groups',array('id'=>$id));
		//print_r($classname->name);
		$mform->addElement('header', 'class_to_be_rescheduled', $classname->name);
		$displaycourse = array();
        $courses = $DB->get_records('course',array('id'=>$COURSE->id),'','id,shortname,category');
		foreach($courses as $course){			
			if($course->category && $category_context =  context_coursecat::instance($course->category)){
				$check_role_assignment = $DB->get_record('role_assignments',array('roleid'=>10,'contextid'=>$category_context->id,'userid'=>$USER->id));
			}			
			if($check_role_assignment || is_siteadmin()){
				//if($course->id == $COURSE->id){
					$displaycourse[$course->id] = $course->shortname;
				//}
			}
		}
		
        $mform->addElement('select', 'courseid', 'Select Course', $displaycourse);
        $mform->addRule('courseid', get_string('required'), 'required', null, 'client');
		$mform->setType('courseid', PARAM_INT);
		
		$displaysection = array();
		$sections = get_all_sections($COURSE->id);
		foreach($sections as $section){
			if($section->name == ''){
				$sectiondisplayname = get_string('section').' '.$section->section;				
			}else{
				$sectiondisplayname = $section->name;
			}
			if($section->summary != ''){
				$sectiondisplayname .= ' ('.substr($section->summary,0,50).')';
			}
		    if($section->section == 0){
			    continue;
			}
			if($section->name && $section->sequence){			    
				$displaysection[$section->id] = $sectiondisplayname;
			}elseif($section->name == '' && $section->sequence){
				$displaysection[$section->id] = $sectiondisplayname;
			}
		}
		$mform->addElement('select', 'section', 'Select Lesson', $displaysection);
        $mform->addRule('section', get_string('required'), 'required', null, 'client');
		$mform->setType('section', PARAM_INT);
		
        $mform->addElement('date_time_selector', 'startdate', get_string('class_startdate'));
        $mform->addHelpButton('startdate', 'startdate');
        $mform->setDefault('startdate', userdate(time()));
        $mform->setType('startdate', PARAM_MULTILANG);
        
       
        

        $mform->addElement('hidden','id');
        $mform->setType('id', PARAM_INT);

     /*  $mform->addElement('hidden','courseid');
        $mform->setType('courseid', PARAM_INT);
*/
        $this->add_action_buttons();
    }

    function validation($data, $files) {
        global $COURSE, $DB, $CFG;

        $errors = parent::validation($data, $files);

        $textlib = textlib_get_instance();

        $name = trim($data['name']);
        if ($data['id'] and $group = $DB->get_record('groups', array('id'=>$data['id']))) {           

        } else if (groups_get_group_by_name($COURSE->id, $name)) {
            $errors['name'] = get_string('groupnameexists', 'group', $name);
        }
        
     
        
        return $errors;
    }

    function get_editor_options() {
        return $this->_customdata['editoroptions'];
    }
}
