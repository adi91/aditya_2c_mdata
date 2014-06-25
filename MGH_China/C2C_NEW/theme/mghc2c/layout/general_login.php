<?php

$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-pre', $OUTPUT));
$hassidepost = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-post', $OUTPUT));
$haslogininfo = (empty($PAGE->layout_options['nologininfo']));

$showsidepre = ($hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT));
$showsidepost = ($hassidepost && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT));

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

	

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $PAGE->title ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
</head>
<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">
<div id="header"><?php if($CFG->theme=='mghchina'){ ?><img src="<?php echo $CFG->wwwroot.'/pix/logo.jpg'; ?>" alt="" width="215"/><?php } ?></div>
<?php //echo $OUTPUT->standard_top_of_body_html() ?>
<!--<div id="page">-->
<?php if ($hasheading || $hasnavbar) { ?>
    <!--<div id="page-header">
        <?php if ($hasheading) { ?>
        <h1 class="headermain"><?php //echo $PAGE->heading ?></h1>
        <div class="headermenu"><?php
            if ($haslogininfo) {
                //echo $OUTPUT->login_info();
            }
            if (!empty($PAGE->layout_options['langmenu'])) {
                //echo $OUTPUT->lang_menu();
            }
            //echo $PAGE->headingmenu
        ?></div><?php } ?>
        <?php if ($hascustommenu) { ?>
        <div id="custommenu"><?php //echo $custommenu; ?></div>
        <?php } ?>
       <?php if ($hasnavbar) { ?>
            <div class="navbar clearfix">
                <div class="breadcrumb"><?php //echo $OUTPUT->navbar(); ?></div>
                <div class="navbutton"> <?php //echo $PAGE->button; ?></div>
            </div>
        <?php } ?>
    </div>-->
<?php } ?>
<!-- END OF HEADER -->
<?php if ($hassidepre) { ?>
				<div id="container">		
					<!--Left side box starts-->
					<div class="col_left">
						<div class="dashboard">
							<div class="profile_block">
								<div class="profile">
									<div class="profileimage"><?php echo $OUTPUT->user_picture($user, array('size'=>100)); ?></div>
									<div class="profilename">
										<h2>Hello</h2>
										<h3><?php echo $USER->firstname.' '.$USER->lastname;?></h3>
									</div>
								<div class="profilestatus"><?php echo $OUTPUT->blocks_for_region('side-pre') ?></div>
						   </div>
						   <div class="verticle_navbar">
								<ul>
									<li><a href="<?php echo $CFG->wwwroot; ?>" class="home">home</a></li>
									<li><a href="<?php echo $CFG->wwwroot.'/course/courseview.php'; ?>" class="course">course</a></li>
									<li><a href="#" class="lesson">lesson</a></li>
									<li><a href="<?php echo $CFG->wwwroot.'/message/'; ?>" class="message">message</a></li>
									<li><a href="<?php echo $CFG->wwwroot.'/login/logout.php?sesskey='.$_SESSION['USER']->sesskey; ?>" class="logout<?php if($language == 'zh_cn')echo '_'.$language; ?>">logout</a></li>
								</ul>
								
						   </div>
						</div>
						<div class="profile_block_bottom"></div>
						<div class="calendar"><img src="<?php echo $CFG->wwwroot.'/pix/icon/calender.png'; ?>" alt="" width="253" height="227" /></div>
						<?php if(is_siteadmin()){ ?>
						
							
						
						<?php } ?>
					</div>
				</div>
                <!--
                    <div class="region-content">                        
						<div style="width:100%;background:#ff0;float:left;">Profile block
						</div>-->
						
                    <!--</div>
                </div>-->
                <?php } ?>
    <!--<div id="page-content">
        <div id="region-main-box">
            <div id="region-post-box">

                <div id="region-main-wrap">
                    <div id="region-main">
                        <div class="region-content">-->
                            <?php echo $OUTPUT->main_content() ?>
                        <!--</div>
                    </div>
                </div>-->

                

                <!--<?php if ($hassidepost) { ?>
                <div id="region-post" class="block-region">
                    <div class="region-content">
                        <?php //echo $OUTPUT->blocks_for_region('side-post') ?>
                    </div>
                </div>
                <?php } ?>-->
            <!--</div>
        </div>
    </div>-->

<!-- START OF FOOTER -->
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
</body>
</html>