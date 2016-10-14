<!-- idea_search_box -->
<div class="clear idea_search_box">
	<div class="col-md-4">
		<form method="GET" class="idea_sort_form">
			<div class="idea_sort_wrapper">
				<select class="idea_sort" name="order_by">
					<?php $sort_options = pi_idea_sort_by(); ?>
					<?php if ($sort_options): ?>
						<?php foreach ($sort_options as $key => $sort_title): ?>
							<?php $sort_selected = (isset($_GET['order_by']) && $_GET['order_by'] === $key) ? ' selected="selected"' : ''; ?>
							<option value="<?php echo $key; ?>"<?php echo $sort_selected; ?>><?php echo $sort_title; ?></option>
						<?php endforeach ?>
					<?php endif ?>
				</select>

				<?php if (!empty($_GET['s'])): ?>
					<input type="hidden" name="s" value="<?php echo $_GET['s']; ?>">
				<?php endif ?>

				<?php if (!empty($_GET['post_type'])): ?>
					<input type="hidden" name="post_type" value="<?php echo $_GET['post_type']; ?>">
				<?php endif ?>
			</div>
		</form>
	</div>
	<div class="col-md-5">
		<form method="GET" class="idea_search">
			<div class="table">
				<div class="table-cell">
					<?php $keyword = !empty($_GET['s']) ? $_GET['s'] : ''; ?>
					<input type="text" name="s" class="idea_search_input" placeholder="<?php _e('Keyword...', 'picasso-ideas'); ?>" value="<?php echo $keyword; ?>">
					<input type="hidden" name="post_type" value="idea">
					<?php if (!empty($_GET['order_by'])): ?>
						<input type="hidden" name="order_by" value="<?php echo $_GET['order_by']; ?>">
					<?php endif ?>
				</div>
				<div class="table-cell">
					<button type="submit" class="btn-primary submit_button">
						<span class="fa fa-search"></span>
					</button>
				</div>
			</div>
		</form>
	</div>
	<div class="col-md-3">
		<a href="<?php echo pi_idea_create_page(); ?>" class="btn-primary submit_button add_idea_button">
			<span class="fa fa-lightbulb-o"></span>
			<?php _e('Add Idea', 'picasso-ideas'); ?>
		</a>
	</div>
</div><!-- .idea_search_box -->