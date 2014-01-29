<?php
/*
Plugin Name: Eletro Widgets
Plugin URI: http://eletrowidgets.hacklab.com.br
Description: Allows you to use the power and flexibility of the WordPress Widgets to set up a dynamic area anywhere in your site and manage multiple columns of widgets, dragging and dropping them around
Author: HackLab
Version: 1.0.1


*/

///// PLUGIN PATH ///////////

define('EW_ABSPATH', WP_CONTENT_DIR.'/plugins/'.plugin_basename( dirname(__FILE__)).'/' );
if(function_exists('domain_mapping_plugins_uri'))
{
	define('EW_URLPATH', domain_mapping_plugins_uri(WP_CONTENT_URL.'/plugins/'.plugin_basename( dirname(__FILE__)).'/' ));
}
else
{
	define('EW_URLPATH', WP_CONTENT_URL.'/plugins/'.plugin_basename( dirname(__FILE__)).'/' );
}
define('EW_DB_VERSION', 4);

add_action('wp_print_scripts', 'eletrowidgets_print_scripts');
add_action('wp_print_styles', 'eletrowidgets_print_styles');

add_action('init', 'eletrowidgets_load_textdomain');
add_action('init', 'eletrowidgets_update_db');

function eletrowidgets_load_textdomain() {
	$pluginFolder = plugin_basename( dirname(__FILE__) );
    load_plugin_textdomain('eletroWidgets', "wp-content/plugins/$pluginFolder/lang", "$pluginFolder/lang");
}

function eletrowidgets_print_scripts() {

    // Since we only need JS when admin is logged in, its ok to add it everywhere
    if (current_user_can('manage_eletro_widgets')) {
        wp_enqueue_script('eletro-widgets', EW_URLPATH . 'js/eletro-widgets.js', array('jquery', 'jquery-ui-sortable'));
        $messages = array(
            'ajaxurl' => EW_URLPATH.'ajax/eletro-widgets-ajax.php',
            'confirmClear' => __('Are you sure you want to clear all widgets and its settings from this canvas?', 'eletroWidgets'),
            'confirmApply' => __('Are you sure you want to apply this configuration to the public view of this canvas?', 'eletroWidgets'),
            'confirmRestore' => __('Are you sure you want to restore an old configuration and discard unsaved changed? The restored configuration will not be public unless you apply it.', 'eletroWidgets'),
            'feedbackApply' => __('Widgets applied', 'eletroWidgets'),
            'confirmRemove' => __('Are you sure you want to remove this widget?', 'eletroWidgets')
        );
        wp_localize_script('eletro-widgets', 'eletro', $messages);
    }

}

function eletrowidgets_print_styles() {

    if (current_user_can('manage_eletro_widgets')) {
        wp_enqueue_style('eletro-widgets-admin', EW_URLPATH.'css/eletro-widgets-admin.css');
    }

    $css = file_exists(TEMPLATEPATH . '/eletro-widgets.css') ? get_bloginfo('template_url') . '/eletro-widgets.css' : EW_URLPATH . 'css/eletro-widgets.css';
    wp_enqueue_style('eletro-widgets', $css);
}

////////////////////////////

class EletroWidgets {

    function EletroWidgets($cols = 2, $id = 0, $onlyEletro = false) {

        $this->id = $id;
        $this->cols = $cols;
        $this->onlyEletro = $onlyEletro;
        $this->outputCanvas();

    }

    /**
     * Output the eletro widgets canvas and its widgets
     *
     * @return void
     */
    function outputCanvas() {

        echo "<div class='eletro_widgets_separator'></div>";

        // The main DIV for this canvas
        echo "<div id='eletro_widgets_container_{$this->id}' class='eletro_widgets_container'>";

        #echo "<form name='eletro_widgets_form_{$this->id}' id='eletro_widgets_form_{$this->id}'";

        // If admin, print the control
        if (current_user_can('manage_eletro_widgets')) {
            echo "<div id='eletro_widgets_control'>";

            echo '<div class="eletro_list_widgets">';
            $this->list_widgets();
            echo '</div>';

            echo '<div class="eletro_widgets_buttons">';
            echo '<div class="left">';
            echo '<a class="eletroToggleControls">' . __('Show/Hide Controls', 'eletroWidgets') . '</a>';
            echo '<a class="eletroClearAll">' . __('Clear', 'eletroWidgets') . '</a>';            
            echo '<a class="eletroImport">' . __('Import', 'eletroWidgets') . '</a>';
            echo '<a class="eletroExport" href="',
							plugins_url("export.php?id={$this->id}", __FILE__), '">',
							__('Export', 'eletroWidgets'), '</a>';
            echo '<a class="eletroRestore">' . __('Restore', 'eletroWidgets') . '</a>';
            echo '<select id="eletroHistory">';
            echo '<option value="0">', __('No History', 'eletroWidgets'), '</option>';
            echo '</select>';
            echo '</div>';
            echo '<div class="right">';
            echo '<a class="eletroApply">' . __('Apply', 'eletroWidgets') . '</a>';
            echo '</div>';
            echo '</div>';
    
            
            echo '</div>';
            
            echo '<div style="clear: both;"></div>';
        }

        // Put the canvas ID in a hidden field
        echo "<input type='hidden' name='eletro_widgets_id' id='eletro_widgets_id' value='{$this->id}'>";

        $dashedCols = '';
        // Get saved widgets and print them
        if (current_user_can('manage_eletro_widgets')) {
            $options = get_option('eletro_widgets', array());
            $dashedCols = 'eletro_widgets_dashed';
        } else {
            $options = get_option('eletro_widgets_public', array());
        }

        $colunas = array_key_exists($this->id, $options) ? $options[$this->id]['widgets'] : array();

        for ($i = 0; $i < $this->cols; $i ++) {
            echo "<div class='eletro_widgets_col $dashedCols' id='eletro_widgets_col_$i'>";
            if ( array_key_exists($i, $colunas) && is_array($colunas[$i])) {
                foreach ($colunas[$i] as $w) {
                    print_eletro_widgets($w['id'], $w['number'], $w['id_base'], $this->id);
                }
            }
            echo "</div>";
        }

        // closes the form and the canvas div
        #echo "</form>";
        echo "</div>";
        
        echo '<div class="eletro_widgets_separator"></div>';

    }

    /**
     * Returns the next avaliable number to be used to create a new widget instance.
     *
     * @param string $id unique string that identifies the widget type (archive, calendar etc)
     * @return int $number the next number avaliable to this type of widget
     */
	function next_widget_id_number($id) {
	    $options = get_option('eletro_widgets');
	    $number = 1;
	    if (isset($options[$this->id]['widgets_options'][$id]['last_number'])) {
	    	$number = $options[$this->id]['widgets_options'][$id]['last_number'];
	    	$number++;
	    }
	    return $number;
	}

	/**
	 * Output a select box with the list of avaliable widgets types
	 *
	 * Based on the function list_widgets() on the file wp-admin/includes/widgets.php
	 *
	 * @return void
	 */
	function list_widgets() {
	    global $wp_registered_widgets, $wp_registered_widget_controls;

	    $sort = $wp_registered_widgets;
	    usort( $sort, create_function( '$a, $b', 'return strnatcasecmp( $a["name"], $b["name"] );' ) );
	    $done = array();

	    $selectBox = "<option value='' >".__('Select')."</option>";
	    $addControls = '';

	    foreach ($sort as $widget) {
	        if (in_array($widget['callback'], $done, true)) // We already showed this multi-widget
	            continue;

            if ($this->onlyEletro && ( !isset($widget['eletroWidget'] ) ) ) // Check for only eletro widgets option
                continue;

	        $sidebar = is_active_widget($widget['callback'], $widget['id'], false, false);
	        $done[] = $widget['callback'];

	        if (!isset($widget['params'][0]))
	            $widget['params'][0] = array();

	        $args = array('widget_name' => $widget['name'], '_display' => 'template');

	        if (is_object($widget['callback'][0])) {
	            $id_base = $wp_registered_widget_controls[$widget['id']]['id_base'];
	            $args['_multi_num'] = $this->next_widget_id_number($id_base);
                $args['_add'] = 'multi';
	            $args['_id_base'] = $id_base;
		        if (!is_object($widget['callback'][0])) print_r($widget);
		        $args['widget_id'] = get_class($widget['callback'][0]);
	            $args['_multi_num'] = $this->next_widget_id_number($args['widget_id']);
	        } else {
	            $args['_add'] = 'single';
	            if ($sidebar)
	                $args['_hide'] = '1';
	            $args['_id_base'] = $widget['id'];
	            $args['widget_id'] = $widget['id'];
	        }

	        $selectBox .= "<option value='{$args['_id_base']}' >{$widget['name']}</option>";

            $addControls .= $this->get_widget_on_list($args);
	    }
        echo '<div class="eletro_widgets_add_select">';
	    echo __('Add new Widget: ', 'eletroWidgets');
	    echo "<select id='eletro_widgets_add' name='eletro_widgets_add'>$selectBox</select>";
	    echo '</div>';
	    echo $addControls;
	}

	function get_widget_on_list($args) {
		$r = "<div class='widget_add_control' id='widget_add_control_{$args['_id_base']}'>";
		$r .= "<input type='hidden' class='id_base' name='id_base' value='{$args['_id_base']}'>";
		$r .= "<input type='hidden' class='multi_number' name='multi_number' value='" . (array_key_exists('_multi_num', $args) ? $args['_multi_num'] : '') . "'>";
		$r .= "<input type='hidden' class='widget-id' name='widget-id' value='{$args['widget_id']}'>";
		$r .= "<input type='hidden' class='add' name='add' value='{$args['_add']}'>";

		$r .= "<input type='button' value='".__('Add', 'eletroWidgets')."' class='eletro_widgets_add_button'>";
		$r .= '</div>';

		return $r;
	}
}

function print_eletro_widgets($id, $number, $id_base, $canvas_id, $refresh = false) {


    if ($id) {
		global $wp_registered_widgets, $wp_registered_widget_controls;

        if (class_exists($id)) {
			// Multi Widget
            $widgetName = $id_base;
			$newWidget = new $id;
			$newWidget->_set($number);
			$widgetNiceName = $newWidget->name;
			if (current_user_can('manage_eletro_widgets')) {
                $options = get_option('eletro_widgets');
            } else {
                $options = get_option('eletro_widgets_public');
            }

			if (is_array($options[$canvas_id]['widgets_options'][$id]) && array_key_exists($number, $options[$canvas_id]['widgets_options'][$id])) {
				$options = $options[$canvas_id]['widgets_options'][$id][$number];
			}
			$widgetType = 'multi';
			$widgetDivID = $newWidget->id;

		} else if (array_key_exists($id, $wp_registered_widgets)) {
			// Single Widget
            $widgetName = $widgetNiceName = $wp_registered_widgets[$id]['name'];
			$callback = $wp_registered_widgets[$id]['callback'];
			$callbackControl = $wp_registered_widget_controls[$id]['callback'];
			$widgetType = 'single';
			$widgetDivID = $id;
		} else {
			// The widget doesn't exist, replace it with a dummy one
			$widgetName = $id_base;
			$newWidget = new EletroWidgetsDummyWidget($id);
			$newWidget->_set($number);
			$widgetNiceName = $newWidget->name;
			$widgetDivID = $newWidget->id;
			$widgetType = 'multi';
			$options = array('missingWidget' => $id);
		}


		$params = array(
			'name' => $widgetName,
			'id' => $id,
			'before_widget' => '',
			'after_widget' => '',
			'before_title' => '<h2>',
			'after_title' => '</h2>',
		);

        // This is weird, but is needed
        if ($widgetType == 'single')
            $params = array($params);

        if (!$refresh) {
            echo "<div id='{$widgetDivID}' class='itemDrag' alt='{$widgetName}'>";
        }

        echo "<input type='hidden' name='widget-id' value='$id'>";
        echo "<input type='hidden' name='widget-number' value='$number'>";
        echo "<input type='hidden' name='widget-type' value='$widgetType'>";
        echo "<input type='hidden' name='canvas-id' value='$canvas_id'>";
        echo "<input type='hidden' name='id_base' value='$id_base'>";
        echo "<input type='hidden' name='action' value='save_widget_options'>";

        echo '<div class="eletro_widgets_content ' . $widgetName . '">';

        if (current_user_can('manage_eletro_widgets'))
            echo '<span class="itemDrag">' . $widgetNiceName . '</span>';

        // Print Widget
        if ($widgetType == 'multi') {
			$newWidget->widget($params, $options);
		} else {
			if ( is_callable($callback) )
                call_user_func_array($callback, $params);
		}

        echo '</div>';

        // Control
        if (current_user_can('manage_eletro_widgets')) {

            // load this files that have some functions (such as checked()) used by some widget controls
            require_once(ABSPATH . 'wp-admin/includes/template.php');

            echo "<div class='eletro_widgets_control'>";

            if ($widgetType == 'multi') {
            	$newWidget->form($options);
            } else {
				if ( is_callable($callbackControl) ) {
                    call_user_func_array($callbackControl, '');
                } else {
                     _e('There are no options for this widget.');
                }
			}

            echo '<input class="save" name="save" type="button" value="Save">';
            echo "</div>";
            echo '<div class="clearfix"></div>';
        }

        if (!$refresh) {
            echo "</div>";
        }
    }
}

function defineAsEletroWidget($widgetId) {
    global $wp_registered_widgets;
    $wp_registered_widgets[$widgetId]['eletroWidget'] = true;
}

function eletroWidgetsInstall() {
    $role = get_role('administrator');
    $role->add_cap('manage_eletro_widgets');
    $options = array();
    update_option('eletro_widgets', $options);
    update_option('eletro_widgets_public', $options);
}

function eletrowidgets_update_db() {
	if (get_option('eletro_widgets_db_version') != EW_DB_VERSION) {
		global $wpdb;
		$table = $wpdb->prefix . 'eletro_widgets_history';

		$query = "CREATE TABLE $table (
			id int NOT NULL AUTO_INCREMENT,
			date timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
			data text,
			canvas varchar(128) NOT NULL,
			UNIQUE KEY id (id)
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($query);

		update_option('eletro_widgets_db_version', EW_DB_VERSION);
	}
}

function eletroWidgetsUninstall() {
    $role = get_role('administrator');
    $role->remove_cap('manage_eletro_widgets');
    remove_option('eletro_widgets');
    remove_option('eletro_widgets_public');
}

register_activation_hook( __FILE__, 'eletroWidgetsInstall' );
register_deactivation_hook( __FILE__, 'eletroWidgetsInstall' );

class EletroWidgetsDummyWidget extends WP_Widget {
	public function __construct() {
		parent::__construct('eletrowidgets_dummy_widget',
			'EletroWidgets Dummy Widget',
			array('description' =>
				__('Displayed when the included widget is missing',
					'eletroWidgets')));
	}

	public function widget($args, $instance) {
		if (current_user_can('manage_eletro_widgets')) {
			$missingWidget = array_key_exists('missingWidget', $instance)?
				$instance['missingWidget'] : '';
			printf(__('%s widget is missing, reactive it or remove this',
					'eletroWidgets'), $missingWidget);
		}
	}
}

?>
