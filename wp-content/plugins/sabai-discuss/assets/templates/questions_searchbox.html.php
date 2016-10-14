<?php if ($search['form_type'] === 0 || ($search['form_type'] === 1 && (!$category_select = $this->Taxonomy_SelectList($category_bundle, array('name' => 'category', 'class' => 'sabai-pull-right', 'parent' => $category, 'current' => $current_category, 'content_bundle' => 'questions', 'depth' => $search['cat_depth'], 'hide_empty' => $search['cat_hide_empty'], 'hide_count' => $search['cat_hide_count'], 'default_text' => __('Select category', 'sabai-discuss')))))) return;?>
<div class="sabai-questions-search sabai-clearfix">
    <form method="get" action="<?php echo $action_url;?>" class="sabai-search">
        <div class="sabai-row">
<?php switch ($search['form_type']):?>
<?php   case 2:?>
            <div class="sabai-col-sm-10 sabai-questions-search-keyword">
                <input name="keywords" type="text" value="<?php Sabai::_h($keywords);?>" placeholder="<?php Sabai::_h(__('Search...', 'sabai-discuss'));?>" />
            </div>
<?php     break;?>
<?php   case 1:?>
            <div class="sabai-col-sm-10 sabai-questions-search-category">
                <?php echo $category_select;?>
            </div>
<?php     break;?>
<?php   default: $category_select = $this->Taxonomy_SelectList($category_bundle, array('name' => 'category', 'class' => 'sabai-pull-right', 'parent' => $category, 'current' => $current_category, 'content_bundle' => 'questions', 'depth' => $search['cat_depth'], 'hide_empty' => $search['cat_hide_empty'], 'hide_count' => $search['cat_hide_count'], 'default_text' => __('Select category', 'sabai-discuss')));?>
            <div class="<?php if ($category_select):?>sabai-col-sm-6<?php else:?>sabai-col-sm-10<?php endif;?> sabai-questions-search-keyword">
                <input name="keywords" type="text" value="<?php Sabai::_h($keywords);?>" placeholder="<?php Sabai::_h(__('Search...', 'sabai-discuss'));?>" />
            </div>
<?php     if ($category_select):?>
            <div class="sabai-col-sm-4 sabai-questions-search-category">
                <?php echo $category_select;?>
            </div>
<?php     endif;?>
<?php endswitch;?>
            <div class="sabai-col-sm-2 sabai-questions-search-submit">
                <button type="submit" class="sabai-btn sabai-btn-sm <?php echo Sabai::h($button);?> sabai-questions-btn-search sabai-btn-block">
                    <i class="fa fa-search"></i>
                </button>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {
    $('<?php echo $CURRENT_CONTAINER;?> .sabai-questions-search input').keydown(function(e){
        if (e.keyCode == 13) { 
            $("<?php echo $CURRENT_CONTAINER;?> .sabai-questions-search-submit .sabai-btn").click();
        }
    });
<?php if (empty($search['no_key']) && !empty($search['auto_suggest'])):?>
<?php   if (!empty($search['suggest_cat'])):?>
    var categories = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        prefetch: {
            url: '<?php echo $this->Url('/sabai/taxonomy/termlist', is_array($category_bundle) ? array('bundle' => implode(',', array_keys($category_bundle)), Sabai_Request::PARAM_CONTENT_TYPE => 'json') : array('bundle' => $category_bundle, 'parent' => $category, Sabai_Request::PARAM_CONTENT_TYPE => 'json'), '', '&');?>'
        },
        limit: <?php echo isset($search['suggest_cat_num']) ? $search['suggest_cat_num'] : 5;?>
    });
    categories.initialize();
<?php   endif;?>
<?php   if (!empty($search['suggest_question'])): $num = isset($search['suggest_question_num']) ? $search['suggest_question_num'] : 5;?>
    var questions = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('title'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            wildcard: 'QUERY',
            url: '<?php echo $this->Url('/sabai/questions/questionlist', array('query' => 'QUERY', Sabai_Request::PARAM_CONTENT_TYPE => 'json') + (isset($bundle) ? array('bundle' => $bundle->name, 'category' => $category, 'num' => $num) : array('bundle_type' => 'questions', 'num' => $num)), '', '&');?>'
        },
        limit: <?php echo $num;?>
    });
    questions.initialize();
<?php   endif;?>
    $('<?php echo $CURRENT_CONTAINER;?> .sabai-questions-search-keyword input').typeahead(
        {highlight: true}
<?php     if (!empty($search['suggest_question'])):?>
        , {name: 'questions', displayKey: 'title', source: questions.ttAdapter()}
<?php     endif;?>
<?php     if (!empty($search['suggest_cat'])):?>
        , {name: 'categories', displayKey: 'title', source: categories.ttAdapter()<?php if (!empty($search['suggest_cat_icon'])):?>, templates: {suggestion: function(item){return '<i class="fa fa-<?php Sabai::_h($search['suggest_cat_icon']);?>"></i> ' + item.title}}<?php endif;?>}
<?php     endif;?>
    ).bind('typeahead:selected', function(obj, datum, name) {
<?php   if (!empty($search['suggest_question_jump'])):?>
            if (name === 'questions') window.location.href = datum.url;
<?php   endif;?>
<?php   if (!empty($search['suggest_cat_jump'])):?>
            if (name === 'categories') window.location.href = datum.url;
<?php   endif;?>
    });
<?php endif;?>
});
</script>