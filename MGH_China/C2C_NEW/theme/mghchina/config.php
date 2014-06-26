<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Configuration for Moodle's standard theme.
 *
 * This theme is the default theme within Moodle 2.0, it builds upon the base theme
 * adding only CSS to create the simple look and feel Moodlers have come to recognise.
 *
 * For full information about creating Moodle themes, see:
 *  http://docs.moodle.org/dev/Themes_2.0
 *
 * @package   moodlecore
 * @copyright 2010 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$THEME->name = 'mghchina';
$THEME->parents = array('');
$THEME->sheets = array(
   'pagelayout',   /** Must come first: Page layout **/
   'core',         /** Must come second: General styles **/
   'admin',
   'blocks',
   'calendar',
   'course',
   'dock',
   'grade',
   'message',
   'question',
   'user',	
   'modules',    
   'css3',	/** Sets up CSS 3 + browser specific styles **/
   'main',
   'boxstyle'
);
$THEME->enable_dock = false;
$THEME->editor_sheets = array('editor');

$THEME->layouts = array(
    // Most backwards compatible layout without the blocks - this is the layout used by default
    'base' => array(
        'file' => 'general.php',
         'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    // Standard layout with blocks, this is recommended for most pages with general information
    'standard' => array(
        'file' => 'general.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    // Main course page
    'course' => array(
        'file' => 'general.php',
        'regions' => array('side-pre'),
           'defaultregion' => 'side-pre',
       // 'options' => array('langmenu'=>true),
    ),
    'coursecategory' => array(
        'file' => 'general.php',
        'regions' => array('side-pre'),
        // 'defaultregion' => 'side-post',
    ),
    // part of course, typical for modules - default page layout if $cm specified in require_login()
    'incourse' => array(
        'file' => 'general.php',
        'regions' => array('side-pre'),
    	//'options' => array('nofooter'=>true, 'nonavbar'=>true, 'nocustommenu'=>true ),
        // 'defaultregion' => 'side-post',
    ),
    'incourse_stud' => array(
        'file' => 'embedded.php',
        'regions' => array('side-pre'),
    	'options' => array('nofooter'=>true, 'nonavbar'=>true, 'nocustommenu'=>true ,'noheaders'=>true),
        // 'defaultregion' => 'side-post',
    ),
    // The site home page.
    'frontpage' => array(
        'file' => 'frontpage.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
	'frontpage_student' => array(
        'file' => 'frontpage_student.php',
        'regions' => array('side-pre'),
          'defaultregion' => 'side-pre',
    ),
	'frontpage_parent' => array(
        'file' => 'frontpage_parent.php',
        'regions' => array('side-pre'),
          'defaultregion' => 'side-pre',
    ),
	// Server administration scripts.
    'admin' => array(
        'file' => 'general.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    // My dashboard page
    'mydashboard' => array(
        'file' => 'general.php',
        'regions' => array('side-pre'),
        // 'defaultregion' => 'side-post',
        'options' => array('langmenu'=>true),
    ),
    // My public page
    'mypublic' => array(
        'file' => 'general.php',
        'regions' => array('side-pre'),
        // 'defaultregion' => 'side-post',
    ),
    'login' => array(
        'file' => 'general_login.php',
        'regions' => array(),
        'options' => array('langmenu'=>true),
    ),

    // Pages that appear in pop-up windows - no navigation, no blocks, no header.
    'popup' => array(
        'file' => 'general.php',
        'regions' => array(),
        'options' => array('nofooter'=>true, 'nonavbar'=>true, 'nocustommenu'=>true, 'nologininfo'=>true),
    ),
    // No blocks and minimal footer - used for legacy frame layouts only!
    'frametop' => array(
        'file' => 'general.php',
        'regions' => array(),
        'options' => array('nofooter'=>true),
    ),
    // Embeded pages, like iframe/object embeded in moodleform - it needs as much space as possible
    'embedded' => array(
        'file' => 'embedded.php',
        'regions' => array(),
        'options' => array('nofooter'=>true, 'nonavbar'=>true, 'nocustommenu'=>true),
    ),
    // Used during upgrade and install, and for the 'This site is undergoing maintenance' message.
    // This must not have any blocks, and it is good idea if it does not have links to
    // other places - for example there should not be a home link in the footer...
    'maintenance' => array(
        'file' => 'general.php',
        'regions' => array(),
        'options' => array('noblocks'=>true, 'nofooter'=>true, 'nonavbar'=>true, 'nocustommenu'=>true),
    ),
    // Should display the content and basic headers only.
    'print' => array(
        'file' => 'general.php',
        'regions' => array(),
        'options' => array('noblocks'=>true, 'nofooter'=>true, 'nonavbar'=>false, 'nocustommenu'=>true),
    ),
    // The pagelayout used when a redirection is occuring.
    'redirect' => array(
        'file' => 'embedded.php',
        'regions' => array(),
        'options' => array('nofooter'=>true, 'nonavbar'=>true, 'nocustommenu'=>true),
    ),
    // The pagelayout used for reports
    'report' => array(
        'file' => 'report.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
     'incourse_cust' => array(
        'file' => 'general_cust.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
     	'options' => array('nofooter'=>true, 'nonavbar'=>true, 'nocustommenu'=>true, 'noheader'=>true),
    ),
    
);

// We don't want the base theme to be shown on the theme selection screen, by setting
// this to true it will only be shown if theme designer mode is switched on.
$THEME->hidefromselector = false;

/** List of javascript files that need to included on each page */
$THEME->javascripts = array();
$THEME->javascripts_footer = array();
