/**
*
* Creates the report charts for the RHEM Web Tool
*
* @class charts.js
* @autor Gerardo Armendariz
* 
*/

// Load the Visualization API and the piechart package
google.load('visualization', '1.1', {'packages':['corechart','table']});

/**
* Draws the slope chart based on user's selection for steepness, lenght, shape, and units.
*
* @method renderRPCharts
* @param {Number} slpLength The slope length
* @param {Number} slpShape The slope shape identifier
* @param {Number} slpSteepness The slope steepness
* @param {String} units The units string
* @return {void}
*/
function renderSlopeChart(slpLength, slpShape, slpSteepness, units) {
	var curveArray;
	var yVal = (slpSteepness/100) * slpLength;
	xCenter = slpLength/2;
	xRight = (slpLength/4 ) * 3;
	xLeft = (slpLength/4 );

	// define the curve type
	switch(slpShape){
		case 1: // Uniform curve
		  curveArray = [['x_axis', 'y_axis'],
						[0,         yVal],
						[slpLength,        0]];
		  break;
		case 2: // Convex curve
		  curveArray = [['x_axis', 'y_axis'],
					   [0,          yVal],
					   [xLeft * 0.9,   yVal - (yVal * .05)],
					   [xCenter * 1.1,    yVal * 0.7],
					   [slpLength,  0]];
		  break;
		case 3: // Concave curve
		  curveArray = [['x_axis', 'y_axis'],
						[0,         yVal],
						[xCenter * 0.9,   yVal * 0.3],
						[xRight * 0.9,   yVal * 0.10],
						[slpLength, 0]];
		  break;
		case 4: // S-Shaped curve
		  curveArray = [['x_axis', 'y_axis'],
						[0,         yVal],
                        [xLeft * 0.6,   yVal - (yVal * .05)],
                        [xCenter/1.45,   yVal - (yVal * .25)],
						[xCenter/.85,   yVal * .25],
                        [xRight * 1.13,   yVal * 0.04],
						[slpLength, 0]];
		  break;
	}
	var units_label = 'ft';
	if(units == 'metric')
		units_label = 'm';
	
	var data = google.visualization.arrayToDataTable(curveArray);
	// these are the options for building the linechart for the slope graph
	var options = {title:'',backgroundColor:{'stroke':'#EDEDED','strokeWidth':4,'fill':'#FFFFFF'},
				   chartArea:{left:55,top:30,width:"90%",height:"80%"},
				   hAxis:{title: "Length (" + units_label + ")",baseline:slpLength, baselineColor:'#EDEDED', viewWindowMode:'maximized' },
				   vAxis:{title: "Elevation (" + units_label + ")", baseline:yVal, baselineColor:'#EDEDED', viewWindowMode:'maximized'},
				   pointSize:0, enableInteractivity:false,tooltip:{trigger:'none'},
				   width:773,height:450,legend: 'none',
				   curveType:'function',
				   colors: ['#B37435']};
	
	// first add the chart header and container
	$("#slopeChartContainer").html('<span class="toggleButton">OVERLAND FLOW ELEMENT PROFILE</span><div id="slope_chart"></div>');
	var chart = new google.visualization.LineChart($("#slope_chart")[0]);
	chart.draw(data, options);
}

/**
* Downloads an SVG version of the current chart. 
*
* @method saveSVG
* @param {String} domElement an ID to a DOM element
* @return {void}
*/
function saveSVG(domElement){
	// get the svg object 
	var svgObj = $(domElement).find("svg");

	// remove the "clip-path" attribute from the svg g object in order for the svg file to be saved correctly
	svgObj.find( "g" ).each( function() {
        if ($( this ).attr( 'clip-path' )){
            $( this ).attr( 'clip-path', '' )
        }
    });

	// get the stringified version of the svg object
	var svgHTML = svgObj.prop('outerHTML');

	//add xml declaration to the svg object
	source = '<?xml version="1.0" standalone="no"?>\r\n' + svgHTML;

	//convert svg source to URI data scheme
	var svgURL = "data:image/svg+xml;charset=utf-8,"+encodeURIComponent(source);

	// create a download link and force the file to download
	var downloadLink = document.createElement("a");
	downloadLink.href = svgURL;
	downloadLink.download = "rhem_" + domElement.replace("#","") + ".svg";
	document.body.appendChild(downloadLink);
	downloadLink.click();
	document.body.removeChild(downloadLink);
}

/**
* Downloads an JPG version of the current chart. 
*
* @method saveSVG
* @param {String} domElement an ID to a DOM element
* @return {void}
*/
function savePNG(domElement){
	//console.log($(domElement).find("svg"));
	// get the svg object 
	var svgObj = $(domElement).find("svg");
	//svgObj[0].setAttribute("viewBox", "0 0 200 100");

	// remove the "clip-path" attribute from the svg g object in order for the svg file to be saved correctly
	svgObj.find( "g" ).each( function() {
        if ($( this ).attr( 'clip-path' )){
            $( this ).attr( 'clip-path', '' )
        }
    });

    var svgData = new XMLSerializer().serializeToString( svgObj[0]);

    var canvas = document.createElement( "canvas" );

    console.log(domElement);
    if(domElement == "#ra_chart_div"){
    	canvas.width = 750;
    	canvas.height = 380;
    	console.log("Aqui 1");
    }
    else if (domElement == "#farp_chart_div"){
    	canvas.width = 745;
    	canvas.height = 365;
    	console.log("Aqui 2");
    }
    else{
		//canvas.width=795;
		//canvas.height=466;
		canvas.width = 396; 
		canvas.height = 230;
		console.log("Aqui 3");
	}
    var ctx = canvas.getContext( "2d" );

    var dataUri = '';
    try {
        dataUri = 'data:image/svg+xml;base64,' + btoa(svgData);
    } catch (ex) {
	    // For browsers that don't have a btoa() method, send the text off to a webservice for encoding
	    /* Uncomment if needed
	    $.ajax({
	        url: "http://www.mysite.com/webservice/encodeString",
	        data: { svg: svgData },
	        type: "POST",
	        async: false,
	        success: function(encodedSVG) {
	            dataUri = 'data:image/svg+xml;base64,' + encodedSVG;
	        }
	    })
	    */
    }

    var img = document.createElement( "img" );

    img.onload = function() {
        ctx.drawImage( img, 0, 0 );
        try {                   
            // Try to initiate a download of the image
            var a = document.createElement("a");
            a.download =  "rhem_" + domElement.replace("#","") + ".png";
            a.href = canvas.toDataURL("image/png");
            document.querySelector("body").appendChild(a);
            a.click();
            document.querySelector("body").removeChild(a);                  
        } catch (ex) {
            // If downloading not possible (as in IE due to canvas.toDataURL() security issue) 
            // then display image for saving via right-click
            var imgPreview = document.createElement("div");
            imgPreview.appendChild(img);
            document.querySelector("body").appendChild(imgPreview);
        }
    };
    img.src = dataUri;
}

/**
* Adds a download button to the Google Chart image to download an SVG graphic.
*
* @method addSVGDownloadButton
* @param {String} domElement an ID to a DOM element
* @return {void}
*/
function addSVGDownloadButton(domElement){
	$(domElement).hover(
		function(){
			attrID = "#" + $(this).attr("id");
			var numItems = $('.svg_download_btn').length;
			if(numItems == 0)
				$(this).append("<div class='svg_download_btn' onclick='saveSVG(\"" + attrID + "\")'><i class='icon-download-alt' style='opacity:0.7'></i>SVG</div>")
		}, 
		function(){
			//$(this).find("div:last").remove();
			$('.svg_download_btn').remove();
		}
	);
}


/**
* Adds a download button to the Google Chart image to download an SVG graphic.
*
* @method addSVGDownloadButton
* @param {String} domElement an ID to a DOM element
* @return {void}
*/
function addPNGDownloadButton(domElement){
	$(domElement).hover(
		function(){
			attrID = "#" + $(this).attr("id");
			var numItems = $('.png_download_btn').length;
			if(numItems == 0)
				$(this).append("<div class='png_download_btn' onclick='savePNG(\"" + attrID + "\")'><i class='icon-download-alt' style='opacity:0.7'></i>PNG</div>")
		}, 
		function(){
			//$(this).find("div:last").remove();
			$('.png_download_btn').remove();
		}
	);
}

// use the function suggested here to save the svg to the file system: https://stackoverflow.com/questions/38477972/javascript-save-svg-element-to-file-on-disk


/**
* Render the average results for each of the scenarios using the Google Charts API. 
* A new chart will be rendered for the four variable types of interest.
*
* @method renderAvgCharts
* @param {Array} chartDataArray An array of average results for each scenario
* @param {String} units The units that will be used to render the chart
* @return {void}
*/
function renderAvgCharts(chartDataArray,units) {
	var rainData = google.visualization.arrayToDataTable(chartDataArray[0]);
	var runoffData = google.visualization.arrayToDataTable(chartDataArray[1]);
	var soilLossData = google.visualization.arrayToDataTable(chartDataArray[2]);
	var sedYieldData = google.visualization.arrayToDataTable(chartDataArray[3]);
	var options = {title:"",backgroundColor:{stroke:'#EDEDED',strokeWidth:10,fill:'#fff'},
		titleTextStyle:{fontSize: 12},
		legend:'none',
		chartArea:{width:"80%",height:"65%", left:65},
		width:396, height:230, //width:800, height:465, //width:396, height:230,
		hAxis: {title: "Scenario",baselineColor:'#404040',textPosition:'none'},
		vAxis:{title:"", baselineColor:'#404040'},
		isStacked:true,
		enableInteractivity: false,
		colors:['#46A23C','#BFA11B','#404040','#73AEC9','#A13F3D']};
	

	if(units == "english")
		options.vAxis.title = "in/year";
	else
		options.vAxis.title = "mm/year";

	// RAIN CHART
	options.title = "Rain";
	var rain_div = document.getElementById('rain_avg_chart_div');
	var rain_chart = new google.visualization.ColumnChart(rain_div);
	// Wait for the chart to finish drawing before calling the getImageURI() method.
	//google.visualization.events.addListener(rain_chart, 'ready', function () {
	//    rain_div.innerHTML = '<img src="' + rain_chart.getImageURI() + '" class="downloadable">';
	//});
	rain_chart.draw(rainData,options);
	// add a SVG download button
	addSVGDownloadButton("#rain_avg_chart_div");
	addPNGDownloadButton("#rain_avg_chart_div");

	
	// RUNOFF CHART
	options.title = "Runoff";
	var runoff_div = document.getElementById('ro_avg_chart_div');
	var runoff_chart = new google.visualization.ColumnChart(runoff_div)
	// Wait for the chart to finish drawing before calling the getImageURI() method.
	//google.visualization.events.addListener(runoff_chart, 'ready', function () {
	//    runoff_div.innerHTML = '<img src="' + runoff_chart.getImageURI() + '" class="downloadable">';
	//});
	runoff_chart.draw(runoffData,options);
	// add a SVG download button
	addSVGDownloadButton("#ro_avg_chart_div");
	addPNGDownloadButton("#ro_avg_chart_div");
	
	if(units == "english")
		options.vAxis.title = "ton/ac/year";
	else
		options.vAxis.title = "Mg/ha/year";

	// SEDIMENT CHART
	options.title = "Sediment Yield";
	var sedyield_div = document.getElementById('sy_avg_chart_div');
	var sedyield_chart = new google.visualization.ColumnChart(sedyield_div);
	// Wait for the chart to finish drawing before calling the getImageURI() method.
	//google.visualization.events.addListener(sedyield_chart, 'ready', function () {
	//    sedyield_div.innerHTML = '<img src="' + sedyield_chart.getImageURI() + '" class="downloadable">';
	//});
	sedyield_chart.draw(sedYieldData,options);
	// add a SVG download button
	addSVGDownloadButton("#sy_avg_chart_div");
	addPNGDownloadButton("#sy_avg_chart_div");

	// SOIL LOSS CHART
	options.title = "Soil Loss";
	var soilloss_div = document.getElementById('sl_avg_chart_div');
	var soilloss_chart = new google.visualization.ColumnChart(soilloss_div);
	// Wait for the chart to finish drawing before calling the getImageURI() method.
	//google.visualization.events.addListener(soilloss_chart, 'ready', function () {
	//    soilloss_div.innerHTML = '<img src="' + soilloss_chart.getImageURI() + '" class="downloadable">';
	//});
	soilloss_chart.draw(soilLossData,options);
	// add a SVG download button
	addSVGDownloadButton("#sl_avg_chart_div");
	addPNGDownloadButton("#sl_avg_chart_div");
}

/**
* Render the return period results for each of the scenarios using the Google Charts API. 
* A new chart will be rendered for the four variable types of interest.
*
* @method renderRPCharts
* @param {Array} chartDataArray An array of return perdiod results for each scenario to compare
* @return {void}
*/
function renderRPCharts(chartDataArray,units) {
	var rainData = google.visualization.arrayToDataTable(chartDataArray[0]);
	var runoffData = google.visualization.arrayToDataTable(chartDataArray[1]);
	var soilLossData = google.visualization.arrayToDataTable(chartDataArray[2]);
	var sedYieldData = google.visualization.arrayToDataTable(chartDataArray[3]);
	var options = {title:"",'backgroundColor':{'stroke':'#EDEDED','strokeWidth':10,'fill':'#fff'},
			titleTextStyle:{fontSize: 12},
			legend:'none',
			chartArea:{width:"80%",height:"65%", left:65},
			width:396, height:230,
			hAxis: {title: "Return Frequency (years)",baselineColor:'#404040' },
			vAxis:{title:"", baselineColor:'#404040'},
			enableInteractivity: false,
			colors:['#46A23C','#BFA11B','#404040','#73AEC9','#A13F3D']};
	
	
	if(units == "english")
		options.vAxis.title = "in";
	else
		options.vAxis.title = "mm";	
	// RAIN RP CHART
	options.title = "Rain";
	var rain_rp_div = document.getElementById('rain_rp_chart_div');
	var rain_rp_chart = new google.visualization.ColumnChart(rain_rp_div);
	// Wait for the chart to finish drawing before calling the getImageURI() method.
	//google.visualization.events.addListener(rain_rp_chart, 'ready', function () {
	//    rain_rp_div.innerHTML = '<img src="' + rain_rp_chart.getImageURI() + '">';
	//});
	rain_rp_chart.draw(rainData,options);
	// add a SVG download button
	addSVGDownloadButton("#rain_rp_chart_div");
	addPNGDownloadButton("#rain_rp_chart_div");
	  
	// RUNOFF RP CHART
	options.title = "Runoff";
	var runoff_rp_div = document.getElementById('ro_rp_chart_div');
	var runoff_rp_chart = new google.visualization.ColumnChart(runoff_rp_div);
	// Wait for the chart to finish drawing before calling the getImageURI() method.
	//google.visualization.events.addListener(runoff_rp_chart, 'ready', function () {
	//    runoff_rp_div.innerHTML = '<img src="' + runoff_rp_chart.getImageURI() + '">';
	//});
	runoff_rp_chart.draw(runoffData,options);
	// add a SVG download button
	addSVGDownloadButton("#ro_rp_chart_div");
	addPNGDownloadButton("#ro_rp_chart_div");

	if(units == "english")
		options.vAxis.title = "ton/ac";
	else
		options.vAxis.title = "Mg/ha";

	// SEDIMENT YIELD RP CHART
	options.title = "Sediment Yield";
	var sedyield_rp_div = document.getElementById('sy_rp_chart_div');
	var sedyield_rp_chart = new google.visualization.ColumnChart(sedyield_rp_div);
	// Wait for the chart to finish drawing before calling the getImageURI() method.
	//google.visualization.events.addListener(sedyield_rp_chart, 'ready', function () {
	//    sedyield_rp_div.innerHTML = '<img src="' + sedyield_rp_chart.getImageURI() + '">';
	//});
	sedyield_rp_chart.draw(sedYieldData,options);
	// add a SVG download button
	addSVGDownloadButton("#sy_rp_chart_div");
	addPNGDownloadButton("#sy_rp_chart_div");

	// SOIL LOSS RP CHART
	options.title = "Soil Loss";
	var soilloss_rp_div = document.getElementById('sl_rp_chart_div');
	var soilloss_rp_chart = new google.visualization.ColumnChart(soilloss_rp_div);
	// Wait for the chart to finish drawing before calling the getImageURI() method.
	//google.visualization.events.addListener(soilloss_rp_chart, 'ready', function () {
	//    soilloss_rp_div.innerHTML = '<img src="' + soilloss_rp_chart.getImageURI() + '">';
	//});
	soilloss_rp_chart.draw(soilLossData,options);
	// add a SVG download button
	addSVGDownloadButton("#sl_rp_chart_div");
	addPNGDownloadButton("#sl_rp_chart_div");
}

//////////////////////////////////////////////
////////// RISK ASSESSMENT CHARTS ////////////

/**
* Draws the main risk assessment chart for the runRiskAssessment controller
*
* @method drawRiskAssessmentChart
* @param {Array} ra_js_array An array of risk assessment results for each scenario to compare
* @param {Array} scenarioNamesArray an array with the scenario names
* @return {void}
*/
function drawRiskAssessmentChart(ra_js_array, scenarioNamesArray) {
	var data = new google.visualization.DataTable();

	data.addColumn("string","Scenario");
	data.addColumn("number","Low");
	data.addColumn({type:"number", role:"annotation"});
	data.addColumn("number","Medium");
	data.addColumn({type:"number", role:"annotation"});
	data.addColumn("number","High");
	data.addColumn({type:"number", role:"annotation"});
	data.addColumn("number","Very High");
	data.addColumn({type:"number", role:"annotation"});
	data.addRows(ra_js_array);

	// add the name of the scenarios to the chart
	//for(var i = 0; i < scenarioNamesArray.length; i++){
		 //currentScenarioName = data.getValue(i, 0);
		 //data.setCell(i,0, currentScenarioName + " (" + scenarioNamesArray[i]) + ")";
	//}
	//var data = google.visualization.arrayToDataTable(' . $ra_js_array . ');

	var options = {
	hAxis: {title: "Scenario", titleTextStyle: {color: "black", fontSize:14}},
	vAxis: {title: "Probability", titleTextStyle: {color: "black", fontSize:14}, viewWindow: {max:1, min:0} },
	isStacked:true,
	bar: {groupWidth: "90%"},
	colors:["#99CC00","#FFFC66","#FFCC00","#DC1F00"],
	fontSize: 13,
	width:750,
	height:380,
	enableInteractivity: false,
	chartArea:{left:100,top:5,width:"750", height:"310", backgroundColor:{"stroke":"#B1B1B1","strokeWidth": 1}},
	legend:"none"
	};

	var ra_chart = new google.visualization.ColumnChart(document.getElementById("ra_chart_div"));
	ra_chart.draw(data, options);
	// add a SVG download button
	addSVGDownloadButton("#ra_chart_div");
	addPNGDownloadButton("#ra_chart_div");
}


/**
* Prints a line chart (using Google Charts) showing the various frequency analysis return periods for soil loss
*
* @method drawFrequencyAnalysisReturnPeriodsChart
* @param {Array} scenarios_array - an array of the scenarios
* @param {String} a string with the current units
* @return {void}
*/
function drawFrequencyAnalysisReturnPeriodsChart(scenarios_array, units) {
   var data = new google.visualization.DataTable();
   data.addColumn("number","Return Period");
   data.addColumn("number","Baseline Scenario");

   // to add annotations to this chart, I can use the following logic
   // data.addColumn({type:"string", role:"annotation"});
   // data.addColumn({type:"string", role:"annotationText"});  

   // dynamically add the scenario headers to the Google Charts data table
   for (var i = 0; i < scenarios_array[0].length - 2; i++){
   		data.addColumn("number", "Scenario " + (i + 1));	
   }
   data.addRows(scenarios_array);

   var options = {
      title: "",
      hAxis: { 
      		title: "Return Period (years)",
            minValue: 0, 
            maxValue: 102,
            ticks: [0,10,20,30,40,50,60,70,80,90,100],
            gridlines: {color: "#F9F9F9"},
            titleTextStyle: {color: "black", fontSize:14}
      },
      vAxis: { 
               title: "Soil loss (" + units + "/year)", 
               gridlines: {color: "#F9F9F9"},
               minValue: 0,
               titleTextStyle: {color: "black", fontSize:14}
			 },
	legend: { position: 'right' },
      interpolateNulls: true,
      width:745,
      height:365,
      fontSize: 12,
      crosshair: { trigger: "both",selected: { color: "#F8B04D", opacity: 1.0 } } ,
      chartArea:{left:100,top:40,width:"750",height:"260",backgroundColor:{"stroke":"#E8E8E8","strokeWidth": 1}},
      colors:["#404040","#9C640C","#196F3D","#1A5276","#633974","#7B241C"]

    };

   farpc_chart = new google.visualization.LineChart(document.getElementById("farp_chart_div"));
   farpc_chart.draw(data, options);
   	// add a SVG download button
	addSVGDownloadButton("#farp_chart_div");
	addPNGDownloadButton("#farp_chart_div");
}

/**
* Calculate the return periods based on a numerical interpolation for all alternative
*
* @method calculateAllReturnPeriods
* @param {Array} outputRAArray - an array of the scenarios
* @param {String} scenarioNames - a string with a list of scenario names
* @return {void}
*/
function calculateAllReturnPeriods(outputRAArray, scenarioNames) {
   var interpolatedResultsArray = [];
   for(var x = 0; x < outputRAArray[0].length;x++){
   	  // the baseline return period
      var currentRP = outputRAArray[0][x];
      // the baseline soil loss
      var currentSoilLoss = outputRAArray[1][x];

      //console.log("Current: " + currentRP + "    " + currentSoilLoss + "    "  + outputRAArray[2][x]);

      var currentRPIndex = outputRAArray[0].indexOf(currentRP);
      var max_baseline_soil_loss = outputRAArray[1][currentRPIndex];
    
      var rpPeriodArray = [currentSoilLoss, currentRP];
      //var tableCellArray = array();
      for(var i = 2; i < outputRAArray.length; i++){
        var alt_scenario = outputRAArray[i].filter(isSmallerThan(max_baseline_soil_loss));
        
        // default the altenative scenario interpolated value to 1
        var alt_scenario_interp = 1;
        if(alt_scenario.length != 0){
          alt_scenario_interp = Math.round( everpolate.linear(max_baseline_soil_loss, outputRAArray[i], outputRAArray[0]) * 1000)/1000;

          // set the return period to 100 if the interpolated value is greater than 100
          if (alt_scenario_interp > 100 || isNaN(alt_scenario_interp))
            alt_scenario_interp = 100;
        }
        // round the interpolated year to the nearest 10th place
        rpPeriodArray.push( Math.round(alt_scenario_interp*10)/10 );
      }
      interpolatedResultsArray.push(rpPeriodArray);
   }
 
   var rp_data_table = null;
   rp_data_table = new google.visualization.DataTable();

   // add Baseline scenario columns
   var scenarioName = "Baseline (soil loss)";
   rp_data_table.addColumn("number",scenarioName);
   var scenarioName = "Baseline (years)";
   rp_data_table.addColumn("number",scenarioName);

   // add colums for all alternative scenarios
   for(var s = 0; s < interpolatedResultsArray[0].length - 1; s++){
      if (s > 0){
         scenarioName = "Scenario " + s + "(years) <br/><br/>" + scenarioNames[s];
     	 rp_data_table.addColumn("number", scenarioName);
     }
   }
   //console.log("Array Size: " + interpolatedResultsArray.length);
   //if (interpolatedResultsArray.length && interpolatedResultsArray[0].length){
   rp_data_table.addRows(interpolatedResultsArray);
   //}

   var rp_table = new google.visualization.Table(document.getElementById("rp_interpolated_table_div"));
      var cssClassNames = {
	    "headerRow": "google_charts_table_header",
	    "tableRow": "google_charts_table_rows",
	    "oddTableRow": "google_charts_table_rows",
	    "tableCell": "google_charts_table_cell",
	    "selectedTableRow":"google_charts_table_selected_row",
	    "hoverTableRow":"google_charts_table_hover_row"
	  };
   rp_table.draw(rp_data_table, {showRowNumber: false, width: "100%", height: "100%","cssClassNames": cssClassNames, "allowHtml":true});

   // add an event listner to the data table
   google.visualization.events.addListener(rp_table, "select", function(){
      farpc_chart.setSelection([{"row": rp_table.getSelection()[0].row, column:1}]);
   });
   // add an event listner to the line chart
   google.visualization.events.addListener(farpc_chart, "select", function(){
      rp_table.setSelection([{"row": farpc_chart.getSelection()[0].row}]);
   });
}

/** Callback function to find values thare are smaller than or equal to. **/
function isSmallerThan(maxValue) { 
  return function(value) {
    return value <= maxValue;
  }
}

/**  Tranposes an array and returns it. **/
function transposeArray(orig_array){
  var newArray = orig_array[0].map(function(col, i) { 
    return orig_array.map(function(row) { 
      return row[i] 
    })
  });
  return newArray;
}
