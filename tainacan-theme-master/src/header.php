<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php endif; ?>
	<?php wp_head(); ?>
	<?php
	// Increase execution time for heavy pages while debugging locally
	@ini_set( 'max_execution_time', '120' );
	@set_time_limit(120);
	?>
</head>
<body <?php body_class(); ?>>

	<?php
		if ( function_exists( 'wp_body_open' ) ) {
			wp_body_open();
		} else {
			do_action( 'wp_body_open' );
		}

		$cedoc_layout = '2';

		$cedoc_layout_base = home_url( '/' );
		// Primary menu simplified for commercial layout
		$cedoc_primary_menu = array(
			array(
				'label' => 'Sobre nós',
				'url' => home_url( '/sobre/' ),
			),
			array(
				'label' => 'Acervo',
				'url' => home_url( '/catalogo/' ),
			),
			array(
				'label' => 'Contato',
				'url' => home_url( '/contato/' ),
			),
			array(
				'label' => 'Catalogo',
				'url' => home_url( '/catalogo/' ),
			),
		);

		$cedoc_v2_menu = array(
			array(
				'label' => 'Sobre nós',
				'url' => home_url( '/sobre/' ),
			),
			array(
				'label' => 'Acervo',
				'url' => home_url( '/catalogo/' ),
			),
			array(
				'label' => 'Contato',
				'url' => home_url( '/contato/' ),
			),
			array(
				'label' => 'Redes Sociais',
				'url' => home_url( '/contato/#redes-sociais' ),
			),
		);

		if ( true ) :
	?>
	<nav 
			style="min-height: 40px;"
			class="navbar navbar-expand-md navbar-light bg-white menu-shadow px-0 navbar--border-bottom cedoc-header--v<?php echo esc_attr( $cedoc_layout ); ?> <?php echo 'tainacan-header-layout--' . esc_attr( get_theme_mod( 'tainacan_header_alignment_options', 'default' ) ); ?>">
		<div class="container-fluid max-large px-0" id="topNavbar">
			<?php echo wp_kses_post(tainacan_get_logo() ?? ''); ?>

			<div class="navbar-box cedoc-header-shell cedoc-header-shell--v<?php echo esc_attr( $cedoc_layout ); ?>">
				<?php if ( '1' === $cedoc_layout ) : ?>
					<nav class="cedoc-header-nav cedoc-header-nav--v1" aria-label="Categorias do acervo">
						<?php foreach ( $cedoc_primary_menu as $menu_item ) : ?>
							<a class="cedoc-header-nav-item" href="<?php echo esc_url( $menu_item['url'] ); ?>">
								<?php echo esc_html( $menu_item['label'] ); ?>
							</a>
						<?php endforeach; ?>
					</nav>

					<?php if ( ! get_theme_mod( 'tainacan_hide_search_input', false ) ) : ?>
						<div class="cedoc-header-search cedoc-header-search--v1">
							<?php get_search_form(); ?>
						</div>
					<?php endif; ?>
				<?php elseif ( '2' === $cedoc_layout ) : ?>
					<div class="cedoc-header-rail cedoc-header-rail--commercial">
						<div class="cedoc-header-rail__eyebrow">CEACA / Tainacan</div>
						<div class="cedoc-header-rail__title">Acervo com visual comercial</div>
					</div>
					<nav class="cedoc-header-nav cedoc-header-nav--v2 cedoc-header-nav--with-search" aria-label="Categorias do acervo">
						<?php foreach ( $cedoc_v2_menu as $menu_item ) : ?>
							<a class="cedoc-header-nav-item" href="<?php echo esc_url( $menu_item['url'] ); ?>">
								<?php echo esc_html( $menu_item['label'] ); ?>
							</a>
						<?php endforeach; ?>

							<div class="cedoc-header-search cedoc-header-search--v2 cedoc-header-search--inline">
								<form role="search" method="get" class="cedoc-inline-search" action="<?php echo esc_url( home_url( '/catalogo/' ) ); ?>">
									<div class="cedoc-inline-search__field">
										<input class="form-control" type="search" name="s" placeholder="Buscar" id="tainacan-search">
										<button class="btn cedoc-inline-search__btn" type="submit"><i class="tainacan-icon tainacan-icon-search"></i></button>
									</div>
								</form>
							</div>
					</nav>
				<?php else : ?>
					<nav class="cedoc-header-nav cedoc-header-nav--v3" aria-label="Categorias do acervo">
						<a class="cedoc-header-nav-hero" href="<?php echo esc_url( $cedoc_primary_menu[0]['url'] ); ?>">
							<span class="cedoc-header-nav-hero__kicker">Primeiro destaque</span>
							<strong><?php echo esc_html( $cedoc_primary_menu[0]['label'] ); ?></strong>
							<span>Instituição, memória e território</span>
						</a>
						<div class="cedoc-header-nav-grid">
							<?php foreach ( array_slice( $cedoc_primary_menu, 1 ) as $menu_item ) : ?>
								<a class="cedoc-header-nav-item" href="<?php echo esc_url( $menu_item['url'] ); ?>">
									<?php echo esc_html( $menu_item['label'] ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					</nav>

					<?php if ( ! get_theme_mod( 'tainacan_hide_search_input', false ) ) : ?>
						<div class="cedoc-header-search cedoc-header-search--v3">
							<?php get_search_form(); ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	</nav>

	<?php else:	block_template_part( 'header' ); endif; ?>

	<a href="javascript:" id="return-to-top" style="<?php echo (get_theme_mod( 'tainacan_footer_color', 'dark' ) == 'colored' ? 'background-color: #2c2d2d;' : '') ?>"><i class="tainacan-icon tainacan-icon-arrowup"></i></a>

    <?php if ( !is_page_template( 'page-templates/landing.php' ) ) : ?>
		<?php echo wp_kses_post( tainacan_interface_the_breadcrumb() ); ?>
	<?php endif; ?>