<?php

require_once($CFG->dirroot.'/lib/formslib.php');


class sites_create_form extends moodleform {

    function definition () {

        global $CFG, $USER, $OUTPUT;

        $mform = & $this->_form;

        // Preset attributes
        $mform->addElement('header', 'general', get_string('sitesettings', 'block_sites'));

        $mform->addElement('text', 'name', get_string('name'), 'maxlength="254" size="60"');
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->setType('name', PARAM_RAW);
        
        $mform->registerRule('check_name', 'callback','checkname' );
		$mform->addRule('name', get_string('sitenameexists','block_sites'), 'check_name','-1', 'server');
		 $mform->setType('name', PARAM_RAW);
        
        $mform->addElement('text', 'siteid', get_string('siteid','block_sites'), 'maxlength="254" size="60"');
        $mform->addRule('siteid', null, 'required', null, 'client');
         $mform->setType('name', PARAM_RAW);
        
      	$mform->registerRule('check_siteid', 'callback','checksiteid' );
		$mform->addRule('siteid', get_string('siteidexists','block_sites'), 'check_siteid','-1', 'server');
        
        $mform->setType('siteid', PARAM_RAW);

        $mform->addElement('htmleditor', 'description', get_string('description'));
        $mform->setType('description', PARAM_CLEANHTML);

        $mform->addElement('text', 'author', get_string('siteauthor', 'block_sites'), 'maxlength="254" size="60"');
        $mform->setType('author', PARAM_RAW);
        $mform->setDefault('author', $USER->firstname.' '.$USER->lastname);
        
        //$mform->addElement('checkbox', 'excludesensiblesettings', get_string('autohidesensiblesettings', 'block_admin_presets'));

        // Moodle settings table
        //$mform->addElement('header', 'general', get_string('adminsettings', 'block_admin_presets'));
        //$mform->addElement('html', '<div id="settings_tree_div" class="ygtv-checkbox"><img src="'.$OUTPUT->pix_url('i/loading_small', 'core').'"/></div><br/>');

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
