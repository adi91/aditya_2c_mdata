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
	
	//require_once('config.php');
	require_once($CFG->dirroot . '/my/lib.php');
	require_once($CFG->dirroot . '/tag/lib.php');
	require_once($CFG->dirroot . '/user/profile/lib.php');
	require_once($CFG->libdir.'/filelib.php');
	require_once($CFG->libdir.'/blocklib.php');
global $SESSION;$language = $SESSION->lang;if($language == 'zh_cn'){$languagefolder = '/'.$language.'/';}else{$languagefolder = '/';}	
	
$basedir = explode("/",$_SERVER["SCRIPT_NAME"]);
$maindir = $basedir[count($basedir)-2];
$mainfile = $basedir[count($basedir)-1];
if($language == "zh_cn") 
{
	$class_home ="home_".$language;
	$class_course= "course_".$language;
	$class_lesson= "lesson_".$language;
	$class_message= "message_".$language;
	switch($maindir)
		{
			case 'message': $class_message = "message_".$language."sel";
							break;
			default : if(in_array($mainfile,$CFG->parent_course))
						$class_course= "course_".$language."sel";
						else if(in_array($mainfile,$CFG->parent_home))
						$class_home= "home_".$language."sel";
						break;
		}	
}
else
{
	$class_home ="home";
	$class_course= "course";
	$class_lesson= "lesson";
	$class_message= "message";
	switch($maindir)
		{
			case 'message': $class_message = "messagesel";
							break;
			default : if(in_array($mainfile,$CFG->parent_course))
						$class_course= "coursesel";
						else if(in_array($mainfile,$CFG->parent_home))
						$class_home= 'homesel';
						break;
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
<div id="header"><img src="<?php echo $CFG->wwwroot.'/pix/logo.jpg'; ?>" alt="" width="215"/><span style="text-align:right;"><?php echo $OUTPUT->lang_menu();?></span></div>

<!-- END OF HEADER -->        

<?php if ($hassidepre) { ?>
     <div id="container">	
	 
				    <!--Left side box starts-->
					<div class="col_left">
				    	<div class="dashboard">
					        <div class="profile_block">
					        	<div class="profile">
					            	<div class="profileimage"><?php echo $OUTPUT->user_picture($user, array('size'=>98)); ?></div>
					                <div class="profilename">
						                <h2><?php echo get_string('hello'); ?> </h2>
						                <h3><?php //echo $USER->firstname.' '.$USER->lastname;
													echo wordwrap(fullname($USER),13,"\n",true);
										?> </h3>
					                </div>
					            
										<?php //$mychildren = array();
										$startday=strtotime('0 day', strtotime(date('Y-m-d')));
									      $mychildern = get_mychildren();
										  //print_r($mychildern);
										  foreach($mychildern as $mychild){
										     //print_r($mychild->firstname);
											 ?><div class="profilestatus">
												<p><?php echo $mychild->firstname ." ". get_string('isstudying');?></p>
											    <?php if (!isset($hiddenfields['mycourses'])){
															//if ($mycourses = enrol_get_all_users_courses($mychild->id, true, NULL, 'visible DESC,sortorder ASC')) {
															if ($mycourses = apt_enrol_get_users_courses($mychild->id, true, NULL, 'visible DESC,sortorder ASC')) {
															    foreach($mycourses as $mycourse){
															    	
															    	$group = groups_get_groupby_role($mychild->id,$mycourse->id);
																	$per = get_course_completion($mycourse->id,$group->id);
																	
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
										?>							
					       </div>
					       <div class="verticle_navbar">
								<ul>
									<li><a href="<?php echo $CFG->wwwroot; ?>" class="<?php echo $class_home;?>">home</a></li>
									<li><a href="<?php echo $CFG->wwwroot.'/parent_courseview.php'; ?>" class="<?php echo $class_course;?>">course</a></li>
									<!-- <li><a href="#" class="<?php echo $class_lesson;?>">lesson</a></li> -->
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
						
						<div id = "custom_blocks" class="calendar">
						<!--<img src="<?php echo $CFG->wwwroot.'/pix/icon/calender.png'; ?>" alt="" width="253" height="227" />-->
						  <?php //if(is_siteadmin()){ ?>
								     <?php echo $OUTPUT->blocks_for_region('side-pre') ?>
								  <?php //} ?>
						  
					    </div>  
				    </div>
				</div>                   
                <?php } ?>
				<div style="float:left;width:70%;margin-top:5px;"><?php echo $breadcrumb; ?> <?php echo $OUTPUT->main_content(); ?></div>
                <?php if ($hassidepost) { ?>               
                        <?php echo $OUTPUT->blocks_for_region('side-post'); ?>                  
                <?php } ?>
</div>
<!-- START OF FOOTER -->
    <?php if ($hasfooter) { ?>    
	<div id="footer">
		<div id="foot">
			<img src="<?php echo $CFG->wwwroot.'/pix/poweredby.png'; ?>" alt="" width="170" height="28" style="float:left;padding-top: 29px;" />
			<img src="<?php echo $CFG->wwwroot.'/pix/footer-right-add.jpg'; ?>" alt="" width="350" height="71" style="float:right;" />
	    </div>
	</div>
    <?php } ?> 
</div>
<?php echo $OUTPUT->standard_end_of_body_html(); ?>
</body>
</html>