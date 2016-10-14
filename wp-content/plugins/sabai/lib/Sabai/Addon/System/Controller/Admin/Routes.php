<?php
class Sabai_Addon_System_Controller_Admin_Routes extends Sabai_Addon_Form_Controller
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_cancelUrl = null;
        $this->_submitable = false;
        
        // Init variables
        $sortable_headers = array('path' => 'path', 'date' => 'created');
        $sort = $context->getRequest()->asStr('sort', 'path', array_keys($sortable_headers));
        $order = $context->getRequest()->asStr('order', 'ASC', array('ASC', 'DESC'));
        $addon = $context->getRequest()->asStr('addon', '');
        $path = $context->getRequest()->asStr('path', '');
        $url_params = array('sort' => $sort, 'order' => $order, 'path' => $path, 'addon' => $addon);
        $show_admin_routes = $context->getRequest()->asBool('admin', false);

        // Paginate orders
        $routes = $this->getModel($show_admin_routes ? 'Adminroute' : 'Route', 'System');
        if ($addon) {
            $routes->addon_is($addon);
        }
        if ($path) {
            $routes->path_startsWith($path);
        }
        
        // Init form
        $form = array(
            'routes' => array(
                '#type' => 'tableselect',
                '#header' => array(
                    'path' => 'Path',
                    'addon' => 'Add-on',
                    'controller' => 'Controller',
                    'controller_addon' => 'Controller Add-on',
                    'date' => 'Date Created',
                ),
                '#options' => array(),
                '#disabled' => true,
                '#multiple' => true,
            ),
        );
        
        // Set sortable headers
        $this->_makeTableSortable($context, $form['routes'], array_keys($sortable_headers), array(), $sort, $order, $url_params);

        foreach ($routes->fetch(0, 0, array($sort, 'weight'), array($order, 'ASC')) as $route) {
            $form['routes']['#options'][$route->id] = array(
                'path' => $route->path,
                'addon' => $route->addon,
                'controller' => $route->controller,
                'controller_addon' => $route->controller_addon,
                'date' => $this->DateTime($route->created),
            );
        }
        
        return $form;
    }
}