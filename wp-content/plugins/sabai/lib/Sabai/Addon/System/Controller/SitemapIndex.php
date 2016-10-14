<?php
class Sabai_Addon_System_Controller_SitemapIndex extends Sabai_Controller
{    
    protected function _doExecute(Sabai_Context $context)
    {        
        $context->setContentType('xml')
            ->addTemplate('system_sitemap_index')
            ->setAttributes(array(
                'sitemaps' => $this->Filter('system_sitemap_index', array()),
                'urls_perpage' => $this->Filter('system_sitemap_num_urls_page', 10000),
            ));
    }
}