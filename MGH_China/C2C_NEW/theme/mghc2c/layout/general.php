<?php
$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);
$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);
$showsidepre = $hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT);
$showsidepost = $hassidepost && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT);
$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));

$bodyclasses = array();
if ($showsidepre && !$showsidepost) {
    $bodyclasses[] = 'side-pre-only';
} else if ($showsidepost && !$showsidepre) {
    $bodyclasses[] = 'side-post-only';
} else if (!$showsidepost && !$showsidepre) {
    $bodyclasses[] = 'content-only';
}
if ($hascustommenu) {
    $bodyclasses[] = 'has_custom_menu';
}
if ($hasnavbar) {
    $bodyclasses[] = 'hasnavbar';
}

echo $OUTPUT->doctype();

if(!empty($USER->id) || $userid!=""){
	global $DB;
    $userid = '';
	$userid = $userid ? $userid : $USER->id;       // Owner of the page
	$user = $DB->get_record('user', array('id' => $userid));

	if ($user->deleted) {
		    $PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
		    echo $OUTPUT->header();
		    echo $OUTPUT->heading(get_string('userdeleted'));
		    echo $OUTPUT->footer();
		    die;
	}

	$currentuser = ($user->id == $USER->id);
	$context = $usercontext = get_context_instance(CONTEXT_USER, $userid, MUST_EXIST);
	
	//require_once('config.php');
	require_once($CFG->dirroot . '/my/lib.php');
	require_once($CFG->dirroot . '/tag/lib.php');
	require_once($CFG->dirroot . '/user/profile/lib.php');
	require_once($CFG->libdir.'/filelib.php');
}
global $SESSION;
if(isset($SESSION->lang)){
$language = $SESSION->lang;
if($language == 'zh_cn'){
	$languagefolder = '/'.$language.'/';}else{$languagefolder = '/';
}
}else{
	$language = 'en_us';
}

$basedir = explode("/",$_SERVER["SCRIPT_NAME"]);
$maindir = $basedir[count($basedir)-2];
$mainfile = $basedir[count($basedir)-1];

if($language == "zh_cn") 
{
	$class_home ="home_".$language;
	$class_class ="class_".$language;
	$class_course= "course_".$language;
	$class_lesson= "lesson_".$language;
	$class_message= "message_".$language;
	$class_users ="users_".$language;
	$class_center="center_".$language;
	$class_profile= "profileuser_".$language;
	if(is_centeradmin())
		{
		switch($maindir)
			{
			case 'message': $class_message = "message_".$language."sel";
							break;
			case 'course': if(in_array($mainfile,$CFG->cadmin_course))
								$class_course= "course_".$language."sel";
							elseif(in_array($mainfile,$CFG->cadmin_lesson))
								$class_lesson="lesson_".$language."sel";
							break;
			case 'calendar': if(in_array($mainfile,$CFG->cadmin_class))	
								$class_class="class_".$language."sel";
								break;
			case 'group': if(in_array($mainfile,$CFG->cadmin_class))	
								$class_class="class_".$language."sel";
								break;
			case 'uploaduser': if(in_array($mainfile,$CFG->cadmin_users))	
								$class_users="users_".$language."sel";
								break;   
			case 'admin' : if(in_array($mainfile,$CFG->cadmin_users))
							$class_users="users_".$language."sel"; break;
			default : if(in_array($mainfile,$CFG->cadmin_home)){
							$class_home="home_".$language."sel";
						}
						else if(in_array($mainfile,$CFG->cadmin_course))	
						{
							$class_course= "course_".$language."sel";
						}
						break;
			
		}
					
	}
	if(is_teacher() || is_non_editing_teacher())
	{
		switch($maindir)
			{	
			case 'message': $class_message = "message_".$language."sel";
							break;
			case 'course': if(in_array($mainfile,$CFG->instructor_course)) {
								$class_course= "course_".$language."sel";
							}else  if(in_array($mainfile,$CFG->instructor_lesson)) {
								$class_lesson= "lesson_".$language."sel";
							}
							break;
			case 'user':  if(in_array($mainfile,$CFG->instructor_profile))
						$class_profile= "profileuser_".$language."sel";
						break;
			default : if(in_array($mainfile,$CFG->instructor_home))
						$class_home="home_".$language."sel";
						break;
		}	
	}
	if(is_siteadmin())
	{
		switch($maindir)
			{
			case 'message': $class_message = "message_".$language."sel";
							break;
			case 'course': if(in_array($mainfile,$CFG->admin_course)){
								$class_course= "course_".$language."sel";
							}else if(in_array($mainfile,$CFG->admin_center))
							{
								$class_center= "center_".$language."sel";
							}
							else if(in_array($mainfile,$CFG->admin_lesson))
							{
								$class_lesson="lesson_".$language."sel";
							}
							else if(in_array($mainfile,$CFG->admin_home))
							{
								$class_home="home_".$language."sel";
							}
							break;
			case 'calendar': if(in_array($mainfile,$CFG->admin_class))	
								$class_class="class_".$language."sel";
								break;
			case 'group': if(in_array($mainfile,$CFG->admin_class))	
								$class_class="class_".$language."sel";
								break;
			case 'uploaduser': if(in_array($mainfile,$CFG->admin_users))	
								$class_users="users_".$language."sel";
								break;   
			case 'admin' : if(in_array($mainfile,$CFG->admin_users))
							$class_users="users_".$language."sel"; break;
			default : if(in_array($mainfile,$CFG->admin_home)){
						$class_home="home_".$language."sel";
						}
						break;
			
		}
	}
	
	if(is_parent())
	{
		switch($maindir)
		{
			case 'message': $class_message = "message_".$language."sel";
							break;
		}	
	}
	if(is_student())
	{
		switch($maindir)
		{
			case 'user':  if(in_array($mainfile,$CFG->student_profile))
						$class_profile= "profileuser_".$language."sel";
						break;
			case 'message': $class_message = "message_".$language."sel";
							break;
		}	
	}
}
else
{
	$class_home ="home";
	$class_class ="class";
	$class_users ="users";
	$class_course= "course";
	$class_lesson= "lesson";
	$class_message= "message";
	$class_center="center";
	$class_profile= "profileuser";
	if(is_centeradmin())
		{
		switch($maindir)
			{
			case 'message': $class_message = "messagesel";
							break;
			case 'course': if(in_array($mainfile,$CFG->cadmin_course))
								$class_course= "coursesel";
							elseif(in_array($mainfile,$CFG->cadmin_lesson))
								$class_lesson="lessonsel";
							break;
			case 'calendar': if(in_array($mainfile,$CFG->cadmin_class))	
								$class_class="classsel";
								break;
			case 'group': if(in_array($mainfile,$CFG->cadmin_class))	
								$class_class="classsel";
								break;
			case 'uploaduser': if(in_array($mainfile,$CFG->cadmin_users))	
								$class_users="userssel";
								break;    
			case 'admin' : if(in_array($mainfile,$CFG->cadmin_users))
							$class_users="userssel"; break;
			default : if(in_array($mainfile,$CFG->cadmin_home)){
							$class_home="homesel";
						}
						else if(in_array($mainfile,$CFG->cadmin_course))	
						{
							$class_course= "coursesel";
						}
						break;
			
		}
					
	}
	if(is_teacher() || is_non_editing_teacher())
	{
		//echo $maindir;
		switch($maindir)
			{	
			case 'message': $class_message = "messagesel";
							break;
			case 'course': if(in_array($mainfile,$CFG->instructor_course)) {
								$class_course= "coursesel";
							} else  if(in_array($mainfile,$CFG->instructor_lesson)) {
								$class_lesson= "lessonsel";
							}
							break;
			case 'user':  if(in_array($mainfile,$CFG->instructor_profile))
						$class_users= "userssel";
						break;
			case 'calendar' : if(in_array($mainfile,$CFG->instructor_home))
						$class_home="homesel";
						break;
			default : if(in_array($mainfile,$CFG->instructor_home))
						$class_home="homesel";
					 
						break;
		}	
	}
	if(is_siteadmin())
	{
		//echo $maindir;
		switch($maindir)
			{
			case 'message': $class_message = "messagesel";
							break;
			case 'course': if(in_array($mainfile,$CFG->admin_course)){
								$class_course= "coursesel";
							}else if(in_array($mainfile,$CFG->admin_center))
							{
								$class_center= "centersel";
							}
							else if(in_array($mainfile,$CFG->admin_lesson))
							{
								$class_lesson="lessonsel";
							}
							else if(in_array($mainfile,$CFG->admin_home))
							{
								$class_home="homesel";
							}
							break;
			case 'calendar': if(in_array($mainfile,$CFG->admin_class))	
								$class_class="classsel";
								break;
			case 'group': if(in_array($mainfile,$CFG->admin_class))	
								$class_class="classsel";
								break;
			case 'uploaduser': if(in_array($mainfile,$CFG->admin_users))	
								$class_users="userssel";
								break;   
			case 'admin' : if(in_array($mainfile,$CFG->admin_users))
							$class_users="userssel"; break;
			case 'user' : if(in_array($mainfile,$CFG->admin_users))
							$class_users="userssel"; break;
			default : if(in_array($mainfile,$CFG->admin_home)){
						$class_home="homesel";
						}
						break;
			
		}
					
	
	}
	if(is_mhescordinator())
	{
		switch($maindir)
			{
			case 'message': $class_message = "messagesel";
							break;
			case 'course': 	if(in_array($mainfile,$CFG->mhes_home)){
								$class_home="homesel";
							}elseif(in_array($mainfile,$CFG->mhes_center)){
								$class_center="centersel";
							}elseif(in_array($mainfile,$CFG->mhes_course)){
								$class_course="coursesel";
							}
							break;
			case 'enrol': 	if(in_array($mainfile,$CFG->mhes_course)){
								$class_course="coursesel";
							}
							break;
			case 'calendar': if(in_array($mainfile,$CFG->mhes_course)){
								$class_course="coursesel";
							}
							break;
			case 'group': if(in_array($mainfile,$CFG->mhes_course))	
								$class_course="coursesel";
								break;
			case 'uploaduser': if(in_array($mainfile,$CFG->mhes_users))	
								$class_users="userssel";
								break;  
			case 'admin' : if(in_array($mainfile,$CFG->mhes_users))
							$class_users="userssel"; 
							break;
			case 'user' : if(in_array($mainfile,$CFG->mhes_users))
							$class_users="userssel"; 
							break;
			default : if(in_array($mainfile,$CFG->mhes_home)){
						$class_home="homesel";
						}
						break;
			
		}
		//die($class_home);
	}
	
	if(is_institute_cordinator())
	{
		//echo $maindir;
		switch($maindir)
			{
			case 'message': $class_message = "messagesel";
							break;
			case 'course': 	if(in_array($mainfile,$CFG->instco_course)){
								$class_course="coursesel";
							}
							break;
			case 'enrol': 	if(in_array($mainfile,$CFG->instco_course)){
								$class_course="coursesel";
							}
							break;
			case 'calendar': if(in_array($mainfile,$CFG->instco_home)){
								$class_home="homesel";
							}
							break;
			case 'group': if(in_array($mainfile,$CFG->instco_course))	
								$class_course="coursesel";
								break;
			case 'uploaduser': if(in_array($mainfile,$CFG->instco_users))	
								$class_users="userssel";
								break;  
			case 'admin' : if(in_array($mainfile,$CFG->instco_users))
							$class_users="userssel"; 
							break;
			case 'user' : if(in_array($mainfile,$CFG->instco_users))
							$class_users="userssel"; 
							break;
			default : if(in_array($mainfile,$CFG->instco_home)){
						$class_home="homesel";
						}
						break;
			
		}
		//die($class_home);
	}
	
	if(is_parent())
	{
		switch($maindir)
		{
			case 'message': $class_message = "messagesel";
							break;
			default: break;
		}	
	}
	if(is_student())
	{
		
		switch($maindir)
		{
			case 'user':  if(in_array($mainfile,$CFG->student_profile))
						$class_users= "userssel";
						break;
			case 'course':  if(in_array($mainfile,$CFG->student_lesson))
						$class_lesson= "lessonsel";
						break;
			case 'message': $class_message = "messagesel";
							break;
			default:if(in_array($mainfile,$CFG->student_home))
						$class_home= "homesel"; 
					break;
		}	
	}
	
	
}
	if(is_teacher() || is_non_editing_teacher()){
		$searchcourse="";
		$courses[0] = apt_enrol_get_users_courses($USER->id,false,'*', 'visible DESC,sortorder ASC',$searchcourse);
	}else if(is_centeradmin()){		
		
		$params = array();
		$sql = 'select * from {role_assignments} where roleid = '.CENTERADMIN_ROLEID.' and userid = '.$USER->id.' and contextid > 1';
	
		$check_role_assignments = $DB->get_records_sql($sql);
		if($check_role_assignments){
			foreach($check_role_assignments as $check_role_assignment){
		   
				$context = context::instance_by_id($check_role_assignment->contextid);
				$category = $DB->get_record('course_categories',array('id'=>$context->instanceid));
				$courses[$context->instanceid] = get_courses($context->instanceid, $sort="c.sortorder ASC", $fields="c.*");
				
			}
		}
		
	}else if(is_siteadmin()){
		$course_categories = $DB->get_records('course_categories',array());
		foreach($course_categories as $course_category){
			$courses[$course_category->id] = get_courses($course_category->id, $sort="c.sortorder ASC", $fields="c.*");
		}
	}

	
 ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $PAGE->title ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
</head>
<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">
<?php echo $OUTPUT->standard_top_of_body_html() ?>
<div id="header"><img src="<?php echo $CFG->wwwroot.'/theme/'.$CFG->theme.'/pix/logo.png'; ?>" alt="" width="215"/><span style="text-align:right;"><?php echo $OUTPUT->lang_menu();?></span></div>
<?php
	if(isset($courses)){
	foreach($courses as $course){
		foreach($course as $c){
			$courseid = $c->id;
			break 2;
		}
		//break;
	}
	}
	//echo $courseid;
?>
<!-- END OF HEADER -->        

<?php if ($hassidepre) { ?>
     <div id="container">		
				    <!--Left side box starts-->
					<div class="col_left">
				    	<div class="dashboard">
					        <div class="profile_block">
					        	<div class="profile">
					            	<div class="profilepicture"><?php 
									echo $OUTPUT->user_picture($user, array('size'=>244));
									//print_user_picture($USER->id,1,300,false,false); ?></div>
					                <div class="profilename">
						                <h2>Hello</h2>
						                <h3><?php //echo $USER->firstname.' '.$USER->lastname;
										echo wordwrap(fullname($USER),13,"\n",true);
										?></h3>
										<?php if(is_mhescordinator()){ ?>
										<h2>
										<a href="<?php echo $CFG->wwwroot; ?>/mod/certificate/index.php?id=<?php echo $COURSE->id; ?>">
											<img src="<?php echo $CFG->wwwroot; ?>/images/generatecertificate.jpg" alt="" style="float:right;">
										</a>
										</h2>
										<?php } ?>
					                </div>
									 <?php //$mychildren = array();
									    if(is_parent()){
										  $startday=strtotime('0 day', strtotime(date('Y-m-d')));
									      $mychildern = get_mychildren();
										  //print_r($mychildern);
										 
										  foreach($mychildern as $mychild){
										     //print_r($mychild->firstname);
											 //die();
											 ?><div class="profilestatus">
												<p><?php echo $mychild->firstname ." ". get_string('isstudying');?></p>
											    <?php if (!isset($hiddenfields['mycourses'])){
															//if ($mycourses = enrol_get_all_users_courses($mychild->id, true, NULL, 'visible DESC,sortorder ASC')) {
															if ($mycourses = apt_enrol_get_users_courses($mychild->id, true, NULL, 'visible DESC,sortorder ASC')) {
															    foreach($mycourses as $mycourse){
															    	
															    	$group = groups_get_groupby_role($mychild->id,$mycourse->id);
																	$per = get_course_completion($mycourse->id,$group->id);
																	//int_r($group);
																	//echo $mycourse->fullname;
																	?>
																	  <h3><?php echo $mycourse->fullname; ?></h3>
																	  <div class="yel-bar">
																			<div class="gre-bar" style="width:<?php echo $per; ?>%;"></div>
																		</div>
																		<p><?php echo $per; ?>% <?php echo get_string('studcompletion');?></p>
																	
																	<?php
																}			
															}
														}
												?>
											    </div>
											 <?php
											}
											
										}
										?>
								<?php if(is_student()){?>					            
									<div class="profilestatus">
											    <?php if (!isset($hiddenfields['mycourses'])){
											    	$startday=strtotime('0 day', strtotime(date('Y-m-d')));
															if ($mycourses = apt_enrol_get_users_courses($USER->id, true, NULL, 'visible DESC,sortorder ASC')) {
															?><p <?php if($CFG->theme == 'mghc2c'){echo 'style="font-size:19px;font-weight:bold;background:#ccc;"'; } ?>><?php echo get_string('You_are_studying');?></p><?											 
															    foreach($mycourses as $mycourse){
															    	$group = groups_get_groupby_role($USER->id,$mycourse->id);
																	$calendardate = strtotime(date('d').'-'.date('m').'-'.date('Y').' 00:00:00');
																	$calendarenddate = strtotime(date('d').'-'.date('m').'-'.date('Y').' 23:59:59');
																	//print_r($calendardate);
																	//print_r($calendarenddate);
																	if($group){
																		$sql = "SELECT * FROM {class_activity} WHERE groupid = $group->id and section != ''";
																		$classes_scdeduled = $DB->get_records_sql($sql,array());
																		$countlessontotal = 0;
																		$countlessonpassed = 0;
																		$countlessoncurrent = 0;
																		$countlessonupcomming = 0;
																		foreach($classes_scdeduled as $cs_sc){
																			//$eventbox .= '<p>'.$cs_sc->groupid.'->'.$cs_sc->availablefrom.'</p>';
																			$countlessontotal++;
																			if($cs_sc->availablefrom < time()){
																				$countlessonpassed++;
																			}
																			if($cs_sc->availablefrom > time()){
																				$countlessonupcomming++;
																			}
																			if($cs_sc->availablefrom == time()){
																				$countlessoncurrent++;
																			}
																		}
																		$completion = intval((($countlessonpassed+$countlessoncurrent)/$countlessontotal)*100);
																		$per = $completion;	
																	}else{
																		$per = 0;
																	}												    	
																	//echo $mycourse->fullname;
																	?>
																	  <h3><a href="<?php echo $CFG->wwwroot;?>/<?php if($CFG->theme == 'mghc2c'){?>course/view.php?catid=&id=<?php }else{?>index_student.php?courseid=<?php }echo $mycourse->id?>"><?php echo $mycourse->fullname; ?></a></h3>
 																		<div class="yel-bar">
																			<div class="gre-bar" style="width:<?php echo $per; ?>%;"></div>
																		</div>
																		<p><?php echo $per; ?>% <?php echo get_string('studcompletion');?></p>
																		
																	<?php
																}	
															}
														}
												?>
									</div>
									<?php }?>
									<?php if($CFG->theme == 'mghc2c' && (is_student() || is_teacher())){ ?>
									<?php
									$totclass = 0;
									$a = 0;
									$b = 0;
								if ($mycourses = apt_enrol_get_users_courses($USER->id, true, NULL, 'visible DESC,sortorder ASC')) {
															
									foreach($mycourses as $mycourse){
										if($group = groups_get_groupby_role($USER->id,$mycourse->id)){
											$groups .= $group->id.',';
										}
									}
								}
								if($mycourses){
									$groups = substr($groups,0,-1);
									$currenttime = time();
									$classes_sql = "SELECT * FROM {class_activity} where groupid in ($groups) and section <> '' and availablefrom <= $currenttime";
									$classes = $DB->get_records_sql($classes_sql);
									$totclass = count($classes);
									$attendance_sql = "SELECT * FROM {custom_userpresent} where cid in ($groups)";
									if(is_student()){
										$attendance_sql .= " and (studentid like '%@$USER->id@%' OR studentid like '%@$USER->id' OR studentid like '$USER->id@%')";
									}elseif(is_teacher()){
										$attendance_sql .= " and teacherid = $USER->id";
									}
									
									$attendance = $DB->get_records_sql($attendance_sql);
									$a = count($attendance);
									$b = ($totclass - $a);
									?>
									<div class="att-status">	
				                        <div class="im"><img src="<?php echo $CFG->wwwroot.'/theme/mghc2c/pix/attend.jpg'?>" alt=""  /></div>						
										<ul>
											<li class="ct1">Total Classes :<span><?php echo $totclass;?></span></li>
											<li class="ct2">Attended :<span><?php echo $a;?></span></li>
											<li class="ct3">Missed :<span><?php echo $b;?></span></li>
										</ul>
									</div>
									<?php } ?>
									<?php } ?>
					       </div>
					       
				        </div>
				        <!--<div class="profile_block_bottom"></div>-->
						<div id = "custom_blocks" class="calendar">
						<!--<img src="<?php echo $CFG->wwwroot.'/pix/icon/calender.png'; ?>" alt="" width="253" height="227" />-->
						  <?php //if(is_siteadmin()){ ?>
								     <?php echo $OUTPUT->blocks_for_region('side-pre') ?>
								  <?php //} ?>
						  
					    </div>  
				    </div>
				</div>                   
                <?php } ?>
				<div style="float:left;width:70%;margin-bottom:20px;">
				   <?php if(isloggedin() and !isguestuser()){ ?>
					<div class="verticle_navbar" style="position:relative;z-index:999999;">
								<ul>
									<li><a href="<?php echo $CFG->wwwroot; ?>" class="<?php echo $class_home;?>">Home</a></li>
									<?php if(is_institute_cordinator()){ ?>
										<?php
											$institute_cordinator = $DB->get_records('role_assignments', array('userid'=>$USER->id,'roleid'=>'13'));
											foreach($institute_cordinator as $ic){
												if($ic->contextid == 1){
													continue;
												}
												$context = $DB->get_record('context',array('id'=>$ic->contextid));
												$catid .= $context->instanceid.',';
											}
											$catid = substr($catid,0,-1);
											//echo $catid;
											$course_sql = "SELECT id,category from {course} where category in ($catid)";
											$courses = $DB->get_records_sql($course_sql);
											foreach($courses as $course){
												$courseid = $course->id;
												$categoryid = $course->category;
												break;
											}										
										?>
									
									<li><a href="<?php echo $CFG->wwwroot.'/course/view.php?catid='.$categoryid.'&id='.$courseid.'&edit=on&sesskey='.$USER->sesskey; ?>" class="<?php echo $class_course;?>" >Resources</a></li>
									<?php }?>
									<?php if(is_siteadmin() || is_mhescordinator()){ ?>
									<li><a href="<?php echo $CFG->wwwroot.'/course/center.php?categoryedit=on'; ?>" class="<?php echo $class_center;?>" title="Center">Institutes</a></li>
									
									<?php } ?>
									<?php if(is_centeradmin()){?>
									<li><a href="<?php echo $CFG->wwwroot.'/calendar/view_centeradmin.php?view=day&cal_d='.date('d').'&cal_m='.date('m').'&cal_y='.date('Y'); ?>" class="<?php echo $class_class;?>" title="Classes">Class</a></li>
									<?php } ?>
									<?php if(is_siteadmin() || is_mhescordinator()){ ?>
									<li><a href="<?php echo $CFG->wwwroot.'/course/index.php'; ?>" class="<?php echo $class_course;?>">Courses</a></li>
									<?php }elseif(is_centeradmin()){?>
									<li><a href="<?php echo $CFG->wwwroot.'/centeradmin_courseview.php'; ?>" class="<?php echo $class_course;?>">Courses</a></li>
									<?php }else if(is_teacher() || is_non_editing_teacher()){
												$mycourses = enrol_get_users_courses($USER->id, true, NULL, 'visible DESC,sortorder ASC');
												foreach($mycourses as $mycourse){
													$courseid = $mycourse->id;
													break;
												}
									?>
									<li><a href="<?php echo $CFG->wwwroot.'/course/courseview.php'; ?>" class="<?php echo $class_course;?>">Courses</a></li>
									<?php }else if(is_parent()){?>
									<li><a href="<?php echo $CFG->wwwroot.'/parent_courseview.php'; ?>" class="<?php echo $class_course;?>">Courses</a></li>
									<?php }else{ ?>
									<?php if($CFG->theme != 'mghc2c'){ ?>
									<li><a href="<?php echo $CFG->wwwroot.'/course/courseview.php'; ?>" class="<?php echo $class_course;?>">Courses</a></li>
											<?php } ?>
									<?php }?>
									<?php if(is_centeradmin()){?>
									<li><a href="<?php /*echo $CFG->wwwroot.'/course/courset.php';*/echo $CFG->wwwroot.'/course/view.php?id='.$courseid.'&edit=on&sesskey='.$USER->sesskey; ?>" class="<?php echo $class_lesson;?>">Lessons</a></li>
									<?php }else if(is_teacher() || is_non_editing_teacher()){ ?>
									<li><a href="<?php /*echo $CFG->wwwroot.'/course/courset.php';*/echo $CFG->wwwroot.'/course/view.php?id='.$courseid.'&edit=on&sesskey='.$USER->sesskey; ?>" class="<?php echo $class_lesson;?>">Lessons</a></li>
									<?php }else if(is_student()){ ?>
										<?php if($CFG->theme == 'mghc2c'){ ?>
												<?php foreach($mycourses as $mycourse){
														$catid = $mycourse->category;
														$id = $mycourse->id;
														if($mycourse->shortname != 'preassessment' || $mycourse->shortname != 'postassessment'){
															break;
														}
													}
												?>
												<li><a href="<?php echo $CFG->wwwroot.'/course/view.php?catid=&id='.$id;?>" class="<?php echo $class_lesson;?>">Lessons</a></li>
										<?php }else{ ?>
												<li><a href="<?php echo $CFG->wwwroot.'/student_lesson.php';?>" class="<?php echo $class_lesson;?>">Lessons</a></li>
										<?php } ?>
									<?php }/*else{ ?>
									<li><a href="#" class="<?php echo $class_lesson;?>">lesson</a></li>
									<?php } */?>
									<?php if(is_centeradmin() || is_siteadmin() || is_mhescordinator()){?>
									<li><a href="<?php echo $CFG->wwwroot.'/admin/user.php'; ?>" class="<?php echo $class_users;?>" title="User Management">Users</a></li>
									<?php } ?>	
									<?php if(is_teacher() || is_non_editing_teacher() || is_student()){ ?>
									<li><a href="<?php echo $CFG->wwwroot.'/user/edit.php'; ?>" class="<?php echo $class_users;?>" >Profile</a></li>
									<?php }?>
									<?php if(is_parent()){?>
									<li><a href="<?php echo $CFG->wwwroot.'/message/index.php?viewing=recentconversations'; ?>" class="<?php echo $class_message;?>">Messages</a></li>
									<?php }else{ ?>
									<li><a href="<?php echo $CFG->wwwroot.'/message/'; ?>" class="<?php echo $class_message;?>">Messages</a></li>
									<?php } ?>
									<?php if(is_institute_cordinator()){ ?>
										<?php
											$institute_cordinator = $DB->get_records('role_assignments', array('userid'=>$USER->id,'roleid'=>'13'));
											foreach($institute_cordinator as $ic){
												if($ic->contextid == 1){
													continue;
												}
												$context = $DB->get_record('context',array('id'=>$ic->contextid));
												$catid .= $context->instanceid.',';
											}
											$catid = substr($catid,0,-1);
											//echo $catid;
											$course_sql = "SELECT id,category from {course} where category in ($catid)";
											$courses = $DB->get_records_sql($course_sql);
											foreach($courses as $course){
												$courseid = $course->id;
												$categoryid = $course->category;
												break;
											}										
										?>
									<li><a href="<?php echo $CFG->wwwroot.'/course/faculty_dashboard.php?catid='.$categoryid.'&id='.$courseid.'&edit=on&sesskey='.$USER->sesskey; ?>" class="<?php echo $class_center;?>" >Faculty Dashboard</a></li>
									<?php }?>
								</ul>
								<ul style="float:right;width:10%;">
									<li><a href="<?php echo $CFG->wwwroot.'/login/logout.php?sesskey='.$_SESSION['USER']->sesskey; ?>" class="logout<?php if($language == 'zh_cn')echo '_'.$language; ?>">Logout</a></li>
								</ul>
							</div>
							<?php }else{ ?>
							<div class="verticle_navbar">
								<ul>
									<li><a href="<?php echo $CFG->wwwroot; ?>" class="<?php echo $class_home;?>">Back to Login Page</a></li>
								</ul>
							</div>
							<?php } ?>
				<?php  $breadcrumb; ?><?php echo $OUTPUT->main_content(); ?></div>
                <?php if ($hassidepost) { ?>               
                        <?php echo $OUTPUT->blocks_for_region('side-post'); ?>                  
                <?php } ?>
</div>
<!-- START OF FOOTER -->
    <?php if ($hasfooter) { ?>    
	<div id="footer">
		<div id="foot">
			<img width="153" height="45" style="float:left;margin-top:12px;" alt="" src="<?php echo $CFG->wwwroot; ?>/images/poweredby.png">
			   <div class="footer-text"> 
			   <span><img align="top" alt="" src="<?php echo $CFG->wwwroot; ?>/images/call.jpg"> : +91 120 4383400  
			  &nbsp;&nbsp;<img align="top" alt="" src="<?php echo $CFG->wwwroot; ?>/images/mail.jpg"> : <a href="mailto:mhesindia@mcgraw-hill.com" style="text-decoration:none;color:#fff;">mhesindia@mcgraw-hill.com</a>
			  </span>			  
			<p align="right" style="padding-top:10px;">Copyright&copy; 2012 McGraw-Hill Educational Services</p>
			</div>
			<div style="float:right;padding:15px;">
			  <a href="http://www.facebook.com/pages/McGraw-Hill-Education-Services/399008360164008" target="_blank"><img style="float:right;" alt="" src="<?php echo $CFG->wwwroot; ?>/images/fb.jpg"></a>
			  <a href="http://www.linkedin.com/pub/mhes-india/56/5a4/991" target="_blank"><img style="float:right;" alt="" src="<?php echo $CFG->wwwroot; ?>/images/in.jpg"></a>
			  <a href="https://twitter.com/@MHESIndia" target="_blank"><img style="float:right;" alt="" src="<?php echo $CFG->wwwroot; ?>/images/tw.jpg"></a>
			</div>
			
	    </div>
	</div>
    <?php } ?> 
</div>
<?php echo $OUTPUT->standard_end_of_body_html(); ?>
</body>
</html>