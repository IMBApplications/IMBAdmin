<script type="text/javascript"src="http://www.google.com/jsapi?v=2&key=ABQIAAAAoQTlyGjpddJMRStim8sqRhS-FT1JS65wtMkQxe7iDJyuhifRuBSPdRuPdH75Zcjtp8yMJZZGC2vu7w"></script>
<!-- <script src="https://maps.googleapis.com/maps/api/js?v=3&sensor=true_or_false" type="text/javascript"></script> -->
<script type="text/javascript"charset="utf-8">google.load("maps","2.x");</script>
<script type="text/javascript">
$(document).ready(function(){ 
  var map = new GMap2(document.getElementById(‘userMap’)); 
  var burnsvilleMN = new GLatLng(44.797916,-93.278046); 
  map.setCenter(burnsvilleMN, 8); 
  
  // setup 10 random points 
    var bounds = map.getBounds(); 
    var southWest = bounds.getSouthWest(); 
    var northEast = bounds.getNorthEast(); 
    var lngSpan = northEast.lng() – southWest.lng(); 
    var latSpan = northEast.lat() – southWest.lat(); 
    var markers = []; 
    for (var i = 0; i<10; i++) { 
      var point = new GLatLng(southWest.lat() + latSpan * Math.random(), southWest.lng() + lngSpan * Math.random()); 
      marker = new GMarker(point); 
      map.addOverlay(marker); 
      markers[i] = marker; 
    }

    $(markers).each(function(i,marker){ 
      GEvent.addListener(marker,"click", function(){ 
      map.panTo(marker.getLatLng()); 
     }); 

});

  
  $("#message").appendTo(map.getPane(G_MAP_FLOAT_SHADOW_PANE));

});
</script>
<style media="screen"type="text/css">
    #userMap { width:500px; height:400px; }
    #mapMessage { position:absolute; padding:10px; background:#555; color:#fff; width:75px; }
    #list { float:left; width:200px; background:#eee; list-style:none; padding:0; } 
    #list li { padding:10px; } 
    #list li:hover { background:#555; color:#fff; cursor:pointer; cursor:hand; }
</style>
<!-- 
Src: http://marcgrabanski.com/articles/jquery-google-maps-tutorial-basics
MAP API Key: ABQIAAAAoQTlyGjpddJMRStim8sqRhS-FT1JS65wtMkQxe7iDJyuhifRuBSPdRuPdH75Zcjtp8yMJZZGC2vu7w
-->
<div id="userMap"></div>
<div id="mapMessage"style="display:none;">Test text.</div>
<ul id="list"></ul>
