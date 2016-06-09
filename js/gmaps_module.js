var GMapsModule = (function () {


	var map;

	var gps_points = [];

	/**
	 * public init 
	 * @Description:
	 *		This method saves the points in the modules scope, otherwise they would only be available
	 *		in the '.done' method of the ajax request
	 *		
	 * @param {int} id - route id 
	 * @return {null} 
	 */
	var init = function(lat,lon){
		map = new GMaps({
			el: '#map',
			lat: lat,
			lng: lon
		});
	};
	

	/**
	 * private savePointData
	 * @Description:
	 *		This method saves the points in the modules scope, otherwise they would only be available
	 *		in the '.done' method of the ajax request
	 *		
	 * @param {int} id - route id 
	 * @return {null} 
	 */
	var savePointData = function (points){				
		
		for(var i=0;i<points.length;i++){
			gps_points[i] = points[i];
		}
		console.log(gps_points);
	};
	
	/**
	 * private _getRoute
	 * @Description:
	 *		This method gets the points needed from the database, saves them locally, then calls the draw function.
	 * @param {int} id - route id 
	 * @return {null} 
	 */	
	var _getPoints = function (type,id) {
	
		var url =  "http://msu2u.us/bus/api/v1/gps_points/"+type+"/"+id
		var callback = '';
		
		if(type == 'route'){
			callback = _drawRoute;
		}else{
			callback = _drawBusStops;
		}
		
	
		$.get(url)
		.done(function( data ) {
			savePointData(data.points);
			callback();
		});
	};
	
	
	/**
	 * private _drawRoute
	 * @Description:
	 *		This draws a route onto a map using a local array of points.
	 * @param {int} id - route id 
	 * @return {null} 
	 */					
	var _drawRoute = function(){

		for(var i=0;i<gps_points.length;i++){
			var j = (i + 1) % gps_points.length;
			
			map.drawRoute({
				origin: [gps_points[i].lat, gps_points[i].lon],
				destination: [gps_points[j].lat, gps_points[j].lon],
				travelMode: 'driving',
				strokeColor: '#000000',
				strokeOpacity: 0.6,
				strokeWeight: 4
			});
		}
	};
	
	/**
	 * private _drawBusStops
	 * @Description:
	 *		This draws a route onto a map using a local array of points.
	 * @param {int} id - route id 
	 * @return {null} 
	 */					
	var _drawBusStops = function(){

		for(var i=0;i<gps_points.length;i++){					
			map.addMarker({
			  lat: gps_points[i].lat,
			  lng: gps_points[i].lon,
			  title: gps_points[i].stop_type,
			  click: function(e) {
				console.log('You clicked in this marker');
			  }
			});
		}
	};
	
	/**
	 * public drawRoute
	 * @param {int} id - route id 
	 * @return {null} 
	 */			
	var drawRoute = function(id){
		_getPoints('route',id);
	}
	
	/**
	 * public drawBusStops
	 * @param {int} id - route id 
	 * @return {null} 
	 */			
	var drawBusStops = function(id){
		_getPoints('bus_stop',id);
	}			

	return {
		init		: init,
		drawRoute	: drawRoute,
		drawBusStops: drawBusStops
	}
})();


