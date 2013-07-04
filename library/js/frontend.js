(function($) {
    $('.cbl-shortcode-wrap').foundation();

    var map ;
    var markers = {};
    var infowindows = {};
    var latlngbounds = new google.maps.LatLngBounds();

    $(document).ready(function() {
        $('#cbl-map-canvas').height( $('.cbl-locations').height() );

        var mapOptions = {
            zoom: 6,
            center: new google.maps.LatLng(41.850033, -87.6500523),
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            scrollwheel: false,
        };

        map = new google.maps.Map($('#cbl-map-canvas').get(0), mapOptions);

        function panToCbl( id, map, marker, infowindow ) {
            return function() {
                infowindow.open( map, marker );
            };
        }

        $('.cbl-location').each(function(index) {
            var id = $(this).data('locationId');
            var title = $(this).data('title');
            var address = $(this).data('address');
            var phone = $(this).data('phone');
            var lat = $(this).data('lat');
            var lng = $(this).data('lng');
            var latlng = new google.maps.LatLng( lat, lng );

            latlngbounds.extend( latlng );

            markers[ id ] = new google.maps.Marker({
                position: latlng,
                map: map,
            });

            $(markers[ id ] ).data('locationId', id);

            infowindows[ id ] = new google.maps.InfoWindow({
                content: '<div class="cbl-infowindow"><h4>' + title + '</h4><p>' + address + '</br>' + phone + '</p></div>'
            });

            google.maps.event.addListener( markers[ id ], 'click', function() {
                infowindows[ id ].open( map, markers[ id ] );
                $('.cbl-location').removeClass('active');
                $('.cbl-location-' + id).addClass('active');
                $('#cbl-map-canvas').height($('.cbl-locations').height());
            });

            map.fitBounds( latlngbounds );
        });


        $('.cbl-location').live( 'click', function() {
            var id = $(this).data('locationId');
            infowindows[ id ].open( map, markers[ id ] );
            $('#cbl-map-canvas').height($('.cbl-locations').height());
        });

        google.maps.event.addListener(map, 'zoom_changed', function() {
            zoomChangeBoundsListener =
                google.maps.event.addListener(map, 'bounds_changed', function(event) {
                    if (this.getZoom() > 15 && this.initialZoom === true) {
                        // Change max/min zoom here
                        this.setZoom(11);
                        this.initialZoom = false;
                    }
                    google.maps.event.removeListener(zoomChangeBoundsListener);
                });
        });
        map.initialZoom = true;
        map.fitBounds( latlngbounds );
    });
})( jQuery );