<?php
require_once 'HTML/QuickForm/Renderer/Default.php';

class SabaiFramework_HTMLQuickForm_Renderer extends HTML_QuickForm_Renderer_Default
{
    protected $_groupTemplateDefault, $_formClass = '',
        $_elementId = array(), $_elementClass = array(), $_elementErrors = array(), $_elementRequired = array(),
        $_elementPrefix = array(), $_elementSuffix = array(), $_elementFieldPrefix = array(), $_elementFieldSuffix = array(),
        $_headerHtml = array(), $_groups = array(), $_currentGroup;

    public function __construct()
    {
        parent::HTML_QuickForm_Renderer_Default();
        $this->_inGroup = 0;
        $this->setElementTemplate('
<div<!-- BEGIN id --> id="{id}"<!-- END id --> class="sabai-form-field<!-- BEGIN class --> {class}<!-- END class -->">
  <!-- BEGIN label --><div class="sabai-form-field-label"><span>{label}</span><!-- BEGIN required --><span class="sabai-form-field-required">*</span><!-- END required --></div><!-- END label -->
  <!-- BEGIN field_prefix --><span class="sabai-form-field-prefix">{field_prefix}</span><!-- END field_prefix -->
  {element}
  <!-- BEGIN field_suffix --><span class="sabai-form-field-suffix">{field_suffix}</span><!-- END field_suffix -->
  <!-- BEGIN error_msg --><span class="sabai-form-field-error">{error}</span><!-- END error_msg -->
  <!-- BEGIN label_2 --><div class="sabai-form-field-description">{label_2}</div><!-- END label_2 -->
</div>
');
        $this->setGroupTemplate('
<fieldset<!-- BEGIN id --> id="{id}"<!-- END id --> class="sabai-form-field<!-- BEGIN class --> {class}<!-- END class -->">
  <!-- BEGIN label --><legend><span>{label}</span><!-- BEGIN required --><span class="sabai-form-field-required">*</span><!-- END required --></legend><!-- END label -->
  <!-- BEGIN error_msg --><span class="sabai-form-field-error">{error}</span><!-- END error_msg -->
  <!-- BEGIN label_2 --><div class="sabai-form-field-description">{label_2}</div><!-- END label_2 -->
  <div class="sabai-form-fields">
    {element}
  </div>
  <!-- BEGIN label_3 --><div class="sabai-form-field-description">{label_3}</div><!-- END label_3 -->
</fieldset>', null);
        $this->setFormTemplate('
<form novalidate class="sabai-form {class}"{attributes}>
  <!-- BEGIN header --><div class="sabai-form-header">{header}</div><!-- END header -->
  <div class="sabai-form-fields">{content}{hidden}</div>
</form>');
    }

    public function renderElement($element, $required, $error)
    {
        if (!isset($this->_currentGroup)) {
            $name = $element->getName();
            $template = isset($this->_templates[$name]) ? $this->_templates[$name] : $this->_elementTemplate;
            $this->_html .= $this->_renderElementTemplate($element, $template, $required, $error, true);
        } elseif (!isset($this->_groupElementTemplate[$this->_currentGroup])) {
            $name = $element->getName();
            $template = isset($this->_templates[$name]) ? $this->_templates[$name] : $this->_elementTemplate;
            $this->_groupElements[$this->_currentGroup][] = $this->_renderElementTemplate($element, $template, $required, $error, true);
        } else {
            $this->_groupElements[$this->_currentGroup][] = $this->_renderElementTemplate($element, $this->_groupElementTemplate[$this->_currentGroup], $required, $error, true);
        }
    }

    protected function _renderElementTemplate($element, $html, $required, $error, $renderElementHtml = false)
    {
        $label = $element->getLabel();
        if (is_array($label)) {
            $nameLabel = array_shift($label);
        } else {
            $nameLabel = $label;
        }

        $element_name = $element->getName();
        if ($nameLabel) {
            $html = str_replace(array('{label}', '<!-- BEGIN label -->', '<!-- END label -->'), array($nameLabel), $html);
        }
        if ($required || !empty($this->_elementRequired[$element_name])) {
            $html = str_replace(array('<!-- BEGIN required -->', '<!-- END required -->'), '', $html);
        }
        if (isset($error)
            || ($error = @$this->_elementErrors[$element_name])
        ) {
            unset($this->_elementErrors[$element_name]);
            if (!isset($this->_elementClass[$element_name])) {
                $this->_elementClass[$element_name] = array();
            }
            $this->_elementClass[$element_name] .= ' sabai-form-field-error';
            if (is_string($error) && strlen($error)) {
                $html = str_replace(array('{error}', '<!-- BEGIN error_msg -->', '<!-- END error_msg -->'), array(Sabai::h($error)), $html);
            }
        }
        if (is_array($label)) {
            foreach ($label as $key => $text) {
                if (empty($text)) continue;
                $key  = is_int($key) ? $key + 2 : $key;
                $html = str_replace(array("{label_{$key}}", "<!-- BEGIN label_{$key} -->", "<!-- END label_{$key} -->"), array($text), $html);
            }
        }
        if (strpos($html, '{label')) {
            $html = preg_replace(
                array(
                    '/\s*<!-- BEGIN label_(\S+) -->.*<!-- END label_\1 -->\s*/is',
                    '/\s*<!-- BEGIN label -->.*<!-- END label -->\s*/is'
                ),
                '',
                $html
            );
        }
        
        if (isset($this->_currentGroup) && $this->_currentGroup === $element_name) {
            return $renderElementHtml ? str_replace('{element}', $element->toHtml(), $html) : $html;
        }

        // Insert element id if any
        if (!empty($this->_elementId[$element_name])) {
            $html = str_replace(array('{id}', '<!-- BEGIN id -->', '<!-- END id -->'), array(Sabai::h($this->_elementId[$element_name])), $html);
        }

        // Insert class name if any
        if (isset($this->_elementClass[$element_name])) {
            $html = str_replace(array('{class}', '<!-- BEGIN class -->', '<!-- END class -->'), array(Sabai::h($this->_elementClass[$element_name])), $html);
        }

        // Add field/element level prefix/suffix
        if ($renderElementHtml) {
            if (isset($this->_elementFieldPrefix[$element_name])) {
                $html = str_replace(array('{field_prefix}', '<!-- BEGIN field_prefix -->', '<!-- END field_prefix -->'), array($this->_elementFieldPrefix[$element_name]), $html);
            }
            if (isset($this->_elementFieldSuffix[$element_name])) {
                $html = str_replace(array('{field_suffix}', '<!-- BEGIN field_suffix -->', '<!-- END field_suffix -->'), array($this->_elementFieldSuffix[$element_name]), $html);
            }

            $html = str_replace('{element}', $element->toHtml(), $html);
        }

        $prefix = isset($this->_elementPrefix[$element_name]) ? $this->_elementPrefix[$element_name] : '';
        $suffix = isset($this->_elementSuffix[$element_name]) ? $this->_elementSuffix[$element_name] : '';

        return implode(PHP_EOL, array($prefix, $html, $suffix));
    }

    public function startGroup($group, $required, $error)
    {
        $name = $group->getName();
        $this->_groupElements[$name] = array();
        $template = isset($this->_templates[$name]) ? $this->_templates[$name] : $this->_groupTemplateDefault;
        $this->_groupTemplate[$name] = $this->_renderElementTemplate($group, $template, $required, $error);
        if (isset($this->_currentGroup)) {
            $this->_groups[] = $this->_currentGroup;
        }
        $this->_currentGroup = $name;
        $this->_inGroup = true;
    }

    public function finishGroup($group)
    {
        $html = str_replace('{element}', implode(PHP_EOL, $this->_groupElements[$this->_currentGroup]), $this->_groupTemplate[$this->_currentGroup]);        
        if (!empty($this->_groups)) {
            $this->_currentGroup = array_pop($this->_groups);
            $this->_groupElements[$this->_currentGroup][] = $html;
        } else {
            $this->_html .= $html;
            $this->_currentGroup = null;
            $this->_inGroup = false;
        }
    }

    public function finishForm($form)
    {
        // add form attributes and content
        $html = str_replace('{attributes}', $form->getAttributes(true), $this->_formTemplate);

        // add header
        if (!empty($this->_headerHtml)) {
            $html = str_replace(array('{header}', '<!-- BEGIN header -->', '<!-- END header -->'), array(implode(PHP_EOL, $this->_headerHtml)), $html);
        }
        // add footer
        if (!empty($this->_footerHtml)) {
            $html = str_replace(array('{footer}', '<!-- BEGIN footer -->', '<!-- END footer -->'), array(implode(PHP_EOL, $this->_footerHtml)), $html);
        }

        if (strpos($this->_formTemplate, '{hidden}')) {
            $html = str_replace('{hidden}', $this->_hiddenHtml, $html);
        } else {
            $this->_html .= $this->_hiddenHtml;
        }
        $this->_hiddenHtml = '';
        $this->_html = str_replace('{content}', $this->_html, $html);

        // remove all remaining comments
        $this->_html = preg_replace(array(
            '/([ \t\n\r]*)?<!-- BEGIN header -->.*<!-- END header -->([ \t\n\r]*)?/isU',
            '/([ \t\n\r]*)?<!-- BEGIN footer -->.*<!-- END footer -->([ \t\n\r]*)?/isU',
            '/([ \t\n\r]*)?<!-- BEGIN id -->.*<!-- END id -->([ \t\n\r]*)?/isU',
            '/([ \t\n\r]*)?<!-- BEGIN class -->.*<!-- END class -->([ \t\n\r]*)?/isU',
            '/([ \t\n\r]*)?<!-- BEGIN error_msg -->.*<!-- END error_msg -->([ \t\n\r]*)?/isU',
            '/([ \t\n\r]*)?<!-- BEGIN required -->.*<!-- END required -->([ \t\n\r]*)?/isU',
            '/([ \t\n\r]*)?<!-- BEGIN field_prefix -->.*<!-- END field_prefix -->([ \t\n\r]*)?/isU',
            '/([ \t\n\r]*)?<!-- BEGIN field_suffix -->.*<!-- END field_suffix -->([ \t\n\r]*)?/isU',
            '/\s*<!-- BEGIN label(\S*) -->.*<!-- END label\1 -->\s*/is',
        ), '', $this->_html);

        // add form classes
        $this->_html = str_replace('{class}', $this->_formClass, $this->_html);
    }

    public function renderHeader($header)
    {
        $name = $header->getName();
        if (!empty($name) && isset($this->_templates[$name])) {
            $this->_headerHtml[] = str_replace('{header}', $header->toHtml(), $this->_templates[$name]);
        } else {
            $this->_headerHtml[] = $header->toHtml();
        }
    }
    
    public function setGroupTemplate($html, $group)
    {
        $this->_groupTemplateDefault = $html;
    }

    public function setFormClass($class)
    {
        $this->_formClass = $class;
    }

    public function setElementClass($elementName, $class)
    {
        $this->_elementClass[$elementName] = $class;
    }

    public function setElementId($elementName, $id)
    {
        $this->_elementId[$elementName] = $id;
    }

    public function setElementError($elementName, $message = true)
    {
        $this->_elementErrors[$elementName] = $message;
    }

    public function setElementRequired($elementName)
    {
        $this->_elementRequired[$elementName] = true;
    }

    public function setElementPrefix($elementName, $html)
    {
        $this->_elementPrefix[$elementName] = $html;
    }

    public function setElementSuffix($elementName, $html)
    {
        $this->_elementSuffix[$elementName] = $html;
    }

    public function setElementFieldPrefix($elementName, $html)
    {
        $this->_elementFieldPrefix[$elementName] = $html;
    }

    public function setElementFieldSuffix($elementName, $html)
    {
        $this->_elementFieldSuffix[$elementName] = $html;
    }
}