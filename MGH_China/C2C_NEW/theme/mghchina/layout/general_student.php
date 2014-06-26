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
	
	require_once('config.php');
	require_once($CFG->dirroot . '/my/lib.php');
	require_once($CFG->dirroot . '/tag/lib.php');
	require_once($CFG->dirroot . '/user/profile/lib.php');
	require_once($CFG->libdir.'/filelib.php');
global $SESSION;
$language = $SESSION->lang;if($language == 'zh_cn'){$languagefolder = '/'.$language.'/';}else{$languagefolder = '/';}

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
							}else if(in_array($mainfile,$CFG->admin_home))
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
			default : if(in_array($mainfile,$CFG->instructor_home))
						$class_home="homesel";
						break;
		}	
	}
	if(is_siteadmin())
	{

		switch($maindir)
			{
			case 'message': $class_message = "messagesel";
							break;
			case 'course': if(in_array($mainfile,$CFG->admin_course)){
								$class_course= "coursesel";
							}else if(in_array($mainfile,$CFG->admin_center))
							{
								$class_center= "centersel";
							}else if(in_array($mainfile,$CFG->admin_home))
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
			case 'admin' : if(in_array($mainfile,$CFG->admin_users))
							$class_users="userssel"; break;
			default : if(in_array($mainfile,$CFG->admin_home)){
						$class_home="homesel";
						}
						break;
			
		}
					
	
	}
	if(is_parent())
	{
		switch($maindir)
		{
			case 'message': $class_message = "messagesel";
							break;
		}	
	}
	if(is_student())
	{
		switch($maindir)
		{
			case 'message': $class_message = "messagesel";
							break;
		}	
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
<div id="header"><img src="<?php echo $CFG->wwwroot.'/pix/logo.jpg'; ?>" alt="" width="344"/></div>

<!-- END OF HEADER -->        

<?php if ($hassidepre) { ?>
<?php global $SESSION;$language = $SESSION->lang;if($language == 'zh_cn'){$languagefolder = '/'.$language.'/';}else{$languagefolder = '/';} ?>
     <div id="container">		
				    <!--Left side box starts-->
					<div class="col_left">
				    	<div class="dashboard">
					        <div class="profile_block">
					        	<div class="profile">
					            	<div class="profileimage"><?php echo $OUTPUT->user_picture($user, array('size'=>100)); ?></div>
					                <div class="profilename">
						                <h2>Hello</h2>
						                <h3><?php //echo $USER->firstname.' '.$USER->lastname;
												echo wordwrap(fullname($USER),13,"\n",true);		
										?></h3>
					                </div>
					            <div class="profilestatus">
								  <?php if(is_siteadmin()){ ?>
								     <?php echo $OUTPUT->blocks_for_region('side-pre') ?>
								  <?php } ?>
								</div>
					       </div>
					       <div class="verticle_navbar">
								<ul>
									<li><a href="<?php echo $CFG->wwwroot; ?>" class="<?php echo $class_home;?>">home</a></li>
									<?php if(is_siteadmin()){ ?>
									<li><a href="<?php echo $CFG->wwwroot.'/course/center.php?categoryedit=on'; ?>" class="<?php echo $class_center;?>" title="Center">Center</a></li>
									
									<?php } ?>
									<?php if(is_centeradmin()){?>
									<li><a href="<?php echo $CFG->wwwroot.'/calendar/view_centeradmin.php?view=day&cal_d='.date('d').'&cal_m='.date('m').'&cal_y='.date('Y'); ?>" class="<?php echo $class_class;?>" title="Classes">Class</a></li>
									<?php } ?>
									<?php if(is_siteadmin()){ ?>
									<li><a href="<?php echo $CFG->wwwroot.'/course/index.php'; ?>" class="<?php echo $class_course;?>">course</a></li>
									<?php }elseif(is_centeradmin()){?>
									<li><a href="<?php echo $CFG->wwwroot.'/centeradmin_courseview.php'; ?>" class="<?php echo $class_course;?>">course</a></li>
									<?php }else if(is_teacher() || is_non_editing_teacher()){ ?>
									<li><a href="<?php echo $CFG->wwwroot.'/course/courseview.php'; ?>" class="<?php echo $class_course;?>">course</a></li>
									<?php }else if(is_parent()){?>
									<li><a href="<?php echo $CFG->wwwroot.'/parent_courseview.php'; ?>" class="<?php echo $class_course;?>">course</a></li>
									<?php }else{ ?>
									<li><a href="<?php echo $CFG->wwwroot.'/course/courseview.php'; ?>" class="<?php echo $class_course;?>">course</a></li>
									<?php }?>
									<?php if(is_centeradmin()){?>
									<li><a href="<?php echo $CFG->wwwroot.'/course/courset.php'; ?>" class="<?php echo $class_lesson;?>">lesson</a></li>
									<?php }else if(is_teacher() || is_non_editing_teacher()){ ?>
									<li><a href="<?php echo $CFG->wwwroot.'/course/courset.php'; ?>" class="<?php echo $class_lesson;?>">lesson</a></li>
									<?php }else if(is_student()){ ?>
									<li><a href="<?php echo $CFG->wwwroot.'/student_lesson.php';?>" class="<?php echo $class_lesson;?>">lesson</a></li>
									<?php  }else{ ?>
									<li><a href="#" class="<?php echo $class_lesson;?>">lesson</a></li>
									<?php } ?>
									<?php if(is_centeradmin()){?>
									<li><a href="<?php echo $CFG->wwwroot.'/admin/user.php'; ?>" class="<?php echo $class_users;?>" title="User Management">Users</a></li>
									<?php } ?>	
									
									<?php if(is_parent()){?>
									<li><a href="<?php echo $CFG->wwwroot.'/message/index.php?viewing=recentconversations'; ?>" class="<?php echo $class_message;?>">message</a></li>
									<?php }else{ ?>
									<li><a href="<?php echo $CFG->wwwroot.'/message/'; ?>" class="<?php echo $class_message;?>">message</a></li>
									<?php } ?>
									<li><a href="<?php echo $CFG->wwwroot.'/login/logout.php?sesskey='.$_SESSION['USER']->sesskey; ?>" class="logout<?php if($language == 'zh_cn')echo '_'.$language; ?>">logout</a></li>
								</ul>
					       </div>
				        </div>
				        <div class="profile_block_bottom"></div>
						<div class="calendar">
						  <img src="<?php echo $CFG->wwwroot.'/pix/icon/calender.png'; ?>" alt="" width="253" height="227" />
					    </div>  
				    </div>
				</div>                   
                <?php } ?>
				<div style="float:left;width:70%;"><?php echo $OUTPUT->main_content(); ?></div>
                <?php if ($hassidepost) { ?>               
                        <?php echo $OUTPUT->blocks_for_region('side-post'); ?>                  
                <?php } ?>
</div>
<!-- START OF FOOTER -->
    <?php if ($hasfooter) { ?>    
	<div id="footer">
		<div id="foot">
			<img src="<?php echo $CFG->wwwroot.'/pix/poweredby.png'; ?>" alt="" width="216" height="36" style="float:left;padding-top: 29px;" />
			<img src="<?php echo $CFG->wwwroot.'/pix/footer-right-add.jpg'; ?>" alt="" width="442" height="90" style="float:right;" />
	    </div>
	</div>
    <?php } ?> 
</div>
<?php echo $OUTPUT->standard_end_of_body_html(); ?>
</body>
</html>