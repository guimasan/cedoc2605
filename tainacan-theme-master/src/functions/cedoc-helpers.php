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
 * Get a random image from items in a category
 */
function cedoc_get_random_category_image($category_slug = '') {
    // Get Tainacan collection ID for CEDOC
    $collection_id = cedoc_get_cedoc_collection_id();
    
    if (!$collection_id) {
        return false;
    }
    
    // Correct Tainacan post type format: tnc_col_{id}_item
    $item_post_type = 'tnc_col_' . $collection_id . '_item';
    
    $query = new WP_Query(array(
        'post_type' => $item_post_type,
        'posts_per_page' => 1,
        'post_status' => 'publish',
        'orderby' => 'rand',
    ));
    
    if ($query->have_posts()) {
        $query->the_post();
        $thumbnail_id = get_post_thumbnail_id();
        
        if ($thumbnail_id) {
            $image = wp_get_attachment_image_src($thumbnail_id, 'large');
            wp_reset_postdata();
            return isset($image[0]) ? $image[0] : false;
        }
        
        wp_reset_postdata();
    }
    
    return false;
}

/**
 * Get a random item image from the collection
 */
function cedoc_get_random_item_image() {
    // Get Tainacan collection ID for CEDOC
    $collection_id = cedoc_get_cedoc_collection_id();
    
    if (!$collection_id) {
        return false;
    }
    
    // Correct Tainacan post type format: tnc_col_{id}_item
    $item_post_type = 'tnc_col_' . $collection_id . '_item';
    
    // Get random published item with thumbnail
    $query = new WP_Query(array(
        'post_type' => $item_post_type,
        'posts_per_page' => 1,
        'post_status' => 'publish',
        'orderby' => 'rand',
    ));
    
    if ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        $thumbnail_id = get_post_thumbnail_id($post_id);
        
        if ($thumbnail_id) {
            $image = wp_get_attachment_image_src($thumbnail_id, 'medium_large');
            wp_reset_postdata();
            return isset($image[0]) ? $image[0] : false;
        }
        
        wp_reset_postdata();
    }
    
    return false;
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
    // Check if there's a collection named "Catálogo" or similar
    $collections = get_posts(array(
        'post_type' => 'tainacan-collection',
        'posts_per_page' => 1,
        'post_status' => 'publish',
    ));
    
    if (!empty($collections)) {
        return $collections[0]->ID;
    }
    
    // Default to collection ID 42 based on the context
    return 42;
}

/**
 * Get item thumbnail with fallback
 */
function cedoc_get_item_thumbnail($post_id, $size = 'medium_large') {
    $thumbnail_id = get_post_thumbnail_id($post_id);
    
    if ($thumbnail_id) {
        $image = wp_get_attachment_image_src($thumbnail_id, $size);
        return isset($image[0]) ? $image[0] : '';
    }
    
    return '';
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
    
    // Query items (all for now, then filter in PHP for the current subcategory view)
    $query = new WP_Query(array(
        'post_type' => $item_post_type,
        'posts_per_page' => $posts_per_page,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
    ));

    $items = cedoc_unique_items_from_query($query->posts);
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
    
    // Get subcategories for this category
    $mapping = cedoc_get_category_subcategory_mapping();
    $subcategories = isset($mapping[$category_slug]) ? $mapping[$category_slug]['subcategories'] : array();
    
    if (empty($subcategories)) {
        return array();
    }
    
    // Query items - fetch all published items
    $query = new WP_Query(array(
        'post_type' => $item_post_type,
        'posts_per_page' => $posts_per_page,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
    ));
    
    return $query->posts;
}

/**
 * Get a paginated query for the CEDOC gallery.
 */
function cedoc_get_cedoc_gallery_items($posts_per_page = 24, $paged = 1, $search = '') {
    $collection_id = cedoc_get_cedoc_collection_id();

    if (!$collection_id) {
        return new WP_Query(array('post__in' => array(0)));
    }

    $item_post_type = 'tnc_col_' . $collection_id . '_item';

    $query = new WP_Query(array(
        'post_type' => $item_post_type,
        'posts_per_page' => $posts_per_page,
        'paged' => max(1, (int) $paged),
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
    ));

    $query->posts = cedoc_unique_items_from_query($query->posts);
    $query->posts = cedoc_filter_items_by_search($query->posts, $search);
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
