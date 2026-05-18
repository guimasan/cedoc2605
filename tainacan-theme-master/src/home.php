<?php
/**
 * The home page template
 *
 * @package Tainacan_Interface
 */

get_header();
get_template_part( 'template-parts/bannerheader' );

$categories = cedoc_get_categories();
?>

<main role="main" class="mt-5 cedoc-home-layout cedoc-home-layout-2">
	<section class="cedoc-layout-v2 py-5">
		<div class="container-fluid max-large margin-one-column">
			<?php if ( ! empty( $categories ) ) : ?>
			<div id="cedocCategoriesCarousel" class="carousel slide mb-4" data-ride="carousel" data-interval="5000">
				<div class="carousel-inner">
					<?php $first = true; foreach ( $categories as $cat ) : $image = cedoc_get_random_category_image( $cat['slug'] ); ?>
						<div class="carousel-item <?php echo $first ? 'active' : ''; ?>">
							<div class="cedoc-carousel-slide">
								<?php if ( $image ) : ?>
									<img class="cedoc-carousel-slide__image" src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $cat['name'] ); ?>">
								<?php endif; ?>
								<div class="cedoc-carousel-slide-overlay">
									<div class="container h-100 d-flex align-items-end">
										<div class="cedoc-carousel-slide-content text-white">
											<span class="cedoc-carousel-slide-kicker"><?php echo esc_html( $cat['name'] ); ?></span>
											<h2><?php echo esc_html( $cat['name'] ); ?></h2>
											<p><?php echo esc_html( $cat['description'] ); ?></p>
											<a href="#cedoc-category-<?php echo esc_attr( $cat['slug'] ); ?>" class="btn btn-light btn-lg">Explorar</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php $first = false; endforeach; ?>
				</div>
				<a class="carousel-control-prev" href="#cedocCategoriesCarousel" role="button" data-slide="prev">
					<span class="carousel-control-prev-icon" aria-hidden="true"></span>
					<span class="sr-only">Previous</span>
				</a>
				<a class="carousel-control-next" href="#cedocCategoriesCarousel" role="button" data-slide="next">
					<span class="carousel-control-next-icon" aria-hidden="true"></span>
					<span class="sr-only">Next</span>
				</a>
			</div>
			<?php endif; ?>

			<section class="cedoc-v2-categories" id="cedoc-v2-categories">
				<div class="cedoc-v2-categories-head">
					<h2>Categorias do Acervo</h2>
					<p>Prévia com imagens reais de cada categoria.</p>
				</div>
				<div class="cedoc-v2-category-grid">
					<?php foreach ( $categories as $category ) :
						$category_image = cedoc_get_random_category_image( $category['slug'] );
						if ( ! $category_image ) {
							$category_image = cedoc_get_random_item_image();
						}
						$category_subpages = cedoc_get_subcategories_by_category( $category['slug'] );
						$category_link = add_query_arg( 'category', $category['slug'], home_url( '/acervo-cedoc/' ) );
						if ( ! empty( $category_subpages ) && isset( $category_subpages[0]['id'] ) ) {
							$category_link = get_page_link( $category_subpages[0]['id'] );
						}
						?>
						<article class="cedoc-v2-category-card" id="cedoc-category-<?php echo esc_attr( $category['slug'] ); ?>">
							<a href="<?php echo esc_url( $category_link ); ?>" class="cedoc-v2-category-card__link">
								<div class="cedoc-v2-category-card__media" style="background-image: url('<?php echo esc_url( $category_image ); ?>');"></div>
								<div class="cedoc-v2-category-card__body">
									<h3><?php echo esc_html( $category['name'] ); ?></h3>
									<p><?php echo esc_html( $category['description'] ); ?></p>
									<span class="cedoc-v2-category-card__cta">Explorar categoria</span>
								</div>
							</a>
						</article>
					<?php endforeach; ?>
				</div>
			</section>

			<section class="cedoc-v2-subpages py-2">
				<?php foreach ( $categories as $category ) :
					$subpages = cedoc_get_subcategories_by_category( $category['slug'] );
					if ( empty( $subpages ) ) {
						continue;
					}
					?>
					<div class="cedoc-v2-subpages-group" id="cedoc-v2-subpages-<?php echo esc_attr( $category['slug'] ); ?>">
						<h3 class="cedoc-v2-subpages-title"><?php echo esc_html( $category['name'] ); ?></h3>
						<div class="cedoc-v2-subpages-grid">
							<?php foreach ( array_slice( $subpages, 0, 6 ) as $subpage ) :
								$subpage_link = get_page_link( $subpage['id'] );
								$subpage_title = $subpage['title'];
								$subpage_excerpt = ! empty( $subpage['content'] )
									? wp_trim_words( wp_strip_all_tags( $subpage['content'] ), 20, '...' )
									: wp_trim_words( wp_strip_all_tags( (string) get_post_field( 'post_content', $subpage['id'] ) ), 20, '...' );
								$subpage_image = cedoc_get_post_primary_image_url( $subpage['id'], 'medium_large' );
								if ( ! $subpage_image ) {
									$subpage_image = cedoc_get_random_category_image( $category['slug'] );
								}
								?>
								<article class="cedoc-v2-subpage-card">
									<a href="<?php echo esc_url( $subpage_link ); ?>" class="cedoc-v2-subpage-card__link">
										<div class="cedoc-v2-subpage-card__media" style="background-image: url('<?php echo esc_url( $subpage_image ); ?>');"></div>
										<div class="cedoc-v2-subpage-card__body">
											<h4><?php echo esc_html( $subpage_title ); ?></h4>
											<p><?php echo esc_html( $subpage_excerpt ); ?></p>
											<span class="cedoc-v2-subpage-card__cta">Abrir página</span>
										</div>
									</a>
								</article>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</section>

			<section class="cedoc-v2-items py-4">
				<?php $gallery = cedoc_get_cedoc_gallery_items( 18, 1 ); if ( $gallery && ! empty( $gallery->posts ) ) : ?>
					<div class="cedoc-gallery-grid">
						<?php foreach ( $gallery->posts as $item ) :
							$thumb = cedoc_get_item_thumbnail( $item->ID );
							$link = get_permalink( $item->ID );
							$title = get_the_title( $item->ID );
							$synopsis = cedoc_get_item_synopsis( $item->ID );
							?>
							<article class="cedoc-gallery-card">
								<a href="<?php echo esc_url( $link ); ?>" class="text-decoration-none text-reset">
									<div class="cedoc-gallery-card-media" style="background-color: #eee;">
										<?php if ( $thumb ) : ?>
											<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $title ); ?>">
										<?php endif; ?>
									</div>
									<div class="p-3">
										<h6 class="mb-1"><?php echo esc_html( $title ); ?></h6>
										<p class="small text-muted mb-0"><?php echo esc_html( $synopsis ); ?></p>
									</div>
								</a>
							</article>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<p><?php esc_html_e( 'Nenhum item encontrado para exibir.', 'tainacan-interface' ); ?></p>
				<?php endif; ?>
			</section>
		</div>
	</section>
</main>

<?php get_footer();
