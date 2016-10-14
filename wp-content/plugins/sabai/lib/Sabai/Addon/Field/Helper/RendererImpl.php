<?php
class Sabai_Addon_Field_Helper_RendererImpl extends Sabai_Helper
{
    private $_impls = array();

    /**
     * Gets an implementation of Sabai_Addon_Field_IRenderer interface for a given renderer type
     * @param Sabai $application
     * @param string $renderer
     */
    public function help(Sabai $application, $renderer, $returnFalse = false)
    {
        if (!isset($this->_impls[$renderer])) {
            $renderers = $application->Field_Renderers();
            // Valid renderer type?
            if (!isset($renderers[$renderer])
                || (!$application->isAddonLoaded($renderers[$renderer]))
            ) {
                // for deprecated renderer
                if ($renderer === 'markdown_text') {
                    return $this->help($application, 'text', $returnFalse);
                }
                
                if ($returnFalse) return false;
                throw new Sabai_UnexpectedValueException(sprintf('Invalid renderer type: %s', $renderer));
            }
            $this->_impls[$renderer] = $application->getAddon($renderers[$renderer])->fieldGetRenderer($renderer);
        }

        return $this->_impls[$renderer];
    }
}