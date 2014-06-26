<?php
 require_once("../config.php");
    require_once("lib.php");
	require_login();
	
 	$filename     = required_param('filename', PARAM_TEXT);
	header("Content-Type: application/csv\n");
    header("Content-Disposition: attachment; filename=$filename");
    readfile($CFG->wwwroot.'/'.$filename);
?>