<?php
/**
 * Template Name: Subcategory Page with Tainacan Collection
 * Description: Subcategory page template that displays page content and filtered Tainacan collection
 *
 * @package Tainacan_Interface
 */

get_header(); 
get_template_part( 'template-parts/bannerheader' );

$subcategory_slug = cedoc_get_current_subcategory_slug();
$search_term = isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '';
?>

<main role="main" class="mt-5">
	
	<!-- SUBCATEGORY HEADER -->
	<section class="cedoc-subcategory-header py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
		<div class="container-fluid max-large margin-one-column">
			<div class="row">
				<div class="col-12">
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb bg-transparent mb-3">
							<li class="breadcrumb-item"><a href="<?php echo esc_url(home_url()); ?>" style="color: rgba(255,255,255,0.8);">Home</a></li>
							<li class="breadcrumb-item active" style="color: rgba(255,255,255,0.9);"><?php the_title(); ?></li>
						</ol>
					</nav>

					<h1 class="mb-3"><?php the_title(); ?></h1>
					
					<?php if (have_posts()) : the_post(); ?>
						<p class="lead mb-4">
							<?php 
							$excerpt = get_the_excerpt();
							if ($excerpt) {
								echo wp_kses_post($excerpt);
							}
							?>
						</p>
					<?php endif; wp_reset_postdata(); ?>

					<div class="cedoc-subcategory-actions">
						<a href="<?php echo esc_url(home_url()); ?>#categoria-<?php echo esc_attr(cedoc_get_category_from_subcategory_slug($subcategory_slug)); ?>" class="btn btn-light btn-sm mr-2">
							<i class="tainacan-icon tainacan-icon-arrowleft"></i> Voltar para Categorias
						</a>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- SUBCATEGORY DESCRIPTION -->
	<section class="cedoc-subcategory-content py-5 bg-white">
		<div class="container-fluid max-large margin-one-column">
			<div class="row">
				<div class="col-12">
					<?php
					if (have_posts()) {
						while (have_posts()) {
							the_post();
							?>
							<article class="cedoc-page-article">
								<?php
								// Display content but exclude heading (already in header)
								$content = get_the_content();
								$content = preg_replace('/<h1[^>]*>.*?<\/h1>/i', '', $content);
								echo wp_kses_post($content);
								?>
							</article>
							<?php
						}
					}
					wp_reset_postdata();
					?>
				</div>
			</div>
		</div>
	</section>

	<section class="cedoc-subcategory-collection py-5" style="background-color: #f8f9fa;">
		<div class="container-fluid max-large margin-one-column">
			<?php
			$items = cedoc_get_items_by_subcategory($subcategory_slug, 24, $search_term);
			$collection_id = cedoc_get_cedoc_collection_id();
			$archive_url = $collection_id ? get_post_type_archive_link('tainacan-item') : home_url('/acervo-cedoc/');
			$category_slug = cedoc_get_category_from_subcategory_slug($subcategory_slug);
			?>

			<div class="cedoc-gallery-toolbar card border-0 shadow-sm mb-4">
				<div class="card-body">
					<div class="row align-items-end">
						<div class="col-12 col-lg-8 mb-3 mb-lg-0">
							<h2 class="mb-2">Acervo desta Subcategoria</h2>
							<p class="text-muted mb-0">Visualização em cards com busca, seleção e acesso rápido aos itens relacionados.</p>
						</div>
						<div class="col-12 col-lg-4 text-lg-right">
							<a href="<?php echo esc_url(home_url('/acervo-cedoc/')); ?>" class="btn btn-sm btn-outline-dark mr-2 mb-2">Ver acervo</a>
							<a href="<?php echo esc_url(home_url('/#categoria-' . esc_attr($category_slug))); ?>" class="btn btn-sm btn-outline-primary mb-2">Voltar categoria</a>
						</div>
					</div>

					<form method="get" class="mt-4">
						<div class="row">
							<div class="col-12 col-lg-8 mb-3 mb-lg-0">
								<div class="input-group">
									<input type="search" class="form-control" name="search" value="<?php echo esc_attr($search_term); ?>" placeholder="Buscar neste acervo...">
									<div class="input-group-append">
										<button class="btn btn-primary" type="submit">Buscar</button>
									</div>
								</div>
							</div>
							<div class="col-12 col-lg-4 d-flex justify-content-lg-end">
								<div class="btn-group" role="group" aria-label="Modo de visualização">
									<a href="<?php echo esc_url(add_query_arg(array_filter(array('search' => $search_term)), $archive_url)); ?>" class="btn btn-outline-secondary active">Galeria</a>
									<a href="<?php echo esc_url(add_query_arg('posts_per_page', 12, get_permalink())); ?>" class="btn btn-outline-secondary">Resumo</a>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>

			<?php if (!empty($items)) : ?>
				<div class="cedoc-gallery-grid">
					<?php foreach ($items as $item) : ?>
						<?php
						$thumbnail = cedoc_get_item_thumbnail($item->ID, 'large');
						$item_link = get_permalink($item->ID);
						$item_title = get_the_title($item->ID) ?: 'Item ' . $item->ID;
						$item_synopsis = cedoc_get_item_synopsis($item->ID, 18);
						?>
						<article class="cedoc-gallery-card">
							<a href="<?php echo esc_url($item_link); ?>">
								<div class="cedoc-gallery-card-media">
									<img src="<?php echo esc_url($thumbnail ? $thumbnail : get_theme_file_uri('/assets/images/thumbnail_placeholder.png')); ?>" alt="<?php echo esc_attr($item_title); ?>">
								</div>
								<div class="cedoc-gallery-card-body">
									<div class="cedoc-gallery-card-kicker">Subcategoria</div>
									<h3 class="cedoc-gallery-card-title"><?php echo esc_html($item_title); ?></h3>
									<p class="cedoc-gallery-card-excerpt"><?php echo esc_html($item_synopsis); ?></p>
								</div>
							</a>
						</article>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="alert alert-info" role="alert">
					<i class="tainacan-icon tainacan-icon-info"></i>
					Nenhum item encontrado nesta subcategoria ainda.
				</div>
			<?php endif; ?>
		</div>
	</section>

	<!-- RELATED SUBCATEGORIES -->
	<section class="cedoc-related-subcategories py-5 bg-white">
		<div class="container-fluid max-large margin-one-column">
			<div class="row mb-4">
				<div class="col-12">
					<h3 class="text-center">Outras Subcategorias</h3>
				</div>
			</div>

			<div class="row">
				<?php
				$current_category = cedoc_get_category_from_subcategory_slug($subcategory_slug);
				$related_subcategories = cedoc_get_subcategories_by_category($current_category);
				$current_page_id = get_the_ID();
				
				if (!empty($related_subcategories)) {
					$count = 0;
					foreach ($related_subcategories as $subcat) {
						if ($subcat['id'] === $current_page_id) {
							continue; // Skip current page
						}
						
						if ($count >= 3) {
							break; // Show only 3 related
						}
						
						$random_image = cedoc_get_random_item_image();
						$bg_style = $random_image ? 'background-image: url(' . esc_url($random_image) . ');' : '';
						?>
						<div class="col-12 col-md-6 col-lg-4 mb-3">
							<a href="<?php echo esc_url(get_page_link($subcat['id'])); ?>" class="cedoc-related-card card border-0 shadow-sm h-100 text-decoration-none overflow-hidden" style="transition: transform 0.3s ease;">
								<div class="cedoc-related-image" style="height: 160px; background-size: cover; background-position: center; background-color: #e9ecef; <?php echo esc_attr($bg_style); ?>"></div>
								<div class="card-body">
									<h6 class="card-title text-dark mb-2"><?php echo esc_html($subcat['title']); ?></h6>
									<span class="badge badge-secondary badge-sm">Acessar</span>
								</div>
							</a>
						</div>
						<?php
						$count++;
					}
				}
				?>
			</div>
		</div>
	</section>

</main>

<?php get_footer(); ?>
