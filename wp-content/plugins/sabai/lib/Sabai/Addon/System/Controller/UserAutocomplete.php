<?php
class Sabai_Addon_System_Controller_UserAutocomplete extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        if (!$context->getRequest()->isAjax()) {
            $context->setBadRequestError();
            return;
        }

        $term = trim($context->getRequest()->asStr('term'));
        if (strlen($term) <= 1) {
            $context->setBadRequestError();
            return;
        }

        $limit = 10;
        $offset = ($context->getRequest()->asInt(Sabai::$p, 1) - 1) * $limit;
        $method = $context->getRequest()->asBool('search_by_name') ? 'searchByName' : 'search';
        $context->identities = $this->getPlatform()->getUserIdentityFetcher()->$method($term, $limit, $offset);
        $context->addTemplate('system_userautocomplete');
    }
}