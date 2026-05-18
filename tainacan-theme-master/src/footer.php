<?php if ( ! is_404() ) :
	if ( !get_theme_mod('tainacan_use_block_template_parts_on_footer', false) ) : ?>
		<footer class="container-fluid p-4 p-sm-5 mt-5 tainacan-footer <?php echo esc_attr( ('tainacan-footer-' . get_theme_mod( 'tainacan_footer_color', 'dark' )) ) ?>" style="padding-bottom: 0 !important;">
			<div class="cedoc-share-strip" data-share-bar>
				<div class="cedoc-share-strip__copy">
					<strong>Compartilhar esta página</strong>
					<span>Leve o conteúdo do CEACA para suas redes.</span>
				</div>
				<div class="cedoc-share-links">
					<a data-share-network="whatsapp" target="_blank" rel="noreferrer noopener" href="#">WhatsApp</a>
					<a data-share-network="facebook" target="_blank" rel="noreferrer noopener" href="#">Facebook</a>
					<a data-share-network="linkedin" target="_blank" rel="noreferrer noopener" href="#">LinkedIn</a>
					<a data-share-network="x" target="_blank" rel="noreferrer noopener" href="#">X</a>
				</div>
			</div>
			<?php if ( is_active_sidebar( 'tainacan-sidebar-footer' ) ) { ?>
				<div class="row tainacan-footer-widgets-area">
					<ul class="col-12 col-lg pt-3 pb-3 pl-0 pr-0 d-lg-flex flex-wrap justify-content-xs-center mb-md-0">
						<?php dynamic_sidebar( 'tainacan-sidebar-footer' ); ?>
					</ul>
				</div>
			<?php } ?>
			<hr class="tainacan-footer-area-separator"/>
			<div class="row pt-3 pb-4 pl-0 pr-0 tainacan-footer-info">
				<div class="col text-white font-weight-normal">
					<p class="tainacan-footer-info--blog">
						<?php echo bloginfo( 'title' );
						if ( ! wp_is_mobile() ) {
							echo '<br>';
						} else {
							echo '</p><p>';
						}
						if ( get_theme_mod( 'tainacan_blogaddress' ) ) {
							echo wp_filter_nohtml_kses( get_theme_mod( 'tainacan_blogaddress', '' ) );
						} ?>
						<?php if ( get_theme_mod( 'tainacan_blogemail' ) ) {
							printf( __( 'E-mail: %s', 'tainacan-interface' ), sanitize_email( get_theme_mod( 'tainacan_blogemail', '' ) ) );
						}
						if ( get_theme_mod( 'tainacan_blogemail' ) && get_theme_mod( 'tainacan_blogphone' ) ) {
							if ( wp_is_mobile() ) :
								echo '<br>';
							else :
								echo ' - ';
							endif;
						}
						if ( get_theme_mod( 'tainacan_blogphone' ) ) {
							printf( __( 'Telephone: %s', 'tainacan-interface' ), wp_filter_nohtml_kses( get_theme_mod( 'tainacan_blogphone', '' ) ) );
						} ?>
					</p>
				</div>
				<?php if (get_theme_mod('tainacan_display_footer_logo', true) == true) : ?>
					<div class="col-auto pr-0 pr-md-3 d-none d-md-block align-self-md-top">
							<?php
							
							if ( get_theme_mod( 'tainacan_footer_logo' ) ) {
								$footerImage = esc_attr( get_theme_mod( 'tainacan_footer_logo' ) );
							} else {
								$footerImage = get_theme_mod( 'tainacan_footer_color', 'dark' ) == 'light' ? esc_url( get_template_directory_uri() ) . '/assets/images/logo.svg' : esc_url( get_template_directory_uri() ) . '/assets/images/logo-footer.svg';
							}
							?>
							<a href="<?php echo esc_url(get_theme_mod('tainacan_footer_logo_link', 'https://tainacan.org')) ?>">
								<img src="<?php echo $footerImage; ?>" class="tainacan-footer-info--logo" >
							</a>
					</div>
				<?php endif; ?>
				<div class="col-12 tainacan-powered">
					<span>
						<?php if ( true == get_theme_mod( 'tainacan_display_powered', false ) ) {
							/* translators: 1: WordPress; 2: Tainacan*/
							printf( __( 'Proudly powered by %1$s and %2$s.', 'tainacan-interface' ), '<a href="https://wordpress.org/">WordPress</a>', '<a href="https://tainacan.org/">Tainacan</a>' ); } ?>
					</span>
				</div>
			</div>
		</footer>
	<?php else:
		block_template_part( 'footer' );
	endif;
	?>
<?php endif; ?>
<?php wp_footer(); ?>
<script>
(function(){
	try {
		var url = encodeURIComponent(window.location.href);
		var title = encodeURIComponent(document.title || document.querySelector('h1') && document.querySelector('h1').innerText || '');
		var links = document.querySelectorAll('.cedoc-share-links a[data-share-network]');
		Array.prototype.forEach.call(links, function(a){
			var net = a.getAttribute('data-share-network');
			var href = '#';
			if (net === 'whatsapp') href = 'https://api.whatsapp.com/send?text=' + title + '%20' + url;
			else if (net === 'facebook') href = 'https://www.facebook.com/sharer/sharer.php?u=' + url;
			else if (net === 'linkedin') href = 'https://www.linkedin.com/shareArticle?mini=true&url=' + url + '&title=' + title;
			else if (net === 'x') href = 'https://twitter.com/intent/tweet?text=' + title + '&url=' + url;
			a.setAttribute('href', href);
		});
	} catch(e) {
		if (window.console) console.error('Share init error', e);
	}
})();
</script>
</body>

</html>
