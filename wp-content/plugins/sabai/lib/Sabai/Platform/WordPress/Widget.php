<?php
class Sabai_Platform_WordPress_Widget extends WP_Widget
{
    protected $_addonName, $widgetName;

    public function __construct($addonName, $widgetName, $widgetTitle, $widgetSummary)
    {
        $options = array('description' => $widgetSummary);
        parent::__construct(false, sprintf('%s | sabai', $widgetTitle), $options);
        $this->_addonName = $addonName;
        $this->_widgetName = $widgetName;
    }
    
    /**
     * Call an application helper
     */
    public function __call($name, $args)
    {
        return get_sabai()->getHelperBroker()->callHelper($name, $args);
    }

    function widget($args, $instance)
    {
        if ((!$widget = $this->_getWidget(true))
            || (!$content = $widget->widgetsWidgetGetContent($instance))
        ) return;

        // Display content
        if (!strlen($instance['_title_'])) {
            echo $args['before_widget'];
            $this->_display($content);
            echo $args['after_widget'];
        } else {  
            echo $args['before_widget'];
            echo $args['before_title'];
            Sabai::_h($instance['_title_']);
            echo $args['after_title'];
            $this->_display($content);
            echo $args['after_widget'];
        }
    }
    
    private function _display($content)
    {
        echo '<div class="sabai sabai-wordpress-widget sabai-widget-'. str_replace('_', '-', $this->_widgetName) .' sabai-clearfix">';
        if (is_array($content)) {
            $custom_assets_dir = Sabai_Platform_WordPress::getInstance()->getCustomAssetsDir();
            if (isset($content['template'])) {
                if (!@file_exists($tpl = $custom_assets_dir . '/' . basename($content['template']))) {
                    $tpl = $content['template'];
                }
            } else {
                if (!@file_exists($tpl = $custom_assets_dir . '/wordpress_widget_' . $this->_widgetName . '.html.php')) {
                    if (!@file_exists($tpl = $custom_assets_dir . '/wordpress_widget.html.php')) {
                        $tpl = Sabai_Platform_WordPress::getInstance()->getAssetsDir() . '/templates/wordpress_widget.html.php';
                    }
                }
            }
            $this->_include($tpl, array('addon_name' => $this->_addonName, 'widget_name' => $this->_widgetName) + $content);
        } else {
            echo $content;
        }
        echo '</div>';
    }
    
    private function _include()
    {
        extract(func_get_arg(1), EXTR_SKIP);
        include func_get_arg(0);
    }

    function update($new_instance, $old_instance)
    {
        if ($widget = $this->_getWidget()) {
            $widget->widgetsWidgetOnSettingsSaved($new_instance, $old_instance);
        }
        
        return $new_instance;
    }

    function form($instance)
    {        
        if (!$widget = $this->_getWidget()) {
            return;
        }

        // Get additional settings
        $elements = array('#token' => false, '#build_id' => false);
        if ($widget_settings = $widget->widgetsWidgetGetSettings()) {
            foreach ($widget_settings as $key => $data) {
                if ($data['#type'] === 'checkbox') {
                    $default_value = isset($instance[$key]) && is_array($instance[$key]) && array_shift($instance[$key]) ? true : false;
                } else {
                    $default_value = array_key_exists($key, $instance) ? $instance[$key] : @$widget_settings[$key]['#default_value'];
                }
                $elements[$this->get_field_name($key)] = array_merge(
                    $data,
                    array(
                        '#type' => @$data['#type'],
                        '#title' => isset($data['#title']) ? $data['#title'] : null,
                        '#description' => isset($data['#description']) ? $data['#description'] : null,
                        '#default_value' => $default_value,
                    )
                );
            }
        }
        $elements[$this->get_field_name('_title_')] = array(
            '#title' => __('Title', 'sabai'),
            '#type' => 'textfield',
            '#default_value' => isset($instance['_title_'])
                ? $instance['_title_']
                : $widget->widgetsWidgetGetLabel(),
            '#weight' => -1,
        );

        list($html, ) = get_sabai()->Form_Build($elements)->render(true);
        echo $html;
    }
    
    protected function _getWidget($loadModel = false)
    {
        $sabai = get_sabai();
        if (!$sabai->isAddonLoaded($this->_addonName)) return;

        if ($loadModel && !$sabai->isRunning()) {
            // For some strange reason, this is required for the Model classes to be loaded on non-Sabai pages
            class_exists('Sabai_Model');
        }
        
        return $sabai->getAddon($this->_addonName)->widgetsGetWidget($this->_widgetName);
    }
}