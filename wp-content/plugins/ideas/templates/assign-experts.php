<?php
// load required scripts
wp_enqueue_style('select2-styles');
wp_enqueue_script('select2-js');
wp_enqueue_style('zebra_datepicker-styles');
wp_enqueue_script('zebra_datepicker-js');

// idea deadline
$idea_deadline = get_post_meta(get_the_ID(), '_idea_deadline', true);
?>

<!-- back to details button -->
<a href="<?php the_permalink(); ?>" class="btn btn-primary btn-xs back-to-idea-details assign-experts"><i class="fa fa-backward"></i><?php _e('Back to details', IDEAS_TEXT_DOMAIN); ?></a>

<form action="" method="POST" class="idea-modifier-form">
    <div class="assign-idea-experts">
        <?php $experts = klc_get_idea_experts(); ?>
        <label for="expert_id"><?php _e('Assign expert', IDEAS_TEXT_DOMAIN); ?>:</label>
        <select name="expert_id" id="expert_id" style="width: 100%;">
            <option></option>
            <?php foreach ($experts as $expert): ?>
                <?php
                $expert_id = $expert->ID;
                $display_name = $expert->data->display_name;
                $placeholder = __('Search expert', IDEAS_TEXT_DOMAIN);
                $member_type = function_exists('bp_get_member_type') ? bp_get_member_type($expert_id) : '';
                $avatar_link = klc_get_avatar($expert_id);
                ?>
                <option value="<?php echo $expert_id; ?>" data-member-type="<?php echo $member_type; ?>" data-avatar="<?php echo $avatar_link; ?>"><?php echo $display_name; ?></option>
            <?php endforeach ?>
        </select>
    </div>

    <div class="assign-deadline">
        <label for="idea_deadline"><?php _e('Idea deadline', IDEAS_TEXT_DOMAIN); ?>:</label>
        <input type="text" name="idea_deadline" id="idea_deadline" value="<?php echo $idea_deadline; ?>">
    </div>

    <div class="idea-buttons-wrapper save-idea-fields">
        <input type="submit" value="<?php _e('Save', IDEAS_TEXT_DOMAIN); ?>" class="btn btn-primary save-idea-fields-button" data-idea-id="<?php the_ID(); ?>" data-campaign-id="<?php echo $campaign_id; ?>" data-ajax-url="<?php echo admin_url('admin-ajax.php'); ?>" data-action="klc_assign_experts_and_deadline_for_idea" data-notify-message="<?php _e('Successfully updated', IDEAS_TEXT_DOMAIN); ?>">
        <span class="idea-loading fa fa-spinner fa-spin"></span>
    </div>
</form>

<div class="idea-experts-table">
    <?php klc_render_template('experts-table', array('idea_id' => get_the_ID(), 'idea_experts' => $idea_experts)); ?>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        if (jQuery().select2) {
            function format(state) {
                var originalOption = state.element,
                    member_type = '';

                if ($(originalOption).data('member-type')) {
                    member_type = ' (' + $(originalOption).data('member-type') + ')';
                }
                return '<span><img src="' + $(originalOption).data('avatar') + '" width="32" height="32" /> ' + state.text + member_type + '</span>';
            }

            $('#expert_id').select2({
                placeholder: '<?php echo $placeholder; ?>',
                formatResult: format,
                formatSelection: format,
            });
        }

        if (jQuery().Zebra_DatePicker) {
            $('#idea_deadline').Zebra_DatePicker({
                default_position: 'below'
            });
        }
    });
</script>