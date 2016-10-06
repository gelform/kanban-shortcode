<iframe class="kanban-iframe" src="<?php echo $url ?>"></iframe>

<style>
	<?php if ( $atts['css'] ) : ?>
	<?php echo $atts['css'] ?>
	<?php else : ?>
	.kanban-iframe {
		border: 1px solid black;
		height: <?php echo $atts['height'] ?>;
		width: <?php echo $atts['width'] ?>;
	}
	<?php endif ?>
</style>