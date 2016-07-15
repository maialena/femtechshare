//global variable: location of WickedlySmart H!

var ourCoords = {
    latitude: 47.624851,
};

window.onload = getMyLocation; // call the geoMyLocation function as soon as the browser loads the page

var watchId = null;

function watchLocation() {
    watchId = navigator.geolocation.watchPosition(displayLocation, displayError);
}

function clearWatch() {
    if (watchId) {
        navigator.geolocation.clearWatch(watchId);
        watchId = null;
    }
}




function getMyLocation() {
    if (navigator.geolocation) { // this is how we check if browser supports GeoLocation API; it it does, t hen we'll have an object (anything not 0 evaluates to true)
        var watchButton = document.getElementById("watch");
        watchButton.onclick = watchLocation;
        var clearWatchButton = document.getElementById("clearWatch");
        clearWatchButton.onclick = clearWatch;
    } else {
        alert("Oops, no geolocation support"); // if browser does not support
    }
}


function displayLocation(position) {
    var latitude = position.coords.latitude; //coords is an object that has latitude and longitude attributes
    var longitude = position.coords.longitude;
    var div = document.getElementById("location");
    div.innerHTML = "You are at latitude: " + latitude + " and longitude: " + longitude;￼
    div.innerHTML += " (with " + position.coords.accuracy + " meters accuracy)";


    //compare here to HQs
    var km = computeDistance(position.coords, ourCoords);
    var distance = document.getElementById("distance");
    distance.innerHTML = "You are " + km + " km from the WickedlySmart HQ";

    if (map == null) {
        showMap(position.coords);
    }

}


function displayError(error) {
    var errorTypes = {
        0: "Unknown error",
        1: "Permission denied by user",
        2: "Position is not available",
        3: "Request timed out"
    };
    var errorMessage = errorTypes[error.code];
    if (error.code == 0 || error.code == 2) {
        errorMessage = errorMessage + " " + error.message;
    }
    var div = document.getElementById("location");
    div.innerHTML = errorMessage;
}


//create a Google Map
var map;

function showMap(coords) {
    var googleLatAndLong =
        new google.maps.LatLng(coords.latitude, coords.longitude); //grab our lat and long
    var mapOptions = { // create a mapOptions object to pass into the Map constructor below
        zoom: 10,
        center: googleLatAndLong, // we want to center our map on this location
        mapTypeId: google.maps.MapTypeId.ROADMAP //can change this to SATELLITE and HYBRID for different views
    };
    var mapDiv = document.getElementById("map");
    map = new google.maps.Map(mapDiv, mapOptions);
}

//compute distance from your location and WickedlySmart HQfunction computeDistance(startCoords, destCoords) {
function computeDistance(startCoords, destCoords) {
    var startLatRads = degreesToRadians(startCoords.latitude);
    var startLongRads = degreesToRadians(startCoords.longitude);
    var destLatRads = degreesToRadians(destCoords.latitude);
    var destLongRads = degreesToRadians(destCoords.longitude);
    var Radius = 6371; // radius of the Earth in km
    var distance = Math.acos(Math.sin(startLatRads) * Math.sin(destLatRads) +
        Math.cos(startLatRads) * Math.cos(destLatRads) * Math.cos(startLongRads - destLongRads)) * Radius;
    return distance;
}

function degreesToRadians(degrees) {
    var radians = (degrees * Math.PI) / 180;
    return radians;

}