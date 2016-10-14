<?php
class Sabai_Addon_Content_Controller_Feed extends Sabai_Controller
{
    protected $_numItems = 20, $_cacheLifetime = 86400; // 1 day
    
    protected function _doExecute(Sabai_Context $context)
    {
        $link = $this->_getLink($context);
        $cache_id = 'content_feed_' . $link;
        if (false === $cache = $this->getPlatform()->getCache($cache_id)) {
            $items = array();
            foreach ($this->_createQuery($context)->fetch($this->_numItems) as $post) {
                $items[] = array(
                    'title' => $this->_getItemTitle($context, $post),
                    'pub_date' => $post->getTimestamp(),
                    'guid' => $guid = $this->Entity_Url($post),
                    'link' => $guid,
                    'content' => $this->_getItemContent($context, $post),
                    'author' => $post->getAuthor()->name,
                    'extras' => $this->_getItemExtras($context, $post),
                    'post' => $post,
                );
            }
            $cache = array('time' => time(), 'items' => $this->Filter('content_feed_items', $items));
            $this->getPlatform()->setCache($cache, $cache_id, $this->_cacheLifetime);
        }
        
        $context->setContentType('xml')
            ->addTemplate('content_feed')
            ->setAttributes(array(
                'title' => $this->_getTitle($context),
                'link'  => $link,
                'description' => $this->_getDescription($context),
                'build_date' => $cache['time'],
                'items' => $cache['items'],
                'namespaces' => $this->_getNamespaces($context),
            ));
    }
    
    protected function _getTitle(Sabai_Context $context)
    {
        return __('Recent posts', 'sabai');
    }
    
    protected function _getItemTitle(Sabai_Context $context, Sabai_Addon_Entity_Entity $post)
    {
        return $post->getTitle();
    }
        
    protected function _getItemContent(Sabai_Context $context, Sabai_Addon_Entity_Entity $post)
    {
        return $post->getSingleFieldValue('content_body', 'html');
    }
        
    protected function _getItemExtras(Sabai_Context $context, Sabai_Addon_Entity_Entity $post)
    {
        return array();
    }
    
    protected function _createQuery(Sabai_Context $context)
    {
        return $this->Entity_Query('content')
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->sortByProperty('post_published', 'DESC');
    }
        
    protected function _getDescription(Sabai_Context $context)
    {
        return sprintf(__('The most %d recent posts', 'sabai'), $this->_numItems);
    }
    
    protected function _getLink(Sabai_Context $context)
    {
        return $this->Url($context->getRoute());
    }
            
    protected function _getNamespaces(Sabai_Context $context)
    {
        return array();
    }
}