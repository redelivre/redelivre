<?php

require_once('../../../../wp-load.php');

global $wp_registered_widgets, $wp_registered_widget_controls, $wp_registered_widget_updates;

switch ($_POST['action']) {
    case 'save_widget_options':      
        $id = $_POST['widget-id'];
        $number = $_POST['widget-number'];
        $canvas_id = $_POST['canvas-id'];
        $id_base = $_POST['id_base'];
        
        $option_name = 'widget-'.$id_base;
        
        if ( class_exists($id) ) {
        // Saving Multi Widgets
            $newOptions = $_POST[$option_name][$number];
            $options = get_option('eletro_widgets');
            
            if (is_array( $options[$canvas_id]['widgets_options'][$id] )) {
                $oldOptions = $options[$canvas_id]['widgets_options'][$id][$number];
            } else {
                $oldOptions = array();
            }
            
            $newWidget = new $id;
            $newWidget->_set($number);
            $newOptions = stripslashes_deep($newOptions);
            $newOptions = $newWidget->update($newOptions, $oldOptions);
            
            if (is_array( $options[$canvas_id]['widgets_options'][$id] )) {
                $options[$canvas_id]['widgets_options'][$id][$number] = $newOptions;
            } else {
                $options[$canvas_id]['widgets_options'][$id] = array($number => $newOptions);
            }
            update_option('eletro_widgets', $options);
            
        } else {
        // Saving single Widgets
            $callbackControl = $wp_registered_widget_controls[$id]['callback'];
            if ( is_callable($callbackControl) ) 
                call_user_func_array($callbackControl, '');
        }
        
        break;
        
    case 'add':
    
    	$widget_id = $_POST['widget_id'];
        $id_base = $_POST['id_base'];
        $refresh = array_key_exists('refresh', $_POST) && $_POST['refresh'] ? true : false;
        $widget_number = $_POST['widget_number'];
        $canvas_id = $_POST['canvas_id'];
        
        if (!$refresh) {
            $options = get_option('eletro_widgets');
            if ( isset($options[$canvas_id]['widgets_options'][$widget_id]['last_number']) && is_int($options[$canvas_id]['widgets_options'][$widget_id]['last_number']) ) {
                $options[$canvas_id]['widgets_options'][$widget_id]['last_number'] ++;
            } else {
                $options[$canvas_id]['widgets_options'][$widget_id]['last_number'] = 1;
            }
            update_option('eletro_widgets', $options);
        }
        print_eletro_widgets($widget_id, $widget_number, $id_base, $canvas_id, $refresh);
        break;
        
    case 'save':
    
    	$canvas_id = $_POST['id'];
        
        $theOptions = get_option('eletro_widgets');
        $options = array();
        $values = $_POST['value'];

        if (is_array($values)) {
            foreach ($values as $col => $ws) {
                $options[$col] = array();
                $items = explode(',', $ws);
                $i = 0;
                foreach ($items as $widget) {
                   	$w = explode('X|X', $widget);
                   	$options[$col][$i]['id'] = $w[0];
                   	$options[$col][$i]['number'] = $w[1];
                    $options[$col][$i]['id_base'] = $w[2];
                 	$i ++;
                }
            }
        }
        
        $theOptions[$canvas_id]['widgets'] = $options;
        update_option('eletro_widgets', $theOptions);   
        
        break;
        
    case 'apply' :
    
        $canvas_id = $_POST['canvas_id'];
        $adminOptions = get_option('eletro_widgets');
        $publicOptions = get_option('eletro_widgets_public');
        
        $publicOptions[$canvas_id] = $adminOptions[$canvas_id];
        
        update_option('eletro_widgets_public', $publicOptions);
        
        // if using wp-super-cache, clean the cache
        if (function_exists('wp_cache_clean_cache')) wp_cache_clean_cache('wp-cache');

        $lastOptions = eletrowidgets_get_last_options($canvas_id);

        if (json_decode($lastOptions, true) !== $publicOptions) {
            eletrowidgets_insert_into_history($publicOptions, $canvas_id);
        }
        
        break;
        
    case 'restore' :
    
        $canvas_id = $_POST['canvas_id'];
        $adminOptions = get_option('eletro_widgets');
        $publicOptions = get_option('eletro_widgets_public');
        
        $adminOptions[$canvas_id] = $publicOptions[$canvas_id];
        
        update_option('eletro_widgets', $adminOptions);
        
        break;
    
    case 'get_history':
        $canvas = $_POST['canvas_id'];
        $offset = (int) $_POST['offset'];
        $limit = (int) $_POST['limit'];

        $history = eletrowidgets_get_history($canvas, $offset, $limit);

        echo json_encode($history);

        break;
}

function eletrowidgets_get_history($canvas, $offset, $limit) {
    global $wpdb;

    $table = $wpdb->prefix . 'eletro_widgets_history';

    $query = "SELECT id, date FROM $table "
        . 'WHERE CANVAS = %d '
        . 'ORDER BY ID DESC ';
    if ($limit > 0) {
        $query .= "LIMIT $limit ";
    }
    if ($offset > 0) {
        $query .= "OFFSET $offset ";
    }
    $query = $wpdb->prepare($query, $canvas);

    $history = array();
    for ($i = 0; $row = $wpdb->get_row($query, ARRAY_A, $i); $i++) {
        $history[$row['id']] = $row['date'];
    }

    return $history;
}

function eletrowidgets_insert_into_history($options, $canvas) {
    global $wpdb;

    $table = $wpdb->prefix . 'eletro_widgets_history';
    $data = array('data' => json_encode($options),
                'canvas' => $canvas);

    $wpdb->insert($table, $data);
}

function eletrowidgets_get_last_options($canvas) {
    global $wpdb;

    $table = $wpdb->prefix . 'eletro_widgets_history';

    $query = $wpdb->prepare("SELECT data FROM $table "
        . 'WHERE canvas = %d '
        . 'ORDER BY ID DESC '
        . 'LIMIT 1', $canvas);

    return $wpdb->get_var($query);
}

?>
