<?php
// Todo: need to use cmb2 metabox

$idea_deadline = get_post_meta($idea_id, '_idea_expert_reviews_deadline', true);
$campaign_id = get_post_meta($idea_id, '_idea_campaign', true);
?>

<!-- back to details button -->
<a href="<?php echo get_the_permalink(); ?>" id="back-to-idea-details" class="btn btn-primary btn-xs"><i class="fa fa-backward"></i><?php _e('Back to details', 'picasso-ideas'); ?></a>

<form action="" method="POST" class="add-idea-expert">
    <h4><?php _e('Add Expert', 'picasso-ideas'); ?></h4>

    <div class="table">
        <div class="table-cell select-expert">
            <?php $experts = pi_get_idea_experts(); ?>
            <select name="expert_id" id="expert_id" style="width: 100%;" data-placeholder="<?php _e('Search expert', 'picasso-ideas'); ?>">
                <option></option>
                <?php foreach ($experts as $expert): ?>
                    <?php
                    $expert_id = $expert->ID;
                    $display_name = $expert->data->display_name;
                    $member_type = function_exists('bp_get_member_type') ? bp_get_member_type($expert_id) : '';
                    $avatar_link = pi_get_avatar($expert_id);
                    ?>
                    <option value="<?php echo $expert_id; ?>" data-member-type="<?php echo $member_type; ?>" data-avatar="<?php echo $avatar_link; ?>"><?php echo $display_name; ?></option>
                <?php endforeach ?>
            </select>
        </div>

        <div class="table-cell select-deadline">
            <input type="text" name="deadline" id="idea_deadline" placeholder="<?php _e('Idea deadline', 'picasso-ideas'); ?>" value="<?php echo $idea_deadline; ?>">
        </div>

        <div class="table-cell add-button">
            <?php wp_nonce_field('add_idea_expert_nonce'); ?>
            <input type="hidden" name="idea_id" value="<?php echo $idea_id; ?>">
            <input type="hidden" name="campaign_id" value="<?php echo $campaign_id; ?>">
            <input type="submit" name="add_idea_expert" value="<?php _e('Add', 'picasso-ideas'); ?>" class="btn btn-primary">
        </div>
    </div>
</form>

<div class="idea-experts-table">
    <?php pi_render_template(IDEAS_TEMPLATE_PATH . 'partials/idea-experts-table.php', array('idea_id' => $idea_id, 'idea_experts' => $idea_experts)); ?>
</div>