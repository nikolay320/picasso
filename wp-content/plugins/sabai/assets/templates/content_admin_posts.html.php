<?php
foreach ($buttons as $key => $_button) {
    $class = 'sabai-btn sabai-btn-default sabai-btn-xs';
    if ($key === $status) {
        $class .= ' sabai-active';
    }    
    $buttons[$key] = $this->LinkToRemote($_button, $CURRENT_CONTAINER, $this->Url($CURRENT_ROUTE, array('status' => $key) + $url_params), array(), array('class' => $class));
}
?>
<div class="sabai-navigation sabai-clearfix">
    <div class="sabai-pull-left">
        <div class="sabai-btn-group"><?php echo implode(PHP_EOL, $buttons);?></div>
    </div>
<?php if (!empty($links)):?>
    <div class="sabai-pull-right">
        <div class="sabai-btn-group"><?php echo implode('&nbsp;', $links);?></div>
    </div>
<?php endif;?>
</div>
<?php if (!empty($form->settings['#filters'])): uasort($form->settings['#filters'], create_function('$a,$b','return $a["order"] < $b["order"] ? -1 : 1;'));?>
<div class="sabai-entity-filters sabai-clearfix">
    <?php $this->FormTag($this->Url($CURRENT_ROUTE, array_diff_key($url_params, $form->settings['#filters'], array('limit' => 1, 'content_keywords' => 1))));?>
<?php   foreach ($form->settings['#filters'] as $filter_name => $filter):?>
        <select name="<?php Sabai::_h($filter_name);?>">
            <option value=""><?php Sabai::_h($filter['default_option_label']);?></option>
<?php     foreach ($filter['options'] as $filter_option_value => $filter_option_label):?>
            <option value="<?php Sabai::_h($filter_option_value);?>"<?php if (isset($url_params[$filter_name]) && $url_params[$filter_name] == $filter_option_value):?> selected="selected"<?php endif;?>><?php Sabai::_h($filter_option_label);?></option>
<?php     endforeach;?>
        </select>
<?php   endforeach;?>
        <select name="limit">
<?php   foreach ($this->Filter('content_admin_posts_limit', array(20, 30, 50, 100, 200, 500)) as $limit):?>
            <option value="<?php echo $limit;?>"<?php if ($limit == $url_params['limit']):?> selected="selected"<?php endif;?>><?php echo $limit;?></option>
<?php   endforeach;?>
        </select>
        <input type="text" name="content_keywords" value="<?php Sabai::_h($url_params['content_keywords']);?>" size="10" />
        <button type="submit" class="sabai-btn sabai-btn-default sabai-btn-sm"><?php echo __('Filter', 'sabai');?></button>
    </form>
</div><?php endif;?>
<?php echo $this->Form_Render($form);?>
<?php if ($pager && $pager->count()):?>
<div class="sabai-navigation sabai-navigation-bottom sabai-clearfix" style="text-align:center;">
    <?php echo $this->PageNav($CURRENT_CONTAINER, $pager, $this->Url($CURRENT_ROUTE, $url_params));?>
</div>
<?php endif;?>