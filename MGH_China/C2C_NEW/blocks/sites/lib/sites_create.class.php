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
class sites_create extends sites_base {


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

        $url = $CFG->wwwroot.'/blocks/sites/index.php?action=create&mode=execute';
        $this->moodleform = & new sites_create_form($url);
    }


    /**
     * Stores the preset into the DB
     */
    public function execute() {

        global $CFG, $USER, $DB;

        confirm_sesskey();

        $url = $CFG->wwwroot.'/blocks/sites/index.php?action=create&mode=execute';
        $this->moodleform = & new sites_create_form($url);

        // Reload site settings
        //$sitesettings = $this->_get_site_settings();

        if ($data = $this->moodleform->get_data()) {

            // admin_preset record
            $site->site_id = $data->siteid;
            $site->site_name = $data->name;
            $site->site_desc = $data->description;
            $site->site_author = $data->author;
            $site->site_created = time();
            $site->site_modified = time();
            if (!$site->site_pk = $DB->insert_record('sites', $site)) {
                print_error('errorinserting', 'block_sites');
            }
            else 
            {
            	redirect($CFG->wwwroot.'/blocks/sites/index.php');	
            }
         
        }

        
    }


    /**
     * To download system presets
     *
     * @return  xmlfile   preset file
     */
    public function download_xml() {

        global $DB;

        confirm_sesskey();

        if (!$preset = $DB->get_record('admin_preset', array('id' => $this->id))) {
            print_error('errornopreset', 'block_admin_presets');
        }

        if (!$items = $DB->get_records('admin_preset_item', array('adminpresetid' => $this->id))) {
            print_error('errornopreset', 'block_admin_presets');
        }

        // Start
        $xmloutput = new memory_xml_output();
        $xmlwriter = new xml_writer($xmloutput);
        $xmlwriter->start();

        // Preset data
        $xmlwriter->begin_tag('PRESET');
        foreach ($this->rel as $dbname => $xmlname) {
        	$xmlwriter->full_tag($xmlname, $preset->$dbname);
        }

        // We ride through the settings array
        $allsettings = $this->_get_settings_from_db($items);
        if ($allsettings) {

        	$xmlwriter->begin_tag('ADMIN_SETTINGS');

            foreach ($allsettings as $plugin => $settings) {

                $tagname = strtoupper($plugin);

                // To aviod xml slash problems
                if (strstr($tagname, '/') != false) {
                    $tagname = str_replace('/', '__', $tagname);
                }


                $xmlwriter->begin_tag($tagname);

                // One tag for each plugin setting
                if (!empty($settings)) {

                    $xmlwriter->begin_tag('SETTINGS');

                    foreach ($settings as $setting) {

                        // Unset the tag attributes string
                        $attributes = array();

                        // Getting setting attributes, if present
                        $attrs = $DB->get_records('admin_preset_item_attr', array('itemid' => $setting->itemid));
                        if ($attrs) {
                            foreach ($attrs as $attr) {
                                $attributes[$attr->name] = $attr->value;
                            }
                        }

                        $xmlwriter->full_tag(strtoupper($setting->name), $setting->value, $attributes);
                    }

                    $xmlwriter->end_tag('SETTINGS');
                }

                $xmlwriter->end_tag(strtoupper($tagname));
            }

            $xmlwriter->end_tag('ADMIN_SETTINGS');
        }

        // End
        $xmlwriter->end_tag('PRESET');
        
        $xmlwriter->stop();
        
        $xmlstr = $xmloutput->get_allcontents();

        $filename = addcslashes($preset->name, '"').'.xml';
        send_file($xmlstr, $filename, 0, 0, true, true);
    }

}
