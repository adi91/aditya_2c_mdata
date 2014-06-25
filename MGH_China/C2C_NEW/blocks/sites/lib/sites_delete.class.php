<?php

require_once($CFG->dirroot.'/blocks/sites/lib/sites_base.class.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->libdir.'/adminlib.php');


/**
 * Delete class
 *
 * @since      Moodle 2.0
 * @package    block/admin_presets
 * @copyright  2010 David Monllaó <david.monllao@urv.cat>
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt
 */
class sites_delete extends sites_base {


    /**
     * Shows a confirm box
     */
    public function show() {

        global $DB, $CFG, $OUTPUT;

        // Getting the preset name
        $sitedata = $DB->get_record('sites', array('id' => $this->id), 'site_name');
        $deletetext = get_string("deletesite", "block_sites", $sitedata->site_name);
        $confirmurl = $CFG->wwwroot.'/blocks/sites/index.php?action='.$this->action.'&mode=execute&id='.$this->id.'&sesskey='.sesskey();
        $cancelurl = $CFG->wwwroot.'/blocks/sites/index.php';

        $this->outputs = $OUTPUT->confirm($deletetext, $confirmurl, $cancelurl);
    }


    /**
     * Delete the DB preset
     */
    public function execute() {

        global $DB, $CFG;

        confirm_sesskey();

      
		/*$users_data = $DB->get_records('user', array('site_fk' => $this->id));
		foreach ($users_data as $user_data) 
		{
			user_delete_user($user_data);
		}*/
		 if (!$DB->delete_records('sites', array('id' => $this->id))) {
            print_error('errordeleting', 'block_sites');
        }
        redirect($CFG->wwwroot.'/blocks/sites/index.php');
    }

}
