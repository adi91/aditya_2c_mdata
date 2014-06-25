<?php

defined('MOODLE_INTERNAL') || die();

$sitessurl = $CFG->wwwroot.'/blocks/sites/index.php';

$sitestabs = array('base' => 'base',
    'create' => 'create');

if (!array_key_exists($this->action, $sitestabs)) {
    $row[] = new tabobject($this->action, $sitesurl.'?action='.$this->action, get_string('action'.$this->action, 'block_sites'));
}

foreach ($sitestabs as $actionname) {
    //$row[] = new tabobject($actionname, $sitessurl.'?action='.$actionname, get_string('action'.$actionname,'block_sites'));
    $row[] = new tabobject($actionname, $sitessurl.'?action='.$actionname, get_string('action'.$actionname,'block_sites'));
}

print_tabs(array($row), $this->action);
