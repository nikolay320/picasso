<?php
class Sabai_Addon_Entity_Helper_BundleLabel extends Sabai_Helper
{
    protected $_labels = array();
    
    public function help(Sabai $application, $bundle, $singular = true)
    {
        if (!$bundle instanceof Sabai_Addon_Entity_Model_Bundle) {
            if (!$_bundle = $application->Entity_Bundle($bundle)) {
                throw new Sabai_RuntimeException('Invalid bundle: ' . $bundle);
            }
            $bundle = $_bundle;
        }
        $index = $singular ? 1 : 0;
        if (!isset($this->_labels[$bundle->name][$index])) {
            $this->_labels[$bundle->name][$index] = $application->Filter('entity_bundle_label', $application->Translate($singular ? $bundle->label_singular : $bundle->label), array($bundle->name, $singular));
        }
        return $this->_labels[$bundle->name][$index];
    }
}