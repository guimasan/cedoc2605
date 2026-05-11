<?php
/**
 * The front page template
 *
 * This is the main template file for the front page of the site.
 * It displays the carousel, CTA buttons, and category dropdown sections.
 *
 * @package Tainacan_Interface
 */

get_header(); 
?>

<main role="main" class="mt-0">
	
	<?php $categories = cedoc_get_categories(); ?>

	<!-- HERO CAROUSEL SECTION - Right after header -->
	<section class="cedoc-hero-carousel">
		<div id="cedocHeroCarousel" class="carousel slide cedoc-featured-carousel" data-ride="carousel" data-interval="6500" data-pause="hover">
				<ol class="carousel-indicators">
					<?php foreach ($categories as $index => $category) : ?>
						<li data-target="#cedocHeroCarousel" data-slide-to="<?php echo esc_attr($index); ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>"></li>
					<?php endforeach; ?>
				</ol>

				<div class="carousel-inner">
					<?php foreach ($categories as $index => $category) :
						$slide_image = cedoc_get_random_category_image($category['slug']);
						if (!$slide_image) {
							$slide_image = cedoc_get_random_item_image();
						}
						$subcategories = cedoc_get_subcategories_by_category($category['slug']);
						$primary_link = !empty($subcategories)
							? get_page_link($subcategories[0]['id'])
							: home_url('/acervo-cedoc/');
					?>
						<div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
							<div class="cedoc-featured-slide">
								<div class="cedoc-featured-slide-image-wrap">
									<?php if ($slide_image) : ?>
										<img class="cedoc-featured-slide-image" src="<?php echo esc_url($slide_image); ?>" alt="<?php echo esc_attr($category['name']); ?>">
									<?php else : ?>
										<div class="cedoc-featured-slide-placeholder"></div>
									<?php endif; ?>
								</div>
								<div class="cedoc-featured-slide-content">
									<span class="cedoc-featured-kicker">Categoria do Acervo</span>
									<h1><?php echo esc_html($category['name']); ?></h1>
									<p><?php echo esc_html($category['description']); ?></p>
									<div class="cedoc-featured-actions">
										<a href="<?php echo esc_url($primary_link); ?>" class="btn btn-light btn-lg">Ver Categoria</a>
										<a href="#cedoc-category-panel-<?php echo esc_attr($category['slug']); ?>" class="btn btn-outline-light btn-lg">Explorar Subcategorias</a>
									</div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<a class="carousel-control-prev" href="#cedocHeroCarousel" role="button" data-slide="prev">
					<span class="carousel-control-prev-icon" aria-hidden="true"></span>
					<span class="sr-only">Anterior</span>
				</a>
				<a class="carousel-control-next" href="#cedocHeroCarousel" role="button" data-slide="next">
					<span class="carousel-control-next-icon" aria-hidden="true"></span>
					<span class="sr-only">Próximo</span>
				</a>
			</div>
		</div>
	</section>

	<!-- CATEGORIES DROPDOWN SECTION -->
	<section class="cedoc-categories-section py-5" style="background-color: #f8f9fa;">
		<div class="container-fluid max-large margin-one-column">
			<div class="cedoc-section-header text-center mb-4">
				<h2 class="mb-2">CEACA - Centro de Estudos e Aplicação da Capoeira</h2>
			</div>

			<div class="cedoc-dropdown-list">
				<?php
				foreach ($categories as $category) :
					$subcategories = cedoc_get_subcategories_by_category($category['slug']);
					$preview_image = cedoc_get_random_category_image($category['slug']);
				?>
					<details class="cedoc-category-dropdown" id="cedoc-category-panel-<?php echo esc_attr($category['slug']); ?>">
						<summary class="cedoc-category-summary">
							<div class="cedoc-category-preview">
								<?php if ($preview_image) : ?>
									<img src="<?php echo esc_url($preview_image); ?>" alt="<?php echo esc_attr($category['name']); ?>">
								<?php endif; ?>
							</div>
							<div class="cedoc-category-meta">
								<span class="cedoc-category-name"><?php echo esc_html($category['name']); ?></span>
								<span class="cedoc-category-count"><?php echo esc_html(count($subcategories)); ?> subcategorias disponíveis</span>
							</div>
							<span class="cedoc-toggle-icon tainacan-icon tainacan-icon-arrowdown"></span>
						</summary>

						<div class="cedoc-category-panel">
							<p class="cedoc-category-desc"><?php echo esc_html($category['description']); ?></p>
							<div class="cedoc-subcategories-grid">
								<?php foreach ($subcategories as $subcategory) : ?>
									<a href="<?php echo esc_url(get_page_link($subcategory['id'])); ?>" class="cedoc-subcategory-card">
										<span class="cedoc-subcategory-title"><?php echo esc_html($subcategory['title']); ?></span>
									</a>
								<?php endforeach; ?>
							</div>
						</div>
					</details>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

</main>

<?php get_footer(); ?>
