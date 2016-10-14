<?php if ($idea_experts): ?>
    <h4><?php _e('Idea Experts', 'picasso-ideas'); ?></h4>

    <div class="idea-experts">
        <table class="table">
            <thead>
                <tr>
                    <th>
                        <?php _e('Experts', 'picasso-ideas'); ?>
                    </th>
                    <th>
                        <?php _e('Action', 'picasso-ideas'); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($idea_experts as $expert_id): ?>
                    <?php
                    $user_data = get_userdata($expert_id);
                    $display_name = $user_data->display_name;
                    $member_type = bp_get_member_type($expert_id);

                    $nonce = wp_create_nonce('remove_expert_nonce');
                    
                    $args = array(
                        'add-experts' => '',
                        'action'      => 'remove-idea-expert',
                        'idea_id'     => $idea_id,
                        'expert_id'   => $expert_id,
                        '_wpnonce'    => $nonce,
                    );

                    $remove_link = add_query_arg($args, get_the_permalink($idea_id));
                    ?>
                    <tr>
                        <td>
                            <?php echo get_avatar($expert_id, '32'); ?>
                            <?php echo $display_name; ?>
                            <?php echo ($member_type) ? '(' . $member_type . ')' : '' ?>
                        </td>
                        <td width="30%">
                            <a href="<?php echo $remove_link; ?>" class="remove-expert"><?php _e('Remove', 'picasso-ideas'); ?></a>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>