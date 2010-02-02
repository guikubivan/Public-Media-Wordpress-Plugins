<?php
	require_once(dirname(__FILE__).'/../../../wp-load.php');
	require_once(dirname(__FILE__).'/../../../wp-admin/admin.php');
	header("Content-Type: text/javascript");
	
	include_once(dirname(__FILE__).'/utils_js.php');
?>


/**
* Program scheduler editor
* Author : http://wfiu.org
*
* Credits: http://mypage.iu.edu/~gvanwoer/
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

        return this.each(function(){


			function find_last_valid_row (col, row){
				var crow = row;
				while(jQuery("#" + crow + '_' + col).hasClass('ui-selected') ){
					//jQuery("#" + crow + '_' + col).css('background-color', 'white');
					++crow;
				}
				if(crow != row){
					--crow;
				}
				return crow;
			}
			
			function getControls() {
				return $("<div class='event_controls' style='height: "+ opts.cell_height +"px;' ><span class='delete_button'>x</span></div>");
				
			}
			
			function clearSelected(sr, sc, er, ec){
				//debug("clearing (" + sr + ", " + sc + ") ("+er+","+ec+ ")");
				for(var i=sc; i<=ec;++i){
					for(var j=sr; j<=er; ++j){
						$(obj).find("#" + j + '_' + i).removeClass('selectable_cell');
						//$(obj).find("#" + j + '_' + i).removeClass('ui.selectee');
						$(obj).find("#" + j + '_' + i).removeClass('ui-selected');
						$(obj).find("#" + j + '_' + i).removeClass('ui-selectee');
					}
				}
			}
			
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
				$(checkboxesDiv).append(span);
				$(selectElement).before(checkboxesDiv);
				//document.forms.event_details.elements.event_repeats
				$(selectElement).remove();
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
					table += '<td style="text-align:center">'+ days[i] + '<br /><input type="checkbox" name="event_repeats" '+checked+' value="'+j+'"></td>';
				}	
				table += "</tr></table>";
				$(checkboxesDiv).append(table);
				
				var span = $(" <span class='clickable'>select</span>");
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


			function bindEvent2ProgramFromXML(id, index){
				$(events.name[id]).text(programsXML.getElementsByTagName("name")[index].childNodes[0].nodeValue);
				events.color[id] = programsXML.getElementsByTagName("color")[index].childNodes[0].nodeValue;
				events.category_color[id] = (programsXML.getElementsByTagName("category_color")[index] == undefined) ? '' : programsXML.getElementsByTagName("category_color")[index].childNodes[0].nodeValue;
				if(events.category_color[id]){
					setBackgroundColor(id, events.category_color[id]);
				}else{
					setBackgroundColor(id, events.color[id]);
				}
				
				events.program_id[id] = programsXML.getElementsByTagName("ID")[index].childNodes[0].nodeValue;
				//$(events.description[id]).text(programsXML.getElementsByTagName("description")[index].childNodes[0].nodeValue);
				//events.start_date[id] = programsXML.getElementsByTagName("ID")[index].childNodes[0].nodeValue;
				//events.category[id] = programsXML.getElementsByTagName("category")[index].childNodes[0].nodeValue;;
			}

			function updateCategories(event_id){
				if( events.category_name[event_id] == undefined) events.category_name[event_id] = '';
				if( events.category_color[event_id] == undefined) events.category_color[event_id] = '';
				//debug('cate name: ' + events.category_name[event_id]);
				var name_text = $('<input style="width: 150px" type="text" name="event_category_name" value="' + events.category_name[event_id] + '"/>');
				var color_text = $('<input type="text" name="event_category_color" value="' + events.category_color[event_id] + '"/>');
				var doSelect = $('<span class="clickable">list</span>');
				if(categories.name.length > 0){
					debug('Updating categories-select');
					var sel_text = '<select name="event_category_name"><option value="">none</option>';
					for(index in categories.name){
						sel_text += '<option value="' + categories.name[index] + '">' + categories.name[index] + '</option>';
					}
					sel_text += '<option value="other">other</option></select>';
					
					cat_select = $(sel_text);
					if(events.category_name[event_id]){
						//debug('has cat name: ' + events.category_name[event_id]);
						$(cat_select).val(events.category_name[event_id]);
					}
					
					$(cat_select).bind("click", function(e){
						if($(cat_select).val() == 'other' ){
							
							$(doSelect).bind("click", function(e){
								
								$(this).prev().remove();
								$(this).next().remove();
								$(this).next().remove();
								$(this).next().remove();
								$(this).next().remove();
								//$(this).next().remove();
								$(this).before(cat_select);
								$(this).remove();
							});
							
							
							$(this).after(color_text);
							$(this).after('<br /><span>Category color: </span><br />');
							$(this).after(doSelect);
							$(this).after(name_text);
							$(this).remove();

						}
					});
					//alert(document.forms.event_details.elements);
					//$(document.forms.event_details.elements.event_description).after('<br />');
					$(document.forms.event_details.elements.event_description).after(cat_select);
					
					$(document.forms.event_details.elements.event_description).after('<br />Category name: <br />');
					
				}else{
					debug('Updating categories-text');
					$(document.forms.event_details.elements.event_description).after(color_text);
					$(document.forms.event_details.elements.event_description).after('<br /><span>Category color: </span><br />');
					$(document.forms.event_details.elements.event_description).after(name_text);
					$(document.forms.event_details.elements.event_description).after('<br />Category name: <br />');
				}
			}

			function updateProgramSelect(id){
				debug('Updating program select');
				if(programsXML.getElementsByTagName("ID").length > 0){
					
					var select = $("<select id='program_id' name='program_id'><option value='-1'>None</option></select>");
					
					for(var index=0; index<programsXML.getElementsByTagName("ID").length; ++index){
						$(select).append("<option value='" + index + "' >" + programsXML.getElementsByTagName("name")[index].childNodes[0].nodeValue + "</option>");
					}
					
					$(events.container[cur_id]).next().find("div:first").find("div:first").html("Select existing program:<br />");
					$(events.container[cur_id]).next().find("div:first").find("div:first").append(select);
					$(events.container[cur_id]).next().find("div:first").find("div:first").append("<input type='button' value='OK' />");
					$(events.container[cur_id]).next().find("div:first").find("div:first").append("<br />OR<br />");
					
					$(select).next().bind("click", function(e){
						if($(select).val() > -1 ){
							var xmlIndex = $(select).val();
							$(this).parent().parent().next().show();
							$(this).parent().parent().hide();
							$(this).parent().parent().next().html('');
							events.program_id[cur_id] = programsXML.getElementsByTagName("ID")[xmlIndex].childNodes[0].nodeValue;
							$(events.container[cur_id]).css('background-color', programsXML.getElementsByTagName("color")[xmlIndex].childNodes[0].nodeValue);
							loadProgramForm(cur_id, $(this).parent().parent().next(), true);
														
						}
						/*
						if($(select).val() > -1 ){
							ajax_bind_event(cur_id, $(select).val());
						}*/
						
					});
				}else{
					$(events.container[id]).next().find("div:first").find("div:first").html("No existing programs.<br />");
				}
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
				if(end < start_datetime && (!$(events.container[id]).hasClass('dontdelete'))){
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
				
				//events.category[id] = programsXML.getElementsByTagName("category")[xmlIndex].childNodes[0].nodeValue;
				
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
					//debug('description: ' + programsXML.getElementsByTagName("description")[xmlIndex].childNodes[0].nodeValue);
					//if( events.description[id] == undefined){
					events.description[id] =  programsXML.getElementsByTagName("description")[xmlIndex].childNodes[0].nodeValue;
					//}
				}

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
							createEvent(id, events.srow[id], events.scol[id], events.erow[id], events.ecol[id]);
							drawEvent(id, false);
						}else{
							debug('Error (' + events.name[id] + '): (' +events.srow[id]+ ',' + events.scol[id] + ') to (' + events.erow[id] +',' + events.ecol[id] +')');

						}
					}
				}else{
					eval(response);
				}
			}
			
			function ajax_get_categories(id){
				debug('Ajax - getting cats');
				var mysack = new sack("<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    
				mysack.execute = 0;
				mysack.method = 'POST';
				mysack.setVar( "action", "action_send_categories" );
				mysack.setVar('schedule_name', opts.schedule_name);
				mysack.encVar( "cookie", document.cookie, false );
				mysack.onError = function() { alert('Ajax error in sending event.' ); };

				mysack.onCompletion = function completed(){
					if(mysack.response == 'false'){
						categories = {
							name: [],
							color: []
						};						
					}else{
						eval(mysack.response);
						//debug(categories.name);
					}
					updateCategories(id);			
				};
				
				mysack.runAJAX();
				return true;
			}
			
			function ajax_get_programs(action, id){
				
				var mysack = new sack("<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    
				mysack.execute = 0;
				mysack.method = 'POST';
				mysack.setVar( "action", "action_send_programs" );
				mysack.setVar('schedule_name', opts.schedule_name);
				
				if(action == 'select'){
					mysack.setVar( 'content', 'minimal' );
				}else{
					mysack.setVar( 'content', 'full' );
					if(action == 'load'){
						//alert('query start: ' + start_datetime.toString());
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
				
				//mysack.onLoading = whenLoading;
				//mysack.onLoaded = whenLoaded; 
				//mysack.onInteractive = whenInteractive;

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
						$(obj).find('#0_0').find("div:first").find('div.loading_div').remove();
					}else if(action == 'single'){
						updateEventFromXML(id, 0);
						var event_details_div = $("<div class='event_form' />");
						loadProgramForm(id, event_details_div);
						$(events.container[id]).next().append(event_details_div);
						
						var closeButton = $("<span class='button' style='margin-left: 7px' >Close</span>").bind("click", function(e){
							if(cur_id > -1){
								toggleFocus(cur_id);
							}
						});
						var deleteButton = $("<span class='button' style='margin-left: 7px' >Delete</span>").bind("click", function(e){
							if(cur_id > -1){
								confirmDelete(cur_id);
							}
						});
						$(events.container[id]).next().find('h4').append(deleteButton);
						$(events.container[id]).next().find('h4').append(closeButton);
						
						
						
						if(categories.name.length > 0){
							updateCategories(id);	
						}else{
							ajax_get_categories(id);
						}
					}
					
					if( (events.container[cur_id] == undefined) || (cur_id == -1 )){
						return;	
					}
					if(action == 'select'){
						updateProgramSelect(id);
						updateXML = false;
					}
				};
				
				mysack.runAJAX();
				return true;
			}
	
	
			function ajax_delete_event(myid){
				if(events.program_id[myid] == -1 ) return;
				
				var mysack = new sack("<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    

				//mysack.execute = 1;
				mysack.method = 'POST';
				mysack.setVar( "action", "action_delete_event" );
				mysack.setVar('schedule_name', opts.schedule_name);
				//mysack.setVar('program_id',  events.program_id[myid]);
				mysack.setVar('event_id',  events.event_id[myid]);

				mysack.encVar( "cookie", document.cookie, false );
				mysack.onError = function() { alert('Ajax error in sending event.' )};
				mysack.onCompletion = function completeDelete(){
					var success = eval(mysack.response);
					if(success){
						deleteInterfaceEvent(myid);
					}else{
						alert('Error when deleting event');	
					}
				};
				mysack.runAJAX();

				return true;
			}
			
			function ajax_bind_event(id, xmlIndex){
				var mysack = new sack("<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    
				//mysack.execute = 1;
				mysack.method = 'POST';
				mysack.setVar( "action", "action_receive_event" );
				mysack.setVar('schedule_name', opts.schedule_name);
				//alert(events.start_date[id]);
				//alert(events.end_date[id]);
				var event = { start_date: events.start_date[id],
							end_date: events.end_date[id],
							never_ends: events.never_ends[id],
							weekdays: events.repeats[id],
							program_id: programsXML.getElementsByTagName("ID")[xmlIndex].childNodes[0].nodeValue
				}
							
				for(prop in event){
					mysack.setVar( prop, event[prop] );
				}
				mysack.encVar( "cookie", document.cookie, false );
				mysack.onError = function() { alert('Ajax error in sending event.' )};
				
				mysack.onCompletion = function setEventProperties(){
					var resp = eval(mysack.response);
					if( (typeof resp == 'object')  && (resp.length > 0) ){
						events.event_id[cur_id] = resp[1];
						bindEvent2ProgramFromXML(cur_id, xmlIndex);
						toggleFocus(cur_id);
						alert('Program instance added');
					}
					
				};
				mysack.runAJAX();

				return true;
	
				
			}
				
			function ajax_send_event(id){
				var mysack = new sack("<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    

				//mysack.execute = 1;
				mysack.method = 'POST';
				mysack.setVar( "action", "action_receive_event" );
				mysack.setVar('schedule_name', opts.schedule_name);
				var event = { name: $(events.name[id]).text(),
							description: events.description[id],
							start_date: events.start_date[id],
							end_date: events.end_date[id],
							never_ends: events.never_ends[id],
							weekdays: events.repeats[id],
							color: events.color[id],
							category_name: events.category_name[id],
							category_color: events.category_color[id],
							url: events.url[id],
							blog_id: events.blog_id[id],
							post_id: events.post_id[id]
							}
				
				if(events.program_id[id]> -1) event.program_id = events.program_id[id];
				if(events.event_id[id]> -1) event.event_id = events.event_id[id];
				//alert(event.name);
				//alert(event.event_id);
				//return;
				for(prop in event){
					mysack.setVar( prop, event[prop] );
				}
				mysack.encVar( "cookie", document.cookie, false );
				mysack.onError = function() { alert('Ajax error in sending event.' )};
				mysack.onCompletion = function setProgramID(){
					var response = eval(mysack.response);
					if(response == true){
						debug('Program and instance updated (program id:' + events.program_id[id] + ', event id:' + events.event_id[id] + ')');
						toggleFocus(id);
						//window.location.reload();
					}else if( (typeof response == 'object')  && (response.length > 0) ){
						events.program_id[id] = response[0];
						events.event_id[id] = response[1];
						debug('Program added (program id:' + events.program_id[id] + ') with instance (event id:' + events.event_id[id] + ')');
						toggleFocus(id);
						//window.location.reload()
					}
					updateXML = true;
					categories = {
						name: [],
						color: []
					};
					
				};
				mysack.runAJAX();

				return true;

			}

			function saveEventDetails(id){
				if(document.forms.event_details.elements.event_name != undefined){
					$(events.name[id]).text(document.forms.event_details.elements.event_name.value);
				
					//alert(document.forms.event_details.elements.event_description.value);
					events.description[id] = document.forms.event_details.elements.event_description.value;
					//alert($(events.description[id]).text());
					
					events.category_name[id] = document.forms.event_details.elements.event_category_name.value;
					if(document.forms.event_details.elements.event_category_color != undefined) events.category_color[id] = document.forms.event_details.elements.event_category_color.value;

					events.post_id[id] = document.forms.event_details.elements.event_post_id.value;
					events.blog_id[id] = document.forms.event_details.elements.event_blog_id.value;
					events.url[id] = document.forms.event_details.elements.event_url.value;
					
					events.color[id] = document.forms.event_details.elements.event_color.value;
					$(events.container[id]).css('background-color', events.color[id]);
				}
									
				events.start_date[id] = document.forms.event_details.elements.event_start_date.value + ' ' + document.forms.event_details.elements.event_start_time.value;
				events.end_date[id] = document.forms.event_details.elements.event_end_date.value + ' ' + document.forms.event_details.elements.event_end_time.value;
				events.never_ends[id] = document.forms.event_details.elements.event_never_ends.checked;

				if(document.forms.event_details.elements.event_repeats.nodeName == 'SELECT'){
					events.repeats[id] = document.forms.event_details.elements.event_repeats.value;
					//debug('1saving events repeats to : ' +events.repeats[id]);
				}else{
					events.repeats[id] = '';
					for(i=0; i<document.forms.event_details.elements.event_repeats.length; ++i){
						if(document.forms.event_details.elements.event_repeats[i].checked){
							events.repeats[id] += document.forms.event_details.elements.event_repeats[i].value;
						}
						
					}
				}
				//debug('2saving events repeats to : ' +events.repeats[id]);

				if( (events.program_id[cur_id] > -1) && (events.event_id[cur_id] == -1) ){
					ajax_bind_event(id, $(events.container[cur_id]).next().find("div:first").find("div:first").find('select:first').val());
					//alert($(events.container[cur_id]).next().find("div:first").find("div:first").find('select:first').val());
				}else{
					ajax_send_event(id);
				}
				
			}
			
			function getDateTimeFields(id){
				var myDates =  new Array();
				myDates[0] = new Date(events.start_date[id]);
				myDates[1] = new Date(events.end_date[id]);	
				var select = getSelectRepeat();
				var useSelect = false;
				if(events.repeats[id] != undefined){
					$(select).val(events.repeats[id]);
					if($(select).val()){
						if($(select).val().length > 0){
							useSelect = true;
						}
					}else if(events.repeats[id].length == 0){
						useSelect = true;
					}
				}else{
					useSelect = true;	
				}
				var isChecked = (events.never_ends[id] ==true) ? 'checked="checked"' : '';
				var isDisabled = (events.never_ends[id] ==true) ? 'disabled' : '';
				var fields  = {
					start_date: $('<input type="text" name="event_start_date" value="' + (myDates[0].getMonth()+1) + '/' + myDates[0].getDate() + '/' + myDates[0].getFullYear() + '" />'),
					end_date: [$('<input type="text" name="event_end_date" value="' + (myDates[1].getMonth()+1) + '/' + myDates[1].getDate() + '/' + myDates[1].getFullYear() + '" '+isDisabled+'/ ><input type="checkbox" ' + isChecked + ' onclick="this.previousSibling.disabled = this.checked;" name="event_never_ends" />'), 'Never'],
					start_time: $('<input type="text" name="event_start_time" value="' + myDates[0].getHours() + ':' + myDates[0].getMinutes() + ':' + myDates[0].getSeconds()  + '" />'),
					end_time: $('<input type="text" name="event_end_time" value="' + myDates[1].getHours() + ':' + myDates[1].getMinutes() + ':' + myDates[1].getSeconds()  + '" />'),
					repeats: useSelect ? select : getCheckboxRepeat(id)
				}
				
				$(fields.repeats).bind("change", function(e){
					if(this.value == 'other'){
						doOtherDaysFields(this);
					}
				});

				return fields;
			}
			
			function getFields(id){
				

				var fields  = {
					name: $('<input type="text" name="event_name" value="' + $(events.name[id]).text() + '"/>'),
					description: $('<textarea style="width: 300px; height: 100px;" name="event_description" />'),
					url: $('<input type="text" name="event_url" value="' + events.url[id] + '"/>'),
					color: $('<input type="text" title="include the # for hex colors" size="10" name="event_color" value="' + events.color[id] + '" />'),
					blog_id: $('<input type="text" size="9" MAXLENGTH="9"  name="event_blog_id" value="' + events.blog_id[id] + '" />'),
					post_id: $('<input type="text" size="9" MAXLENGTH="9" name="event_post_id" value="' + events.post_id[id] + '" />'),
				}
				

				$(fields.description).val(events.description[id]);
								
				$(fields.color).bind("keyup", function(e){
					setBackgroundColor(cur_id, this.value);
				});

				return fields;
				
			}	

			function loadProgramForm(id, div, dateOnly){
				var form = $('<form name="event_details">');
				var form_div1 = $("<div style='float:left;' />");
				
				var fields = getFields(id);
				
				for(i in fields){
					$(form_div1).append(i.substr(0,1).toUpperCase() + i.substring(1).replace(/_/, ' ') + ': ');
					$(form_div1).append('<br />');

					$(form_div1).append(fields[i]);
					$(form_div1).append('<br />');
				}
				
				var dateTimeFields = getDateTimeFields(id);
				var timeStuff = $("<div style='float:left;' />");
				var extra;
				for(i in dateTimeFields){
					$(timeStuff).append(i.substr(0,1).toUpperCase() + i.substring(1).replace(/_/, ' ') + ': ');
					$(timeStuff).append('<br />');
					if(dateTimeFields[i] instanceof Array) {
						extra = dateTimeFields[i][1];
						dateTimeFields[i] = dateTimeFields[i][0];
						$(timeStuff).append(dateTimeFields[i]);
						$(timeStuff).append(extra);
					}else{
						$(timeStuff).append(dateTimeFields[i]);
					}
					
					$(timeStuff).append('<div style="clear:both" /> <br />');
				}

				var saveButton = $('<input id="'+id+'_save_button" type="button" value="Save" />');
				$(saveButton).bind("click", function(e){
					saveEventDetails(id);
				});
				$(timeStuff).append(saveButton);
				if(dateOnly == undefined){
					$(form).append(form_div1);
				}
				
				$(form).append(timeStuff);
				$(div).append(form);
			}
			
			function loadForm(id){
				if(events.program_id[id] > -1){
					ajax_get_programs('single', id);
				}else{
					var add_details_choice = $("<div />");
					$(add_details_choice).append(" <input type='button' onclick='jQuery(this).parent().next().show();jQuery(this).parent().hide();' value='Add new program'/><br />");

					var event_details_div = $("<div class='event_form' style='display:none;' />");
					loadProgramForm(id, event_details_div);

					//$(event_details_div).hide();
					$(events.container[id]).next().append(add_details_choice);
					$(events.container[id]).next().append(event_details_div);
					
					$(events.container[id]).next().find("div:first").prepend("<div>Getting programs...</div>");
					var deleteButton = $("<span class='button' style='margin-left: 7px' >Cancel</span>").bind("click", function(e){
						if(cur_id > -1){
							toggleFocus(cur_id);
						}
					});
					
					$(events.container[id]).next().find('h4').append(deleteButton);
					
					//$(add_details_choice).append("<div style='text-align:right' />");
					//$(add_details_choice).find("div").append(deleteButton);
					//$(add_details_choice).append(deleteButton);
					
					if(updateXML){
						ajax_get_programs('select');
					}else{
						updateProgramSelect(id);
					}
					
					if(categories.name.length > 0){
						updateCategories(id);						
					}else{
						ajax_get_categories();
					}
				}
				
			}

			function removeFocusAll(){
				for(var div_id in events.event_id){
					//alert(div_id);
					if($(events.container[div_id]).hasClass('edit_single')){
						$(events.container[div_id]).removeClass('edit_single');
						$(events.container[div_id]).next().remove();
					}
				}	
			}
			
			function toggleFocus(id){
				if($(events.container[id]).hasClass('edit_single')){
					$(events.container[id]).removeClass('edit_single');
					$(obj).selectable('enable');
					$(events.container[id]).next().remove();
					debug('disabling event : ' +cur_id);
					cur_id = -1;
					
					if((events.event_id[id] == -1) && (!$(events.container[id]).hasClass('dontdelete'))){
						deleteInterfaceEvent(id);
					}
				}else{
					if(events.event_id[cur_id] == -1 ){
						deleteInterfaceEvent(cur_id);
					}
					removeFocusAll();
					$(events.container[id]).addClass('edit_single');
					$(obj).selectable('disable');
					$(events.container[id]).after("<div style='margin-left: " + $('#'+id).css('width') + "' class='event_details'><h4>Edit Details</h4></div>");
					cur_id = id;
					loadForm(id);
					debug('current event id is : ' +cur_id + '(program id: ' + events.program_id[id] + ', event_id: '+events.event_id[id]+')');
				}
			}
			
			function resetCells(sr, sc, er, ec){
				for(var i=sc; i<=ec;++i){
					for(var j=sr; j<=er; ++j){
						//alert("#" + j + '_' + i);
						$(obj).find("#" + j + '_' + i).addClass('selectable_cell');
						//$(obj).find("#" + j + '_' + i).removeClass('ui.selectee');
						$(obj).find("#" + j + '_' + i).addClass('ui-selected');
						$(obj).find("#" + j + '_' + i).addClass('ui-selectee');
					}
				}
			}
			
			function clearAllEvents(){
				removeFocusAll();
				for(var div_id in events.event_id){
					deleteInterfaceEvent(div_id);
				}
			}
			
			function deleteInterfaceEvent(id){
				if($(events.container[id]).hasClass('edit_single')){
					toggleFocus(id);
				}
				debug('deleting interface event ' + id);
				$(events.container[id]).remove();
				
				//resetCells(events.srow[id], events.scol[id], events.erow[id], events.ecol[id]);
				for(prop in events){
					delete events[prop][id];
				}
			}
			
			function confirmDelete(id){
				var answer = confirm('Delete this event belonging to program: ' + $(events.name[id]).text() + '?')
				if (answer){
					ajax_delete_event(id);
				}				
			}
			
			function increase_event_width(id, ncols){
				debug('increasing event width for ' + id);
				//var cur_width = (events.ecol[id] - events.scol[id] + 1 ) * (opts.cell_width+1);
				//var new_width = cur_width + ncols * (opts.cell_width);
				events.width[id] += ncols * (opts.cell_width+1);
				
				//$(events.container[id]).css('width', new_width + 'px');
				events.ecol[id] += 1;
				//clearSelected(events.srow[id], events.ecol[id], events.erow[id], events.ecol[id]);
			}
			
			function addRepeatingEvents(id, midEventID, wrapWeek){
				//debug('adding repeating events for ' + $(events.name[id]).text() +  '(' +  events.repeats[id]+')');
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
					debug(prev_day + ' and ' + days[i]);

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
					debug('-------(' +events.srow[newId]+ ',' + events.scol[newId] + ') to (' + events.erow[newId] +',' + events.ecol[newId] +') id: '+newId);
					createEvent(newId, events.srow[newId], events.scol[newId], events.erow[newId], events.ecol[newId]);
					newIds.push(newId);
				}
				for(i in newIds){
					drawEvent(newIds[i], false);	
				}
			}
			
			function drawEvent(id, doToggle){
				var color = (events.category_color[id]) ? events.category_color[id] : events.color[id];
				events.container[id] = $('<div id="'+id+'" class="calendar_overlay " style="background-color: '+ color +'; height: '+((events.erow[id]-events.srow[id]+1)*opts.cell_height + (events.erow[id]-events.srow[id]-1))+'px; width: '+events.width[id]+'px;"></div>');
				$(obj).find('#'+events.srow[id]+'_'+events.scol[id]).find("div:first").prepend(events.container[id]);
				events.controls[id] = getControls();
				$(events.controls[id]).prepend(events.name[id]);
				$(events.container[id]).append($(events.controls[id]));										
				bindEvents(id);
				if(doToggle){
					toggleFocus(id);
				}
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
				createEvent(newId, events.srow[newId], events.scol[newId], events.erow[newId], events.ecol[newId]);
				return newId;
			}	
			
			function createEvent(id, srow, scol, erow, ecol){
				//clearSelected(srow, scol, erow, ecol);
				if(events.event_id[id] == undefined){
					events.event_id[id] = -1;					
				}
				
				events.name[id] = (events.name[id] == undefined) ? $("<span class='event_name' id='" + id + "_event_name'>Untitled</span>") : $("<span class='event_name' id='" + id + "_event_name'>"+events.name[id]+"</span>");
				//events.description[id] = (events.description[id] == undefined) ? $("<div class='event_description' id='" + id + "_event_description'>No description</div>") : $("<div class='event_description' id='" + id + "_event_description'>"+events.description[id]+"</div>");
				events.description[id] = (events.description[id] == undefined) ? '' : events.description[id];
				
				if(events.program_id[id] == undefined)events.program_id[id] = -1;
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
				
				if( (events.start_date[id] == undefined) | (events.end_date[id] == undefined) ){
					var myDates = getStartEndDates(id);
					events.start_date[id] = myDates[0].toLocaleDateString() +  ' ' + myDates[0].toLocaleTimeString();
					events.end_date[id] =  myDates[1].toLocaleDateString() +  ' ' + myDates[1].toLocaleTimeString();
				}
				
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
					if( $(e.target).hasClass('delete_button') ){
						confirmDelete(id);
					}else{
						//toggleFocus(this.id);
						toggleFocus(id);
					}
				});
							/*		
				$(events.controls[id]).find('.delete_button').bind("click", function(e){
					confirmDelete(this);
				});*/

			}

			obj.selectable({ filter: "td.selectable_cell", 
				autoRefresh: false,
				selected: function(event, ui) { var a;},
				stop: function(event, ui) {
					if(cur_id > -1 )return;
					if($(ui.selectable).find('td.ui-selected:first').length  == 0)return;

					/*if(cur_id > -1){
						toggleFocus(cur_id);
					}*/

					var sid= $(ui.selectable).find('td.ui-selected:first').attr('id');
					var eid = $(ui.selectable).find('td.ui-selected:last').attr('id');
					sind = sid.split('_');
					eind = eid.split('_');

					var srow = sind[0];
					var scol = sind[1];
					
					var erow = eind[0];
					var ecol = eind[1];
					var myDate=new Date();
					var div_id = myDate.getTime();
					//alert(((erow+ecol+scol+srow)%10));
					
					
					for(var i=scol; i<=ecol; ++i){
						this_row = find_last_valid_row(i, srow)
						if(this_row < erow){
							debug('col ' + i + ' terminated prematurely at ' + this_row + ' (end row is ' + erow + ')');	
																				
							if(i > scol){
								ecol = i-1;
							}else{
								erow = this_row;
								ecol = i;
							}
							break;
						}//else{
							//alert('col ' + i + ' ok (end row is ' + erow + ')');
						//}
					}
					
					if(! $(obj).find("#" + srow + '_' + scol).hasClass('selectable_cell') ){return;}					
					ecol = scol;
					createEvent(div_id, srow, scol, erow, ecol);	
					drawEvent(div_id, true);
					//$(ui.selectable).find('td.ui-selected').removeClass('selectable_cell');
					//$(ui.selectable).find('td.ui-selected').removeClass('ui-selected');
				}

			});
			
			$('.' + opts.mod_date_class).bind("click", function(e){
				clearAllEvents();
				$(obj).find('#0_0').find("div:first").prepend('<div class="loading_div calendar_overlay" style="height: ' + (opts.cell_height*(opts.hour_fraction*24)) + 'px; width: ' + (opts.cell_width*7+7) + 'px;"><img src="<?php echo get_bloginfo('url'); ?>/wp-content/plugins/program_scheduler/images/loading.gif" /></div>');
				start_datetime = parse_start_date();
				initializeDaysHash();
				ajax_get_programs('load');
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
							clearAllEvents();
							$(obj).find('#0_0').find("div:first").prepend('<div class="loading_div calendar_overlay" style="height: ' + (opts.cell_height*(opts.hour_fraction*24)) + 'px; width: ' + (opts.cell_width*7+7) + 'px;"><img src="<?php echo get_bloginfo('url'); ?>/wp-content/plugins/program_scheduler/images/loading.gif" /></div>');
							start_datetime = parse_start_date();
							initializeDaysHash();
							ajax_get_programs('load');
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
   			
			$('#neweventbutton').bind("click", function buttonclicked(e) {
				var myDate=new Date();
				var div_id = myDate.getTime();
				createEvent(div_id, 0, 0, 0, 0);
				//$(this).after("<div style='' class='event_details'><h4>Edit Details</h4></div>");
				events.container[div_id] = $(this);
				toggleFocus(div_id);
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
			var updateXML = true;
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
			ajax_get_programs('load');


			$('.edit_program').each(function buttonclicked(rowInd) {
				var myDate=new Date();
				var div_id = myDate.getTime();
				events.container[div_id] = this;
				events.program_id[div_id] = $(this).attr('id');
				events.event_id[div_id] = $(this).attr('event_id');
				createEvent(div_id, 0, 0, 0, 0);
				bindEvents(div_id);
			});

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
		mod_date_class: 'date_changer' ,
		schedule_name: getURLVar('schedule_name')  
    };


});
