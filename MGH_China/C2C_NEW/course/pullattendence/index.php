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
$sectionid = $_GET['section'];
$teacher = $_GET['teacher'];
$students = $_GET['students'];

$objcustomuserpresent = new stdClass();
$objcustomuserpresent->id= -1;
$objcustomuserpresent->cid = $cid;
$objcustomuserpresent->sectionid = $sectionid;
$objcustomuserpresent->teacherid = $teacher;
$objcustomuserpresent->studentid = $students;

$find = $DB->get_records_sql("SELECT * FROM mdl_custom_userpresent WHERE cid = ". $cid ." AND sectionid = ".$sectionid." AND teacherid = ". $teacher);
if(count($find)>0):
    foreach($find as $values):
        $updateval = new stdClass();
        $updateval->id = $values->id;
        $updateval->studentid = $students;
        $DB->update_record('custom_userpresent',$updateval);
    endforeach;
else:
    $DB->insert_record('custom_userpresent',$objcustomuserpresent);
endif;

$find2 = $DB->get_records_sql("SELECT * FROM mdl_custom_userpresent WHERE cid = ". $cid ." AND sectionid = ".$sectionid." AND teacherid = ". $teacher);
foreach($find2 as $values2):
    $updateval2 = $values2->studentid;
endforeach;
echo $updateval2;

?>
