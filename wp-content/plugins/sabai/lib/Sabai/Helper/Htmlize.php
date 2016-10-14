<?php
class Sabai_Helper_Htmlize extends Sabai_Helper
{
    protected $_inlineTags;
    
    /**
     * @return string Filtered text
     * @param Sabai $application
     * @param string $text Text to filter
     * @param bool $inlineTagsOnly
     */
    public function help(Sabai $application, $text, $inlineTagsOnly = false)
    {
        if ($inlineTagsOnly) {
            if (!isset($this->_inlineTags)) {
                $this->_inlineTags = array('a', 'abbr', 'acronym', 'b', 'code', 'del', 'em', 'i', 'ins', 'span', 'strong', 'sub', 'sup', 'u');
            }
            $tags = $this->_inlineTags;
        } else {
            $tags = null;
        }
        return $application->getPlatform()->htmlize($text, $tags);
    }
}