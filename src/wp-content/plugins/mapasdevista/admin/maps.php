<?php

add_action('init', 'mapasdevista_save_map');

function mapasdevista_save_map() {


    if ( isset($_POST['submit_map']) ) {
        update_option('mapasdevista', $_POST['map']);

        wp_redirect(add_query_arg(array('action' => '', 'message' => 'save_success')));

    }

    

}



function mapasdevista_maps_page() {
    global $wp_post_types, $wp_taxonomies;
    ?>

    <form method="POST">
    <div class="wrap">
        <h2>Configuração do Mapa</h2>
        
        
            
            <?php if (isset($_GET['message']) && $_GET['message'] == 'save_success'): ?>
                
                <div class="updated">
                <p>
                <?php _e('Map Saved', 'mapasdevista'); ?>
                </p>
                </div>
           
            <?php endif; ?>
            
            <?php if( isset($_GET['action']) && $_GET['action'] == 'add_menu_item' ): ?>
            
                <?php 
                
                $menu = wp_get_nav_menu_object('main');
                $items = wp_get_nav_menu_items('main');
                $menuItem = null;
                
                if ($menu) {
                    foreach ($items as $item) {
                        if ($item->url == home_url('/mapa')) {
                            $menuItem = $item;
                        }
                    }
                
                    if (!$menuItem) {
                        wp_update_nav_menu_item($menu->term_taxonomy_id, 0, array(
                            'menu-item-title' => 'Mapa',
                            'menu-item-url' => home_url('/mapa'), 
                            'menu-item-status' => 'publish')
                        );
                        $msg = 'Entrada no menu inserida com sucesso!';
                    } else {
                        $msg = 'Já existe este item no menu!';
                    }
                }
                
                ?>
                
                <div class="updated">
                <p>
                <?php echo $msg; ?>
                </p>
                </div>
           
            <?php endif; ?>
            

            <?php
            $map = get_option('mapasdevista', true);

            if (!is_array($map))
                $map = array();
            if (!(isset($map['post_types']) && is_array($map['post_types'])))
                $map['post_types'] = array();
            if (!(isset($map['taxonomies']) && is_array($map['taxonomies'])))
                $map['taxonomies'] = array();
            if (!(isset($map['filters']) && is_array($map['filters'])))
                $map['filters'] = array();

            ?>
            
            <div class="updated">
            
                <p>
                
                <h3>Visibilidade do Mapa:</h3>

                <input type="radio" name="map[visibility]" value="private" id="map_visibility_private" <?php if($map['visibility'] == 'private' || !isset($map['visibility'])) echo 'checked'; ?> /> <b><label for="map_visibility_private">Privado</label></b> - Apenas usuários logados, com permissão de edição neste site poderão ver o mapa.
                <br /><br />
                <input type="radio" name="map[visibility]" value="public" id="map_visibility_public" <?php if($map['visibility'] == 'public') echo 'checked'; ?> /> <b><label for="map_visibility_public">Público</label></b> - Qualquer visitante do site poderá ver o mapa e as informações publicadas nele.
                <br /><br />
                O mapa pode ser acessado através do endereço <a href="<?php echo site_url('mapa'); ?>"><?php echo site_url('mapa'); ?></a>.
                
                Se você ainda não tiver colocado <input type="button" name="create_menu_item" value="Inserir item no menu" onClick="document.location = '<?php echo add_query_arg('action', 'add_menu_item'); ?>';" />
                
                </p>
            
            </div>
            

            
            <?php do_action('mapasdevista_maps_settings_top',$map); ?>
            
            <input type="hidden" name="original_page_id" value="<?php echo $_GET['page_id']; ?>" />

            
            <input type="hidden" name="map[name]" value="Mapa">

            <h3><?php _e('Map API', 'mapasdevista'); ?></h3>
            <ul id="mpv_map_api">
                <li>
                    <input type="radio" name="map[api]" id="mpv_api_googlev3" value="googlev3"<?php if ($map['api'] == 'googlev3') echo ' checked'; ?>>
                    <label for="mpv_api_googlev3">Google Maps</label>
                </li>
                <li>
                    <input type="radio" name="map[api]" id="mpv_api_openlayers" value="openlayers"<?php if ($map['api'] == 'openlayers') echo ' checked'; ?>>
                    <label for="mpv_api_openlayers">Open Street Maps</label>
                </li>
                <!--
                <li>
                    <input type="radio" name="map[api]" id="mpv_api_image" value="image"<?php if ($map['api'] == 'image') echo ' checked'; ?>>
                    <label for="mpv_api_image"><?php _e('Image as map', 'mapasdevista'); ?></label>
                </li>
                -->
            </ul>

            <fieldset id="mpv_map_fields">
                <h3><?php _e('Map initial state', 'mapasdevista'); ?></h3>
                
                <div id="mpv_canvas_googlev3" class="mpv_canvas" style="display:none"></div>
                <div id="mpv_canvas_openlayers" class="mpv_canvas" style="display:none"></div>
                
                <table class="map_config">
                    <tr>
                        <td>
                        <label><?php _e('Map type', 'mapasdevista'); ?></label>
                            <ul id="mpv_map_type">
                                <li>
                                    <input type="radio" name="map[type]" id="mpv_map_type_road" value="road"<?php echo $map['type']=='road'?' checked="checked"':'';?>/>
                                    <label for="mpv_map_type_road" class="small"><?php _e('Road', 'mapasdevista');?>:</label>
                                </li>
                                <li>
                                    <input type="radio" name="map[type]" id="mpv_map_type_satellite" value="satellite"<?php echo $map['type']=='satellite'?' checked="checked"':'';?>/>
                                    <label for="mpv_map_type_satellite" class="small"><?php _e('Satellite', 'mapasdevista');?>:</label>
                                </li>
                                <li>
                                    <input type="radio" name="map[type]" id="mpv_map_type_hybrid" value="hybrid"<?php echo $map['type']=='hybrid'?' checked="checked"':'';?>/>
                                    <label for="mpv_map_type_hybrid" class="small"><?php _e('Hybrid', 'mapasdevista');?>:</label>
                                </li>
                            </ul>
                        </td>
                        <td>
                        <label><?php _e('Initial position', 'mapasdevista'); ?>:</label>
                        <ul id="mpv_map_status">
                            <li>
                                <label for="mpv_lat" class="small"><?php _e('Latitude', 'mapasdevista');?>:</label>
                                <input type="text" class="small-field" name="map[coord][lat]" id="mpv_lat" value="<?php echo $map['coord']['lat'];?>"/>
                            </li>
                            <li>
                                <label for="mpv_lon" class="small"><?php _e('Longitude', 'mapasdevista');?>:</label>
                                <input type="text" class="small-field" name="map[coord][lng]" id="mpv_lng" value="<?php echo $map['coord']['lng'];?>"/>
                            </li>
                            <li>
                                <label for="mpv_zoom" class="small">Nível de zoom:</label>
                                <input type="text" class="small-field" name="map[zoom]" id="mpv_zoom" value="<?php echo $map['zoom'];?>"/>
                            </li>
                            <li><input type="button" id="mapbutton" value="Center map"/></li>
                        </ul>
                        </td>
                        <td>
                        <label><?php _e('North East limit position', 'mapasdevista'); ?>:</label>
                        <p><?php _e('Empty for no limit.', 'mapasdevista'); ?> <input type="button" value="<?php _e('Capture current position', 'mapasdevista'); ?>" id="mpv_capture_position_ne" /></p>
                        <ul id="mpv_map_status">
                            <li>
                                <label for="north_east_max_lat" class="small"><?php _e('Latitude', 'mapasdevista');?>:</label>
                                <input type="text" class="small-field" name="map[north_east][lat]" id="north_east_max_lat" value="<?php echo $map['north_east']['lat'];?>"/>
                            </li>
                            <li>
                                <label for="north_east_max_lon" class="small"><?php _e('Longitude', 'mapasdevista');?>:</label>
                                <input type="text" class="small-field" name="map[north_east][lng]" id="north_east_max_lng" value="<?php echo $map['north_east']['lng'];?>"/>
                            </li>
                            
                        </ul>
                        </td>
                        <td>
                        <label><?php _e('South West limit position', 'mapasdevista'); ?>:</label>
                        <p><?php _e('Empty for no limit.', 'mapasdevista'); ?> <input type="button" value="<?php _e('Capture current position', 'mapasdevista'); ?>" id="mpv_capture_position_sw" /></p>
                        <ul id="mpv_map_status">
                            <li>
                                <label for="south_west_max_lat" class="small"><?php _e('Latitude', 'mapasdevista');?>:</label>
                                <input type="text" class="small-field" name="map[south_west][lat]" id="south_west_max_lat" value="<?php echo $map['south_west']['lat'];?>"/>
                            </li>
                            <li>
                                <label for="south_west_max_lon" class="small"><?php _e('Longitude', 'mapasdevista');?>:</label>
                                <input type="text" class="small-field" name="map[south_west][lng]" id="south_west_max_lng" value="<?php echo $map['south_west']['lng'];?>"/>
                            </li>
                        </ul>
                        </td>
                        <td>
                        <label><?php _e('Zoom limit', 'mapasdevista'); ?>:</label>
                        <p><?php _e('Empty for no limit.', 'mapasdevista'); ?> </p>
                        <ul id="mpv_map_status">
                             <li>
                                <label for="mpv_min_zoom" class="small">mínimo:</label>
                                <input type="text" class="small-field" name="map[min_zoom]" id="mpv_min_zoom" value="<?php echo $map['min_zoom'];?>"/>
                                <input type="button" value="<?php _e('Capture current level', 'mapasdevista'); ?>" id="mpv_capture_min_zoom" />
                            </li>
                             <li>
                                <label for="mpv_max_zoom" class="small">máximo:</label>
                                <input type="text" class="small-field" name="map[max_zoom]" id="mpv_max_zoom" value="<?php echo $map['max_zoom'];?>"/>
                                <input type="button" value="<?php _e('Capture current level', 'mapasdevista'); ?>" id="mpv_capture_max_zoom" />
                            </li>
                        </ul>
                        </td>
                        
                    </tr>
                </table>
            </fieldset>

            <h3>Exibir Controles:</h3>
            <ul>
                <li><?php _e("Zoom");?>:
                        <ul style="padding-left: 30px">
                            <li><input type="radio" name="map[control][zoom]"<?php if($map['control']['zoom'] == 'large'){ echo ' checked';}?> id="mpv_control_large_zoom" value="large"/> <label for="mpv_control_large_zoom">Grande</label></li>
                            <li><input type="radio" name="map[control][zoom]"<?php if($map['control']['zoom'] == 'small'){ echo ' checked';}?> id="mpv_control_small_zoom" value="small"/> <label for="mpv_control_small_zoom">Pequeno</label></li>
                            <li><input type="radio" name="map[control][zoom]"<?php if($map['control']['zoom'] == 'none') { echo ' checked';}?> id="mpv_control_no_zoom" value="none"/> <label for="mpv_control_no_zoom">Não mostrar</label></li>
                        </ul>
                </li>
                <li><input type="checkbox" id="mpv_control_pan"<?php if(isset($map['control']['pan'])){ echo ' checked';}?> name="map[control][pan]" /> <label for="mpv_control_pan">Mover</label></li>
                <li><input type="checkbox" id="mpv_control_map_type"<?php if(isset($map['control']['map_type'])){ echo ' checked';}?> name="map[control][map_type]" /> <label for="mpv_control_map_type">Tipo de mapa (satélite ou mapa)</label></li>
            </ul>            

            <script type="text/javascript">
            (function($) {
                function fill_fields(lat, lng, zoom) {
                    $("#mpv_lat").val(lat);
                    $("#mpv_lng").val(lng);
                    $("#mpv_zoom").val(zoom);
                }

                // center map on <input/> coords. if coords
                // is invalid, center the map at hacklab ;)
                function centerMapAndZoom() {
                    try {
                        var point = new mxn.LatLonPoint(
                                        parseFloat($("#mpv_lat").val()),
                                        parseFloat($("#mpv_lng").val())
                                    );
                        mapstraction.setCenterAndZoom(point, parseInt($("#mpv_zoom").val()));
                    } catch(e) {
                        if(console && console.log) console.log(e);
                        mapstraction.setCenterAndZoom(new mxn.LatLonPoint(-23.531095, -46.673999), 16);
                        fill_fields(mapstraction.getCenter().lat, mapstraction.getCenter().lon, mapstraction.getZoom());
                    }
                }

                // set default api
                var api = 'openlayers';
                if($('#mpv_map_api input:checked').val()) {
                    api = $('#mpv_map_api input:checked').val();
                } else {
                    $('#mpv_map_api input[value='+api+']').attr('checked','checked');
                }

                $('#mpv_map_fields').attr('class',api);

                if(api === 'image') {
                    mapstraction = new mxn.Mapstraction('mpv_canvas_openlayers', 'openlayers');
                } else {
                    $('#mpv_canvas_'+api).show();
                    mapstraction = new mxn.Mapstraction('mpv_canvas_'+api, api);
                }
                centerMapAndZoom();

                mapstraction.addControls({
                    'pan': true,
                    'map_type': true,
                    'zoom': 'large'
                });

                // fill #mpv_zoom when zoom changes
                mapstraction.changeZoom.addHandler(function(n,s,a) {
                    $("#mpv_zoom").val(s.getZoom());
                });
                // fill #mpv_lat, #mpv_lon and #mpv_zoom after drag the map
                mapstraction.endPan.addHandler(function(n,s,a) {
                        fill_fields(s.getCenter().lat, s.getCenter().lon, s.getZoom());
                });
                // center map on #mpv_lat, #mpv_lon coords
                $("#mpv_map_status input[type=button]").click(function(e){centerMapAndZoom();});

                // set the default map-type. should be (road|satellite|hybrid)
                var map_type = 'road';
                try{
                    if($('#mpv_map_type input:checked').length == 1) {
                        map_type = $('#mpv_map_type input:checked').val();
                    }
                    var mxn_map_type = map_type.toUpperCase();
                    if(mxn.Mapstraction[mxn_map_type])
                        mapstraction.setMapType(mxn.Mapstraction[mxn_map_type]);
                } catch(e) { // happens when map_type=='road' and api=='openlayers'
                    $('input#mpv_map_type_'+map_type).attr('checked','checked');
                    $('#mpv_map_type input[value!='+map_type+']').attr('disabled','disabled');
                }

                // event to switch map type (road|satellite|hybrid))
                $('#mpv_map_type input').change(function(e) {
                    mxn_map_type = $(this).val().toUpperCase();
                    mapstraction.setMapType(mxn.Mapstraction[mxn_map_type]);
                });

                // event to switch api (googlev3|openlayers)
                $('#mpv_map_api input').click(function(e) {
                    if($(this).val()) {
                        api = $(this).val();
                    }
                    $('#mpv_map_fields').attr('class',api);

                    if(api === 'openlayers'){
                        mapstraction.swap('mpv_canvas_'+api, api);
                        $('input#mpv_map_type_road').attr('checked','checked');
                        $('#mpv_map_type input[value!=road]').attr('disabled','disabled');
                    } else if(api === 'googlev3') {
                        mapstraction.swap('mpv_canvas_'+api, api);
                        $('#mpv_map_type input[value!=road]').attr('disabled',false);
                    }
                    map_type = ['road','satellite','hybrid'][mapstraction.getMapType()-1];
                    $('input#mpv_map_type_'+map_type).attr('checked','checked');
                });
                
                $('#mpv_capture_position_ne').click(function() {
                    var coords = mapstraction.getCenter();
                    $('#north_east_max_lat').val(coords.lat);
                    $('#north_east_max_lng').val(coords.lng || coords.lon);
                });
                $('#mpv_capture_position_sw').click(function() {
                    var coords = mapstraction.getCenter();
                    $('#south_west_max_lat').val(coords.lat);
                    $('#south_west_max_lng').val(coords.lng || coords.lon);
                });
                $('#mpv_min_zoom').change(function() {
                    if($(this).val().match(/^[0-9]+$/) && $('#mpv_max_zoom').val().match(/^[0-9]+$/)) {
                        var cc = $('#mpv_max_zoom').css('background-color');
                        var max = parseInt($('#mpv_max_zoom').val());
                        var min = parseInt($('#mpv_min_zoom').val());
                        if(min > max) {
                            max = min;
                            $('#mpv_max_zoom').animate({backgroundColor:'#ff7f7f'},300,'linear',function(){$(this).animate({backgroundColor:cc})});
                        }
                        $('#mpv_max_zoom').val(max);
                    }
                }).blur(function(){$(this).change();});

                $('#mpv_max_zoom').change(function() {
                    if($(this).val().match(/^[0-9]+$/) && $('#mpv_min_zoom').val().match(/^[0-9]+$/)) {
                        var cc = $('#mpv_min_zoom').css('background-color');
                        var max = parseInt($('#mpv_max_zoom').val());
                        var min = parseInt($('#mpv_min_zoom').val());
                        if(min > max) {
                            min = max;
                            $('#mpv_min_zoom').animate({backgroundColor:'#ff7f7f'},300,'linear',function(){$(this).animate({backgroundColor:cc})});
                        }
                        $('#mpv_min_zoom').val(min);
                    }
                }).blur(function(){$(this).change();});

                $('#mpv_capture_min_zoom').click(function() {
                    var zoom = mapstraction.getZoom();
                    $('#mpv_min_zoom').val(zoom).change();
                });
                $('#mpv_capture_max_zoom').click(function() {
                    var zoom = mapstraction.getZoom();
                    $('#mpv_max_zoom').val(zoom).change();
                });
                
                
                
            })(jQuery);
            </script>
            
            
            <input type="<?php echo is_super_admin() ? 'text' : 'hidden' ?>" name="map[post_types][]" value="<?php echo implode(',', $map['post_types']);?>" />
            
            <input type="hidden" name="map[taxonomies][]" value="categoria-mapa" />
            
            <input type="hidden" name="map[logical_operator]" value="OR" />
            
            <?php do_action('mapasdevista_maps_settings_bottom',$map); ?>
            
            <input type="submit" name="submit_map" value="<?php _e('Save Changes', 'mapasdevista'); ?>" />

        



    </div>
    </form>

    <?php

}
