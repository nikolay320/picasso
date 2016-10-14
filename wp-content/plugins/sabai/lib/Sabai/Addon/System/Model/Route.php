<?php
class Sabai_Addon_System_Model_Route extends Sabai_Addon_System_Model_Base_Route
{
    public function toArray()
    {
        return array(
            'path' => $this->path,
            'controller' => $this->controller,
            'controller_addon' => $this->controller_addon,
            'forward' => $this->forward,
            'addon' => $this->addon,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'title_callback' => $this->title_callback,
            'access_callback' => $this->access_callback,
            'callback_path' => $this->callback_path,
            'callback_addon' => $this->callback_addon,
            'weight' => $this->weight,
            'format' => $this->format,
            'ajax' => $this->ajax,
            'method' => $this->method,
            'class' => $this->class,
            'data' => $this->data,
        );
    }
}

class Sabai_Addon_System_Model_RouteRepository extends Sabai_Addon_System_Model_Base_RouteRepository
{
}