<pre>
latMin: {$latMin}
latMax: {$latMax}

lonMin: {$lonMin}
lonMax: {$lonMax}

latCenter: {$latCenter}
lonCenter: {$lonCenter}

</pre>
{foreach $locations as $location}
    <b>{$location.name}</b> [lat: {$location.lat}, lon: {$location.lon}]:<br />
    <div style="color: {$location.color};">({$location.count}) {$location.userstr}</div><br />
{/foreach}



{*


<!-- <script type="text/javascript" charset="utf-8">google.load("maps","2.x");</script> -->
<!-- <script src="https://maps.googleapis.com/maps/api/js?v=3&sensor=true" type="text/javascript"></script> -->
<script type="text/javascript">
    $(document).ready(function(){
    

        jQuery.getScript("https://maps.googleapis.com/maps/api/js?v=3&sensor=true", function (){
            console.log(1);
            //var latlng = new google.maps.LatLng(-34.397, 150.644);
            console.log(2);
            var myOptions = {
                zoom: 8
                //    center: latlng,
                //    mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            var map = new google.maps.Map(document.getElementById("userMap"), myOptions);
       
        });
        //jQuery.getScript("http://www.google.com/jsapi?key=ABQIAAAAoQTlyGjpddJMRStim8sqRhS-FT1JS65wtMkQxe7iDJyuhifRuBSPdRuPdH75Zcjtp8yMJZZGC2vu7w");
        
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
<!-- <div id="mapMessage"style="display:none;">Test text.</div> -->
<!-- <ul id="list"></ul> -->
*}