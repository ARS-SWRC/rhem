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
    <script type="text/javascript">google.load("jquery", "1.7.1");</script>
    <script type="text/javascript">

      // on-page-load initializations
$(window).load(function () {
    // init map used to select precip station
    initializeMap();
  });    
</script>
  </head>

  <body>

        <div id="googleMap" style="width: 100%; height:100% ;border:1px solid #F4F4F4;"></div>
    
    <script type="text/javascript" src="../assets/scripts/jquery-plugins.js"></script>
    <script>google.load('maps','3',{other_params:'sensor=false'});</script>
    <script type="text/javascript" src="../assets/scripts/map_func.js?updated=<?php echo time();?>"></script>
    

  </body>
</html>