<?php
class Sabai_Addon_Form_Field_Icon extends Sabai_Addon_Form_Field_Text
{
    static private $_elements = array();
    
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        $data['#separator'] = null;
        $data['#id'] = $form->getFieldId($name);
        if (!isset($data['#size'])) {
            $data['#size'] = 20;
        }
        // Register pre render callback if this is the first date element
        if (empty(self::$_elements[$form->settings['#id']])) {
            $form->settings['#pre_render'][] = array($this, 'preRenderCallback');
        }
        self::$_elements[$form->settings['#id']][$name] = $data['#id'];
        
        return parent::formFieldGetFormElement($name, $data, $form);
    }

    public function preRenderCallback($form)
    {
        if (!isset(self::$_elements[$form->settings['#id']])) return;
        
        $application = $this->_addon->getApplication();
        $application->LoadJs('typeahead.bundle.min.js', 'twitter-typeahead', 'jquery');

        $form->addJs(sprintf(
            'jQuery(document).ready(function ($) {
    suggestIcons();
    function suggestIcons() {
        var icons = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace("name"),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            prefetch: {url: "%s"},
            limit: 5
        });
        icons.initialize();
        $("#%s input").typeahead(
            {highlight: true},
            {name: "icons", displayKey: "name", source: icons.ttAdapter(), templates: {suggestion: function(item){return \'<i class="fa fa-\' + item.name + \'"></i> \' + item.name}}}
        );
    }
});',
            $application->getPlatform()->getAssetsUrl() . '/fonts/fontawesome.json',
            implode(' input, #', self::$_elements[$form->settings['#id']])
        ));
    }
}
