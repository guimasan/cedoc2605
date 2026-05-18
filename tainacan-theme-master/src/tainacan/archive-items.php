<?php
get_header();

$collection_id = function_exists('cedoc_get_cedoc_collection_id') ? cedoc_get_cedoc_collection_id() : 42;
$current_post_type = get_query_var('post_type');
$expected_post_type = 'tnc_col_' . $collection_id . '_item';
$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
$search_term = isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '';
$current_filters = array(
	'doc_type'  => isset($_GET['filter_doc_type']) ? sanitize_text_field(wp_unslash($_GET['filter_doc_type'])) : '',
	'support'   => isset($_GET['filter_support']) ? sanitize_text_field(wp_unslash($_GET['filter_support'])) : '',
	'year'      => isset($_GET['filter_year']) ? sanitize_text_field(wp_unslash($_GET['filter_year'])) : '',
	'event'     => isset($_GET['filter_event']) ? sanitize_text_field(wp_unslash($_GET['filter_event'])) : '',
	'author'    => isset($_GET['filter_author']) ? sanitize_text_field(wp_unslash($_GET['filter_author'])) : '',
	'keyword'   => isset($_GET['filter_keyword']) ? sanitize_text_field(wp_unslash($_GET['filter_keyword'])) : '',
);
$is_cedoc_gallery = $current_post_type === $expected_post_type || strpos( $request_uri, 'acervo-cedoc' ) !== false;
$cedoc_layout = isset( $_GET['layout'] ) ? sanitize_key( wp_unslash( $_GET['layout'] ) ) : '1';
if ( ! in_array( $cedoc_layout, array( '1', '2', '3' ), true ) ) {
	$cedoc_layout = '1';
}

if ($is_cedoc_gallery && function_exists('cedoc_get_cedoc_gallery_items')) :
	$paged = max(1, (int) get_query_var('paged'));
	$query = cedoc_get_cedoc_gallery_items(24, $paged, $search_term, $current_filters);
	$category_options = function_exists( 'cedoc_get_categories' ) ? cedoc_get_categories() : array();
	$subcategory_options = array();
	if ( function_exists( 'cedoc_get_category_subcategory_mapping' ) ) {
		foreach ( cedoc_get_category_subcategory_mapping() as $category_data ) {
			if ( empty( $category_data['subcategories'] ) || ! is_array( $category_data['subcategories'] ) ) {
				continue;
			}
			$subcategory_options = array_merge( $subcategory_options, $category_data['subcategories'] );
		}
		$subcategory_options = array_values( array_unique( $subcategory_options ) );
	}
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
			<div class="cedoc-gallery-toolbar card border-0 shadow-sm mb-4 cedoc-gallery-toolbar--v<?php echo esc_attr( $cedoc_layout ); ?>">
				<div class="card-body">
					<div class="row align-items-start g-3">
						<div class="col-12 col-lg-7 mb-2 mb-lg-0">
							<?php if ( '2' === $cedoc_layout ) : ?>
								<h2 class="mb-2" style="font-size: 1.55rem; font-weight: 900; color: #111;">Acervo CEDOC — Explorar com filtros rápidos</h2>
									<p class="text-muted mb-0">Pesquise por texto e refine por tipo documental, evento, autoria, suporte, palavras-chave e período.</p>
							<?php elseif ( '3' === $cedoc_layout ) : ?>
								<h2 class="mb-2" style="font-size: 1.55rem; font-weight: 900; color: #111;">Acervo CEDOC — Exploração Imersiva</h2>
								<p class="text-muted mb-0">Modo imersivo com filtros dinâmicos e foco visual em imagens e metadados.</p>
							<?php else: ?>
								<h2 class="mb-2" style="font-size: 1.55rem; font-weight: 900; color: #111;">Filtrar o acervo</h2>
								<p class="text-muted mb-0">Busque por título, código ou texto, e use a navegação por categorias e subcategorias abaixo para orientar a exploração.</p>
							<?php endif; ?>
						</div>
						<div class="col-12 col-lg-5">
							<form method="get" class="cedoc-refine-form">
								<input type="hidden" name="layout" value="<?php echo esc_attr( $cedoc_layout ); ?>">
								<div class="cedoc-refine-search mb-3">
									<label class="cedoc-filter-label mb-1" for="cedoc-gallery-search">Buscar no acervo</label>
									<div class="input-group cedoc-refine-search__group">
										<input id="cedoc-gallery-search" type="search" class="form-control" name="search" value="<?php echo esc_attr( $search_term ); ?>" placeholder="Título, assunto, palavra-chave...">
										<div class="input-group-append">
											<button class="btn btn-dark" type="submit">Buscar</button>
										</div>
									</div>
								</div>

								<div class="cedoc-refine-chip-group mb-3">
									<label class="cedoc-filter-label d-block mb-2">Tipos rápidos</label>
									<div class="d-flex flex-wrap" style="gap: .45rem;">
										<?php
										$current_args = array_filter( array(
											'layout' => $cedoc_layout,
											'search' => $search_term,
											'filter_support' => $current_filters['support'],
											'filter_year' => $current_filters['year'],
											'filter_event' => $current_filters['event'],
											'filter_author' => $current_filters['author'],
											'filter_keyword' => $current_filters['keyword'],
										) );
										$doc_type_options = function_exists( 'cedoc_get_gallery_meta_values' ) ? cedoc_get_gallery_meta_values( '66', 12 ) : array();
										$all_type_url = remove_query_arg( 'filter_doc_type' );
										$all_type_active = empty( $current_filters['doc_type'] );
										?>
										<a class="cedoc-filter-chip <?php echo $all_type_active ? 'cedoc-chip-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( $current_args, $all_type_url ) ); ?>">Todos</a>
										<?php foreach ( $doc_type_options as $doc_type_label ) : ?>
											<?php $chip_args = $current_args; $chip_args['filter_doc_type'] = $doc_type_label; ?>
											<a class="cedoc-filter-chip <?php echo ( sanitize_title( $current_filters['doc_type'] ) === sanitize_title( $doc_type_label ) ) ? 'cedoc-chip-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( $chip_args, remove_query_arg( 'paged' ) ) ); ?>"><?php echo esc_html( $doc_type_label ); ?></a>
										<?php endforeach; ?>
									</div>
								</div>

								<details class="cedoc-refine-advanced mb-3" open>
									<summary class="cedoc-refine-advanced__summary">Filtros avançados</summary>
									<div class="cedoc-refine-advanced__body">
										<div class="row g-3">
											<div class="col-12 col-md-6">
												<label class="cedoc-filter-label" for="cedoc-filter-event">Evento</label>
												<input id="cedoc-filter-event" type="text" class="cedoc-filter-select w-100" name="filter_event" value="<?php echo esc_attr( $current_filters['event'] ); ?>" placeholder="Batizado, roda, encontro...">
											</div>
											<div class="col-6 col-md-3">
												<label class="cedoc-filter-label" for="cedoc-filter-year">Ano</label>
												<input id="cedoc-filter-year" type="text" inputmode="numeric" class="cedoc-filter-select w-100" name="filter_year" value="<?php echo esc_attr( $current_filters['year'] ); ?>" placeholder="2022">
											</div>
											<div class="col-6 col-md-3">
												<label class="cedoc-filter-label" for="cedoc-filter-support">Suporte</label>
												<input id="cedoc-filter-support" type="text" class="cedoc-filter-select w-100" name="filter_support" value="<?php echo esc_attr( $current_filters['support'] ); ?>" placeholder="Digital, papel...">
											</div>
											<div class="col-12">
												<label class="cedoc-filter-label" for="cedoc-filter-author">Autor / criador</label>
												<input id="cedoc-filter-author" type="text" class="cedoc-filter-select w-100" name="filter_author" value="<?php echo esc_attr( $current_filters['author'] ); ?>" placeholder="Nome do autor ou criador">
											</div>
											<div class="col-12">
												<label class="cedoc-filter-label" for="cedoc-filter-keyword">Palavras-chave</label>
												<input id="cedoc-filter-keyword" type="text" class="cedoc-filter-select w-100" name="filter_keyword" value="<?php echo esc_attr( $current_filters['keyword'] ); ?>" placeholder="capoeira, fotografia, oficina...">
											</div>
										</div>
									</div>
								</details>

								<div class="d-flex flex-wrap align-items-center" style="gap: 0.75rem;">
									<button type="submit" class="btn btn-dark">Aplicar filtros</button>
									<a class="btn btn-outline-dark" href="<?php echo esc_url( remove_query_arg( array( 'search', 'filter_doc_type', 'filter_support', 'filter_year', 'filter_event', 'filter_author', 'filter_keyword', 'paged' ) ) ); ?>">Limpar</a>
								</div>
							</form>

							<?php if ( '2' === $cedoc_layout ) : ?>
							<div class="cedoc-v2-sort-row d-flex flex-wrap justify-content-lg-end align-items-center mt-4" style="gap: 0.8rem;">
								<label class="cedoc-sort-label mb-0">Visualizar:</label>
								<div class="btn-group btn-group-sm cedoc-view-toggle" role="group">
									<button type="button" class="btn btn-sm btn-light active" data-view="gallery" title="Galeria"><i class="fas fa-th"></i></button>
									<button type="button" class="btn btn-sm btn-light" data-view="list" title="Lista"><i class="fas fa-list"></i></button>
								</div>
								<select class="cedoc-sort-select" onchange="this.form.submit()">
									<option value="relevancia">Relevância</option>
									<option value="data-desc">Mais recentes</option>
									<option value="data-asc">Mais antigos</option>
									<option value="popular">Populares</option>
								</select>
							</div>
							<?php elseif ( '3' === $cedoc_layout ) : ?>
							<div class="cedoc-v3-toolbar-section">
								<div class="cedoc-v3-filter-chips mb-3">
									<span class="cedoc-chip-label">Filtrar por:</span>
									<button class="cedoc-filter-chip cedoc-chip-active" data-filter="all">Todos</button>
									<button class="cedoc-filter-chip" data-filter="ceaca">CEACA</button>
									<button class="cedoc-filter-chip" data-filter="educacao">Educação & Cultura</button>
									<button class="cedoc-filter-chip" data-filter="articulacao">Articulação</button>
									<button class="cedoc-filter-chip" data-filter="documentacao">Documentação de Saberes</button>
									<button class="cedoc-filter-chip" data-filter="manifestacoes">Manifestações Culturais</button>
								</div>
								<div class="cedoc-v3-view-row d-flex flex-wrap justify-content-lg-end align-items-center" style="gap: 0.8rem;">
									<button class="btn btn-sm btn-outline-dark cedoc-v3-filter-toggle" data-toggle="collapse" data-target="#cedoc-v3-advanced-filters">
										<i class="fas fa-sliders-h"></i> Filtros Avançados
									</button>
									<button class="btn btn-sm btn-outline-dark cedoc-v3-view-toggle" data-view="immersive" title="Visualização imersiva">
										<i class="fas fa-image"></i> Imersivo
									</button>
									<button class="btn btn-sm btn-outline-dark cedoc-v3-zoom-toggle" data-zoom="on" title="Ativar zoom">
										<i class="fas fa-search-plus"></i> Zoom
									</button>
								</div>
							</div>
							<div class="collapse" id="cedoc-v3-advanced-filters">
								<div class="card card-body border-0 bg-light mt-3" style="border-radius: 12px;">
									<div class="row g-3">
										<div class="col-sm-6 col-md-3">
											<label class="cedoc-v3-filter-advanced-label">Período</label>
											<select class="form-control form-control-sm cedoc-v3-filter-select" name="v3_year">
												<option>Todos os anos</option>
												<option>2024</option>
												<option>2023</option>
												<option>2022</option>
											</select>
										</div>
										<div class="col-sm-6 col-md-3">
											<label class="cedoc-v3-filter-advanced-label">Tipo de Material</label>
											<select class="form-control form-control-sm cedoc-v3-filter-select" name="v3_material">
												<option>Todos</option>
												<option>Fotografia</option>
												<option>Documento</option>
											</select>
										</div>
										<div class="col-sm-6 col-md-3">
											<label class="cedoc-v3-filter-advanced-label">Estado</label>
											<select class="form-control form-control-sm cedoc-v3-filter-select" name="v3_status">
												<option>Todos</option>
												<option>Destacados</option>
												<option>Recentes</option>
											</select>
										</div>
										<div class="col-sm-6 col-md-3">
											<label class="cedoc-v3-filter-advanced-label">Busca por Texto</label>
											<input type="text" class="form-control form-control-sm" placeholder="Palavra-chave..." name="v3_search">
										</div>
									</div>
									<div class="mt-3 text-right">
										<button type="submit" class="btn btn-sm btn-dark">Aplicar Filtros</button>
										<button type="reset" class="btn btn-sm btn-outline-dark">Limpar</button>
									</div>
								</div>
								</div>
							<?php else : ?>
								<div class="d-flex flex-wrap justify-content-lg-end" style="gap: 0.5rem;">
									<a class="btn btn-sm btn-outline-dark" href="<?php echo esc_url( home_url( '/' ) ); ?>#categoria-ceaca">CEACA</a>
									<a class="btn btn-sm btn-outline-dark" href="<?php echo esc_url( home_url( '/' ) ); ?>#categoria-educacao-cultura">Educação e Cultura</a>
									<a class="btn btn-sm btn-outline-dark" href="<?php echo esc_url( home_url( '/' ) ); ?>#categoria-articulacao">Articulação</a>
									<a class="btn btn-sm btn-outline-dark" href="<?php echo esc_url( home_url( '/' ) ); ?>#categoria-documentacao-saberes">Saberes</a>
									<a class="btn btn-sm btn-outline-dark" href="<?php echo esc_url( home_url( '/' ) ); ?>#categoria-manifestacoes-culturais">Manifestações</a>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>

			<?php if ($query->have_posts()) : ?>
				<div id="cedoc-gallery-grid" class="cedoc-gallery-grid">
					<?php while ($query->have_posts()) : $query->the_post(); ?>
							<?php $thumb = cedoc_get_item_thumbnail( get_the_ID(), 'large' ); ?>
							<?php if ( ! $thumb ) { $thumb = cedoc_get_random_item_image(); } ?>
						<?php $synopsis = cedoc_get_item_synopsis(get_the_ID(), 18); ?>
						<article class="cedoc-gallery-card">
							<a href="<?php the_permalink(); ?>">
								<div class="cedoc-gallery-card-media">
										<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php the_title_attribute(); ?>">
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
