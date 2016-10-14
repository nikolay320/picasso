<?php if (!empty($entities)):
$items = array();
if (!empty($settings['photo_only'])) {
    foreach ($entities as $entity) {
        $items[] = sprintf(
            '<a href="%s"><img title="%s" src="%s" alt="" /></a>',
            $this->Entity_Url($entity['entity']),
            Sabai::h($entity['entity']->getTitle()),
            isset($entity['entity']->directory_photos[0]) ? $this->Directory_PhotoUrl($entity['entity']->directory_photos[0], $settings['photo_size']) : $this->NoImageUrl($settings['photo_size'] === 'thumbnail')
        );
    }
} else {
    foreach ($entities as $entity) {
        $items[] = $this->renderTemplate($entity['entity']->getBundleType() . '_single_' . $entity['display_mode'], array('settings' => $settings) + $entity);
    }
}
?>
<div class="sabai-directory-slider">
    <?php echo $this->Carousel($items, $settings['carousel']);?>
</div>
<?php endif;?>