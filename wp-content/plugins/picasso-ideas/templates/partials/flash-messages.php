<?php if (Picasso_Ideas_Session::exists('pi_success_message')): ?>
	<div class="alert alert-success" role="alert">
		<?php echo Picasso_Ideas_Session::flash('pi_success_message'); ?>
	</div>
<?php endif ?>

<?php if (Picasso_Ideas_Session::exists('pi_error_message')): ?>
	<div class="alert alert-danger" role="alert">
		<?php echo Picasso_Ideas_Session::flash('pi_error_message'); ?>
	</div>
<?php endif ?>