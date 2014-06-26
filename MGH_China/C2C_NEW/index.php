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
 * Moodle frontpage.
 *
 * @package    core
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    if (!file_exists('./config.php')) {
        header('Location: install.php');
        die;
    }

    require_once('./config.php');
    require_once($CFG->dirroot .'/course/lib.php');
    require_once($CFG->libdir .'/filelib.php');

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
        redirect($CFG->wwwroot .'/'. $CFG->admin .'/index.php');
    }
	//print_r($_SESSION['user_role']);
	
	$_SESSION['user_role'] = ''; 
	if(is_mhescordinator()){
		$redirect_string ='';
		$redirect_string .= $CFG->wwwroot;
		$redirect_string .= '/course/view.php?';
		$courses = get_courses();
		foreach($courses as $course){
			if($course->category != 0){
				$redirect_string .= 'catid='.$course->category;
				$redirect_string .= '&id='.$course->id;
				break;
			}
		}
		$redirect_string .= '&edit=on&sesskey='.$USER->sesskey;
		//echo $redirect_string;
		//die();
		redirect($redirect_string);
	}
	if(is_centeradmin()){
	   
	    $redirect_string ='';
		$redirect_string .= $CFG->wwwroot;
		$redirect_string .= '/index_centeradmin.php?view=day';
		if($_GET['cal_d']){
			$redirect_string .= '&cal_d='.$_GET['cal_d'];
		}else{
			$redirect_string .= '&cal_d='.date('d');
		}
		if($_GET['cal_m']){
			$redirect_string .= '&cal_m='.$_GET['cal_m'];
		}else{
			$redirect_string .= '&cal_m='.date('m');
		}
		if($_GET['cal_y']){
			$redirect_string .= '&cal_y='.$_GET['cal_y'];
		}else{
			$redirect_string .= '&cal_y='.date('Y');
		}
		redirect($redirect_string);
	}
	if(is_institute_cordinator()){
	   
	    $redirect_string ='';
		$redirect_string .= $CFG->wwwroot;
		$redirect_string .= '/calendar/view.php?view=day';
		if($_GET['cal_d']){
			$redirect_string .= '&cal_d='.$_GET['cal_d'];
		}else{
			$redirect_string .= '&cal_d='.date('d');
		}
		if($_GET['cal_m']){
			$redirect_string .= '&cal_m='.$_GET['cal_m'];
		}else{
			$redirect_string .= '&cal_m='.date('m');
		}
		if($_GET['cal_y']){
			$redirect_string .= '&cal_y='.$_GET['cal_y'];
		}else{
			$redirect_string .= '&cal_y='.date('Y');
		}
		redirect($redirect_string);
	}
	if(is_teacher() || is_non_editing_teacher()){
		$redirect_string ='';
		$redirect_string .= $CFG->wwwroot;
		$redirect_string .= '/index_teacher.php?view=day2';
		if($_GET['cal_d']){
			$redirect_string .= '&cal_d='.$_GET['cal_d'];
		}else{
			$redirect_string .= '&cal_d='.date('d');
		}
		if($_GET['cal_m']){
			$redirect_string .= '&cal_m='.$_GET['cal_m'];
		}else{
			$redirect_string .= '&cal_m='.date('m');
		}
		if($_GET['cal_y']){
			$redirect_string .= '&cal_y='.$_GET['cal_y'];
		}else{
			$redirect_string .= '&cal_y='.date('Y');
		}
		redirect($redirect_string);
	    //redirect($CFG->wwwroot.'/index_teacher.php?view=day2&cal_d='.date('d').'&cal_m='.date('m').'&cal_y='.date('Y'));
	}
    if(is_parent()){
		$redirect_string ='';
		$redirect_string .= $CFG->wwwroot;
		$redirect_string .= '/index_parent.php?view=day2';
		if($_GET['cal_d']){
			$redirect_string .= '&cal_d='.$_GET['cal_d'];
		}else{
			$redirect_string .= '&cal_d='.date('d');
		}
		if($_GET['cal_m']){
			$redirect_string .= '&cal_m='.$_GET['cal_m'];
		}else{
			$redirect_string .= '&cal_m='.date('m');
		}
		if($_GET['cal_y']){
			$redirect_string .= '&cal_y='.$_GET['cal_y'];
		}else{
			$redirect_string .= '&cal_y='.date('Y');
		}
		redirect($redirect_string);
		//redirect($CFG->wwwroot.'/index_parent.php?view=day2&cal_d='.date('d').'&cal_m='.date('m').'&cal_y='.date('Y'));
	}	
	if(is_student()){
		/*$redirect_string ='';
		$redirect_string .= $CFG->wwwroot;
		$redirect_string .= '/index_student.php?view=day2';
		if($_GET['cal_d']){
			$redirect_string .= '&cal_d='.$_GET['cal_d'];
		}else{
			$redirect_string .= '&cal_d='.date('d');
		}
		if($_GET['cal_m']){
			$redirect_string .= '&cal_m='.$_GET['cal_m'];
		}else{
			$redirect_string .= '&cal_m='.date('m');
		}
		if($_GET['cal_y']){
			$redirect_string .= '&cal_y='.$_GET['cal_y'];
		}else{
			$redirect_string .= '&cal_y='.date('Y');
		}
		redirect($redirect_string);*/
		if($CFG->theme == 'mghc2c'){
			redirect($CFG->wwwroot.'/index_student_c2c.php');
		}else{
			redirect($CFG->wwwroot.'/index_student.php');
		}
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
     if(!is_siteadmin() && !is_mhescordinator()){
	   if($CFG->theme = 'mghc2c'){
			$roleid = 13;
		}else{
			$roleid = 10;
		}
	   $sql= "SELECT * FROM {user} u
				JOIN {role_assignments} ra ON ra.userid= u.id 
				WHERE ra.contextid !=1  AND ra.roleid=$roleid";
	   $user_sites = explode("*",$USER->site_fk);
	   $c=0;
	   $count_site = count($user_sites);
	   if($user_sites){
		   $sql .= " AND (";
		   foreach($user_sites as $user_site){
			$sql .= "(u.site_fk like '$user_site' or  u.site_fk like '%*$user_site' or u.site_fk like '$user_site*%')";
			if($c < ($count_site -1)){
				$sql .= " or ";
			}
			$c++;
		   }
		    $sql .= ") ";
		}

		//$param= array(10);
		$centeradmins = $DB->get_records_sql($sql);
		//print_r($centeradmins);
		
		$courses = enrol_get_users_courses($rs->userid,true,'*', 'visible DESC,sortorder ASC',$searchcourse);
		foreach($courses as $course) {
			$courseid=$course->id;
		} 
		echo html_writer::start_tag('h4');
			if($CFG->theme == 'mghc2c'){
				echo $course->shortname ." ". get_string('norolemessagec2c');
			}else{
				echo $course->shortname ." ". get_string('norolemessage');
			}
		echo html_writer::start_tag('h4');
		$ca = array();
		foreach($centeradmins as $rs){
			//echo '<pre>';print_r($rs->userid);print_r($ca);echo '</pre>';
			if(!(in_array($rs->userid,$ca))){
				echo html_writer::start_tag('div', array('class'=>'sectionmessage'));	            
		            	echo html_writer::start_tag('div', array('class'=>'messagebox'));
			            		echo html_writer::start_tag('div', array('class'=>'topbx'));
			            			echo html_writer::start_tag('div', array('class'=>'topbx'));
			            				echo html_writer::start_tag('div', array('class'=>'imageborder'));
			                				print_user_picture($rs->userid, $courseid, 1, 80, false, false);
			                			echo html_writer::end_tag('div');
										echo html_writer::start_tag('div', array('class'=>'topbx-ri'));
			                				echo fullname($rs,true);
			                			echo html_writer::end_tag('div');
			                		echo html_writer::end_tag('div');		
			                	echo html_writer::end_tag('div');
			                	echo html_writer::start_tag('div', array('colspan'=>'2','class'=>'blline','align'=>'center'));
			                	echo "<a href='".$CFG->wwwroot."/message/index.php?id=".$rs->userid."'><input name='Send Message' type='button' class='sendmessagebtn' onClick=window.location.href('".$CFG->wwwroot."/message/index.php?id=".$rs->userid."'); /></a>";
			                	echo html_writer::end_tag('div');
			                echo html_writer::end_tag('div');
			    echo html_writer::end_tag('div');
				$ca[] = $rs->userid;
			}
		}
		
		
	}else{
/// Print Section or custom info
    if (!empty($CFG->customfrontpageinclude)) {
        include($CFG->customfrontpageinclude);

    } else if ($SITE->numsections > 0) {

        if (!$section = $DB->get_record('course_sections', array('course'=>$SITE->id, 'section'=>1))) {
            $DB->delete_records('course_sections', array('course'=>$SITE->id, 'section'=>1)); // Just in case
            $section->course = $SITE->id;
            $section->section = 1;
            $section->summary = '';
            $section->summaryformat = FORMAT_HTML;
            $section->sequence = '';
            $section->visible = 1;
            $section->id = $DB->insert_record('course_sections', $section);
        }

        if (!empty($section->sequence) or !empty($section->summary) or $editing) {
            echo $OUTPUT->box_start('generalbox sitetopic');

            /// If currently moving a file then show the current clipboard
            if (ismoving($SITE->id)) {
                $stractivityclipboard = strip_tags(get_string('activityclipboard', '', $USER->activitycopyname));
                echo '<p><font size="2">';
                echo "$stractivityclipboard&nbsp;&nbsp;(<a href=\"course/mod.php?cancelcopy=true&amp;sesskey=".sesskey()."\">". get_string('cancel') .'</a>)';
                echo '</font></p>';
            }

            $context = get_context_instance(CONTEXT_COURSE, SITEID);
            $summarytext = file_rewrite_pluginfile_urls($section->summary, 'pluginfile.php', $context->id, 'course', 'section', $section->id);
            $summaryformatoptions = new stdClass();
            $summaryformatoptions->noclean = true;
            $summaryformatoptions->overflowdiv = true;

            echo format_text($summarytext, $section->summaryformat, $summaryformatoptions);

            if ($editing) {
                $streditsummary = get_string('editsummary');
                echo "<a title=\"$streditsummary\" ".
                     " href=\"course/editsection.php?id=$section->id\"><img src=\"" . $OUTPUT->pix_url('t/edit') . "\" ".
                     " class=\"iconsmall\" alt=\"$streditsummary\" /></a><br /><br />";
            }

            get_all_mods($SITE->id, $mods, $modnames, $modnamesplural, $modnamesused);
            print_section($SITE, $section, $mods, $modnamesused, true);

            if ($editing) {
                print_section_add_menus($SITE, $section->section, $modnames);
            }
            echo $OUTPUT->box_end();
        }
    }

    if (isloggedin() and !isguestuser() and isset($CFG->frontpageloggedin)) {
        $frontpagelayout = $CFG->frontpageloggedin;
    } else {
        $frontpagelayout = $CFG->frontpage;
    }

    foreach (explode(',',$frontpagelayout) as $v) {
        switch ($v) {     /// Display the main part of the front page.
            case FRONTPAGENEWS:
                if ($SITE->newsitems) { // Print forums only when needed
                    require_once($CFG->dirroot .'/mod/forum/lib.php');

                    if (! $newsforum = forum_get_course_forum($SITE->id, 'news')) {
                        print_error('cannotfindorcreateforum', 'forum');
                    }

                    // fetch news forum context for proper filtering to happen
                    $newsforumcm = get_coursemodule_from_instance('forum', $newsforum->id, $SITE->id, false, MUST_EXIST);
                    $newsforumcontext = get_context_instance(CONTEXT_MODULE, $newsforumcm->id, MUST_EXIST);

                    $forumname = format_string($newsforum->name, true, array('context' => $newsforumcontext));
                    echo html_writer::tag('a', get_string('skipa', 'access', moodle_strtolower(strip_tags($forumname))), array('href'=>'#skipsitenews', 'class'=>'skip-block'));

                    if (isloggedin()) {
                        $SESSION->fromdiscussion = $CFG->wwwroot;
                        $subtext = '';
                        if (forum_is_subscribed($USER->id, $newsforum)) {
                            if (!forum_is_forcesubscribed($newsforum)) {
                                $subtext = get_string('unsubscribe', 'forum');
                            }
                        } else {
                            $subtext = get_string('subscribe', 'forum');
                        }
                        echo $OUTPUT->heading($forumname, 2, 'headingblock header');
                        $suburl = new moodle_url('/mod/forum/subscribe.php', array('id' => $newsforum->id, 'sesskey' => sesskey()));
                        echo html_writer::tag('div', html_writer::link($suburl, $subtext), array('class' => 'subscribelink'));
                    } else {
                        echo $OUTPUT->heading($forumname, 2, 'headingblock header');
                    }

                    forum_print_latest_discussions($SITE, $newsforum, $SITE->newsitems, 'plain', 'p.modified DESC');
                    echo html_writer::tag('span', '', array('class'=>'skip-block-to', 'id'=>'skipsitenews'));
                }
            break;

            case FRONTPAGECOURSELIST:
                if (isloggedin() and !$hassiteconfig and !isguestuser() and empty($CFG->disablemycourses)) {
                    echo html_writer::tag('a', get_string('skipa', 'access', moodle_strtolower(get_string('mycourses'))), array('href'=>'#skipmycourses', 'class'=>'skip-block'));
                    echo $OUTPUT->heading(get_string('mycourses'), 2, 'headingblock header');
                    print_my_moodle();
                    echo html_writer::tag('span', '', array('class'=>'skip-block-to', 'id'=>'skipmycourses'));
                } else if ((!$hassiteconfig and !isguestuser()) or ($DB->count_records('course') <= FRONTPAGECOURSELIMIT)) {
                    // admin should not see list of courses when there are too many of them
                    echo html_writer::tag('a', get_string('skipa', 'access', moodle_strtolower(get_string('availablecourses'))), array('href'=>'#skipavailablecourses', 'class'=>'skip-block'));
                    echo $OUTPUT->heading(get_string('availablecourses'), 2, 'headingblock header');
                    print_courses(0);
                    echo html_writer::tag('span', '', array('class'=>'skip-block-to', 'id'=>'skipavailablecourses'));
                }
            break;

            case FRONTPAGECATEGORYNAMES:
                echo html_writer::tag('a', get_string('skipa', 'access', moodle_strtolower(get_string('categories'))), array('href'=>'#skipcategories', 'class'=>'skip-block'));
                echo $OUTPUT->heading(get_string('categories'), 2, 'headingblock header');
                echo $OUTPUT->box_start('generalbox categorybox');
                print_whole_category_list(NULL, NULL, NULL, -1, false);
                echo $OUTPUT->box_end();
                print_course_search('', false, 'short');
                echo html_writer::tag('span', '', array('class'=>'skip-block-to', 'id'=>'skipcategories'));
            break;

            case FRONTPAGECATEGORYCOMBO:
                echo html_writer::tag('a', get_string('skipa', 'access', moodle_strtolower(get_string('courses'))), array('href'=>'#skipcourses', 'class'=>'skip-block'));
                echo $OUTPUT->heading(get_string('courses'), 2, 'headingblock header');
                $renderer = $PAGE->get_renderer('core','course');
                // if there are too many courses, budiling course category tree could be slow,
                // users should go to course index page to see the whole list.
                $coursecount = $DB->count_records('course');
                if (empty($CFG->numcoursesincombo)) {
                    // if $CFG->numcoursesincombo hasn't been set, use default value 500
                    $CFG->numcoursesincombo = 500;
                }
                if ($coursecount > $CFG->numcoursesincombo) {
                    $link = new moodle_url('/course/');
                    echo $OUTPUT->notification(get_string('maxnumcoursesincombo', 'moodle', array('link'=>$link->out(), 'maxnumofcourses'=>$CFG->numcoursesincombo, 'numberofcourses'=>$coursecount)));
                } else {
                    echo $renderer->course_category_tree(get_course_category_tree());
                }
                print_course_search('', false, 'short');
                echo html_writer::tag('span', '', array('class'=>'skip-block-to', 'id'=>'skipcourses'));
            break;

            case FRONTPAGETOPICONLY:    // Do nothing!!  :-)
            break;

        }
        echo '<br />';
    }
    }
    echo $OUTPUT->footer();