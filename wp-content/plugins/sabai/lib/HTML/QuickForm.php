<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Create, validate and process HTML forms
 * 
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @author      Alexey Borzov <avb@php.net>
 * @copyright   2001-2011 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 * @version     CVS: $Id: QuickForm.php 317587 2011-10-01 07:55:53Z avb $
 * @link        http://pear.php.net/package/HTML_QuickForm
 */

/**
 * PEAR and PEAR_Error classes, for error handling
 */
require_once 'PEAR.php';
/**
 * Base class for all HTML classes
 */
require_once 'HTML/Common.php';

/**
 * Element types known to HTML_QuickForm
 * @see HTML_QuickForm::registerElementType(), HTML_QuickForm::getRegisteredTypes(),
 *      HTML_QuickForm::isTypeRegistered()
 * @global array $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES']
 */ 
$GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'] = 
        array(
            'group'         =>array('HTML/QuickForm/group.php','HTML_QuickForm_group'),
            'hidden'        =>array('HTML/QuickForm/hidden.php','HTML_QuickForm_hidden'),
            'reset'         =>array('HTML/QuickForm/reset.php','HTML_QuickForm_reset'),
            'checkbox'      =>array('HTML/QuickForm/checkbox.php','HTML_QuickForm_checkbox'),
            'file'          =>array('HTML/QuickForm/file.php','HTML_QuickForm_file'),
            'password'      =>array('HTML/QuickForm/password.php','HTML_QuickForm_password'),
            'radio'         =>array('HTML/QuickForm/radio.php','HTML_QuickForm_radio'),
            'button'        =>array('HTML/QuickForm/button.php','HTML_QuickForm_button'),
            'submit'        =>array('HTML/QuickForm/submit.php','HTML_QuickForm_submit'),
            'select'        =>array('HTML/QuickForm/select.php','HTML_QuickForm_select'),            
            'text'          =>array('HTML/QuickForm/text.php','HTML_QuickForm_text'),
            'textarea'      =>array('HTML/QuickForm/textarea.php','HTML_QuickForm_textarea'),            
            'static'        =>array('HTML/QuickForm/static.php','HTML_QuickForm_static'),
            'header'        =>array('HTML/QuickForm/header.php', 'HTML_QuickForm_header'),
        );

// {{{ error codes

/**#@+
 * Error codes for HTML_QuickForm
 *
 * Codes are mapped to textual messages by errorMessage() method, if you add a 
 * new code be sure to add a new message for it to errorMessage()
 *
 * @see HTML_QuickForm::errorMessage()
 */ 
define('QUICKFORM_OK',                      1);
define('QUICKFORM_ERROR',                  -1);
define('QUICKFORM_NONEXIST_ELEMENT',       -3);
define('QUICKFORM_UNREGISTERED_ELEMENT',   -5);
define('QUICKFORM_INVALID_ELEMENT_NAME',   -6);
/**#@-*/

// }}}

/**
 * Create, validate and process HTML forms
 *
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @author      Alexey Borzov <avb@php.net>
 * @version     Release: 3.2.13
 */
class HTML_QuickForm extends HTML_Common
{
    // {{{ properties

    /**
     * Array containing the form fields
     * @since     1.0
     * @var  array
     * @access   private
     */
    var $_elements = array();

    /**
     * Array containing element name to index map
     * @since     1.1
     * @var  array
     * @access   private
     */
    var $_elementIndex = array();

    /**
     * Array containing indexes of duplicate elements
     * @since     2.10
     * @var  array
     * @access   private
     */
    var $_duplicateIndex = array();

    /**
     * Array containing required field IDs
     * @since     1.0
     * @var  array
     * @access   private
     */ 
    var $_required = array();

    /**
     * Datasource object implementing the informal
     * datasource protocol
     * @since     3.3
     * @var  object
     * @access   private
     */
    var $_datasource;

    /**
     * Array of default form values
     * @since     2.0
     * @var  array
     * @access   private
     */
    var $_defaultValues = array();

    /**
     * Array of constant form values
     * @since     2.0
     * @var  array
     * @access   private
     */
    var $_constantValues = array();

    /**
     * Array of submitted form values
     * @since     1.0
     * @var  array
     * @access   private
     */
    var $_submitValues = array();

    /**
     * Array of submitted form files
     * @since     1.0
     * @var  integer
     * @access   public
     */
    var $_submitFiles = array();

    /**
     * Value for maxfilesize hidden element if form contains file input
     * @since     1.0
     * @var  integer
     * @access   public
     */
    var $_maxFileSize = 1048576; // 1 Mb = 1048576

    /**
     * Array containing the validation errors
     * @since     1.0
     * @var  array
     * @access   private
     */
    var $_errors = array();

    /**
     * Note for required fields in the form
     * @var       string
     * @since     1.0
     * @access    private
     */
    var $_requiredNote = '<span style="font-size:80%; color:#ff0000;">*</span><span style="font-size:80%;"> denotes required field</span>';

    /**
     * Whether the form was submitted
     * @var       boolean
     * @access    private
     */
    var $_flagSubmitted = false;

    // }}}
    // {{{ constructor

    /**
     * Class constructor
     * @param    string      $formName          Form's name.
     * @param    string      $method            (optional)Form's method defaults to 'POST'
     * @param    string      $action            (optional)Form's action
     * @param    string      $target            (optional)Form's target defaults to '_self'
     * @param    mixed       $attributes        (optional)Extra attributes for <form> tag
     * @param    bool        $trackSubmit       (optional)Whether to track if the form was submitted by adding a special hidden field
     * @access   public
     */
    function HTML_QuickForm($formName='', $method='post', $action='', $target='', $attributes=null, $trackSubmit = false)
    {
        HTML_Common::HTML_Common($attributes);
        $method = (strtoupper($method) == 'GET') ? 'get' : 'post';
        $action = ($action == '') ? $_SERVER['PHP_SELF'] : $action;
        $target = empty($target) ? array() : array('target' => $target);
        $attributes = array('action'=>$action, 'method'=>$method, 'name'=>$formName, 'id'=>$formName) + $target;
        $this->updateAttributes($attributes);
        if (!$trackSubmit || isset($_REQUEST['_qf__' . $formName])) {
            if (1 == get_magic_quotes_gpc()) {
                $this->_submitValues = $this->_recursiveFilter('stripslashes', 'get' == $method? $_GET: $_POST);
                foreach ($_FILES as $keyFirst => $valFirst) {
                    foreach ($valFirst as $keySecond => $valSecond) {
                        if ('name' == $keySecond) {
                            $this->_submitFiles[$keyFirst][$keySecond] = $this->_recursiveFilter('stripslashes', $valSecond);
                        } else {
                            $this->_submitFiles[$keyFirst][$keySecond] = $valSecond;
                        }
                    }
                }
            } else {
                $this->_submitValues = 'get' == $method? $_GET: $_POST;
                $this->_submitFiles  = $_FILES;
            }
            $this->_flagSubmitted = count($this->_submitValues) > 0 || count($this->_submitFiles) > 0;
        }
        if ($trackSubmit) {
            unset($this->_submitValues['_qf__' . $formName]);
            $this->addElement('hidden', '_qf__' . $formName, null);
        }
        if (preg_match('/^([0-9]+)([a-zA-Z]*)$/', ini_get('upload_max_filesize'), $matches)) {
            // see http://www.php.net/manual/en/faq.using.php#faq.using.shorthandbytes
            switch (strtoupper($matches['2'])) {
                case 'G':
                    $this->_maxFileSize = $matches['1'] * 1073741824;
                    break;
                case 'M':
                    $this->_maxFileSize = $matches['1'] * 1048576;
                    break;
                case 'K':
                    $this->_maxFileSize = $matches['1'] * 1024;
                    break;
                default:
                    $this->_maxFileSize = $matches['1'];
            }
        }    
    } // end constructor

    // }}}
    // {{{ apiVersion()

    /**
     * Returns the current API version
     *
     * @since     1.0
     * @access    public
     * @return    float
     */
    function apiVersion()
    {
        return 3.2;
    } // end func apiVersion

    // }}}
    // {{{ registerElementType()

    /**
     * Registers a new element type
     *
     * @param     string    $typeName   Name of element type
     * @param     string    $include    Include path for element type
     * @param     string    $className  Element class name
     * @since     1.0
     * @access    public
     * @return    void
     */
    public static function registerElementType($typeName, $include, $className)
    {
        $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'][strtolower($typeName)] = array($include, $className);
    } // end func registerElementType

    // }}}
    // 
    // {{{ elementExists()

    /**
     * Returns true if element is in the form
     *
     * @param     string   $element         form name of element to check
     * @since     1.0
     * @access    public
     * @return    boolean
     */
    function elementExists($element=null)
    {
        return isset($this->_elementIndex[$element]);
    } // end func elementExists

    // }}}

    // {{{ setDefaults()

    /**
     * Initializes default form values
     *
     * @param     array    $defaultValues       values used to fill the form
     * @param     mixed    $filter              (optional) filter(s) to apply to all default values
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    HTML_QuickForm_Error
     */
    function setDefaults($defaultValues = null)
    {
        if (is_array($defaultValues)) {
            $this->_defaultValues = HTML_QuickForm::arrayMerge($this->_defaultValues, $defaultValues);
            foreach (array_keys($this->_elements) as $key) {
                $this->_elements[$key]->onQuickFormEvent('updateValue', null, $this);
            }
        }
    } // end func setDefaults

    // }}}
    // {{{ setConstants()

    /**
     * Initializes constant form values.
     * These values won't get overridden by POST or GET vars
     *
     * @param     array   $constantValues        values used to fill the form    
     *
     * @since     2.0
     * @access    public
     * @return    void
     * @throws    HTML_QuickForm_Error
     */
    function setConstants($constantValues = null)
    {
        if (is_array($constantValues)) {
            $this->_constantValues = HTML_QuickForm::arrayMerge($this->_constantValues, $constantValues);
            foreach (array_keys($this->_elements) as $key) {
                $this->_elements[$key]->onQuickFormEvent('updateValue', null, $this);
            }
        }
    } // end func setConstants

    // }}}
    // {{{ setMaxFileSize()

    /**
     * Sets the value of MAX_FILE_SIZE hidden element
     *
     * @param     int    $bytes    Size in bytes
     * @since     3.0
     * @access    public
     * @return    void
     */
    function setMaxFileSize($bytes = 0)
    {
        if ($bytes > 0) {
            $this->_maxFileSize = $bytes;
        }
        if (!$this->elementExists('MAX_FILE_SIZE')) {
            $this->addElement('hidden', 'MAX_FILE_SIZE', $this->_maxFileSize);
        } else {
            $el = $this->getElement('MAX_FILE_SIZE');
            $el->updateAttributes(array('value' => $this->_maxFileSize));
        }
    } // end func setMaxFileSize

    // }}}
    // {{{ getMaxFileSize()

    /**
     * Returns the value of MAX_FILE_SIZE hidden element
     *
     * @since     3.0
     * @access    public
     * @return    int   max file size in bytes
     */
    function getMaxFileSize()
    {
        return $this->_maxFileSize;
    } // end func getMaxFileSize

    // }}}
    // {{{ &createElement()

    /**
     * Creates a new form element of the given type.
     * 
     * This method accepts variable number of parameters, their 
     * meaning and count depending on $elementType
     *
     * @param     string     $elementType    type of element to add (text, textarea, file...)
     * @since     1.0
     * @access    public
     * @return    HTML_QuickForm_Element
     * @throws    HTML_QuickForm_Error
     */
    public static function createElement($elementType)
    {
        $args    =  func_get_args();
        $element = HTML_QuickForm::_loadElement('createElement', $elementType, array_slice($args, 1));
        return $element;
    } // end func createElement

    // }}}
    // {{{ _loadElement()

    /**
     * Returns a form element of the given type
     *
     * @param     string   $event   event to send to newly created element ('createElement' or 'addElement')
     * @param     string   $type    element type
     * @param     array    $args    arguments for event
     * @since     2.0
     * @access    private
     * @return    HTML_QuickForm_Element
     * @throws    HTML_QuickForm_Error
     */
    public static function _loadElement($event, $type, $args)
    {
        $type = strtolower($type);
        if (!self::isTypeRegistered($type)) {
            $error = PEAR::raiseError(null, QUICKFORM_UNREGISTERED_ELEMENT, null, E_USER_WARNING, "Element '$type' does not exist in HTML_QuickForm::_loadElement()", 'HTML_QuickForm_Error', true);
            return $error;
        }
        $className = $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'][$type][1];
        $includeFile = $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'][$type][0];
        include_once($includeFile);
        $elementObject = new $className();
        for ($i = 0; $i < 5; $i++) {
            if (!isset($args[$i])) {
                $args[$i] = null;
            }
        }
        $err = $elementObject->onQuickFormEvent($event, $args, $this);
        if ($err !== true) {
            return $err;
        }
        return $elementObject;
    } // end func _loadElement

    // }}}
    // {{{ addElement()

    /**
     * Adds an element into the form
     * 
     * If $element is a string representing element type, then this 
     * method accepts variable number of parameters, their meaning 
     * and count depending on $element
     *
     * @param    mixed      $element        element object or type of element to add (text, textarea, file...)
     * @since    1.0
     * @return   HTML_QuickForm_Element     a reference to newly added element
     * @access   public
     * @throws   HTML_QuickForm_Error
     */
    function addElement($element)
    {
        if (is_object($element) && is_subclass_of($element, 'html_quickform_element')) {
           $elementObject = &$element;
           $elementObject->onQuickFormEvent('updateValue', null, $this);
        } else {
            $args = func_get_args();
            $elementObject =& $this->_loadElement('addElement', $element, array_slice($args, 1));
            if (PEAR::isError($elementObject)) {
                return $elementObject;
            }
        }
        $elementName = $elementObject->getName();

        // Add the element if it is not an incompatible duplicate
        if (!empty($elementName) && isset($this->_elementIndex[$elementName])) {
            if ($this->_elements[$this->_elementIndex[$elementName]]->getType() ==
                $elementObject->getType()) {
                $this->_elements[] =& $elementObject;
                $elKeys = array_keys($this->_elements);
                $this->_duplicateIndex[$elementName][] = end($elKeys);
            } else {
                $error = PEAR::raiseError(null, QUICKFORM_INVALID_ELEMENT_NAME, null, E_USER_WARNING, "Element '$elementName' already exists in HTML_QuickForm::addElement()", 'HTML_QuickForm_Error', true);
                return $error;
            }
        } else {
            $this->_elements[] =& $elementObject;
            $elKeys = array_keys($this->_elements);
            $this->_elementIndex[$elementName] = end($elKeys);
        }

        return $elementObject;
    } // end func addElement
    
    // }}}
    
    // {{{ addGroup()

    /**
     * Adds an element group
     * @param    array      $elements       array of elements composing the group
     * @param    string     $name           (optional)group name
     * @param    string     $groupLabel     (optional)group label
     * @param    string     $separator      (optional)string to separate elements
     * @param    string     $appendName     (optional)specify whether the group name should be
     *                                      used in the form element name ex: group[element]
     * @return   HTML_QuickForm_group       reference to a newly added group
     * @since    2.8
     * @access   public
     * @throws   HTML_QuickForm_Error
     */
    function addGroup($elements, $name=null, $groupLabel='', $separator=null, $appendName = true)
    {
        static $anonGroups = 1;

        if (0 == strlen($name)) {
            $name       = 'qf_group_' . $anonGroups++;
            $appendName = false;
        }
        $group = $this->addElement('group', $name, $groupLabel, $elements, $separator, $appendName);
        return $group;
    } // end func addGroup
    
    // }}}
    // {{{ &getElement()

    /**
     * Returns a reference to the element
     *
     * @param     string     $element    Element name
     * @since     2.0
     * @access    public
     * @return    HTML_QuickForm_element    reference to element
     * @throws    HTML_QuickForm_Error
     */
    function getElement($element)
    {
        if (isset($this->_elementIndex[$element])) {
            return $this->_elements[$this->_elementIndex[$element]];
        } else {
            $error = PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$element' does not exist in HTML_QuickForm::getElement()", 'HTML_QuickForm_Error', true);
            return $error;
        }
    } // end func getElement

    // }}}
    // {{{ &getElementValue()

    /**
     * Returns the element's raw value
     * 
     * This returns the value as submitted by the form (not filtered) 
     * or set via setDefaults() or setConstants()
     *
     * @param     string     $element    Element name
     * @since     2.0
     * @access    public
     * @return    mixed     element value
     * @throws    HTML_QuickForm_Error
     */
    function &getElementValue($element)
    {
        if (!isset($this->_elementIndex[$element])) {
            $error = PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$element' does not exist in HTML_QuickForm::getElementValue()", 'HTML_QuickForm_Error', true);
            return $error;
        }
        $value = $this->_elements[$this->_elementIndex[$element]]->getValue();
        if (isset($this->_duplicateIndex[$element])) {
            foreach ($this->_duplicateIndex[$element] as $index) {
                if (null !== ($v = $this->_elements[$index]->getValue())) {
                    if (is_array($value)) {
                        $value[] = $v;
                    } else {
                        $value = (null === $value)? $v: array($value, $v);
                    }
                }
            }
        }
        return $value;
    } // end func getElementValue

    // }}}
    // {{{ getSubmitValue()

    /**
     * Returns the elements value after submit and filter
     *
     * @param     string     Element name
     * @since     2.0
     * @access    public
     * @return    mixed     submitted element value or null if not set
     */    
    function getSubmitValue($elementName)
    {
        $value = null;
        if (isset($this->_submitValues[$elementName]) || isset($this->_submitFiles[$elementName])) {
            $value = isset($this->_submitValues[$elementName])? $this->_submitValues[$elementName]: array();
            if (is_array($value) && isset($this->_submitFiles[$elementName])) {
                foreach ($this->_submitFiles[$elementName] as $k => $v) {
                    $value = HTML_QuickForm::arrayMerge($value, $this->_reindexFiles($this->_submitFiles[$elementName][$k], $k));
                }
            }

        } elseif ('file' == $this->getElementType($elementName)) {
            return $this->getElementValue($elementName);

        } elseif (false !== ($pos = strpos($elementName, '['))) {
            $base = str_replace(
                        array('\\', '\''), array('\\\\', '\\\''), 
                        substr($elementName, 0, $pos)
                    );
            $idx  = "['" . str_replace(
                        array('\\', '\'', ']', '['), array('\\\\', '\\\'', '', "']['"), 
                        substr($elementName, $pos + 1, -1)
                    ) . "']";
            if (isset($this->_submitValues[$base])) {
                $value = eval("return (isset(\$this->_submitValues['{$base}']{$idx})) ? \$this->_submitValues['{$base}']{$idx} : null;");
            }

            if ((is_array($value) || null === $value) && isset($this->_submitFiles[$base])) {
                $props = array('name', 'type', 'size', 'tmp_name', 'error');
                $code  = "if (!isset(\$this->_submitFiles['{$base}']['name']{$idx})) {\n" .
                         "    return null;\n" .
                         "} else {\n" .
                         "    \$v = array();\n";
                foreach ($props as $prop) {
                    $code .= "    \$v = HTML_QuickForm::arrayMerge(\$v, \$this->_reindexFiles(\$this->_submitFiles['{$base}']['{$prop}']{$idx}, '{$prop}'));\n";
                }
                $fileValue = eval($code . "    return \$v;\n}\n");
                if (null !== $fileValue) {
                    $value = null === $value? $fileValue: HTML_QuickForm::arrayMerge($value, $fileValue);
                }
            }
        }
        
        // This is only supposed to work for groups with appendName = false
        if (null === $value && 'group' == $this->getElementType($elementName)) {
            $group    =& $this->getElement($elementName);
            $elements =& $group->getElements();
            foreach (array_keys($elements) as $key) {
                $name = $group->getElementName($key);
                // prevent endless recursion in case of radios and such
                if ($name != $elementName) {
                    if (null !== ($v = $this->getSubmitValue($name))) {
                        $value[$name] = $v;
                    }
                }
            }
        }
        return $value;
    } // end func getSubmitValue

    // }}}
    // {{{ _reindexFiles()

   /**
    * A helper function to change the indexes in $_FILES array
    *
    * @param  mixed   Some value from the $_FILES array
    * @param  string  The key from the $_FILES array that should be appended
    * @return array
    */
    function _reindexFiles($value, $key)
    {
        if (!is_array($value)) {
            return array($key => $value);
        } else {
            $ret = array();
            foreach ($value as $k => $v) {
                $ret[$k] = $this->_reindexFiles($v, $key);
            }
            return $ret;
        }
    }

    // }}}
    // {{{ getElementError()

    /**
     * Returns error corresponding to validated element
     *
     * @param     string    $element        Name of form element to check
     * @since     1.0
     * @access    public
     * @return    string    error message corresponding to checked element
     */
    function getElementError($element)
    {
        if (isset($this->_errors[$element])) {
            return $this->_errors[$element];
        }
    } // end func getElementError
    
    // }}}
    // {{{ setElementError()

    /**
     * Set error message for a form element
     *
     * @param     string    $element    Name of form element to set error for
     * @param     string    $message    Error message, if empty then removes the current error message
     * @since     1.0       
     * @access    public
     * @return    void
     */
    function setElementError($element, $message = null)
    {
        if (!empty($message)) {
            $this->_errors[$element] = $message;
        } else {
            unset($this->_errors[$element]);
        }
    } // end func setElementError
         
     // }}}
     // {{{ getElementType()

     /**
      * Returns the type of the given element
      *
      * @param      string    $element    Name of form element
      * @since      1.1
      * @access     public
      * @return     string    Type of the element, false if the element is not found
      */
     function getElementType($element)
     {
         if (isset($this->_elementIndex[$element])) {
             return $this->_elements[$this->_elementIndex[$element]]->getType();
         }
         return false;
     } // end func getElementType

     // }}}
     // {{{ updateElementAttr()

    /**
     * Updates Attributes for one or more elements
     *
     * @param      mixed    $elements   Array of element names/objects or string of elements to be updated
     * @param      mixed    $attrs      Array or sting of html attributes
     * @since      2.10
     * @access     public
     * @return     void
     */
    function updateElementAttr($elements, $attrs)
    {
        if (is_string($elements)) {
            $elements = preg_split('/[ ]?,[ ]?/', $elements);
        }
        foreach (array_keys($elements) as $key) {
            if (is_object($elements[$key]) && is_a($elements[$key], 'HTML_QuickForm_element')) {
                $elements[$key]->updateAttributes($attrs);
            } elseif (isset($this->_elementIndex[$elements[$key]])) {
                $this->_elements[$this->_elementIndex[$elements[$key]]]->updateAttributes($attrs);
                if (isset($this->_duplicateIndex[$elements[$key]])) {
                    foreach ($this->_duplicateIndex[$elements[$key]] as $index) {
                        $this->_elements[$index]->updateAttributes($attrs);
                    }
                }
            }
        }
    } // end func updateElementAttr

    // }}}
    // {{{ removeElement()

    /**
     * Removes an element
     *
     * The method "unlinks" an element from the form, returning the reference
     * to the element object. If several elements named $elementName exist, 
     * it removes the first one, leaving the others intact.
     * 
     * @param string    $elementName The element name
     * @param boolean   $removeRules True if rules for this element are to be removed too                     
     * @access public
     * @since 2.0
     * @return HTML_QuickForm_element    a reference to the removed element
     * @throws HTML_QuickForm_Error
     */
    function removeElement($elementName, $removeRules = true)
    {
        if (!isset($this->_elementIndex[$elementName])) {
            $error = PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$elementName' does not exist in HTML_QuickForm::removeElement()", 'HTML_QuickForm_Error', true);
            return $error;
        }
        $el =& $this->_elements[$this->_elementIndex[$elementName]];
        unset($this->_elements[$this->_elementIndex[$elementName]]);
        if (empty($this->_duplicateIndex[$elementName])) {
            unset($this->_elementIndex[$elementName]);
        } else {
            $this->_elementIndex[$elementName] = array_shift($this->_duplicateIndex[$elementName]);
        }
        if ($removeRules) {
            $this->_required = array_diff($this->_required, array($elementName));
            unset($this->_errors[$elementName]);
        }
        return $el;
    } // end func removeElement

    // }}}
    
    // {{{ _recursiveFilter()

    /**
     * Recursively apply a filter function
     *
     * @param     string   $filter    filter to apply
     * @param     mixed    $value     submitted values
     * @since     2.0
     * @access    private
     * @return    cleaned values
     */
    function _recursiveFilter($filter, $value)
    {
        if (is_array($value)) {
            $cleanValues = array();
            foreach ($value as $k => $v) {
                $cleanValues[$k] = $this->_recursiveFilter($filter, $v);
            }
            return $cleanValues;
        } else {
            return call_user_func($filter, $value);
        }
    } // end func _recursiveFilter

    // }}}
    // {{{ arrayMerge()

   /**
    * Merges two arrays
    *
    * Merges two array like the PHP function array_merge but recursively.
    * The main difference is that existing keys will not be renumbered
    * if they are integers.
    *
    * @access   public
    * @param    array   $a  original array
    * @param    array   $b  array which will be merged into first one
    * @return   array   merged array
    */
    function arrayMerge($a, $b)
    {
        foreach ($b as $k => $v) {
            if (is_array($v)) {
                if (isset($a[$k]) && !is_array($a[$k])) {
                    $a[$k] = $v;
                } else {
                    if (!isset($a[$k])) {
                        $a[$k] = array();
                    }
                    $a[$k] = HTML_QuickForm::arrayMerge($a[$k], $v);
                }
            } else {
                $a[$k] = $v;
            }
        }
        return $a;
    } // end func arrayMerge

    // }}}
    // {{{ isTypeRegistered()

    /**
     * Returns whether or not the form element type is supported
     *
     * @param     string   $type     Form element type
     * @since     1.0
     * @access    public
     * @return    boolean
     */
    public static function isTypeRegistered($type)
    {
        return isset($GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'][strtolower($type)]);
    } // end func isTypeRegistered

    // }}}
    // {{{ getRegisteredTypes()

    /**
     * Returns an array of registered element types
     *
     * @since     1.0
     * @access    public
     * @return    array
     */
    function getRegisteredTypes()
    {
        return array_keys($GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES']);
    } // end func getRegisteredTypes

    // }}}
    
    // {{{ isElementRequired()

    /**
     * Returns whether or not the form element is required
     *
     * @param     string   $element     Form element name
     * @since     1.0
     * @access    public
     * @return    boolean
     */
    function isElementRequired($element)
    {
        return in_array($element, $this->_required, true);
    } // end func isElementRequired

    // }}}

    // {{{ setRequiredNote()

    /**
     * Sets required-note
     *
     * @param     string   $note        Message indicating some elements are required
     * @since     1.1
     * @access    public
     * @return    void
     */
    function setRequiredNote($note)
    {
        $this->_requiredNote = $note;
    } // end func setRequiredNote

    // }}}
    // {{{ getRequiredNote()

    /**
     * Returns the required note
     *
     * @since     2.0
     * @access    public
     * @return    string
     */
    function getRequiredNote()
    {
        return $this->_requiredNote;
    } // end func getRequiredNote

    // }}}
    // {{{ validate()

    /**
     * Performs the server side validation
     * @access    public
     * @since     1.0
     * @return    boolean   true if no error found
     * @throws    HTML_QuickForm_Error
     */
    function validate()
    {
        return $this->isSubmitted() && !count($this->_errors);
    } // end func validate

    // }}}

    // {{{ accept()

   /**
    * Accepts a renderer
    *
    * @param object     An HTML_QuickForm_Renderer object
    * @since 3.0
    * @access public
    * @return void
    */
    function accept($renderer)
    {
        $renderer->startForm($this);
        foreach (array_keys($this->_elements) as $key) {
            $element =& $this->_elements[$key];
            $elementName = $element->getName();
            $required    = $this->isElementRequired($elementName);
            $error       = $this->getElementError($elementName);
            $element->accept($renderer, $required, $error);
        }
        $renderer->finishForm($this);
    } // end func accept

    // }}}
    // {{{ defaultRenderer()

   /**
    * Returns a reference to default renderer object
    *
    * @access public
    * @since 3.0
    * @return object a default renderer object
    */
    function defaultRenderer()
    {
        if (!isset($GLOBALS['_HTML_QuickForm_default_renderer'])) {
            include_once('HTML/QuickForm/Renderer/Default.php');
            $GLOBALS['_HTML_QuickForm_default_renderer'] = new HTML_QuickForm_Renderer_Default();
        }
        return $GLOBALS['_HTML_QuickForm_default_renderer'];
    } // end func defaultRenderer

    // }}}
    // {{{ toHtml ()

    /**
     * Returns an HTML version of the form
     *
     * @param string $in_data (optional) Any extra data to insert right
     *               before form is rendered.  Useful when using templates.
     *
     * @return   string     Html version of the form
     * @since     1.0
     * @access   public
     */
    function toHtml ($in_data = null)
    {
        if (!is_null($in_data)) {
            $this->addElement('html', $in_data);
        }
        $renderer = $this->defaultRenderer();
        $this->accept($renderer);
        return $renderer->toHtml();
    } // end func toHtml

    // }}}
    
    // {{{ getSubmitValues()

    /**
     * Returns the values submitted by the form
     *
     * @since     2.0
     * @access    public
     * @param     bool      Whether uploaded files should be returned too
     * @return    array
     */
    function getSubmitValues($mergeFiles = false)
    {
        return $mergeFiles? HTML_QuickForm::arrayMerge($this->_submitValues, $this->_submitFiles): $this->_submitValues;
    } // end func getSubmitValues

    // }}}
    // {{{ toArray()

    /**
     * Returns the form's contents in an array.
     *
     * The description of the array structure is in HTML_QuickForm_Renderer_Array docs
     * 
     * @since     2.0
     * @access    public
     * @param     bool      Whether to collect hidden elements (passed to the Renderer's constructor)
     * @return    array of form contents
     */
    function toArray($collectHidden = false)
    {
        include_once 'HTML/QuickForm/Renderer/Array.php';
        $renderer = new HTML_QuickForm_Renderer_Array($collectHidden);
        $this->accept($renderer);
        return $renderer->toArray();
     } // end func toArray

    // }}}
  
    // {{{ isSubmitted()

   /**
    * Tells whether the form was already submitted
    *
    * This is useful since the _submitFiles and _submitValues arrays
    * may be completely empty after the trackSubmit value is removed.
    *
    * @access public
    * @return bool
    */
    function isSubmitted()
    {
        return $this->_flagSubmitted;
    }


    // }}}
    // {{{ isError()

    /**
     * Tell whether a result from a QuickForm method is an error (an instance of HTML_QuickForm_Error)
     *
     * @access public
     * @param mixed     result code
     * @return bool     whether $value is an error
     * @static
     */
    function isError($value)
    {
        return (is_object($value) && is_a($value, 'html_quickform_error'));
    } // end func isError

    // }}}
    // {{{ errorMessage()

    /**
     * Return a textual error message for an QuickForm error code
     *
     * @access  public
     * @param   int     error code
     * @return  string  error message
     * @static
     */
    function errorMessage($value)
    {
        // make the variable static so that it only has to do the defining on the first call
        static $errorMessages;

        // define the varies error messages
        if (!isset($errorMessages)) {
            $errorMessages = array(
                QUICKFORM_OK                    => 'no error',
                QUICKFORM_ERROR                 => 'unknown error',
                QUICKFORM_NONEXIST_ELEMENT      => 'nonexistent html element',
                QUICKFORM_UNREGISTERED_ELEMENT  => 'unregistered element',
                QUICKFORM_INVALID_ELEMENT_NAME  => 'element already exists',
            );
        }

        // If this is an error object, then grab the corresponding error code
        if (HTML_QuickForm::isError($value)) {
            $value = $value->getCode();
        }

        // return the textual error message corresponding to the code
        return isset($errorMessages[$value]) ? $errorMessages[$value] : $errorMessages[QUICKFORM_ERROR];
    } // end func errorMessage

    // }}}
} // end class HTML_QuickForm

/**
 * Class for errors thrown by HTML_QuickForm package
 *
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @version     Release: 3.2.13
 */
class HTML_QuickForm_Error extends PEAR_Error {

    // {{{ properties

    /**
    * Prefix for all error messages
    * @var string
    */
    var $error_message_prefix = 'QuickForm Error: ';

    // }}}
    // {{{ constructor

    /**
    * Creates a quickform error object, extending the PEAR_Error class
    *
    * @param int   $code the error code
    * @param int   $mode the reaction to the error, either return, die or trigger/callback
    * @param int   $level intensity of the error (PHP error code)
    * @param mixed $debuginfo any information that can inform user as to nature of the error
    */
    function HTML_QuickForm_Error($code = QUICKFORM_ERROR, $mode = PEAR_ERROR_RETURN,
                         $level = E_USER_NOTICE, $debuginfo = null)
    {
        if (is_int($code)) {
            $this->PEAR_Error(HTML_QuickForm::errorMessage($code), $code, $mode, $level, $debuginfo);
        } else {
            $this->PEAR_Error("Invalid error code: $code", QUICKFORM_ERROR, $mode, $level, $debuginfo);
        }
    }

    // }}}
} // end class HTML_QuickForm_Error
?>
