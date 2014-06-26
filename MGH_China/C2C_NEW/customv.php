<?php
define('CENTERADMIN_ROLEID',10);
define('INSTRUCTOR_ROLEID',3);
define('NON_EDITING_INSTRUCTOR_ROLEID',4);
define('CENTERADMIN',10);
define('PARENTUSER',11);
define('STUDENT',5);
define('SITEADMIN' ,9);
define('INSTRUCTOR' ,3);
define('NONINSTRUCTOR' ,4);
$CFG->fullnamedisplay='firstname lastname';

$CFG->al_search_array = array(
							"cm\\:author",
							"cm\\:Title",
							"cm\\:Language",
							"cm\\:Name",
							"cm\\:Description",
							"custom\\:Language",
							"custom\\:Source",
							"custom\\:ContentID",
							"custom\\:Keyword",
							"custom\\:Version",
							"custom\\:Status",
							"custom\\:VersionComments",
							"custom\\:Audience",
							"custom\\:AgeRange",
							"custom\\:LevelofDifficulty",
							"custom\\:Division",
							"custom\\:Subdivision",
							"custom\\:Subject",
							"custom\\:Discipline",
							"custom\\:Course",
							"custom\\:Contributor",
							"custom\\:CopyrightYear",
							"custom\\:UseType",
							"custom\\:EvaluationMethod",
							"custom\\:Edition",
							"custom\\:CountryOfUse",
							"custom\\:ISBN10",
							"custom\\:ISBN13",
							"custom\\:Rights",
							"custom\\:Customer",
							"custom\\:Program",
							"custom\\:FileFormat",
							"custom\\:LearningObjective",
							"custom\\:RightsExpiryDate",
							"custom\\:CustomerEditable",
							"custom\\:UserRating");

$CFG->admin_home =array("index.php");
$CFG->admin_center= array("center.php","editcenter.php");
$CFG->admin_course =array("index.php","edit.php","editcategory.php","modedit.php","mod.php","view.php");
$CFG->admin_lesson= array("view.php");
$CFG->admin_users= array("user.php","index.php","user","editadvanced.php","user_bulk.php");

$CFG->mhes_home =array("view.php");
$CFG->mhes_center= array("center.php","editcenter.php");
$CFG->mhes_course =array("index.php","edit.php","editcategory.php","modedit.php","mod.php","category.php","index.php","group.php","groupings.php","grouping.php","assign.php","users.php","view.php");
$CFG->mhes_lesson= array("view.php");
$CFG->mhes_users= array("user.php","index.php","user","editadvanced.php","user_bulk.php");


$CFG->instco_home =array("view.php");
$CFG->instco_center= array("center.php","editcenter.php");
$CFG->instco_course =array("index.php","edit.php","editcategory.php","modedit.php","mod.php","category.php","index.php","group.php","groupings.php","grouping.php","assign.php","users.php","view.php");
$CFG->instco_lesson= array("view.php");
$CFG->instco_users= array("user.php","index.php","user","editadvanced.php","user_bulk.php");



$CFG->cadmin_home =array("index_centeradmin.php");
$CFG->cadmin_class= array("view_centeradmin.php","reschedule_lesson.php","group.php");
$CFG->cadmin_center= array("center.php");
$CFG->cadmin_users= array("user.php","index.php");
$CFG->cadmin_course =array("centeradmin_courseview.php","edit.php","index.php");
$CFG->cadmin_lesson= array("courset.php","editsection.php","modedit.php","mod.php","view.php");


$CFG->instructor_home =array("index_teacher.php","view.php");
$CFG->instructor_course =array("courseview.php");
$CFG->instructor_lesson= array("courset.php","editsection.php","modedit.php","mod.php","view.php");
$CFG->instructor_message=array("message");	
$CFG->instructor_profile=array("edit.php","profile.php");

$CFG->parent_home =array("index_parent.php");
$CFG->parent_course =array("parent_courseview.php");
$CFG->parent_message=array("message","index.php");


$CFG->student_home =array("index_student.php","index_student_c2c.php");
$CFG->student_course =array("courseview.php","courselesson.php");
$CFG->student_lesson= array("student_lesson.php","view.php");
$CFG->student_message=array("message","index.php");	
$CFG->student_profile=array("edit.php","profile.php");





?>