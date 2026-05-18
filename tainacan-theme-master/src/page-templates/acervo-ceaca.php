<?php
/**
 * Template Name: Acervo CEACA
 * Description: Página de acervo para conteúdos CEACA
 */

get_header(); 
?>

<main role="main" class="mt-0">
	<!-- CEACA ACERVO HEADER -->
	<section class="cedoc-acervo-header py-5" style="background: linear-gradient(135deg, #C41E3A 0%, #8B0000 100%); color: white;">
		<div class="container-fluid max-large margin-one-column">
			<div class="row align-items-center">
				<div class="col-12 col-md-8">
					<h1 class="mb-3" style="font-size: 3rem; font-weight: 900; letter-spacing: -0.02em;">Acervo CEACA</h1>
					<p class="lead" style="font-size: 1.3rem; opacity: 0.95;">
						Centro de Estudos e Aplicação da Capoeira - Conteúdos, pesquisas e recursos educacionais
					</p>
				</div>
				<div class="col-12 col-md-4 text-center">
					<div style="width: 150px; height: 150px; margin: 0 auto; background: rgba(255, 255, 255, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
						<span style="font-size: 4rem;">🥁</span>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- NAVIGATION TABS -->
	<section class="cedoc-acervo-nav py-4" style="background: #f8f9fa; border-bottom: 2px solid #FFD700;">
		<div class="container-fluid max-large margin-one-column">
			<ul class="nav nav-pills" role="tablist" style="gap: 1rem; flex-wrap: wrap;">
				<li class="nav-item">
					<a class="nav-link active" data-toggle="tab" href="#cedoc-galeria" role="tab" style="background: #FFD700; color: #000; font-weight: 700; padding: 0.75rem 1.5rem; border-radius: 8px;">
						📸 Galeria
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" data-toggle="tab" href="#cedoc-subcategorias" role="tab" style="border: 2px solid #FFD700; color: #000; font-weight: 700; padding: 0.75rem 1.5rem; border-radius: 8px;">
						📁 Subcategorias
					</a>
				</li>
			</ul>
		</div>
	</section>

	<!-- GALERIA VIEW -->
	<div class="tab-content">
		<div id="cedoc-galeria" class="tab-pane fade show active" role="tabpanel">
			<section class="cedoc-acervo-galeria py-5">
				<div class="container-fluid max-large margin-one-column">
					<?php 
						// Get CEACA subcategories and display items
						$ceaca_items = cedoc_get_items_by_category('ceaca');
					?>
					
					<?php if (!empty($ceaca_items)) : ?>
						<div class="cedoc-items-gallery" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
							<?php foreach ($ceaca_items as $item) : ?>
								<div class="cedoc-gallery-item" style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); transition: all 0.3s ease; cursor: pointer;" 
									onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 12px 32px rgba(196, 30, 58, 0.2)';" 
									onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0, 0, 0, 0.1)';">
									
									<?php 
										$image_url = cedoc_get_item_thumbnail( $item->ID, 'large' );
										if ( ! $image_url ) {
											$image_url = cedoc_get_random_item_image();
										}
									?>
									
									<div style="aspect-ratio: 1; overflow: hidden; background: #f0f0f0;">
										<img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($item->post_title); ?>" 
											style="width: 100%; height: 100%; object-fit: cover;">
									</div>
									
									<div style="padding: 1.5rem; background: #fff;">
										<h3 style="font-size: 1.1rem; font-weight: 700; color: #000; margin-bottom: 0.5rem; line-height: 1.3;">
											<?php echo esc_html($item->post_title); ?>
										</h3>
										<p style="font-size: 0.9rem; color: #666; margin-bottom: 1rem; line-height: 1.5;">
											<?php echo esc_html(cedoc_get_item_synopsis($item->ID, 15)); ?>
										</p>
										<a href="<?php echo get_permalink($item->ID); ?>" class="btn btn-sm" 
											style="background: #FFD700; color: #000; border: none; font-weight: 700; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; display: inline-block;">
											Ver Item
										</a>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</section>
		</div>

		<!-- SUBCATEGORIAS VIEW -->
		<div id="cedoc-subcategorias" class="tab-pane fade" role="tabpanel">
			<section class="cedoc-acervo-subcategorias py-5">
				<div class="container-fluid max-large margin-one-column">
					<?php 
						$ceaca_subcats = cedoc_get_subcategories_by_category('ceaca');
					?>
					
					<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
						<?php foreach ($ceaca_subcats as $subcat) : ?>
							<div class="cedoc-subcat-card" style="border: 2px solid #FFD700; border-radius: 12px; padding: 2rem; background: #fff; transition: all 0.3s ease;"
								onmouseover="this.style.borderColor='#C41E3A'; this.style.boxShadow='0 8px 24px rgba(196, 30, 58, 0.2)';" 
								onmouseout="this.style.borderColor='#FFD700'; this.style.boxShadow='none';">
								
								<h3 style="font-size: 1.3rem; font-weight: 900; color: #000; margin-bottom: 0.75rem;">
									<?php echo esc_html($subcat['title']); ?>
								</h3>
								
								<p style="font-size: 0.95rem; color: #666; margin-bottom: 1.5rem; line-height: 1.6;">
									<?php echo esc_html(substr(strip_tags($subcat['content']), 0, 100)) . '...'; ?>
								</p>
								
								<a href="<?php echo get_permalink($subcat['id']); ?>" class="btn btn-sm" 
									style="background: #C41E3A; color: #fff; border: none; font-weight: 700; padding: 0.75rem 1.5rem; border-radius: 6px; text-decoration: none; display: inline-block;">
									Acessar Subcategoria
								</a>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		</div>
	</div>

</main>

<?php get_footer();
