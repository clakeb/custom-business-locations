(function($) {
    $( ".sortable" ).sortable();

    var timer;
    var geocoder = new google.maps.Geocoder();

    $(document).ready(function() {
        $(".locations .location").each(function(index) {
            var lat = $(this).find('.location-lat').val();
            var lng = $(this).find('.location-lng').val();
            var latlng = new google.maps.LatLng(lat, lng);
            var mapOptions = {
                zoom: 9,
                center: latlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                disableDefaultUI: true,
                draggable: false,
                zoomControl: false,
                scrollwheel: false,
                disableDoubleClickZoom: true
            };

            $(this).find('.map-canvas').height(400);
            var map = new google.maps.Map($(this).find('.map-canvas').get(0), mapOptions);
            map.setCenter(latlng);

            var marker = new google.maps.Marker({
                map: map,
                position: latlng
            });
        });
        setTimeout(function() {
            $('.cbl_plugin_options_wrap').foundation();
        }, 200);
    });

    $("#add-location").live( 'click', function(event) {
        event.preventDefault();
        var order = $(".locations .location").size();

        var data = {
            action: 'cbl_plugin_options',
            order: order,
            add_location: true,
        };

        $.post(ajaxurl, data, function(response) {
            $('#no-locations-alert').hide();
            $('.locations').append(response);
        });
    });

    $(".close-section").live( 'click', function(event) {
        event.preventDefault();
        $(this).closest('.location').removeClass('active' );
    });

    $(".remove-location").live( 'click', function(event) {
        event.preventDefault();
        var order = $(this).parents('.location').data('locationId');
        $(this).parents('.location').remove();
        setTimeout(function() {
            if( $(".locations .location").size() < 1 ) {
                $('#no-locations-alert').show();
            }

            var data = {
                action: 'cbl_plugin_options',
                order: order,
                remove_location: true,
            };

            $.post(ajaxurl, data, function(response) {
            });
        }, 200);
    });

    $(".location-closed").live( 'change', function() {
        if( $(this).is(':checked') ) {
            $(this).parents('.location-day').find('.location-from').val('');
            $(this).parents('.location-day').find('.location-to').val('');
            $(this).parents('.location-day').find('.location-from').attr('readonly', true);
            $(this).parents('.location-day').find('.location-to').attr('readonly', true);
        } else {
            $(this).parents('.location-day').find('.location-from').val('12:00');
            $(this).parents('.location-day').find('.location-to').val('12:00');
            $(this).parents('.location-day').find('.location-from').attr('readonly', true);
            $(this).parents('.location-day').find('.location-to').attr('readonly', true);
        }
    });

    $(".location-title").live( 'keyup', function() {
        $(this).parents('.location').find('.title a').html($(this).val());
    });

    $(".location-address").live( 'keyup', function() {
        if ( $(this).val().length < 4 ) {
            return;
        }

        var address = $(this);
        window.clearTimeout(timer);
        timer = window.setTimeout(function() {
            geocoder.geocode( { 'address': address.val()}, function(results, status) {
                var suggestions = '';
                if (status === google.maps.GeocoderStatus.OK) {
                    var count = 0;
                    $.each(results, function(key, value) {
                        if( count < 5 ) {
                            suggestions += '<p><a class="search-result panel phone-12 columns" href="#" data-address="' + value.formatted_address + '" data-lng="' + value.geometry.location.lng() + '" data-lat="' + value.geometry.location.lat() + '">' + value.formatted_address + '</a></p>';
                        } else {
                            return;
                        }
                        count++;
                    });
                } else {
                    suggestions = "";
                }
                address.siblings('.address-search-results').show().html(suggestions);
            });
        }, 500);
    });

    $(".search-result").live( 'click', function(event) {
        event.preventDefault();
        var lat = $(this).data("lat");
        var lng = $(this).data("lng");
        var wrap = $(this).parents('.address-wrap');
        var formatted_address = $(this).data("address");
        wrap.find('.location-address').val( formatted_address );
        wrap.find('.location-latlng').val( lat + ', ' + lng );
        wrap.find('.location-lat').val( lat );
        wrap.find('.location-lng').val( lng );
        wrap.find('.map-canvas').height(400);
        $(this).parents('.address-search-results').html('').hide();

        var latlng = new google.maps.LatLng(lat, lng);
        var mapOptions = {
            zoom: 9,
            center: latlng,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            disableDefaultUI: true,
            draggable: false,
            zoomControl: false,
            scrollwheel: false,
            disableDoubleClickZoom: true
        };

        var map = new google.maps.Map(wrap.find('.map-canvas').get(0), mapOptions);
        map.setCenter(latlng);

        var marker = new google.maps.Marker({
            map: map,
            position: latlng
        });
    });

})( jQuery );