<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Template_Widget
 *
 * @author rafael
 */
abstract class WidgetTemplate {

    protected $id;
    protected $template_part;
    protected $isAjaxRequest = false;

    static function init() {
        $class = get_called_class();
        add_action("wp_ajax_" . strtolower($class) . "_save", array($class, 'ajaxSave'));
        add_action("wp_ajax_nopriv_" . strtolower($class) . "_save", array($class, 'ajaxSave'));
    }

    
    static function ajaxSave() {
        if (current_user_can('manage_options')) {
            
            $class = get_called_class();
            if (is_array($_POST) && isset($_POST['_widget_id'])) {
                $widget_id = $_POST['_widget_id'];
                $data = $_POST;
                unset($data['_widget_id'], $data['_template_part'], $data['action']);
                update_option("{$class}-{$widget_id}", $_POST);
                $template_part = $_POST['_template_part'] ? $_POST['_template_part'] : null;
                $wid = new $class($widget_id, $template_part);
                $wid->isAjaxRequest = true;
                echo $wid;
                die;
            }
        }
    }

    function __construct($id, $template_part = null) {
        $this->id = $id;

        $this->template_part = $template_part;
    }

    function __toString() {
        ob_start();

        $config = $this->getConfig();

        if(!$this->isAjaxRequest)
            $this->_form();
        
        $class = get_class($this);
        $div_id = strtolower(get_class($this).'-'.$this->id);
        if(!$this->isAjaxRequest){
            ?><div id="<?php echo $div_id; ?>" class="<?php echo strtolower($class); ?>"><?php 
        }
        
        if ($this->template_part)
            html::part($this->template_part, array('self' => $this, 'config' => $config));
        else
            $this->widget($config);
            
        if(!$this->isAjaxRequest){
            ?></div><?php 
        }
        
        $html = ob_get_clean();
        
        return is_string($html) ? $html : '';
    }

    protected function getConfig() {
        $class = get_class($this);
        $config = get_option("{$class}-{$this->id}");
        return $config;
    }

    protected function getFormTitle() {
        $class = get_class($this);
        return "implemente o método <b>protected function getFormTitle() na classe $class</b>";
    }

    
    protected function _form() {

        if (!current_user_can('manage_options'))
            return;

        $class = get_class($this);
        $div_id = strtolower(get_class($this).'-'.$this->id);
        ?>
        <div class="hl-lightbox">editar
            <div class="hl-lightbox-dialog">
                <header>
                    <a href="#" class="hl-lightbox-close"></a>
                    <h1><?php echo $this->getFormTitle(); ?></h1>
                </header>
                <form id="<?php echo "{$class}-{$this->id}"; ?>" data-div_id="<?php echo $div_id; ?>" class="template-widget-form">
                    <input type="hidden" name="action" value="<?php echo strtolower($class) . "_save"; ?>" />
                    <input type="hidden" name="_template_part" value="<?php echo $this->template_part; ?>" />
                    <input type="hidden" name="_widget_id" value="<?php echo $this->id; ?>" />
                        <?php $this->form($this->getConfig()); ?>
                    <input type="submit" name="save" value="<?php _e('salvar', 'consulta'); ?>"/>
                </form>
            </div>
        </div>
        <?php
    }

    abstract protected function widget($config);

    abstract protected function form($config);
}
