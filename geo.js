var geoObj = function (new_id) {
    var map = 0;
    var z = 0;
    var op = 0;
    var accuracy = 0;
    var accuracy_alt = 0;
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
    var latlon = 0.0;
    var current_datetime=0;
    var user_id = new_id;
    
    console.log(user_id);
    
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
        current_datetime = format_time_component(h) + ":" + format_time_component(m) + ":" + format_time_component(s);
        
        lat = position.coords.latitude;
        lon = position.coords.longitude;
        
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
                
                latlon = position.coords.latitude.toFixed(6) + " , " + position.coords.longitude.toFixed(6);
                accuracy = position.coords.accuracy.toFixed(2) + "m";
                min_speed = (min_speed ? min_speed  : "Not recorded"); 
                max_speed = (max_speed ? max_speed  : "Not recorded");
                min_altitude = (min_altitude ? min_altitude : "Not recorded");
                max_altitude = (max_altitude ? max_altitude : "Not recorded");
                if(position.coords.altitudeAccuracy){
                    accuracy_alt = position.coords.altitudeAccuracy.toFixed(2) + "m";
                }else{
                    accuracy_alt = "Null";
                }
                log_position(position,user_id);
                print_position(position);
            }            
        } else {
            info_string = "Accuracy not sufficient (" + Math.round(position.coords.accuracy, 1) + "m vs " + min_accuracy + "m) - last reading taken at: " + current_datetime;
        }
    }
    
    function print_position(position){
        $('#latlon').html(latlon);
        $('#minspeed').html(min_speed);    
        $('#maxspeed').html(max_speed);    
        $('#minaltitude').html(min_altitude);
        $('#maxaltitude').html(max_altitude);
        $('#accuracy').html(accuracy);
        $('#accuracy_alt').html(accuracy_alt);
        $('#updated').html(current_datetime);
    }
    
    /**
    * 
    */
    function log_position(position,user){
        console.log(position);
        console.log(user);
        $.post( "https://msu2u.us/bus/api/v1/logUser/", { user_id: user, loc_data: position})
            .done(function( data ) {
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

    function init_geo() {
        if (wpid) {
            navigator.geolocation.clearWatch(wpid);
            wpid = false;
        } else {
            get_pos();
        }
            
    }
    
    init_geo();
    
    return {
        get_pos: get_pos,
        print_position: print_position
    }
};


