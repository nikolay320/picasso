<?php
class SabaiFramework_HTMLQuickForm_Renderer_Array extends SabaiFramework_HTMLQuickForm_Renderer
{
    /**#@+
    * @access private
    */
   /**
    * An array being generated
    * @var array
    */
    var $_ary;

   /**
    * Number of sections in the form (i.e. number of headers in it)
    * @var integer
    */
    var $_sectionCount;

   /**
    * Current section number
    * @var integer
    */
    var $_currentSection;

   /**
    * Array representing current group
    * @var array
    */
    var $_currentGroup = array();
	
	var  $_currentGroupIndex = 0;

   /**
    * Additional style information for different elements
    * @var array
    */
    var $_elementStyles = array();

   /**
    * true: collect all hidden elements into string; false: process them as usual form elements
    * @var bool
    */
    var $_collectHidden = false;

   /**
    * true:  render an array of labels to many labels, $key 0 named 'label', the rest "label_$key"
    * false: leave labels as defined
    * @var bool
    */
    var $_staticLabels = false;
    
    /**
    * Returns the resultant array
    *
    * @access public
    * @return array
    */
    function toArray()
    {
        return $this->_ary;
    }


    function startForm($form)
    {
        $this->_ary = array(
            'attributes'        => $form->getAttributes(true),
            'errors'            => array()
        );
        if ($this->_collectHidden) {
            $this->_ary['hidden'] = '';
        }
        $this->_elementIdx     = 1;
        $this->_currentSection = null;
        $this->_sectionCount   = 0;
    } // end func startForm


    function renderHeader($header)
    {
        $this->_ary['sections'][$this->_sectionCount] = array(
            'header' => $header->toHtml(),
            'name'   => $header->getName(),
            'class' => $this->_formClass,
        );
        $this->_currentSection = $this->_sectionCount++;
    } // end func renderHeader


    function renderElement($element, $required, $error)
    {
        $elAry = $this->_elementToArray($element, $required, $error);
        if (!empty($error)) {
            $this->_ary['errors'][$elAry['name']] = $error;
        }
        $this->_storeArray($elAry);
    } // end func renderElement


    function renderHidden($element)
    {
        if ($this->_collectHidden) {
            $this->_ary['hidden'] .= $element->toHtml() . "\n";
        } else {
            $this->renderElement($element, false, null);
        }
    } // end func renderHidden


    function startGroup($group, $required, $error)
    {
        $this->_currentGroupIndex++;
        $this->_currentGroup[$this->_currentGroupIndex] = $this->_elementToArray($group, $required, $error);
        if (!empty($error)) {
            $this->_ary['errors'][$this->_currentGroup[$this->_currentGroupIndex]['name']] = $error;
        }
    } // end func startGroup


    function finishGroup($group)
    {
        //$this->_storeArray($this->_currentGroup[$this->_currentGroupIndex]);
        $current_group = $this->_currentGroup[$this->_currentGroupIndex];
        unset($this->_currentGroup[$this->_currentGroupIndex]);
        --$this->_currentGroupIndex;
        if (isset($this->_currentGroup[$this->_currentGroupIndex])) {
            $this->_currentGroup[$this->_currentGroupIndex]['elements'][$group->getName()] = $current_group;
        } else {
            $this->_ary['elements'][$group->getName()] = $current_group;    
        }
    } // end func finishGroup


   /**
    * Creates an array representing an element
    *
    * @access private
    * @param  HTML_QuickForm_element    element being processed
    * @param  bool                      Whether an element is required
    * @param  string                    Error associated with the element
    * @return array
    */
    function _elementToArray($element, $required, $error)
    {
        $ret = array(
            'name' => $element->getName(),
            'value' => $element->getValue(),
            'type' => $element->getType(),
            'required' => $required || !empty($this->_elementRequired[$element->getName()]),
            'error' => isset($error) ? $error : @$this->_elementErrors[$element->getName()],
            'class' => @$this->_elementClass[$element->getName()],
            'id' => @$this->_elementId[$element->getName()],
            'prefix' => @$this->_elementPrefix[$element->getName()],
            'suffix' => @$this->_elementSuffix[$element->getName()],
            'field_prefix' => @$this->_elementFieldPrefix[$element->getName()],
            'field_suffix' => @$this->_elementFieldSuffix[$element->getName()],
            'label' => $element->getLabel(),
        );
        if ('group' == $ret['type']) {
            $ret['separator'] = $element->_separator;
            $ret['elements']  = array();
            $ret['position'] = $element->getPosition();
        } else {
            /*
            if (null !== ($size = $element->getAttribute('size'))
                && $size > 5
            ) {
                $element->removeAttribute('size');
                if (strpos($element->getAttribute('style'), 'em')) {
                    if ($ret['field_prefix'] || $ret['field_suffix']) {
                        $element->setAttribute('style', 'width:90%;');
                    } else {
                        $element->setAttribute('style', 'width:100%;');
                    }
                }
            }
            */
            $ret['html'] = $element->toHtml();
        }
        
        if ($attr = $element->getAttributes()) {
            $ret += $attr;
        }
        return $ret;
    }

   /**
    * Stores an array representation of an element in the form array
    *
    * @access private
    * @param array  Array representation of an element
    * @return void
    */
    function _storeArray($element)
    {
        // where should we put this element...
        if (isset($this->_currentGroup[$this->_currentGroupIndex])) {
            $this->_currentGroup[$this->_currentGroupIndex]['elements'][$element['name']][] = $element;
        } elseif (isset($this->_currentSection)) {
            $this->_ary['sections'][$this->_currentSection]['elements'][$element['name']][] = $element;
        } else {
            $this->_ary['elements'][$element['name']][] = $element;
        }
    }
}
