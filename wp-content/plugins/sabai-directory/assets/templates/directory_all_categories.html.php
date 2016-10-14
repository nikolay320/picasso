<?php foreach (array_keys($directories) as $category_bundle):?>
<div>
    <h2><a href="<?php echo $directories[$category_bundle]['url'];?>"><?php Sabai::_h($directories[$category_bundle]['title']);?></a></h2>
<?php if (!empty($entities[$category_bundle])):?>
    <?php $this->displayTemplate(
              'directory_categories',
              array(
                  'entities' => $entities[$category_bundle],
                  'column_count' => $column_count,
                  'hide_children' => $hide_children,
                  'hide_empty' => $hide_empty,
                  'hide_count' => $hide_count,
                  'child_count' => $child_count));?>
<?php endif;?>
</div>
<?php endforeach;?>
