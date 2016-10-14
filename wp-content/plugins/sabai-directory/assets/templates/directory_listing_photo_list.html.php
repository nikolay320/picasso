<script type="text/javascript">
jQuery('document').ready(function($){
    var $container = $('.sabai-directory-listing-photos'),
        masonry = function () {
            $container.imagesLoaded(function() {
                var containerWidth = $container.outerWidth() - 1, columnWidth;
                if (containerWidth > 768) {
                    columnWidth = Math.floor((containerWidth - 40) / 3);
                } else if (containerWidth > 480) {
                    columnWidth = Math.floor((containerWidth - 20) / 2);
                } else {
                    columnWidth = containerWidth;
                }
                $container.find('> div').width(columnWidth).end().masonry({columnWidth:columnWidth, itemSelector:'.sabai-entity', gutter:20, isRTL:SABAI.isRTL});
            });
        }
    $(SABAI).bind('comment_comment_added.sabai comment_comment_edited.sabai comment_comment_deleted.sabai comment_comment_hidden.sabai comment_comments_shown.sabai', function(e, data) {
        masonry();
    });
    if ($container.is(':visible')) {
        masonry();
    } else {
        $('#sabai-inline-content-photos-trigger').on('shown.bs.sabaitab', function(e, data){
            masonry();
        });
    }
});
</script>
<?php if (empty($hide_nav)):?>
<div class="sabai-navigation sabai-clearfix">
    <div class="sabai-pull-left sabai-btn-group">
<?php   if (!empty($links[0])):?>
        <?php echo $this->DropdownButtonLinks($links[0]);?>
<?php   endif;?>
        <?php echo $this->DropdownButtonLinks($sorts, 'sm', __('Sort by: <strong>%s</strong>', 'sabai-directory'));?>
    </div>
    <div class="sabai-pull-right">
<?php   if (!empty($links[1])):?>
        <?php echo $this->ButtonLinks($links[1], array('label' => true, 'tooltip' => false));?>
<?php   endif;?>
    </div>
</div>
<?php endif;?>
<?php if (!empty($entities)):?>
<div class="sabai-directory-listing-photos">
<?php   foreach ($entities as $entity):?>
<?php     $this->displayTemplate('directory_listing_photo_single_' . $entity['display_mode'], $entity + array('no_comments' => !empty($no_comments), 'link_to_listing' => !empty($link_to_listing)));?>
<?php   endforeach;?>
</div>
<?php   if ($paginator && empty($hide_pager)):?>
<div class="sabai-navigation sabai-navigation-bottom sabai-clearfix">
<?php     if ($paginator->count() > 1):?>
    <div class="sabai-pull-left">
        <?php printf(__('Showing %d - %d of %s results', 'sabai-directory'), $paginator->getElementOffset() + 1, $paginator->getElementOffset() + $paginator->getElementLimit(), $this->NumberFormat($paginator->getElementCount()));?>
    </div>
    <div class="sabai-pull-right">
        <?php echo $this->PageNav($CURRENT_CONTAINER, $paginator, $this->Url($CURRENT_ROUTE, $url_params));?>
    </div>
<?php     else:?>
    <div class="sabai-pull-left">
        <?php printf(_n('Showing %s result', 'Showing %s results', $paginator->getElementCount(), 'sabai-directory'), $this->NumberFormat($paginator->getElementCount()));?>
    </div>
<?php     endif;?>
</div>
<?php   endif;?>
<?php endif;?>