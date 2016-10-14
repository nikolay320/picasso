<?php
class Sabai_Addon_Markdown_Helper_Transform extends Sabai_Helper
{
    private $_parser;
    
    /**
     * Transforms markdown text into HTML
     *
     * @return string HTML text
     * @param Sabai $application
     * @param string $text Text in markdown format
     */
    public function help(Sabai $application, $text)
    {
        if (!isset($this->_parser)) {
            require_once $application->getAddonPath('Markdown') . '/lib/markdown.php';
            $this->_parser = new Sabai_MarkdownExtra_Parser();
        }
        
        return $this->_parser->transform($text);
    }
}