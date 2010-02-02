<?php
	require_once(dirname(__FILE__).'/../../../wp-load.php');
	//require_once(dirname(__FILE__).'/../../../wp-admin/admin.php');
	header("Content-Type: text/javascript");
	
	include_once(dirname(__FILE__).'/utils_js.php');
?>

/**
* Program scheduler editor
* Author : http://wfiu.org
*
* Credits: Pablo Vanwoerkom
*/

jQuery(function($){ 
    ////////////////////////////////////////////////////////////////////////////
    //
    // $.fn.calendar
    // calendar definition
    //
    ////////////////////////////////////////////////////////////////////////////
    $.fn.calendar = function (options) {
    	

        // set context vars
        //----------------------------------------------------------------------
        var obj = $(this);

        // build main options before element iteration
        //----------------------------------------------------------------------
       	var opts = $.extend({}, $.fn.calendar.defaults, options);
        
        
        // firebug console output
        //----------------------------------------------------------------------
        function debug(text) {
            if (window.console && window.console.log) {
                window.console.log(text);
            }
        };

    	
       ////////////////////////////////////////////////////////////////////////
        //
        // -> init
        // apply calendar to all calling instances
        //
        ////////////////////////////////////////////////////////////////////////
        return this.each(function(objIndex){
			function matrixLoc2Date(row, col){
				var month_day =  parse_month_day(col);
				var month = month_day[0];
				var day = month_day[1];
				var hour_start = parseInt(row / opts.hour_fraction);
				var min_start = parseInt((row % opts.hour_fraction)/opts.hour_fraction * 60.0);				
				return new Date(month + " " + day + ", " + jQuery(".calendar_year:first").text() + ' ' + hour_start + ':' + min_start + ':00');
			}
			
			function getStartEndDates(id){
				var year = jQuery(".calendar_year:first").text();
				var month_day =  parse_month_day(events.scol[id]);
				var month = month_day[0];
				var day = month_day[1];
				var hour_start = parseInt(events.srow[id] / opts.hour_fraction);
				var min_start = parseInt((events.srow[id] % opts.hour_fraction)/opts.hour_fraction * 60.0);
				var myDates = new Array();
				myDates[0] = new Date(month + " " + day + ", " + year + ' ' + hour_start + ':' + min_start + ':00');
				
				month_day =  parse_month_day(events.ecol[id]);
				month = month_day[0];
				day = month_day[1];
				hour_start = parseInt(events.erow[id] / opts.hour_fraction);
				min_start = parseInt((events.erow[id] % opts.hour_fraction)/opts.hour_fraction * 60.0);
				
				myDates[1] = new Date(month + " " + day + ", " + year + ' ' + hour_start + ':' + min_start + ':00');
				myDates[1].setTime(myDates[1].getTime() + parseInt((1.0/opts.hour_fraction)*3600000));
				return myDates;
			}

			function setBackgroundColor(id, color){
				$(events.container[id]).css('background-color', color);
			}
			
			function doOtherDaysFields(selectElement){
				var days = Array();
				days.push('M');
				days.push('T');
				days.push('W');
				days.push('Th');
				days.push('F');
				days.push('Sa');
				days.push('Su');
				var vals = $(selectElement).val().toString().split('');
				var checkboxesDiv = $("<div />");
				
				var table = "<table style='float: left'><tr>";
				for(i in days){
					j = parseInt(i)+1;
					checked = '';
					if(jQuery.inArray(j, vals) > -1){
						checked = 'checked="checked"';	
					}
					table += '<td style="text-align:center">'+ days[i] + '<br /><input type="checkbox" name="event_repeats" '+checked+' value="'+j+'"></td>';
				}
						
				table += "</tr></table>";
				$(checkboxesDiv).append(table);
				$(selectElement).before(checkboxesDiv);
			}

			function getCheckboxRepeat(id){
				var days = Array();
				days.push('M');
				days.push('T');
				days.push('W');
				days.push('Th');
				days.push('F');
				days.push('Sa');
				days.push('Su');
				var vals = events.repeats[id].toString().split('');
				for(i in vals)vals[i] = parseInt(vals[i]);
				//alert("vals: " + vals);
				var checkboxesDiv = $("<div />");
				
				var table = "<table style='float: left'><tr>";
				for(i in days){
					j = parseInt(i)+1;
					checked = '';
					if((jQuery.inArray(j, vals) > -1)){
						checked = 'checked="checked"';	
					}
					table += '<td style="text-align:center">'+ days[i] + '<br /><input type="checkbox" disabled="disabled" name="event_repeats" '+checked+' value="'+j+'"></td>';
				}	
				table += "</tr></table>";
				$(checkboxesDiv).append(table);
				
				var span = $(" <span style='padding-left: 5px; color: blue; cursor: pointer;text-decoration: underline;'>select</span>");
				$(span).bind("click", function(e){
					var select = getSelectRepeat();
					$(select).bind("change", function(e){
						if(this.value == 'other'){
							doOtherDaysFields(this);
						}
					});
					$(this).parent().before(select);
					$(this).parent().remove();

				});
				return [$(table), span];
			}


			function getSelectRepeat(){
				return $('<select name="event_repeats">'+
								'<option value="">Does not repeat</option>'+
								'<option value="1234567">Daily</option>'+
								'<option value="12345">Every weekday (Mon-Fri)</option>'+
								'<option value="135">Every Mon., Wed., Fri.</option>'+
								'<option value="24">Every Tues., and Thurs.</option>'+
								'<option value="weekly">Weekly</option>'+
								'<option value="other">Other days</option></select>');	
			}
			
			function getBoundedDate(dateObj, boundary){
				
				if(boundary == 'start'){
					if(dateObj < start_datetime ){
						dateObj.setDate(start_datetime.getDate());
						dateObj.setMonth(start_datetime.getMonth());
						dateObj.setYear(start_datetime.getFullYear());
					}
				}else{
					var end_date = new Date();
					end_date.setTime(start_datetime.valueOf());
					end_date.setDate(end_date.getDate()+6);
					
					if(dateObj > end_date ){
						dateObj.setDate(end_date.getDate());
						dateObj.setMonth(end_date.getMonth());
						dateObj.setYear(end_date.getFullYear());
					}
				}
				return dateObj;	
			}
			
			function initializeDaysHash(){
				var iterator = new Date();
				iterator.setTime(start_datetime.valueOf());
				
				daysHash = {};
				for(var i =0; i<7; ++i){
					 daysHash[iterator.getMonth() + '_' + iterator.getDate()] = i;
					 iterator.setDate(iterator.getDate()+1);
				}
				//dumpProps(daysHash);
			}
			
			//Date.prototype.bindDate=getBoundedDate;
			function updateEventFromXML(id, xmlIndex){
				var start, end, srow, scol, erow, ecol;

				start = new Date(programsXML.getElementsByTagName("start_date")[xmlIndex].childNodes[0].nodeValue);
				end = new Date(programsXML.getElementsByTagName("end_date")[xmlIndex].childNodes[0].nodeValue);
				//debug('+'+start + '('+programsXML.getElementsByTagName("start_date")[xmlIndex].childNodes[0].nodeValue + ')');
				//debug('+'+end + '('+programsXML.getElementsByTagName("end_date")[xmlIndex].childNodes[0].nodeValue + ')');
				if(end < start_datetime){
					debug('(' + id + ') ends before this time frame, omitting...');
					return false;
				}
							
				
				events.start_date[id] = (1+start.getMonth()) + '/' + start.getDate() + '/' + start.getFullYear() + ' ' + start.getHours() + ':' + start.getMinutes() + ':' + start.getSeconds();
				events.end_date[id] = (1+end.getMonth()) + '/' + end.getDate() + '/' + end.getFullYear() + ' ' + end.getHours() + ':' + end.getMinutes() + ':' + end.getSeconds();
				//events.end_date[id] = end.toLocaleDateString() +  ' ' + end.toLocaleTimeString();
				
				events.never_ends[id] = (programsXML.getElementsByTagName("never_ends")[xmlIndex].childNodes[0] == undefined) ? false : eval(programsXML.getElementsByTagName("never_ends")[xmlIndex].childNodes[0].nodeValue);
				//debug(xmlIndex + 'repeating events: ' +  events.repeats[id]);
				events.repeats[id] = (programsXML.getElementsByTagName("weekdays")[xmlIndex].childNodes[0] == undefined) ? '' : programsXML.getElementsByTagName("weekdays")[xmlIndex].childNodes[0].nodeValue;
				//debug(xmlIndex + 'repeating events: ' +  events.repeats[id]);
				
				var first_rep;
				var last_rep;

				
				if(events.repeats[id]){
					if(events.repeats[id] == 'weekly'){
						first_rep = (start.getDay() == 0) ? 7 : start.getDay();
						last_rep = (end.getDay() == 0) ? 7 : end.getDay();			
					}else{
						first_rep = parseInt(events.repeats[id].split('')[0]);
						last_rep = parseInt(events.repeats[id].split('')[events.repeats[id].length-1]);
					}
					start = getBoundedDate(start, 'start');
					end = getBoundedDate(end, 'end');
				}
				
				//debug('-'+start);
				//debug('-'+end);

				
				events.program_id[id] = programsXML.getElementsByTagName("ID")[xmlIndex].childNodes[0].nodeValue;
				events.event_id[id] = programsXML.getElementsByTagName("event_id")[xmlIndex].childNodes[0].nodeValue;
				events.color[id] = programsXML.getElementsByTagName("color")[xmlIndex].childNodes[0].nodeValue;
				events.blog_id[id] = (programsXML.getElementsByTagName("blog_id")[xmlIndex] == undefined) ? '' : programsXML.getElementsByTagName("blog_id")[xmlIndex].childNodes[0].nodeValue;
				events.post_id[id] = (programsXML.getElementsByTagName("post_id")[xmlIndex] == undefined) ? '' : programsXML.getElementsByTagName("post_id")[xmlIndex].childNodes[0].nodeValue;
				events.url[id] = (programsXML.getElementsByTagName("url")[xmlIndex] == undefined) ? '' : programsXML.getElementsByTagName("url")[xmlIndex].childNodes[0].nodeValue;
				
				events.category_name[id] = (programsXML.getElementsByTagName("category_name")[xmlIndex] == undefined) ? '' : programsXML.getElementsByTagName("category_name")[xmlIndex].childNodes[0].nodeValue;
				events.category_color[id] = (programsXML.getElementsByTagName("category_color")[xmlIndex] == undefined) ? '' : programsXML.getElementsByTagName("category_color")[xmlIndex].childNodes[0].nodeValue;
				

				if(programsXML.getElementsByTagName("name")[xmlIndex].childNodes[0] != undefined){
					if( events.name[id] == undefined){
						events.name[id] =  programsXML.getElementsByTagName("name")[xmlIndex].childNodes[0].nodeValue;
					}{
						$(events.name[id]).text(programsXML.getElementsByTagName("name")[xmlIndex].childNodes[0].nodeValue);
					}
				}
				
				if(programsXML.getElementsByTagName("description")[xmlIndex] != undefined){
					events.description[id] =  programsXML.getElementsByTagName("description")[xmlIndex].childNodes[0].nodeValue;
				}
				
				var end_mins = end.getHours()*60 + end.getMinutes();
				var start_mins = start.getHours()*60 + start.getMinutes();


				events.srow[id] = start.getHours()*opts.hour_fraction + Math.floor(opts.hour_fraction*start.getMinutes()/60.0);
				events.scol[id] = (events.repeats[id]) ? first_rep-1 : daysHash[start.getMonth() + '_' + start.getDate()];
				if(end_mins < start_mins){
					events.erow[id] = 24*opts.hour_fraction - 1;
				}else{
					events.erow[id] = end.getHours()*opts.hour_fraction - 1 + Math.ceil(opts.hour_fraction*end.getMinutes()/60.0);
				}
				events.ecol[id] = (events.repeats[id]) ? last_rep-1 : daysHash[end.getMonth() + '_' + end.getDate()];;
				if(events.ecol[id] == undefined) events.ecol[id] = events.scol[id];
				return true;
			}
			
			function loadEvents(response){
				if(programsXML.getElementsByTagName("ID").length > 0){
					
					var id;
					var curTime;
					for(var index=0; index<programsXML.getElementsByTagName("ID").length; ++index){
						curTime = new Date();
						id = curTime.getTime();
						/********************************/
						if(!updateEventFromXML(id, index)){
							debug('error updating from xml for ' + id);
							continue;
						}
						/********************************/
						if( (events.scol[id] != undefined) && (events.ecol[id] != undefined) ){ //make sure event is within the time frame
							debug(events.name[id] + '(' + id + ')');
							
							events.ecol[id] = events.scol[id];
							debug('(' +events.srow[id]+ ',' + events.scol[id] + ') to (' + events.erow[id] +',' + events.ecol[id] +')');
							createEvent(id, events.srow[id], events.scol[id], events.erow[id], events.ecol[id], false);
							drawEvent(id, false);
						}else{
							debug('Error (' + events.name[id] + '): (' +events.srow[id]+ ',' + events.scol[id] + ') to (' + events.erow[id] +',' + events.ecol[id] +')');

						}
					}
				}else{
					eval(response);
				}
			}
			
			function ajax_get_programs(action, id){
				
				var mysack = new sack("<?php bloginfo( 'wpurl' ); ?>/wp-content/plugins/program_scheduler/ajax_get_programs.php" );    
				mysack.execute = 0;
				mysack.method = 'POST';
				mysack.setVar( "action", "action_send_programs" );
				mysack.setVar('schedule_name', schedule_name);
				if(action == 'select'){
					mysack.setVar( 'content', 'minimal' );
				}else{
					mysack.setVar( 'content', 'full' );
					if(action == 'load'){
						mysack.setVar( 'start_date', start_datetime.toLocaleDateString() + ' ' +  start_datetime.toLocaleTimeString());
						var end_date = new Date();
						end_date.setTime(start_datetime.valueOf());
						end_date.setDate(end_date.getDate()+6);
						//alert('query end: ' + end_date.toString());
						mysack.setVar( 'end_date', end_date.toLocaleDateString() + ' ' +  end_date.toLocaleTimeString());
					}else if(action == 'single'){
						mysack.setVar( 'program_id', events.program_id[id]);
						mysack.setVar( 'event_id', events.event_id[id]);
					}
				}
				mysack.encVar( "cookie", document.cookie, false );
				mysack.onError = function() { alert('Ajax error in sending event.' ); };
				
				mysack.onCompletion = function completed(){
					//alert(mysack.response);
					/*if(eval(mysack.response) == false){
						programsXML = getXMLObject(mysack.response, false);
					}else{
						programsXML = getXMLObject(mysack.response, true);
					}*/
					programsXML = getXMLObject(mysack.response);
					
					if(action == 'load'){
						loadEvents(mysack.response);
						$(obj[objIndex]).find('#0_0').find("div:first").find('div.loading_div').remove();
						
					}else if(action == 'single'){
						updateEventFromXML(id, 0);
						var event_details_div = $("<div />");
						loadEventDetails(id, event_details_div);
						$(events.container[id]).next().append(event_details_div);
						var deleteButton = $("<span class='clickable' style='margin-left: 7px' >Close</span>").bind("click", function(e){
							if(cur_id > -1){
								toggleFocus(cur_id);
							}
						});
						$(events.container[id]).next().find('h4').append(deleteButton);
					}
				};
				
				mysack.runAJAX();
				return true;
			}
	
	
			function getFields(id){
				
				var myDates =  new Array();
				myDates[0] = new Date(events.start_date[id]);
				myDates[1] = new Date(events.end_date[id]);	

				var select = getSelectRepeat();
				//$(select).disable();
				var useSelect = false;
				if(events.repeats[id] != undefined){
					$(select).val(events.repeats[id]);
					
					if($(select).val() != null ){
						if($(select).val().length > 0)	useSelect = true;	
					}else if(events.repeats[id].length == 0){
						useSelect = true;
					}
				}else{
					useSelect = true;	
				}
				//alert(useSelect);
				
				var fields  = {
					name: $('<span class="program_name">' + $(events.name[id]).text() + '</span>'),
					description: $('<div class="program_description"/>'),
					url: $('<span class="program_url" >'+ events.url[id] + '</span>'),
					category_name: $('<span class="program_category_name">' + events.category_name[id] + '</span>'),
					category_color: $('<span class="program_category_color">' + events.category_color[id] + '</span>'),
					start_date: $('<span class="program_start_date">' + (myDates[0].getMonth()+1) + '/' + myDates[0].getDate() + '/' + myDates[0].getFullYear() + '</span>'),
					end_date: $('<span class="program_end_date">' +  (myDates[1].getMonth()+1) + '/' + myDates[1].getDate() + '/' + myDates[1].getFullYear() + '</span>'),
					start_time: $('<span class="program_start_time">' + myDates[0].getHours() + ':' + myDates[0].getMinutes() + ':' + myDates[0].getSeconds() + ' EST</span>'),
					end_time: $('<span class="program_end_time">' + myDates[1].getHours() + ':' + myDates[1].getMinutes() + ':' + myDates[1].getSeconds()  + ' EST</span>'),
					
					repeats: useSelect ? $(select).val() : getCheckboxRepeat(id)
				}
				
				$(fields.description).val(events.description[id]);
								
				$(fields.color).bind("keyup", function(e){
					setBackgroundColor(cur_id, this.value);
				});

				$(fields.repeats).bind("change", function(e){
					if(this.value == 'other'){
						doOtherDaysFields(this);
					}
				});

				return fields;
				
			}	

			function loadEventDetails(id, div){
				var fields = getFields(id);

				var form = $('<div />');
				var form_div1 = $("<div style='float:left;' />");
				
				var timeStuff = $("<div style='float:left;' />");
				var doDateTimeStuff = false;
				var extra;
				for(i in fields){
					if(i=='start_date'){
						doDateTimeStuff = true;
					}
					if(doDateTimeStuff){
						$(timeStuff).append(i.substr(0,1).toUpperCase() + i.substring(1).replace(/_/, ' ') + ': ');
						$(timeStuff).append('<br />');
						if(fields[i] instanceof Array) {
							extra = fields[i][1];
							fields[i] = fields[i][0];
							$(timeStuff).append(fields[i]);
							$(timeStuff).append(extra);
						}else{
							$(timeStuff).append(fields[i]);
						}
						
						$(timeStuff).append('<div style="clear:both" /> <br />');

					}else{
						$(form_div1).append(i.substr(0,1).toUpperCase() + i.substring(1).replace(/_/, ' ') + ': ');
						$(form_div1).append('<br />');

						$(form_div1).append(fields[i]);
						$(form_div1).append('<br />');
					}
				}
								
				$(form).append(form_div1);
				$(form).append(timeStuff);
				$(div).append(form);
			}
		
			function removeFocusAll(){
				for(var div_id in events.event_id){
					//alert(div_id);
					$(events.container[div_id]).removeClass('edit_single');
					$(events.container[div_id]).next().remove();
				}	
			}
			
			function toggleFocus(id){
				ajax_get_program(opts.schedule_name, events.program_id[id], events.container[id], 'left');
				
				
			}
			
			function toggleFocus_old(id){

				if($('#'+id).hasClass('edit_single')){
					$('#'+id).removeClass('edit_single');
					$(obj[objIndex]).selectable('enable');
					$(events.container[id]).next().remove();
					debug('disabling event : ' +cur_id);
					cur_id = -1;

				}else{
					removeFocusAll();
					$('#'+id).addClass('edit_single');
					$(obj[objIndex]).selectable('disable');
					$(events.container[id]).after("<div style='margin-left: " + $('#'+id).css('width') + "' class='event_details'><h4>Program Info</h4></div>");
					cur_id = id;
					ajax_get_programs('single', id);
					debug('current event id is : ' +cur_id);
				}
			}

			function deleteInterfaceEvent(id){
				if($('#'+id).hasClass('edit_single')){
					toggleFocus(id);
				}
				$(events.container[id]).remove();
				
				for(prop in events){
					delete events[prop][id];
				}
			}
			
			function clearAllEvents(){
				removeFocusAll();
				for(var div_id in events.event_id){
					deleteInterfaceEvent(div_id);
				}
			}

			function increase_event_width(id, ncols){
				debug('increasing event width for ' + id);
				events.width[id] += ncols * (opts.cell_width)+1;
				events.ecol[id] += 1;
			}

			function addRepeatingEvents(id, midEventID, wrapWeek){
				debug('adding repeating events for ' + $(events.name[id]).text() +  '(' +  events.repeats[id]+')');
				var start = new Date(events.start_date[id]);
				var end = new Date(events.end_date[id]);
				var days;
				
				if(events.repeats[id] == 'weekly'){
					days = [start.getDay()];
				}else{
					days = events.repeats[id].split('');
				}				

				var curTime;
				var col;
				var matrixStart;
				//alert(days);
				var prev_day = events.scol[id]+1;
				var prevID = id;
				var newIds = [];
				for(i in days){
					days[i] = parseInt(days[i]);
					if(days[i] == prev_day){
						continue;	
					}
					col = parseInt(days[i])-1;
					if(col == -1) col = 6;
					//alert(col);				
					curTime = new Date();
					newId = curTime.getTime();
					
					matrixStart = matrixLoc2Date(col, events.srow[id]);//returns Date object
					if(matrixStart >= end){
						debug('------' +newId + ' starts after event ends, omitting...');
						continue;
					}
					debug(days[i] + ' and ' + prev_day);

					if(days[i] == (prev_day+1)){
						increase_event_width(prevID, 1);
						if(midEventID != undefined && midEventID > -1){
							if( (events.ecol[midEventID] >= 6) && wrapWeek ){
								if((events.scol[midEventID]-1) == 0 ){
									events.scol[midEventID] = 0;
									increase_event_width(midEventID, 1);			
								}else{
									nID = addMidnightEvent(id, 0, events.erow[midEventID]);
									drawEvent(nID);									
								}
							}else if(events.ecol[midEventID] < 6 ){
								increase_event_width(midEventID, 1);
							}
						}
						prev_day = days[i];
						continue;
					}
					prev_day = days[i];
					prevID = newId;

					events.start_date[newId] = events.start_date[id];
					events.end_date[newId] = events.end_date[id];
					events.name[newId] = $(events.name[id]).text();
					events.program_id[newId] = events.program_id[id];
					events.event_id[newId] = events.event_id[id];
					events.color[newId] = events.color[id];
					events.category_name[newId] = events.category_name[id];
					events.category_color[newId] = events.category_color[id];
					
					events.srow[newId] = events.srow[id];
					events.scol[newId] = col;
					events.erow[newId] = events.erow[id];
					events.ecol[newId] = col;
					debug('-------(' +events.srow[newId]+ ',' + events.scol[newId] + ') to (' + events.erow[newId] +',' + events.ecol[newId] +')');
					createEvent(newId, events.srow[newId], events.scol[newId], events.erow[newId], events.ecol[newId]);
					newIds.push(newId);
				}
				for(i in newIds){
					drawEvent(newIds[i], false);
				}
			}
			
			function drawEvent(id){
				events.container[id] = $('<div id="'+id+'" class="calendar_overlay " style="background-color: '+ events.color[id] +'; height: '+((events.erow[id]-events.srow[id]+1)*opts.cell_height + (events.erow[id]-events.srow[id]-1))+'px; width: '+events.width[id]+'px;"></div>');
				if(events.category_color[id] != '-' ){
					setBackgroundColor(id, events.category_color[id]);
				}else{
					setBackgroundColor(id, events.color[id]);
				}
				
				$(obj[objIndex]).find('#'+events.srow[id]+'_'+events.scol[id]).find("div:first").prepend(events.container[id]);
				events.controls[id] = getControls();
				$(events.controls[id]).prepend(events.name[id]);
				$(events.container[id]).append($(events.controls[id]));										
				bindEvents(id);
			}
			
			function getControls() {
				return $("<div class='event_controls' style='height: "+ opts.cell_height +"px;' ></div>");
				
			}
			
			function addMidnightEvent(id, col, erow){
				var curTime = new Date();
				var newId = curTime.getTime();
				
				events.name[newId] = $(events.name[id]).text();
				events.program_id[newId] = events.program_id[id];
				events.event_id[newId] = events.event_id[id];
				events.color[newId] = events.color[id];
				events.category_name[newId] = events.category_name[id];
				events.category_color[newId] = events.category_color[id];
				
				events.srow[newId] = 0;
				events.scol[newId] = col;
				events.erow[newId] = erow;
				events.ecol[newId] = col;
				debug('adding midnight event at col: ' + col);
				createEvent(newId, events.srow[newId], events.scol[newId], events.erow[newId], events.ecol[newId], false);
				return newId;
			}		
			
			function createEvent(id, srow, scol, erow, ecol){
				events.name[id] = $("<span class='event_name' id='" + id + "_event_name'>"+events.name[id]+"</span>");
				events.description[id] = (events.description[id] == undefined) ? 'No description' : events.description[id];
				
				if(events.category_name[id] == undefined)events.category_name[id] = '';
				if(events.category_color[id] == undefined)events.category_color[id] = '';
				if(events.post_id[id] == undefined)events.post_id[id] = '';
				if(events.blog_id[id] == undefined)events.blog_id[id] = '';
				if(events.url[id] == undefined) events.url[id] = '';
				if(events.never_ends[id] == undefined) events.never_ends[id] = false;

				/***************************************************/
				events.srow[id] = srow;
				events.scol[id] = scol;
				events.erow[id] = erow;
				events.ecol[id] = ecol;
				/************************************************/

				if(events.color[id] == undefined){
					var ran = 100+(Math.ceil(Math.random()*1379))%900;
					events.color[id] = '#F4'+ran+'F';
				}
				
				events.width[id] = (ecol-scol+1)*(opts.cell_width);
				
				var midEventID = -1;
				var wrapWeek = false;
				if(events.start_date[id] != undefined){
					var start = new Date(events.start_date[id]);
					var end = new Date(events.end_date[id]);
					var end_mins = end.getHours()*60 + end.getMinutes();
					var start_mins = start.getHours()*60 + start.getMinutes();
					wrapWeek = (start <= start_datetime);
					if(start_mins > end_mins && end_mins > 0){
						var newcol = ((events.ecol[id]+1) > 6) ? 0 : events.ecol[id]+1;;
						if( (newcol != 0) || (start <= start_datetime) ){
							
							midEventID = addMidnightEvent(id, newcol, end.getHours()*opts.hour_fraction - 1 + Math.ceil(opts.hour_fraction*end.getMinutes()/60.0) );
						}
					}
				}
				
				if(events.repeats[id] && (events.repeats[id]!='weekly')){
					addRepeatingEvents(id, midEventID, wrapWeek);
				}
				
				if(midEventID > -1){
					drawEvent(midEventID, false);
				}
												

			}
			
			function bindEvents(id){			
				$(events.container[id]).bind("click", function(e){
						toggleFocus(this.id);
				});
			}
			
			$('.' + opts.mod_date_class).bind("click", function(e){
				
				var newDate = parse_start_date();

				debug('new: ' + newDate.getMonth() + ' ' + newDate.getDate() + ', ' + newDate.getFullYear());
				debug('old: ' + start_datetime.getMonth() + ' ' + start_datetime.getDate() + ', ' + start_datetime.getFullYear());
				
				//if( (newDate.getMonth() != start_datetime.getMonth()) && (newDate.getDate() != start_datetime.getDate()) && (newDate.getFullYear() != start_datetime.getFullYear()) ){
				if( newDate.getDate() != start_datetime.getDate() ){
					clearAllEvents();
					$(obj[objIndex]).find('#0_0').find("div:first").prepend('<div class="loading_div calendar_overlay" style="height: ' + (opts.cell_height*(opts.hour_fraction*24)) + 'px; width: ' + (opts.cell_width*7-35) + 'px;"><img src="<?php echo get_bloginfo('url'); ?>/wp-content/plugins/program_scheduler/images/loading.gif" /></div>');
					start_datetime.setTime(newDate.getTime());
					initializeDaysHash();
					ajax_get_programs('load');
				}
			});
			
			if(opts.datepicker_selector != undefined){
				$(opts.datepicker_selector).datepicker({
					showOn: 'button',
					showAnim: 'fadeIn',
					buttonImage: '<?php echo  get_bloginfo('url')."/wp-content/plugins/program_scheduler/images/calendar.gif"; ?>', 
					buttonImageOnly: true,
					onSelect: function(dateText) {
						var d = new Date(dateText);
						if(isSingle){
							update_date(find_previous_day(d.getDay(), d));
						}else{
							update_date(find_previous_day(1, d));
						}
				
						var newDate = parse_start_date();
						if(newDate.getTime() != start_datetime.getTime()){
							clearAllEvents();
							$(obj[objIndex]).find('#0_0').find("div:first").prepend('<div class="loading_div calendar_overlay" style="height: ' + (opts.cell_height*(opts.hour_fraction*24)) + 'px; width: ' + (opts.cell_width*7-35) + 'px;"><img src="<?php echo get_bloginfo('url'); ?>/wp-content/plugins/program_scheduler/images/loading.gif" /></div>');
							start_datetime.setTime(newDate.getTime());
							initializeDaysHash();
							ajax_get_programs('load');
						}
					}
				});
			}
			
			$(document).keypress(function keyPressHandler(e) {
				var s = (window.event) ?    // MSIE or Firefox?
						 event.keyCode : e.keyCode;
				switch(s)
				{
					// user presses the "esc"
					case 27:	if(cur_id > -1){toggleFocus(cur_id);} //MSIE
								break;
					case e.DOM_VK_ESCAPE:	if(cur_id > -1){toggleFocus(cur_id);} //Firefox
					// user presses the "g" key 
					//case 103:	showViaKeypress("#links");
				}

   			});
			
			////////////////////////////////////////////////////////////////////////
			//
			// acuire elements inside
			//
			////////////////////////////////////////////////////////////////////////

			// calendar vars
			//------------------------------------------------------------------
			//define event object array
			var programsXML;
			var start_datetime = opts.start_datetime;
			debug('starting at ' + start_datetime);
			var daysHash = {};
			initializeDaysHash();
			
			var categories = {
				name: [],
				color: []
				};
				
			var events  = {
				event_id: [],
				controls: [],
				container: [],
				width: [],
				program_id: [],
				name: [],
				description: [],
				category_name: [],
				category_color: [],
				start_date: [],
				end_date: [],
				never_ends: [],
				repeats: [],
				color: [],
				srow: [],
				scol: [],
				erow: [],
				ecol: [],
				url: [],
				blog_id: [],
				post_id: []
			};
			
			var cur_id = -1;
			var schedule_name = isArray(opts.schedule_name) ? opts.schedule_name[objIndex] : opts.schedule_name;
			ajax_get_programs('load');

        });
    	
    };
    
    
    ////////////////////////////////////////////////////////////////////////////
    //
    // $.fn.calendar.defaults
    // set default  options
    //
    ////////////////////////////////////////////////////////////////////////////
    $.fn.calendar.defaults = {
        start_datetime:    parse_start_date(),
        cell_width: 100,
        cell_height: 15,
        hour_fraction: 2,
		mod_date_class: 'date_changer',
		schedule_name: getURLVar('schedule_name'),
		border: 2
    };


});
