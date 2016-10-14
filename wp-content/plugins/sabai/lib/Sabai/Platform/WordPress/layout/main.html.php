<?php
$this->Platform()->getTemplate()
    ->set('title', $CONTENT_TITLE)
    ->set('htmlHeadTitle', $HTML_HEAD_TITLE)
    ->set('breadcrumbs', $CONTENT_BREADCRUMBS)
    ->set('url', $CONTENT_URL)
    ->set('summary', $CONTENT_SUMMARY)
    ->set('content', $CONTENT_MAIN)
    ->render();
?>
<div id="sabai-content" class="sabai sabai-main">
<?php echo $CONTENT;?>
</div>