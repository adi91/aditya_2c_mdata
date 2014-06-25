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

    require_once('config.php');
    require_once($CFG->dirroot .'/course/lib.php');
    require_once($CFG->libdir .'/filelib.php');

    //redirect_if_major_upgrade_required();
    $startday=strtotime('0 day', strtotime(date('Y-m-d')));
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
	if(is_student()) {
    $PAGE->set_pagelayout('frontpage');
	} else {
	$PAGE->set_pagelayout('frontpage');
	}
    $editing = $PAGE->user_is_editing();
    $PAGE->set_title($SITE->fullname);
    $PAGE->set_heading($SITE->fullname);
    echo $OUTPUT->header();

   $searchcourse = '';
   $mycourses = enrol_get_users_courses($USER->id,true,'*', 'visible DESC,sortorder ASC',$searchcourse);
   foreach($mycourses as $mycourse){
		if(strtolower($mycourse->shortname) == 'pre assessment' || strtolower($mycourse->shortname) == 'preassessment' || strtolower($mycourse->shortname) == 'post assessment' || strtolower($mycourse->shortname) == 'postassessment'){
			unset($mycourses[$mycourse->id]);
		}
   }
    //echo '<pre>';print_r($mycourses);echo '</pre>';
	$taskcounty = 0;
	if($mycourses){
		foreach($mycourses as $mycourse){		     
				        $group = groups_get_groupby_role($USER->id,$mycourse->id);
						if($group){
							$per = get_course_completion($mycourse->id,$group->id);
						}else{
							$per = 0;
						}
						$activities = get_array_of_activities($mycourse->id);
						$taskcount = 0;
						if($activities) {
							foreach($activities as $activity){
								if($activity->section >0){
									$sction_act_com= get_group_les_activity($group->id,$activity->sectionid,$mycourse->id);
									if($sction_act_com->availablefrom < $startday && $sction_act_com->availablefrom!="")
										{
											$taskcount++;
										}
										
								}
							}
						}
											
	    }
	}
	echo pre_post_quiz_report();
	//echo quiz_report();
	if($mycourses) {
			    $c = 1;
			    foreach($mycourses as $mycourse){
					$summary = $DB->get_record('course', array('id'=>$mycourse->id));
					?>
						<div class="sectionlesson">
					        <div class="box1wf">
								<div class="co-number" style="background: url('<?php echo $CFG->wwwroot;?>/images/tag.png') no-repeat scroll 15px 18px transparent;"><span style="padding-left:23px;padding-top:21px;color:#fff;"><?php echo $c; ?></span></div>
								<div class="co-details">
									<div class="task-le">
										<div class="table">
											<div style="display:block">
											  <h4 class="hd1" style="color:#000;font-size:18px;"><?php echo $mycourse->fullname; ?></h4>
				                            <?php
											$group = groups_get_groupby_role($USER->id,$mycourse->id);
											$calendardate = strtotime(date('d').'-'.date('m').'-'.date('Y').' 00:00:00');
											$calendarenddate = strtotime(date('d').'-'.date('m').'-'.date('Y').' 23:59:59');
											//print_r($calendardate);
											//print_r($calendarenddate);
											if($group){
												$sql = "SELECT * FROM {class_activity} WHERE groupid = $group->id and section != ''";
												$classes_scdeduled = $DB->get_records_sql($sql,array());
											}
											$countlessontotal = 0;
											$countlessonpassed = 0;
											$countlessoncurrent = 0;
											$countlessonupcomming = 0;
											$greenball = 0;
											$blueball = 0;
											if($classes_scdeduled){
												foreach($classes_scdeduled as $cs_sc){
													//$eventbox .= '<p>'.$cs_sc->groupid.'->'.$cs_sc->availablefrom.'</p>';
													$countlessontotal++;
													if($cs_sc->availablefrom < time()){
														$countlessonpassed++;
														$greenball++;
													}
													if($cs_sc->availablefrom > time()){
														$countlessonupcomming++;
														$blueball++;
													}
													if($cs_sc->availablefrom == time()){
														$countlessoncurrent++;
														$greenball++;
													}
												}
												$completion = intval((($countlessonpassed+$countlessoncurrent)/$countlessontotal)*100);
												$per = $completion;
											}else{
												$per = 0;
											}
																	
											//$group = groups_get_groupby_role($USER->id,$mycourse->id);
					                        //$per3 = get_course_completion($mycourse->id,$group->id);
											//$sec = '';
                                            //$sction_act_com_new= get_group_les_activity($group->id,$sec,$mycourse->id);											
                                            $activities = get_array_of_activities($mycourse->id);
											//print_r(date('d M y',strtotime(userdate($group->startdate))));
											$taskcount = 0;$taskblue = 0;$taskyel = 0; $taskred = 0;
											 $gre = 0;$blue = 0;$yel = 0; $red = 0;
											if($activities) {
												foreach($activities as $activity){
													//if($activity->section >0){
														$sction_act_com= get_group_les_activity($group->id,$activity->sectionid,$mycourse->id);
														if($sction_act_com->availablefrom < $startday && $sction_act_com->availablefrom!="")
														{
																$taskcount++;
														} elseif($sction_act_com->availablefrom > $startday && $sction_act_com->availablefrom!="")
														{
																$taskblue++;
														} elseif($sction_act_com->availablefrom == $startday && $sction_act_com->availablefrom!="")
														{
																$taskyel++;
														} else {
																$taskred++;
														} 										
													//}
												}
											}
											if($activities){
										        $blue =	intval(($taskblue/count($activities))*100);		
									            $yel =	intval(($taskyel/count($activities))*100);		
									            $red =	intval(($taskred/count($activities))*100);	
									            $gre =	intval(($taskcount/count($activities))*100);
											}
											?>									  
											</div>
											<div class="pr1"><?php echo $mycourse->summary; ?>
											</div></div>
											<div class="tabrgt">
											<div class="tableaside" style="width:100px;">
											    <?php //if($activities){?>
												<span class="pr1g">Completion</span>
												<div class="calc" style="height:auto;"><?php echo $per.'%';?></div>
												
												<div class="percentg">
													<div class="cbar1" style="background: #1FE3FF;">
														<div class="grebar" style="width:<?php echo $per.'%';?>;height:3px;background:#00ff00;padding:0;margin:0;"></div>
													</div>
												</div>
												 <?php //} ?>
											</div>
										</div>
									</div>
									<div class="taskcomplete-le">
										<div class="pr1gl">
											<span style="margin-left:10px;"><?php if($group->startdate){ 
											echo date('d M Y',strtotime(userdate($group->startdate)));} ?></span>											
                                            <?php 
											$activities1 = get_all_sections($mycourse->id);
											$task = 0; if($activities1){
									        	foreach($activities1 as $activity){ 
						                            	if($activity->section > 0){ 
								                        $sction_act_com= get_group_les_activity($group->id,$activity->id,$mycourse->id);
							
								                       if($sction_act_com->availablefrom < $startday && $sction_act_com->availablefrom!="")
							                         	{
									             	$task++;
						           	                	} 						
							                   }
						                   }
											?>										
											<div class="right">
												<span class="right" style="margin-right:20px;"><?php echo $greenball; ?> <?php echo  'Lessons Completed';?></span></br>											
											<div class="que"><p><?php
											$b = 1;
												
												foreach($activities1 as $activity){											
													if($activity->section>0){
														/*$sction_act_com= get_group_les_activity($group->id,$activity->id,$mycourse->id);	
															if($sction_act_com->availablefrom < $startday && $sction_act_com->availablefrom!="") {
																echo '<span class="g">'.$b.'</span>'.$greenball.'<br>';	
															} elseif ($sction_act_com->availablefrom > $startday){
																echo '<span class="b">'.$b.'</span>'.$sction_act_com->availablefrom.'<br>';
															} elseif ($sction_act_com->availablefrom == $startday) {
															    echo '<span class="y">'.$b.'</span>'.$sction_act_com->availablefrom.'<br>';
															} else {
															    echo '<span class="r">'.$b.'</span>'.$sction_act_com->availablefrom.'<br>';
															}
															*/
															if($b <= $greenball){
																echo '<span class="g">';
															}else{
																echo '<span class="b">';
															}
															echo $b;
															echo '</span>';
															$b++;
													}
												
											}
											?>
											</p></div></div>	
                                         <?php } ?>											
										</div>
									</div>		                
								</div>
							</div>
				        </div>
					<?php
					
                				
			
	$context = get_context_instance(CONTEXT_COURSE, $mycourse->id);
	 if (!empty($CFG->coursecontact)) {
        $managerroles = explode(',', $CFG->coursecontact);
        $namesarray = array();
        $rusers = array();

        if (!isset($course->managers)) {
            $rusers = get_role_users($managerroles, $context, true,
                'ra.id AS raid, u.id, u.username, u.firstname, u.lastname,
                 r.name AS rolename, r.sortorder, r.id AS roleid',
                'r.sortorder ASC, u.lastname ASC');
        } else {
            //  use the managers array if we have it for perf reasosn
            //  populate the datastructure like output of get_role_users();
            foreach ($course->managers as $manager) {
                $u = new stdClass();
                $u = $manager->user;
                $u->roleid = $manager->roleid;
                $u->rolename = $manager->rolename;

                $rusers[] = $u;
            }
        }

        /// Rename some of the role names if needed
        if (isset($context)) {
            $aliasnames = $DB->get_records('role_names', array('contextid'=>$context->id), '', 'roleid,contextid,name');
        }

        $namesarray = array();
        $canviewfullnames = has_capability('moodle/site:viewfullnames', $context);
        foreach ($rusers as $ra) {
            if (isset($namesarray[$ra->id])) {
                //  only display a user once with the higest sortorder role
                continue;
            }

            if (isset($aliasnames[$ra->roleid])) {
                $ra->rolename = $aliasnames[$ra->roleid]->name;
            }

            $fullname = fullname($ra, $canviewfullnames);
            $namesarray[$ra->id] =   html_writer::link(new moodle_url('/user/view.php', array('id'=>$ra->id, 'course'=>SITEID)), $fullname);
        }
		
 
    }
    $c++;
	}
    } else {
	echo '<div class="box1wf">Sorry, you are not enrolled for any courses yet.</div>';
	}
   
    echo $OUTPUT->footer();
