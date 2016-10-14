<?php if ($idea_experts): ?>
    <div class="idea-experts">
        <table class="table">
            <thead>
                <tr>
                    <th>
                        <?php _e('Experts', IDEAS_TEXT_DOMAIN); ?>
                    </th>
                    <th>
                        <?php _e('Action', IDEAS_TEXT_DOMAIN); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($idea_experts as $expert_id): ?>
                    <?php
                    $user_data = get_userdata($expert_id);
                    $display_name = $user_data->display_name;
                    $member_type = bp_get_member_type($expert_id);
                    ?>
                    <tr>
                        <td>
                            <?php echo get_avatar($expert_id, '32'); ?>
                            <?php echo $display_name; ?>
                            <?php echo ($member_type) ? '(' . $member_type . ')' : '' ?>
                        </td>
                        <td width="30%">
                            <a href="javascript:void(0)" class="remove-expert" data-action="<?php echo 'klc_remove_expert'; ?>" data-ajax-url="<?php echo admin_url('admin-ajax.php'); ?>" data-idea-id="<?php echo $idea_id; ?>" data-expert-id="<?php echo $expert_id; ?>" data-notify-message="<?php _e('Successfully removed', IDEAS_TEXT_DOMAIN); ?>"><?php _e('Remove', IDEAS_TEXT_DOMAIN); ?></a>
                            <span class="idea-loading fa fa-spinner fa-spin"></span>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>