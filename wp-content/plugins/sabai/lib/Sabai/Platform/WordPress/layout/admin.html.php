<?php
global $title, $menu, $submenu, $pagenow, $typenow, $self, $parent_file, $submenu_file, $plugin_page, $user_identity;
require_once ABSPATH . 'wp-admin/admin-header.php';
echo $this->Platform()->getHeaderHtml();
?>
<div class="wrap">
    <div id="sabai-content" class="sabai sabai-admin">
<?php echo $CONTENT;?>
    </div>
</div>
<?php
echo $this->Platform()->getJsHtml();
require_once ABSPATH . 'wp-admin/admin-footer.php';
?>