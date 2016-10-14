<!-- idea_search_box -->
<div class="clear idea_search_box">
	<div class="col-md-6">
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

				<?php if (!empty($_GET['keyword'])): ?>
					<input type="hidden" name="keyword" value="<?php echo $_GET['keyword']; ?>">
				<?php endif ?>
			</div>
		</form>
	</div>
	<div class="col-md-6">
		<form method="GET" class="idea_search">
			<div class="table">
				<div class="table-cell">
					<?php $keyword = !empty($_GET['keyword']) ? $_GET['keyword'] : ''; ?>
					<input type="text" name="keyword" class="idea_search_input" placeholder="<?php _e('Keyword...', 'picasso-ideas'); ?>" value="<?php echo $keyword; ?>">
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
</div><!-- .idea_search_box -->