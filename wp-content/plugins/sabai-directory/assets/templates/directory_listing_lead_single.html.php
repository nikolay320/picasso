<table class="sabai-table sabai-directory-listing-lead">
    <thead>
        <tr>
            <th><?php echo __('Field', 'sabai-directory');?></th>
            <th><?php echo __('Value', 'sabai-directory');?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong><?php echo __('Name', 'sabai-directory');?></strong></td>
            <td><?php echo $this->UserIdentityLink($author = $this->Entity_Author($lead));?></td>
        </tr>
        <tr>
            <td><strong><?php echo __('E-mail', 'sabai-directory');?></strong></td>
            <td><a href="mailto:<?php echo $author->email;?>"><?php echo $author->email;?></a></td>
        </tr>
        <tr>
            <td><strong><?php echo __('Message', 'sabai-directory');?></strong></td>
            <td><?php echo $this->Entity_RenderField($lead, 'content_body');?></td>
        </tr>
<?php foreach ($this->Entity_CustomFields($lead) as $field):?>
<?php   if ($field_output = $this->Entity_RenderField($lead, $field)):?>
        <tr>
            <td><strong><?php Sabai::_h($field->getFieldTitle());?></strong></td>
            <td><?php echo $field_output;?></td>
        </tr>
<?php   endif;?>
<?php endforeach;?>
    </tbody>
</table>