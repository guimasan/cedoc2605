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
				<a href="" class="btn btn-sm" style="background:#FFD700; color:#000; font-weight:800; border-radius:8px;">Explorar Coleção</a>
			</div>
		</div>
	</div>
</section>

<section class="tainacan-acervo py-5">
	<div class="container-fluid max-large margin-one-column">
		<?php
		$collection_id = cedoc_get_cedoc_collection_id();
		if (!$collection_id) {
			echo '<p>Coletando acervo indisponível.</p>';
		} else {
			$item_post_type = 'tnc_col_' . $collection_id . '_item';
			$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
			$query = new WP_Query(array(
				'post_type' => $item_post_type,
				'posts_per_page' => 24,
				'paged' => $paged,
				'post_status' => 'publish',
			));
			if ($query->have_posts()) :
				echo '<div class="tainacan-view-mode-grid">';
				while ($query->have_posts()) : $query->the_post();
					$thumb = cedoc_get_item_thumbnail(get_the_ID(), 'large');
					if (!$thumb) $thumb = get_theme_file_uri('/assets/images/thumbnail_placeholder.png');
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
		?>
	</div>
</section>

</main>

<?php get_footer();
