<div class="footer-732">

	<ul class="footer-732-menu">

	<?php $menu = $this->createFooterMenu(); foreach($menu as $url => $text) : ?>
		<li>
			<?php if (is_string($url)) : ?>
				<a href="<?php echo $url; ?>"><?php echo $text; ?></a>
			<?php else : ?>
				<span><?php echo $text; ?></span>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
	</ul>

</div>