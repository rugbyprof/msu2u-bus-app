var geoObj = function (eleID,user_id) {
    var map = 0;
    var z = 0;
    var op = 0;
    var prev_lat = 0;
    var prev_long = 0;
    var min_speed = 0;
    var max_speed = 0;
    var min_altitude = 0;
    var max_altitude = 0;
    var distance_travelled = 0;
    var min_accuracy = 150000;
    var date_pos_updated = "";
    var info_string = "";
    var wpid = null;
    
    function format_time_component(time_component) {
        if (time_component < 10)
            time_component = "0" + time_component;
        else if (time_component.length < 2)
            time_component = time_component + "0";
        return time_component;
    }
    function geo_success(position) {
        //info_string = "";
        var d = new Date();
        var h = d.getHours();
        var m = d.getMinutes();
        var s = d.getSeconds();
        var current_datetime = format_time_component(h) + ":" + format_time_component(m) + ":" + format_time_component(s);
        if (position.coords.accuracy <= min_accuracy) {
            if (prev_lat != position.coords.latitude || prev_long != position.coords.longitude) {
                if (position.coords.speed > max_speed)
                    max_speed = position.coords.speed;
                else if (position.coords.speed < min_speed)
                    min_speed = position.coords.speed;
                if (position.coords.altitude > max_altitude)
                    max_altitude = position.coords.altitude;
                else if (position.coords.altitude < min_altitude)
                    min_altitude = position.coords.altitude;
                prev_lat = position.coords.latitude;
                prev_long = position.coords.longitude;
                info_string = "Current positon: lat=" + position.coords.latitude + ", long=" + position.coords.longitude + " (accuracy " + Math.round(position.coords.accuracy, 1) + "m)<br />Speed: min=" + (min_speed ? min_speed : "Not recorded/0") + "m/s, max=" + (max_speed ? max_speed : "Not recorded/0") + "m/s<br />Altitude: min=" + (min_altitude ? min_altitude : "Not recorded/0") + "m, max=" + (max_altitude ? max_altitude : "Not recorded/0") + "m (accuracy " + Math.round(position.coords.altitudeAccuracy, 1) + "m)<br />last reading taken at: " + current_datetime;
                log_position("99",position);
            }            
        } else {
            info_string = "Accuracy not sufficient (" + Math.round(position.coords.accuracy, 1) + "m vs " + min_accuracy + "m) - last reading taken at: " + current_datetime;
        }
        document.getElementById(eleID).innerHTML = info_string;
    }
    
    /**
    * 
    */
    function log_position(user_id,position){
        
        $.post( "http://msu2u.us/bus/api/v1/logUser/", { user_id: user_id, loc_data: position})
            .done(function( data ) {
            //console.log( "Data Loaded: " + data );
        });
        
    }
    
    function geo_error(error) {
        switch (error.code) {
            case error.TIMEOUT:
                this.op.innerHTML = "Timeout!";
                break;
        };
    }

    function get_pos() {
        if (!!navigator.geolocation)
            wpid = navigator.geolocation.watchPosition(geo_success, geo_error, {
                enableHighAccuracy: true,
                maximumAge: 30000,
                timeout: 27000
            });
        else{
            this.op.innerHTML = "ERROR: Your Browser doesnt support the Geo Location API";
        }
    }

    function init_geo(eleID) {
        if (wpid) {
            document.getElementById(eleID).innerHTML = "Starting...";
            navigator.geolocation.clearWatch(wpid);
            wpid = false;
        } else {
            document.getElementById(eleID).innerHTML = "Aquiring Geo Location...";
            get_pos();
        }
            
    }
    
    init_geo(eleID);
    
    return {
        get_pos: get_pos
    }
};


