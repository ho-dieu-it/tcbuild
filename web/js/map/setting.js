//============ MULTI GOOGLE MAP ============//
$(document).ready(function(){
	$("#map").TekMap({
		mapoptions : {
		  zoom: 5
		}
	});
	$("#map").TekMarker({lat:50.01,lng:10.05, draggable:true,infowindow:"Pagaden"});
	$("#map").TekMarker({lat:49.01,lng:2.05, draggable:true,infowindow:"Pamanukan"});
	$("#map").TekMarker({lat:47.01,lng:3.05, draggable:true,infowindow:"Kalijati"});
	$("#map").TekMarker({lat:47.01,lng:10.05, draggable:true,infowindow:"Purwadadi"});
	$("#map").TekMarker({lat:42.01,lng:13.05, draggable:true,infowindow:"Tonggoh"});
});