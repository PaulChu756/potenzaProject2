<?php
class Ia_View_Helper_BoundableMap extends Zend_View_Helper_Abstract
{

    public $latitude = null;

    public $longitude = null;

    public $zoom = 22;

    public $enableDrawingManager = false;

    public $encodeOverlayJsonTargetId = false;

    public $mapTypeId = 'hybrid';

    public $jsonMapData = false;

    public $width = '100%';

    public $height = 500;

    public function boundableMap()
    {
        if(!$this->latitude || !$this->longitude){
            return '<p>Map cannot be generated because no starting latitude/longitude were provided {'.$this->latitude.','.$this->longitude.'}</p>';
        }

        $rand = md5(rand(10000,99999) + time());

        $this->view->headScript()->captureStart();
        ?>
           var mapOverlays = new Array();

           /* Global scope so it can be referenced elsewhere */
           var drawingManager = null;

          function initialize() {
            var mapOptions = {
              mapTypeId: '<?=$this->mapTypeId?>',
              center: new google.maps.LatLng(<?=$this->latitude?>,<?=$this->longitude?>),
              zoom: <?=$this->zoom?>
            };
            var map = new google.maps.Map(document.getElementById("map-canvas-<?=$rand;?>"),
                mapOptions);

            <?php if($this->jsonMapData): ?>
            setMapData(map,'<?=$this->jsonMapData?>');
            <?php endif; ?>    

            <?php if($this->enableDrawingManager): ?>
                  drawingManager = new google.maps.drawing.DrawingManager({
                  drawingMode: google.maps.drawing.OverlayType.POLYGON,
                  drawingControl: true,
                  drawingControlOptions: {
                    position: google.maps.ControlPosition.TOP_CENTER,
                    drawingModes: [
                      google.maps.drawing.OverlayType.POLYGON,
                    ]
                  }
                }); 
                drawingManager.setMap(map);
                <?php if($this->encodeOverlayJsonTargetId): ?>
                google.maps.event.addListener(drawingManager, 'overlaycomplete', function(event) {

                    var uniqueid =  uniqid();
                    event.overlay.uniqueid =  uniqueid;
                    event.overlay.title = "";
                    event.overlay.content = "";
                    event.overlay.type = event.type;
                    mapOverlays.push(event.overlay);
                    mapToObject(map);

                    var result = JSON.stringify( mapToObject(map) );
                    document.getElementById('<?=$this->encodeOverlayJsonTargetId?>').value = result;
                    
                });
                <?php endif; ?>
            <?php endif; ?>
          }

          google.maps.event.addDomListener(window, 'load', initialize);

          /* Helper functions */

        function uniqid(){
            var newDate = new Date;
            return newDate.getTime();
        }

        function mapToObject(mapObj){

            var tmpMap = new Object;
            var tmpOverlay, paths;
            tmpMap.zoom = mapObj.getZoom();
            tmpMap.tilt = mapObj.getTilt();
            tmpMap.mapTypeId = mapObj.getMapTypeId();
            tmpMap.center = { lat: mapObj.getCenter().lat(), lng: mapObj.getCenter().lng() };
            tmpMap.overlays = new Array();

            for( var i=0; i < mapOverlays.length; i++ ){
                if( mapOverlays[i].getMap() == null ){
                    continue;
                }
                tmpOverlay = new Object;
                tmpOverlay.type = mapOverlays[i].type;
                tmpOverlay.title = mapOverlays[i].title;
                tmpOverlay.content = mapOverlays[i].content;

                if( mapOverlays[i].fillColor ){
                    tmpOverlay.fillColor = mapOverlays[i].fillColor;
                }

                if( mapOverlays[i].fillOpacity ){
                    tmpOverlay.fillOpacity = mapOverlays[i].fillOpacity;
                }

                if( mapOverlays[i].strokeColor ){
                    tmpOverlay.strokeColor = mapOverlays[i].strokeColor;
                }

                if( mapOverlays[i].strokeOpacity ){
                    tmpOverlay.strokeOpacity = mapOverlays[i].strokeOpacity;
                }

                if( mapOverlays[i].strokeWeight ){
                    tmpOverlay.strokeWeight = mapOverlays[i].strokeWeight;
                }

                if( mapOverlays[i].icon ){
                    tmpOverlay.icon = mapOverlays[i].icon;
                }

                if( mapOverlays[i].flat ){
                    tmpOverlay.flat = mapOverlays[i].flat;
                }

                if( mapOverlays[i].type == "polygon" ){
                    tmpOverlay.paths = new Array();
                    paths = mapOverlays[i].getPaths();
                    for( var j=0; j < paths.length; j++ ){
                        tmpOverlay.paths[j] = new Array();
                        for( var k=0; k < paths.getAt(j).length; k++ ){
                            tmpOverlay.paths[j][k] = { lat: paths.getAt(j).getAt(k).lat().toString() , lng: paths.getAt(j).getAt(k).lng().toString() };
                        }
                    }

                }else if( mapOverlays[i].type == "polyline" ){
                    tmpOverlay.path = new Array();
                    path = mapOverlays[i].getPath();
                    for( var j=0; j < path.length; j++ ){
                        tmpOverlay.path[j] = { lat: path.getAt(j).lat().toString() , lng: path.getAt(j).lng().toString() };
                    }

                }else if( mapOverlays[i].type == "circle" ){
                    tmpOverlay.center = { lat: mapOverlays[i].getCenter().lat(), lng: mapOverlays[i].getCenter().lng() };
                    tmpOverlay.radius = mapOverlays[i].radius;
                }else if( mapOverlays[i].type == "rectangle" ){
                    tmpOverlay.bounds = {  sw: {lat: mapOverlays[i].getBounds().getSouthWest().lat(), lng: mapOverlays[i].getBounds().getSouthWest().lng()},
                        ne:     {lat: mapOverlays[i].getBounds().getNorthEast().lat(), lng: mapOverlays[i].getBounds().getNorthEast().lng()}
                    };
                }else if( mapOverlays[i].type == "marker" ){
                    tmpOverlay.position = { lat: mapOverlays[i].getPosition().lat(), lng: mapOverlays[i].getPosition().lng() };
                }
                tmpMap.overlays.push( tmpOverlay );
            }
            return tmpMap;
        }

        function setMapData( mapObj, jsonString ){

            if( jsonString.length == 0 ){
                return false;
            }
            var inputData = JSON.parse( jsonString );
            if( inputData.zoom ){
                mapObj.setZoom( inputData.zoom );
            }else{
                mapObj.setZoom( 10 );
            }

            if( inputData.tilt ){
                mapObj.setTilt( inputData.tilt );
            }else{
                mapObj.setTilt( 0 );
            }

            if( inputData.mapTypeId ){
                mapObj.setMapTypeId( inputData.mapTypeId );
            }else{
                mapObj.setMapTypeId( "hybrid" );
            }

            if( inputData.center ){
                mapObj.setCenter( new google.maps.LatLng( inputData.center.lat, inputData.center.lng ) );
            }else{
                mapObj.setCenter( new google.maps.LatLng( 19.006295, 73.309021 ) );
            }

            var tmpOverlay, ovrOptions;
            var properties = new Array( 'fillColor', 'fillOpacity', 'strokeColor', 'strokeOpacity','strokeWeight', 'icon');
            for( var m = inputData.overlays.length-1; m >= 0; m-- ){
                ovrOptions = new Object();

                for( var x=properties.length; x>=0; x-- ){
                    if( inputData.overlays[m][ properties[x] ] ){
                        ovrOptions[ properties[x] ] = inputData.overlays[m][ properties[x] ];
                    }
                }


                if( inputData.overlays[m].type == "polygon" ){

                    var tmpPaths = new Array();
                    for( var n=0; n < inputData.overlays[m].paths.length; n++ ){

                        var tmpPath = new Array();
                        for( var p=0; p < inputData.overlays[m].paths[n].length; p++ ){
                            tmpPath.push(  new google.maps.LatLng( inputData.overlays[m].paths[n][p].lat, inputData.overlays[m].paths[n][p].lng ) );
                        }
                        tmpPaths.push( tmpPath );
                    }
                    ovrOptions.paths = tmpPaths;
                    tmpOverlay = new google.maps.Polygon( ovrOptions );

                }else if( inputData.overlays[m].type == "polyline" ){

                    var tmpPath = new Array();
                    for( var p=0; p < inputData.overlays[m].path.length; p++ ){
                        tmpPath.push(  new google.maps.LatLng( inputData.overlays[m].path[p].lat, inputData.overlays[m].path[p].lng ) );
                    }
                    ovrOptions.path = tmpPath;
                    tmpOverlay = new google.maps.Polyline( ovrOptions );

                }else if( inputData.overlays[m].type == "rectangle" ){
                    var tmpBounds = new google.maps.LatLngBounds(
                        new google.maps.LatLng( inputData.overlays[m].bounds.sw.lat, inputData.overlays[m].bounds.sw.lng ),
                        new google.maps.LatLng( inputData.overlays[m].bounds.ne.lat, inputData.overlays[m].bounds.ne.lng ) );
                    ovrOptions.bounds = tmpBounds;
                    tmpOverlay = new google.maps.Rectangle( ovrOptions );

                }else if( inputData.overlays[m].type == "circle" ){
                    var cntr = new google.maps.LatLng( inputData.overlays[m].center.lat, inputData.overlays[m].center.lng );
                    ovrOptions.center = cntr;
                    ovrOptions.radius = inputData.overlays[m].radius;
                    tmpOverlay = new google.maps.Circle( ovrOptions );

                }else if( inputData.overlays[m].type == "marker" ){
                    var pos = new google.maps.LatLng( inputData.overlays[m].position.lat, inputData.overlays[m].position.lng );
                    ovrOptions.position = pos;
                    if( inputData.overlays[m].icon ){
                        ovrOptions.icon = inputData.overlays[m].icon ;
                    }
                    if( typeof isEditable != 'undefined' && isEditable ){
                        ovrOptions.draggable =true;
                    }
                    tmpOverlay = new google.maps.Marker( ovrOptions );

                }
                tmpOverlay.type = inputData.overlays[m].type;
                tmpOverlay.setMap( mapObj );
                if( typeof isEditable != 'undefined' && isEditable && inputData.overlays[m].type != "marker"){
                    tmpOverlay.setEditable( true );

                }

                var uniqueid =  uniqid();
                tmpOverlay.uniqueid =  uniqueid;
                if( inputData.overlays[m].title ){
                    tmpOverlay.title = inputData.overlays[m].title;
                }else{
                    tmpOverlay.title = "";
                }

                if( inputData.overlays[m].content ){
                    tmpOverlay.content = inputData.overlays[m].content;
                }else{
                    tmpOverlay.content = "";
                }

                //attach the click listener to the overlay
                if(typeof AttachClickListener !== 'undefined')
                    AttachClickListener( tmpOverlay );

                //save the overlay in the array
                mapOverlays.push( tmpOverlay );

            }

        }
        <?php
        $this->view->headScript()->captureEnd();

        $this->view->headStyle()->captureStart();
        ?>
        #map-canvas-<?=$rand;?> {
            width: <?=(strpos($this->width,'%')===false) ? ($this->width.'px') : $this->width;?>;
            height: <?=$this->height?>px;
        }
        <?php
        $this->view->headStyle()->captureEnd();

        return '<div id="map-canvas-'.$rand.'"></div>';

    }

}