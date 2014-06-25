<?php 
ob_start();
 require_once('../config.php');
 require_once($CFG->libdir.'/blocklib.php');
 require_once($CFG->dirroot.'/course/lib.php');
 require_once($CFG->dirroot.'/mod/scorm/locallib.php'); 
 require_once($CFG->dirroot.'/mod/resource/locallib.php');
 require_once($CFG->dirroot.'/mod/imscp/locallib.php');
 require_once($CFG->dirroot.'/group/lib.php');
global $USER,$COURSE,$DB;
require_login();
$action   = optional_param('action',0,PARAM_ALPHANUM);
$courseid     =optional_param('courseid',0,PARAM_INT);
$resouceid     =optional_param('videoid',0,PARAM_INT);
$sectionid     =optional_param('sectionid',0,PARAM_INT);
$default     =optional_param('sel_def',0,PARAM_INT);
$startday=strtotime('0 day', strtotime(date('Y-m-d')));
$endday= $startday + (3600 *24) -1;
//$startday=date('Ymd');
switch($action)
{
	case 'coursedetails':	$param = array($courseid);
							$course =$DB->get_record_select("course","id=?",$param);
							$group= groups_get_groupby_role($USER->id,$courseid);
							$jsscript="";
							$modinfo = get_fast_modinfo($course);
							get_all_mods($course->id, $mods, $modnames, $modnamesplural, $modnamesused);
							$sections = array_slice(get_all_sections($course->id), 0, $course->numsections+1, true);
							//$htmllesson = '<div id="carouselcoursedetails" class="lesson-list" >';
							$htmllesson = '<ul id="carouselcoursedetails" class="lesson-list" style="width:346px;">';
							$arr_repsonse="";
							$firstsection ="";
							$section_arr = array();
							$timenow = time();
						    $weekdate = $course->startdate;    // this should be 0:00 Monday of that week
						    $weekdate += 7200;                 // Add two hours to avoid possible DST problems
						    $sectionweek = 1;
						    $weekofseconds = 604800;
						    //echo "<br>course->enddate:".
						    $course->enddate = $course->startdate + ($weekofseconds * $course->numsections);
							while ($weekdate < $course->enddate) {
						        	$nextweekdate = $weekdate + ($weekofseconds);
						        	$currentweek = (($weekdate <= $timenow) && ($timenow < $nextweekdate));
						        	if($currentweek)
						        	{
									$setSectionid=$sectionweek;					        		
						        	}
									$sectionweek++;
		            				$weekdate = $nextweekdate;
									}
									$tempvisit ='';
									
								while(list($key,$modid) = each($mods))
								{   
									reset($sections);
									$assign=0;
									$sect_np=1;
									foreach ($sections as $key_section => $section) 
									{
										
										
										$sectionname = get_section_name($course,$section);
										if (!array_key_exists($section->section, $modinfo->sections) || $key_section == 0) 
										{
											continue;
										}
										
										
										if(!in_array($sectionname,$section_arr) && $modid->sectionnum == $key_section)
										{
											
											$section_arr[$section->section] = $sectionname;
											$sec_act=get_group_activity($group->id,$section->section,$courseid);
											$get_act_record_count=get_group_act_no($group->id,$courseid); 
											if($sec_act->availablefrom < $startday && $get_act_record_count >1){
												$class_small='greencarosmall';	
											}elseif($sec_act->availablefrom >= $startday && $sec_act->availablefrom < $endday && $get_act_record_count >1)
											{
												$class_small='bluecarosmall';	
											}
											else
											{
												$class_small='yellowcarosmall';	
											}
											
											
											$htmllesson .= "<li id='section_".$section->section."' class='$class_small'>";
											/*if($section->section < $setSectionid){
												$htmlvisit = '<span class="compvisit">Previous Weeks</span>';
											}
											if($section->section == $setSectionid){
												$htmlvisit = '<span class="currvisit">This Week</span>';
											}
											if($section->section > $setSectionid){
												$htmlvisit = '<span class="nextvisit">Next Weeks</span>';
											}
											//echo $tempvisit."*****".$htmlvisit;
											if($tempvisit != $htmlvisit)
											{
											
												$tempvisit = $htmlvisit;
												$htmllesson .=$htmlvisit;
											}*/
											
											
											if($sec_act->availablefrom < $startday){
											$htmllesson .="<a href='#' onclick='getsectionload(".$section->section.",".$course->id.")' id='sel_".$section->section."'><span>".$sect_np."</span></a>";
											$firstsection = $section->section;
											}elseif($sec_act->availablefrom >= $startday && $sec_act->availablefrom < $endday)
											{
											$htmllesson .="<a href='#' onclick='getsectionload(".$section->section.",".$course->id.")' id='sel_".$section->section."'><span>".$sect_np."</span></a>";	
											$firstsection = $section->section;
											}
											else
											{
											$htmllesson .="<a href='#' onclick='getsectionload(".$section->section.",".$course->id.")' id='sel_".$section->section."'><span>".$sect_np."</span></a>";
											}
											$htmllesson .= "</li>";
										}
										
									$sect_np++;
									}
								}
							
								
							$htmllesson .='</ul>';
							
							$courses = enrol_get_users_courses($USER->id, 'visible DESC,sortorder ASC', '*', false, 1000);
							
							if(count($courses) > 0)
							{	
								foreach ($courses as $c) 
								{
									$jsscript.=" if(document.getElementById('course_".$c->id."')) { document.getElementById('course_".$c->id."').className='';}";
								}
							}
							
							
							if($sectionid > 0 )
								{
										$jsscript.=" removeSectClass(".$sectionid."); ";
										$jsscript.=" getsectionload(".$sectionid.",".$course->id."); ";
										$jsscript.=" if(document.getElementById('section_".$sectionid."')) { document.getElementById('section_".$sectionid."').className+= ' yui-carousel-item-selected'; }";
								}
							elseif($firstsection != '')
								{
										$jsscript.=" getsectionload(".$firstsection.",".$course->id.",1); ";
										$sectionid = $firstsection;
										$jsscript.=" if(document.getElementById('section_".$sectionid."')) { document.getElementById('section_".$sectionid."').className+= ' yui-carousel-item-selected';}";
								}	
							//$jsscript.=" if(document.getElementById('course_".$course->id."')) { document.getElementById('course_".$course->id."').className='yui-carousel-item-selected_';}";
							
							
							
							$arr_repsonse = array();
							$arr_repsonse["htmllesson"]=$htmllesson;
							$arr_repsonse["jsscript"]=$jsscript;
							echo json_encode($arr_repsonse);   
							break;
	case 'sectiondetails':	$param = array($courseid);
							$course =$DB->get_record_select("course","id=?",$param);
							$group= groups_get_groupby_role($USER->id,$courseid);
							$modinfo = get_fast_modinfo($course);
							get_all_mods($course->id, $mods, $modnames, $modnamesplural, $modnamesused);	
							$sections = array_slice(get_all_sections($course->id), 0, $course->numsections+1, true);
							$jsscript ="";
							$htmlassessment ="";
							$arr_repsonse="";
							$htmlassessment=array();
							$vcnt =  1;
							$rcnt =  0;
							$firstvideoid = '';
							//$htmlvideo = '<ul id="video-list" class="p-lists">';
							 while(list($key,$modid) = each($mods))
							{   
								reset($sections);
							
								foreach ($sections as $key_section => $section) 
								{	
									$get_act_record_count="";
									if($section->section == $sectionid && $modid->sectionnum == $key_section && $modid->visible==true)
									{
										$sectionname = get_section_name($course,$section);
										//$htmlassessment .= $section->section ."******".$modid->modname;
										if(strtolower($modid->modname)=='scorm')
										{
											
											$cm = $DB->get_record("course_modules", array("id"=>$modid->id));
											$scorm = $DB->get_record("scorm", array("id"=>$modid->instance));
											$scoes = $DB->get_record("scorm_scoes", array("scorm"=>$cm->instance,'scormtype'=>'sco'));
											$act_com =get_activity_completion_mod($cm->id);
											$sec_act=get_group_les_activity($group->id,$section->id,$courseid);
											$get_act_record_count=get_group_act_no($group->id,$courseid); 
											$act_com== 1? $class='greenact' : $class='greenactno' ;
											if($sec_act->availablefrom < $startday && $get_act_record_count >1){
												$act_com== 1? $class='greenact' : $class='greenactno' ;
												
											}elseif($sec_act->availablefrom >= $startday && $sec_act->availablefrom < $endday && $get_act_record_count >1){
												$class='blueact';
											}else{
												$class='yellowact';
											}
											
											$htmlscorm ='<li class='.$class.'  title="'.htmlentities($modid->name).'">';
											if (($scoes->organization == '') && ($scoes->launch == '')) 
											{
												$orgidentifier = $scoes->identifier;
											} 
											else 
											{
												$orgidentifier = $scoes->organization;
											}
											$htmlscorm .="<form name='frm".$key."' id='frm".$key."' action='".$CFG->wwwroot."/mod/scorm/player.php' method='post'>";
											if($sec_act->availablefrom < $startday){
											$htmlscorm .="<a href='#' onclick='act_sel(".$key."); frm".$key.".submit();' id='sel_frm_".$key."' title='".$modid->name."'>";
											}elseif($sec_act->availablefrom >= $startday && $sec_act->availablefrom < $endday){
											$htmlscorm .="<a href='#'  onclick='act_sel(".$key."); frm".$key.".submit();' id='sel_frm_".$key."' >";	
											}else
											{
											$htmlscorm .="<a href='#' >";	
											}
											$htmlscorm .='<div>';
											$htmlscorm .='<h4>'.get_string('studentactivity').' :'.$vcnt.'</h4>';
											$htmlscorm .='<p>'.wordwrap(substr($modid->name,0,30),20,"</br>");
											if(strlen($modid->name) >30)
											{
											$htmlscorm .= '...';	
											}
											$htmlscorm .= '</p>';
											$htmlscorm .='</div></a>';
											//$htmlscorm .='<a href="#" onclick="frm'.$key.'.submit();">'.$modid->name.'</a>';
											$htmlscorm .= '<input type="hidden" name="mode" value="normal" /><input type="hidden" name="scoid"  value=""/><input type="hidden" name="cm" value="'.$cm->id.'"/><input type="hidden" name="currentorg" value="'.$orgidentifier.'" /></form>';
											$htmlscorm .='</li>';
											$htmlassessment[] = $htmlscorm;
											$vcnt++;
										}
										else if(strtolower($modid->modname)=='resource')
										{
											$resource = $DB->get_record("resource", array("id"=>$modid->instance));
											$cm = get_coursemodule_from_instance('resource', $resource->id, $resource->course, false, MUST_EXIST);
											$act_com =get_activity_completion_mod($cm->id);
											$sec_act=get_group_les_activity($group->id,$section->id,$courseid);
											$get_act_record_count=get_group_act_no($group->id,$courseid);
											//$act_com== 1? $class='greenact' : $class='greenactno' ;
											if($sec_act->availablefrom < $startday && $get_act_record_count >1){
												$act_com== 1? $class='greenact' : $class='greenactno' ;
												
											}elseif($sec_act->availablefrom >= $startday && $sec_act->availablefrom < $endday && $get_act_record_count >1){
												$class='blueact';
											}else{
												$class='yellowact';
											}
											
											$fs = get_file_storage();
											$files = $fs->get_area_files($modid->context->id, 'mod_resource', 'content', 0, 'sortorder');
											$file = array_pop($files);
											//$path = '/'.$modid->context->id.'/mod_resource/content/'.$resource->revision.$file->get_filepath().$file->get_filename();
											//$fullurl = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, false);
											$mimetype = $file->get_mimetype();
											$mime_type_video = array('audio/mp3','video/x-flv','application/x-shockwave-flash','video/quicktime','video/mpeg','audio/x-pn-realaudio','video/avi','video/x-ms-wm','video/x-ms-wmv');
											$title    = $resource->name;
											if(in_array($mimetype, $mime_type_video))
											{
												
												$htmlresource = '<li class='.$class.'  title="'.htmlentities($modid->name).'">';	
												$htmlresource .="<form name='frm".$key."' id='frm".$key."' action='".$CFG->wwwroot."/mod/resource/view.php' method='post'>";
													if($sec_act->availablefrom < $startday && $get_act_record_count >1){
													//$htmlother .="<a href='#' class=$class onclick='frm".$key.".submit();' id='sel_frm_".$key."'>";
													$htmlresource .="<a href='#'  onclick='act_sel(".$key."); load_video(".$modid->id.",".$cm->id.");' id='sel_frm_".$key."' >";
													}elseif($sec_act->availablefrom >= $startday && $sec_act->availablefrom < $endday && $get_act_record_count >1){
													//$htmlother .="<a href='#' class='blueact' onclick='frm".$key.".submit();' id='sel_frm_".$key."'>";
													$htmlresource .="<a href='#'  onclick='act_sel(".$key."); load_video(".$modid->id.",".$cm->id.");' id='sel_frm_".$key."' >";
													}else
													{
													$htmlresource .="<a href='#'  >";	
													}
													$htmlresource .='<div>';
													$htmlresource .='<h4>'.get_string('studentactivity').' :'.$vcnt.'</h4>';
													$htmlresource .='<p>'.wordwrap(substr($modid->name,0,30),20,"</br>");
													if(strlen($modid->name) >30)
													{
													$htmlresource .= '...';	
													}
													$htmlresource .= '</p>';
													$htmlresource .='</div></a>';
													$htmlresource .='</form>';
													$htmlresource .= "</li>";
													$htmlassessment[] = $htmlresource;
													$vcnt++;
											
											}
											else
											{
												
												$path = '/'.$modid->context->id.'/mod_resource/content/'.$resource->revision.$file->get_filepath().$file->get_filename();
												$fullurl = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, false);
												$mimetype_arr_ = explode("/",$mimetype);
												$mimetype_arr = explode(".",$mimetype_arr_[1]);
												$mime_type=array_pop($mimetype_arr);
												if($sec_act->availablefrom < $startday && $get_act_record_count >1){
													$act_com== 1? $class='greenact' : $class='greenactno' ;
													
												}elseif($sec_act->availablefrom >= $startday && $sec_act->availablefrom < $endday && $get_act_record_count >1){
													$class='blueact';
												}else{
													$class='yellowact';
												}
												$htmlresource = '<li class='.$class.'  title="'.htmlentities($modid->name).'">';
												if($sec_act->availablefrom < $startday && $get_act_record_count >1){
												//$htmlresource .="<form name='frm".$modid->id."' id='frm".$modid->id."' action='".$fullurl."' target='_blank'>";
												$htmlresource .="<a href='#'  onclick=load_resou(".$modid->id.",".$cm->id.",'".rawurlencode($fullurl)."',".$key."); id='sel_frm_".$key."' >";
												//$htmlresource .="<a href='#' class=$class onclick='frm".$key.".submit();' id='sel_frm_".$key."'>";
												}
												elseif($sec_act->availablefrom >= $startday && $sec_act->availablefrom < $endday && $get_act_record_count >1){
												//$htmlresource .="<form name='frm".$modid->id."' id='frm".$modid->id."' action='".$fullurl."' target='_blank'>";
												$htmlresource .="<a href='#'  onclick=load_resou(".$modid->id.",".$cm->id.",'".rawurlencode($fullurl)."',".$key."); id='sel_frm_".$key."'  >";
												//$htmlresource .="<a href='#' class='blueact' onclick='frm".$key.".submit();' id='sel_frm_".$key."'>";
												}else
												{
												$htmlresource .="<form name='frm".$modid->id."' id='frm".$modid->id."' action='".$fullurl."' target='_blank'>";
												
												$htmlresource .="<a href='#'  >";	
												}
												$htmlresource .='<div>';
												$htmlresource .='<h4>'.get_string('studentactivity').' :'.$vcnt.'</h4>';
												$htmlresource .='<p>'.wordwrap(substr($modid->name,0,30),20,"</br>");
												if(strlen($modid->name) >30)
												{
												$htmlresource .= '...';	
												}
												$htmlresource .= '</p>';
												$htmlresource .='</div></a>';
												$htmlresource .='</form>';
												$htmlresource .= "</li>";
												$htmlassessment[] = $htmlresource;
												$vcnt++;
												
											}
											
										}
										
										else if(strtolower($modid->modname)=='url')
										{
											$url = $DB->get_record("url", array("id"=>$modid->instance));
											$cm = $DB->get_record("course_modules", array("id"=>$modid->id));
											$act_com =get_activity_completion_mod($cm->id);
											$sec_act=get_group_les_activity($group->id,$section->id,$courseid);
											$get_act_record_count=get_group_act_no($group->id,$courseid); 
											//$act_com== 1? $class='greenact' : $class='greenactno' ;
											$fs = get_file_storage();
											$files = $fs->get_area_files($modid->context->id, 'mod_resource', 'content', 0, 'sortorder');
											$file = array_pop($files);
											if($sec_act->availablefrom < $startday && $get_act_record_count >1){
													$act_com== 1? $class='greenact' : $class='greenactno' ;
													
												}elseif($sec_act->availablefrom >= $startday && $sec_act->availablefrom < $endday && $get_act_record_count >1){
													$class='blueact';
												}else{
													$class='yellowact';
												}
											//$path = '/'.$modid->context->id.'/mod_resource/content/'.$resource->revision.$file->get_filepath().$file->get_filename();
											//$fullurl = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, false);
											//$mimetype = $file->get_mimetype();
											//$mime_type_video = array('audio/mp3','video/x-flv','application/x-shockwave-flash','video/quicktime','video/mpeg','audio/x-pn-realaudio','video/avi','video/x-ms-wm','video/x-ms-wmv');
											$title    = $resource->name;
												$htmlresource = '<li class='.$class.'  title="'.htmlentities($modid->name).'">';	
												$htmlresource .="<form name='frm".$key."' id='frm".$key."' action='".$CFG->wwwroot."/mod/url/view.php' method='post'>";
													if($sec_act->availablefrom < $startday && $get_act_record_count >1){
													//$htmlresource .="<a href='#' class=$class onclick='frm".$key.".submit();' id='sel_frm_".$key."'>";
													$htmlresource .="<a href='#'  onclick=load_url(".$modid->id.",'".rawurlencode($url->externalurl)."',".$key."); id='sel_frm_".$key."' >";
													}elseif($sec_act->availablefrom >= $startday && $sec_act->availablefrom < $endday && $get_act_record_count >1){
													$htmlresource .="<a href='#'  onclick= load_url(".$modid->id.",'".rawurlencode($url->externalurl)."',".$key."); id='sel_frm_".$key."' >";
													}else
													{
													$htmlresource .="<a href='#'  >";	
													}
													$htmlresource .='<div>';
													$htmlresource .='<h4>'.get_string('studentactivity').' :'.$vcnt.'</h4>';
													$htmlresource .='<p>'.wordwrap(substr($modid->name,0,30),20,"</br>");
													if(strlen($modid->name) >30)
														{
														$htmlresource .= '...';	
														}
														$htmlresource .= '</p>';
													$htmlresource .='</div></a>';
													$htmlresource .="<input type='hidden' name='id' value='$cm->id'/>";
													$htmlresource .='</form>';
													$htmlresource .= "</li>";
													$htmlassessment[] = $htmlresource;
													$vcnt++;
											
											
										}
										else if(strtolower($modid->modname)=='imscp')
											{
												$url = $DB->get_record("imscp", array("id"=>$modid->instance));
												$cm = $DB->get_record("course_modules", array("id"=>$modid->id));
												$act_com =get_activity_completion_mod($cm->id);
												$sec_act=get_group_les_activity($group->id,$section->id,$courseid);
												$get_act_record_count=get_group_act_no($group->id,$courseid); 
												//$act_com== 1? $class='greenact' : $class='greenactno' ;
												if($sec_act->availablefrom < $startday && $get_act_record_count >1){
													$act_com== 1? $class='greenact' : $class='greenactno' ;
													
												}elseif($sec_act->availablefrom >= $startday && $sec_act->availablefrom < $endday && $get_act_record_count >1){
													$class='blueact';
												}else{
													$class='yellowact';
												}
												/*$fs = get_file_storage();
												$files = $fs->imscp_get_file_areas($modid->context->id, 'mod_imscp', 'content', 0, 'sortorder');
												$file = array_pop($files);
												$path = '/'.$modid->context->id.'/mod_imscp/content/'.$resource->revision.$file->get_filepath().$file->get_filename();
												$fullurl = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, false);*/
												$fs=imscp_parse_structure($url,$cm->id);
												$items = unserialize($url->structure);
											    $first = reset($items);
											    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
											    $urlbase = "$CFG->wwwroot/pluginfile.php";
											    $path = '/'.$context->id.'/mod_imscp/content/'.$url->revision.'/'.$first['href'];
										    	$firsturl = file_encode_url($urlbase, $path, false);
												
												
												//$mimetype = $file->get_mimetype();
												//$mime_type_video = array('audio/mp3','video/x-flv','application/x-shockwave-flash','video/quicktime','video/mpeg','audio/x-pn-realaudio','video/avi','video/x-ms-wm','video/x-ms-wmv');
												$title    = $resource->name;
													$htmlresource = '<li class='.$class.'  title="'.htmlentities($modid->name).'">';	
													$htmlresource .="<form name='frm".$key."' id='frm".$key."' action='".$CFG->wwwroot."/mod/imscp/view.php' method='post'>";
														if($sec_act->availablefrom < $startday && $get_act_record_count >1){
														//$htmlresource .="<a href='#' class=$class onclick='frm".$key.".submit();' id='sel_frm_".$key."'>";
														$htmlresource .="<a href='#'  onclick=load_imscp('".rawurlencode($firsturl)."',".$key."); id='sel_frm_".$key."' >";
														}elseif($sec_act->availablefrom >= $startday && $sec_act->availablefrom < $endday && $get_act_record_count >1){
														//$htmlresource .="<a href='#' class='blueact' onclick='frm".$key.".submit();' id='sel_frm_".$key."'>";
														$htmlresource .="<a href='#'  onclick=load_imscp('".rawurlencode($firsturl)."',".$key."); id='sel_frm_".$key."' >";
														}else
														{
														$htmlresource .="<a href='#'  >";
														//$htmlresource .="<a href='#' class=$class onclick=load_imscp(".$modid->id.",".$cm->id.",'".rawurlencode($fullurl)."'); id='sel_frm_".$key."'>";
														}
														$htmlresource .='<div>';
														$htmlresource .='<h4>'.get_string('studentactivity').' :'.$vcnt.'</h4>';
														$htmlresource .='<p>'.wordwrap(substr($modid->name,0,30),20,"</br>");
														if(strlen($modid->name) >30)
														{
														$htmlresource .= '...';	
														}
														$htmlresource .= '</p>';
														$htmlresource .='</div></a>';
														$htmlresource .="<input type='hidden' name='id' value='$cm->id'/>";
														$htmlresource .='</form>';
														$htmlresource .= "</li>";
														$htmlassessment[] = $htmlresource;
														$vcnt++;
												
												
											}
										
										
										else if(strtolower($modid->modname)=='quiz')
										{
											$cm = $DB->get_record("course_modules", array("id"=>$modid->id));
											$sec_act=get_group_les_activity($group->id,$section->id,$courseid);
											$act_com =get_activity_completion_mod($cm->id);
											$get_act_record_count=get_group_act_no($group->id,$courseid); 
											$act_com== 1? $class='greenact' : $class='greenactno' ;
											if($sec_act->availablefrom < $startday && $get_act_record_count >1){
													$act_com== 1? $class='greenact' : $class='greenactno' ;
													
												}elseif($sec_act->availablefrom >= $startday && $sec_act->availablefrom < $endday && $get_act_record_count >1){
													$class='blueact';
												}else{
													$class='yellowact';
												}
											$htmlother ='<li class='.$class.'  title="'.htmlentities($modid->name).'">';
											
											$htmlother .="<form name='frm".$key."' id='frm".$key."' action='".$CFG->wwwroot."/mod/quiz/startattempt.php' method='post'>";
											if($sec_act->availablefrom < $startday && $get_act_record_count >1){
											//$htmlother .="<a href='#' class=$class onclick='frm".$key.".submit();' id='sel_frm_".$key."'>";
											$htmlother .="<a href='#'  onclick='act_sel(".$key."); load_quiz(".$modid->id.",".$cm->id.");' id='sel_frm_".$key."' >";
											}elseif($sec_act->availablefrom >= $startday && $sec_act->availablefrom < $endday && $get_act_record_count >1){
											//$htmlother .="<a href='#' class='blueact' onclick='frm".$key.".submit();' id='sel_frm_".$key."'>";
											$htmlother .="<a href='#' onclick='act_sel(".$key."); load_quiz(".$modid->id.",".$cm->id.");' id='sel_frm_".$key."' >";
											}else
											{
											$htmlother .="<a href='#' alt='".$modid->name."'>";	
											}
											$htmlother .='<div>';
											$htmlother .='<h4>'.get_string('studentactivity').' :'.$vcnt.'</h4>';
											$htmlother .='<p>'.wordwrap(substr($modid->name,0,30),20,"</br>").'</p>';
											if(strlen($modid->name) >30)
											{
											$htmlother .= '...';	
											}
											$htmlother .= '</p>';
											$htmlother .='</div></a>';
											//$htmlother .="<a href='#' onclick='frm$key.submit();'>$modid->name</a>";
											$htmlother .="<input type='hidden' name='cmid' value='$cm->id'/>";
											$htmlother .= "<input type='hidden' name='sesskey' value='".sesskey()."'/> </form>";
											$htmlother .="</li>";
											$htmlassessment[] = $htmlother;
											$vcnt++;
										}
										else if(strtolower($modid->modname)=='assignment')
										{
											
											$cm = $DB->get_record("course_modules", array("id"=>$modid->id));
											$sec_act=get_group_les_activity($group->id,$section->id,$courseid);
											$act_com =get_activity_completion_mod($cm->id);
											$get_act_record_count=get_group_act_no($group->id,$courseid); 
											$act_com== 1? $class='greenact' : $class='greenactno' ;
											if($sec_act->availablefrom < $startday && $get_act_record_count >1){
												$act_com== 1? $class='greenact' : $class='greenactno' ;
												
											}elseif($sec_act->availablefrom >= $startday && $sec_act->availablefrom < $endday && $get_act_record_count >1){
												$class='blueact';
											}else{
												$class='yellowact';
											}
											$htmlassignment ='<li class='.$class.'  title="'.htmlentities($modid->name).'">';
											//$htmlassignment .="<form name='frm".$key."' id='frm".$key."' action='".$CFG->wwwroot."/mod/assignment/view.php' method='get'>";
											if($sec_act->availablefrom < $startday && $get_act_record_count >1){
											$htmlassignment .="<a href='#' onclick='act_sel(".$key."); load_assessment(".$modid->id.");' id='sel_frm_".$key."'  >";
											}elseif($sec_act->availablefrom >= $startday && $sec_act->availablefrom < $endday && $get_act_record_count >1)
											{
											$htmlassignment .="<a href='#' onclick='act_sel(".$key."); load_assessment(".$modid->id.");' id='sel_frm_".$key."' >";	
											}else
											{
											$htmlassignment .="<a href='#' >";	
											}
											$htmlassignment .='<div>';
											$htmlassignment .='<h4>'.get_string('studentactivity').' :'.$vcnt.'</h4>';
											$htmlassignment .='<p>'.wordwrap(substr($modid->name,0,30),20,"</br>");
											if(strlen($modid->name) >30)
											{
											$htmlassignment .= '...';	
											}
											$htmlassignment .= '</p>';
											$htmlassignment .='</div></a>';
											//$htmlassignment .="<a href='#' onclick='frm$key.submit();'>$modid->name</a>";
											$htmlassignment .="<input type='hidden' name='id' value='$cm->id'/>";
											$htmlassignment .= "<input type='hidden' name='sesskey' value='".sesskey()."'/>";
											//$htmlassignment .= " </form>";
											$htmlassignment .='</li>';
											$htmlassessment[] = $htmlassignment;
											$vcnt++;
										}
										else if(strtolower($modid->modname)=='lesson')
										{
											$cm = $DB->get_record("course_modules", array("id"=>$modid->id));
											$sec_act=get_group_les_activity($group->id,$section->id,$courseid);
											$act_com = get_activity_completion_mod($cm->id);
											$get_act_record_count=get_group_act_no($group->id,$courseid); 
											$act_com== 1? $class='greenact' : $class='greenactno' ;
											if($sec_act->availablefrom < $startday && $get_act_record_count >1){
												$act_com== 1? $class='greenact' : $class='greenactno' ;
												
											}elseif($sec_act->availablefrom >= $startday && $sec_act->availablefrom < $endday && $get_act_record_count >1){
												$class='blueact';
											}else{
												$class='yellowact';
											}
											$htmllesson ='<li class='.$class.'  title="'.htmlentities($modid->name).'">';
											
											$htmllesson .="<form name='frm".$key."' id='frm".$key."' action='".$CFG->wwwroot."/mod/lesson/view.php' method='get'>";
											if($sec_act->availablefrom < $startday && $get_act_record_count >1){
											//$htmllesson .="<a href='#' class=$class onclick='frm".$key.".submit();' id='sel_frm_".$key."'>";
											$htmllesson .="<a href='#'  onclick='act_sel(".$key."); load_les(".$modid->id.",".$cm->id.");' id='sel_frm_".$key."' >";
											}elseif($sec_act->availablefrom >= $startday && $sec_act->availablefrom < $endday && $get_act_record_count >1){
											//$htmllesson .="<a href='#' class='blueact' onclick='frm".$key.".submit();' id='sel_frm_".$key."'>";
											$htmllesson .="<a href='#'  onclick='act_sel(".$key."); load_les(".$modid->id.",".$cm->id.");' id='sel_frm_".$key."' >";	
											}else
											{
											$htmllesson .="<a href='#'  >";	
											}
											$htmllesson .='<div>';
											$htmllesson .='<h4>'.get_string('studentactivity').' :'.$vcnt.'</h4>';
											$htmllesson .='<p>'.wordwrap(substr($modid->name,0,30),20,"</br>");
											if(strlen($modid->name) >30)
											{
											$htmllesson .= '...';	
											}
											$htmllesson .= '</p>';
											
											$htmllesson .= '</div></a>';
											//$htmllesson .="<a href='#' onclick='frm$key.submit();'>$modid->name</a>";
											$htmllesson .="<input type='hidden' name='id' value='$cm->id'/>";
											$htmllesson .= "<input type='hidden' name='sesskey' value='".sesskey()."'/> </form>";
											$htmllesson .="</li>";
											$htmlassessment[] = $htmllesson;
											$vcnt++;
										}
										else if(strtolower($modid->modname)=='page')
										{
											$cm = $DB->get_record("course_modules", array("id"=>$modid->id));
											$sec_act=get_group_les_activity($group->id,$section->id,$courseid);
											$get_act_record_count=get_group_act_no($group->id,$courseid); 
											$act_com = get_activity_completion_mod($cm->id);
											//$act_com== 1? $class='greenact' : $class='greenactno' ;
											if($sec_act->availablefrom < $startday && $get_act_record_count >1){
												$act_com== 1? $class='greenact' : $class='greenactno' ;
											}elseif($sec_act->availablefrom >= $startday && $sec_act->availablefrom < $endday && $get_act_record_count >1){
												$class='blueact';
											}else{
												$class='yellowact';
											}
											$htmllesson ='<li class='.$class.'  title="'.htmlentities($modid->name).'">';
											
											$htmllesson .="<form name='frm".$key."' id='frm".$key."' action='".$CFG->wwwroot."/mod/page/view.php' method='get'>";
											if($sec_act->availablefrom < $startday && $get_act_record_count >1){
											$htmllesson .="<a href='#'  onclick='act_sel(".$key."); frm".$key.".submit();' id='sel_frm_".$key."' >";
											//$htmllesson .="<a href='#' class=$class onclick='load_les(".$modid->id.",".$cm->id.");' id='sel_frm_".$key."'>";
											}elseif($sec_act->availablefrom >= $startday && $sec_act->availablefrom < $endday && $get_act_record_count >1){
											$htmllesson .="<a href='#'  onclick='act_sel(".$key."); frm".$key.".submit();' id='sel_frm_".$key."' >";
											//$htmllesson .="<a href='#' class='blueact' onclick='load_les(".$modid->id.",".$cm->id.");' id='sel_frm_".$key."'>";	
											}else
											{
											$htmllesson .="<a href='#'  >";	
											}
											$htmllesson .='<div>';
											$htmllesson .='<h4>'.get_string('studentactivity').' :'.$vcnt.'</h4>';
											$htmllesson .='<p>'.wordwrap(substr($modid->name,0,30),20,"</br>");
											if(strlen($modid->name) >30)
											{
											$htmllesson .= '...';	
											}
											$htmllesson .= '</p>';
											$htmllesson .= '</div></a>';
											//$htmllesson .="<a href='#' onclick='frm$key.submit();'>$modid->name</a>";
											$htmllesson .="<input type='hidden' name='id' value='$cm->id'/>";
											$htmllesson .= "<input type='hidden' name='sesskey' value='".sesskey()."'/> </form>";
											$htmllesson .="</li>";
											$htmlassessment[] = $htmllesson;
											$vcnt++;
										}
										
										
									}
									
								}
							}
						//	print_r($htmlassessment);
							//$htmlvideo .="</ul>";
							
							//$jsscript .=" var tree = new YAHOO.widget.TreeView('assessmentdiv'); 
								//	var rootNode = tree.getRoot(); ";
							
							$html_assessment ="";
						/*	while(list($key,$val) = each($htmlassessment))
							{
								$jsscript .=" tmpNode = new YAHOO.widget.HTMLNode('".addslashes($val)."', tree.getRoot(), true,true);"; 
								//$html_assessment .=" <dt>".get_string($key,"my")."</dt>";	
								//reset($val);
								/*while(list($keyli,$valli) = each($val))
								{
									$jsscript .=" tmpNode = new YAHOO.widget.TextNode('".addslashes($valli)."', tree.getRoot(), true,true);"; 
									//$html_assessment .= "<dd>".$valli."</dd>";
								//	$jsscript .=" tmpNode = new YAHOO.widget.HTMLNode('".addslashes($valli)."', tree.getRoot(), false, true); ";
									
								}*/
						//	}
							//$jsscript .=" tree.draw(); ";
							
							if($vcnt == 0)
							{
								//$jsscript .=" document.getElementById('videobox').innerHTML='Please select Video section to see video'; "; 
								//$jsscript .=" document.getElementById('videoid2').style.display='none'; ";
								//$jsscript .=" document.getElementById('videoid1').style.display='block'; ";
								
							}
							else
							{
								//$jsscript .=" document.getElementById('videoid2').style.display='block'; ";
							//	$jsscript .=" document.getElementById('videobox').innerHTML='Please select Video section to see video'; "; 
							//	$jsscript .=" document.getElementById('videoid1').style.display='block'; ";
								//$jsscript .=" document.getElementById('videoid1').style.height='230px'; ";
								if($firstvideoid != '')
								{
									//$jsscript.=" getvideoload(".$firstvideoid."); ";								
								}
							}
							/*while(list($key,$val) = each($htmlassessment))
							{
								$jsscript.="document.getElementById('sectionlessonid').innerHTML='".addslashes($val)."';";
							}*/
							if($default ==1){
								$date_show = date('d-M-Y');
							}else
							{
								$date_show = date('d-M-Y', $sec_act->availablefrom);
							}
							if($sectionid > 0 )
								{	
										$jsscript.= " removeSectClass(".$sectionid."); "; 
										//$jsscript.=" getsectionload(".$sectionid.",".$course->id."); ";
										//$jsscript.=" if(document.getElementById('sel_".$sectionid."')) { document.getElementById('sel_".$sectionid."').className += ' sel';}";
								}
							
							
							$jsscript.="document.getElementById('sectionlessonid').innerHTML=unescape('<ul>".rawurlencode(implode("",$htmlassessment))."</ul>');";
							$jsscript.="document.getElementById('disp_date').innerHTML='".addslashes($date_show)."';";
							//reset($sections);
							/*foreach ($sections as $key_section => $section) 
							{
								$jsscript.=" if(document.getElementById('section_".$section->section."')) { document.getElementById('section_".$section->section."').className='les_div';}";
							}
							$jsscript.=" if(document.getElementById('section_".$sectionid."')) { document.getElementById('section_".$sectionid."').className='lesson-listsel';}";
							*/
							
							//$jsscript.=" if(document.getElementById('sel_frm_".$key."')) { document.getElementById('sel_frm_".$key."').className=' act_sele';}";
							
							//$jsscript.=" if(document.getElementById('course_".$course->id."')) { document.getElementById('course_".$course->id."').className='yui-carousel-item-selected_';}";
							/*if($rcnt == 0 && $vcnt > 0 )
							{
								$jsscript.="document.getElementById('assessmentdiv').innerHTML='Videos are listed below.';";
							}*/
							//$arr_repsonse["htmlassessment"]=$html_assessment;
							
							//$arr_repsonse["htmlvideo"]=$htmlvideo;
							//alert($jsscript);
							
							
							$arr_repsonse = array();
							$arr_repsonse["jsscript"]=$jsscript;
							echo json_encode($arr_repsonse);   
							break;
	case 'videoshow':
					$resource = $DB->get_record("resource", array("id"=>$resouceid));
					$cm = get_coursemodule_from_instance('resource', $resource->id, $resource->course, false, MUST_EXIST);
					$fs = get_file_storage();
					$context = get_context_instance(CONTEXT_MODULE, $cm->id);
					$files = $fs->get_area_files($context->id, 'mod_resource', 'content', 0, 'sortorder');
					$file = array_pop($files);
					
					$path = '/'.$context->id.'/mod_resource/content/'.$resource->revision.$file->get_filepath().$file->get_filename();
   					$fullurl = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, false)."?d=415x260";
   					
   					$clicktoopen = resource_get_clicktoopen($file, $resource->revision);
    				$mimetype = $file->get_mimetype();
    				$title    = $resource->name;
   					
				if ($mimetype == 'audio/mp3') {
			        // MP3 audio file
			        $code = resourcelib_embed_mp3($fullurl, $title, $clicktoopen);
			
			    } else if ($mimetype == 'video/x-flv') {
			        // Flash video file
			        $code = resourcelib_embed_flashvideo($fullurl, $title, $clicktoopen);
			
			    } else if ($mimetype == 'application/x-shockwave-flash') {
			        // Flash file
			        $code = resourcelib_embed_flash($fullurl, $title, $clicktoopen);
			
			    } else if (substr($mimetype, 0, 10) == 'video/x-ms') {
			        // Windows Media Player file
			        $code = resourcelib_embed_mediaplayer($fullurl, $title, $clicktoopen);
			
			    } else if ($mimetype == 'video/quicktime') {
			        // Quicktime file
			        $code = resourcelib_embed_quicktime($fullurl, $title, $clicktoopen);
			
			    } else if ($mimetype == 'video/mpeg') {
			        // Mpeg file
			        $code = resourcelib_embed_mpeg($fullurl, $title, $clicktoopen);
			
			    } else if ($mimetype == 'audio/x-pn-realaudio') {
			        // RealMedia file
			        $code = resourcelib_embed_real($fullurl, $title, $clicktoopen);
			
			    } else {
			        // anything else - just try object tag enlarged as much as possible
			        $code = resourcelib_embed_general($fullurl, $title, $clicktoopen, $mimetype);
			    }
			    
			  
			    //echo $code;
			   
			    
			     $chars = preg_split('/(<[^>]*[^\/]>)/i', $code, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
			    $flagjavascript = false;
			    $html = '';
			    $jsscript = ' flowplayer=undefined; M.util.video_players = [];';
			 
			    while(list($key,$val)=each($chars))
			    {
			    	if($val=='<script type="text/javascript">')
			    	{
			    		 $flagjavascript = true;
			    		 continue;
			    	}
			    	else
			    	if($val=='</script>')
			    	{
			    		 $flagjavascript = false;
			    		 continue;
			    	}
			    	elseif($flagjavascript == false)
			    	{
			    		$html .=$val;
			    	}
			    	else 
			    	{
			    		$jsscript.=$val;
		    	    }

			    } 
			    
			 $jsscript.='   M.util.load_flowplayer(); ';			
			 //$jsscript.=" if(document.getElementById('video_".$resouceid."')) { document.getElementById('video_".$resouceid."').className='yui-carousel-item-selected-video';}";
			 if(empty($title)){
			 	$jsscript	.="document.getElementById('videoheading').innerHTML='<small>&nbsp</small>'"; 
			 }	
			 else
			 {
			 $jsscript	.=" if(document.getElementById('laod_assigment')) {document.getElementById('videoheading').innerHTML='<small>".addslashes_js($title)."</small>'; }";
			 }
			   $arr_repsonse["code"]=trim($html) ;
			   $arr_repsonse["jsscript"]=trim(str_replace(array("//<![CDATA[","]]","//>"),Array("","",""),$jsscript));
			   $arr_repsonse["jsscript"]=trim($jsscript);
				echo json_encode($arr_repsonse);
				break;
		
}
?>