<?php
class Sabai_Addon_Field_Helper_TypeImpl extends Sabai_Helper
{
    private $_addons, $_impls = array();

    /**
     * Gets an implementation of Sabai_Addon_Field_IType interface for a given field type
     * @param Sabai $application
     * @param string $type
     * @param bool $useCache
     */
    public function help(Sabai $application, $type, $returnFalse = false, $useCache = true)
    {
        if (!isset($this->_impls[$type])) {
            // Field handlers initialized?
            if (!isset($this->_addons) || !$useCache) {
                $this->_init($application, $useCache);
            }
            // Valid field type?
            if (!isset($this->_addons[$type])
                || (!$addon = $application->getAddon($this->_addons[$type]))
            ) {
                // for deprecated renderer
                if ($type === 'markdown_text') {
                    return $this->help($application, 'text', $returnFalse, $useCache);
                }
                
                if ($returnFalse) return false;
                
                throw new Sabai_UnexpectedValueException(sprintf('Invalid field type: %s', $type));
            }
            $this->_impls[$type] = $addon->fieldGetType($type);
        }

        return $this->_impls[$type];
    }

    private function _init(Sabai $application, $useCache)
    {
        $this->_addons = array();
        foreach ($application->Field_Types($useCache) as $type => $data) {
            $this->_addons[$type] = $data['addon'];
        }
        unset($this->_addons['markdown_text']);
    }
}