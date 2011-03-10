// Copyright 2006-2008 (c) Paul Demers  <paul@acscdg.com>
// modified by Pablo Vanwoerkom <guikubivan@gmail.com>

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA., or visit one 
// of the links here:
//  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
//  http://www.acscdg.com/LICENSE.txt

//////////////////////////////////////////////////////////////////

// Map drawing and distance tools.

//
// JavaScript objects that work with the Google geocoder object.
// Web site with this code running: http://www.acscdg.com/
//

//
//
//// Dependencies:
// _map from index.js
// Google maps for geocode and geopoint.

var _geocoder;  // The geocoder is only created once.
var _addressToGeoCode;  // Save the address, for the error message.
var _latID;
var _longID;

var googleApiKey = WPGoogleAPI.key;//obtained from wordpress plugin when localized
var _functionToCall = '';

//
//// Called from the Google geocode when it is done.
function foundLocation(response)
{
  if ((response == null) || (response.Status.code != G_GEO_SUCCESS))
  {
	clearCoordinateFields();

    return;//alert("\"" + _addressToGeoCode + "\" not found");
  }
  else
  {
    var place = response.Placemark[0];

    document.getElementById(_latID).value = place.Point.coordinates[1];
    document.getElementById(_longID).value = place.Point.coordinates[0];
    if(_functionToCall){
	//alert('calling');
	setTimeout(_functionToCall,10)
    }

  }
}

function clearCoordinateFields(){
    document.getElementById(_latID).value = '';
    document.getElementById(_longID).value = '';
}

//
//// Called when the "Find" button is pushed.
//// Reads the place name from the form, then calls the geocoder. 

function get_google_coordinates(location, latID, longID){
	_addressToGeoCode = location;
	_latID = latID;
	_longID = longID;
	if(!location){
		//alert('nothing entered');
		clearCoordinateFields();
	}
  	_geocoder.getLocations(_addressToGeoCode, foundLocation);
	
} 


//
//// Create and cache the Google geocoder object.
//// caller must destroy _geocoder when no longer needed.
function initGeoCoder()
{
  _geocoder = new GClientGeocoder();
}

initGeoCoder();

//// End of geocoding.
//

