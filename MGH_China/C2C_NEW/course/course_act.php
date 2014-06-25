<?php
  require_once('../config.php');
  require_once($CFG->dirroot .'/course/lib.php');
  global $CFG,$DB;  
     $course 	= optional_param('c', 0, PARAM_INT);
     $section 	= optional_param('sec', 0, PARAM_INT);
     $type      = optional_param('type', 0, PARAM_INT);
     $coursese = $DB->get_record('course',array('id'=>$course));
     $modinfo =& get_fast_modinfo($coursese);

    get_all_mods($coursese->id, $mods, $modnames, $modnamesplural, $modnamesused);
    foreach($mods as $modid=>$unused) {
        if (!isset($modinfo->cms[$modid])) {
            rebuild_course_cache($course->id);
            $modinfo =& get_fast_modinfo($COURSE);
            debugging('Rebuilding course cache', DEBUG_DEVELOPER);
            break;
        }
    }
     switch ($type)
     {
     	case 1 : $deaf =print_section_add_activities($coursese,$section,$modnames);
     			break;
     	case 2 :
     		break;
     		 
     }
 ?>
<script language="javascript" type="text/javascript">
var x=document.getElementById("section"+<?php echo $section;?>);
var y;
for (var i=0;i<x.length;i++)
  {
	if(x.elements[i].name == "jump")
	{
		alert(12);
		//x.elements[i].setAttribute('type','button');
		//x.elements[i].setAttribute('onclick','set_parent_act();');
		x.elements[i].id = 'jump';	
		
	}
	if(x.elements[i].value == "go")
	{
		 x.elements[i].setAttribute('type','button');
		x.elements[i].setAttribute('onclick','set_parent_act();');
		//x.elements[i].onclick = function() { return set_parent_act(y);} ;		
	}
  }
function set_parent_act(val)
{
	
	var x=document.getElementById('jump').selectedIndex;
	var surl=document.getElementsByTagName("option")[x].value;
	alert(surl);
	alert(M.cfg.wwwroot);
	return false;
	//opener.window.location = surl;
	//self.close();
}
</script>
