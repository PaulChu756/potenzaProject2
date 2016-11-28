(function( $ ){

  $.fn.canvasAreaDraw = function(options) {
    var args = Array.prototype.slice.call(arguments, 1);
    var result = null;
    this.each(function(index, element) {
      instance = $(element).data('canvasAreaDraw');
      if(!instance){
        $(element).data('canvasAreaDraw',init.apply(element, [index, element, options]));
      } else {
        if(typeof options === 'string') {
          if(typeof instance[options]=='undefined')
            console.log('Undefined function: ' + options);
          else
            result = instance[options].apply(instance, args);
        }
      }
    });
    return result;
  }

  var init = function(index, input, options) { 

    var thisInstance = this;
    var points, metadata, activePoint, settings, activeRegion, maxRegion, numRegions = 0;
    var $canvas, ctx, image;
    var draw, mousedown, stopdrag, move, resize, reset, rightclick, record;
    var generating = false; //used to differentiate live drawing from generating from input/passed param

    settings = $.extend({
      imageUrl: $(this).attr('data-image-url'),
      width: false,
      height: false,
      sensitivity: 6,
      rectWidth: 2,
      lineWidth: 1,
      initialRegions: 1,
      opacity: 0.5,
      defaultColor: '#ff0000',
      autoGenerate: true,
      drawingOn: true,
      regionTitleCallback: false,
    }, options);

    var drawingOn = settings.drawingOn; //flag to indicate whether or not drawing is currently enabled   

    /**
     * Private Functions
     */

    /**
     * Adjust the canvas to a new size
     */
    resize = function() {

      if(!settings.height)
        $canvas.attr('height', image.height);
      else
        $canvas.attr('height', settings.height);

      if(!settings.width)
        $canvas.attr('width', image.width);
      else
        $canvas.attr('width', settings.width);

      draw();

    };

    /**
     * Move an anchor
     */
    move = function(e) {
      if(!drawingOn){
        return;
      }
      if(!e.offsetX) {
        if(isNaN(e.pageX) || isNaN(e.pageY)){
          e.offsetX = (event.targetTouches[0].pageX - $(e.target).offset().left);
          e.offsetY = (event.targetTouches[0].pageY - $(e.target).offset().top);
        } else {
          e.offsetX = (e.pageX - $(e.target).offset().left);
          e.offsetY = (e.pageY - $(e.target).offset().top);
        }
      }
      points[activeRegion][activePoint] = Math.round(e.offsetX);
      points[activeRegion][activePoint+1] = Math.round(e.offsetY);
      draw();
    };

    /**
     * Stop moving
     */
    stopdrag = function() {
      if(!drawingOn){
        return;
      }      
      $(this).off('mousemove');
      record();
      activePoint = null;
    };

    /**
     * Right click mouse
     */
    rightclick = function(e) {    
      if(!drawingOn){
        return;
      }      
      e.preventDefault();

      if(typeof points[activeRegion]=='undefined'){
        alert('No region is selected.');
      }

      if(!e.offsetX) {
        e.offsetX = (e.pageX - $(e.target).offset().left);
        e.offsetY = (e.pageY - $(e.target).offset().top);
      }
      var x = e.offsetX, y = e.offsetY;
      for (var i = 0; i < points[activeRegion].length; i+=2) {
        dis = Math.sqrt(Math.pow(x - points[activeRegion][i], 2) + Math.pow(y - points[activeRegion][i+1], 2));
        if ( dis < 6 ) {
          points[activeRegion].splice(i, 2);
          draw();
          record();
          return false;
        }
      }
      return false;
    };

    /**
     * Click start
     */
    mousedown = function(e) {
      if(!drawingOn){
        return;
      }
      e.preventDefault();

      if(typeof points[activeRegion]=='undefined'){
        alert('No region is selected.');
      }

      var x, y, dis, lineDis, insertAt = points[activeRegion].length;

      if (e.which === 3) {
        return false;
      }

      if(!e.offsetX) {
        e.offsetX = (e.pageX - $(e.target).offset().left);
        e.offsetY = (e.pageY - $(e.target).offset().top);
      }
      x = e.offsetX; y = e.offsetY;

      if(isNaN(x) || isNaN(y)){
        x = event.targetTouches[0].pageX - $(e.target).offset().left;
        y = event.targetTouches[0].pageY - $(e.target).offset().top;
      }

      for (var i = 0; i < points[activeRegion].length; i+=2) {
        dis = Math.sqrt(Math.pow(x - points[activeRegion][i], 2) + Math.pow(y - points[activeRegion][i+1], 2));
        // this determines sensitivity
        if ( dis < settings.sensitivity ) {
          activePoint = i;
          $(this).on('touchmove mousemove', move);
          return false;
        }
      }

      for (var i = 0; i < points[activeRegion].length; i+=2) {
        if (i > 1) {
          lineDis = dotLineLength(
            x, y,
            points[activeRegion][i], points[activeRegion][i+1],
            points[activeRegion][i-2], points[activeRegion][i-1],
            true
          );
          if (lineDis < 6) {
            insertAt = i;
          }
        }
      }

      points[activeRegion].splice(insertAt, 0, Math.round(x), Math.round(y));
      activePoint = insertAt;
      $(this).on('mousemove', move);

      draw();
      record();

      return false;
    };

    /**
     * Refreshes entire canvas
     */
    draw = function() {

      record();

      ctx.canvas.width = ctx.canvas.width;

      ctx.globalCompositeOperation = 'destination-over';

      ctx.lineWidth = settings.lineWidth;
      var rectWidth = settings.rectWidth;

      for(region_id in points){

        rgb = false;
        if(typeof(metadata[region_id])!='undefined' && typeof(metadata[region_id]['color']!='undefined')){
          var rgb = hexToRgb(metadata[region_id]['color']);
        } else {
          var rgb = hexToRgb(settings.defaultColor);
        }
        if(rgb){
          ctx.fillStyle = 'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',' + settings.opacity + ')';
          ctx.strokeStyle = 'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',1)';
        } else {
          ctx.fillStyle = 'rgba(255,0,0,0.3)';
          ctx.strokeStyle = 'rgba(255,0,0,1)';
        }

        if (points[region_id].length > 0) {
          ctx.beginPath();
          ctx.moveTo(points[region_id][0], points[region_id][1]);
          for (var i = 0; i < points[region_id].length; i+=2) {
            ctx.fillRect(points[region_id][i]-rectWidth, points[region_id][i+1]-rectWidth, (rectWidth * 2), (rectWidth * 2));
            ctx.strokeRect(points[region_id][i]-rectWidth, points[region_id][i+1]-rectWidth, (rectWidth * 2), (rectWidth * 2));
            if (points[region_id].length > 2 && i > 1) {
              ctx.lineTo(points[region_id][i], points[region_id][i+1]);
            }
          }
          ctx.closePath();
          ctx.fill();

          var centerCoords = center(points[region_id]);
          ctx.fillStyle = 'rgba(255,255,255,1)';
          ctx.font="30px Arial";
          ctx.textAlign = "center";
          ctx.textBaseline = "top";       
          if(settings.regionTitleCallback)
            ctx.fillText(settings.regionTitleCallback(region_id), centerCoords[0], centerCoords[1]);
          else
            ctx.fillText('(' + region_id + ')', centerCoords[0], centerCoords[1]);

          if(region_id==activeRegion)
            ctx.stroke();
        }
      }

      $(input).trigger({
        type: "drawingFinished",
      });      

    };

    center = function(arr){
        var minX, maxX, minY, maxY;
        for (var i = 0; i < arr.length; i+=2) {
            minX = (arr[i] < minX || minX == null) ? arr[i] : minX;
            maxX = (arr[i] > maxX || maxX == null) ? arr[i] : maxX;
            minY = (arr[i+1] < minY || minY == null) ? arr[i+1] : minY;
            maxY = (arr[i+1] > maxY || maxY == null) ? arr[i+1] : maxY;
        }
        return [(minX + maxX) /2, (minY + maxY) /2];
    }

    /**
     * Store data in textarea for redrawing
     */
    record = function() { 
      var allData = {'points' : points, 'metadata' : metadata};
      $(input).val(JSON.stringify(allData));
      $(input).trigger({
        type: "pointDataChange",
        points: points,
        metadata: metadata
      });
    }; 

    /**
     * Public methods
     */

    this.getDrawingOn = function(){
        return drawingOn;
    }

     /**
      * Set drawing on flag
      */
    this.setDrawingOn = function(setDrawingOn){
        drawingOn = setDrawingOn;
    }

     /**
      * Get all point and metadata
      */
    this.getPointData = function(){
        var allData = {'points' : points, 'metadata' : metadata};
        return allData;
     }

     /**
      * Set Region Color
      */
     this.setRegionColor = function(region_id,color_code) {
      metadata[region_id]['color'] = color_code;
      $(input).trigger({
        type: "regionColorSet",
        region_id : region_id,
        color_code : color_code
      }); 
      draw();
     }

     /**
      * Get Region Color
      */
     this.getRegionColor = function(region_id) {
      if(typeof(metadata[region_id])!='undefined' && metadata[region_id]['color'] != 'undefined'){
        return metadata[region_id]['color'];
      } else {
        return settings.defaultColor;
      }
     }

     /** 
      * Get Metadata about a region
      */
     this.getMetadata = function(region_id) {
      if(typeof(points[region_id])=='undefined'){
        console.log('getMetadata:: No region matching region_id: ' + region_id);
      } else {
        if(typeof(metadata[region_id])=='undefined'){
          return {};
        } else {
          return metadata[region_id];
        }
      }
     }

     /** 
      * Get Metadata about a region
      */
     this.setMetadata = function(region_id,metadataobj) {
      if(typeof(points[region_id])=='undefined'){
        console.log('setMetadata:: No region matching region_id: ' + region_id);
      } else {
        metadata[region_id] = metadataobj;
        $(input).trigger({
          type: "metadataAdded",
          region_id : region_id,
          metadata : metadataobj
        });
        draw();
      }
     }

    /**
     * Add region
     * Returns region id
     */
    this.addRegion = function(startingMetadata) {
      numRegions++;
      maxRegion++;
      if(typeof points[maxRegion] == 'undefined'){
        points[maxRegion] = [];
        metadata[maxRegion] = (typeof startingMetadata=='undefined') ? {} : startingMetadata;
      }
      $(input).trigger({
        type: "regionAdded",
        region_id : maxRegion
      }); 
      thisInstance.selectRegion(maxRegion);
      draw();
      return maxRegion;
    }

    /**
     * Removes region by id
     */
    this.removeRegion = function(region_id) {
      if(typeof(points[region_id])=='undefined'){
        console.log('removeRegion:: No region matching region_id: ' + region_id);
      } else {
        var newPoints = {};
        var newMetadata = {};
        for(i in points){
          if(region_id!=i){
            newPoints[i] = points[i];
            newMetadata[i] = metadata[i];
          }
        }
        if(region_id==activeRegion){
          activeRegion = null;
          activePoint = null;
        }
        points = newPoints;
        metadata = newMetadata;
        $(input).trigger({
          type: "regionRemoved",
          region_id : region_id
        });
        draw();
      }
    }

    /**
     * Some applications may wish to renumber regions, i.e. after a region is removed
     */
    this.renumberRegions = function() {
      var j = 0;
      var numRegions = 0;
      var newPoints = {};
      var newMetadata = {};
      maxRegion = (j - 1);
      for(i in points){
        maxRegion = j;
        numRegions++;
        newPoints[j] = points[i];
        newMetadata[j] = metadata[i];
        j++;
      }
      points = newPoints;
      metadata = newMetadata;
      $(input).trigger({
        type: "regionsRenumbered",
      });
      draw();
    }

    /**
     * Selects an existing region
     */
    this.selectRegion = function(region_id) {
      if(typeof(points[region_id])=='undefined'){
        console.log('selectRegion:: No region matching region_id: ' + region_id);
      } else {
        activeRegion = region_id;
        activePoint = null;
        $(input).trigger({
          type: "regionSelected",
          region_id : region_id
        }); 
        draw();
      }
    }

    this.getRegionIds = function() {
      var region_ids = [];
      for(i in points){
        region_ids.push(i);
      }
      return region_ids;

    }

    this.getNumberOfRegions = function() {
      var numRegions = 0;
      for(i in points){
        numRegions++;
      }
      return numRegions;
    }

    this.getActiveRegion = function() {
      return activeRegion;
    }

    /**
     * Resets/removes all regions and data
     */
    this.reset = function() {
      points = {};
      metadata = {};
      activeRegion = 0;
      maxRegion = (-1);
      numRegions = 0;
      for(i=0;i<settings.initialRegions;i++)
        thisInstance.addRegion();
      if(settings.noRegionsSelector && numRegions==0){
        $(settings.noRegionsSelector).show();
      }        
      draw();
    };

    this.isGenerating = function()
    {
      return generating;
    }

    /**
     * Resets/removes all regions and data
     */
    this.generate = function() {
      generating = true;
      if(Object.keys(points).length > 0)
        for(region in points)
          thisInstance.addRegion();
      else
        for(i=0;i<settings.initialRegions;i++)
          thisInstance.addRegion(); 

      if(settings.noRegionsSelector && numRegions==0){
        $(settings.noRegionsSelector).show();
      }  

      $(input).after($canvas);
      $canvas.on('mousedown touchstart', mousedown);
      $canvas.on('contextmenu', rightclick);
      $canvas.on('mouseup touchend', stopdrag);
      generating = false;
    };

    /**
     * Setup 
     */

    $canvas = $('<canvas>');

    ctx = $canvas[0].getContext('2d');

    points = {};
    metadata = {};

    if ( $(this).val().length ) {
      allData = JSON.parse($(this).val());
      points = allData.points;
      metadata = allData.metadata;
    } else if (settings.points) {
      allData = settings.points;
      points = allData.points;
      metadata = allData.metadata;
      $(this).val(JSON.stringify(allData));
    }

    activeRegion = 0;

    maxRegion = (-1);

    image = new Image();

    $(image).load(resize);
    image.src = settings.imageUrl;
    if (image.loaded) resize();
    $canvas.css('background-image','url("'+image.src+'")');

    $(document).ready( function() {
      if(settings.autoGenerate){
        thisInstance.generate();
      }
    });

    $(input).on('change', function() {
      if ( $(this).val().length ) {
        points[activeRegion] = $(this).val().split(',').map(function(point) {
          return parseInt(point, 10);
        });
      } else {
        points[activeRegion] = [];
      }
      draw();
    });   

    return this; //end init

  };

  $(document).ready(function() {
    $('.canvas-area[data-image-url]').canvasAreaDraw();
  });

  var dotLineLength = function(x, y, x0, y0, x1, y1, o) {
    function lineLength(x, y, x0, y0){
      return Math.sqrt((x -= x0) * x + (y -= y0) * y);
    }
    if(o && !(o = function(x, y, x0, y0, x1, y1){
      if(!(x1 - x0)) return {x: x0, y: y};
      else if(!(y1 - y0)) return {x: x, y: y0};
      var left, tg = -1 / ((y1 - y0) / (x1 - x0));
      return {x: left = (x1 * (x * tg - y + y0) + x0 * (x * - tg + y - y1)) / (tg * (x1 - x0) + y0 - y1), y: tg * left - tg * x + y};
    }(x, y, x0, y0, x1, y1), o.x >= Math.min(x0, x1) && o.x <= Math.max(x0, x1) && o.y >= Math.min(y0, y1) && o.y <= Math.max(y0, y1))){
      var l1 = lineLength(x, y, x0, y0), l2 = lineLength(x, y, x1, y1);
      return l1 > l2 ? l2 : l1;
    }
    else {
      var a = y0 - y1, b = x1 - x0, c = x0 * y1 - y0 * x1;
      return Math.abs(a * x + b * y + c) / Math.sqrt(a * a + b * b);
    }
  };

  var hexToRgb = function(hex) {
      var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
      return result ? {
          r: parseInt(result[1], 16),
          g: parseInt(result[2], 16),
          b: parseInt(result[3], 16)
      } : null;
  }

})( jQuery );