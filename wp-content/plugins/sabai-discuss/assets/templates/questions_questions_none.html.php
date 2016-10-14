<?php $this->displayTemplate('questions_nav', array('category_suggestions' => $category_suggestions, 'sorts' => $sorts, 'url_params' => $url_params, 'settings' => $settings, 'links' => $links, 'show_filters' => $show_filters, 'show_filters_link' => $show_filters_link, 'filter_form' => $filter_form));?>
<div class="sabai-questions-questions<?php if (empty($settings['search']['filters_top'])):?><?php if (!$filter_form || !$show_filters):?> sabai-col-md-12<?php else:?> sabai-col-md-8<?php endif;?><?php endif;?>">
    <p><?php echo __('No results were found', 'sabai-discuss');?></p>
</div>
<?php $this->displayTemplate('questions_pager', array('paginator' => $paginator, 'url_params' => $url_params, 'settings' => $settings));?>