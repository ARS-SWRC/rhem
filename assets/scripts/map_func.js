/*
 * map_func.js 
 *
 * Author: Gerardo Armendariz
 *
 * Edited:  06-13-2016
 *
 * This script handles all the climate station map functionality
 */

// global variables
var gmap; 
var polys = [];
var labels = [];
var markers = [];

var APPROOT = "https://apps.tucson.ars.ag.gov/rhem/";

var raingauge_image = {
    url: APPROOT + 'assets/images/map_marker/image.png',
    size: new google.maps.Size(40,40),
    origin: new google.maps.Point(0,0),
    anchor: new google.maps.Point(20,40)
};
var raingauge_shadow = {
    url: APPROOT + 'assets/images/map_marker/shadow.png',
    size: new google.maps.Size(64,40),
    origin: new google.maps.Point(0,0),
    anchor: new google.maps.Point(20,40)
};

var infoWindow = new google.maps.InfoWindow();

////////////////
// Initialized the Google Maps component (based on version 3.8)
///////////////
function initializeMap() {
		var mapOptions = { //zoom: 4,
		panControl:false,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		streetViewControl:false,
		navigationControlOptions: {style: google.maps.NavigationControlStyle.SMALL},
		mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU}
	};
	gmap = new google.maps.Map(document.getElementById('googleMap'), mapOptions);
}

////////////////
// Loads the Google Maps interface.  If the GMap object has already been initialized, just center the map.
////////////////
function loadStatesLayer(){
	console.log("Loading stations layer");
	kmlLayer = new google.maps.KmlLayer(APPROOT + "assets/xml/us_states_conus_and_hi.kmz",{suppressInfoWindows: true });
	kmlLayer.setMap(gmap);
	  
	google.maps.event.addListener(kmlLayer, 'click', 
		function(kmlEvent) 
		{ 
			show_all_climate_stations((kmlEvent.featureData.name).toLowerCase());
		}
	); 
}

////////////////
// Add the map object to the DOM
////////////////
//google.maps.event.addDomListener(window, 'load', initializeMap);

////////////////
// Loads the Google Maps interface.  If the GMap object has already been initialized, just center the map.
////////////////
function showMap() {
	// once the KML layer is loaded, show the map container
	$("#mapHeader").show();
	$("#googleMap").show();
	toggleContent("#mapContainer"); 
	google.maps.event.trigger(gmap, 'resize');

	// zoom to US
	uszoom = new google.maps.LatLng(31.00,-110.00);
	gmap.setCenter(uszoom);
	gmap.setZoom(3);
}

////////////////
// Clears all markers in the map
///////////////
function clearMapMarkers(){
	// first, hide the markers added for other states
	for (var i=0; i<markers.length; i++) {
		markers[i].setMap(null);
	}
}

////////////////
// Shows all climate stations in embedded map
////////////////
function show_all_climate_stations(stateid){
	$.getJSON("defineclimate/showstateclimatestations/" + stateid,  function(data){
				if(data.length > 0) {
					if(data == 'session_expired')
						redirectToLogin();
					else{
						// first, clear the markers
						clearMapMarkers();
						// add all of the stations to the state map
						for(var i = 0; i < data.length; i++)
						{
							latnum = data[i].lat;
							longnum = data[i].longitude;
							stationname = data[i].station;
							stationid = data[i].station_id;
							avgrain = data[i].avg_rain;
							monthlyrain = [ data[i].jan, data[i].feb, data[i].mar, data[i].apr, data[i].may, data[i].jun, data[i].jul, data[i].aug, data[i].sep, data[i].oct, data[i].nov, data[i].dec ];
							//console.log("Monthly Rain: " + monthlyrain);
							elevation = data[i].elevation;
							
							myLatlng = new google.maps.LatLng(latnum,longnum); 
							marker = new google.maps.Marker({
								position: myLatlng, 
								map: gmap,
								icon: raingauge_image,
							    shadow: raingauge_shadow,
							    optimized: false,
								id:stationid,
								name:stationname,
								state:stateid,
								avgrain:avgrain,
								monthlyrain:monthlyrain,
								elevation:elevation,
								latitude:latnum,
								longitude:longnum,
							});   
							
							// create a closure to retain the event scope to the current marker.  This will add the infowindow for each marker. 
							(function(marker) {
								google.maps.event.addListener(marker, 'click', function(event) {
									createStationMarker(marker, "<p><input type='button' value='Use this station' onclick='selectStationInList(\"" +  marker.state + "\",\"" + marker.id + "\");$(this).parent().html(\"Currently using this station!\")'/></p>");
								});
							})(marker);

							markers.push(marker);
						}
						// if all marked loaded, zoom to selected state
						$.post("defineclimate/zoomtostate/" + stateid,  function(data){
								if(data.length > 0) {
									zoomPoint = data.split(":");
									statezoom = new google.maps.LatLng(zoomPoint[0],zoomPoint[1]);
									gmap.setCenter(statezoom);
									gmap.setZoom(parseInt(zoomPoint[2]));
								}
						});
					}
				}
		});
}


////////////////
// Shows international climate stations in embedded map
////////////////
function show_international_climate_stations(stateid){
	$.getJSON("defineclimate/showstateclimatestations/" + stateid,  function(data){
				if(data.length > 0) {
					if(data == 'session_expired')
						redirectToLogin();
					else{
						// first, clear the markers
						clearMapMarkers();
						// add all of the stations to the state map
						for(var i = 0; i < data.length; i++)
						{
							latnum = data[i].lat;
							longnum = data[i].longitude;
							stationname = data[i].station;
							stationid = data[i].station_id;
							avgrain = data[i].avg_rain;
							monthlyrain = [ data[i].jan, data[i].feb, data[i].mar, data[i].apr, data[i].may, data[i].jun, data[i].jul, data[i].aug, data[i].sep, data[i].oct, data[i].nov, data[i].dec ];
							//console.log("Monthly Rain: " + monthlyrain);
							elevation = data[i].elevation;
							
							myLatlng = new google.maps.LatLng(latnum,longnum); 
							marker = new google.maps.Marker({
								position: myLatlng, 
								map: gmap,
								icon: raingauge_image,
							    shadow: raingauge_shadow,
							    optimized: false,
								id:stationid,
								name:stationname,
								state:stateid,
								avgrain:avgrain,
								monthlyrain:monthlyrain,
								elevation:elevation,
								latitude:latnum,
								longitude:longnum,
							});   
							
							// create a closure to retain the event scope to the current marker.  This will add the infowindow for each marker. 
							(function(marker) {
								google.maps.event.addListener(marker, 'click', function(event) {
									createStationMarker(marker, "<p><input type='button' value='Use this station' onclick='selectInternationalStationInList(\"" +  marker.state + "\",\"" + marker.id + "\");$(this).parent().html(\"Currently using this station!\")'/></p>");
								});
							})(marker);

							markers.push(marker);
						}

						// set center and extent
						statezoom = new google.maps.LatLng( 25.380064,7.220526);
						gmap.setCenter(statezoom);
						gmap.setZoom(2);
						
					}
				}
		});
}



////////////////
// Creates the content of the infowindow for each marker to show additional information about the selected climate station.
////////////////
function createStationMarker(selectedMarker,selectionMessage){
    monthlyRainChrtData = "chd=t:" + selectedMarker.monthlyrain[0] + "," + selectedMarker.monthlyrain[1] + "," + selectedMarker.monthlyrain[2] + "," + selectedMarker.monthlyrain[3] + "," + selectedMarker.monthlyrain[4] + "," + selectedMarker.monthlyrain[5] + "," + selectedMarker.monthlyrain[6] + "," + selectedMarker.monthlyrain[7] + "," + selectedMarker.monthlyrain[8] + "," + selectedMarker.monthlyrain[9] + "," + selectedMarker.monthlyrain[10] + "," + selectedMarker.monthlyrain[11];
    monthlyRainChrtDataLabels = "chm=N,000000,0,-1,10";
    

	infoWindow.setContent("<b>Name: </b> " + selectedMarker.name + "<br/>" + 
						  "<b>ID: </b>" + selectedMarker.id + "<br/>" +
						  "<b>Elevation: </b>" +  (Number(selectedMarker.elevation) * 0.3048).toFixed(2) + " m ( " + selectedMarker.elevation + " ft )<br/>" +  
						  "<b>Lat: </b>" +  selectedMarker.latitude + "   <b>Long:</b> " + selectedMarker.longitude + " <br/><br/>" +
						  "<b>Avg. Precipitation: </b>" + selectedMarker.avgrain + " mm ( " +  (Number(selectedMarker.avgrain) * 0.0393701).toFixed(2)   + " in ) <br/>" + 
						  "<p><b>Monthly Precipitation (mm):</b><br/>" + 
						  "<img src='https://chart.googleapis.com/chart?chxt=x,y&chds=a&cht=bvs&" + monthlyRainChrtData + "&" + monthlyRainChrtDataLabels + "&chco=DA8B2E&chls=2.0&chs=350x125&chxl=0:|Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec'/>" +
						  "</p>" + 
						  selectionMessage );
	infoWindow.open(gmap,selectedMarker);
}


////////////////
// Selecte the marker in the map based on the state and station seleced in the list
////////////////
function selectStationInMap(){

	var selectedStationID = $("#climatestationid").val();
	var selectedStationName = $("#climatestationid option:selected").text();
	
	var selectedMarker;
	for (var i=0; i<markers.length; i++) {
		//console.log("Station ID: " + markers[i].id + "    Selected ID: " + selectedStationID);
		if(markers[i].id == selectedStationID){
			selectedMarker = markers[i];
			break;
		}
	}

	createStationMarker(selectedMarker,"<p>Currently using this station!</p>");
}


////////////////
// Selecte the marker in the map based on the state and station seleced in the list
////////////////
function selectInternationalStationInMap(){

	var selectedStationID = $($("[id=climatestationid]")[1]).val()
	//var selectedStationName = $("#climatestationid option:selected").text();
	
	var selectedMarker;
	for (var i=0; i<markers.length; i++) {
		//console.log("Station ID: " + markers[i].id + "    Selected ID: " + selectedStationID);
		if(markers[i].id == selectedStationID){
			selectedMarker = markers[i];
			break;
		}
	}

	createStationMarker(selectedMarker,"<p>Currently using this station!</p>");
}


////////////////
// Select the state and station in the lists based on the marker selected in the map
////////////////
function selectStationInList(stateid,stationid){
	$("#stateid").val(stateid.toLowerCase());
	// update the climate station list based on the selected state
	$.post("defineclimate/getclimatestations/", {stateQueryString: "" + stateid.toLowerCase() + ""}, function(data){
				if(data.length > 0) {
					$('#climatestationid').html(data);
					$("#climatestationid").val(stationid);
				}
		});
}


////////////////
// Select the state and station in the lists based on the marker selected in the map
////////////////
function selectInternationalStationInList(stateid,stationid){
	//$("#stateid").val(stateid.toLowerCase());
	// update the climate station list based on the selected state
	$.post("defineclimate/getclimatestations/", {stateQueryString: "" + stateid.toLowerCase() + ""}, function(data){
				if(data.length > 0) {
					$($("[id=climatestationid]")[1]).html(data);
					$($("[id=climatestationid]")[1]).val(stationid);
				}
		});
}



////////////////
// Updates the soils list and the climate stations list based on the selected state
////////////////
function update_climatestations_by_state(){
		var state = $("#stateid").val();
		$.post("defineclimate/getclimatestations/", {stateQueryString: "" + state + ""}, function(data){
			if(data.length > 0) {
				if(data == 'session_expired')
					redirectToLogin();
				else{
					$('#climatestationid').html(data);
				}
			}
		});
}

////////////////
// Updates the soils list and the climate stations list based on the selected state and shows the the stations
// in the map
////////////////
function update_climatestations_and_show_map(){
	var state = $("#stateid").val();
	
	show_all_climate_stations(state);
	// load the map if it's not visible, and load the available climate stations for the selected state in the map
	if($("#mapContainer").is(":hidden")){
		showMap();
	}
	update_climatestations_by_state();
}


////////////////
// Show the international climate stations 
////////////////
function update_international_climatestations_and_show_map(){
	show_international_climate_stations("INL");
	// load the map if it's not visible, and load the available climate stations for the selected state in the map
	if($("#mapContainer").is(":hidden")){
		showMap();
	}
	//update_climatestations_by_state();
}

////////////////
// Show the national list of climates stations
////////////////
function showSelectedClimatesStation(){
	// load the states kml layer
	loadStatesLayer();
	// clear all markers 
	clearMapMarkers();
	update_climatestations_and_show_map();
	if($("#climatestationid").val() != ''){
		//console.log("Entered showSelectedClimatesStation()");
		selectStationInMap();
	}
}

////////////////
// Show the national list of climates stations
////////////////
function showInternationalClimateStations(){

	// clear all markers 
	clearMapMarkers();
	update_international_climatestations_and_show_map();
	if($("#climatestationid").val() != ''){
		//console.log("Entered showSelectedClimatesStation()");
		selectStationInMap();
	}
}