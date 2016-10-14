<?php
class Sabai_Addon_Field_Helper_FilterImpl extends Sabai_Helper
{
    private $_impls = array();

    /**
     * Gets an implementation of Sabai_Addon_Field_IFilter interface for a given filter type
     * @param Sabai $application
     * @param string $filter
     */
    public function help(Sabai $application, $filter, $returnFalse = false)
    {
        if (!isset($this->_impls[$filter])) {
            $filters = $application->Field_Filters();
            // Valid filter type?
            if (!isset($filters[$filter])
                || (!$application->isAddonLoaded($filters[$filter]))
            ) {
                // for deprecated filter
                if ($filter === 'markdown_text') {
                    return $this->help($application, 'keyword', $returnFalse);
                }
                
                if ($returnFalse) return false;
                throw new Sabai_UnexpectedValueException(sprintf('Invalid filter type: %s', $filter));
            }
            $this->_impls[$filter] = $application->getAddon($filters[$filter])->fieldGetFilter($filter);
        }

        return $this->_impls[$filter];
    }
}