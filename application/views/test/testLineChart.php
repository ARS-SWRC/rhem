<?php 
 /**
   * PURPOSE: To test the Google Charts API in order to add all the graphics to the DRHEM
   *          interface based on this library.
   *
  **/
?>
<html>
  <head>
    <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">

      // Load the Visualization API and the piechart package.
      google.load('visualization', '1.0', {'packages':['corechart']});
      google.load("jquery", "1.7.1");
      
      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback(testDrawChart);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
	 function testDrawChart(){
		 drawChart(43, 3, 43, 'm');
	 }
     function drawChart(slpLength, slpShape, slpSteepness, units) {
		 var curveArray;
		 var yVal = (slpSteepness/100) * slpLength;
		 xCenter = slpLength/2;
		 
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
			   			   [xCenter * 1.1,    yVal * 0.7],
 						   [slpLength,  0]];
			  break;
			case 3: // Concave curve
			  curveArray = [['x_axis', 'y_axis'],
							[0,         yVal],
							[xCenter * 0.9,   yVal * 0.3],
							[slpLength, 0]];
			  break;
			case 4: // S-Shaped curve
			  curveArray = [['x_axis', 'y_axis'],
							[0,         yVal],
							[xCenter/1.45,   yVal - (yVal * .25)],
							[xCenter/.85,   yVal * .25],
							[slpLength, 0]];
			  break;
		}
		
		var data = google.visualization.arrayToDataTable(curveArray);
		// these are the options for building the linechart for the slope graph
        var options = {'title':'','backgroundColor':{'stroke':'#EDEDED','strokeWidth':4,'fill':'#FFFFFF'},
					   'chartArea':{left:50,top:30,width:"90%",height:"80%"},
					   'hAxis':{title: "Elevation (" + units + ")",baseline:slpLength, baselineColor:'#EDEDED', viewWindowMode:'maximized' },
					   'vAxis':{title: "Length (" + units + ")", baseline:yVal, baselineColor:'#EDEDED', viewWindowMode:'maximized'},
					   'pointSize':0, 'enableInteractivity':false,'tooltip':{trigger:'none'},
					   'width':673,'height':450,legend: 'none',
                       'curveType':'function',
					   'colors': ['#4C8029']};

        var chart = new google.visualization.LineChart(document.getElementById('slope_chart'));
        chart.draw(data, options);
      }
    </script>
  </head>
  <body>
    <div id="slope_chart"></div>
  </body>
</html>