<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * HTML QuickForm Alternate Select
 *
 * This file must be included *after* HTML/QuickForm.php
 *
 * HTML_QuickForm plugin that changes a select into a group of radio buttons
 * or checkboxes with an optional textbox for other options not listed. If
 * the select element is listed as multiple, then it will be rendered with
 * checkboxes, otherwise it is rendered with radio buttons.
 *
 * PHP Versions 4 and 5
 *
 * @category    HTML
 * @package     HTML_QuickForm_altselect
 * @author      David Sanders (shang.xiao.sanders@gmail.com)
 * @license     http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version     Release: @package_version@
 * @link        http://pear.php.net/package/HTML_QuickForm_altselect
 * @see         HTML_QuickForm_select
 */

require_once 'HTML/QuickForm/select.php';


// {{{ HTML_QuickForm_altselect

/**
 * HTML QuickForm Alternate Select
 *
 * HTML_QuickForm plugin that changes a select into a group of radio buttons
 * or checkboxes with an optional textbox for other options not listed. If
 * the select element is listed as multiple, then it will be rendered with
 * checkboxes, otherwise it is rendered with radio buttons.
 *
 * @category    HTML
 * @package     HTML_QuickForm_altselect
 * @author      David Sanders (shang.xiao.sanders@gmail.com)
 * @license     http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version     Release: @package_version@
 * @link        http://pear.php.net/package/HTML_QuickForm_altselect
 * @see         HTML_QuickForm_select
 */
class HTML_QuickForm_altselect extends HTML_QuickForm_select
{
    // {{{ properties

    /**
     * Delimiter between subelements.  Use br to go vertical, or nbsp to go 
     * horizontal.
     * 
     * @var     string
     * @access  public
     */
    var $delimiter = '<br />';

    /**
     * Rather than render with a delimiter you may choose to render as a HTML
     * list.
     *
     * @var     string
     * @access  public
     * @see     delimiter
     */
    var $list_type;
 
    /**
     * Associative array of attributes for each of the individual form elements.
     * NOTE: use "_qf_other" for the other radio button, and "_qf_other_text" 
     * for the text field.
     * 
     * @var      array     Associative array of attributes (see HTML_Common)
     * @access   private
     */
    var $_individualAttributes;

    // }}}
    // {{{ HTML_QuickForm_altselect

    /**
     * Constructor.  Used to distinguish the attributes array which should be 
     * an associative array of options to either a typical HTML attribute string
     * or another associative array
     * 
     * @param  string    $elementName  select name attribute
     * @param  mixed     $elementLabel label(s) for the select
     * @param  mixed     $options      data to be used to populate options
     * @param  mixed     $attributes   an associative array of option value 
     *                                 -> attributes. Each attribute is either 
     *                                 a typical HTML attribute string or an
     *                                 associative array.
     *                                 NOTE: use "_qf_other" for the other radio
     *                                 button, "_qf_other_text" for the 
     *                                 text field and "_qf_all" to apply the
     *                                 attributes to all the option elements.
     * @return void
     */
    function HTML_QuickForm_altselect($elementName = null,
                                      $elementLabel = null,
                                      $options = null,
                                      $attributes = null)
    {
        if (func_get_args()) {
            HTML_QuickForm_select::HTML_QuickForm_select($elementName,
                                                         $elementLabel,
                                                         $options);
            $this->_individualAttributes = $attributes;
        }
    }

    // }}}
    // {{{ toHtml

    /**
     * Render the HTML_QuickForm element.
     *
     * @access  public
     * @return  string The rendered HTML
     */
    function toHtml()
    {
        return $this->getElements(false);
    }

    // }}}
    // {{{ getElements

    /**
     * Arrange the buttons/boxes and other bits either concatenated as a html
     * string or in an array.  When this element is registered as a group, 
     * getElements should act in the same way as HTML_QuickForm_group::getElements().
     * (Therefore the default must be to format as an array)
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
                $element = HTML_QuickForm::createElement('checkbox',$myName);
                //xxx - qf won't take a value as constructor argument
                $element->updateAttributes(array('value' => $option['attr']['value']));
            } else {
                $element = HTML_QuickForm::createElement('radio',
                                                          $myName,
                                                          null,
                                                          null,
                                                          $option['attr']['value']);
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
                $elements[$option['attr']['value']] =& $element;
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

    // }}}

    // {{{ setSelected

    /**
     * Set the selected options.  If a non-listed option is specified, it
     * will go into the other text field.  Note at this point, the other and
     * multiple attributes may not have been set.
     *
     * @param   mixed  $values array or comma delimited string of selected values
     * @access  public
     * @return  void
     */
    function setSelected($values)
    {
        parent::setSelected($values);

        //
        // we need to do some extra work here in case the other 
        // option will be/has been set... 
        //
        $other_values = array();

        foreach ($this->_values as $value) {
            // if we are in singular mode and the other button is selected from
            // the submit values then we'll need to record the real other value
            // in _otherValue
            if ($value == '_qf_other') {
                $myName = $this->getName();
                
                $this->_otherValue = @$_REQUEST[$myName.'_qf_other'];
                
                // we only need to grasp the first other value, because we
                // are in singular mode, so we'll return...
                return;
            }
            // otherwise the real other value might be listed in _values
            // from setSelected('junk') or if we're in multiple mode and it was
            // submitted...
            // if we find something not part of the options then we record it in
            // _otherValue and set the _qf_other as part of the values
            else {
                $found = false;
                foreach ($this->_options as $option) {
                    if ((string) $value == (string) $option['attr']['value']) {
                        $found = true;
                    }
                }

                if (!$found) {
                    $this->_values[] = '_qf_other';
                    $other_values[] = $value;
                }
            }
        }

        if (!empty($other_values)) {
            $this->_otherValue = implode(',',$other_values);
        }
    }

    // }}}

    // {{{ setDelimiter

    /**
     * Set the delimiter.
     * 
     * @param  string  $delimiter delimiter to use between the subelements
     * @access public
     * @return void
     */
    function setDelimiter($delimiter)
    {
        if (!is_string($delimiter)) {
            $this->delimiter = '<br />';
        } else {
            $this->delimiter = $delimiter;
        }
    }

    // }}}
    // {{{ setList

    /**
     * Set the options to render as an ordered/unordered list
     *
     * @param string $list_type The list type
     * @access public
     * @return void
     */

    function setListType($list_type)
    {
        $this->list_type = $list_type;
    }

    // }}}
    // {{{ setGroup

    /**
     * Tell this element to act like a group when being accepted.
     * 
     * @param  bool   $is_group whether to act like a group or not
     * @see    HTML_QuickForm_element::_type
     * @access public
     * @return void
     */
    function setGroup($is_group = true)
    {
        $this->_type = $is_group ? 'group' : 'select';
    }

    // }}}
    // {{{ accept

   /**
    * Accepts a renderer.  Overload select in case we'd like to see the 
    * checkboxes/radio buttons in a group when renderering with another
    * renderer.
    *
    * This function was copied from HTML_QuickForm_group::accept() and
    * modified for use with this class.
    *
    * @param  HTML_QuickForm_Renderer $renderer the QF renderer
    * @param  bool                    $require  whether a group is required
    * @param  string                  $error    an error message associated with
                                               a group
    * @see    HTML_QuickForm_group::accept()
    * @access public
    * @return void
    */
    function accept($renderer, $required = false, $error = null)
    {
        // if not asked to act like a group, then pass off to regular accept method
        if ($this->_type != 'group') {
            return parent::accept($renderer, $required, $error);
        }
        $this->_separator = null;
        $this->_appendName = null;
        $this->_required = array();


// Beginning of code from HTML_QuickForm_group::accept() 
// ---8<---

        //$this->_createElementsIfNotExist();
        $renderer->startGroup($this, $required, $error);
        $name = $this->getName();

// --->8---

        // use our method to get the elements instead
        $this->_elements = $this->getElements();

// ---8<---

        foreach (array_keys($this->_elements) as $key) {
            $element =& $this->_elements[$key];
            
            if ($this->_appendName) {
                $elementName = $element->getName();
                if (isset($elementName)) {
                    $element->setName($name .
                                      '[' .
                                      (strlen($elementName)? $elementName: $key) .
                                      ']');
                } else {
                    $element->setName($name);
                }
            }

            $required = in_array($element->getName(), $this->_required);

            $element->accept($renderer, $required);

            // restore the element's name
            if ($this->_appendName) {
                $element->setName($elementName);
            }
        }
        $renderer->finishGroup($this);

// --->8---
    }

    // }}}
    // {{{ getElementName

    /**
     * Returns the name of this element. Used by HTML_QuickForm::getSubmitValue()
     * when this element is registered as a group.
     * 
     * @see     HTML_QuickForm::getSubmitValue()
     * @access  public
     * @return  string
     */
    function getElementName()
    {
        return $this->getName();
    }

    // }}}
}

// }}}

if (class_exists('HTML_QuickForm')) {
    HTML_QuickForm::registerElementType('altselect',
                                        'HTML/QuickForm/altselect.php',
                                        'HTML_QuickForm_altselect');
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
