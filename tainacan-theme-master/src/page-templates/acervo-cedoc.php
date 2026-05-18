<?php
/**
 * Template Name: Acervo CEDOC
 * Description: Página de visualização em galeria para o acervo CEDOC (Tainacan collection)
 */

get_header();
?>

<main role="main" class="mt-0">

<section class="cedoc-acervo-header py-5" style="background: #000; color: #fff;">
	<div class="container-fluid max-large margin-one-column">
		<div class="row align-items-center">
			<div class="col-12 col-md-9">
				<h1 class="mb-2" style="font-size: 2.6rem; font-weight: 900; color: #FFD700;">Acervo CEDOC</h1>
				<p class="lead" style="color: rgba(255,255,255,0.9);">Galeria do acervo digitalizado. Clique em um item para acessar detalhes.</p>
			</div>
			<div class="col-12 col-md-3 text-right">
				<a href="<?php echo esc_url( home_url( '/acervo-cedoc/' ) ); ?>" class="btn btn-sm" style="background:#FFD700; color:#000; font-weight:800; border-radius:8px;">Explorar Coleção</a>
			</div>
		</div>
	</div>
</section>

<section class="tainacan-acervo py-5">
	<div class="container-fluid max-large margin-one-column">
		<?php
		$collection_id = cedoc_get_cedoc_collection_id();
		$requested_category = isset( $_GET['category'] ) ? sanitize_text_field( wp_unslash( $_GET['category'] ) ) : '';

		if ( $requested_category ) {
			// Show category heading and migrated-text pages if available
			$all_categories = cedoc_get_categories();
			$cat_name = '';
			foreach ( $all_categories as $c ) {
				if ( $c['slug'] === $requested_category ) {
					$cat_name = $c['name'];
					break;
				}
			}

			echo '<div class="mb-4">';
			echo '<h2>' . esc_html( $cat_name ? $cat_name : 'Categoria' ) . '</h2>';
			$blueprints = cedoc_get_category_blueprints();
			if ( isset( $blueprints[ $requested_category ] ) ) {
				$page_slugs = $blueprints[ $requested_category ]['page_slugs'];
				echo cedoc_render_page_story_cards( $page_slugs, 'Subpáginas' );
			}
			echo '</div>';

			// Show items for this category
			if ( $collection_id ) {
				$items = cedoc_get_items_by_category( $requested_category, 24 );
				if ( ! empty( $items ) ) {
					echo '<div class="tainacan-view-mode-grid">';
					foreach ( $items as $item ) {
						$thumb = cedoc_get_item_thumbnail( $item->ID, 'large' );
						if ( ! $thumb ) {
							$thumb = cedoc_get_random_item_image();
						}
						echo '<div class="tainacan-items-list-item">';
						echo '<div class="tainacan-thumbnail"><a href="' . esc_url( get_permalink( $item->ID ) ) . '"><img src="' . esc_url( $thumb ) . '" alt="' . esc_attr( get_the_title( $item->ID ) ) . '"></a></div>';
						echo '<div class="metadata"><h3>' . esc_html( get_the_title( $item->ID ) ) . '</h3>';
						echo '<p>' . esc_html( cedoc_get_item_synopsis( $item->ID, 20 ) ) . '</p>';
						echo '<a href="' . esc_url( get_permalink( $item->ID ) ) . '" class="btn btn-sm" style="background:#C41E3A; color:#fff; font-weight:700; border-radius:6px;">Ver</a>';
						echo '</div></div>';
					}
					echo '</div>';
				} else {
					echo '<p>Nenhum item encontrado para essa categoria.</p>';
				}
			} else {
				echo '<p>Coletando acervo indisponível.</p>';
			}

		} else {
			// Default gallery view (all items)
				// Prepare filter values (from GET)
				$filter_search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
				$filter_orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : '';

				// Render a compact, user-friendly toolbar with combined filters when entering the gallery
				if ( $collection_id ) {
					$all_categories = cedoc_get_categories();
					echo '<div class="cedoc-gallery-toolbar cedoc-gallery-toolbar--v2 mb-4">';
					echo '<div class="card-body d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">';
					echo '<form method="get" class="d-flex flex-wrap align-items-center" style="gap:0.5rem; width:100%;">';
					// Search box
					echo '<div class="cedoc-v2-toolbar-section" style="flex:1 1 320px;">';
					echo '<label class="cedoc-filter-label" for="cedoc-filter-search" style="display:block; margin-bottom:0.25rem;">Pesquisar</label>';
					echo '<input id="cedoc-filter-search" name="s" value="' . esc_attr( $filter_search ) . '" placeholder="Buscar no acervo" class="cedoc-filter-select" style="width:100%;">';
					echo '</div>';
					// Category select
					echo '<div class="cedoc-v2-toolbar-section" style="min-width:180px;">';
					echo '<label class="cedoc-filter-label" for="cedoc-filter-category" style="display:block; margin-bottom:0.25rem;">Categoria</label>';
					echo '<select id="cedoc-filter-category" name="category" class="cedoc-filter-select">';
					echo '<option value="">Todas</option>';
					foreach ( $all_categories as $c ) {
						$sel = ( isset( $_GET['category'] ) && $_GET['category'] === $c['slug'] ) ? ' selected' : '';
						echo '<option value="' . esc_attr( $c['slug'] ) . '"' . $sel . '>' . esc_html( $c['name'] ) . '</option>';
					}
					echo '</select>';
					echo '</div>';
					// Orderby select
					echo '<div class="cedoc-v2-toolbar-section" style="min-width:160px;">';
					echo '<label class="cedoc-filter-label" for="cedoc-filter-orderby" style="display:block; margin-bottom:0.25rem;">Ordenar</label>';
					echo '<select id="cedoc-filter-orderby" name="orderby" class="cedoc-filter-select">';
					echo '<option value="">Padrão</option>';
					echo '<option value="newest"' . ( $filter_orderby === 'newest' ? ' selected' : '' ) . '>Mais recentes</option>';
					echo '<option value="oldest"' . ( $filter_orderby === 'oldest' ? ' selected' : '' ) . '>Mais antigos</option>';
					echo '<option value="title"' . ( $filter_orderby === 'title' ? ' selected' : '' ) . '>Título (A–Z)</option>';
					echo '</select>';
					echo '</div>';
					// Submit
					echo '<div class="cedoc-v2-toolbar-section" style="min-width:110px;">';
					echo '<label style="display:block; visibility:hidden; height:0;">&nbsp;</label>';
					echo '<button type="submit" class="btn btn-sm" style="background:#173f35; color:#fff; font-weight:700;">Filtrar</button>';
					echo '</div>';
					echo '</form>';
					echo '</div>';
					echo '</div>';
				}

				if ( ! $collection_id ) {
				echo '<p>Coletando acervo indisponível.</p>';
			} else {
				$item_post_type = 'tnc_col_' . $collection_id . '_item';
				$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
					// Build query args and include simple search/order filters
					$query_args = array(
						'post_type' => $item_post_type,
						'posts_per_page' => 24,
						'paged' => $paged,
						'post_status' => 'publish',
					);

					if ( ! empty( $filter_search ) ) {
						$query_args['s'] = $filter_search;
					}

					if ( 'title' === $filter_orderby ) {
						$query_args['orderby'] = 'title';
						$query_args['order'] = 'ASC';
					} elseif ( 'oldest' === $filter_orderby ) {
						$query_args['orderby'] = 'date';
						$query_args['order'] = 'ASC';
					} elseif ( 'newest' === $filter_orderby ) {
						$query_args['orderby'] = 'date';
						$query_args['order'] = 'DESC';
					}

					$query = new WP_Query( $query_args );
				if ($query->have_posts()) :
					echo '<div class="tainacan-view-mode-grid">';
					while ($query->have_posts()) : $query->the_post();
						$thumb = cedoc_get_item_thumbnail(get_the_ID(), 'large');
						if (!$thumb) {
							$thumb = cedoc_get_random_item_image();
						}
						?>
						<div class="tainacan-items-list-item">
							<div class="tainacan-thumbnail">
								<a href="<?php the_permalink(); ?>">
									<img src="<?php echo esc_url($thumb); ?>" alt="<?php the_title_attribute(); ?>">
								</a>
							</div>
							<div class="metadata">
								<h3><?php the_title(); ?></h3>
								<p><?php echo esc_html(cedoc_get_item_synopsis(get_the_ID(), 20)); ?></p>
								<a href="<?php the_permalink(); ?>" class="btn btn-sm" style="background:#C41E3A; color:#fff; font-weight:700; border-radius:6px;">Ver</a>
							</div>
						</div>
						<?php
					endwhile;
					echo '</div>';
					// Pagination
					$big = 999999999; // need an unlikely integer
					echo '<nav class="mt-4" aria-label="Pagination">';
					echo paginate_links(array(
						'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
						'format' => '?paged=%#%',
						'current' => max(1, $paged),
						'total' => $query->max_num_pages,
						'prev_text' => '« Anterior',
						'next_text' => 'Próximo »',
					));
					echo '</nav>';
					wp_reset_postdata();
				else:
					echo '<p>Nenhum item encontrado no acervo.</p>';
				endif;
			}
		}
		?>
	</div>
</section>

</main>

<?php get_footer();
