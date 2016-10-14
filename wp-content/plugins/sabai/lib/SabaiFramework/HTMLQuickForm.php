<?php
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/altselect.php';
require_once 'HTML/QuickForm/ElementGrid.php';

class SabaiFramework_HTMLQuickForm extends HTML_QuickForm
{
    static private $_initialized = false;
    protected $_renderer;

    public function __construct($formName = '', $method = 'post', $action = '', $target = '', $attributes = null)
    {
        if (empty($action)) $action = isset($_SERVER['ORIG_REQUEST_URI']) ? $_SERVER['ORIG_REQUEST_URI'] : $_SERVER['REQUEST_URI'];

        // Mostly Copy & Paste from HTML_QuickForm constructor without the magic quotes part
        HTML_Common::HTML_Common($attributes);
        $method = (strtoupper($method) == 'GET') ? 'get' : 'post';
        $target = empty($target) ? array() : array('target' => $target);
        $attributes = array('action' => $action, 'method' => $method, 'id' => $formName) + $target;
        $this->updateAttributes($attributes);

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
                    break;
            }
        }
    }

    public static function init()
    {
        // Initialize only once since these methods just add entries to global/static variables
        if (!self::$_initialized) {
            // HTML_Common uses charset defined here for htmlspecialchars
            HTML_Common::charset(SABAI_CHARSET);

            self::registerElementType('file', 'SabaiFramework_HTMLQuickForm_Element_File');
            self::registerElementType('group', 'SabaiFramework_HTMLQuickForm_Element_Group');
            self::registerElementType('checkbox', 'SabaiFramework_HTMLQuickForm_Element_Checkbox');
            self::registerElementType('grid', 'SabaiFramework_HTMLQuickForm_Element_Grid');
            self::registerElementType('altselect', 'SabaiFramework_HTMLQuickForm_Element_AltSelect');

            self::$_initialized = true;
        }
    }

    public static function registerElementType($type, $class, $file = null)
    {
        parent::registerElementType($type, str_replace('_', '/', $class) . '.php', $class);
    }

    /**
     * Returns a form element of the given type
     *
     * @param     string   $type    element type
     * @param     array    $args    arguments for event
     * @return    HTML_QuickForm_Element
     * @throws    HTML_QuickForm_Error
     */
    public static function createElement($type, array $args = array())
    {
        $type = strtolower($type);
        if (!self::isTypeRegistered($type)) {
            $error = PEAR::raiseError(null, QUICKFORM_UNREGISTERED_ELEMENT, null, E_USER_WARNING, "Element '$type' does not exist in SabaiFramework_HTMLQuickForm::createElement()", 'HTML_QuickForm_Error', true);
            return $error;
        }
        $class = $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'][$type][1];
        $file = $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'][$type][0];
        //if (is_array(self::$_elements[$type])) {
        //    list($class, $file) = self::$_elements[$type];
        //} else {
        //    $class = self::$_elements[$type];
        //    $file = str_replace('_', '/', $class) . '.php';
        //}
        require_once $file;
        $reflection = new ReflectionClass($class);

        return $reflection->newInstanceArgs($args);
    }

    /**
     * Adds an element into the form
     *
     * If $element is a string representing element type, then this
     * method accepts variable number of parameters, their meaning
     * and count depending on $element
     *
     * @param    mixed      $element        element object or type of element to add (text, textarea, file...)
     * @return   HTML_QuickForm_Element     a reference to newly added element
     * @throws   HTML_QuickForm_Error
     */
    public function addElement($element)
    {
        if (!is_object($element)) {
            $args = func_get_args();
            $element = self::createElement($element, array_slice($args, 1));
        }
        $element->onQuickFormEvent('updateValue', null, $this);
        $element_name = $element->getName();

        // Add the element if it is not an incompatible duplicate
        if (!empty($element_name) && isset($this->_elementIndex[$element_name])) {
            if ($this->_elements[$this->_elementIndex[$element_name]]->getType() == $element->getType()) {
                $this->_elements[] = $element;
                $elKeys = array_keys($this->_elements);
                $this->_duplicateIndex[$element_name][] = end($elKeys);
            } else {
                $error = PEAR::raiseError(null, QUICKFORM_INVALID_ELEMENT_NAME, null, E_USER_WARNING, "Element '$element_name' already exists in HTML_QuickForm::addElement()", 'HTML_QuickForm_Error', true);
                return $error;
            }
        } else {
            $this->_elements[] = $element;
            $elKeys = array_keys($this->_elements);
            $this->_elementIndex[$element_name] = end($elKeys);
        }

        return $element;
    }

    public function defaultRenderer()
    {
        return $this->getRenderer();
    }

    public function getRenderer()
    {
        if (!isset($this->_renderer)) {
            $this->_renderer = new SabaiFramework_HTMLQuickForm_Renderer();
        }

        return $this->_renderer;
    }

    public function renderElements($renderer = null)
    {
        if (!isset($renderer)) $renderer = $this->getRenderer();
        $renderer->setFormTemplate('{content}{hidden}');

        return $this->_render($renderer);
    }

    public function render($renderer = null)
    {
        if (!isset($renderer)) $renderer = $this->getRenderer();
        return $this->_render($renderer);
    }

    protected function _render($renderer)
    {
        $this->accept($renderer);

        return $renderer->toHtml();
    }

    public function addHeader($header)
    {
        $this->addElement('header', null, $header);
    }
}

// This must be called to register required element types and rules
SabaiFramework_HTMLQuickForm::init();