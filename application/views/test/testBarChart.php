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
      google.setOnLoadCallback(drawChart);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawChart() {

        // Create the data table
        // NOTE: I could also convert an Array to a DataTable (arrayToDataTable()) or I could also initialize the DataTable with a JavaScript object.
        // AVG TABLE
        var avg_data = new google.visualization.DataTable();
        avg_data.addColumn('string', 'Scenario');
        avg_data.addColumn('number', 'S1');
        avg_data.addColumn('number', 'S2');
        avg_data.addColumn('number', 'S3');
        avg_data.addRows([['S1', 3,6, 8]]);
        avg_data.addRows([['S2', 5, 4, 6]]);
        avg_data.addRows([['S3', 2, 6, 5]]);
        avg_data.addRows([['50', 2, 6, 5]]);
        avg_data.addRows([['100', 2, 6, 5]]);
		
		var barArray = [['Scenario','Value'],
						['one',0,3],
						['one',1,6],
						['one',2,8],
						['one',3, 10]];
		
		
		var avg_data = google.visualization.arrayToDataTable(barArray);
        // Set chart options
        var avg_options = {'title':'Average ( Rain )','width':637,'height':450,legend: 'none',vAxis: {title: "mm"},hAxis: {title: "Scenario"},
                       colors: ['#4C8029', '#AB9020', '#303030', '#f3b49f', '#f6c7b6']};
        // RF TABLE
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Scenario');
        data.addColumn('number', 'S1');
        data.addColumn('number', 'S2');
        data.addColumn('number', 'S3');
        data.addRows([['2', 3,6, 8]]);
        data.addRows([['10', 5, 4, 6]]);
        data.addRows([['25', 2, 6, 5]]);
        data.addRows([['50', 2, 6, 5]]);
        data.addRows([['100', 2, 6, 5]]);
        // Set chart options
        var options = {'title':'Return Frequency ( Rain )','width':400,'height':300,legend: 'none',vAxis: {title: "mm"},hAxis: {title: "Return Frequency (years)"},
                       colors: ['#4C8029', '#AB9020', '#303030', '#f3b49f', '#f6c7b6']};

        // Instantiate and draw our chart, passing in some options.
        var avg_chart = new google.visualization.ColumnChart(document.getElementById('chart_div_avg'));
        avg_chart.draw(avg_data, avg_options);
        
        // Instantiate and draw our chart, passing in some options.
        //var avg_chart = new google.visualization.ColumnChart(document.getElementById('chart_div_rf'));
        //avg_chart.draw(data, options);

      }
    </script>
  </head>

  <body>
    <!--Div that will hold the pie chart-->
    <div id="chart_div_avg"></div>
    <div id="chart_div_rf"></div>
  </body>
</html>