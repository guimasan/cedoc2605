<?php
/**
 * The header for our theme
 *
 * @package Tainacan_Interface
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php endif; ?>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

	<?php
		if ( function_exists( 'wp_body_open' ) ) {
			wp_body_open();
		} else {
			do_action( 'wp_body_open' );
		}

		$cedoc_layout = isset( $_GET['layout'] ) ? sanitize_key( wp_unslash( $_GET['layout'] ) ) : '3';
		if ( ! in_array( $cedoc_layout, array( '1', '2', '3' ), true ) ) {
			$cedoc_layout = '3';
		}

		$cedoc_request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
		$cedoc_layout_base = home_url( strtok( $cedoc_request_uri, '?' ) );
		$cedoc_layout_urls = array(
			'1' => remove_query_arg( 'layout', $cedoc_layout_base ),
			'2' => add_query_arg( 'layout', '2', $cedoc_layout_base ),
			'3' => add_query_arg( 'layout', '3', $cedoc_layout_base ),
		);

		$cedoc_primary_menu = array(
			array(
				'label' => 'CEACA Sobre nós',
				'url' => home_url( '/sobre/#ceaca-sobre-nos' ),
			),
			array(
				'label' => 'Articulação',
				'url' => home_url( '/catalogo/#articulacao' ),
			),
			array(
				'label' => 'Documentação de Saberes',
				'url' => home_url( '/catalogo/#saberes' ),
			),
			array(
				'label' => 'Educação e Cultura',
				'url' => home_url( '/catalogo/#educacao' ),
			),
			array(
				'label' => 'Eventos / Manifestações culturais',
				'url' => home_url( '/catalogo/#manifestacoes' ),
			),
		);

		if ( ! get_theme_mod( 'tainacan_use_block_template_parts_on_header', false ) ) :
	?>
	<div class="cedoc-header-layout-switcher" aria-label="Seletor de versão">
		<span class="cedoc-header-layout-label">Versão do site</span>
		<div class="cedoc-header-layout-links">
			<a class="<?php echo '1' === $cedoc_layout ? 'active' : ''; ?>" href="<?php echo esc_url( $cedoc_layout_urls['1'] ); ?>">Versão 1</a>
			<a class="<?php echo '2' === $cedoc_layout ? 'active' : ''; ?>" href="<?php echo esc_url( $cedoc_layout_urls['2'] ); ?>">Versão 2</a>
			<a class="<?php echo '3' === $cedoc_layout ? 'active' : ''; ?>" href="<?php echo esc_url( $cedoc_layout_urls['3'] ); ?>">Versão 3</a>
		</div>
	</div>
	<nav 
			style="min-height: 40px;"
			class="navbar navbar-expand-md navbar-light bg-white menu-shadow px-0 navbar--border-bottom cedoc-header--v<?php echo esc_attr( $cedoc_layout ); ?> <?php echo 'tainacan-header-layout--' . esc_attr( get_theme_mod( 'tainacan_header_alignment_options', 'default' ) ); ?>">
		<div class="container-fluid max-large px-0 margin-one-column" id="topNavbar">
			<?php echo wp_kses_post( tainacan_get_logo() ?? '' ); ?>

			<div class="navbar-box cedoc-header-shell cedoc-header-shell--v<?php echo esc_attr( $cedoc_layout ); ?>">
				<?php if ( '1' === $cedoc_layout ) : ?>
					<nav class="cedoc-header-nav cedoc-header-nav--v1" aria-label="Categorias do acervo">
						<?php foreach ( $cedoc_primary_menu as $menu_item ) : ?>
							<a class="cedoc-header-nav-item<?php echo 'CEACA Sobre nós' === $menu_item['label'] ? ' cedoc-header-nav-item--featured' : ''; ?>" href="<?php echo esc_url( $menu_item['url'] ); ?>">
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
					<nav class="cedoc-header-nav cedoc-header-nav--v2" aria-label="Categorias do acervo">
						<?php foreach ( $cedoc_primary_menu as $menu_item ) : ?>
							<a class="cedoc-header-nav-item<?php echo 'CEACA Sobre nós' === $menu_item['label'] ? ' cedoc-header-nav-item--featured' : ''; ?>" href="<?php echo esc_url( $menu_item['url'] ); ?>">
								<?php echo esc_html( $menu_item['label'] ); ?>
							</a>
						<?php endforeach; ?>
					</nav>

					<div class="cedoc-header-search cedoc-header-search--v2">
						<?php get_search_form(); ?>
					</div>
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

	<?php else: block_template_part( 'header' ); endif; ?>

	<a href="javascript:" id="return-to-top" style="<?php echo ( get_theme_mod( 'tainacan_footer_color', 'dark' ) == 'colored' ? 'background-color: #2c2d2d;' : '' ); ?>"><i class="tainacan-icon tainacan-icon-arrowup"></i></a>

	<?php if ( ! is_page_template( 'page-templates/landing.php' ) ) : ?>
		<?php echo wp_kses_post( tainacan_interface_the_breadcrumb() ); ?>
	<?php endif; ?>