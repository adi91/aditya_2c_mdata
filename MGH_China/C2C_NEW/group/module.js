/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
M.core_group = {
    hoveroverlay : null
};

M.core_group.init_hover_events = function(Y, events) {
    // Prepare the overlay if it hasn't already been created
    this.hoveroverlay = this.hoveroverlay || (function(){
        // New Y.Overlay
        var overlay = new Y.Overlay({
            bodyContent : 'Loading',
            visible : false,
            zIndex : 2
        });
        // Render it against the page
        overlay.render(Y.one('#page'));
        return overlay;
    })();

    // Iterate over the events and attach an event to display the description on
    // hover
    for (var id in events) {
        var node = Y.one('#'+id);
        if (node) {
            node = node.ancestor();
            node.on('mouseenter', function(e, content){
                M.core_group.hoveroverlay.set('xy', [this.getX()+(this.get('offsetWidth')/2),this.getY()+this.get('offsetHeight')-5]);
                M.core_group.hoveroverlay.set("bodyContent", content);
                M.core_group.hoveroverlay.show();
                M.core_group.hoveroverlay.get('boundingBox').setStyle('visibility', 'visible');
            }, node, events[id]);
            node.on('mouseleave', function(e){
                M.core_group.hoveroverlay.hide();
                M.core_group.hoveroverlay.get('boundingBox').setStyle('visibility', 'hidden');
            }, node);
        }
    }
};

M.core_group.init_index = function(Y, wwwroot, courseid) 
{
	M.core_group.groupsCombo = new UpdatableGroupsCombo(wwwroot, courseid);
	var node = document.getElementById("groups");
	if(node.selectedIndex==-1 && node.options.length > 0)
	{
		node.options[0].selected = true;
	}
    M.core_group.membersCombo = new UpdatableMembersCombo(wwwroot, courseid);
    M.core_group.membersCombo.refreshMembers();
};
/******* #234  Scheduling Class times - times should be intelligent. ****** modified by Pankaj *** 01/09/2012 ***/
function getendtime(starttime,endtime)
{
		endtime = document.getElementById(endtime);
		starttime = document.getElementById(starttime);
		
		 
		if(endtime && starttime)
		{
			var lasttime = endtime.value;
			try
			{
				endtime.options.length = 0;
			}
			catch(e)
			{
			   for(i=0;i<endtime.options.length;i++)
			   {
					endtime.remove(oOption);
			   }
			}
			
			for(i=(starttime.selectedIndex+1);i<starttime.options.length;i++)
			   {
					var option=document.createElement("option");
					option.text=starttime.options[i].text;
					option.value=starttime.options[i].value;
					if(lasttime == option.value)
					{
						option.selected=true;
					}
					
					try
					  {
					  // for IE earlier than version 8
						endtime.add(option,endtime.options[null]);
					  }
					catch (e)
					  {
						endtime.add(option,null);
					  }
			   }
			
		}
		
		 if(endtime.parentNode.parentNode.parentNode)
		 {
		  if(endtime.parentNode.parentNode.parentNode.style.display=='none')
		  {
			  endtime.disabled=true;
		  }
		 }

}

function selectLesson(val,val2,val3)
{
	
	if(document.getElementById(val2).checked == true)
	{
		document.getElementById('lesson'+val).style.display='';
		/*document.getElementById(val2+'_starttime_1').disabled=false;
		for(i=2;i<10;i++)
		{
			if(i<=eval(val3))
			{
				document.getElementById(val2+'_starttime_'+i).disabled='';	
			}
			else
			{
				
				document.getElementById(val2+'_starttime_'+i).disabled='disabled';
			}
		}*/
		
	}
	else
	{
		document.getElementById('lesson'+val).style.display='none';
		/*document.getElementById(val2+'_starttime_1').disabled=true;
		for(i=2;i<10;i++)
		{
			document.getElementById(val2+'_starttime_'+i).disabled='disabled';	
		}*/
		
	}
}


function show_lesson(val1,val)
{
	var val2= val+1;
	document.getElementById(val1+'button_'+val).style.display='none';
	document.getElementById(val1+'_starttime_'+val2).disabled=false;
	document.getElementById(val1+'lesson_'+val2).style.display='';
	document.getElementById(val1+'button_'+val2).style.display='';
	
}

function remove_lesson(val1,val)
{
	var val2= val - 1 ;
	document.getElementById(val1+'button_'+val2).style.display='';
	 document.getElementById(val1+'_starttime_'+val).disabled='disabled';
	document.getElementById(val1+'lesson_'+val).style.display='none';
	document.getElementById(val1+'button_'+val).style.display='none';
}


M.core_group.init_fill_call = function(Y) 
{
	getendtime('id_starttime','id_endtime');
	getendtime('id_starttime2','id_endtime2');
}
/******* #234  Scheduling Class times - times should be intelligent. ****** modified by Pankaj *** 01/09/2012 ***/ 