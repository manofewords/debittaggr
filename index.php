<?php 
	session_start();
	require_once("DebitTaggr.class.php");
	$debitTaggr = new DebitTaggr();
	
	$debitTaggr->login("TBD", "TBD"); // TODO : REMOVE THIS!
?>
<html>
	<head>
		<meta http-equiv="Content-Language" content="en-us"/>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		<meta name="author" content="Manuel Hitz"/>
		<meta name="copyright" content="All Copyright Manuel Hitz"/>
		<title>DebitTaggr</title>

		<!-- YUI -->
		<link type="text/css" rel="stylesheet" href="yui/build/fonts/fonts.css"/>
		<link type="text/css" rel="stylesheet" href="yui/build/reset/reset.css"/>
		<link type="text/css" rel="stylesheet" href="yui/build/calendar/assets/calendar.css"/>
		<script type="text/javascript" src="yui/build/yahoo/yahoo.js"></script>
		<script type="text/javascript" src="yui/build/event/event.js" ></script>
		<script type="text/javascript" src="yui/build/dom/dom.js" ></script>
		<script type="text/javascript" src="yui/build/yahoo-dom-event/yahoo-dom-event.js"></script>
		<script type="text/javascript" src="yui/build/autocomplete/autocomplete-min.js"></script>
		<script type="text/javascript" src="yui/build/dragdrop/dragdrop-min.js" ></script>
		<script type="text/javascript" src="yui/build/animation/animation-min.js"></script>
		<script type="text/javascript" src="yui/build/connection/connection-min.js"></script>
		<script type="text/javascript" src="yui/build/calendar/calendar.js"></script>
		
		<!-- Slideshow
		<script type="text/javascript" src="yui-slideshow-0.3/slideshow.js"></script>
		<link type="text/css" rel="stylesheet"  href="yui-slideshow-0.3/slideshow.css"/>
		 -->
		<script>
			YAHOO.namespace("example.calendar");
			
			var getDebitCallback =
			{
				success: function(o) {			  	
					document.getElementById("debit_splits").innerHTML = '';
					document.getElementById("nb_splits").value = 0;

					var root = o.responseXML.documentElement; // debits
					var debits = root.getElementsByTagName('debit'); // all debit nodes
					for(var i=0; i<debits.length; i++) {
						var id = (debits[i].getElementsByTagName('id')[0].firstChild != undefined?debits[i].getElementsByTagName('id')[0].firstChild.nodeValue:'');
						var amount = (debits[i].getElementsByTagName('amount')[0].firstChild != undefined?debits[i].getElementsByTagName('amount')[0].firstChild.nodeValue:'');
						var currency = (debits[i].getElementsByTagName('currency')[0].firstChild != undefined?debits[i].getElementsByTagName('currency')[0].firstChild.nodeValue:'');
						var description = (debits[i].getElementsByTagName('description')[0].firstChild != undefined?debits[i].getElementsByTagName('description')[0].firstChild.nodeValue:'');
						var date = (debits[i].getElementsByTagName('date')[0].firstChild != undefined?debits[i].getElementsByTagName('date')[0].firstChild.nodeValue:'');

						document.getElementById('debit_id').value = id;
						document.getElementById('debit_amount').value = amount;
						document.getElementById('debit_currency').innerHTML = currency;
						document.getElementById('debit_description').innerHTML = description;
						document.getElementById('debit_date').innerHTML = date;
			
						var splits = debits[i].getElementsByTagName('split');
						for(var j=0; j<splits.length; j++) {
							var split_id = (splits[j].getElementsByTagName('id')[0].firstChild != undefined?splits[j].getElementsByTagName('id')[0].firstChild.nodeValue:'');
							var split_notes = (splits[j].getElementsByTagName('notes')[0].firstChild != undefined?splits[j].getElementsByTagName('notes')[0].firstChild.nodeValue:'');
							var split_tags = (splits[j].getElementsByTagName('tags')[0].firstChild != undefined?splits[j].getElementsByTagName('tags')[0].firstChild.nodeValue:'');
							var split_amount = (splits[j].getElementsByTagName('amount')[0].firstChild != undefined?splits[j].getElementsByTagName('amount')[0].firstChild.nodeValue:'');
						
							createSplit(j, split_id, split_amount, split_notes, split_tags);
						}
/*						
						// update calendar
						var day = date.substr(6,2);
						var month = date.substr(4,2);
						var year = date.substr(0,4);
						date = month + "/" + day + "/" + year;
						YAHOO.example.calendar.cal1.deselectAll();
						YAHOO.example.calendar.cal1.select(date);
						YAHOO.example.calendar.cal1.cfg.setProperty("pagedate", month + "/" + year);
						YAHOO.example.calendar.cal1.render();
*/					}
					document.getElementById('message').innerHTML = '';
				},
			  	failure: function(o) {return;}
			}
			function handleSelect(type,args,obj) {
				var arrDates = YAHOO.example.calendar.cal1.getSelectedDates();
				if(arrDates.length > 0) {
					var date = arrDates[0];

					var selMonthFrom = document.getElementById("selMonthFrom");
					var selDayFrom = document.getElementById("selDayFrom");
					var selYearFrom = document.getElementById("selYearFrom");

					selMonthFrom.selectedIndex = date.getMonth()+1;
					selDayFrom.selectedIndex = date.getDate();

					for (var y=0;y<selYearFrom.options.length;y++) {
						if (selYearFrom.options[y].text == date.getFullYear()) {
							selYearFrom.selectedIndex = y;
							break;
						}
					}
					
					// get the debit element via ajax
					var postString = 'selDayFrom='+date.getDate()+'&selMonthFrom='+(date.getMonth()+1)+'&selYearFrom='+date.getFullYear()+'&a=3';
					var cObj = YAHOO.util.Connect.asyncRequest('POST', '<?php echo CONTROLLER_URL; ?>', getDebitCallback, postString);
				}
				else // no date selected
				{
						var selMonthFrom = document.getElementById("selMonthFrom");
						var selDayFrom = document.getElementById("selDayFrom");
						var selYearFrom = document.getElementById("selYearFrom");

						selMonthFrom.selectedIndex = 0;
						selDayFrom.selectedIndex = 0;
						selYearFrom.selectedIndex = 0;

						var selMonthTo = document.getElementById("selMonthTo");
						var selDayTo = document.getElementById("selDayTo");
						var selYearTo = document.getElementById("selYearTo");

						selMonthTo.selectedIndex = 0;
						selDayTo.selectedIndex = 0;
						selYearTo.selectedIndex = 0;
				}
				if(arrDates.length > 1) {
					var date = arrDates[arrDates.length - 1];

					var selMonthTo = document.getElementById("selMonthTo");
					var selDayTo = document.getElementById("selDayTo");
					var selYearTo = document.getElementById("selYearTo");

					selMonthTo.selectedIndex = date.getMonth()+1;
					selDayTo.selectedIndex = date.getDate();

					for (var y=0;y<selYearTo.options.length;y++) {
						if (selYearTo.options[y].text == date.getFullYear()) {
							selYearTo.selectedIndex = y;
							break;
						}
					}
				}
				else // only 1 date selected
				{
						var selMonthTo = document.getElementById("selMonthTo");
						var selDayTo = document.getElementById("selDayTo");
						var selYearTo = document.getElementById("selYearTo");

						selMonthTo.selectedIndex = 0;
						selDayTo.selectedIndex = 0;
						selYearTo.selectedIndex = 0;
				}
			}
			function updateCalendar() {
				var selMonthFrom = document.getElementById("selMonthFrom");
				var selDayFrom = document.getElementById("selDayFrom");
				var selYearFrom = document.getElementById("selYearFrom");

				var monthFrom = parseInt(selMonthFrom.options[selMonthFrom.selectedIndex].text);
				var dayFrom = parseInt(selDayFrom.options[selDayFrom.selectedIndex].value);
				var yearFrom = parseInt(selYearFrom.options[selYearFrom.selectedIndex].value);

				var selMonthTo = document.getElementById("selMonthTo");
				var selDayTo = document.getElementById("selDayTo");
				var selYearTo = document.getElementById("selYearTo");

				var monthTo = parseInt(selMonthTo.options[selMonthTo.selectedIndex].text);
				var dayTo = parseInt(selDayTo.options[selDayTo.selectedIndex].value);
				var yearTo = parseInt(selYearTo.options[selYearTo.selectedIndex].value);

				if ((! isNaN(monthFrom) && ! isNaN(dayFrom) && ! isNaN(yearFrom))
					&& (! isNaN(monthTo) && ! isNaN(dayTo) && ! isNaN(yearTo))) {
					var dateFrom = monthFrom + "/" + dayFrom + "/" + yearFrom;
					var dateTo = monthTo + "/" + dayTo + "/" + yearTo;

					YAHOO.example.calendar.cal1.deselectAll();
					YAHOO.example.calendar.cal1.select(dateFrom);
					YAHOO.example.calendar.cal1.select(dateTo);
					YAHOO.example.calendar.cal1.cfg.setProperty("pagedate", monthFrom + "/" + yearFrom);
					YAHOO.example.calendar.cal1.render();
				}
			}
			function initCalendar() {
				YAHOO.example.calendar.cal1 = new YAHOO.widget.Calendar("cal1","calendar", {mindate:"1/1/2004", maxdate:"12/31/2020", MULTI_SELECT: true});
				YAHOO.example.calendar.cal1.selectEvent.subscribe(handleSelect, YAHOO.example.calendar.cal1, true);
				YAHOO.example.calendar.cal1.deselectEvent.subscribe(handleSelect, YAHOO.example.calendar.cal1, true);
				YAHOO.example.calendar.cal1.render();

				YAHOO.util.Event.addListener(["selMonthFrom","selDayFrom","selYearFrom"], "change", updateCalendar);
				YAHOO.util.Event.addListener(["selMonthTo","selDayTo","selYearTo"], "change", updateCalendar);
			}
			
			var names = new Array(); // global var... bad!
			
			var initTagsCallback = {
				success: function(o) {
					//var names = new Array();
					var counts = new Array();
					
					// extract tags
					var root = o.responseXML.documentElement; // tags
					var tags = root.getElementsByTagName('tag'); // all tag nodes
					for(var i=0; i<tags.length; i++) {
						names[i] = (tags[i].getElementsByTagName('name')[0].firstChild != undefined?tags[i].getElementsByTagName('name')[0].firstChild.nodeValue:'');
						counts[i] = (tags[i].getElementsByTagName('count')[0].firstChild != undefined?tags[i].getElementsByTagName('count')[0].firstChild.nodeValue:'');
					}

					// create auto-complete widgets				
					var oACDS = new YAHOO.widget.DS_JSArray(names);

					var oAutoComp2 = new YAHOO.widget.AutoComplete('stats_tags','stats_tags_autocomplete', oACDS);
					oAutoComp2.queryDelay = 0;
					oAutoComp2.prehighlightClassName = "yui-ac-prehighlight";
					oAutoComp2.useShadow = false;
					oAutoComp2.typeAhead = true;
					oAutoComp2.delimChar = " ";
					oAutoComp2.textboxKeyEvent.subscribe(function(){oAutoComp2.sendQuery("");});
				
					// create tags cloud
					var html = "<h1>TAGS</h1>";
					var count = 0;
					for(var i=0; i<names.length; i++) {	
						count = counts[i];
						html += "<span id=\"draggable"+i+"\" class=\"";
						if(count < <?php echo TAG_THRESHOLD_XS; ?>) html += "tagXS";
						else if(count < <?php echo TAG_THRESHOLD_S; ?>) html += "tagS";
						else if(count < <?php echo TAG_THRESHOLD_M; ?>) html += "tagM";
						else if(count < <?php echo TAG_THRESHOLD_L; ?>) html += "tagL";
						else html += "tagXL";
						html += "\" title=\""+count+" debits\">"+names[i]+"</span> ";
					}
					document.getElementById('tagsCloud').innerHTML = html;				

					// create drag'n'drop elements
					var ddTarget1 = new YAHOO.util.DDTarget("stats_tags");
					var ddTarget2 = new YAHOO.util.DDTarget("debit_tags");

					var dynamicJS; // crappy... using eval is not good for perf!
					for(var i=0; i<names.length; i++) {
						dynamicJS  = "var dd"+i+" = new YAHOO.util.DD(\"draggable"+i+"\");";
						dynamicJS += "dd"+i+".onDragDrop = function(e, id) {";
						dynamicJS += "	document.getElementById(id).value += \" \"+\""+names[i]+"\";";
						dynamicJS += "};";
						dynamicJS += "dd"+i+".onMouseUp = function(e) {";
						dynamicJS += "	var attributes = {";
						dynamicJS += "		points: {";
						dynamicJS += "			to: [dd"+i+".startPageX, dd"+i+".startPageY]";
						dynamicJS += "		}";
						dynamicJS += "	};";
						dynamicJS += "	var anim = new YAHOO.util.Motion(dd"+i+".getEl(), attributes, 1, YAHOO.util.Easing.bounceOut);";
						dynamicJS += "	anim.animate();";
						dynamicJS += "};";
						eval(dynamicJS);
					}
				},
				failure: function(o) {return;}
			}
			function initTags() {
				var postString = 'a=6';
				var cObj = YAHOO.util.Connect.asyncRequest('POST', '<?php echo CONTROLLER_URL; ?>', initTagsCallback, postString);
			}

			var getStatsCallback = {
				success: function(o) {alert(o.responseText)},
				failure: function(o) {return;}
			}		
			function getStats() {
				var formObject = document.getElementById('statsForm');
				YAHOO.util.Connect.setForm(formObject);
				var cObj = YAHOO.util.Connect.asyncRequest('POST', '<?php echo CONTROLLER_URL; ?>', getStatsCallback);
			}

			var updateDebitCallback = {
				success: function(o) {
					document.getElementById('message').innerHTML = '<span style="color:red">Saved.</span>';
					initTags();
				},
				failure: function(o) {return;}
			}
			function updateDebit() {
				var formObject = document.getElementById('debitForm');
				YAHOO.util.Connect.setForm(formObject);
				var cObj = YAHOO.util.Connect.asyncRequest('POST', '<?php echo CONTROLLER_URL; ?>', updateDebitCallback);
			}
			
			function getPreviousDebit(e) {
				var formObject = document.getElementById('debitForm');
				var postString = 'id='+formObject.elements[1].value+'&a=4';
				var cObj = YAHOO.util.Connect.asyncRequest('POST', '<?php echo CONTROLLER_URL; ?>', getDebitCallback, postString);
			}
			function getNextDebit(e) {
				var formObject = document.getElementById('debitForm');
				var postString = 'id='+formObject.elements[1].value+'&a=5';
				var cObj = YAHOO.util.Connect.asyncRequest('POST', '<?php echo CONTROLLER_URL; ?>', getDebitCallback, postString);
			}
			function splitDebit() {
				createSplit(Number(document.getElementById('nb_splits').value));
			}
			function createSplit(i, id, amount, notes, tags) {
				var div = document.getElementById("debit_splits");
				var table = document.createElement("table");
				
				var html = '	<tr>';
				html += '		<td class="label">AMOUNT SPLIT '+(i+1)+' :</td>';
				html += '		<td>';
				html += '			<input id="split_amount_'+i+'" type="text" name="split_amount[]" value="'+(typeof(amount)!='undefined'?amount:document.getElementById('debit_amount').value)+'"/>';
				html += '			<input id="split_id_'+i+'" type="hidden" name="split_id[]" value="'+(typeof(id)!='undefined'?id:'')+'"/>';
				html += '		</td>';
				html += '	</tr>';
				html += '	<tr>';
				html += '		<td class="label">NOTES SPLIT '+(i+1)+' :</td>';
				html += '		<td>';
				html += '			<textarea id="split_notes_'+i+'" name="split_notes[]">'+(typeof(notes)!='undefined'?notes:'')+'</textarea>';
				html += '		</td>';
				html += '	</tr>';
				html += '	<tr>';
				html += '		<td class="label">TAGS SPLIT '+(i+1)+' :</td>';
				html += '		<td>';
				html += '			<input id="split_tags_'+i+'" type="text" name="split_tags[]" value="'+(typeof(tags)!='undefined'?tags:'')+'"/>';
				html += '			<div id="split_tags_autocomplete_'+i+'" class="debit_tags_autocomplete"></div>';
				html += '		</td>';
				html += '	</tr>';
				
				table.innerHTML = html;
				div.appendChild(table);
				
				document.getElementById('nb_splits').value = Number(document.getElementById('nb_splits').value)+1;
				
				// create auto-complete widgets				
				var oACDS = new YAHOO.widget.DS_JSArray(names); // names is a global var... eeew!

				var oAutoComp = new YAHOO.widget.AutoComplete('split_tags_'+i, 'split_tags_autocomplete_'+i, oACDS);
				oAutoComp.queryDelay = 0;
				oAutoComp.prehighlightClassName = "yui-ac-prehighlight";
				oAutoComp.useShadow = false;
				oAutoComp.typeAhead = true;
				oAutoComp.delimChar = " ";
				oAutoComp.textboxKeyEvent.subscribe(function(){oAutoComp.sendQuery("");});
			}
			function init() {
				initCalendar();
				initTags();

				var oElement = document.getElementById("previousDebitElement");
				YAHOO.util.Event.addListener(oElement, "click", getPreviousDebit);
				
				oElement = document.getElementById("nextDebitElement");
				YAHOO.util.Event.addListener(oElement, "click", getNextDebit);
			}
			YAHOO.util.Event.addListener(window, "load", init);
		</script>
		<link type="text/css" rel="stylesheet" href="style.css" />
	</head>
	<body>
		<div id="pageContainer">
			<div id="calendar"></div>
			<div id="statistics">
				<h1>STATISTICS</h1>
				<form name="statsForm" id="statsForm" action="javascript:getStats();">
					<input type="hidden" name="a" value="1"/>
					<table cellspacing="0" cellpadding="0" border="0">
						<tr>
							<td class="label">DATE FROM :</td>
							<td>
								<select name="selDayFrom" id="selDayFrom">
									<option value="" selected> </option>
<?php 
									for($i = 1; $i < 32; $i++)
									{
										echo '<option value="'.$i.'">'.$i.'</option>'."\n";
									}
?>
								</select>
								<select id="selMonthFrom" name="selMonthFrom">
									<option value="" selected> </option>
									<option value="Jan">1</option>
									<option value="Feb">2</option>
									<option value="Mar">3</option>
									<option value="Apr">4</option>
									<option value="May">5</option>
									<option value="Jun">6</option>
									<option value="Jul">7</option>
									<option value="Aug">8</option>
									<option value="Sep">9</option>
									<option value="Oct">10</option>
									<option value="Nov">11</option>
									<option value="Dec">12</option>
								</select>
								<select name="selYearFrom" id="selYearFrom">
									<option value="" selected> </option>
<?php 
									for($i = 2004; $i < 2021; $i++)
									{
										echo '<option value="'.$i.'">'.$i.'</option>'."\n";
									}
?>
								</select>
							</td>
						</tr>
						<tr>
							<td class="label">DATE TO :</td>
							<td>
								<select name="selDayTo" id="selDayTo">
									<option value="" selected> </option>
<?php 
									for($i = 1; $i < 32; $i++)
									{
										echo '<option value="'.$i.'">'.$i.'</option>'."\n";
									}
?>
								</select>
								<select id="selMonthTo" name="selMonthTo">
									<option value="" selected> </option>
									<option value="Jan">1</option>
									<option value="Feb">2</option>
									<option value="Mar">3</option>
									<option value="Apr">4</option>
									<option value="May">5</option>
									<option value="Jun">6</option>
									<option value="Jul">7</option>
									<option value="Aug">8</option>
									<option value="Sep">9</option>
									<option value="Oct">10</option>
									<option value="Nov">11</option>
									<option value="Dec">12</option>
								</select>
								<select name="selYearTo" id="selYearTo">
									<option value="" selected> </option>
<?php 
									for($i = 2004; $i < 2021; $i++)
									{
										echo '<option value="'.$i.'">'.$i.'</option>'."\n";
									}
?>
								</select>
							</td>
						</tr>
						<tr>
							<td class="label">TAGS :</td>
							<td>
								<input id="stats_tags" type="text" name="stats_tags" value=""/>
								<div id="stats_tags_autocomplete"></div>
							</td>
						</tr>
						<tr>
							<td colspan="2" style="text-align:center">
								<input type="submit" name="submit" value="SHOW"/>
								<input type="reset" name="reset" value="CLEAR"/>
							</td>
						</tr>
					</table>
				</form>
			</div>
			<div id="debitElement">
				<table cellspacing="0" cellpadding="0" border="0" width="100%"><?php // a table just for vertical alignment... i'm loving browsers... ?>
					<tr>
						<td id="previousDebitElement" valign="center">&laquo;</td>
						<td id="currentDebitElement">
							<form name="debitForm" id="debitForm" action="javascript:updateDebit();">
								<div id="message"></div>
								<input type="hidden" name="a" value="2"/>
								<input type="hidden" name="debit_id" id="debit_id" value=""/>
								<input type="hidden" name="nb_splits" id="nb_splits" value="0"/>
								<table id="debit_details" cellspacing="0" cellpadding="0" border="0">
									<tr>
										<td class="label">TOTAL AMOUNT :</td>
										<td>
											<input type="text" name="debit_amount" id="debit_amount" readonly="readonly" value=""/>
											<span id="debit_currency"></span>
										</td>
									</tr>
									<tr>
										<td class="label">DATE :</td>
										<td id="debit_date"></td>
									</tr>
									<tr>
										<td class="label">DESCRIPTION :</td>
										<td id="debit_description"></td>
									</tr>
								</table>
								<div id="debit_splits"></div>
								<table cellspacing="0" cellpadding="0" border="0">
									<tr>
										<td style="text-align:center">
											<input type="button" name="splie" value="SPLIT" onclick="javascript:splitDebit();"/>
											<input type="submit" name="submit" value="SAVE"/>
										</td>
									</tr>
								</table>
							</form>
						</td>
						<td id="nextDebitElement" valign="center">&raquo;</td>
					</tr>
				</table>
			</div>
			<div id="tagsCloud"></div>
		</div>
	</body>
</html>