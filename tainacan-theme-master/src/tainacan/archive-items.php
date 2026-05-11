<?php
get_header();

$collection_id = function_exists('cedoc_get_cedoc_collection_id') ? cedoc_get_cedoc_collection_id() : 42;
$current_post_type = get_query_var('post_type');
$expected_post_type = 'tnc_col_' . $collection_id . '_item';
$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
$search_term = isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '';
$is_cedoc_gallery = $current_post_type === $expected_post_type || strpos($request_uri, 'acervo-cedoc') !== false;

if ($is_cedoc_gallery && function_exists('cedoc_get_cedoc_gallery_items')) :
	$paged = max(1, (int) get_query_var('paged'));
	$query = cedoc_get_cedoc_gallery_items(24, $paged, $search_term);
?>

<main role="main" class="mt-0">
	<section class="cedoc-gallery-archive-header py-5">
		<div class="container-fluid max-large margin-one-column">
			<div class="row align-items-center">
				<div class="col-12 col-lg-8">
					<h1 class="mb-3" style="font-size: clamp(2.2rem, 4vw, 3.4rem); font-weight: 900; color: #FFD700;">Acervo CEDOC</h1>
					<p class="lead mb-0" style="font-size: 1.1rem;">Visualização em galeria do acervo documental, com imagens, metadados essenciais e acesso direto aos itens publicados.</p>
				</div>
				<div class="col-12 col-lg-4 text-lg-right mt-4 mt-lg-0">
					<a href="#cedoc-gallery-grid" class="btn" style="background:#FFD700; color:#000; font-weight:800; border-radius:10px; padding:0.85rem 1.25rem;">Explorar galeria</a>
				</div>
			</div>
		</div>
	</section>

	<section class="cedoc-gallery-archive py-5">
		<div class="container-fluid max-large margin-one-column">
			<div class="cedoc-gallery-toolbar card border-0 shadow-sm mb-4">
				<div class="card-body">
					<div class="row align-items-center">
						<div class="col-12 col-lg-7 mb-3 mb-lg-0">
							<h2 class="mb-2" style="font-size: 1.55rem; font-weight: 900; color: #111;">Filtrar o acervo</h2>
							<p class="text-muted mb-0">Busque por título, código ou texto, e use a navegação por categorias e subcategorias abaixo para orientar a exploração.</p>
						</div>
						<div class="col-12 col-lg-5">
							<form method="get" class="mb-3">
								<div class="input-group">
									<input type="search" class="form-control" name="search" value="<?php echo esc_attr($search_term); ?>" placeholder="Buscar no acervo...">
									<div class="input-group-append">
										<button class="btn btn-primary" type="submit">Buscar</button>
									</div>
								</div>
							</form>
							<div class="d-flex flex-wrap justify-content-lg-end" style="gap: 0.5rem;">
								<a class="btn btn-sm btn-outline-dark" href="<?php echo esc_url(home_url('/')); ?>#categoria-ceaca">CEACA</a>
								<a class="btn btn-sm btn-outline-dark" href="<?php echo esc_url(home_url('/')); ?>#categoria-educacao-cultura">Educação e Cultura</a>
								<a class="btn btn-sm btn-outline-dark" href="<?php echo esc_url(home_url('/')); ?>#categoria-articulacao">Articulação</a>
								<a class="btn btn-sm btn-outline-dark" href="<?php echo esc_url(home_url('/')); ?>#categoria-documentacao-saberes">Saberes</a>
								<a class="btn btn-sm btn-outline-dark" href="<?php echo esc_url(home_url('/')); ?>#categoria-manifestacoes-culturais">Manifestações</a>
							</div>
						</div>
					</div>
				</div>
			</div>

			<?php if ($query->have_posts()) : ?>
				<div id="cedoc-gallery-grid" class="cedoc-gallery-grid">
					<?php while ($query->have_posts()) : $query->the_post(); ?>
						<?php $thumb = cedoc_get_item_thumbnail(get_the_ID(), 'large'); ?>
						<?php $synopsis = cedoc_get_item_synopsis(get_the_ID(), 18); ?>
						<article class="cedoc-gallery-card">
							<a href="<?php the_permalink(); ?>">
								<div class="cedoc-gallery-card-media">
									<img src="<?php echo esc_url($thumb ? $thumb : get_theme_file_uri('/assets/images/thumbnail_placeholder.png')); ?>" alt="<?php the_title_attribute(); ?>">
								</div>
								<div class="cedoc-gallery-card-body">
									<div class="cedoc-gallery-card-kicker">Item do acervo</div>
									<h2 class="cedoc-gallery-card-title"><?php the_title(); ?></h2>
									<p class="cedoc-gallery-card-excerpt">
										<?php echo esc_html($synopsis); ?>
									</p>
								</div>
							</a>
						</article>
					<?php endwhile; ?>
				</div>

				<div class="cedoc-gallery-pagination">
					<?php echo paginate_links(array(
						'current' => max(1, $paged),
						'total' => $query->max_num_pages,
						'prev_text' => '« Anterior',
						'next_text' => 'Próximo »',
					)); ?>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<div class="text-center py-5">
					<h2 style="font-weight:900; color:#111;">Nenhum item encontrado</h2>
					<p style="color:#555;">O acervo está vazio ou os itens ainda não possuem imagem destacada.</p>
				</div>
			<?php endif; ?>
		</div>
	</section>
</main>

<?php
else :
	get_template_part('tainacan/header-collection');
	tainacan_the_faceted_search([
		'default_items_per_page' => get_theme_mod('tainacan_items_page_default_items_per_page', 12),
		'hide_filters' => get_theme_mod('tainacan_items_page_hide_filters', false),
		'hide_hide_filters_button' => get_theme_mod('tainacan_items_page_hide_hide_filters_button', false),
		'hide_search' => get_theme_mod('tainacan_items_page_hide_search', false),
		'hide_advanced_search' => get_theme_mod('tainacan_items_page_hide_advanced_search', false),
		'hide_sort_by_button' => get_theme_mod('tainacan_items_page_hide_sort_by_button', false),
		'hide_exposers_button' => get_theme_mod('tainacan_items_page_hide_exposers_button', false),
		'hide_items_per_page_button' => get_theme_mod('tainacan_items_page_hide_items_per_page_button', false),
		'hide_go_to_page_button' => get_theme_mod('tainacan_items_page_hide_go_to_page_button', false),
		'show_filters_button_inside_search_control' => get_theme_mod('tainacan_items_page_show_filters_button_inside_search_control', false),
		'start_with_filters_hidden' => get_theme_mod('tainacan_items_page_start_with_filters_hidden', false),
		'filters_as_modal' => get_theme_mod('tainacan_items_page_filters_as_modal', false),
		'show_inline_view_mode_options' => get_theme_mod('tainacan_items_page_show_inline_view_mode_options', false),
		'show_fullscreen_with_view_modes' => get_theme_mod('tainacan_items_page_show_fullscreen_with_view_modes', false),
		'should_not_hide_filters_on_mobile' => get_theme_mod('tainacan_items_page_should_not_hide_filters_on_mobile', false),
		'display_filters_horizontally' => get_theme_mod('tainacan_items_page_display_filters_horizontally', false),
		'hide_filter_collapses' => get_theme_mod('tainacan_items_page_hide_filter_collapses', false)
	]);
endif;

get_footer();
