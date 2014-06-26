<?php

require_once($CFG->dirroot.'/blocks/sites/lib/sites_base.class.php');
require_once($CFG->dirroot.'/backup/util/xml/xml_writer.class.php');
require_once($CFG->dirroot.'/backup/util/xml/output/xml_output.class.php');
require_once($CFG->dirroot.'/backup/util/xml/output/memory_xml_output.class.php');

/**
 * Class to export admin presets
 *
 * Reads the settings loaded by the system, the default
 * settings values and outputs the settings tree to
 * select what to export
 *
 * The form and config.php it's required by index.php
 *
 * @uses       backup
 *
 * @since      Moodle 2.0
 * @package    block/admin_presets
 * @copyright  2010 David Monllaó <david.monllao@urv.cat>
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt
 */
class sites_edit extends sites_base {


    /**
     * Shows the initial form to export/save admin settings
     *
     * Loads the database configuration and prints
     * the settings in a hierical table
     */
    public function show() {

        global $CFG, $PAGE;

        // Load site settings in the common format and do the js calls to populate the tree
        //$settings = $this->_get_site_settings();
        //$this->_get_settings_branches($settings);

        $url = $CFG->wwwroot.'/blocks/sites/index.php?action=edit&mode=execute';
        $this->moodleform = & new sites_edit_form($url);
    }


    /**
     * Stores the preset into the DB
     */
    public function execute() {

        global $CFG, $USER, $DB;

        confirm_sesskey();

        $url = $CFG->wwwroot.'/blocks/sites/index.php?action=edit&mode=execute';
        $this->moodleform = & new sites_edit_form($url);

        // Reload site settings
        //$sitesettings = $this->_get_site_settings();

        if ($data = $this->moodleform->get_data()) {

            // admin_preset record
            $site->id = $data->id;
            $site->site_id = $data->site_id;
            $site->site_name = $data->site_name;
            $site->site_desc = $data->site_desc;
            $site->site_author = $data->site_author;
            $site->site_modified = time();
            if (!$DB->update_record('sites', $site)) {
                print_error('errorupdating', 'block_sites');
            }
            else 
            {
            	redirect($CFG->wwwroot.'/blocks/sites/index.php');	
            }
         
        }

        
    }

}
