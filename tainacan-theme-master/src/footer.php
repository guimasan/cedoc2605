<footer class="footer-shell">
	<div class="footer">
		<div class="footer-grid">
			<div class="footer-brands">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-footer.svg' ); ?>" alt="CEACA">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/url-circle.png' ); ?>" alt="URL">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/whatsapp-circle.png' ); ?>" alt="WhatsApp">
			</div>
			<div class="footer-contact">
				<strong>CEACA</strong>
				<div>Tel: 1234-5678</div>
				<div>email: capoeiraceaca@gmail.com</div>
				<div>Rua Exemplo, 123</div>
				<div class="footer-share">
					<a href="https://api.whatsapp.com/send?text=<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noreferrer noopener">WhatsApp</a>
					<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noreferrer noopener">Facebook</a>
					<a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noreferrer noopener">LinkedIn</a>
				</div>
			</div>
			<div class="footer-right" id="contato">
				<strong>ponto de troca</strong>
				<div>amorim lima ceaca</div>
				<div>parcerias / memória</div>
				<div>pedagogia / doc</div>
			</div>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>

</html>
