<?php
/**
 * CEDOC/CEACA helper functions
 * 
 * @package Tainacan_Interface
 */

/**
 * Get all main categories/grupos from subcategory pages
 */
function cedoc_get_categories() {
    $categories = array(
        array(
            'name' => 'CEACA',
            'slug' => 'ceaca',
            'description' => 'Conteúdo relacionado ao Centro de Estudos e Aplicação da Capoeira',
        ),
        array(
            'name' => 'Educação e Cultura Tradicional',
            'slug' => 'educacao-cultura',
            'description' => 'Recursos educacionais e manifestações culturais tradicionais',
        ),
        array(
            'name' => 'Articulação',
            'slug' => 'articulacao',
            'description' => 'Parcerias, redes e projetos de articulação institucional',
        ),
        array(
            'name' => 'Documentação de Saberes',
            'slug' => 'documentacao-saberes',
            'description' => 'Pesquisas, livros, projetos e estudos de campo',
        ),
        array(
            'name' => 'Manifestações Culturais',
            'slug' => 'manifestacoes-culturais',
            'description' => 'Danças, festas, cortejos e apresentações',
        ),
    );
    
    return apply_filters('cedoc_categories', $categories);
}

    /**
     * Get a page snapshot by slug for content hubs.
     */
    function cedoc_get_page_snapshot_by_slug(string $slug) {
        $page = get_page_by_path($slug, OBJECT, 'page');

        if ( ! ($page instanceof WP_Post) ) {
            return null;
        }

        $excerpt = trim( wp_strip_all_tags( (string) get_the_excerpt( $page->ID ) ) );
        if ( $excerpt === '' ) {
            $excerpt = wp_trim_words( wp_strip_all_tags( (string) $page->post_content ), 26, '...' );
        }

        return array(
            'id' => (int) $page->ID,
            'title' => get_the_title( $page->ID ),
            'url' => get_permalink( $page->ID ),
            'image' => cedoc_get_post_primary_image_url( $page->ID, 'medium_large' ),
            'excerpt' => $excerpt,
            'slug' => get_post_field( 'post_name', $page->ID ),
        );
    }

    /**
     * Category blueprints used by the homepage layouts.
     */
    function cedoc_get_category_blueprints() {
        return array(
            'ceaca' => array(
                'lead' => 'História institucional, mestres, formação e memória oral do CEACA.',
                'page_slugs' => array(
                    'institucional',
                    'historico',
                    'missao',
                    'equipe',
                    'mestres',
                    'mestre-alcides-de-lima',
                    'dorival-dos-santos',
                    'durval-do-coco',
                    'eventos',
                    'premios',
                ),
            ),
            'educacao-cultura' => array(
                'lead' => 'Publicações, leis, oficinas e ações educativas que sustentam a cultura tradicional.',
                'page_slugs' => array(
                    'educacao-e-publicacoes',
                    'oficinas',
                    'oficina-audiovisual',
                    'oficina-wordpress',
                    'leis',
                    'lei-cultura-viva',
                    'lei-grio',
                    'lei-no-10-639-2003',
                    'amorim-lima',
                    'capoeira-na-usp',
                    'espaco-girassol',
                ),
            ),
            'articulacao' => array(
                'lead' => 'Redes, parcerias, imprensa e relações institucionais do CEACA.',
                'page_slugs' => array(
                    'redes',
                    'links',
                    'colabore',
                    'imprensa',
                    'forum-das-culturas',
                ),
            ),
            'documentacao-saberes' => array(
                'lead' => 'Memórias, referências, infoteca e produções de pesquisa e documentação.',
                'page_slugs' => array(
                    'memorias-e-projetos',
                    'capoeira-coco-e-ciranda-na-escola',
                    'cultura-e-saude',
                    'infoteca',
                    'audiovisuais',
                    'imagens',
                    'livros',
                    'referencias',
                ),
            ),
            'manifestacoes-culturais' => array(
                'lead' => 'Eventos, rodas, participações e celebrações que mantêm a prática viva.',
                'page_slugs' => array(
                    'eventos',
                    'abril-pra-angola-2017',
                    'seminario-capoeira-e-cidadania-2014',
                    'encontro-usp-e-escola',
                    'participacoes',
                    'viraamorim',
                ),
            ),
        );
    }

    /**
     * Subcategory blueprints used to group migrated texts on the page templates.
     */
    function cedoc_get_subcategory_blueprints() {
        return array(
            'ceaca-memoria-e-oralidade-ceaca' => array(
                'lead' => 'Memória institucional e trajetória oral do CEACA.',
                'page_slugs' => array( 'institucional', 'historico', 'minha-historia' ),
            ),
            'ceaca-mestre-alcides' => array(
                'lead' => 'O fundador, sua biografia e os desdobramentos do mestre.',
                'page_slugs' => array( 'mestre-alcides-de-lima', 'mestres', 'historico' ),
            ),
            'ceaca-formacao-de-aprendizes-ceaca' => array(
                'lead' => 'Formação, equipe e processos de aprendizado.',
                'page_slugs' => array( 'equipe', 'oficinas', 'oficina-wordpress' ),
            ),
            'ceaca-eventos-e-batizados-ceaca' => array(
                'lead' => 'Eventos, batizados e celebrações do CEACA.',
                'page_slugs' => array( 'eventos', 'abril-pra-angola-2017', 'viraamorim' ),
            ),
            'ceaca-materiais-didaticos-ceaca' => array(
                'lead' => 'Ferramentas de ensino, vídeos e material de apoio.',
                'page_slugs' => array( 'educacao-e-publicacoes', 'oficina-audiovisual', 'oficina-wordpress' ),
            ),
            'educacao-e-cultura-tradicional-articulacao-educacao' => array(
                'lead' => 'Ações pedagógicas e articulação com escolas e projetos.',
                'page_slugs' => array( 'educacao-e-publicacoes', 'oficinas', 'capoeira-na-usp' ),
            ),
            'educacao-e-cultura-tradicional-documentacao-de-saberes-educacao' => array(
                'lead' => 'Pesquisa, memória e documentação aplicada à educação.',
                'page_slugs' => array( 'memorias-e-projetos', 'referencias', 'infoteca' ),
            ),
            'educacao-e-cultura-tradicional-manifestacoes-educacao' => array(
                'lead' => 'Relação entre educação, festa, corpo e prática cultural.',
                'page_slugs' => array( 'eventos', 'participacoes', 'viraamorim' ),
            ),
            'educacao-e-cultura-tradicional-politicas-publicas' => array(
                'lead' => 'Leis, direitos e marcos de política pública.',
                'page_slugs' => array( 'leis', 'lei-cultura-viva', 'lei-grio', 'lei-no-10-639-2003' ),
            ),
            'articulacao-acao-grio' => array(
                'lead' => 'Parcerias, redes e caminhos da ação griô.',
                'page_slugs' => array( 'colabore', 'redes', 'imprensa' ),
            ),
            'articulacao-forum-das-culturas' => array(
                'lead' => 'Diálogos e participação em fóruns culturais.',
                'page_slugs' => array( 'eventos', 'redes', 'links' ),
            ),
            'articulacao-parcerias-institucionais' => array(
                'lead' => 'Instituições, cooperação e articulação ampliada.',
                'page_slugs' => array( 'colabore', 'imprensa', 'redes' ),
            ),
            'articulacao-redes-de-cultura' => array(
                'lead' => 'Redes de cultura, referências e conexões externas.',
                'page_slugs' => array( 'redes', 'links', 'referencias' ),
            ),
            'documentacao-de-saberes-teses-dissertacoes-artigos' => array(
                'lead' => 'Produção acadêmica e referências para consulta.',
                'page_slugs' => array( 'referencias', 'livros', 'educacao-e-publicacoes' ),
            ),
            'documentacao-de-saberes-projetos-pesquisa-cultural' => array(
                'lead' => 'Projetos, pesquisa e desdobramentos culturais.',
                'page_slugs' => array( 'memorias-e-projetos', 'capoeira-coco-e-ciranda-na-escola', 'cultura-e-saude' ),
            ),
            'documentacao-de-saberes-livros-documentacao' => array(
                'lead' => 'Livros, publicações e materiais de base documental.',
                'page_slugs' => array( 'livros', 'educacao-e-publicacoes', 'capoeira-na-usp' ),
            ),
            'documentacao-de-saberes-pesquisas-campo' => array(
                'lead' => 'Pesquisa de campo, entrevistas e registros de memória.',
                'page_slugs' => array( 'memorias-e-projetos', 'referencias', 'cultura-e-saude' ),
            ),
            'documentacao-de-saberes-musicalidade-instrumentos' => array(
                'lead' => 'Som, imagem e instrumentos como parte da documentação.',
                'page_slugs' => array( 'infoteca', 'audiovisuais', 'imagens' ),
            ),
            'manifestacoes-culturais-dancas-corporalidades' => array(
                'lead' => 'Corpo, dança e expressão em movimento.',
                'page_slugs' => array( 'eventos', 'participacoes', 'viraamorim' ),
            ),
            'manifestacoes-culturais-festas-cortejos-rituais' => array(
                'lead' => 'Festas, cortejos e rituais que formam a cena do CEACA.',
                'page_slugs' => array( 'eventos', 'abril-pra-angola-2017', 'seminario-capoeira-e-cidadania-2014' ),
            ),
            'manifestacoes-culturais-rodas-apresentacoes' => array(
                'lead' => 'Rodas, apresentações e circulação pública da capoeira.',
                'page_slugs' => array( 'eventos', 'encontro-usp-e-escola', 'participacoes' ),
            ),
            'manifestacoes-culturais-rodas-capoeira' => array(
                'lead' => 'Rodas de capoeira e convivência comunitária.',
                'page_slugs' => array( 'eventos', 'viraamorim', 'abril-pra-angola-2017' ),
            ),
        );
    }

    /**
     * Render a compact set of migrated-text cards for a list of page slugs.
     */
    function cedoc_render_page_story_cards(array $page_slugs, string $label = 'Texto migrado'): string {
        $cards = array();

        foreach ($page_slugs as $slug) {
            $snapshot = cedoc_get_page_snapshot_by_slug((string) $slug);
            if (empty($snapshot)) {
                continue;
            }

            $cards[] = $snapshot;
        }

        if (empty($cards)) {
            return '';
        }

        $html = '<div class="cedoc-story-grid">';

        foreach ($cards as $card) {
            $html .= '<a class="cedoc-story-card" href="' . esc_url($card['url']) . '">'
                . '<span class="cedoc-story-card__label">' . esc_html($label) . '</span>'
                . '<strong>' . esc_html($card['title']) . '</strong>'
                . '<p>' . esc_html($card['excerpt']) . '</p>'
                . '<span class="cedoc-story-card__action">Abrir página</span>'
                . '</a>';
        }

        $html .= '</div>';

        return $html;
    }

/**
 * Map between category slug and subcategory search patterns
 */
function cedoc_get_category_subcategory_mapping() {
    return array(
        'ceaca' => array(
            'pattern' => 'subcategoria-ceaca-',
            'subcategories' => array(
                'Eventos e batizados CEACA',
                'Formacao de Aprendizes CEACA',
                'Materiais didaticos CEACA',
                'Memoria e oralidade CEACA',
                'Mestre Alcides',
            ),
        ),
        'educacao-cultura' => array(
            'pattern' => 'subcategoria-educacao-e-cultura-tradicional-',
            'subcategories' => array(
                'Articulacao Educacao',
                'Documentacao de saberes Educacao',
                'Manifestacoes Educacao',
                'Politicas Publicas',
            ),
        ),
        'articulacao' => array(
            'pattern' => 'subcategoria-articulacao-',
            'subcategories' => array(
                'Acao Grio',
                'Forum das Culturas',
                'Parcerias institucionais',
                'Redes de cultura',
            ),
        ),
        'documentacao-saberes' => array(
            'pattern' => 'subcategoria-documentacao-de-saberes-',
            'subcategories' => array(
                'Livros Documentacao',
                'Musicalidade instrumentos',
                'Pesquisas campo',
                'Projetos pesquisa cultural',
            ),
        ),
        'manifestacoes-culturais' => array(
            'pattern' => 'subcategoria-manifestacoes-culturais-',
            'subcategories' => array(
                'Dancas corporalidades',
                'Festas cortejos rituais',
                'Rodas apresentacoes',
                'Rodas capoeira',
            ),
        ),
    );
}

/**
 * Get subcategories by category slug
 */
function cedoc_get_subcategories_by_category($category_slug) {
    global $wpdb;
    
    $mapping = cedoc_get_category_subcategory_mapping();
    
    if (!isset($mapping[$category_slug])) {
        return array();
    }
    
    $pattern = $mapping[$category_slug]['pattern'];
    
    // Query pages that match the pattern
    $query = new WP_Query(array(
        'post_type' => 'page',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        's' => '',
        'orderby' => 'title',
        'order' => 'ASC',
    ));
    
    $subcategories = array();
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_name = get_post_field('post_name');
            
            // Check if this page matches the category pattern
            if (strpos($post_name, $pattern) === 0) {
                $subcategories[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'content' => get_the_excerpt(),
                    'slug' => $post_name,
                );
            }
        }
    }
    
    wp_reset_postdata();
    
    // Also include pages that have explicit meta 'cedoc_category' set to this category
    $meta_pages = get_posts(array(
        'post_type' => 'page',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_key' => 'cedoc_category',
        'meta_value' => $category_slug,
    ));

    if (!empty($meta_pages)) {
        foreach ($meta_pages as $mp) {
            $already = false;
            foreach ($subcategories as $sc) {
                if (isset($sc['id']) && $sc['id'] == $mp->ID) {
                    $already = true;
                    break;
                }
            }

            if ($already) {
                continue;
            }

            $subcategories[] = array(
                'id' => $mp->ID,
                'title' => get_the_title($mp->ID),
                'content' => wp_trim_words( wp_strip_all_tags( get_post_field('post_content', $mp->ID) ), 24, '...' ),
                'slug' => get_post_field('post_name', $mp->ID),
            );
        }
    }

    return $subcategories;
}

/**
 * Get a random real image from the CEDOC collection.
 */
function cedoc_get_post_primary_image_url( $post_id, $size = 'large' ) {
    $post_id = (int) $post_id;

    if ( $post_id <= 0 ) {
        return false;
    }

    $thumbnail_id = get_post_thumbnail_id( $post_id );
    if ( $thumbnail_id ) {
        $image = wp_get_attachment_image_src( $thumbnail_id, $size );
        if ( isset( $image[0] ) && $image[0] ) {
            return $image[0];
        }
    }

    $attachments = get_attached_media( 'image', $post_id );
    if ( ! empty( $attachments ) ) {
        foreach ( $attachments as $attachment ) {
            $image = wp_get_attachment_image_src( $attachment->ID, $size );
            if ( isset( $image[0] ) && $image[0] ) {
                return $image[0];
            }
        }
    }

    $content = (string) get_post_field( 'post_content', $post_id );
    if ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $matches ) ) {
        return esc_url_raw( $matches[1] );
    }

    return false;
}

function cedoc_get_random_collection_image( $size = 'large', $attempts = 12 ) {
    $collection_id = cedoc_get_cedoc_collection_id();

    if ( $collection_id ) {
        $item_post_type = 'tnc_col_' . $collection_id . '_item';

        $query = new WP_Query( array(
            'post_type'      => $item_post_type,
            'posts_per_page' => max( 1, (int) $attempts ),
            'post_status'    => 'publish',
            'orderby'        => 'rand',
            'no_found_rows'  => true,
        ) );

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $image_url = cedoc_get_post_primary_image_url( get_the_ID(), $size );

                if ( $image_url ) {
                    wp_reset_postdata();
                    return $image_url;
                }
            }

            wp_reset_postdata();
        }
    }

    // Fallback: try the media library for any random image
    $attach_query = new WP_Query( array(
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'posts_per_page' => 1,
        'orderby' => 'rand',
        'post_status' => 'inherit',
    ) );

    if ( $attach_query->have_posts() ) {
        $attach_query->the_post();
        $image = wp_get_attachment_image_src( get_the_ID(), $size );
        wp_reset_postdata();
        return isset( $image[0] ) ? $image[0] : false;
    }

    return false;
}

/**
 * Get a random image from items in a category.
 */
function cedoc_get_random_category_image( $category_slug = '' ) {
    if ( ! empty( $category_slug ) ) {
        $category_items = cedoc_get_items_by_category( (string) $category_slug, 40 );

        if ( ! empty( $category_items ) && is_array( $category_items ) ) {
            shuffle( $category_items );

            foreach ( $category_items as $item ) {
                if ( ! isset( $item->ID ) ) {
                    continue;
                }

                $image_url = cedoc_get_post_primary_image_url( (int) $item->ID, 'large' );
                if ( $image_url ) {
                    return $image_url;
                }
            }
        }
    }

    $image = cedoc_get_random_collection_image( 'large', 16 );
    if ( $image ) {
        return $image;
    }

    $fallback = cedoc_get_random_collection_image( 'medium_large', 24 );
    return $fallback ? $fallback : get_theme_file_uri( '/assets/images/section_default.png' );
}

/**
 * Get a random item image from the collection.
 */
function cedoc_get_random_item_image() {
    $image = cedoc_get_random_collection_image( 'medium_large', 16 );
    if ( $image ) {
        return $image;
    }

    $fallback = cedoc_get_random_collection_image( 'large', 24 );
    return $fallback ? $fallback : get_theme_file_uri( '/assets/images/section_default.png' );
}

/**
 * Get page ID by slug
 */
function cedoc_get_page_by_slug($slug) {
    $page = get_page_by_path($slug, OBJECT, 'page');
    return $page ? $page->ID : false;
}

/**
 * Get CEDOC Tainacan collection ID
 */
function cedoc_get_cedoc_collection_id() {
    // Allow setting via option first
    $opt = get_option('cedoc_collection_id');
    if ( $opt && is_numeric( $opt ) ) {
        return (int) $opt;
    }

    // Try to find collections and prefer ones with common slugs/titles
    $collections = get_posts( array(
        'post_type' => 'tainacan-collection',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ) );

    if ( ! empty( $collections ) ) {
        foreach ( $collections as $c ) {
            $slug = sanitize_title( $c->post_name );
            $title = sanitize_title( $c->post_title );
            if ( in_array( $slug, array( 'acervo-cedoc', 'catalogo', 'catálogo', 'cedoc', 'catalog' ), true ) || in_array( $title, array( 'acervo-cedoc', 'acervo cedoc', 'catalogo', 'catálogo', 'cedoc', 'catalog' ), true ) ) {
                return $c->ID;
            }
        }

        // fallback to first found collection
        return $collections[0]->ID;
    }

    return false;
}

/**
 * Get item thumbnail with fallback
 */
function cedoc_get_item_thumbnail($post_id, $size = 'medium_large') {
    $image_url = cedoc_get_post_primary_image_url( $post_id, $size );

    if ( $image_url ) {
        return $image_url;
    }

    $fallback = cedoc_get_random_collection_image( $size, 12 );
    return $fallback ? $fallback : get_theme_file_uri( '/assets/images/section_default.png' );
}

/**
 * Collect unique values for a Tainacan metadata field across the CEDOC items.
 */
function cedoc_get_gallery_meta_values( $meta_key, $limit = 24 ) {
    $collection_id = cedoc_get_cedoc_collection_id();

    if ( ! $collection_id ) {
        return array();
    }

    $item_post_type = 'tnc_col_' . $collection_id . '_item';
    $query = new WP_Query( array(
        'post_type'      => $item_post_type,
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ) );

    $values = array();

    foreach ( $query->posts as $post_id ) {
        $value = trim( (string) get_post_meta( (int) $post_id, (string) $meta_key, true ) );
        if ( $value === '' ) {
            continue;
        }

        $values[ sanitize_title( $value ) ] = $value;
    }

    wp_reset_postdata();

    $values = array_values( $values );
    sort( $values, SORT_NATURAL | SORT_FLAG_CASE );

    if ( $limit > 0 ) {
        $values = array_slice( $values, 0, $limit );
    }

    return $values;
}

/**
 * Build a meta query fragment for gallery filters.
 */
function cedoc_build_gallery_meta_query( array $filters ) {
    $meta_query = array( 'relation' => 'AND' );

    $meta_map = array(
        'doc_type'    => array( '66' ), // Espécie documental
        'support'     => array( '69' ), // Suporte
        'event'       => array( '58' ), // Evento
        'author'      => array( '77' ), // Autoria
        'keyword'     => array( '83' ), // Palavras-chave
        'condition'   => array( '85' ), // Estado de conservação
        'reference'   => array( '86' ), // Referências
    );

    foreach ( $meta_map as $filter_key => $meta_keys ) {
        $value = isset( $filters[ $filter_key ] ) ? trim( (string) $filters[ $filter_key ] ) : '';

        if ( $value === '' ) {
            continue;
        }

        $value_slug = sanitize_title( $value );
        $group = array( 'relation' => 'OR' );

        foreach ( $meta_keys as $meta_key ) {
            $group[] = array(
                'key'     => $meta_key,
                'value'   => $value,
                'compare' => 'LIKE',
            );

            if ( $value_slug !== '' && $value_slug !== $value ) {
                $group[] = array(
                    'key'     => $meta_key,
                    'value'   => $value_slug,
                    'compare' => 'LIKE',
                );
            }
        }

        $meta_query[] = $group;
    }

    if ( isset( $filters['year'] ) ) {
        $year = preg_replace( '/[^0-9]/', '', (string) $filters['year'] );
        if ( strlen( $year ) === 4 ) {
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key'     => '72',
                    'value'   => $year,
                    'compare' => 'LIKE',
                ),
                array(
                    'key'     => '72',
                    'value'   => $year,
                    'compare' => 'LIKE',
                ),
            );
        }
    }

    return count( $meta_query ) > 1 ? $meta_query : array();
}

    /**
     * Build a compact synopsis for an item using its title, description and date.
     */
    function cedoc_get_item_synopsis($post_id, $words = 24) {
        $post_id = (int) $post_id;
        $title = trim((string) get_the_title($post_id));
        $description = trim(wp_strip_all_tags((string) get_post_field('post_content', $post_id)));

        if ($description === '') {
            $description = trim(wp_strip_all_tags((string) get_the_excerpt($post_id)));
        }

        $description = preg_replace('/\s+/', ' ', $description);
        $description = wp_trim_words($description, max(8, (int) $words), '...');

        $date = get_the_date('d/m/Y', $post_id);
        $parts = array_filter(array(
            $title,
            $description,
            $date ? 'Data: ' . $date : '',
        ));

        return implode(' - ', $parts);
    }

/**
 * Check if current page is a subcategory page
 */
function cedoc_is_subcategory_page() {
    if (is_page()) {
        $page_slug = get_page_template_slug(get_the_ID());
        $post_name = get_post_field('post_name', get_the_ID());
        return strpos($post_name, 'subcategoria-') === 0;
    }
    return false;
}

/**
 * Get current subcategory slug from page post_name
 */
function cedoc_get_current_subcategory_slug() {
    if (!is_page()) {
        return false;
    }
    
    $post_name = get_post_field('post_name', get_the_ID());
    
    if (strpos($post_name, 'subcategoria-') === 0) {
        // Extract the slug after 'subcategoria-'
        $slug = substr($post_name, 13); // len('subcategoria-') = 13
        return $slug;
    }
    
    return false;
}

/**
 * Get category name from subcategory slug
 */
function cedoc_get_category_from_subcategory_slug($subcategory_slug) {
    if (!$subcategory_slug) {
        return false;
    }

    $mapping = cedoc_get_category_subcategory_mapping();

    foreach ($mapping as $category_slug => $data) {
        $pattern = isset($data['pattern']) ? $data['pattern'] : '';
        if ($pattern && strpos('subcategoria-' . $subcategory_slug, $pattern) === 0) {
            return $category_slug;
        }
    }

    return false;
}

/**
 * Remove duplicated items from a WP_Query result using the base slug.
 */
function cedoc_unique_items_from_query($posts) {
    $unique_posts = array();
    $seen_keys = array();

    foreach ((array) $posts as $post) {
        if (!($post instanceof WP_Post)) {
            continue;
        }

        $slug = get_post_field('post_name', $post->ID);
        $base_slug = preg_replace('/-\d+$/', '', (string) $slug);
        $title_key = sanitize_title(get_the_title($post->ID));
        $dedupe_key = $base_slug ? $base_slug : ($title_key ? $title_key : (string) $post->ID);

        if (isset($seen_keys[$dedupe_key])) {
            continue;
        }

        $seen_keys[$dedupe_key] = true;
        $unique_posts[] = $post;
    }

    return $unique_posts;
}

/**
 * Filter items by a search term across title, excerpt, content and slug.
 */
function cedoc_filter_items_by_search($posts, $search = '') {
    $search = trim((string) $search);

    if ($search === '') {
        return $posts;
    }

    $needle = function_exists('mb_strtolower') ? mb_strtolower($search) : strtolower($search);
    $filtered = array();

    foreach ((array) $posts as $post) {
        if (!($post instanceof WP_Post)) {
            continue;
        }

        $haystack = implode(' ', array(
            get_the_title($post->ID),
            get_the_excerpt($post->ID),
            $post->post_content,
            $post->post_name,
        ));

        $haystack = function_exists('mb_strtolower') ? mb_strtolower($haystack) : strtolower($haystack);

        if (strpos($haystack, $needle) !== false) {
            $filtered[] = $post;
        }
    }

    return $filtered;
}

/**
 * Get Tainacan items filtered by subcategory
 * Searches items by matching the subcategory name in item titles/metadata
 */
function cedoc_get_items_by_subcategory($subcategory_slug = '', $posts_per_page = -1, $search = '') {
    // Get Tainacan collection ID
    $collection_id = cedoc_get_cedoc_collection_id();
    if (!$collection_id) {
        return array();
    }
    
    // If no slug provided, try to get from current page
    if (!$subcategory_slug) {
        $subcategory_slug = cedoc_get_current_subcategory_slug();
    }
    
    if (!$subcategory_slug) {
        return array();
    }
    
    $item_post_type = 'tnc_col_' . $collection_id . '_item';

    $query = new WP_Query(array(
        'post_type' => $item_post_type,
        'posts_per_page' => $posts_per_page,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
    ));

    $items = array();
    $fallback_items = array();
    $normalized_subcategory = sanitize_title( $subcategory_slug );
    $normalized_category = sanitize_title( (string) cedoc_get_category_from_subcategory_slug( $subcategory_slug ) );

    foreach ( $query->posts as $item ) {
        $fallback_items[] = $item;

        $item_subcategory = sanitize_title( (string) get_post_meta( $item->ID, 'cedoc_subcategory', true ) );
        $item_category = sanitize_title( (string) get_post_meta( $item->ID, 'cedoc_category', true ) );

        if ( ( $normalized_subcategory && $item_subcategory === $normalized_subcategory ) || ( $normalized_category && $item_category === $normalized_category ) ) {
            $items[] = $item;
        }
    }

    if ( empty( $items ) ) {
        $items = $fallback_items;
    }

    $items = cedoc_unique_items_from_query($items);
    $items = cedoc_filter_items_by_search($items, $search);

    return $items;
}

/**
 * Get items filtered by category for gallery view
 * Returns items related to a specific category (e.g., CEACA)
 */
function cedoc_get_items_by_category($category_slug = '', $posts_per_page = 20) {
    // Get Tainacan collection ID
    $collection_id = cedoc_get_cedoc_collection_id();
    if (!$collection_id) {
        return array();
    }
    
    if (!$category_slug) {
        return array();
    }
    
    $item_post_type = 'tnc_col_' . $collection_id . '_item';
    
    $query = new WP_Query(array(
        'post_type' => $item_post_type,
        'posts_per_page' => $posts_per_page,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
    ));

    $items = array();
    $fallback_items = array();
    $normalized_category = sanitize_title( $category_slug );

    foreach ( $query->posts as $item ) {
        $fallback_items[] = $item;

        $item_category = sanitize_title( (string) get_post_meta( $item->ID, 'cedoc_category', true ) );
        if ( $item_category && $item_category === $normalized_category ) {
            $items[] = $item;
        }
    }

    if ( empty( $items ) ) {
        $items = $fallback_items;
    }

    return cedoc_unique_items_from_query( $items );
}

/**
 * Get a paginated query for the CEDOC gallery.
 */
function cedoc_get_cedoc_gallery_items($posts_per_page = 24, $paged = 1, $search = '', $filters = array()) {
    $collection_id = cedoc_get_cedoc_collection_id();

    if (!$collection_id) {
        return new WP_Query(array('post__in' => array(0)));
    }

    $item_post_type = 'tnc_col_' . $collection_id . '_item';

    $query_args = array(
        'post_type' => $item_post_type,
        'posts_per_page' => $posts_per_page,
        'paged' => max(1, (int) $paged),
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
    );

    if ( trim( (string) $search ) !== '' ) {
        $query_args['s'] = $search;
    }

    if ( isset( $filters['year'] ) && preg_match( '/^[0-9]{4}$/', (string) $filters['year'] ) ) {
        $query_args['date_query'] = array(
            array(
                'year' => (int) $filters['year'],
            ),
        );
    }

    $meta_query = cedoc_build_gallery_meta_query( $filters );
    if ( ! empty( $meta_query ) ) {
        $query_args['meta_query'] = $meta_query;
    }

    $query = new WP_Query( $query_args );

    $query->posts = cedoc_unique_items_from_query($query->posts);
    $query->posts = cedoc_filter_items_by_search($query->posts, $search);

    if ( ! empty( $filters ) ) {
        $filtered_posts = array();

        foreach ( $query->posts as $post ) {
            if ( ! ( $post instanceof WP_Post ) ) {
                continue;
            }

            $post_id = (int) $post->ID;
            $matches = true;

            foreach ( array( 'doc_type' => '66', 'support' => '69', 'event' => '58', 'author' => '77', 'keyword' => '83', 'condition' => '85', 'reference' => '86' ) as $filter_key => $meta_key ) {
                $filter_value = isset( $filters[ $filter_key ] ) ? trim( (string) $filters[ $filter_key ] ) : '';
                if ( $filter_value === '' ) {
                    continue;
                }

                $meta_value = sanitize_title( (string) get_post_meta( $post_id, $meta_key, true ) );
                $filter_slug = sanitize_title( $filter_value );

                if ( $meta_value === '' || ( $meta_value !== $filter_slug && strpos( $meta_value, $filter_slug ) === false && strpos( $filter_slug, $meta_value ) === false ) ) {
                    $matches = false;
                    break;
                }
            }

            if ( $matches && isset( $filters['year'] ) ) {
                $year = preg_replace( '/[^0-9]/', '', (string) $filters['year'] );
                if ( strlen( $year ) === 4 ) {
                    $post_year = get_post_time( 'Y', false, $post_id );
                    $meta_year = preg_replace( '/[^0-9]/', '', (string) get_post_meta( $post_id, '72', true ) );
                    $date_meta = (string) get_post_meta( $post_id, '72', true );
                    $date_match = ( $post_year === $year ) || ( $meta_year === $year ) || ( strpos( $date_meta, $year ) !== false );

                    if ( ! $date_match ) {
                        $matches = false;
                    }
                }
            }

            if ( $matches ) {
                $filtered_posts[] = $post;
            }
        }

        $query->posts = $filtered_posts;
    }

    $query->post_count = count($query->posts);

    return $query;
}

/**
 * Get random item image with marker for demonstration
 * Returns array with 'url' and 'is_random' indicator
 */
function cedoc_get_random_item_image_with_marker() {
    $args = array(
        'post_type' => array('attachment'),
        'post_mime_type' => 'image',
        'posts_per_page' => 1,
        'orderby' => 'rand',
        'post_status' => 'inherit',
    );
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        $query->the_post();
        $attachment_id = get_the_ID();
        $image = wp_get_attachment_image_src($attachment_id, 'medium_large');
        wp_reset_postdata();
        
        return array(
            'url' => isset($image[0]) ? $image[0] : false,
            'is_random' => true,
            'id' => $attachment_id,
        );
    }
    
    wp_reset_postdata();
    return array(
        'url' => false,
        'is_random' => false,
        'id' => 0,
    );
}

/**
 * Wrap image with random indicator marker (orange dot)
 */
function cedoc_render_image_with_random_marker($image_url, $is_random = false, $alt_text = '') {
    if (!$image_url) {
        return '';
    }
    
    $html = '<div class="cedoc-image-wrapper" style="position: relative; display: inline-block; width: 100%;">';
    $html .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($alt_text) . '" class="cedoc-image" style="width: 100%; height: auto; display: block;">';
    
    if ($is_random) {
        $html .= '<div class="cedoc-random-marker" title="Imagem de demonstração - selecionada aleatoriamente" style="position: absolute; top: 10px; right: 10px; width: 16px; height: 16px; background-color: #FF8C00; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.3); cursor: help;"></div>';
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * Render the migrated-text hub for the homepage layouts.
 */
function cedoc_render_category_story_hub(array $categories, array $blueprints): string {
    $html = '<div class="cedoc-story-hub">';

    foreach ( $categories as $category ) {
        $blueprint = $blueprints[ $category['slug'] ] ?? array();
        $page_slugs = $blueprint['page_slugs'] ?? array();
        $lead = $blueprint['lead'] ?? $category['description'];

        if ( empty( $page_slugs ) ) {
            continue;
        }

        $html .= '<section class="cedoc-story-section" id="cedoc-story-' . esc_attr( $category['slug'] ) . '">'
            . '<div class="cedoc-story-section-header">'
            . '<div>'
            . '<span class="cedoc-layout-label">' . esc_html( $category['name'] ) . '</span>'
            . '<h3>' . esc_html( $category['name'] ) . '</h3>'
            . '<p>' . esc_html( $lead ) . '</p>'
            . '</div>'
            . '<a class="btn btn-sm btn-outline-primary" href="#cedoc-category-' . esc_attr( $category['slug'] ) . '">Ver categoria</a>'
            . '</div>'
            . cedoc_render_page_story_cards( $page_slugs, 'Texto migrado do ceaca.wordpress' )
            . '</section>';
    }

    $html .= '</div>';

    return $html;
}
