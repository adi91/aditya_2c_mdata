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
 * For most people, just lists the course categories
 * Allows the admin to create, delete and rename course categories
 *
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package course
 */

require_once("../../config.php");
require_once("../lib.php");
/// Everything else is editing on mode.
require_once($CFG->libdir.'/adminlib.php');


global $DB,$USER;
$cid = $_GET['cid'];

$str1 = 'SELECT course FROM mdl_class_activity WHERE id = '. $cid;
$rows1 = $DB->get_records_sql($str1);
foreach($rows1 as $values1):
    $course = $values1->course;
endforeach;

$str3 = "SELECT studentid FROM mdl_custom_userpresent WHERE cid = ". $cid . " AND teacherid = " . $USER->id;
$getStudents = $DB->get_records_sql($str3);
    foreach($getStudents as $values2):
        $vals = $values2->studentid;
    endforeach;
$stu = str_replace('@',',',$vals);

if(count($vals) && $vals != ""):
    $str2 = 'SELECT u.id,u.firstname,u.lastname FROM `mdl_user` u
                JOIN mdl_role_assignments ra ON u.id = ra.userid
                JOIN mdl_role r ON ra.roleid = r.id
                JOIN mdl_context c ON ra.contextid = c.id
                WHERE c.contextlevel = 50
                AND c.instanceid = '.$course.'
                AND r.id = 5 AND u.id IN ('. $stu .')';
    $rows2 = $DB->get_records_sql($str2);
    $options = "";
    foreach($rows2 as $values2):
    $options .= '<option value="'.$values2->id.'">'.$values2->firstname . ' ' . $values2->lastname .'</option>';
    endforeach;
    echo $options;
endif;
?>