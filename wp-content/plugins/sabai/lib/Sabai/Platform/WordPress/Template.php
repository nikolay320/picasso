<?php
class Sabai_Platform_WordPress_Template
{
    private $_platform, $_title, $_summary, $_content, $_url, $_breadcrumbs, $_htmlHeadTitle,
        $_isFilteringSabaiPage;

    // The following is required which is to be set by the add_filter method
    public $wp_filter_id;
    
    public function __construct(Sabai_Platform_WordPress $platform)
    {
        $this->_platform = $platform;
    }

    public function set($name, $value)
    {
        $property = '_' . $name;
        $this->$property = $value;
        return $this;
    }

    public function render()
    {
        add_action('wp_head', array($this, 'onWpHeadAction'));
        add_action('wp_footer', array($this, 'onWpFooterAction'));

        if (is_single()) {
            if (defined('WPSEO_VERSION')) { // WP SEO
                add_filter('wpseo_breadcrumb_links', array($this, 'onWpSeoBreadcrumbLinksFilterSingle'), 99999, 1);
            }
            return;
        }
        
        if (!is_page()) return;
        
        // Required for the Gantry framework to call wp_title filter
        if (isset($GLOBALS['gantry'])) unset($GLOBALS['gantry']->pageTitle);
        
        add_filter('wp_title', array($this, 'onWpTitleFilter'), function_exists('genesis') ? 19 : 99999, 3); // allow genesis theme to wrap title
        add_filter('the_title', array($this, 'onTheTitleFilter'), 99999, 2);
        add_filter('page_link', array($this, 'onPageLinkFilter'), 99999, 3);  
        add_filter('the_permalink', array($this, 'onThePermalinkFilter'), 99999, 3);
        $replace_canonical = $add_og_url = true;
        if (defined('WPSEO_VERSION')) { // WP SEO
            add_filter('wpseo_title', array($this, 'onWpSeoTitleFilter'), 99999);
            add_filter('wpseo_metadesc', array($this, 'onDescriptionFilter'), 99999);
            add_filter('wpseo_breadcrumb_links', array($this, 'onWpSeoBreadcrumbLinksFilter'), 99999, 1);
            add_filter('wpseo_canonical', array($this, 'onCanonicalFilter'), 99999);  
            add_filter('wpseo_pre_analysis_post_content', array($this, 'onWpSeoPreAnalysisPostContent'), 99999);
            add_action('wpseo_twitter', array($this, 'onWpSeoTwitterAction'), 99999); // until wpseo_pre_analysis_post_content is added to WPSEO_Twitter
            $replace_canonical = $add_og_url = false;
        }
        if (defined('AIOSEOP_VERSION')) { // All-in-one SEO Pack
            add_filter('aioseop_title_page', array($this, 'onAioSeoPTitlePageFilter'), 99999);
            add_filter('aioseop_description', array($this, 'onDescriptionFilter'), 99999);
            add_filter('aioseop_canonical_url', array($this, 'onCanonicalFilter'), 99999);
            //add_filter('aiosp_opengraph_meta', array($this, 'onAioSeoPOpengraphMeta'), 99999, 3);
            $replace_canonical = $add_og_url = false;
        }
        if (defined('SU_MINIMUM_WP_VER')) { // SEO Ultimate
            remove_all_actions('su_head');
            add_action('wp_head', array($this, 'onWpHeadActionDescription'), 9);
        }
        if (class_exists('Facebook_Loader', false)) { // Facebook
            add_filter('facebook_rel_canonical', array($this, 'onCanonicalFilter'), 99999);
            $add_og_url = false;
        }
        if ($replace_canonical) {
            remove_action('wp_head', 'rel_canonical');
            add_action('wp_head', array($this, 'onWpHeadActionCanonical'));
        }
        if ($add_og_url) {
            add_action('wp_head', array($this, 'onWpHeadActionOgUrl'));
        }
    }

    public function onWpHeadAction()
    {
        echo $this->_platform->getHeaderHtml();
    }
    
    public function onWpFooterAction()
    {
        echo $this->_platform->getJsHtml();
    }
    
    public function onWpHeadActionDescription()
    {
        if (isset($this->_summary) && strlen($this->_summary)) {
            echo '<meta name="description" content="' . Sabai::h($this->_summary) . '" />';
        }
    }
    
    public function onWpHeadActionCanonical()
    {
        if (isset($this->_url)) {
            echo '<link rel="canonical" href="' . (string)$this->_url . '" />';
        }
    }
        
    public function onWpHeadActionOgUrl()
    {
        if (isset($this->_url)) {
            echo '<meta property="og:url" content="' . (string)$this->_url . '" />';
        }
    }

    public function onWpTitleFilter($title, $sep = '')
    {
        if (!isset($this->_htmlHeadTitle) || false === $this->_htmlHeadTitle) return $title;

        return str_replace(
            array('%%title%%', '%%sitename%%', '%%sep%%', '{post}', '{blog}'),
            array($this->_htmlHeadTitle, $sitename = $this->_platform->getSiteName(), $sep, $this->_htmlHeadTitle, $sitename),
            apply_filters('sabai_wordpress_title_format', '%%title%% %%sep%% %%sitename%%')
        );
    }

    public function onTheTitleFilter($title, $pageId = null)
    {
        return isset($this->_title) && false !== $this->_title && $this->_isFilteringSabaiPage($pageId) ? $this->_title : $title;
    }
    
    public function onPageLinkFilter($link, $pageId, $sample)
    {
        // The following flag is used to determine if the_permalink filter is being applied to a Sabai page.
        $this->_isFilteringSabaiPage = $this->_isFilteringSabaiPage($pageId);
        
        return $link;
    }
    
    public function onThePermalinkFilter($link)
    {
        // $this->_isFilteringSabaiPage may be null if not filtering the permalink of a page
        if (!$this->_isFilteringSabaiPage || !isset($this->_url)) {
            return $link;
        }
        $this->_isFilteringSabaiPage = null;
        return $this->_url;
    }
    
    private function _isFilteringSabaiPage($pageId)
    {
        if (empty($pageId)
            || !isset($GLOBALS['post'])
            || $GLOBALS['post']->ID != $pageId // Not filtering current page title?
            || (defined('SABAI_WORDPRESS_FIX_OLD_MENU') && !in_the_loop())
        ) {
            return false;
        }
        
        $page_slugs = get_option('sabai_sabai_page_slugs');
        if (!in_array($pageId, $page_slugs[2])) {
            return false;
        }
        return true;
    }
        
    public function onWpSeoTitleFilter($title)
    {
        if (!isset($this->_htmlHeadTitle) || false === $this->_htmlHeadTitle) return $title;
        
        $options = get_option('wpseo_titles');
        if (!isset($options['title-page']) || !strlen($options['title-page'])) return $this->_htmlHeadTitle;

        if (!$page = get_queried_object()) {
            $page = new stdClass(); // not really why but get_queried_object() returns null on certain occasions
        }
        $page->post_title = $this->_htmlHeadTitle;
        return wpseo_replace_vars($options['title-page'], $page);
    }
    
    public function onWpSeoBreadcrumbLinksFilter($links)
    {
        $links = array($links[0]);
        foreach ($this->_breadcrumbs as $breadcrumb) {
            $links[] = array('url' => (string)$breadcrumb['url'], 'text' => $breadcrumb['title']);
        }
        return $links;
    }
    
    public function onWpSeoBreadcrumbLinksFilterSingle($links)
    {
        foreach (array_keys($links) as $i) {
            if (isset($links[$i]['ptarchive'])) {
                $post_type_obj = get_post_type_object($links[$i]['ptarchive']);
                if (is_object($post_type_obj)
                    && ($post_type_archive_page = get_page_by_path($post_type_obj->has_archive))
                ) {
                    $links[$i] = array('id' => $post_type_archive_page->ID);
                }
                break;
            }
        }
        return $links;
    }
    
    public function onWpSeoPreAnalysisPostContent($content)
    {
        return isset($this->_content) && strlen($this->_content)
            ? $this->_content
            : $content;
    }
    
    public function onWpSeoTwitterAction()
    {    
        if (isset($this->_content)
            && strlen($this->_content)
            && preg_match_all('`<img [^>]+>`', $this->_content, $matches)
        ) {
            foreach ($matches[0] as $img) {
                if (preg_match('`src=(["\'])(.*?)\1`', $img, $match)) {
                    echo '<meta name="twitter:image:src" content="' . Sabai::h($match[2]) . '"/>';
                    break;
                }
            }
        }
    }
    
    public function onCanonicalFilter($url)
    {
        return isset($this->_url) ? (string)$this->_url : $url;
    }
    
    public function onAioSeoPTitlePageFilter($title)
    {
        return isset($this->_htmlHeadTitle) && false !== $this->_htmlHeadTitle ? $this->_htmlHeadTitle : $title;
    }
    
    public function onDescriptionFilter($desc)
    {
        return isset($this->_summary) && strlen($this->_summary)
            ? $this->_summary // for taxonomy pages
            : $desc;
    }
}