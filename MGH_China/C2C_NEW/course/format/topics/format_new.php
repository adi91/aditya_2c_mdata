<?php

// Display the whole course as "topics" made of of modules
// Included from "view.php"
/**
 * Evaluation topics format for course display - NO layout tables, for accessibility, etc.
 *
 * A duplicate course format to enable the Moodle development team to evaluate
 * CSS for the multi-column layout in place of layout tables.
 * Less risk for the Moodle 1.6 beta release.
 *   1. Straight copy of topics/format.php
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
require_once($CFG->dirroot.'/group/lib.php');

$topic = optional_param('topic', -1, PARAM_INT);
$startday=strtotime('0 day', strtotime(date('Y-m-d')));
$endday= $startday + (3600 *24) -1;
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
//echo $completioninfo->display_help_icon();

$OUTPUT->heading(get_string('topicoutline'), 2, 'headingblock header outline');

// Note, an ordered list would confuse - "1" could be the clipboard or summary.
//echo "<ul class='topics'>\n";

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

if ($thissection->summary or $thissection->sequence or $PAGE->user_is_editing()) {

    // Note, no need for a 'left side' cell or DIV.
    // Note, 'right side' is BEFORE content.
   // echo '<li id="section-0" class="section main clearfix" >';
   // echo '<div class="left side">&nbsp;</div>';
  //  echo '<div class="right side" >&nbsp;</div>';
  //  echo '<div class="content">';
 
    if (!is_null($thissection->name)) {
        $section_less = $OUTPUT->heading(format_string($thissection->name, true, array('context' => $context)), 3, 'sectionname');
    }
  //  echo '<div class="summary">';

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
   // echo '</div>';
	
    //print_section($course, $thissection, $mods, $modnamesused);

    if ($PAGE->user_is_editing()) {
     //   print_section_add_menus($course, $section, $modnames);
    }

   // echo '</div>';
   // echo "</li>\n";
}


/// Now all the normal modules by topic
/// Everything below uses "section" terminology - each "section" is a topic.

$section = 1;
$sectionmenu = array();
$cntLess=1;
$section_less="";

while ($section <= $course->numsections) {
	$summary="";
   if (!empty($sections[$section])) {
        $thissection = $sections[$section];

    }
    /* else {
        $thissection = new stdClass;
        $thissection->course  = $course->id;   // Create a new section structure
        $thissection->section = $section;
        $thissection->name    = null;
        $thissection->summary  = '';
        $thissection->summaryformat = FORMAT_HTML;
        $thissection->visible  = 1;
        $thissection->id = $DB->insert_record('course_sections', $thissection);
    }*/
  
   // print_r($sections[$section]);
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

      //  echo '<li id="section-'.$section.'" class="section main clearfix'.$sectionstyle.'" >'; //'<div class="left side">&nbsp;</div>';

        //    echo '<div class="left side">'.$currenttext.$section.'</div>';
        // Note, 'right side' is BEFORE content.
        //echo '<div class="right side">';

       /* if ($displaysection == $section) {    // Show the zoom boxes
            echo '<a href="view.php?id='.$course->id.'&amp;topic=0#section-'.$section.'" title="'.$strshowalltopics.'">'.
                 '<img src="'.$OUTPUT->pix_url('i/all') . '" class="icon" alt="'.$strshowalltopics.'" /></a><br />';
        } else {
            $strshowonlytopic = get_string("showonlytopic", "", $section);
            echo '<a href="view.php?id='.$course->id.'&amp;topic='.$section.'" title="'.$strshowonlytopic.'">'.
                 '<img src="'.$OUTPUT->pix_url('i/one') . '" class="icon" alt="'.$strshowonlytopic.'" /></a><br />';
        }*/

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
      //  echo '</div>';

     //echo '<div class="content">';
     
        if (!has_capability('moodle/course:viewhiddensections', $context) and !$thissection->visible) {   // Hidden for students
            echo get_string('notavailable');
        } else {
            if (!is_null($thissection->name)) {
                $section_less =$OUTPUT->heading(format_string($thissection->name, true, array('context' => $context)), 3, 'sectionname');
            }
            else
            {
            	 $section_less ="Lesson ".$cntLess;
            }
            
           
          //  echo '<div class="summary">';
            if ($thissection->summary) {
                $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
                $summarytext = file_rewrite_pluginfile_urls($thissection->summary, 'pluginfile.php', $coursecontext->id, 'course', 'section', $thissection->id);
                $summaryformatoptions = new stdClass();
                $summaryformatoptions->noclean = true;
                $summaryformatoptions->overflowdiv = true;
               $summary= format_text($summarytext, $thissection->summaryformat, $summaryformatoptions);
            } else {
               echo '&nbsp;';
            }

            /*if ($PAGE->user_is_editing() && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
                echo ' <a title="'.$streditsummary.'" href="editsection.php?id='.$thissection->id.'">'.
                     '<img src="'.$OUTPUT->pix_url('t/edit') . '" class="iconsmall edit" alt="'.$streditsummary.'" /></a><br /><br />';
            }*/
           // echo '</div>';
          // 	$section_perc= get_topic_completion_percentage($course->id,$thissection->id,$USER->id);
          $sec_act_group="";
          $group_act="";
          $sec_act_group="";
          $act_class="";
          if($parentuser>0)
          {
          	$group_act=groups_get_groupby_role($parentuser,$course->id);
            $sect_act= get_topic_completion_activity($course->id,$thissection->id,$parentuser);
            $sec_act_group=get_group_activity($group_act->id,$thissection->section,$course->id);
          }
          else
          {
          	$group_act=groups_get_groupby_role($USER->id,$course->id);
             $sect_act= get_topic_completion_activity($course->id,$thissection->id,$USER->id);
           	 $sec_act_group=get_group_activity($group_act->id,$thissection->section,$course->id);
          }
        
         	if($sec_act_group->availablefrom){
	            if($sec_act_group->availablefrom < $startday){
	            	$act_class="le-number-gr";
	            }else if($sec_act_group->availablefrom >= $startday && $sec_act_group->availablefrom < $endday){
	            	$act_class="le-number-bl";
	            }
         	    else{
            	$act_class="le-number-ye";
            	}
         	}
         	else
         	{
         		$act_class="le-number-ye";
         	}
            
          
          	if($sec_act_group->sectionno %2==1) { $gtime=$group_act->starttime; } else {$gtime= $group_act->starttime2;}
        	$lesson_date=$DB->get_record('class_activity',array('section'=>$thissection->id,'course'=>$course->id,'groupid'=>$group_act->id,'sectionno'=>$thissection->section));
			//print_r($lesson_date);
			///echo date("d M y ",$lesson_date->availablefrom);
        	//echo get_topic_completion_percentage($course->id,$thissection->id,$USER->id);
            echo "<div class='box1wf'>";
            if(is_student()){
            	echo "<a href='".$CFG->wwwroot."/index_student.php?courseid=".$course->id."&sectionid=".$thissection->section."'>
            		<div class=".$act_class.">";
              		echo "<span>".$cntLess."</span>";
                echo "</div>";
            }else
            {
            	echo "<div class=".$act_class.">";
              		echo "<span>".$cntLess."</span>";
                echo "</div>";
            }
             	echo "<div class='le-details'>";
                 echo "<div class='task-le'>";
                	echo "<div class='table'>";
                	if(is_student()){
                		 echo "<div style='display:block' class='newdivel'><h4 class='hd1'>".$section_less."</h4>";
                	}else
                	{
	                     echo "<div style='display:block' class='newdivel'><h4 class='hd1'>".$section_less."</h4>";
                	}
	                     echo "<span class='pr1g'>".get_string('studcompletion')."</span></div>";
	                      		echo "<div class='pr1'>".$summary."</div>";
	                       		 echo "<div class='tableaside'><div class='calc'>".$sect_act['percentage']."</div><div class='yel-bar'><div class='gre-bar' style='width:".$sect_act['percentage']."%;'></div></div></div>";
	                    echo "</div>";
					echo "</div>";
				
		                echo "<div class='taskcomplete-le'>";
			                echo "<div class='pr1gl'>";
			                if($sec_act_group->availablefrom){
			                  echo "<span>".date("d M y D H:i",$sec_act_group->availablefrom)."</span>";
			                	}
			                        echo "<div class='right'>";
			                        echo "<span class='right'>".$sect_act['compcount']." ".get_string('taskcomptleted')."</span><br/>";
			                      //  echo "<span class='right'><span class='edu-icon_01'></span><span class='edu-icon_01'></span><span class='edu-icon_03'></span><span class='edu-icon_02'></span><span class='edu-icon_02'></span><span class='edu-icon_02'></span></span></div>";
			                       echo "<span class='right'>".$sect_act['act']."</span></div>";
			                  echo "</div>";
							echo "</div>";
		                echo "</div>";
					if(is_student())
					{
						echo "</a>";
					}
            echo "</div>";
            
           /// print_section_new($course, $thissection, $mods, $modnamesused);
           
            if ($PAGE->user_is_editing()) {
             //   print_section_add_menus($course, $section, $modnames);
            }
        }

      //  echo '</div>';
     //   echo "</li>\n";
    }

    unset($sections[$section]);
    $section++;
    $cntLess++;
}
/*
if (!$displaysection and $PAGE->user_is_editing() and has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
    // print stealth sections if present
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

*/
//echo "</ul>\n";

if (!empty($sectionmenu)) {
    $select = new single_select(new moodle_url('/course/view.php', array('id'=>$course->id)), 'topic', $sectionmenu);
    $select->label = get_string('jumpto');
    $select->class = 'jumpmenu';
    $select->formid = 'sectionmenu';
    echo $OUTPUT->render($select);
}
