<?php

require_once($CFG->dirroot.'/lib/formslib.php');


class sites_edit_form extends moodleform {

    function definition () {

        global $CFG, $USER, $OUTPUT, $DB;

        $mform = & $this->_form;
        

        // Preset attributes
        $mform->addElement('header', 'general', get_string('sitesettings', 'block_sites'));

        $mform->addElement('text', 'site_name', get_string('name'), 'maxlength="254" size="60"');
        $mform->addRule('site_name', null, 'required', null, 'client');
        $mform->setType('site_name', PARAM_RAW);
        
        $mform->registerRule('check_name', 'callback','checkname' );
		
        
        $mform->addElement('text', 'site_id', get_string('siteid','block_sites'), 'maxlength="254" size="60"');
        $mform->addRule('site_id', null, 'required', null, 'client');
        $mform->setType('site_id', PARAM_RAW);
        
        $mform->registerRule('check_siteid', 'callback','checksiteid' );
		

        $mform->addElement('htmleditor', 'site_desc', get_string('description'));
        $mform->setType('site_desc', PARAM_CLEANHTML);

        $mform->addElement('text', 'site_author', get_string('siteauthor', 'block_sites'), 'maxlength="254" size="60"');
        $mform->setType('site_author', PARAM_RAW);
        $mform->setDefault('site_author', $USER->firstname.' '.$USER->lastname);
        
        $mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);
        
    	if(!$siteid = $mform->getElementValue('id'))
        {
        	$siteid = optional_param('id', 0, PARAM_INT); 
        }
        
        $mform->addRule('site_name', get_string('sitenameexists','block_sites'), 'check_name',$siteid, 'server');
        
        $mform->registerRule('check_siteid', 'callback','checksiteid' );
		  $mform->addRule('site_id', get_string('siteidexists','block_sites'), 'check_siteid',$siteid, 'server');
        
    	if ($siteid) {
            $site = $DB->get_record('sites', array('id'=>$siteid));
        } else {
            $site = false;
        }
        
        $this->set_data($site);
        

        // Submit
         $this->add_action_buttons(false,"Save changes");
        
    }
		
}

		function checkname($name,$id)
		{
			global $DB;
			$select =" id !='".$id."' and site_name='".mysql_escape_string($name)."'";
			if ($DB->record_exists_select('sites', $select)) 
			{
                return false;
            }
            else 
            {
            	return true;
            }
				
		}
		function checksiteid($siteid,$id)
		{
			global $DB;
			$select =" id !='".$id."' and site_id='".mysql_escape_string($siteid)."'";
			if ($DB->record_exists_select('sites', $select)) 
			{
                return false;
            }
            else 
            {
            	return true;
            }
		}
