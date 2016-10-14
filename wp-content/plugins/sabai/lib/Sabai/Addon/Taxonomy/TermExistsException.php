<?php
class Sabai_Addon_Taxonomy_TermExistsException extends Sabai_RuntimeException
{
    private $_term;
    
    public function __construct(Sabai_Addon_Taxonomy_Model_Term $term, $message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code);
        $this->_term = $term;
    }
    
    public function getTerm()
    {
        return $this->_term;
    }
}
