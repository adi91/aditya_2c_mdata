<?php

    require_once('../config.php');
    require_once($CFG->dirroot .'/course/lib.php');
	require_once($CFG->dirroot .'/group/lib.php');
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
	if(is_student()){
		$PAGE->set_pagelayout('frontpage');
	}else{
		$PAGE->set_pagelayout('frontpage');
	}
    $editing = $PAGE->user_is_editing();
    $PAGE->set_title($SITE->fullname);
    $PAGE->set_heading($SITE->fullname);
    echo $OUTPUT->header();
    //print_my_moodle_profile();
   $mycourses = enrol_get_users_courses($USER->id,true,'*', 'visible DESC,sortorder ASC',$searchcourse);
   $startday=strtotime('0 day', strtotime(date('Y-m-d')));
   $act_not_available=0;
    if (count($mycourses)>0) 
    {
			    $c = 1;
			    foreach($mycourses as $mycourse){
				    $category = $DB->get_record('course_categories',array('id'=>$mycourse->category));
					
					$summary = $DB->get_record('course', array('id'=>$mycourse->id));
					$group = groups_get_groupby_role($USER->id,$mycourse->id);
					$per = get_course_completion($mycourse->id,$group->id);
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
					//$percent_cop= intval(($taskcount / count($activities))*100);
					
					?>
						<div class="sectionlesson">
						    <div><h3><u>Course is in Center : <?php print_r($category->name); ?></u></h3></div>
					        <div class="box1wf">
					        <?php if(is_student()) {?>
					        	<a href="<?php echo $CFG->wwwroot;?>/course/courselesson.php?id=<?php echo $mycourse->id?>">
					        	<?php } else {?>
					        	<a href="<?php echo $CFG->wwwroot;?>/course/view.php?id=<?php echo $mycourse->id?>">
					        	<?php }?>
								<div class="co-number" style="background: url('<?php echo $CFG->wwwroot;?>/pix/icon/crincomplete-number.png') no-repeat scroll 0 0 transparent;"><span><?php echo $c; ?></span></div>
								<div class="co-details">
									<div class="task-le">
										<div class="table">
											<div style="display:block">
											  <h4 class="hd1"><?php echo $mycourse->fullname; ?></h4>
											 <span class="pr1g"><?php echo get_string('studcompletion');?></span>
											</div>
											<div class="pr1">
											</div>
											<div class="tableaside">
											    
												<div class="calc"><?php echo $per.'%';?></div>
												<div class="yel-bar"><div class="gre-bar" style="width:<?php if($per){echo $per.'%';}else{echo '0%';} ?>;"></div></div>
											</div>
										</div>
									</div>
									<div class="taskcomplete-le">
										<div class="pr1gl">
											<span><?php if($group->startdate){ 
											echo date('d M Y ',$group->startdate);} ?></span>
                                            <?php 
											$activities = get_array_of_activities($mycourse->id);
											if($activities){
											?>
											<div class="right">
											</div>
											<div class="right">
												<span class="right"><?php echo $taskcount; ?> <?php echo  get_string('taskcomptleted');?></span></br>											
											<?php
											$taskcount = 0;
												foreach($activities as $activity){
													///$completion = $DB->get_record('course_modules_completion',array('coursemoduleid'=>$activity->cm,'userid'=>$USER->id));
													if($activity->section>0){
														$sction_act_com= get_group_les_activity($group->id,$activity->sectionid,$mycourse->id);
													
														if($sction_act_com->availablefrom!=""){
															if($sction_act_com->availablefrom < $startday ) {
																 echo '<span class="edu-icon_01"></span>';
															}else
															{
																echo '<span class="edu-icon_02"></span>';
															}
														}
													}
																												
													/*if($completion->completionstate == 1){
													  echo '<span class="edu-icon_01"></span>';
													  $taskcount++;
													}else{
													  echo '<span class="edu-icon_02"></span>';
													}*/
												
											}
											
											?>
											</div>	
                                         <?php } 
                                       	$get_act_record_count=get_group_act_no($group->id,$mycourse->id); 
		    							if($get_act_record_count==0)
										{
											echo "<h4>".get_string('class_activity_not_preseent')."</h4>";
										}
                                         ?>											
										</div>
									</div>		                
								</div>
								</a>
							</div>
				        </div>
					<?php
					
                				
			
	$context = get_context_instance(CONTEXT_COURSE, $mycourse->id);
	 if (!empty($CFG->coursecontact)) {
        $managerroles = explode(',', $CFG->coursecontact);
		array_push($managerroles,NONINSTRUCTOR);
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
		
        if (!empty($namesarray)) {
            echo html_writer::start_tag('div', array('class'=>'sectionmessage'));
            echo html_writer::start_tag('h4');
            echo $mycourse->shortname ." ". get_string('teacherare');
            echo html_writer::start_tag('h4');
           $cnt=1;
            foreach ($namesarray as $key => $name) {
            	$group_user= groups_get_groupby_role($USER->id,$mycourse->id);
            	$check_user_enroll = $DB->get_record('groups_members',array('groupid'=>$group_user->id,'userid'=>$key));
            	if($check_user_enroll) {
	            	echo html_writer::start_tag('div', array('class'=>'messagebox'));
	            		echo html_writer::start_tag('div', array('class'=>'topbx'));
	            			echo html_writer::start_tag('div', array('class'=>'topbx'));
	            				echo html_writer::start_tag('div', array('class'=>'imageborder'));
	                				print_user_picture($key, $course->id, 1, 80, false, false);
	                			echo html_writer::end_tag('div');
								echo html_writer::start_tag('div', array('class'=>'topbx-ri'));
	                				echo $name;
	                				/* echo "<p class='pr1'>".get_string('nationality').": British<br />
											".get_string('DOB').":  26 December 1971<br />
	                        				".get_string('TCL').":  3.5</p>";*/
	                			echo html_writer::end_tag('div');
	                		echo html_writer::end_tag('div');		
	                	echo html_writer::end_tag('div');
	                	echo html_writer::start_tag('div', array('colspan'=>'2','class'=>'blline','align'=>'center'));
	                	echo "<a href='".$CFG->wwwroot."/message/index.php?id=".$key."'><input name='' type='button' class='sendmessagebtn' /></a>";
	                	echo html_writer::end_tag('div');
	                echo html_writer::end_tag('div');
            	}
            }
            echo html_writer::end_tag('div');
        }
    }
    $c++;
	}
    }
    else
	{
	echo get_string('nocourse')." ".get_string('contactcenter');
	global $COURSE,$DB;
	$courses = enrol_get_users_courses($USER->id,true,'*', 'visible DESC,sortorder ASC',$searchcourse);
	
	$course_cnt=0;
	foreach($courses as $course) {
		$contextid = get_context_instance(CONTEXT_COURSECAT, $course->category);
		$sql= "SELECT * FROM {user} u
				JOIN {role_assignments} ra ON ra.userid= u.id 
				WHERE ra.contextid =? AND ra.roleid=?";
		$param= array($contextid->id,10);
				
		$rs=$DB->get_records_sql($sql,$param);
		
			foreach($rs as $user)
			{
				
					echo html_writer::start_tag('div', array('class'=>'sectionmessage'));
		            echo html_writer::start_tag('h4');
		            echo $course->shortname ." ". get_string('centeadminare');
		            echo html_writer::start_tag('h4');	
		            
		            	echo html_writer::start_tag('div', array('class'=>'messagebox'));
			            		echo html_writer::start_tag('div', array('class'=>'topbx'));
			            			echo html_writer::start_tag('div', array('class'=>'topbx'));
			            				echo html_writer::start_tag('div', array('class'=>'imageborder'));
			                				print_user_picture($user->userid, $course->id, 1, 80, false, false);
			                			echo html_writer::end_tag('div');
										echo html_writer::start_tag('div', array('class'=>'topbx-ri'));
			                				echo fullname($user,true);
			                			echo html_writer::end_tag('div');
			                		echo html_writer::end_tag('div');		
			                	echo html_writer::end_tag('div');
			                	echo html_writer::start_tag('div', array('colspan'=>'2','class'=>'blline','align'=>'center'));
			                	echo "<a href='".$CFG->wwwroot."/message/index.php?id=".$user->userid."'><input name='' type='button' class='sendmessagebtn' onClick=window.location.href('".$CFG->wwwroot."/message/index.php?id=".$user->userid."'); /></a>";
			                	echo html_writer::end_tag('div');
			                echo html_writer::end_tag('div');
			     echo html_writer::end_tag('div');
			     $course_cnt++;
			}
			
			
		}
		if($course_cnt==0){
			
		///$contextid = get_context_instance(CONTEXT_COURSE);
	$sql= "SELECT * FROM {user} u
				JOIN {role_assignments} ra ON ra.userid= u.id 
				WHERE ra.contextid !=1  AND ra.roleid=? ";
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
		$param= array(10,$USER->site_fk);
		$rs=$DB->get_record_sql($sql,$param);
		
		$courses = enrol_get_users_courses($rs->userid,true,'*', 'visible DESC,sortorder ASC',$searchcourse);
		foreach($courses as $course) {
			$courseid=$course->id;
		}
		echo html_writer::start_tag('div', array('class'=>'sectionmessage'));
		            echo html_writer::start_tag('h4');
		            echo $course->shortname ." ". get_string('centeadminare');
		            echo html_writer::start_tag('h4');	
		            
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
		
		}
	
	}
    echo $OUTPUT->footer();