/**
 * Base fuctionality for the RHEM Web Tool. 
 *
 * Functionalty is ordered as follows:
 * 1) Panels initialization
 * 2) Scenario functions
 * 3) Climate Station functions (This section can be found in map_func.js)
 * 4) Soil functions
 * 5) Slope functions
 * 6) Vegetation Cover functions
 * 7) Miscellaneous functions
*
* @class drhem.js
* @autor Gerardo Armendariz
*/

var APPROOT = "https://apps.tucson.ars.ag.gov/rhem/";
var TIMER;

// attach events as soon as the document is ready
$(document).ready(function(e) {
	attachPopupEvents();
	attachUIElementEvents();
	// TODO: Add aphanumeric checks 
	$('.alphanumericCheck2').alphanumeric({allow:".-_, "});
});

// initialization when the document finishes loading
$(window).load(function () {
    // confirm before closing window to prevent scenario data loss
    preventWindowClose();
    // init map used to select precip stationteamviewer
    
    initializeMap();
    // load the pannel interactivity
	loadPanels();
	// when opening an existing scenario, load the selected climate station and load the scenario output table
	if($($("[id=stateid]")[0]).val() != '' || $($("[id=climatestationid]")[1]).val() != ''){
		// only update the climate stations for national stations
		if($($("[id=stateid]")[0]).val() != "ITL"){

			// if a national station will be selected, use a delay to make sure that the states KMZ layer is loaded first
			$("#stateid").delay(100).queue(function() {
				update_climatestations_by_state();
			});
		}
		printScenarioResultsTable();
		
		checkAndResetScenarioSlopeLength();
	 }
});

// Prevents scenario data loss do the the current window being closed or a page refresh
function preventWindowClose(){
 	var needToConfirm = true;
 	//window.onbeforeunload = function() {
	//if(needToConfirm)
	//	return 'You have unsaved work. Would you really like to close the window?';
	//}
}

/**
* Attach event handlers to popups that will be use to provide help 
* feedback to user.  
*
* @method attachPopupEvents Attaches mouse over and out events to popups
*                           based on the hoverIntent plugin
* @return {void}
*/
function attachPopupEvents(){
    $('*[data-poload]').hoverIntent({
		interval:350,
		timeout:500,
		over:function(){
			var e=$(this);
			var placement = 'right';
			if(e.attr('class') == 'help_lnk'){
				$.post(e.data('poload'),function(d){
					e.popover({content: d, html:true}).popover('show');
				});
			}
			else if(e.attr('class') == 'help_lnk left'){
				placement = 'left';
				$.post(e.data('poload'),function(d){
					e.popover({content: d, placement: placement,html:true}).popover('show');
				});
			}
			else if(e.attr('class') == 'help_lnk left wide'){
				placement = 'left';
				template= '<div class="popover popover-medium" style="width:950px;"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>';
				$.post(e.data('poload'),function(d){
					e.popover({content: d, placement: placement,html:true, template:template}).popover('show');
				});
			}
			
    	},
		out:function(){
			var e=$(this);
			e.popover('hide'); 
		}
	});
}

/**
* Attach event handlers
*
* @method attachElementEvents
* @return {void}
*/
function attachUIElementEvents(){
	$("#clearContent").click(clearScenario);
	$("input[name=units]").click(setUnits);
	$("#showScenarios").click(printUserScenarios);
	$("#stateid").change(update_climatestations_and_show_map);
	$("#climatestationid").change(selectStationInMap);
	$($("[id=climatestationid]")[1]).change(selectInternationalStationInMap);
	$("#showClimateStation").click(showSelectedClimatesStation); //showMap
	$("#showInternationalClimateStations").click(showInternationalClimateStations); //show international climate stations
	$("#showSlopeChart").click(refreshSlopeChart);
	$("#printCompareTable").click(printUserScenariosToCompare);
	$("#runScenario").click(runScenario); 
	$("#printRiskAssessmentTableBtn").click(printUserScenariosForRiskAssessment);

	$('.editSoilFileDialog').openDOMWindow({ 
						height:500, 
						width:500, 
						positionType:'centered',
						eventType:'click', 
						loader:1, 
						loaderHeight:16, 
						loaderWidth:17, 
						overlayColor:'#FFFFFF',
						overlayOpacity:'65',
						borderColor:'#D4812F',
						borderSize:8, 
						windowSource:'ajax', 
						windowHTTPType:'post',
						draggable:0 
						});
}

/**
* Load the interaction for the scenario tables. 
*  This is linked to the "Mnaage User Scenarios" functionality
*
* @method loadScenariosTableInteraction 
* @return {void}
*/
function loadScenariosTableInteraction(){
	// table row highlights
	enableTableRowHighlights();

	// set the table sorter for the table
	$("#scenariosTable").tablesorter(); 
	
	// allow the scenario names to be editable. allow user to rename scenario
	$('span.editable').editable(function(value, settings) {
		// get the scenario id from the url in the button used to open the scenario 
		var jo = jQuery(this);
		original_name = this.revert;
		scenario_id = jo.parent().prev().children(".btn").attr("onclick").split("/")[6].replace("'","");

		// send the new name to the server in order to rename the scenario
	    $.post("tool/renameScenario/" + value + "/" + scenario_id, function(response){
			if(response.length > 0) {
				$.prompt("This scenario name already exists. Please use a different name.",{ buttons: { Ok: true } , overlayspeed:'fast' });
				// use the old value
				jo.html(original_name);
			}
		});
		return(value);
	  }, {
	  	 tooltip : 'Click to edit...',
	     type    : 'text',
	     submit  : 'Save',
	     cancel    : 'Cancel'
	 });
}

/**
* Load the interaction for the comparison scenario tables
*
* @method loadComparisonTableInteraction 
* @return {void}
*/
function loadComparisonTableInteraction(){
	// table row highlights
	enableTableRowHighlights();

	// Define an index to be used to keep track of the order in which the user selected the scenarios
	var orderIndex = 0;

	// toggle the checkbox based on wheter the user 
	$("td:last-child").click(function(){
		if($(this).find('input[type=checkbox]').prop('checked')){
			$(this).find('input[type=checkbox]').prop('checked', false);

			// remove the order attribute from the checkbox deselected
			removedOrderIndex = $(this).find('input[type=checkbox]').attr('order');
			//console.log("Removing index " + removedOrderIndex);
			$(this).find('input[type=checkbox]').removeAttr("order");
			$(this).children("span").remove();
			orderIndex--;

			// if removing first order item 
			if(removedOrderIndex == 0){
				$(this).parent().parent().find("td:last-child").find('input[type=checkbox]').each(function(index, element){
					if($(element).attr('order')){
						currOrder = parseInt($(element).attr('order'));
						$(element).attr('order', currOrder - 1);
						$(element).siblings("span").text(currOrder);
					}
					//console.log($(element).attr('order'));
				})
			}
			else if(removedOrderIndex > 0 && removedOrderIndex < orderIndex){
				$(this).parent().parent().find("td:last-child").find('input[type=checkbox]').each(function(index, element){
					var currentCBorder = parseInt($(element).attr('order'));
					if(currentCBorder > removedOrderIndex){
						$(element).attr('order', currentCBorder - 1);
						$(element).siblings("span").text(currentCBorder);
					}

					if(parseInt($(element).attr('order')) >= 0){
						console.log($(this).parent().siblings(".table-column-text-overflow").html() + "      " + $(element).attr('order'));
					}
				})
			}
		}
		else{
			console.log("Else");
			$(this).find('input[type=checkbox]').prop('checked', true);
			// add an order attribute to the selected checkbox
			$(this).find('input[type=checkbox]').attr('order', orderIndex);
			$(this).find('input[type=checkbox]').after(function() {
				return '<span class="order_index_text">' + (orderIndex + 1) + '</span>';
			  });
			orderIndex++;
		}
	});
	$("td:last-child input[type=checkbox]").click(function(){
		if($(this).prop('checked'))
			$(this).prop('checked', false);
		else
			$(this).prop('checked', true);
	});

	// set the table sorter for the table
	$("#scenariosTable").tablesorter(); 

	// attach event to the compare scenarios button
	$("#runCompareScenarios").click(runCompareScenarios);
}

/**
* Highlight table rows on hover
*
* @method enableTableRowHighlights
* @return {void}
*/
function enableTableRowHighlights(){
	// set the on hover style when the table loads
	$("tr").not(':first').hover(
	  function () {
	    $(this).css("background","#F7E4D2");
	  }, 
	  function () {
	    $(this).css("background","");
	  }
	);
}

/**
* Load all panel events
*
* @method loadPanels
* @return {void}
*/
function loadPanels() {
	// load accordion effects for left panel
	if(!$('#left_panel> div:first')){
		$('#left_panel> div').hide(); 
		$('#left_panel> h2').addClass("toggle_off");
	}
	// add functionality to all but scenario name panel
	$('#left_panel> h2:gt(0):lt(4)').click(function() {
		if($(this).hasClass("toggle_off")){
			$(this).addClass("toggle_on");
			$(this).removeClass("toggle_off");
		}
		else if($(this).hasClass("toggle_on")){
			$(this).addClass("toggle_off");
			$(this).removeClass("toggle_on");
		}
		$(this).siblings(":gt(0):visible").removeClass("toggle_on");
		$(this).siblings(":gt(0):visible").addClass("toggle_off");
		$(this).next('div').slideToggle('fast').siblings('div.panel:visible').slideUp('fast'); 
	});
	// set the units based on the ones saved for the current scenario
	// TODO: Delete if not needed
	setUnitLabels();
}

////////////////////////////////////////////////////////////////
//////////////////// SCNEARIOS FUNCTIONS ///////////////////////
////////////////////////////////////////////////////////////////
/**
* Deletes the selected scenarios
*
* @method deleteScenarios
* @return {void}
*/
function deleteScenarios(){
	if($("#userScenariosTableContainer input:checked").length > 0){
		scenariosToDelete = "{";
		$("#userScenariosTableContainer input:checked").each(function(i){
			scenariosToDelete += '"' +  i + '":' + this.value;
			if($("#userScenariosTableContainer input:checked").length - 1 > i)
				scenariosToDelete += ",";
		});
		scenariosToDelete += "}";
		// show progress bar and disable delete button
		$('#progressBar').show(); 
		$('#scenariosDeleteButton').attr('disabled', 'disabled');
		// Delete the selected scenarios
		$.post("tool/deleteScenarios/", {scenarios: scenariosToDelete}, function(data){
				if(data.length > 0) {
					// hide progress bar and enable delete button
					$('#progressBar').hide(); 
					$('#scenariosDeleteButton').removeAttr('disabled');
					
					$('#userScenariosTableContainer').html(data);
					toggleContent("#userScenariosTableContainer");
				}
		}); 
	}
	else{
		$.prompt('Please select at least one scenario to delete.',
		{ buttons: { OK: 'OK'} , 
		  overlayspeed:'fast'		
		});
	}
}

/**
* Validate and begin the scenario run process
*
* @method runScenario
* @return {void}
*/
function runScenario(){
	// first, validate the scenario
	validationMessage = validateScenario();
	
	// if not all required parameters have been entered
	// TODO: simplify this section, try to refactor the way the validation message is displayed
	if(validationMessage != '' && validationMessage.substring(0,5) != 'NOTE:'){
		$('#progressBar').hide();
		$.prompt(validationMessage,{ buttons: { Ok: true } , overlayspeed:'fast'});
	}
	// if cover values are under 1%, ask the use if the scenario still need to be ran
	else if(validationMessage != '' && validationMessage.substring(0,5) == 'NOTE:'){
		$.prompt(validationMessage,{ buttons: { Yes: true , No: false} , overlayspeed:'fast',
			submit: function(e,v,m,f){
		  		if(v){
					checkIfScenarioExists();
				}
			}
		});
	}
	// if the form validates correctly
	else{
		checkIfScenarioExists();
	}
}

/**
* Checks if the current scenario already exists for the current user
*
* @method checkIfScenarioExists
* @return {Boolean} Return 0 if the scnario does not exists or 1 if it does
*/
function checkIfScenarioExists(){
	// check if scenario exists
	$.post("run/doesScenarioExist/" + $("#scenarioname").val() + "/" , function(existsFlag){
		//console.log("Exists? " + $("#scenarioname").val() + " Flag: " + existsFlag);
		if(existsFlag == 'session_expired')
			redirectToLogin();
		if(existsFlag == 0)
			saveScenario();
		else if(existsFlag == 1)
			updateScenario();
	});
}

/**
* User should decide if the scenario should be saved as a user scenario.
*
* @method saveScenario
* @return {void}
*/
function saveScenario(){
	$.prompt('Would you like to save the current scenario? ',
		{ buttons: { Save: 'save',  Cancel: 'cancel'} , 
		  overlayspeed:'fast', 
		  submit: function(e,v,m,f){
		  		if(v == 'cancel'){
				}
				else
					doRun(v);
		  }		
		});
}

/**
* User should decide if the scenario should be update
*
* @method saveScenario
* @return {void}
*/
function updateScenario(){
	$.prompt('This scenario already exists. Would you like to overwrite the current scenario?',
	{ buttons: { Overwrite: 'update', Cancel: 'cancel' } , 
	  overlayspeed:'fast',
	  submit: function(e,v,m,f){
			if(v == 'cancel'){
			}
			else
				doRun(v);
	  }
	});
}

/**
* After the form has been validated, run the model and present results table. 
*
* @method doRun
* @saveOrUpdate {String} This parameter defines if the scenario will be saved as a new one or udpated
* @return {void}
*/
function doRun(saveOrUpdate){
	var scenarioname = $("#scenarioname").val();
	var scenariodescription = ($("#scenariodescription").val() == ''?' ':$("#scenariodescription").val());
	var units = getUnits();
	var stateid = $("#stateid").val();
	var climatestationid = $("#climatestationid").val();

	// if the current scenario is being ran with an international station, set the values of the second state and station select objects
	if($("ul#station_tabs li.active").text() == "International"){
		stateid = $($("[id=stateid]")[1]).val();
		climatestationid = $($("[id=climatestationid]")[1]).val();
	}
	var soiltexture = $("#soiltexture").val();
	var modsoilflag = $("#modifiedPARfileflag").val();
	var slopelength = $("#slopelength").val();
	var slopeshape = $("#slopeshape").val();
	var slopesteepness = $("#slopesteepness").val();
	var bunchgrasscanopycover = $("#bunchgrasscanopycover").val();
	var forbscanopycover = $("#forbscanopycover").val();
	var shrubscanopycover = $("#shrubscanopycover").val();
	var sodgrasscanopycover = $("#sodgrasscanopycover").val();
	var basalcover = $("#basalcover").val();
	var rockcover = $("#rockcover").val();
	var littercover = $("#littercover").val();
	var cryptogamscover = $("#cryptogamscover").val();
    var sar_value = $("#sar_value").val();
	// show model running progress
	hideAllContent();
	
	if($("#editPARfilelink").length > 0){
		// send a false flag to the editsoil controller to reset the modify PAR file dialog
		var modifiedPARfileurl = $("#editPARfilelink").attr('href').replace("true","false");
		$("#editPARfilelink").attr('href', modifiedPARfileurl);
	}

	// set the timer 
	TIMER = new Date().getTime();
	
	$('#progressBar').hide();
	$.prompt('<div id="progressBarRunning"><p>Running scenario...</p></div>',
	{ buttons: {} , 
	  overlayspeed:'fast', 
	  submit: function(e,v,m,f){
	  	if(v){
				e.preventDefault();
			}
	  }		
	});

	// run scenario
	$.post("run/runScenario/" + saveOrUpdate + "/" + scenarioname + "/"  + units + "/" + stateid + "/" + climatestationid + "/" + soiltexture + "/" + 
								modsoilflag + "/" + slopelength + "/" +  slopeshape + "/" + slopesteepness + "/" + 
								bunchgrasscanopycover + "/" + forbscanopycover + "/" + shrubscanopycover + "/" + sodgrasscanopycover + "/" +
								basalcover + "/" + rockcover + "/" + littercover + "/" + cryptogamscover + "/" + sar_value, {scenarioDescription: scenariodescription}, function(scenarioRunInformation){
		if(scenarioRunInformation.length > 0) {
			// this run array has information about the current scenario by the user
			// param1: scenarioid, param2: pid, param3: userid
			var runArray = scenarioRunInformation.split("-");
			checkIfProcessFinished(runArray[0],runArray[1],runArray[2]);
		}
	});
}

/////////////////
// Checks wheater a scenario has finished running on STORM
/////////////////
function checkIfProcessFinished(scenarioidran,pid,userid) {
    function checkProcess(pid) {
    	$.ajaxSetup({ cache: false });
        $.ajax({
            url: "run/is_process_running/" + pid + "/" + scenarioidran + "/" + userid,
            method: 'GET',
            async: true,
            success: function(data) {
                if (data == "3") {
                	// wait 5 seconds before checking again for scenario run status
                	setTimeout(function(){ 
                		checkProcess(pid);
					}, 5000);
                }
                else{
                	$('#progressBarRunning').append('<p>Done running scenario.</p>');
					$('#progressBarRunning').append('<p>Saving scenario...</p>');
					$('#progressBarRunning').append('<p>Creating output table...</p>');

					saveScenarioResultsTable(scenarioidran);

					cleanScenarioOuputs(scenarioidran, userid);
					$.prompt.close();
                }
            }
        });
    }
    checkProcess(pid);
}

/////////////////
// Cleans the current run from the server
/////////////////
function cleanScenarioOuputs(scenarioidran,userid)
{
		// run scenario
	$.post("run/deleteFilesFromSTORM/" + scenarioidran + "/" + userid, function(result){
		if(result.length > 0) {
			//console.log('Deleted files...');
		}
	});
}

/////////////////
// Persist the scenario results to the DB
/////////////////
function saveScenarioResultsTable(scenarioidran)
{
	// print the scenarios table 
	$.post("results/saveSummaryOutput/" + scenarioidran, function(response){
		if(response.length > 0) {
			$('#progressBar').append('<p>Done saving scenario.</p>');
			$('#progressBar').append('<p>All done...</p>');

			// stop timer
			var end = new Date().getTime();
			var execution_time = end - TIMER;
			// print execution time when running scenario
			runTimeMessage = '';
			if((parseInt(execution_time) * 0.001) > 0)
				 runTimeMessage = "<p class='scenario_run_message'>Scenario ran in " + (parseInt(execution_time) * 0.001).toFixed(0) + " seconds.</p>"
			$('#scenarioRunContainer').html(response + runTimeMessage);
			toggleContent("#scenarioRunContainer");
			$('#progressBar').hide(); 
			$('#progressBar').html('');
		}
	}); 
}

/////////////////
// Prints the scenario inputs and outputs tables
/////////////////
function printScenarioResultsTable()
{
	// print the scenarios table 
	$.post("results/printSingleScenarioResultsTable/", function(response){
		if(response.length > 0) {
			$('#progressBar').append('<p>All done...</p>');
			// stop timer
			var end = new Date().getTime();
			var execution_time = end - TIMER;
			// print execution time when running scenario
			runTimeMessage = '';
			if((parseInt(execution_time) * 0.001) > 0)
				 runTimeMessage = "<p class='scenario_run_message'>Scenario ran in " + (parseInt(execution_time) * 0.001).toFixed(0) + " seconds.</p>"
			$('#scenarioRunContainer').html(response + runTimeMessage);
			toggleContent("#scenarioRunContainer");
			$('#progressBar').hide(); 
			$('#progressBar').html('');
		}
	}); 
}

/////////////////
//  Display all of the user scenario tables that will be us
/////////////////
function printUserScenarios()
{
	// show model running progress
	hideAllContent();
	$('#progressBar').show(); 
	
	// print scenarios table to the right panel
	$.post("tool/printUserScenariosTable/", function(data){
		if(data.length > 0) {
			if(data == 'session_expired')
				redirectToLogin();
			else{
				$('#progressBar').hide(); 
				$('#userScenariosTableContainer').html(data);
				toggleContent("#userScenariosTableContainer");
				$("#userScenariosTable").tablesorter(); 
			}
		}
	}); 
}

/////////////////
//  Display all of the user scenario tables
/////////////////
function printUserScenariosToCompare()
{
	// show model running progress
	hideAllContent();
	$('#progressBar').show();
	
	// print scenarios table
	$.post("tool/printUserScenariosTableToCompare/", function(data){
			if(data.length > 0) {
				if(data == 'session_expired')
					redirectToLogin();
				else{
					$('#progressBar').hide(); 
					$('#scenarioTableContainer').html(data);
					loadComparisonTableInteraction();
					toggleContent("#scenarioTableContainer");
				}
			}
	}); 
}

/////////////////
//  Collects the list of selected scenarios (with the same units) in order to create a comparison report
/////////////////
function runCompareScenarios()
{	
	// the user will be able to compare 5 scenarios at a time
	if($("#scenarioTable input:checked").length > 0 && $("#scenarioTable input:checked").length < 6)
	{
		// validate scenarios units before printint comparison table and charts
		if(validateScenarioUnits())
		{
			selectedScenarios = "{";
			
			var sortedCheckboxes = $("#scenarioTable input:checked").toArray().sort(sorter);
		    $.each(sortedCheckboxes, function (index, value) {
		        selectedScenarios = selectedScenarios + '"' +  index + '":' + $(value).attr("id");
		        
		        if(sortedCheckboxes.length - 1 > index)
		        	selectedScenarios = selectedScenarios + ",";
		    });

		    //console.log("selectedScenarios: " + selectedScenarios);
		    //console.log("selectedScenarios2: " + selectedScenarios2);

			/*$("#scenarioTable input:checked").each(function(i){
				selectedScenarios = selectedScenarios + '"' +  i + '":' + this.id;
				if($("#scenarioTable input:checked").length - 1 > i)
					selectedScenarios = selectedScenarios + ",";
			});*/
			
			selectedScenarios += "}";
			
			hideAllContent();
			$('#progressBar').show();
			
			// prints a comparison table for all selected scenarios
			$.post("results/printScenariosComparisonTablesandCharts/", {scenarios: selectedScenarios}, function(data){
				if(data.length > 0) {
					//console.log(selectedScenarios);
					$('#progressBar').hide(); 
					$('#resultsContainer').html(data);
					toggleContent('#resultsContainer');
				}
			});
		}
	}
	else if($("#scenarioTable input:checked").length > 5){
		$.prompt('You can only compare 5 scenarios at a time.' ,{ buttons: { Ok: true } , overlayspeed:'fast'} );
	}
	else{
		$.prompt('Please select scenarios to compare.' ,{ buttons: { Ok: true } , overlayspeed:'fast'} );
	}
}

////////////////
// Sorts a list based on the order attribute
////////////////
function sorter(a, b) {
    return a.getAttribute('order') - b.getAttribute('order');
};

/////////////////
//  Activates the result container tab functionality.
/////////////////
function activateResultsContainerTabs()
{
	$("#results_panel span.toggleButton").click(function() {
		$(this).next("div").slideToggle("fast");
		$(this).next("img").slideToggle("fast"); 
		
		if($(this).children("span").hasClass("toggleIconOn")){
			$(this).children("span").addClass("toggleIconOff");
			$(this).children("span").removeClass("toggleIconOn");
		}
		else{
			$(this).children("span").addClass("toggleIconOn");
			$(this).children("span").removeClass("toggleIconOff");
		}
	});
}

/////////////////
//  Enable the ability to download each image individually
/////////////////
function enableDowloadableImages()
{
    $('img.downloadable').click(function(){
        var $this = $(this);
        //$this.wrap('<a href="' + $this.attr('src') + '" href="#downloadImageModal" data-toggle="modal" />');
        $this.wrap('<a href="#downloadImageModal" data-toggle="modal"/>');
        
        //console.log("THIS: "  + $this.data("events"));
        // remove any images that have been added to the modal before adding a new one
        $("#downloadImageModal").find(".modal-body").children("img").remove();

        $("#downloadImageModal").find(".modal-body").append('<img src="' +  $this.attr('src')   + '"/>');
        //alert($this.attr('src') )
    });
}

/////////////////
//  Activates the result container tab functionality.
/////////////////
function styleInputParameterTable()
{
	console.log("STYLING");
	$(".s_comp_table tbody tr:nth-child(13)").css("text-transform","uppercase");
	$(".s_comp_table tbody tr:nth-child(18)").css("text-transform","uppercase");
	$(".s_comp_table tbody tr:nth-child(13)").css("background-color","#f4f6f9");
	$(".s_comp_table tbody tr:nth-child(18)").css("background-color","#f4f6f9");
	$(".s_comp_table tbody tr:nth-child(9)").css("height","50px");
	$(".s_comp_table tbody tr:nth-child(9)").css("vertical-align","bottom");
}

/////////////////
//  Prints a chart comparing the average annual outputs for each variable
//  based on the selected scenarios. 
/////////////////
function printScenarioComparisonCharts(selectedScenarios)
{
	// create a JSON representation of the selected variables
	selectedVariables = "{";
	$("#variablesTable input[type='checkbox'][checked!=0]").each(function(i){
		selectedVariables = selectedVariables + '"' +  i + '":"' + this.id + '"';
		if($("#variablesTable input[type='checkbox'][checked!=0]").length - 1 > i)
			selectedVariables = selectedVariables + ",";
 	});
	selectedVariables += "}";
		
	// request and print the chart based on a JSON scenario object
	$.post("results/printScenariosComparisonCharts/",{scenarios: selectedScenarios, variables:selectedVariables}, function(userid){
			if(userid.length > 0) {
				$("#returnPeriodChart").attr("src", APPROOT + 'temp/return_periods_graph_' + parseInt(userid) + '.png' + "?" + (new Date()).getTime());
				$('#resultsGraph').fadeIn("fast");
			}
	});
}

////////////////////////////////////////////////////////////////
///////////////////// SOILS FUNCTIONS //////////////////////////
////////////////////////////////////////////////////////////////

///////////////
// calles the function to find soil texture
///////////////
function saveModifiedSoilInputFile(){
	$('.editSoilFileDialog').closeDOMWindow({eventType:'click'});
}


////////////////////////////////////////////////////////////////
///////////////////// SLOPE FUNCTIONS //////////////////////////
////////////////////////////////////////////////////////////////

////////////////
// refreshes the slope chart based on user selection and input
////////////////
function refreshSlopeChart(){
	var units = $("#englishunits").attr('checked')?$("#englishunits").val():$("#metricunits").val();
	var slpLength = parseInt($("#slopelength").val());
	var slpShape = parseInt($("#slopeshape").val());
	var slpSteepness = parseInt($("#slopesteepness").val());
	if( units == 'metric' && (slpLength <= 0))
			$.prompt("Please enter a slope length value greater than 0 and less than or equal to 50",{ buttons: { Ok: true} , overlayspeed:'fast'});
	else if (units == 'english' && (slpLength <= 0) )
			$.prompt("Please enter a slope length value greater than 0 and less than 164.04ft",{ buttons: { Ok: true} , overlayspeed:'fast'});
	else if(slpShape == "")
		$.prompt("Please select a slope shape",{ buttons: { Ok: true} , overlayspeed:'fast'});
	else if(slpSteepness == "" || slpSteepness <= 0 || slpSteepness > 100)
		$.prompt("Please enter a slope steepness value greater than 0 and less than or equal to 100%.",{ buttons: { Ok: true} , overlayspeed:'fast'});
	else{
		renderSlopeChart(slpLength, slpShape, slpSteepness, units);
		toggleContent("#slopeChartContainer");
	}
}


function checkAndResetScenarioSlopeLength(){
	// for previously ran scnearios, check that the scenario is does not have a slope length greater than 50m
	// if it does, display a dialog window to the user letting them know that any future version of the scenario
	// ran will be using a slope lenght of 50m
	if($('input[name=units]:checked').val() == 'metric'){
		if($("#slopelength").val() != 50){
			$.prompt("<b>Note: </b>You have opened a scenario with a slope length not equal to 50 meters (164.04 ft). RHEM will default all new scenario runs to 50 meters (164.04 ft). There will no longer be an option to define a slope length for new scenarios. Contact us for questions and work-arounds.",{ buttons: { Ok: true} , overlayspeed:'fast'});
			$("#slopelength").val(50);
		}
	}
	else{
		if(Math.round($("#slopelength").val()) != 164){
			$.prompt("<b>Note: </b>You have opened a scenario with a slope length not equal to 50 meters (164.04 ft). RHEM will default all new scenario runs to 50 meters (164.04 ft). There will no longer be an option to define a slope length for new scenarios. Contact us for questions and work-arounds.",{ buttons: { Ok: true} , overlayspeed:'fast'});
			$("#slopelength").val(164.04);
		}
	}
}


//////////////////////////////////////////////////////////////
//////////////// MISCELLANEOUS FUNCTIONS /////////////////////
//////////////////////////////////////////////////////////////

////////////////
// Toggles the main panel content based on user selection
////////////////
function toggleContent(contentitem){
	$("#userScenariosTableContainer").hide();
	$("#mapContainer").hide();
	//gmap.checkResize();
	$("#slopeChartContainer").hide();
	$("#welcomeMessage").hide();
	$("#scenarioRunContainer").hide();
	$("#scenarioTableContainer").hide();
	$("#resultsContainer").hide();
	
	$(contentitem).fadeIn("slow");	
}

////////////////
// Toggles the main panel content based on user selection
// TODO: Refactor this by excluding unecessary containers.  I should be able to just
//       use one container for all of the result views. 
////////////////
function hideAllContent(){
	$("#userScenariosTableContainer").hide();
	$("#mapContainer").hide();
	$("#slopeChartContainer").hide();
	$("#welcomeMessage").hide();
	$("#scenarioRunContainer").hide();
	$("#scenarioTableContainer").hide();
	$("#resultsContainer").hide();	
}

/**
* Validate the DRHEM main form before running the current scenario
*
* @method validateScenario
* @return {void}
*/
function validateScenario(){
	var units = $("#englishunits").attr('checked')?$("#englishunits").val():$("#metricunits").val();
	
	var scenarioname = $("#scenarioname").val(); 

	// set the sate and station id based on wheather it's a national or international climate stations
	if($("ul#station_tabs li.active").text() == "National"){
		var stateid = $($("[id=stateid]")[0]).val();
		var climatestationid = $($("[id=climatestationid]")[0]).val();
	}
	else{
		var stateid = $($("[id=stateid]")[1]).val();
		var climatestationid = $($("[id=climatestationid]")[1]).val();
	}
	//var stateid = $("#stateid").val();
	//var climatestationid = $("#climatestationid").val();
    var soiltexture = $("#soiltexture").val();
    var salinesoil_flag = $("#salinity_checkbox").is(":checked");
    var salinesoil_sar = $("#sar_value").val();
	var slopelength = $("#slopelength").val();
	var slopeslopesteepness = $("#slopesteepness").val();
	var bunchgrasscanopycover = $("#bunchgrasscanopycover").val();
	var forbscanopycover = $("#forbscanopycover").val();
	var shrubscanopycover = $("#shrubscanopycover").val();
	var sodgrasscanopycover = $("#sodgrasscanopycover").val();
	var totalcanopycover = parseFloat(bunchgrasscanopycover) + parseFloat(forbscanopycover) + parseFloat(shrubscanopycover) + parseFloat(sodgrasscanopycover);
	var basalcover = $("#basalcover").val();
	var rockcover = $("#rockcover").val();
	var littercover = $("#littercover").val();
	var cryptogamscover = $("#cryptogamscover").val();
	var totalgroundcover = parseFloat(basalcover) + parseFloat(littercover) + parseFloat(rockcover) + parseFloat(cryptogamscover);
	
	var message = '';
	// check that all of the required inputs have been populated by the user
	message = (scenarioname == ''?"<li>Please add a scenario name.</li>":'');
	message += (stateid == ''?"<li>Please select a state.</li>":'');
	message += (climatestationid == ''?"<li>Please select a climate station.</li>":'');
	message += (soiltexture == ''?"<li>Please select a soil texture.</li>":'');
    
    message += ((salinesoil_flag == true && salinesoil_sar == '')?"<li>You specified that this is a saline scenario, but did not specify an SAR value.</li>":'');
    message += (!$.isNumeric(salinesoil_sar) || (salinesoil_sar < 0 || salinesoil_sar > 50) ?"<li>Please enter a numeric SAR value between 0 and 50.</li>":'');

	// check for the slope length (under 120m or 395ft)
	compareSlopeLength = 120;
	unitsForSlopelength = "m";
	if(getUnits() == "english"){
		compareSlopeLength = 394;
		unitsForSlopelength = "ft";
	}
	
	message += (!$.isNumeric(slopelength) || slopelength <= 0 || slopelength > compareSlopeLength ?"<li>Please enter a numeric slope length value greater than 0 and less than " + compareSlopeLength + unitsForSlopelength + "</li>":'');
	message += (!$.isNumeric(slopeslopesteepness) || slopeslopesteepness <= 0 ?"<li>Please enter a numeric slope steepness value greater than 0.</li>":'');
	
	message += (!$.isNumeric(bunchgrasscanopycover) || (bunchgrasscanopycover < 0 || bunchgrasscanopycover > 100)?"<li>Please specify a numeric bunch grass canopy cover value (0 to 100).</li>":'');
	message += (!$.isNumeric(forbscanopycover) || (forbscanopycover < 0 || forbscanopycover > 100)?"<li>Please specify a numeric forbs canopy cover value (0 to 100).</li>":'');
	message += (!$.isNumeric(shrubscanopycover) || (shrubscanopycover < 0 || shrubscanopycover > 100)?"<li>Please specify a numeric shrubs canopy cover value (0 to 100).</li>":'');
	message += (!$.isNumeric(sodgrasscanopycover) || (sodgrasscanopycover < 0 || sodgrasscanopycover > 100)?"<li>Please specify a numeric sod grass canopy cover value (0 to 100).</li>":'');
	
	//message += (totalcanopycover == 0?"<li>Total canopy cover percent cannot be 0.</li>":'');
	message += (totalcanopycover > 100?"<li>Total canopy cover (bunch + forbs + shrubs + sod) cannot exceed 100%.</li>":'');

	message += (!$.isNumeric(littercover) || (littercover < 0 || littercover > 100)?"<li>Please specify a numeric litter value (0 to 100).</li>":'');
	message += (!$.isNumeric(basalcover) || (basalcover < 0 || basalcover > 100)?"<li>Please specify a numeric basal cover value (0 to 100).</li>":'');
	message += (!$.isNumeric(cryptogamscover) || (cryptogamscover < 0 || cryptogamscover > 100)?"<li>Please specify a numeric cryptogamscover cover value (0 to 100).</li>":'');
	message += (!$.isNumeric(rockcover) || (rockcover < 0 || rockcover > 100)?"<li>Please specify a numeric rock cover value (0 to 100).</li>":'');
	
	
	message += ((totalgroundcover < 0 || totalgroundcover > 100)?"<li>Total ground cover (basal + rock + litter + cryptogamscover) cannot exceed 100%.</li>":'');
	
	// if no warning messages due to lack of parameters, let the user know about groundcover < 1% and > 0%
	if(message == ''){ 
		message += ( (1 > totalgroundcover && totalgroundcover > 0) ?'NOTE: You have specified a total ground cover value under 1%. Would you like to run this scenario?':'');
	}	
	return message;
}

////////////////
// Reset Scenario
////////////////
function clearScenario()
{
	$.prompt('Would you like to clear the current scenario?', 
	        { buttons: { Yes: true, No:false } , 
			  overlayspeed:'fast',
			  submit: function(e,v,m,f){
		  		if(v){
					$("#scenarioname").val('');
					$("#scenariodescription").text('');
					$("#stateid option:first").attr("selected","selected");
					$("#climatestationid option:first").attr("selected","selected");
					$("#climatestationid").html('<option value="">-----</option>');
					$("#soiltexture option:first").attr("selected","selected");
					$("#slopelength").val(50);
					$("#slopelength").attr('disabled',true);
					$("#slopeshape option:first").attr("selected","selected");
					$("#slopesteepness").val('');
					$("#bunchgrasscanopycover").val('');
					$("#forbscanopycover").val('');
					$("#shrubscanopycover").val('');
					$("#sodgrasscanopycover").val('');
					$("#basalcover").val('');
					$("#rockcover").val('');
					$("#cryptogamscover").val('');
					$("#littercover").val('');

                    $("#salinity_checkbox").prop('checked', false);
                    $("#sar_input").hide();
                    $("#sar_value").val('');
					
					// show the welcome page
					toggleContent('#welcomeMessage');
				}
			}
		} );
}

////////////////
// Set the units for the slope length based on metric or english selection
////////////////
function setUnits(evt)
{
	//console.log(evt);
	//console.log("P: " + previousTarget);
	units = "";
	$("#unitsbox input:checked").each(function(i){
		if(this.value == 'metric'){
			if($("#slopelength").val() != ""){
				var slopeLength = parseFloat($("#slopelength").val()) * 0.3048;
				if(slopeLength == 49.999392)
					slopeLength = 50;
				$("#slopelength").val(slopeLength);
			}
		}
		else{
			if($("#slopelength").val() != ""){
				var slopeLength = parseFloat($("#slopelength").val());
				$("#slopelength").val(164.04);//slopeLength * 3.28084);
			}
		}
	});
	return units;
}

////////////////
// Set the unit labels based on metric or english selection
////////////////
function setUnitLabels()
{
	units = "";
	$("#unitsbox input:checked").each(function(i){
		if(this.value == 'metric'){
			$("#lengthunits").text("Length (meters):");
			$("#soil_layer_depth").text("(top 4cm)");
		}
		else{
			$("#lengthunits").text("Length (ft):");
			$("#soil_layer_depth").text("(top 1.57in)");
		}
	});
	return units;
}


////////////////
// Return the units identifier (metric or english) set by the user
////////////////
function getUnits()
{
	units = "";
	$("#unitsbox input:checked").each(function(i){
		if(this.value == 'metric'){
			units = this.value;
		}
		else{
			units = this.value;
		}
	});
	return units;
}

////////////////
// Verifies that the same units are used when comparing scenarios
////////////////
function validateScenarioUnits()
{
	validated = true;
	unitCompare = '';
	$("#results_panel input[type=checkbox]:checked").each(function(i){
		if($(this).parent().prev().get(0).innerHTML != unitCompare && unitCompare != ''){
			$.prompt("Please make sure that all scenarios to compare are of the same units.",{ buttons: { Ok: true } , overlayspeed:'fast' });
			validated = false;
			// return false in order to stop execution of .each function
			return false;
		}
		unitCompare = $(this).parent().prev().get(0).innerHTML; 
	});
	
	return validated;
}

////////////////
// Redirects to the login page if the current session has expired
////////////////
function redirectToLogin()
{
	$.prompt('Your session has ended, you will be redirected to the login page.',{ buttons: { Ok: true} , overlayspeed:'fast',
			submit: function(e,v,m,f){
				if(v){
					window.location = APPROOT + "login";
				}
			}
	});
}

/////////////////
//  Saves the comparison report as an image and passes it on to the server so that it can be persisted to 
//  the file system
/////////////////
function saveComparisonReportAsImage()
{
	html2canvas(document.getElementById("resultsContainer")).then(function(canvas){
        	//document.body.appendChild(canvas);
        	encodedImage = canvas.toDataURL("image/png").split(",")[1];
        	console.log(encodedImage);
            // send the canvas image to the server so that it can be saved
			$.post("results/createPDFReport/",{img_val: encodedImage}, function(data){
				if(data.length > 0) {
					//$("#returnPeriodChart").attr("src", APPROOT + 'temp/return_periods_graph_' + parseInt(userid) + '.png' + "?" + (new Date()).getTime());
					//$('#resultsGraph').fadeIn("fast");
					window.open(APPROOT + 'temp/' + data);
				}
			});
	});
}

///////////////////////////////////////////// RISK ASSESSMENT FUNCTIONALITY //////////////////////////////////////

/////////////////
//  Display all of the user scenario tables that will be used to create
//  the risk assessment scenario
/////////////////
function printUserScenariosForRiskAssessment()
{
	// show model running progress
	hideAllContent();
	$('#progressBar').show();
	
	// print scenarios table
	$.post("tool/printUserScenariosTableForRiskAssessment/", function(data){
			if(data.length > 0) {
				if(data == 'session_expired')
					redirectToLogin();
				else{
					$('#progressBar').hide(); 
					$('#scenarioTableContainer').html(data);
					loadRiskAssessmentTableInteraction();
					toggleContent("#scenarioTableContainer");
				}
			}
	}); 
}

/**
* Load the interatction for the risk assessment scenario table
*
* @method loadRiskAssessmentTableInteraction 
* @return {void}
*/
function loadRiskAssessmentTableInteraction(){
	// set the on hover style when the table loads
	enableTableRowHighlights();

	// toggle the checkbox the checkboxes and create a new radio button when user click anywhee on the current td
	$("td:nth-last-child(2)").click(function(){
		if($(this).find('input[type=checkbox]').prop('checked')){
			$(this).find('input[type=checkbox]').prop('checked', false);
			$(this).parent().children().eq(8).children().remove();
		}
		else{
			$(this).find('input[type=checkbox]').prop('checked', true);
			sID = $(this).children().eq(0).attr("id");
			$(this).parent().children().eq(8).append('<input type="radio" name="baselinescneario_rdio" id="' + sID + '" title="Green selection is recommended baseline scenario based on lowest soil loss value." data-toggle="tooltip" data-placement="left"/>');
		}

		//////////////////////////////////////////////////////
		// find the checkboxes and enable the radio button for the row that has the least soil loss
		// this scneario will become the baseline scenario
		var lowestSoilLossValue = Infinity; 
		var selectedSoilLossValues = [];
		$("td:nth-last-child(2)").find('input[type=checkbox]:checked').parent().prev().map(function(){ 
			lowestSoilLossValue = Math.min(lowestSoilLossValue, parseFloat($(this).text())); 
			selectedSoilLossValues.push(parseFloat($(this).text()));
		}); 

		var indexOfLowestSoilLossValue = jQuery.inArray(lowestSoilLossValue, selectedSoilLossValues);
		
		// clear all cells first
		$("td:nth-last-child(1)").css({'background-color':'#FFFFFF'});

		$("td:nth-last-child(1)").find('input[type=radio]').eq(indexOfLowestSoilLossValue).prop('checked',true);
		$("td:nth-last-child(1)").find('input[type=radio]').eq(indexOfLowestSoilLossValue).parent().css({'background-color':'#8CC972'});
		
		//////////////////////////////////////////////////////
		// opt in tooltips
		$('[data-toggle="tooltip"]').tooltip()

	});

	//  make sure that the toggle functionality is also working when user click on the actual checkbox
	$("td:nth-last-child(2) input[type=checkbox]").click(function(){
		if($(this).prop('checked'))
			$(this).prop('checked', false);
		else
			$(this).prop('checked', true);
	});
	// set the table sorter for the table
	$("#scenariosTable").tablesorter(); 

	// attache even to the risk assemssment button
	$("#runRiskAssessment").click(runRiskAssessment);
}


/////////////////
//  Collects the selected scenarios (with the same units) and baseline scenario to run the risk assessment on the server
/////////////////
function runRiskAssessment()
{	
	// the user will be able to compare 5 scenarios at a time
	if($("#scenarioTable input[type=checkbox]:checked").length > 0 && $("#scenarioTable input[type=checkbox]:checked").length < 6)
	{
		// validate scenarios units before printint comparison table and charts
		if(validateRiskAssessmentScenario())
		{
			// create a JSON object that will hold the scnearios to be used for the risk assessment as
			// well as the baseline scenario to be used for comparison. The first scenario will be the baseline.
			selectedScenarios = "{";

			selected_baseline_id = 0;

			$("#scenarioTable input[type=radio]:checked").each(function(i){
				selectedScenarios = selectedScenarios + '"baseline":' + this.id;
				selected_baseline_id = this.id;
			});

			$("#scenarioTable input[type=checkbox]:checked").each(function(i){
				if(this.id != selected_baseline_id){
					selectedScenarios = selectedScenarios + "," + '"' +  i + '":' + this.id;
				}
				//if($("#scenarioTable input[type=checkbox]:checked").length - 1 > i)
				//	selectedScenarios = selectedScenarios + ",";
			});

			selectedScenarios += "}";
			
			//console.log(selectedScenarios);
			hideAllContent();
			$('#progressBar').show();
			
			// prints a comparison table for all selected scenarios
			$.post("runRiskAssessment/runRiskAssessmentSenario/", {scenarios: selectedScenarios}, function(data){
				if(data.length > 0) {
					$('#progressBar').hide(); 
					$('#resultsContainer').html(data);
					toggleContent('#resultsContainer');
				}
			});
		}
	}
	else if($("#scenarioTable input[type=checkbox]:checked").length > 5){
		$.prompt('You can only select 5 scenarios at a time for  the risk assessment simulation.' ,{ buttons: { Ok: true } , overlayspeed:'fast'} );
	}
	else if($("#scenarioTable input[type=checkbox]:checked").length == 0){
		$.prompt('Please select at least one scenario to run the risk assessment.' ,{ buttons: { Ok: true } , overlayspeed:'fast'} );
	}
}


////////////////
// Verify the risk assessment scenario. Make sure that user is selecting at least two scenarios and a baseline and that the
//  units for all scenario match.
////////////////
function validateRiskAssessmentScenario()
{
	validated = true;
	unitCompare = '';
	versionCompare = '2.3';
	$("#scenarioTable input[type=checkbox]:checked").each(function(i){
		if($(this).parent().prev().prev().get(0).innerHTML != unitCompare && unitCompare != ''){
			$.prompt("Please make sure that all scenarios for risk assessment are of the same units.",{ buttons: { Ok: true } , overlayspeed:'fast' });			
			validated = false;
			return false;
		}
		else if($(this).parent().prev().prev().prev().prev().prev().get(0).innerHTML != versionCompare && versionCompare != ''){
			$.prompt("To run the Risk Assessment tool, please make sure that all scenarios have been run with version 2.3 of RHEM.",{ buttons: { Ok: true } , overlayspeed:'fast' });			
			validated = false;
			return false;
		}
		//console.log("Length: " + $("#scenarioTable input[type=checkbox]:checked").length);
		else if($("#scenarioTable input[type=checkbox]:checked").length < 2){
			$.prompt("To run a risk assessment scenario you need to choose at least 2 scenarios.",{ buttons: { Ok: true } , overlayspeed:'fast' });
			validated = false;
			return false;
		}
		else if($("#scenarioTable input[type=radio]:checked").length == 0){
			$.prompt("Please choose the baseline scnenario.",{ buttons: { Ok: true } , overlayspeed:'fast' });
			validated = false;
			return false;
		}

		unitCompare = $(this).parent().prev().prev().get(0).innerHTML; 
	});
	
	return validated;
}

////////////////
//  For the scenario comparison and the the risk assessment scenario selection panels, display a floating button.  
////////////////
function enableButtonSticky(currButton){
 	var s = $("#" + currButton);
    var pos = s.position();      
    var footer = $(".footer");              
    $(window).scroll(function() {
        var windowpos = $(window).scrollTop();
        var docpos = $(document).scrollTop();
        var docheight = $(document).height();
        var windowheight = $(window).height();
        //console.log("Footer: " +  footer.position().top + "   Button Pos: " +  s.position().top + "   Distance from top:" + pos.top + "     Scroll position: " + windowpos + "      Height: " + windowheight + "    Doc height: " + docheight + "   Doc ScrollTop: " + docpos);
        if (windowpos < (docheight - windowheight - 120)) {
            s.addClass("stick");
        } 
        else if (windowpos >  (docheight - windowheight - 120) ){
            s.removeClass("stick"); 
        }
    });
}