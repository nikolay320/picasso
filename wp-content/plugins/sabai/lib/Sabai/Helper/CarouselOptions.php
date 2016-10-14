<?php
class Sabai_Helper_CarouselOptions extends Sabai_Helper
{    
    public function help(Sabai $application, array $values, $checkbox = false, array $parents = array())
    {
        $values += array(
            'mode' => 'horizontal',
            'auto' => false,
            'controls' => true,
            'pager' => true,
            'pause' => 4000,
            'captions' => true,
        );
        return array(
            'mode' => array(
                '#title' => __('Transition mode', 'sabai'),
                '#type' => 'select',
                '#options' => array(
                    'horizontal' => _x('Horizontal', 'transition mode', 'sabai'),
                    'vertical' => _x('Vertical', 'transition mode', 'sabai'),
                    'fade' => _x('Fade', 'transition mode', 'sabai'),
                ),
                '#weight' => 10,
                '#default_value' => $values['mode'],
            ),
            'auto' => array(
                '#type' => $checkbox ? 'checkbox' : 'yesno',
                '#title' => __('Automatically cycle slides', 'sabai'),
                '#default_value' => !empty($values['auto']),
                '#weight' => 15,
            ),
            'pause' => array(
                '#title' => __('The amount of time to delay between automatically cycling slides.', 'sabai'),
                '#type' => 'number',
                '#default_value' => $values['pause'],
                '#size' => 7,
                '#integer' => true,
                '#weight' => 20,
                '#states' => array(
                    'visible' => array(
                        sprintf('input[name="%s[]"]', $parents ? $application->Form_FieldName($parents) . '[auto]' : 'auto') => array(
                            'type' => 'checked',
                            'value' => true,
                        ),
                    ),
                ),
            ),
            'captions' => array(
                '#type' => $checkbox ? 'checkbox' : 'yesno',
                '#title' => __('Show captions', 'sabai'),
                '#default_value' => $values['captions'],
                '#weight' => 25,
            ),
            'controls' => array(
                '#type' => $checkbox ? 'checkbox' : 'yesno',
                '#title' => __('Show prev/next controls', 'sabai'),
                '#default_value' => $values['controls'],
                '#weight' => 30,
            ),
            'pager' => array(
                '#type' => $checkbox ? 'checkbox' : 'yesno',
                '#title' => __('Show indicator circles', 'sabai'),
                '#default_value' => $values['pager'],
                '#weight' => 35,
            ),
        );
    }
}