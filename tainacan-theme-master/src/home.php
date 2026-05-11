<?php
/**
 * The home page template
 *
 * @package Tainacan_Interface
 */

get_header(); 

// Get the banner to display
get_template_part( 'template-parts/bannerheader' );
?>

<main role="main" class="mt-5">
	
	<!-- CAROUSEL SECTION -->
	<section class="cedoc-carousel-section py-5" style="background-color: #f8f9fa;">
		<div class="container-fluid max-large margin-one-column">
			<h2 class="mb-4 text-center">Categorias do Acervo</h2>
			<div class="row cedoc-carousel">
				<?php
				$categories = cedoc_get_categories();
				foreach ($categories as $category) :
					$random_image = cedoc_get_random_category_image($category['slug']);
					$bg_style = $random_image ? 'background-image: url(' . esc_url($random_image) . ');' : '';
				?>
					<div class="col-12 col-md-6 col-lg-4 mb-4">
						<div class="cedoc-carousel-item card h-100 border-0 shadow-sm" style="overflow: hidden;">
							<div class="cedoc-carousel-image" style="height: 250px; background-size: cover; background-position: center; background-color: #e9ecef; <?php echo esc_attr($bg_style); ?>"></div>
							<div class="card-body">
								<h5 class="card-title"><?php echo esc_html($category['name']); ?></h5>
								<p class="card-text text-muted small"><?php echo esc_html($category['description']); ?></p>
								<a href="#cedoc-category-<?php echo esc_attr($category['slug']); ?>" class="btn btn-sm btn-outline-primary">
									Ver Subcategorias
								</a>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- CEDOC/CEACA CTA BUTTONS SECTION -->
	<section class="cedoc-cta-section py-5" style="background-color: #fff;">
		<div class="container-fluid max-large margin-one-column">
			<div class="row">
				<div class="col-12 col-md-6 mb-4 mb-md-0">
					<div class="cedoc-cta-button cedoc-cta-cedoc text-center p-5 rounded" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; cursor: pointer; text-decoration: none; display: block; min-height: 200px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
						<h3 class="mb-3">Centro de Documentação</h3>
						<p class="mb-4">CEDOC</p>
						<a href="<?php echo esc_url(get_page_link(cedoc_get_page_by_slug('centro-de-documentacao'))); ?>" class="btn btn-light">
							Acessar
						</a>
					</div>
				</div>
				<div class="col-12 col-md-6">
					<div class="cedoc-cta-button cedoc-cta-ceaca text-center p-5 rounded" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; cursor: pointer; text-decoration: none; display: block; min-height: 200px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
						<h3 class="mb-3">Centro de Estudos e Aplicação</h3>
						<p class="mb-4">CEACA</p>
						<a href="<?php echo esc_url(get_page_link(cedoc_get_page_by_slug('centro-de-documentacao'))); ?>" class="btn btn-light">
							Acessar
						</a>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- CATEGORIES DROPDOWN / ACCORDION SECTION -->
	<section class="cedoc-categories-section py-5" style="background-color: #f8f9fa;">
		<div class="container-fluid max-large margin-one-column">
			<h2 class="mb-5 text-center">Explore as Subcategorias</h2>
			
			<div class="accordion cedoc-categories-accordion" id="cedocCategoriesAccordion">
				<?php
				$categories = cedoc_get_categories();
				$accordion_index = 0;
				foreach ($categories as $category) :
					$subcategories = cedoc_get_subcategories_by_category($category['slug']);
					$accordion_id = 'cedoc-category-' . $category['slug'];
				?>
					<div class="card border-0 mb-3 cedoc-category-card">
						<div class="card-header bg-white border-bottom" id="heading_<?php echo esc_attr($category['slug']); ?>">
							<h2 class="mb-0">
								<button 
									class="btn btn-link btn-block text-left p-3 text-dark" 
									type="button" 
									data-toggle="collapse" 
									data-target="#collapse_<?php echo esc_attr($category['slug']); ?>" 
									aria-expanded="false" 
									aria-controls="collapse_<?php echo esc_attr($category['slug']); ?>">
									<strong><?php echo esc_html($category['name']); ?></strong>
									<span class="float-right">
										<i class="tainacan-icon tainacan-icon-arrowdown"></i>
									</span>
								</button>
							</h2>
						</div>

						<div id="collapse_<?php echo esc_attr($category['slug']); ?>" class="collapse" aria-labelledby="heading_<?php echo esc_attr($category['slug']); ?>" data-parent="#cedocCategoriesAccordion">
							<div class="card-body">
								<div class="row">
									<?php 
									foreach ($subcategories as $subcategory) :
										$random_image = cedoc_get_random_item_image();
										$bg_style = $random_image ? 'background-image: url(' . esc_url($random_image) . ');' : '';
										$page_link = get_page_link($subcategory['id']);
									?>
										<div class="col-12 col-md-6 col-lg-4 mb-4">
											<a href="<?php echo esc_url($page_link); ?>" class="cedoc-subcategory-preview card border-0 shadow-sm h-100 text-decoration-none" style="overflow: hidden; display: flex; flex-direction: column;">
												<div class="cedoc-subcategory-image" style="height: 180px; background-size: cover; background-position: center; background-color: #e9ecef; <?php echo esc_attr($bg_style); ?>"></div>
												<div class="card-body d-flex flex-column flex-grow-1">
													<h6 class="card-title text-dark"><?php echo esc_html($subcategory['title']); ?></h6>
													<p class="card-text text-muted small flex-grow-1"><?php echo esc_html(substr($subcategory['content'], 0, 80)) . '...'; ?></p>
													<span class="badge badge-primary mt-auto">Ver Acervo</span>
												</div>
											</a>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					</div>
				<?php 
					$accordion_index++;
				endforeach; 
				?>
			</div>
		</div>
	</section>

	<!-- HOME PAGE CONTENT -->
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

</main>

<?php get_footer(); ?>
