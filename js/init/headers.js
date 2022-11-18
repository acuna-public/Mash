  function geoMap (options) {
		
		switch (options['type']) {
			
      case 'google':
        
        if (options['area2'])
				$(options['area2']).append ('<div id="' + options['area'] + '"></div>');
				
        $('#' + options['area']).css ({ 'width':options['width'], 'height':options['height'] });
        
        var
				place = new google.maps.LatLng (options['latitude'], options['longtitude']),
				mapType = '';
        
        if (!options['map_type']) options['map_type'] = 'roadmap';
        
        if (options['map_type'] == 'roadmap')
        mapType = google.maps.MapTypeId.ROADMAP;
        
        var map = new google.maps.Map (document.getElementById (options['area']), {
          
          'zoom': options['zoom'],
          'center': place,
          'mapTypeId': mapType,
          'disableDefaultUI': true,
          'streetViewControl': true,
          'zoomControl': true,
          'mapTypeControl': true,
          'panControl': false,
          'rotateControl': false,
          
        });
        
        var marker = new google.maps.Marker ({
          
          'position': place,
          'map': map,
          'clickable': false,
          'title': '',
					
        });
				
        //google.maps.event.trigger (map, 'resize');
				//map.setZoom (map.getZoom ());
				
      break;
			
		}
		
	}