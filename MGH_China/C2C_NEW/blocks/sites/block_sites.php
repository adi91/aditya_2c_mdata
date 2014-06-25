<?php

class block_sites extends block_list {


    function init() {
        $this->title = get_string('pluginname', 'block_sites');
    }


    function get_content() {

        global $CFG, $OUTPUT;

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new object();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if (!has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM))) {
            $this->content = '';
            return $this->content;
        }

                $this->content->items[] = '<img src="'.$OUTPUT->pix_url("i/menu").'" class="icon" alt="'.get_string('actionbase', 'block_sites').'" />
                                   <a title="'.get_string('actionbase', 'block_sites').'" href="'.$CFG->wwwroot.'/blocks/sites/index.php">
                                   '.get_string('actionbase', 'block_sites').'</a>';

        return $this->content;
    }


    function applicable_formats() {
        return array('site' => true);
    }

}
