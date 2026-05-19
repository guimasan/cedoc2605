<?php
/**
 * The home page template
 *
 * @package Tainacan_Interface
 */

get_header();

// Get the banner to display
get_template_part( 'template-parts/bannerheader' );

$layout = isset( $_GET['layout'] ) ? sanitize_key( wp_unslash( $_GET['layout'] ) ) : '3';
if ( ! in_array( $layout, array( '1', '2', '3' ), true ) ) {
	$layout = '3';
}

$base_home_url = home_url( '/' );
$layout_1_url  = remove_query_arg( 'layout', $base_home_url );
$layout_2_url  = add_query_arg( 'layout', '2', $base_home_url );
$layout_3_url  = add_query_arg( 'layout', '3', $base_home_url );
$category_blueprints = cedoc_get_category_blueprints();
?>


<main role="main" class="mt-5 cedoc-home-layout cedoc-home-layout-<?php echo esc_attr( $layout ); ?>">
	<?php $categories = cedoc_get_categories(); ?>
	<?php
	ob_start();
	?>
	<div class="cedoc-story-hub">
		<?php foreach ( $categories as $category ) :
			$blueprint = $category_blueprints[ $category['slug'] ] ?? array();
			$page_slugs = $blueprint['page_slugs'] ?? array();
			$lead = $blueprint['lead'] ?? $category['description'];
			if ( empty( $page_slugs ) ) {
				continue;
			}
			?>
			<section class="cedoc-story-section" id="cedoc-story-<?php echo esc_attr( $category['slug'] ); ?>">
				<div class="cedoc-story-section-header">
					<div>
						<span class="cedoc-layout-label"><?php echo esc_html( $category['name'] ); ?></span>
						<h3><?php echo esc_html( $category['name'] ); ?></h3>
						<p><?php echo esc_html( $lead ); ?></p>
					</div>
					<a class="btn btn-sm btn-outline-primary" href="#cedoc-category-<?php echo esc_attr( $category['slug'] ); ?>">Ver categoria</a>
				</div>
				<?php echo wp_kses_post( cedoc_render_page_story_cards( $page_slugs, 'Texto migrado do ceaca.wordpress' ) ); ?>
			</section>
		<?php endforeach; ?>
	</div>
	<?php
	$cedoc_story_hub = ob_get_clean();
	?>

	<div class="container-fluid max-large margin-one-column cedoc-layout-switcher-wrap">
		<div class="cedoc-layout-switcher">
			<span class="cedoc-layout-switcher-label">Versões do layout</span>
			<a class="<?php echo '1' === $layout ? 'active' : ''; ?>" href="<?php echo esc_url( $layout_1_url ); ?>">Base</a>
			<a class="<?php echo '2' === $layout ? 'active' : ''; ?>" href="<?php echo esc_url( $layout_2_url ); ?>">Versão 2</a>
			<a class="<?php echo '3' === $layout ? 'active' : ''; ?>" href="<?php echo esc_url( $layout_3_url ); ?>">Versão 3</a>
		</div>
	</div>

	<?php if ( '1' === $layout ) : ?>
		<!-- CAROUSEL SECTION -->
		<section class="cedoc-carousel-section py-5" style="background-color: #f8f9fa;">
			<div class="container-fluid max-large margin-one-column">
				<h2 class="mb-4 text-center">Categorias do Acervo</h2>
				<div class="row cedoc-carousel">
					<?php foreach ( $categories as $category ) :
						$random_image = cedoc_get_random_category_image( $category['slug'] );
						$bg_style = $random_image ? 'background-image: url(' . esc_url( $random_image ) . ');' : '';
						?>
						<div class="col-12 col-md-6 col-lg-4 mb-4">
							<div class="cedoc-carousel-item card h-100 border-0 shadow-sm" style="overflow: hidden;">
								<div class="cedoc-carousel-image" style="height: 250px; background-size: cover; background-position: center; background-color: #e9ecef; <?php echo esc_attr( $bg_style ); ?>"></div>
								<div class="card-body">
									<h5 class="card-title"><?php echo esc_html( $category['name'] ); ?></h5>
									<p class="card-text text-muted small"><?php echo esc_html( $category['description'] ); ?></p>
									<a href="#cedoc-category-<?php echo esc_attr( $category['slug'] ); ?>" class="btn btn-sm btn-outline-primary">Ver Subcategorias</a>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

		<section class="cedoc-categories-section py-5" style="background-color: #f8f9fa;">
			<div class="container-fluid max-large margin-one-column">
				<h2 class="mb-5 text-center">CEACA - Centro de Estudos e Aplicação da Capoeira</h2>
				<div class="accordion cedoc-categories-accordion" id="cedocCategoriesAccordion">
					<?php foreach ( $categories as $category ) :
						$subcategories = cedoc_get_subcategories_by_category( $category['slug'] );
						?>
						<div class="card border-0 mb-3 cedoc-category-card">
							<div class="card-header bg-white border-bottom" id="heading_<?php echo esc_attr( $category['slug'] ); ?>">
								<h2 class="mb-0">
									<button class="btn btn-link btn-block text-left p-3 text-dark" type="button" data-toggle="collapse" data-target="#collapse_<?php echo esc_attr( $category['slug'] ); ?>" aria-expanded="false" aria-controls="collapse_<?php echo esc_attr( $category['slug'] ); ?>">
										<strong><?php echo esc_html( $category['name'] ); ?></strong>
										<span class="float-right"><i class="tainacan-icon tainacan-icon-arrowdown"></i></span>
									</button>
								</h2>
							</div>

							<div id="collapse_<?php echo esc_attr( $category['slug'] ); ?>" class="collapse" aria-labelledby="heading_<?php echo esc_attr( $category['slug'] ); ?>" data-parent="#cedocCategoriesAccordion">
								<div class="card-body">
									<div class="row">
										<?php foreach ( $subcategories as $subcategory ) :
											$random_image = cedoc_get_random_item_image();
											$bg_style = $random_image ? 'background-image: url(' . esc_url( $random_image ) . ');' : '';
											$page_link = get_page_link( $subcategory['id'] );
											?>
											<div class="col-12 col-md-6 col-lg-4 mb-4">
												<a href="<?php echo esc_url( $page_link ); ?>" class="cedoc-subcategory-preview card border-0 shadow-sm h-100 text-decoration-none" style="overflow: hidden; display: flex; flex-direction: column;">
													<div class="cedoc-subcategory-image" style="height: 180px; background-size: cover; background-position: center; background-color: #e9ecef; <?php echo esc_attr( $bg_style ); ?>"></div>
													<div class="card-body d-flex flex-column flex-grow-1">
														<h6 class="card-title text-dark"><?php echo esc_html( $subcategory['title'] ); ?></h6>
														<p class="card-text text-muted small flex-grow-1"><?php echo esc_html( substr( $subcategory['content'], 0, 80 ) ) . '...'; ?></p>
														<span class="badge badge-primary mt-auto">Ver Acervo</span>
													</div>
												</a>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

		<section class="cedoc-content-section py-5 bg-white">
			<div class="container-fluid max-large margin-one-column">
				<div class="row">
					<div class="col-12">
						<?php
						if ( have_posts() ) {
							while ( have_posts() ) {
								the_post();
								get_template_part( 'template-parts/loop', 'singular' );
							}
						}
						?>
					</div>
				</div>
			</div>
		</section>

	<?php elseif ( '2' === $layout ) : ?>
		<section class="cedoc-layout-v2 py-5">
			<div class="container-fluid max-large margin-one-column">
				<div class="cedoc-v2-grid">
					<aside class="cedoc-v2-sidebar">
						<span class="cedoc-layout-label">Versão 2</span>
						<h2>Layout em duas colunas</h2>
						<p>A mesma base funcional, mas com leitura lateral e catálogo em blocos mais lineares.</p>
						<div class="cedoc-layout-switcher cedoc-layout-switcher-inline">
							<a class="<?php echo '1' === $layout ? 'active' : ''; ?>" href="<?php echo esc_url( $layout_1_url ); ?>">Base</a>
							<a class="<?php echo '2' === $layout ? 'active' : ''; ?>" href="<?php echo esc_url( $layout_2_url ); ?>">Versão 2</a>
							<a class="<?php echo '3' === $layout ? 'active' : ''; ?>" href="<?php echo esc_url( $layout_3_url ); ?>">Versão 3</a>
						</div>
						<a class="btn btn-outline-primary btn-block mt-3" href="#cedoc-v2-categories">Ir para categorias</a>
					</aside>

					<div class="cedoc-v2-main">
						<?php
						$featured_category = ! empty( $categories ) ? $categories[0] : null;
						$featured_image = $featured_category ? cedoc_get_random_category_image( $featured_category['slug'] ) : cedoc_get_random_item_image();
						$featured_link = home_url( '/acervo-cedoc/' );
						if ( $featured_category ) {
							$featured_subcategories = cedoc_get_subcategories_by_category( $featured_category['slug'] );
							if ( ! empty( $featured_subcategories ) ) {
								$featured_link = get_page_link( $featured_subcategories[0]['id'] );
							}
						}
						?>
						<section class="cedoc-v2-hero card">
							<div class="cedoc-v2-hero-media">
								<?php if ( $featured_image ) : ?>
									<img src="<?php echo esc_url( $featured_image ); ?>" alt="<?php echo esc_attr( $featured_category ? $featured_category['name'] : 'CEACA' ); ?>">
								<?php endif; ?>
							</div>
							<div class="cedoc-v2-hero-copy">
								<span class="cedoc-featured-kicker">CEACA CEDOC</span>
								<h1><?php echo esc_html( $featured_category ? $featured_category['name'] : 'CEACA' ); ?></h1>
								<p><?php echo esc_html( $featured_category ? $featured_category['description'] : 'Base funcional do acervo com layout lateral.' ); ?></p>
								<div class="cedoc-featured-actions">
									<a class="btn btn-light btn-lg" href="<?php echo esc_url( $featured_link ); ?>">Ver Categoria</a>
									<a class="btn btn-outline-light btn-lg" href="#cedoc-v2-categories">Ver blocos</a>
								</div>
							</div>
						</section>

						<div class="cedoc-v2-categories" id="cedoc-v2-categories">
							<?php foreach ( $categories as $category ) :
								$subcategories = cedoc_get_subcategories_by_category( $category['slug'] );
								$preview_image = cedoc_get_random_category_image( $category['slug'] );
								?>
								<article class="cedoc-v2-category-card" id="cedoc-v2-category-panel-<?php echo esc_attr( $category['slug'] ); ?>">
									<div class="cedoc-v2-category-image">
										<?php if ( $preview_image ) : ?>
											<img src="<?php echo esc_url( $preview_image ); ?>" alt="<?php echo esc_attr( $category['name'] ); ?>">
										<?php endif; ?>
									</div>
									<div class="cedoc-v2-category-copy">
										<h3><?php echo esc_html( $category['name'] ); ?></h3>
										<p><?php echo esc_html( $category['description'] ); ?></p>
										<div class="cedoc-v2-subcategories">
											<?php foreach ( $subcategories as $subcategory ) : ?>
												<a href="<?php echo esc_url( get_page_link( $subcategory['id'] ) ); ?>" class="btn btn-sm btn-outline-primary"><?php echo esc_html( $subcategory['title'] ); ?></a>
											<?php endforeach; ?>
										</div>
									</div>
								</article>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section class="py-5 bg-light">
			<div class="container-fluid max-large margin-one-column">
				<?php echo cedoc_render_category_story_hub( $categories, $category_blueprints ); ?>
			</div>
		</section>

	<?php else : ?>
		<section class="cedoc-layout-v3 py-5">
			<div class="container-fluid max-large margin-one-column">
				<div class="cedoc-v3-header">
					<div>
						<span class="cedoc-layout-label">Versão 3</span>
						<h2>Layout em mosaico</h2>
						<p>Uma leitura mais fragmentada, com cards por categoria e detalhes expansíveis.</p>
					</div>
					<div class="cedoc-layout-switcher cedoc-layout-switcher-inline">
						<a class="<?php echo '1' === $layout ? 'active' : ''; ?>" href="<?php echo esc_url( $layout_1_url ); ?>">Base</a>
						<a class="<?php echo '2' === $layout ? 'active' : ''; ?>" href="<?php echo esc_url( $layout_2_url ); ?>">Versão 2</a>
						<a class="<?php echo '3' === $layout ? 'active' : ''; ?>" href="<?php echo esc_url( $layout_3_url ); ?>">Versão 3</a>
					</div>
				</div>

				<div class="cedoc-v3-grid">
					<?php foreach ( $categories as $category ) :
						$subcategories = cedoc_get_subcategories_by_category( $category['slug'] );
						$preview_image = cedoc_get_random_category_image( $category['slug'] );
						?>
						<article class="cedoc-v3-card" id="cedoc-v3-card-<?php echo esc_attr( $category['slug'] ); ?>">
							<div class="cedoc-v3-card-image">
								<?php if ( $preview_image ) : ?>
									<img src="<?php echo esc_url( $preview_image ); ?>" alt="<?php echo esc_attr( $category['name'] ); ?>">
								<?php endif; ?>
							</div>
							<div class="cedoc-v3-card-copy">
								<span class="cedoc-featured-kicker"><?php echo esc_html( count( $subcategories ) ); ?> subcategorias</span>
								<h3><?php echo esc_html( $category['name'] ); ?></h3>
								<p><?php echo esc_html( $category['description'] ); ?></p>
								<a class="btn btn-sm btn-outline-primary" href="#cedoc-v3-details-<?php echo esc_attr( $category['slug'] ); ?>">Ver detalhes</a>
							</div>
						</article>
					<?php endforeach; ?>
				</div>

				<div class="cedoc-v3-details">
					<?php foreach ( $categories as $category ) :
						$subcategories = cedoc_get_subcategories_by_category( $category['slug'] );
						?>
						<details class="cedoc-v3-details-item" id="cedoc-v3-details-<?php echo esc_attr( $category['slug'] ); ?>">
							<summary>
								<strong><?php echo esc_html( $category['name'] ); ?></strong>
								<span><?php echo esc_html( count( $subcategories ) ); ?> subcategorias</span>
							</summary>
							<div class="cedoc-v3-details-body">
								<p><?php echo esc_html( $category['description'] ); ?></p>
								<div class="cedoc-v3-subcategories">
									<?php foreach ( $subcategories as $subcategory ) : ?>
										<a href="<?php echo esc_url( get_page_link( $subcategory['id'] ) ); ?>" class="btn btn-sm btn-outline-primary"><?php echo esc_html( $subcategory['title'] ); ?></a>
									<?php endforeach; ?>
								</div>
							</div>
						</details>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

	<?php endif; ?>

	<section class="py-5 bg-light">
		<div class="container-fluid max-large margin-one-column">
			<?php echo cedoc_render_category_story_hub( $categories, $category_blueprints ); ?>
		</div>
	</section>

</main>

<?php get_footer();
