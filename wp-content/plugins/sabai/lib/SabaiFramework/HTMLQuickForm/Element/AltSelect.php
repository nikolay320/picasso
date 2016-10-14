<?php
require_once 'HTML/QuickForm/altselect.php';

class SabaiFramework_HTMLQuickForm_Element_AltSelect extends HTML_QuickForm_altselect
{
    /**
     * Override the parent method to remove code parts utilizing the includeOther feature
     * @param type $values 
     */
    function setSelected($values)
    {
        if (is_string($values) && $this->getMultiple()) {
            $values = preg_split("/[ ]?,[ ]?/", $values);
        }
        if (is_array($values)) {
            $this->_values = array_values($values);
        } else {
            $this->_values = array($values);
        }
    }
    
    /**
     * Overrides the parent method to use the createElement() method of SabaiFramework_HTMLQuickForm instead of the one in HTML_QuickForm.
     * Also removed code parts utilizing the includeOther feature
     * 
     * @param   bool $formatArray set true for an array (default), false for HTML
     * @access  public
     * @see     HTML_QuickForm_group::getElements()
     * @return  mixed Array or HTML string
     */
    function getElements($formatArray = true)
    {
        $html_func_to_use = 'toHtml';
        $is_multiple = $this->getMultiple();

        if ($formatArray) {
            $elements = array();
        } else {
            $preHtml = '';
            $postHtml = '';
            $htmlArray = array();
            $tabs = $this->_getTabs();
            
            if ($this->getComment() != '') {
                $preHtml .= '<!-- ' . $this->getComment() . ' //-->' . PHP_EOL;
            }
        }

        $myName = $this->getName();
        if ($is_multiple) {
            $myName .= '[]';
        }


        foreach ($this->_options as $option) {
            if ($is_multiple) {
                $element = SabaiFramework_HTMLQuickForm::createElement('checkbox', array($myName));
                //xxx - qf won't take a value as constructor argument
                $element->updateAttributes(array('value' => $option['attr']['value']));
            } else {
                $element = SabaiFramework_HTMLQuickForm::createElement('radio', array(
                                                          $myName,
                                                          null,
                                                          null,
                                                          $option['attr']['value']));
            }

            if (isset($this->_individualAttributes['_qf_all'])) {
                $element->updateAttributes($this->_individualAttributes['_qf_all']);
            }

            if (isset($this->_individualAttributes[$option['attr']['value']])) {
                $element->updateAttributes($this->_individualAttributes[$option['attr']['value']]);
            }
                
            if (is_array($this->_values) && in_array((string)$option['attr']['value'], $this->_values)) {
                $element->setChecked(true);
            }

            if ($formatArray) {
                $elements[$option['attr']['value']] = $element;
            } else {
                // write our own label instead of adding text to the radio/cbox
                // as we may want to render without any text when doing from a group
                $htmlArray['_qf_' . $option['attr']['value']] = $tabs .
                                                                $element->$html_func_to_use() .
                                                                '<label for="' . $element->getAttribute('id') . '">' .
                                                                $option['text'] .
                                                                '</label>';
            }
        }

        if ($formatArray) {
            return $elements;
        } else {
            if ($this->list_type === 'ul' || $this->list_type === 'ol') {
                $tempHtml = $preHtml . PHP_EOL .
                            '<' . $this->list_type . '>' . PHP_EOL;
                foreach ($htmlArray as $key => $piece) {
                    $tempHtml .= '<li ';
                    $id = $this->getAttribute('id');
                    if ($id !== null) {
                        $tempHtml .= 'id="' . $key . '_' . $id . '" ';
                    }
                    if ($key === '_qf_other' || $key === '_qf_other_text') {
                        $tempHtml .= 'class="' . $key . '">';
                    } else {
                        $tempHtml .= 'class="_qf_option">';
                    }
                    $tempHtml .= $piece . '</li>' . PHP_EOL;
                }
                $tempHtml .= '</' . $this->list_type . '>' . PHP_EOL .
                             $postHtml;
                return $tempHtml;
            } else {
                return $preHtml . PHP_EOL .
                       implode($this->delimiter . PHP_EOL, $htmlArray) . PHP_EOL .
                       $postHtml;
            }
        }
    }
}