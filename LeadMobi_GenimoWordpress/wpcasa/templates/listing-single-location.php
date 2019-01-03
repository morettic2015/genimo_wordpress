<?php
/**
 * Template: Single Listing Location
 */
global $listing;

$lat = get_post_meta($listing->ID, '_geolocation_lat', true);
$long = get_post_meta($listing->ID, '_geolocation_long', true);

$hide = get_post_meta($listing->ID, '_map_hide', true);

if (!$hide) {//Hide map
    ?>
    <!--<div class="wpsight-listing-description" itemprop="description">
    <?php
    echo get_post_meta($listing->ID, '_map_address', true);

//echo apply_filters( 'wpsight_listing_description', wpsight_format_content( $listing->post_content ) ); 
    ?>
    </div>-->
    <?php
}

if ($lat && $long && !$hide) {
    ?>

    <div class="wpsight-listing-section wpsight-listing-section-location">

        <style>
            #map-canvas {
                width: 100%;
                height: 400px;
            }
            #map-canvas img {
                max-width: none;
            }
        </style>
        <?php
        // Set map default options

        $map_defaults = array(
            'map_type' => 'ROADMAP',
            'control_type' => 'true',
            'control_nav' => 'true',
            'scrollwheel' => 'false',
            'streetview' => 'true',
            'map_zoom' => '14'
        );

        // Get map listing options

        $map_options = array(
            '_map_type' => get_post_meta($listing->ID, '_map_type', true),
            '_map_zoom' => get_post_meta($listing->ID, '_map_zoom', true),
            '_map_no_streetview' => get_post_meta($listing->ID, '_map_no_streetview', true)
        );

        $map_args = array(
            'map_type' => !empty($map_options['_map_type']) ? $map_options['_map_type'] : $map_defaults['map_type'],
            'map_zoom' => !empty($map_options['_map_zoom']) ? $map_options['_map_zoom'] : $map_defaults['map_zoom'],
            'streetview' => !empty($map_options['_map_no_streetview']) ? 'false' : 'true'
        );

        // Parse map args and apply filter		    
        $map_args = apply_filters('wpsight_listing_map_args', wp_parse_args($map_args, $map_defaults));
        ?>
        <script>
            var map;
            function getBaseUrl() {
                var getUrl = window.location;
                var baseUrl = getUrl.protocol + "//" + getUrl.host + "/";
                //alert(baseUrl);
                return baseUrl;
            }
            var lImages = {
                infraestrutura: getBaseUrl() + "/wp-content/plugins/wpcasa-listings-map/assets/images/infraestrutura.png",
                saude: getBaseUrl() + "/wp-content/plugins/wpcasa-listings-map/assets/images/saude.png",
                cultura: getBaseUrl() + "/wp-content/plugins/wpcasa-listings-map/assets/images/cultura.png",
                educacao: getBaseUrl() + "/wp-content/plugins/wpcasa-listings-map/assets/images/educacao.png",
                shop: getBaseUrl() + "/wp-content/plugins/wpcasa-listings-map/assets/images/compras.png",
                esporte: getBaseUrl() + "/wp-content/plugins/wpcasa-listings-map/assets/images/esportes.png",
                transporte: getBaseUrl() + "/wp-content/plugins/wpcasa-listings-map/assets/images/transporte.png",
                seguranca: getBaseUrl() + "/wp-content/plugins/wpcasa-listings-map/assets/images/seguranca.png",
                alimentacao: getBaseUrl() + "/wp-content/plugins/wpcasa-listings-map/assets/images/alimentacao.png"
            }
            function initialize() {
                var myLatlng = new google.maps.LatLng(<?php echo $lat; ?>,<?php echo $long; ?>);
                var mapOptions = {
                    zoom: <?php echo $map_args['map_zoom']; ?>,
                    mapTypeId: google.maps.MapTypeId.<?php echo $map_args['map_type']; ?>,
                    mapTypeControl: <?php echo $map_args['control_type']; ?>,
                    navigationControl: <?php echo $map_args['control_nav']; ?>,
                    scrollwheel: <?php echo $map_args['scrollwheel']; ?>,
                    streetViewControl: <?php echo $map_args['streetview']; ?>,
                    center: myLatlng
                }
                map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
                loadCitywatch(<?php echo $lat; ?>,<?php echo $long; ?>);
                var marker = new google.maps.Marker({
                    position: myLatlng,
                    map: map,
                    title: '<?php echo esc_attr($listing->post_title); ?>'
                });
            }
            function loadCitywatch(mediaLat, mediaLon) {
                localStorage.setItem("mediaLat", mediaLat);
                localStorage.setItem("mediaLon", mediaLon);
                //alert(mediaLat + "/" + mediaLon);
                var postTo = "https://gaeloginendpoint.appspot.com/infosegcontroller.exec?action=50&lat=" + localStorage.getItem("mediaLat") + "&lon=" + localStorage.getItem("mediaLon");
                //NULL
                //

                // if (localStorage.getItem("push") == undefined || localStorage.getItem("push") == null) {
                jQuery.ajax({
                    url: postTo,
                    dataType: "jsonp",
                    cache: true,
                    method: "GET",
                    jsonp: 'callback',
                    jsonpCallback: 'LISTINGS_MAP',
                    success: function (data) {
                        var lista = data.results;
                        for (i = 0; i < lista.length; i++) {
                            var mIcon1 = "";
                            if (lista[i].tipo == "SAUDE") {
                                mIcon1 = lImages.saude;
                            } else if (lista[i].tipo == "EDUCACAO") {
                                mIcon1 = lImages.educacao;
                            } else if (lista[i].tipo == "INFRAESTRUTURA") {
                                mIcon1 = lImages.infraestrutura;
                            } else if (lista[i].tipo == "ESPORTE") {
                                mIcon1 = lImages.esporte;
                            } else if (lista[i].tipo == "TRANSPORTE") {
                                mIcon1 = lImages.transporte;
                            } else if (lista[i].tipo == "CULTURA") {
                                mIcon1 = lImages.cultura;
                            } else if (lista[i].tipo == "ALIMENTACAO") {
                                mIcon1 = lImages.alimentacao;
                            } else if (lista[i].tipo == "SEGURANCA") {
                                mIcon1 = lImages.seguranca;
                            }
                            var newMarker = new google.maps.Marker({
                                // Rollover text. Only applies to point geometries
                                title: lista[i].tit + "/" + lista[i].tipo,
                                // Marker position. Required.
                                position: new google.maps.LatLng(parseFloat(lista[i].lat), parseFloat(lista[i].lon)),
                                // Map on which to display Marker
                                map: map,
                                //label: labels[i % labels.length],
                                icon: mIcon1/** {
                                 // The URL of the image or sprite sheet.
                                 url: mIcon1 == "" ? markerOptions.icon.url : mIcon1,
                                 // The display size of the sprite or image. When using sprites, you must specify the sprite size.
                                 // If the size is not provided, it will be set when the image loads.
                                 size: new google.maps.Size(48, 48),
                                 // The position of the image within a sprite, if any. By default, the origin is located at the top left corner of the image (0, 0).
                                 origin: new google.maps.Point(parseInt(markerOptions.icon.origin[0]), parseInt(markerOptions.icon.origin[1])),
                                 // The position at which to anchor an image in correspondence to the location of the marker on the map.
                                 // By default, the anchor is located along the center point of the bottom of the image.
                                 anchor: new google.maps.Point(parseInt(markerOptions.icon.anchor[0]), parseInt(markerOptions.icon.anchor[1])),
                                 // The size of the entire image after scaling, if any. Use this property to stretch/shrink an image or a sprite.
                                 scaledSize: new google.maps.Size(parseInt(markerOptions.icon.scaledSize[0]), parseInt(markerOptions.icon.scaledSize[1]))
                                 },*/
                            });
                            // InfoBox extends the Google Maps JavaScript API V3 OverlayView class.
                            // An InfoBox behaves like a google.maps.InfoWindow, but it supports several additional properties for advanced styling. An InfoBox can also be used as a map label.
                            // Reference: https://google-maps-utility-library-v3.googlecode.com/svn/trunk/infobox/docs/reference.html
                         /*   newMarker.infobox = new InfoBox({
                                // The content of the InfoBox (plain text or an HTML DOM node).
                                content: markerOptions.infobox.content,
                                // The URL of the image representing the close box.
                                // Note: The default is the URL for Google's standard close box. Set this property to "" if no close box is required.
                                closeBoxURL: markerOptions.infobox.closeBoxURL,
                                // Minimum offset (in pixels) from the InfoBox to the map edge after an auto-pan.
                                infoBoxClearance: new google.maps.Size(40, 40),
                                // Offset of the InfoBox
                                pixelOffset: new google.maps.Size(markerOptions.infobox.pixelOffset[0], markerOptions.infobox.pixelOffset[1])

                            });*/


                            // attach event to "mouseover" (hover) on the marker
                          //  google.maps.event.addListener(newMarker, "click", markerEventHandler(markers));

                            // set the map boundary to include this marker
                            //bounds.extend(newMarker.position);
                            //pos = markers.length;
                            // push this new marker to the markers array so we can reference it later
                         //   markers[pos++] = newMarker;
                        }
                        localStorage.setItem("push", JSON.stringify(data));
                        //markerCluster = new MarkerClusterer(map, markers, mcOptions);
                    },
                    error: function (err) {
                        // myLoader.hide();
                        // alert(err);
                    }
                });
            }
            google.maps.event.addDomListener(window, 'load', initialize);

        </script>

        <div itemprop="availableAtOrFrom" itemscope itemtype="http://schema.org/Place">

            <?php do_action('wpsight_listing_single_location_before', $listing->ID); ?>

            <div class="wpsight-listing-location" itemprop="geo" itemscope itemtype="http://schema.org/GeoCoordinates">

                <div id="map-canvas"></div>

                <meta itemprop="latitude" content="<?php echo $lat; ?>" />
                <meta itemprop="longitude" content="<?php echo $long; ?>" />

                <?php if (!empty($listing->_map_note)) : ?>
                    <div class="wpsight-listing-location-note">
                        <?php echo wp_kses_post($listing->_map_note); ?>
                    </div>
                <?php endif; ?>

            </div>

            <?php do_action('wpsight_listing_single_location_after', $listing->ID); ?>

        </div>
        <p>

    </div><!-- .wpsight-listing-section -->

    <?php
} // endif $location ?>