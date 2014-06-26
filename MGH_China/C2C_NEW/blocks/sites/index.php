<?php

/**
 * Admin presets block main controller
 *
 * @package    blocks/admin_presets
 * @copyright  2010 David Monllaó <david.monllao@urv.cat>
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt
 */

require_once('../../config.php');

$action = optional_param('action', 'base', PARAM_ALPHA);
$mode = optional_param('mode', 'show', PARAM_ALPHAEXT);


require_login();

//die('s');

if (!$context = get_context_instance(CONTEXT_SYSTEM)) {
		
	print_error('wrongcontext', 'error');
}

require_capability('moodle/site:config', $context);


// Loads the required action class and form
$classname = 'sites_'.$action;
$formname = $classname.'_form';
$formpath = $CFG->dirroot.'/blocks/sites/forms/'.$formname.'.php';
require_once($CFG->dirroot.'/blocks/sites/lib/'.$classname.'.class.php');
if (file_exists($formpath)) {
    require_once($formpath);
}

if (!class_exists($classname)) {
    print_error('falseaction', 'block_sites', $action);
}

$url = new moodle_url('/blocks/sites/index.php');
$url->param('action', $action);
$url->param('mode', $mode);
$PAGE->set_url($url);

$PAGE->set_context($context);


// Executes the required action
$instance = new $classname();
if (!method_exists($instance, $mode)) {
	
    print_error('falsemode', 'block_sites', $mode);
}

// Executes the required method and displays output
$instance->$mode();
$instance->display();
$instance->log();
