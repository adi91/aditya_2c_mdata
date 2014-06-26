<?php
    require_once('../config.php');
    require_once($CFG->dirroot .'/course/lib.php');
    require_once($CFG->libdir .'/filelib.php');
       require_once($CFG->libdir.'/completionlib.php');

   //    $reset_user_allowed_editing = true;
       
    redirect_if_major_upgrade_required();
	$id         = optional_param('id', 0, PARAM_INT);
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
    $PAGE->set_pagelayout('frontpage');
  	$editing = $PAGE->user_is_editing();
    $PAGE->set_title($SITE->fullname);
    $PAGE->set_heading($SITE->fullname);
    echo $OUTPUT->header();
    
    if(is_teacher() || is_non_editing_teacher()){
   $searchcourse="";
  	$courses = apt_enrol_get_users_courses($USER->id,false,'*', 'visible DESC,sortorder ASC',$searchcourse);
	}else if(is_centeradmin()){
		
		DEFINE('CENTERADMIN_ROLEID',10);
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
    
    $firstcourseid='';
    if($id!=0)
    {
    	$firstcourseid = $id;
    }
	if(count($courses) > 0)
	{	
		foreach ($courses as $c) 
		{
			if(trim($firstcourseid) =='')
			{			
				$firstcourseid = $c->id;
			}
		}
	}
    //print_r($courses);
    ?>

    <script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/yahoo/yahoo-min.js"></script> 
    <script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/event/event-min.js"></script> 
    <script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/connection/connection-min.js"></script> 
  
    <script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/dom/dom.js"></script> 
    <script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/dragdrop/dragdrop.js"></script> 

  
	<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/utilities/utilities.js"></script> 
	<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/selector/selector-min.js"></script> 
	<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/event-delegate/event-delegate-min.js"></script> 
	<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/event-mouseenter/event-mouseenter-min.js"></script> 
	<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/carousel/carousel-min.js"></script> 
	<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/connection/connection_core-min.js"></script> 
	<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/container/container-min.js"></script> 
	<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/element-delegate/element-delegate-min.js"></script> 
	<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/progressbar/progressbar-min.js"></script>
	<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/json/json-min.js"></script>
    <link href="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/carousel/assets/skins/sam/carousel.css" rel="stylesheet" type="text/css" />
    <link type="text/css" rel="stylesheet" href="<?php echo $CFG->wwwroot;?>/lib/yui/<?php echo $CFG->yui2version;?>/build/container/assets/container.css">
    <style>
    .yui-carousel-nav
    {
  	  display:none;
    }
	.yui-carousel .yui-carousel-item-selected {
	   	border:2px solid red !important;
	}
    </style>
    <input type="hidden" id="sel_course" value=""></input>
    		<div>
    		<?php 
            if(count($courses) > 0)
            {
            ?>
    		<div id="carousel3">
    			<div class="bef3"><span id="prev"><img src="../pix/icon/arrow-left.png" alt="" /></span></div>
            		<div class="caro">
		               <div id='mycustomscroll2'>
		                <ul class="caromid">
	                  <?php  
					  foreach($courses as $c1)
					  {
					  		if(is_centeradmin() || is_siteadmin()){
								foreach($c1 as $c1){
							?>
								<li id='course_<?php echo $c1->id; ?>' class='green' onclick='getlessonload(<?php echo $c1->id; ?>);' title="<?php echo $c1->fullname;?>" alt="<?php echo $c1->fullname;?>"><a href="#"   > <?php echo wordwrap(substr($c1->fullname,0,32),16,"<br>",true);?></a></li>
							<?php 
							   }
							}else{
							?>
								<li id='course_<?php echo $c1->id; ?>' class='green' onclick='getlessonload(<?php echo $c1->id; ?>);' title="<?php echo $c1->fullname;?>" alt="<?php echo $c1->fullname;?>"><a href="#"   > <?php echo wordwrap(substr($c1->fullname,0,32),16,"<br>",true);?></a></li>
							<?php
							}
						}
						?>
	                  </ul>
	                  
	                </div>
               </div>
    			<div class="aft3"><span id="next"><img src="../pix/icon/arrow-right.png" alt="" /></span></div>
    		</div>
              </div>
              <div class="index-rightboard">
	              <div id="container21">
	              </div>
              </div>
			<?php }
	           else
				  {
					echo get_string('nocourse');                  	
	              }
	         ?>
              <script>
              
                var carouselsection    = new YAHOO.widget.Carousel("mycustomscroll2", {
		            	numVisible: [3,1] ,
		 	            animation: { speed: 0.5 },
		 	           	navigation:{prev:"prev",next:"next"},
		 	    		carouselEl: "UL" , 	
		 				isVertical: false
		 				
					}); 
              	carouselsection.on("navigationStateChange",function(){
				 if(carouselsection._nextEnabled)
					{
						document.getElementById("next").style.display='block';
						document.getElementById("next").disabled = false;
					}
					else
					{
						document.getElementById("next").style.display='none';
						document.getElementById("next").disabled = true;
					}
					if(carouselsection._prevEnabled)
					{
						document.getElementById("prev").style.display='block';
						document.getElementById("prev").disabled = false;
					}
					else
					{
						document.getElementById("prev").style.display='none';
						document.getElementById("prev").disabled = true;
					}
				});
              	/*carouselsection.on("getPageForItem", function() {
                    return Math.ceil(
                        (item+1) / parseInt(this.get("numVisible"),4)
                    );
                });*/
                
            	/*var ccar_item=carouselsection.getPageForItem(4);
                if(ccar_item >1)
                {
                	carouselsection.currentPageChange(0,1); 
                }
              	 var arr_c_item = carouselsection.getItems();*/
            //carouselsection.set("selectedItem", -1);
             carouselsection.render();// get ready for rendering the widget
             carouselsection.show();
		
          function getlessonload(val)
              {
        	  	var coursedetails = YAHOO.util.Dom.get('container21');
        		coursedetails.innerHTML ='<div style="margin-left:210px;">&nbsp;<?php echo get_string("content_loading");?><br> <img src="../pix/ajax-loader.gif"></div>';
               var div = document.getElementById('container21');
                  document.getElementById('course_'+val).className+=' yui-carousel-item-selected';
                   var handleSuccess = function(o){

                  //	YAHOO.log("The success handler was called.  tId: " + o.tId + ".", "info", "example");
                  	
                  	if(o.responseText !== undefined)
                    {
                  		/* if(o.responseText =="")
    					{
    							return;
    					}
    					var messages = [];
    		            try {
    		                messages = YAHOO.lang.JSON.parse(o.responseText);
    		            }
    		            catch (x) {
    		                alert("JSON Parse failed!");
    		                return;
    		            }
    		            eval(messages.jsscript);*/
    		           
                  		div.innerHTML = o.responseText;
                  	}
                  }

                  var handleFailure = function(o){
                  		//YAHOO.log("The failure handler was called.  tId: " + o.tId + ".", "info", "example");
                  	if(o.responseText !== undefined){
                  		
                  	}
                  }

                  var callback =
                  {
                    success:handleSuccess,
                    failure:handleFailure,
                    argument: { foo:"foo", bar:"bar" }
                  };

                 var sUrl = '<?php echo $CFG->wwwroot?>/course/courseviewtech.php?id='+val;
                  var request = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);
                 YAHOO.log("Initiating request; tId: " + request.tId + ".", "info", "example");

				 
					/*for(i=0;i<=document.getElementsByTagName('li').length;i++){
					   if(document.getElementsByTagName('li')[i]){
						if(document.getElementsByTagName('li')[i].id && document.getElementsByTagName('li')[i].id == 'course_'+val){
							document.getElementById('course_'+val).style.backgroundColor="#ff0000"; 
							document.getElementById('course_'+val).style.padding="1px";
						}else{
							document.getElementsByTagName('li')[i].style.padding = "0px";
						}
					   }
					 }*/

              }
            
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
  			
              function get_resource(course,section,modnames)
              {
                  //alert(course);
              }
         /**** added by pankaj ***** 14/08/2012 *********/
              function activity_move_down(elem)
				{
					var sUrl = '<?php echo $CFG->wwwroot?>/course/move.php?id='+elem.getAttribute('id')+'&action=down';
					var handleSuccess = function(o){
														if(o.responseText !== undefined)
														{
															eval(o.responseText);               
														}
													}
					var handleFailure = function(o){
														if(o.responseText !== undefined)
														{
															eval(o.responseText);    
														}
													}	
					var callback =
					{
						success:handleSuccess,
						failure:handleFailure,
						argument: { foo:"foo", bar:"bar" }
					};
					var request = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);
					return false;
				}
				
				function activity_move_up(elem)
				{
					var sUrl = '<?php echo $CFG->wwwroot?>/course/move.php?id='+elem.getAttribute('id')+'&action=up';
					var handleSuccess = function(o){
														if(o.responseText !== undefined)
														{
															eval(o.responseText);               
														}
													}
					var handleFailure = function(o){
														if(o.responseText !== undefined)
														{
															eval(o.responseText);    
														}
													}	
					var callback =
					{
						success:handleSuccess,
						failure:handleFailure,
						argument: { foo:"foo", bar:"bar" }
					};
					var request = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);
					return false;
				}
               /**** added by pankaj ***** 14/08/2012 *********/

<?php 
if($firstcourseid != '')
echo "getlessonload(".$firstcourseid.");";
?>
</script>
       
  
    <?php  echo $OUTPUT->footer();
    ?>