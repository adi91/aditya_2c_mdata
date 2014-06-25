<?php

require_once($CFG->dirroot.'/user/filters/lib.php');

if (!defined('MAX_BULK_USERS')) {
    define('MAX_BULK_USERS', 2000);
}

function add_selection_all($ufiltering) {
    global $SESSION, $DB, $CFG,$USER;

 	if(is_siteadmin() || is_mhescordinator()){
    	list($sqlwhere, $params) = $ufiltering->get_sql_filter("id<>:exguest AND deleted <> 1", array('exguest'=>$CFG->siteguest));
    } else {
	     $filter = "id<>:exguest AND deleted <> 1";
		 $filter .= " AND ";
		 $usersites = explode("*",$USER->site_fk);
		 $c=0;
		 $countcenter = count($usersites);
		 foreach($usersites as $usersite){
			$filter .= " site_fk like '$usersite' or  site_fk like '%*$usersite' or  site_fk like '$usersite*%'";
			if($c < ($countcenter -1)){
				$filter .= " or ";
			}
			$c++;
		 }
		 
    	list($sqlwhere, $params) = $ufiltering->get_sql_filter($filter, array('exguest'=>$CFG->siteguest));
    }

    $rs = $DB->get_recordset_select('user', $sqlwhere, $params, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname');
    foreach ($rs as $user) {
        if (!isset($SESSION->bulk_users[$user->id])) {
            $SESSION->bulk_users[$user->id] = $user->id;
        }
    }
    $rs->close();
}

function get_selection_data($ufiltering) {
    global $SESSION, $DB, $CFG,$USER;

    // get the SQL filter
    if(is_siteadmin() || is_mhescordinator()){
    	list($sqlwhere, $params) = $ufiltering->get_sql_filter("id<>:exguest AND deleted <> 1", array('exguest'=>$CFG->siteguest));
    } else {
    	$filter = "id<>:exguest AND deleted <> 1";
		 $filter .= " AND ";
		 $usersites = explode("*",$USER->site_fk);
		 $c=0;
		 $countcenter = count($usersites);
		 foreach($usersites as $usersite){
			$filter .= " site_fk like '$usersite' or  site_fk like '%*$usersite' or  site_fk like '$usersite*%'";
			if($c < ($countcenter -1)){
				$filter .= " or ";
			}
			$c++;
		 }
		 
    	list($sqlwhere, $params) = $ufiltering->get_sql_filter($filter, array('exguest'=>$CFG->siteguest));
    }
    $total  = $DB->count_records_select('user', "id<>:exguest AND deleted <> 1", array('exguest'=>$CFG->siteguest));
    $acount = $DB->count_records_select('user', $sqlwhere, $params);
    $scount = count($SESSION->bulk_users);

    $userlist = array('acount'=>$acount, 'scount'=>$scount, 'ausers'=>false, 'susers'=>false, 'total'=>$total);
    $userlist['ausers'] = $DB->get_records_select_menu('user', $sqlwhere, $params, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname', 0, MAX_BULK_USERS);

    if ($scount) {
        if ($scount < MAX_BULK_USERS) {
            $in = implode(',', $SESSION->bulk_users);
        } else {
            $bulkusers = array_slice($SESSION->bulk_users, 0, MAX_BULK_USERS, true);
            $in = implode(',', $bulkusers);
        }
        $userlist['susers'] = $DB->get_records_select_menu('user', "id IN ($in)", null, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname');
    }

    return $userlist;
}
