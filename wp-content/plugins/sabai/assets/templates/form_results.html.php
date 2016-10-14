<?php if (!empty($success)):?>
<div class="sabai-alert sabai-alert-success" style="margin-bottom:10px;">
<?php   foreach ((array)$success as $_success):?>
    <p><?php echo $_success;?></p>
<?php   endforeach;?>
</div>
<?php endif;?>
<?php if (!empty($error)):?>
<div class="sabai-alert sabai-alert-danger" style="margin-bottom:10px;">
<?php   foreach ((array)$error as $_error):?>
    <p><?php echo $_error;?></p>
<?php   endforeach;?>
</div>
<?php endif;?>
<?php if (!empty($warning)):?>
<div class="sabai-alert sabai-alert-warning" style="margin-bottom:10px;">
<?php   foreach ((array)$warning as $_warning):?>
    <p><?php echo $_warning;?></p>
<?php   endforeach;?>
</div>
<?php endif;?>
<?php if (!empty($info)):?>
<div class="sabai-alert sabai-alert-info" style="margin-bottom:10px;">
<?php   foreach ((array)$info as $_info):?>
    <p><?php echo $_info;?></p>
<?php   endforeach;?>
</div>
<?php endif;?>
<?php if (!empty($notice)):?>
<?php   foreach ((array)$notice as $_notice):?>
<div style="margin-bottom:10px;"><?php echo $_notice;?></div>
<?php   endforeach;?>
<?php endif;?>