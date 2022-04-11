        <?php do_action('masterstudy_before_footer'); ?>

		<footer id="footer" class="<?php echo ( stm_option('footer_parallax_option') ) ? '' : 'parallax-off' ?>">
		<div id="footer-widgets">
   		<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('footer-1') ) : ?>
    	<?php endif; ?>
		</div>
			<div class="footer_wrapper">
				<?php get_template_part('partials/footers/footer', 'top'); ?>
				<?php get_template_part('partials/footers/footer', 'bottom'); ?>
				<?php get_template_part('partials/footers/copyright'); ?>
			</div>
		</footer>

        <?php do_action('masterstudy_after_footer'); ?>

	<?php wp_footer(); ?>
	</body>
</html>