<?php
    require_once('../config.php');
    require_once($CFG->dirroot .'/course/lib.php');
    require_once($CFG->libdir .'/filelib.php');
       require_once($CFG->libdir.'/completionlib.php');

   //    $reset_user_allowed_editing = true;
       
    redirect_if_major_upgrade_required();

    $urlparams = array();
    if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_MY) && optional_param('redirect', 1, PARAM_BOOL) === 0) {
        $urlparams['redirect'] = 0;
    }
    $PAGE->set_url('/', $urlparams);
    $PAGE->set_course($SITE);

    if ($CFG->forcelogin) {
        require_login();
    } else {
        user_accesstime_log();
    }

    $hassiteconfig = has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));

/// If the site is currently under maintenance, then print a message
    if (!empty($CFG->maintenance_enabled) and !$hassiteconfig) {
        print_maintenance_message();
    }

    if ($hassiteconfig && moodle_needs_upgrading()) {
        //redirect($CFG->wwwroot .'/'. $CFG->admin .'/index.php');
    }

    if (get_home_page() != HOMEPAGE_SITE) {
        // Redirect logged-in users to My Moodle overview if required
        if (optional_param('setdefaulthome', false, PARAM_BOOL)) {
            set_user_preference('user_home_page_preference', HOMEPAGE_SITE);
        } else if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_MY) && optional_param('redirect', 1, PARAM_BOOL) === 1) {
            redirect($CFG->wwwroot .'/my/');
        } else if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_USER)) {
            $PAGE->settingsnav->get('usercurrentsettings')->add(get_string('makethismyhome'), new moodle_url('/', array('setdefaulthome'=>true)), navigation_node::TYPE_SETTING);
        }
    }

    if (isloggedin()) {
        add_to_log(SITEID, 'course', 'view', 'view.php?id='.SITEID, SITEID);
    }

/// If the hub plugin is installed then we let it take over the homepage here
    if (get_config('local_hub', 'hubenabled') && file_exists($CFG->dirroot.'/local/hub/lib.php')) {
        require_once($CFG->dirroot.'/local/hub/lib.php');
        $hub = new local_hub();
        $continue = $hub->display_homepage();
        //display_homepage() return true if the hub home page is not displayed
        //mostly when search form is not displayed for not logged users
        if (empty($continue)) {
            exit;
        }
    }
    $PAGE->set_pagetype('site-index');
    $PAGE->set_other_editing_capability('moodle/course:manageactivities');
    $PAGE->set_docs_path('');
    $PAGE->set_pagelayout('frontpage');
  	$editing = $PAGE->user_is_editing();
    $PAGE->set_title($SITE->fullname);
    $PAGE->set_heading($SITE->fullname);
    echo $OUTPUT->header();
    $cid  = optional_param('cid', '', PARAM_ACTION);
	$sectionid  = optional_param('section', '', PARAM_ACTION);
    ?>
    <script language="javascript"> 
    
    function enter_pressed(e){
        var keycode;
        if (window.event) keycode = window.event.keyCode;
        else if (e) keycode = e.which;
        else return false;
        return (keycode == 13);
    }
    
    function search_from(from,cid){
	   alert(from+cid);
        var obj = pullAjax();
        obj.onreadystatechange = function(){
            if(obj.readyState == 4){
                var tmp = obj.responseText;
                if(tmp != ""){
                    document.combo_box.FromLB.length = 0;
                    document.combo_box.FromLB.innerHTML = tmp;
                }
            }
        }
        obj.open("GET","<?php echo $CFG->wwwroot; ?>/course/pullattendence/search-from.php?val="+from+"&cid="+cid,true);
        obj.send(null);
    }
    
    function search_to(to,cid){
        var obj = pullAjax();
        obj.onreadystatechange = function(){
            if(obj.readyState == 4){
                var tmp = obj.responseText;
                if(tmp != ""){
                    document.combo_box.ToLB.length = 0;
                    document.combo_box.ToLB.innerHTML = tmp;
                }
            }
        }
        obj.open("GET","<?php echo $CFG->wwwroot; ?>/course/pullattendence/search-to.php?val="+to+"&cid="+cid,true);
        obj.send(null);
    }
    
    function clearfrom(cid){
        var obj = pullAjax();
        obj.onreadystatechange = function(){
            if(obj.readyState == 4){
                var tmp = obj.responseText;
                if(tmp != ""){
                    document.combo_box.FromLB.length = 0;
                    document.combo_box.from.value = "";
                    document.combo_box.FromLB.innerHTML = tmp;
                }
            }
        }
        obj.open("GET","<?php echo $CFG->wwwroot; ?>/course/pullattendence/clear-from.php?cid="+cid,true);
        obj.send(null);
    }
    
    function clearto(cid){
        var obj = pullAjax();
        obj.onreadystatechange = function(){
            if(obj.readyState == 4){
                var tmp = obj.responseText;
                if(tmp != ""){
                    document.combo_box.ToLB.length = 0;
                    document.combo_box.to.value = "";
                    document.combo_box.ToLB.innerHTML = tmp;
                }
            }
        }
        obj.open("GET","<?php echo $CFG->wwwroot; ?>/course/pullattendence/clear-to.php?cid="+cid,true);
        obj.send(null);
    }
     
    function move(tbFrom, tbTo) 
    {
	   var arrFrom = new Array(); var arrTo = new Array(); 
        var arrLU = new Array();
        var i;
        for (i = 0; i < tbTo.options.length; i++) 
        {
            arrLU[tbTo.options[i].text] = tbTo.options[i].value;
            arrTo[i] = tbTo.options[i].text;
        }
        var fLength = 0;
        var tLength = arrTo.length;
        for(i = 0; i < tbFrom.options.length; i++) 
        {
            arrLU[tbFrom.options[i].text] = tbFrom.options[i].value;
            if (tbFrom.options[i].selected && tbFrom.options[i].value != "") 
            {
                arrTo[tLength] = tbFrom.options[i].text;
                tLength++;
            }
            else 
            {
                arrFrom[fLength] = tbFrom.options[i].text;
                fLength++;
            }
        }

        tbFrom.length = 0;
        tbTo.length = 0;
        var ii;

        for(ii = 0; ii < arrFrom.length; ii++) 
        {
        var no = new Option();
        no.value = arrLU[arrFrom[ii]];
        no.text = arrFrom[ii];
        tbFrom[ii] = no;
        }

        for(ii = 0; ii < arrTo.length; ii++) 
        {
        var no = new Option();
        no.value = arrLU[arrTo[ii]];
        no.text = arrTo[ii];
        tbTo[ii] = no;
        }
    }
    
    function present(t,c,s){
        var elem = document.combo_box.ToLB;
        var list = "";
        for(var i = 0; i < elem.options.length; ++i){
            list += elem.options[i].value + "@";
        }
       list = list.substring(0,list.length-1);
        var obj = pullAjax();
        obj.onreadystatechange = function(){
            if(obj.readyState == 4){
                var tmp = obj.responseText;
                if(tmp == ""){
                    document.getElementById('present').innerHTML = 0;
                    present2 = parseInt(document.getElementById('present').innerHTML);
                    document.getElementById('absent').innerHTML = parseInt(document.getElementById('total').value) - present2;
                }
                if(tmp != ""){
                    document.getElementById('present').innerHTML = tmp.split('@').length;
                    present2 = parseInt(document.getElementById('present').innerHTML);
                    document.getElementById('absent').innerHTML = parseInt(document.getElementById('total').value) - present2;
                }
            }
        }
        obj.open("GET","<?php echo $CFG->wwwroot; ?>/course/pullattendence/index.php?students="+list+"&teacher="+t+"&cid="+c+"&section="+s,true);
        obj.send(null);
    }
    
    function pullAjax()
    {
        var a;
        try
        {
            a=new XMLHttpRequest();
        }
        catch(b)
        {
            try
            {
                a=new ActiveXObject("Msxml2.XMLHTTP");
            }
            catch(b)
            {
                try
                {
                    a=new ActiveXObject("Microsoft.XMLHTTP");
                }
                catch(b)
                {
                    alert("Your browser broke!");return false;
                }
            }
        }
        return a;
    }
    
    </script>
    <style>
        .add{background-image:url("../theme/mghc2c/pix/add.jpg"); width:69px; height:33px; border:none; cursor:pointer;margin-bottom:5px;}
        .remove{background-image:url("../theme/mghc2c/pix/remove.jpg"); width:92px; height:32px; border:none; cursor:pointer;}
    </style>
    <?php
    
    
        $str3 = "SELECT studentid FROM mdl_custom_userpresent WHERE cid = ". $cid . " AND sectionid = ".$sectionid." AND teacherid = " . $USER->id;
        $getStudents = $DB->get_records_sql($str3);
        foreach($getStudents as $values2):
         $vals = $values2->studentid;
        endforeach;
         $stu = str_replace('@',',',$vals);
    
    //echo is_teacher();
    $str1 = 'SELECT course FROM mdl_class_activity WHERE groupid = '. $cid;
    $rows1 = $DB->get_records_sql($str1);
    foreach($rows1 as $values1):
        $courseid = $values1->course;
    endforeach;
   
    $coursecontext=get_context_instance(CONTEXT_COURSE, $courseid);
	$classmembers = $DB->get_records('groups_members',array('groupid'=>$_GET['cid']));
	$count_student = 0;
	$class  = $DB->get_record('groups',array('id'=>$classid));
	$user = array();
	foreach($classmembers as $classmember){
		$student = $DB->get_records('role_assignments', array('userid'=>$classmember->userid,'contextid'=>$coursecontext->id,'roleid'=>'5'));
		if($student){
			if(count($vals) && $vals != ""){
				$sql = "SELECT * from {user} where id not in ($stu) and id = $classmember->userid";
				if(!$user[$classmember->userid] = $DB->get_record_sql($sql)){
					unset($user[$classmember->userid]);
				}
			}else{
				$sql = "SELECT * from {user} where id = $classmember->userid";
				$user[$classmember->userid] = $DB->get_record_sql($sql);
			}			
		}
	}
	$rows2 = $user;
	//echo '<pre>';print_r($rows2);echo '</pre>';
     //die('chandra');
    
    $totalstr2 = 'SELECT u.id,u.firstname,u.lastname FROM `mdl_user` u
                    JOIN mdl_role_assignments ra ON u.id = ra.userid
                    JOIN mdl_role r ON ra.roleid = r.id
                    JOIN mdl_context c ON ra.contextid = c.id
                    WHERE c.contextlevel = 50
                    AND c.instanceid = '.$courseid.'
                    AND r.id = 5';
    $trow = $DB->get_records_sql($totalstr2);
   
    ?>
	<div class="c_box1wf" style="padding-top:10px;padding-bottom:20px;">
    <form name="combo_box">
    <input type="hidden" id="total" value="<?php echo count($rows2) ?>">   
    <table><tr><td><h2>Students - <span id="absent"><?php echo count($rows2); ?></span></h2>
	
    <select multiple size="10" name="FromLB" style="width:250px">
    <?php
    foreach($rows2 as $values2):
        echo '<option value="'.$values2->id.'">'.$values2->firstname . ' ' . $values2->lastname .'</option>';
        
    endforeach;
    ?>
    </select>
    </td>
    <td align="center" valign="middle">
    <input type="button" onClick="move(this.form.FromLB,this.form.ToLB);present(<?php echo $USER->id;  ?>,<?php echo $cid; ?>,<?php echo $sectionid;?> );" 
    value="" class="add"><br />
    <input type="button" onClick="move(this.form.ToLB,this.form.FromLB);present(<?php echo $USER->id;  ?>,<?php echo $cid; ?>,<?php echo $sectionid;?> );" 
    value="" class="remove">
    </td>
    <td>    
    <?php
        if($stu!=""):
            $strpre = "SELECT id, firstname, lastname FROM mdl_user WHERE id IN (".$stu.")";    
            $studprecount = count($DB->get_records_sql($strpre));
        else:
            $studprecount = 0;
        endif;    
    ?>
        <h2>Present - <span id="present"><?php echo $studprecount ?></span></h2>
    
    <select multiple size="10" name="ToLB" style="width:250px">
    <?php
       
        if($stu!=""):
            $str4 = "SELECT id, firstname, lastname FROM mdl_user WHERE id IN (".$stu.")";
            $getStudents2 = $DB->get_records_sql($str4);
            foreach($getStudents2 as $values3):
                echo '<option value='.$values3->id.'>'.$values3->firstname. ' '. $values3->lastname .'</option>';
            endforeach;
        endif;
    ?>
    </select>
    </td></tr>
    
        <tr>
            <td>Search:<br /><input type="text" name="from" value="" onBlur="if(enter_pressed(event)){ search_from(this.value,<?php echo $cid; ?>); }"><br /><input type="button" name="clrfrom" value="Clear" onclick="clearfrom(<?php echo $cid; ?>)" /></td>
            <td></td>
            <td>Search:<br /><input type="text" name="to" value="" onKeyPress="if(enter_pressed(event)){ search_to(this.value,<?php echo $cid; ?>); }"><br /><input type="button" name="clrto" value="Clear" onclick="clearto(<?php echo $cid; ?>)"  /></td>
        </tr>
    
    </table>
    </form>
    <a href="<?php echo $CFG->wwwroot; ?>"><img src="../theme/mghc2c/pix/back.jpg" width="133px" height="35px"/>
	</div>
    <?php  
    echo $OUTPUT->footer();
    ?>