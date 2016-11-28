<?php
class Ia_View_Helper_GoogleMap extends Zend_View_Helper_Abstract
{

    public $latitude = null;

    public $longitude = null;

    public $zoom = 22;

    public $enableDrawingManager = false;

    public $encodeOverlayJsonTargetId = false;

    public $mapTypeId = 'roadmap';

    public $width = 500;

    public $height = 500;

    public $markers = array();

    public function googleMap($options=array())
    {
        $this->markers = (isset($options['markers']) ? $options['markers'] : array());
        if(isset($options['width']))
            $this->width = $options['width'];
        if(isset($options['height']))
            $this->width = $options['height'];

        $this->view->headScript()->captureStart();
        ?>
        var map;
        var allMarkers = [];

        function initialize() {

            var bounds = new google.maps.LatLngBounds();

            var mapOptions = {
                mapTypeId: '<?=$this->mapTypeId?>',
                zoom: <?=$this->zoom?>,
            };

            map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions); 

            <?php if(sizeof($this->markers)>0): 
                $i=0;
                ?>
                <?php foreach($this->markers as $marker): 
                    $i++;
                    ?>
                    var myLatlng<?=$i;?> = new google.maps.LatLng(<?=$marker['lat'];?>,<?=$marker['lon'];?>);
                    var marker<?=$i;?> = new google.maps.Marker({
                        position: myLatlng<?=$i;?>,
                        map: map,
                        title: '<?=$marker['title'];?>'
                    });
                    <?php if($marker['content']): ?>
                    var infoWindow<?=$i;?> = new google.maps.InfoWindow({
                        content: '<?=$marker['content'];?>'
                    });
                    google.maps.event.addListener(marker<?=$i;?>, 'click', function() {
                        infoWindow<?=$i;?>.open(map,marker<?=$i;?>);
                    });                    
                    <?php endif; ?>
                    bounds.extend(marker<?=$i;?>.position);
                    allMarkers.push(marker<?=$i;?>);
                    <?php
                endforeach;
                ?>
                map.fitBounds(bounds);               
                <?php
            endif;
            ?>
        }

        google.maps.event.addDomListener(window, 'load', initialize);

        <?php
        $this->view->headScript()->captureEnd();

        $this->view->headStyle()->captureStart();
        ?>
        #map-canvas {
        width: <?=$this->width?>px;
        height: <?=$this->height?>px;
        }
        <?php
        $this->view->headStyle()->captureEnd();

        return '<div id="map-canvas"></div>';

    }

}