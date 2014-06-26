<?php

// Display the whole course as "topics" made of of modules
// Included from "view.php"
/**
 * Evaluation topics format for course display - NO layout tables, for accessibility, etc.
 *
 * A duplicate course format to enable the Moodle development team to evaluate
 * CSS for the multi-column layout in place of layout tables.
 * Less risk for the Moodle 1.6 beta release.
 *   1. Straight copy of topics/faculty_dashboard_format.php
 *   2. Replace <table> and <td> with DIVs; inline styles.
 *   3. Reorder columns so that in linear view content is first then blocks;
 * styles to maintain original graphical (side by side) view.
 *
 * Target: 3-column graphical view using relative widths for pixel screen sizes
 * 800x600, 1024x768... on IE6, Firefox. Below 800 columns will shift downwards.
 *
 * http://www.maxdesign.com.au/presentation/em/ Ideal length for content.
 * http://www.svendtofte.com/code/max_width_in_ie/ Max width in IE.
 *
 * @copyright &copy; 2006 The Open University
 * @author N.D.Freear@open.ac.uk, and others.
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/completionlib.php');

$topic = optional_param('topic', -1, PARAM_INT);
$ctopic = optional_param('t', -1, PARAM_INT);

if ($topic != -1) {
    $displaysection = course_set_display($course->id, $topic);
} else {
    $displaysection = course_get_display($course->id);
}

$context = get_context_instance(CONTEXT_COURSE, $course->id);

if (($marker >=0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
    $course->marker = $marker;
    course_set_marker($course->id, $marker);
}

$streditsummary  = get_string('editsummary');
$stradd          = get_string('add');
$stractivities   = get_string('activities');
$strshowalltopics = get_string('showalltopics');
$strtopic         = get_string('topic');
$strgroups       = get_string('groups');
$strgroupmy      = get_string('groupmy');
$editing         = $PAGE->user_is_editing();

if ($editing) {
    $strtopichide = get_string('hidetopicfromothers');
    $strtopicshow = get_string('showtopicfromothers');
    $strmarkthistopic = get_string('markthistopic');
    $strmarkedthistopic = get_string('markedthistopic');
    $strmoveup   = get_string('moveup');
    $strmovedown = get_string('movedown');
}

// Print the Your progress icon if the track completion is enabled
$completioninfo = new completion_info($course);
echo $completioninfo->display_help_icon();
if($CFG->theme == 'mghc2c'){
?><div style="padding-left:30px;clear:both;">
<?php if(is_mhescordinator()){ ?>

	<a href="<?php echo $CFG->wwwroot; ?>/course/edit.php?id=<?php echo $course->id; ?>" class="buttonstyled">Edit Course</a>
	<a href="<?php echo $CFG->wwwroot; ?>/group/index.php?id=<?php echo $course->id; ?>" class="buttonstyled">Manage Classes</a>
	<a href="<?php echo $CFG->wwwroot; ?>/enrol/users.php?id=<?php echo $course->id; ?>&amp;page=0&amp;perpage=100&amp;sort=lastname&amp;dir=ASC" class="buttonstyled">Manage Instructors and Students</a>
	<a href="<?php echo $CFG->wwwroot; ?>/course/edit.php?category=<?php echo $course->category; ?>&amp;returnto=topcat" class="buttonstyled">Create Course</a>

<?php } ?>
<?php if(is_institute_cordinator()){ ?>
	<a href="<?php echo $CFG->wwwroot; ?>/group/index.php?id=<?php echo $course->id; ?>" class="buttonstyled">Manage Classes</a>
	<a href="<?php echo $CFG->wwwroot; ?>/enrol/users.php?id=<?php echo $course->id; ?>&amp;page=0&amp;perpage=100&amp;sort=lastname&amp;dir=ASC" class="buttonstyled">Manage Instructors and Students</a>

<?php } ?>
</div>
<?php
}else{
$category = $DB->get_record('course_categories',array('id'=>$course->category));
echo $OUTPUT->heading('This course is in center : '.$category->name, 2, 'headingblock header outline');

echo $OUTPUT->heading('Course Content of course : '.$course->fullname, 3, 'headingblock header outline');
}
// Note, an ordered list would confuse - "1" could be the clipboard or summary.
echo "<ul class='topics'>\n";

/// If currently moving a file then show the current clipboard
if (ismoving($course->id)) {
    $stractivityclipboard = strip_tags(get_string('activityclipboard', '', $USER->activitycopyname));
    $strcancel= get_string('cancel');
    echo '<li class="clipboard">';
    echo $stractivityclipboard.'&nbsp;&nbsp;(<a href="mod.php?cancelcopy=true&amp;sesskey='.sesskey().'">'.$strcancel.'</a>)';
    echo "</li>\n";
}

/// Print Section 0 with general activities

$section = 0;
$thissection = $sections[$section];
unset($sections[0]);
/*
if ($thissection->summary or $thissection->sequence or $PAGE->user_is_editing()) {

    // Note, no need for a 'left side' cell or DIV.
    // Note, 'right side' is BEFORE content.
    echo '<li id="section-0" class="section main clearfix" >';
    echo '<div class="left side">&nbsp;</div>';
    echo '<div class="right side" >&nbsp;</div>';
    echo '<div class="content">';
    if (!is_null($thissection->name)) {
        echo $OUTPUT->heading(format_string($thissection->name, true, array('context' => $context)), 3, 'sectionname');
    }
    echo '<div class="summary">';

    $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
    $summarytext = file_rewrite_pluginfile_urls($thissection->summary, 'pluginfile.php', $coursecontext->id, 'course', 'section', $thissection->id);
    $summaryformatoptions = new stdClass();
    $summaryformatoptions->noclean = true;
    $summaryformatoptions->overflowdiv = true;
    echo format_text($summarytext, $thissection->summaryformat, $summaryformatoptions);

    if ($PAGE->user_is_editing() && has_capability('moodle/course:update', $coursecontext)) {
        echo '<a title="'.$streditsummary.'" '.
             ' href="editsection.php?id='.$thissection->id.'"><img src="'.$OUTPUT->pix_url('t/edit') . '" '.
             ' class="iconsmall edit" alt="'.$streditsummary.'" /></a>';
    }
    echo '</div>';
	
    print_section($course, $thissection, $mods, $modnamesused);

    if ($PAGE->user_is_editing()) {
        print_section_add_menus($course, $section, $modnames);
    }

    echo '</div>';
    echo "</li>\n";
}
*/

/// Now all the normal modules by topic
/// Everything below uses "section" terminology - each "section" is a topic.

$section = 1;
$sectionmenu = array();

while ($section <= $course->numsections) {

    if (!empty($sections[$section])) {
        $thissection = $sections[$section];

    } else {
        $thissection = new stdClass;
        $thissection->course  = $course->id;   // Create a new section structure
        $thissection->section = $section;
        $thissection->name    = null;
        $thissection->summary  = '';
        $thissection->summaryformat = FORMAT_HTML;
        $thissection->visible  = 1;
        $thissection->id = $DB->insert_record('course_sections', $thissection);
    }

    $showsection = (has_capability('moodle/course:viewhiddensections', $context) or $thissection->visible or !$course->hiddensections);

    if (!empty($displaysection) and $displaysection != $section) {  // Check this topic is visible
        if ($showsection) {
            $sectionmenu[$section] = get_section_name($course, $thissection);
        }
        $section++;
        continue;
    }

    if ($showsection) {

        $currenttopic = ($course->marker == $section);

        $currenttext = '';
        if (!$thissection->visible) {
            $sectionstyle = ' hidden';
        } else if ($currenttopic) {
            $sectionstyle = ' current';
            $currenttext = get_accesshide(get_string('currenttopic','access'));
        } else {
            $sectionstyle = '';
        }
        if($ctopic== $section)
        {
        	$sectionstyle = ' current';
        }

        echo '<li id="section-'.$section.'" class="section main clearfix'.$sectionstyle.'" >'; //'<div class="left side">&nbsp;</div>';

            echo '<div class="left side">'.$currenttext.$section.'</div>';
        // Note, 'right side' is BEFORE content.
        echo '<div class="right side">';

        if ($displaysection == $section) {    // Show the zoom boxes
            echo '<a href="view.php?id='.$course->id.'&amp;topic=0#section-'.$section.'" title="'.$strshowalltopics.'">'.
                 '<img src="'.$OUTPUT->pix_url('i/all') . '" class="icon" alt="'.$strshowalltopics.'" /></a><br />';
        } else {
            $strshowonlytopic = get_string("showonlytopic", "", $section);
            echo '<a href="view.php?id='.$course->id.'&amp;topic='.$section.'" title="'.$strshowonlytopic.'">'.
                 '<img src="'.$OUTPUT->pix_url('i/one') . '" class="icon" alt="'.$strshowonlytopic.'" /></a><br />';
        }

        if ($PAGE->user_is_editing() && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {

            if ($course->marker == $section) {  // Show the "light globe" on/off
                echo '<a href="view.php?id='.$course->id.'&amp;marker=0&amp;sesskey='.sesskey().'#section-'.$section.'" title="'.$strmarkedthistopic.'">'.'<img src="'.$OUTPUT->pix_url('i/marked') . '" alt="'.$strmarkedthistopic.'" class="icon"/></a><br />';
            } else {
                echo '<a href="view.php?id='.$course->id.'&amp;marker='.$section.'&amp;sesskey='.sesskey().'#section-'.$section.'" title="'.$strmarkthistopic.'">'.'<img src="'.$OUTPUT->pix_url('i/marker') . '" alt="'.$strmarkthistopic.'" class="icon"/></a><br />';
            }

            if ($thissection->visible) {        // Show the hide/show eye
                echo '<a href="view.php?id='.$course->id.'&amp;hide='.$section.'&amp;sesskey='.sesskey().'#section-'.$section.'" title="'.$strtopichide.'">'.
                     '<img src="'.$OUTPUT->pix_url('i/hide') . '" class="icon hide" alt="'.$strtopichide.'" /></a><br />';
            } else {
                echo '<a href="view.php?id='.$course->id.'&amp;show='.$section.'&amp;sesskey='.sesskey().'#section-'.$section.'" title="'.$strtopicshow.'">'.
                     '<img src="'.$OUTPUT->pix_url('i/show') . '" class="icon hide" alt="'.$strtopicshow.'" /></a><br />';
            }
            if ($section > 1) {                       // Add a arrow to move section up
                echo '<a href="view.php?id='.$course->id.'&amp;random='.rand(1,10000).'&amp;section='.$section.'&amp;move=-1&amp;sesskey='.sesskey().'#section-'.($section-1).'" title="'.$strmoveup.'">'.
                     '<img src="'.$OUTPUT->pix_url('t/up') . '" class="icon up" alt="'.$strmoveup.'" /></a><br />';
            }

            if ($section < $course->numsections) {    // Add a arrow to move section down
                echo '<a href="view.php?id='.$course->id.'&amp;random='.rand(1,10000).'&amp;section='.$section.'&amp;move=1&amp;sesskey='.sesskey().'#section-'.($section+1).'" title="'.$strmovedown.'">'.
                     '<img src="'.$OUTPUT->pix_url('t/down') . '" class="icon down" alt="'.$strmovedown.'" /></a><br />';
            }
        }
        echo '</div>';
?>
<script language="javascript">
function get_act(val)
{
	
	if(document.getElementById('act_'+val).style.display=="")
	{
		document.getElementById('act_'+val).style.display="none";
	}else{
		document.getElementById('act_'+val).style.display="";
	}
	document.getElementById('res_'+val).style.display="none";
}

function get_res(val)
{
	
	if(document.getElementById('res_'+val).style.display=="")
	{
		document.getElementById('res_'+val).style.display="none";
	}else{
		document.getElementById('res_'+val).style.display="";
	}
	document.getElementById('act_'+val).style.display="none";
}
</script>
<?php 
        echo '<div class="content">';
        if (!has_capability('moodle/course:viewhiddensections', $context) and !$thissection->visible) {   // Hidden for students
            echo get_string('notavailable');
        } else {		
		?>
		<div class="classbox1wf">
			<div class="le-details3" style="margin-left:10px;">
				<div class="task-le">
					<div class="table2" style="padding-top:0px;">
							<h4 class="hd1" style="background:url('../theme/mghc2c/pix/book.png') no-repeat scroll 10px 10px #FFFFFF;padding-left:50px;">
							<?php
					            if (!is_null($thissection->name)) {
					                echo $thissection->name;
					            }else{
									echo 'Lesson '.$thissection->section;
								}
							?>
							</h4>
					</div>
					<div class="pr3">						
						<?php
							 if ($thissection->summary) {
							 ?><?php
				                $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
				                $summarytext = file_rewrite_pluginfile_urls($thissection->summary, 'pluginfile.php', $coursecontext->id, 'course', 'section', $thissection->id);
				                $summaryformatoptions = new stdClass();
				                $summaryformatoptions->noclean = true;
				                $summaryformatoptions->overflowdiv = true;
				                echo format_text($summarytext, $thissection->summaryformat, $summaryformatoptions);
							?><?php
				            } else {
				               echo '&nbsp;';
				            }
						?>
						
						<p class="numstudent"><?php echo $count_student; ?>  students are enrolled in this class </p>
						<?php
						$present_sql = "SELECT * from {custom_userpresent} where cid = ".$classid." AND sectionid = ".$thissection->id;
						$studentpresent = $DB->get_record_sql($present_sql);
						$studentcount=0;
						if($studentpresent){
							$students = explode('@',$studentpresent->studentid);
							$studentcount=0;
							foreach($students as $student){
							  if($student){
								$studentcount ++;
							  }
							}
						}
						?>
						<p>No. of student present in this class : <?php if($studentcount)echo $studentcount;else echo "Attendence not marked"; ?></p>
						<?php
						$classdate_sql = "SELECT * from {class_activity} where section = ".$thissection->id." AND groupid = ".$classid." AND module is null";
						$classdate = $DB->get_record_sql($classdate_sql);
						if($classdate->availablefrom){
							?><p><?php echo date('d F Y h:i a',$classdate->availablefrom) ;?></p><?php
						}
						if ($PAGE->user_is_editing()) {
			                //print_section_add_menus($course, $section, $modnaies);			
							echo '<div class="addmenus" style="margin-top:10px;">';
							echo '<span style="float:left;">';
							echo '<a href="#section='.$section.'" onclick="get_act('.$section.');"><img src="../pix/add_activity.jpg" alt="" width="98" height="22" /></a>&nbsp;';
							echo '<a href="#section='.$section.'" onclick="get_res('.$section.');"><img src="../pix/add_resource.jpg" alt="" width="98" height="22" /></a>';
							echo '</span>';
							if ($PAGE->user_is_editing() && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
				                echo '<span style="float:right;"><a title="'.$streditsummary.'" href="editsection.php?id='.$thissection->id.'">'.
				                     '<img src="'.$OUTPUT->pix_url('t/edit') . '" class="iconsmall edit" alt="'.$streditsummary.'" /></a></span>';
				            }
							echo '</div>';
							echo '<div id= "cor_act_'.$section.'" >'.print_section_add_activities($course, $section, $modnames).'</div>';
			                echo '<div id= "cor_act_'.$section.'" >'.print_section_add_menus_resouce($course, $section, $modnames).'</div>'; 
							               
						}
						?>
					</div>
				</div>
			</div>
			<div style="border: 0px solid red;float: right;margin: 10px;padding: 10px;width: 24%;">
				<p style="font-size:17px;font-weight:normal;">Lesson Completion</p>
				<div style="width:94%;float:left;text-align:right;">
				    <?php
					if($classdate->availablefrom && $classdate->availablefrom <= time()){
						$completion = 100;
					}else{
						$completion = 0;
					}					
					?>
					<p style="color:#31B91F;font-size:30px;"><?php echo $completion; ?>% </p>
					<div style="float:right;width:100%;background:#ff0000;">
						<span style="width:<?php echo $completion; ?>%;background:#31B91F;float:left;height:2px;">&nbsp;</span>
					</div>
				</div>
			</div>
		</div>
		<?php           
        }
        
        echo '</div>';
        echo "</li>\n";
    }

    unset($sections[$section]);
    $section++;
}

if (!$displaysection and $PAGE->user_is_editing() and has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
    // print stealth sections if present
    rebuild_course_cache($course->id);
    $modinfo = get_fast_modinfo($course);
    foreach ($sections as $section=>$thissection) {
        if (empty($modinfo->sections[$section])) {
            continue;
        }

        echo '<li id="section-'.$section.'" class="section main clearfix orphaned hidden">'; //'<div class="left side">&nbsp;</div>';

        echo '<div class="left side">';
        echo '</div>';
        // Note, 'right side' is BEFORE content.
        echo '<div class="right side">';
        echo '</div>';
        echo '<div class="content">';
        echo $OUTPUT->heading(get_string('orphanedactivities'), 3, 'sectionname');
        print_section($course, $thissection, $mods, $modnamesused);
        echo '</div>';
        echo "</li>\n";
    }
}


echo "</ul>\n";

if (!empty($sectionmenu)) {
    $select = new single_select(new moodle_url('/course/view.php', array('id'=>$course->id)), 'topic', $sectionmenu);
    $select->label = get_string('jumpto');
    $select->class = 'jumpmenu';
    $select->formid = 'sectionmenu';
    echo $OUTPUT->render($select);
}