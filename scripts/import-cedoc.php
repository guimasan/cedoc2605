<?php

declare(strict_types=1);

require '/var/www/html/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

function cedoc_slug(string $value): string {
    $slug = sanitize_title($value);
    return $slug !== '' ? $slug : 'cedoc-item';
}

function cedoc_first_admin_id(): int {
    $admins = get_users([
        'role' => 'administrator',
        'number' => 1,
        'fields' => 'ID',
    ]);

    if (!empty($admins)) {
        return (int) $admins[0];
    }

    return 1;
}

function cedoc_find_post_by_slug(string $slug, string $post_type): ?WP_Post {
    $post = get_page_by_path($slug, OBJECT, $post_type);
    return $post instanceof WP_Post ? $post : null;
}

function cedoc_publish_post(int $post_id): void {
    wp_update_post([
        'ID' => $post_id,
        'post_status' => 'publish',
    ]);
}

function cedoc_ensure_page(string $slug, string $title, string $content, int $author_id, int $parent = 0): int {
    $existing = cedoc_find_post_by_slug($slug, 'page');

    $payload = [
        'post_title' => $title,
        'post_name' => $slug,
        'post_content' => $content,
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_author' => $author_id,
        'post_parent' => $parent,
    ];

    if ($existing) {
        $payload['ID'] = $existing->ID;
        $page_id = wp_update_post($payload, true);
    } else {
        $page_id = wp_insert_post($payload, true);
    }

    if (is_wp_error($page_id)) {
        throw new RuntimeException('Failed to save page ' . $slug . ': ' . $page_id->get_error_message());
    }

    return (int) $page_id;
}

function cedoc_ensure_collection(string $slug, string $name, string $description, int $author_id): Tainacan\Entities\Collection {
    $collections = Tainacan\Repositories\Collections::get_instance();
    $existing = cedoc_find_post_by_slug($slug, Tainacan\Entities\Collection::get_post_type());

    if ($existing) {
        $collection = new Tainacan\Entities\Collection($existing);
        wp_update_post([
            'ID' => $collection->get_id(),
            'post_status' => 'publish',
            'post_author' => $author_id,
        ]);

        return $collection;
    }

    $collection = new Tainacan\Entities\Collection();
    $collection->set_name($name);
    $collection->set_slug($slug);
    $collection->set_description($description);

    $collection = $collections->insert($collection);
    wp_update_post([
        'ID' => $collection->get_id(),
        'post_status' => 'publish',
        'post_author' => $author_id,
    ]);

    return $collection;
}

function cedoc_ensure_taxonomy(string $slug, string $name, string $description, int $author_id, array $terms_tree = []): int {
    $existing = get_page_by_path($slug, OBJECT, 'tainacan-taxonomy');
    
    if ($existing) {
        $taxonomy_id = (int) $existing->ID;
    } else {
        $taxonomy_id = wp_insert_post([
            'post_type' => 'tainacan-taxonomy',
            'post_title' => $name,
            'post_name' => $slug,
            'post_content' => $description,
            'post_status' => 'publish',
            'post_author' => $author_id,
        ]);
        
        if (is_wp_error($taxonomy_id)) {
            throw new RuntimeException('Failed to create taxonomy: ' . $taxonomy_id->get_error_message());
        }
        $taxonomy_id = (int) $taxonomy_id;
    }

    // Insert/update terms with hierarchy
    if (!empty($terms_tree)) {
        cedoc_insert_taxonomy_terms($taxonomy_id, $terms_tree, 0, $author_id);
    }

    return $taxonomy_id;
}

function cedoc_insert_taxonomy_terms(int $taxonomy_id, array $terms, int $parent_term_id = 0, int $author_id = 1): array {
    $term_ids = [];
    
    foreach ($terms as $term_data) {
        $term_name = $term_data['name'] ?? '';
        $term_desc = $term_data['description'] ?? '';
        $term_slug = cedoc_slug($term_name);
        $children = $term_data['children'] ?? [];
        
        // Check if term exists by slug; this is sufficient for the demonstrator taxonomy.
        $existing_term = get_page_by_path($term_slug, OBJECT, 'tainacan-term');

        if ($existing_term) {
            $term_id = (int) $existing_term->ID;
        } else {
            $term_id = wp_insert_post([
                'post_type' => 'tainacan-term',
                'post_title' => $term_name,
                'post_name' => $term_slug,
                'post_content' => $term_desc,
                'post_parent' => $parent_term_id,
                'post_status' => 'publish',
                'post_author' => $author_id,
                'meta_input' => [
                    'taxonomy_id' => $taxonomy_id,
                ],
            ]);
            
            if (is_wp_error($term_id)) {
                throw new RuntimeException('Failed to create term: ' . $term_id->get_error_message());
            }
            $term_id = (int) $term_id;
        }
        
        $term_ids[$term_name] = $term_id;
        
        // Recursively add children
        if (!empty($children)) {
            cedoc_insert_taxonomy_terms($taxonomy_id, $children, $term_id, $author_id);
        }
    }
    
    return $term_ids;
}

function cedoc_download_remote_file(string $url): array {
    $temp_file = download_url($url, 60);

    if (is_wp_error($temp_file)) {
        throw new RuntimeException('Failed to download ' . $url . ': ' . $temp_file->get_error_message());
    }

    $path = wp_parse_url($url, PHP_URL_PATH) ?: '';
    $name = basename($path);

    return [
        'name' => $name !== '' ? $name : md5($url) . '.bin',
        'tmp_name' => $temp_file,
        'type' => mime_content_type($temp_file) ?: 'application/octet-stream',
    ];
}

function cedoc_ensure_attachment(string $source_url, int $author_id): int {
    $file = cedoc_download_remote_file($source_url);

    $attachment_id = media_handle_sideload($file, 0, '', [
        'post_author' => $author_id,
    ]);

    if (is_wp_error($attachment_id)) {
        if (!empty($file['tmp_name']) && file_exists($file['tmp_name'])) {
            @unlink($file['tmp_name']);
        }
        throw new RuntimeException('Failed to sideload ' . $source_url . ': ' . $attachment_id->get_error_message());
    }

    return (int) $attachment_id;
}

function cedoc_ensure_item(Tainacan\Entities\Collection $collection, array $item_data, int $author_id): int {
    $item_post_type = $collection->get_db_identifier();
    $slug = cedoc_slug($item_data['title']);
    $existing = cedoc_find_post_by_slug($slug, $item_post_type);

    if ($existing) {
        return (int) $existing->ID;
    }

    $attachment_id = cedoc_ensure_attachment($item_data['source_url'], $author_id);

    $item = new Tainacan\Entities\Item();
    $item->set_title($item_data['title']);
    $item->set_slug($slug);
    $item->set_description($item_data['description']);
    $item->set_document_type('attachment');
    $item->set_document($attachment_id);
    $item->set__thumbnail_id($attachment_id);
    $item->set_author_id($author_id);

    if (method_exists($item, 'set_collection')) {
        $item->set_collection($collection);
    }

    $items = Tainacan\Repositories\Items::get_instance();
    $item = $items->insert($item);
    cedoc_publish_post($item->get_id());

    return (int) $item->get_id();
}

function cedoc_catalog_content(string $collection_url): string {
    return '<h1>Acervo CEDOC</h1>'
        . '<p>Acervo de referencia do CEDOC, com itens importados automaticamente a partir de arquivos publicos disponiveis nas fontes fornecidas.</p>'
        . '<p><a href="' . esc_url($collection_url) . '">Abrir a colecao do Tainacan</a></p>'
        . '<p>Os itens abaixo servem como amostra inicial de navegacao e download, para validar o funcionamento local do repositório.</p>';
}

function cedoc_contact_content(): string {
    return '<h1>Solicitacao de Conteudo</h1>'
        . '<p>Use este canal para solicitar inclusao, correcao ou detalhamento de itens do acervo.</p>'
        . '<p>E-mail institucional: <a href="mailto:capoeiraceaca@gmail.com">capoeiraceaca@gmail.com</a></p>'
        . '<p>Este canal integra o site e nao leva a navegacao externa.</p>';
}

function cedoc_fetch_source_html(string $url): string {
    $response = wp_remote_get($url, [
        'timeout' => 30,
        'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
    ]);

    if (is_wp_error($response)) {
        throw new RuntimeException('Failed to fetch source URL ' . $url . ': ' . $response->get_error_message());
    }

    $body = wp_remote_retrieve_body($response);
    if ($body === '') {
        throw new RuntimeException('Empty response from source URL ' . $url);
    }

    return $body;
}

function cedoc_extract_source_content(string $html): string {
    $patterns = [
        '/<article[^>]*>(.*?)<\/article>/is',
        '/<main[^>]*>(.*?)<\/main>/is',
        '/<div class="entry-content">(.*?)<\/div>/is',
        '/<div class="post-content">(.*?)<\/div>/is',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $html, $matches)) {
            $content = $matches[1];
            $content = preg_replace('/<nav[^>]*>.*?<\/nav>/is', '', $content);
            $content = preg_replace('/<aside[^>]*>.*?<\/aside>/is', '', $content);
            $content = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $content);
            $content = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $content);
            $content = preg_replace('/<div[^>]*class="[^"]*(sharedaddy|jp-relatedposts|comments|footer|sidebar)[^"]*"[^>]*>.*?<\/div>/is', '', $content);
            return trim($content);
        }
    }

    return trim(wp_strip_all_tags($html, true));
}

function cedoc_source_page_content(string $title, string $source_url, string $acervo_url, string $fallback_description = ''): string {
    try {
        $html = cedoc_fetch_source_html($source_url);
        $content = cedoc_extract_source_content($html);
        $content = wp_kses_post($content);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $content = '';
    }

    if ($content === '') {
        $content = '<p>' . esc_html($fallback_description !== '' ? $fallback_description : ('Conteudo importado de ' . $source_url)) . '</p>';
    }

    $html = '<h1>' . esc_html($title) . '</h1>';
    $html .= '<div class="cedoc-source-content">' . $content . '</div>';
    $html .= '<div class="cedoc-source-acervo-callout">';
    $html .= '<p><strong>Acervo correspondente:</strong> <a href="' . esc_url($acervo_url) . '">' . esc_html($title) . '</a></p>';
    $html .= '<p><a class="btn btn-primary" href="' . esc_url($acervo_url) . '">Abrir acervo desta area</a></p>';
    $html .= '</div>';

    return $html;
}

function cedoc_category_anchor_url(string $category_slug): string {
    return home_url('/#categoria-' . cedoc_slug($category_slug));
}

function cedoc_simple_page_content(string $title, string $summary, array $links = []): string {
    $content = '<h1>' . esc_html($title) . '</h1>'
        . '<p>' . esc_html($summary) . '</p>';

    if (!empty($links)) {
        $content .= '<h2>Acessos internos</h2><ul>';
        foreach ($links as $label => $url) {
            $content .= '<li><a href="' . esc_url($url) . '">' . esc_html($label) . '</a></li>';
        }
        $content .= '</ul>';
    }

    return $content;
}

function cedoc_subcategory_page_slug(string $category_name, string $subcategory_name): string {
    return cedoc_slug('subcategoria-' . $category_name . '-' . $subcategory_name);
}

function cedoc_subcategory_page_url(string $category_name, string $subcategory_name): string {
    return home_url('/' . cedoc_subcategory_page_slug($category_name, $subcategory_name) . '/');
}

function cedoc_build_subcategory_page_definitions(array $terms_tree, string $collection_url): array {
    $definitions = [];

    foreach ($terms_tree as $term) {
        $category_name = (string) ($term['name'] ?? 'Categoria');
        $category_slug = cedoc_slug($category_name);
        $category_desc = (string) ($term['description'] ?? '');
        $children = $term['children'] ?? [];

        foreach ($children as $child) {
            $child_name = (string) ($child['name'] ?? 'Subcategoria');
            $child_desc = (string) ($child['description'] ?? '');
            $page_slug = cedoc_subcategory_page_slug($category_name, $child_name);
            $page_title = $child_name;
            $summary = $child_desc !== '' ? $child_desc : 'Subcategoria vinculada a ' . $category_name . '.';

            $definitions[$page_slug] = [
                'slug' => $page_slug,
                'title' => $page_title,
                'content' => cedoc_simple_page_content($page_title, $summary, [
                    'Voltar para ' . $category_name => home_url('/#categoria-' . $category_slug),
                    'Abrir acervo' => $collection_url,
                ]),
            ];
        }
    }

    return $definitions;
}

function cedoc_carousel_html(array $items): string {
    if (empty($items)) {
        return '';
    }

    $indicators = '';
    $slides = '';

    foreach ($items as $index => $item) {
        $active = $index === 0 ? ' active' : '';
        $indicators .= '<li data-target="#cedocFeaturedCarousel" data-slide-to="' . (int) $index . '" class="' . ($index === 0 ? 'active' : '') . '"></li>';

        $slides .= '<div class="carousel-item' . $active . '">'
            . '<a href="' . esc_url($item['url']) . '" class="d-block text-decoration-none">'
            . '<div class="cedoc-carousel-card">'
            . '<img src="' . esc_url($item['thumbnail']) . '" alt="' . esc_attr($item['title']) . '">'
            . '<div class="cedoc-carousel-overlay">'
            . '<span class="cedoc-carousel-kicker">Acervo em destaque</span>'
            . '<h3>' . esc_html($item['title']) . '</h3>'
            . '<p>Abrir item e baixar a referencia visual.</p>'
            . '</div></div></a></div>';
    }

    return '<section class="cedoc-carousel-section">'
        . '<div class="cedoc-section-header"><h2>Carrossel do acervo</h2><p>Itens baixados do acervo publico para demonstracao do repositório.</p></div>'
        . '<div id="cedocFeaturedCarousel" class="carousel slide" data-ride="carousel">'
        . '<ol class="carousel-indicators">' . $indicators . '</ol>'
        . '<div class="carousel-inner">' . $slides . '</div>'
        . '<a class="carousel-control-prev" href="#cedocFeaturedCarousel" role="button" data-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="sr-only">Anterior</span></a>'
        . '<a class="carousel-control-next" href="#cedocFeaturedCarousel" role="button" data-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="sr-only">Proximo</span></a>'
        . '</div></section>';
}

function cedoc_categories_accordion_html(array $terms_tree): string {
    if (empty($terms_tree)) {
        return '';
    }

    $accordion_id = 'cedocCategoriesAccordion_' . uniqid();
    $html = '<section class="cedoc-categories-accordion"><div class="accordion" id="' . esc_attr($accordion_id) . '" role="region" aria-label="Categorias hierárquicas do acervo">';

    foreach ($terms_tree as $index => $term) {
        $term_name = $term['name'] ?? '';
        $term_desc = wp_kses_post($term['description'] ?? '');
        $card_id = 'collapse_' . cedoc_slug($term_name) . '_' . $index;
        $active = $index === 0 ? ' show' : '';
        $active_btn = $index === 0 ? '' : ' collapsed';

        $children = $term['children'] ?? [];

        $children_html = '';
        if (!empty($children)) {
            $children_html = '<ul class="cedoc-subcategories-list">';
            foreach ($children as $child) {
                $children_html .= '<li><span class="cedoc-subcategory">' . esc_html($child['name'] ?? '') . '</span></li>';
            }
            $children_html .= '</ul>';
        }

        $html .= '<div class="cedoc-category-card">'
            . '<h2 class="cedoc-category-header">'
            . '<button class="cedoc-category-btn' . $active_btn . '" type="button" data-toggle="collapse" data-target="#' . esc_attr($card_id) . '" aria-expanded="' . ($index === 0 ? 'true' : 'false') . '" aria-controls="' . esc_attr($card_id) . '">'
            . esc_html($term_name)
            . '<span class="cedoc-toggle-icon">▼</span>'
            . '</button>'
            . '</h2>'
            . '<div id="' . esc_attr($card_id) . '" class="collapse' . $active . '" data-parent="#' . esc_attr($accordion_id) . '">'
            . '<div class="cedoc-category-body">'
            . ($term_desc ? '<p class="cedoc-category-desc">' . $term_desc . '</p>' : '')
            . $children_html
            . '</div>'
            . '</div>'
            . '</div>';
    }

    $html .= '</div></section>';
    return $html;
}

function cedoc_categories_dropdown_html(array $terms_tree, array $featured_items = []): string {
    if (empty($terms_tree)) {
        return '';
    }

    $html = '<section class="cedoc-categories-dropdown" role="navigation" aria-label="Categorias do acervo">'
        . '<div class="cedoc-dropdown-list">';

    foreach ($terms_tree as $index => $term) {
        $term_name = esc_html($term['name'] ?? '');
        $thumb = $featured_items[$index]['thumbnail'] ?? (get_template_directory_uri() . '/assets/images/thumbnail_placeholder.png');
        $term_slug = cedoc_slug($term['name'] ?? 'categoria');

        $children = $term['children'] ?? [];

        $children_html = '';
        if (!empty($children)) {
            $children_html .= '<div class="cedoc-subcategories-grid">';
            foreach ($children as $cindex => $child) {
                $sub_slug = cedoc_slug($child['name'] ?? 'subcategoria');
                $sub_url = cedoc_subcategory_page_url($term['name'] ?? 'Categoria', $child['name'] ?? 'Subcategoria');
                $sub_thumb = $featured_items[($index + $cindex + 1) % max(1, count($featured_items))]['thumbnail'] ?? (get_template_directory_uri() . '/assets/images/thumbnail_placeholder.png');
                $children_html .= '<a class="cedoc-subcategory-card" href="' . esc_url($sub_url) . '" role="link" aria-label="' . esc_attr($child['name'] ?? '') . '">'
                    . '<div class="cedoc-subcategory-thumb"><img src="' . esc_url($sub_thumb) . '" alt="' . esc_attr($child['name'] ?? '') . '"></div>'
                    . '<div class="cedoc-subcategory-title">' . esc_html($child['name'] ?? '') . '</div>'
                    . '</a>';
            }
            $children_html .= '</div>';
        }

        $html .= '<details id="categoria-' . esc_attr($term_slug) . '" class="cedoc-category-dropdown"' . ($index === 0 ? ' open' : '') . '>'
            . '<summary class="cedoc-category-summary">'
            . '<div class="cedoc-category-preview"><img src="' . esc_url($thumb) . '" alt="' . esc_attr($term_name) . '"></div>'
            . '<div class="cedoc-category-meta"><span class="cedoc-category-name">' . $term_name . '</span></div>'
            . '<span class="cedoc-toggle-icon">▾</span>'
            . '</summary>'
            . '<div class="cedoc-category-panel">'
            . $children_html
            . '</div>'
            . '</details>';
    }

    $html .= '</div></section>';
    return $html;
}

function cedoc_categories_carousel_html(array $panel_cards, array $featured_items): string {
    if (empty($panel_cards)) {
        return '';
    }

    $indicators = '';
    $slides = '';

    foreach ($panel_cards as $index => $card) {
        $active = $index === 0 ? ' active' : '';
        $indicators .= '<li data-target="#cedocCategoriesCarousel" data-slide-to="' . (int) $index . '" class="' . ($index === 0 ? 'active' : '') . '" role="button" aria-label="Slide ' . ($index + 1) . ' - ' . esc_attr($card['title']) . '"></li>';

        $thumbnail = $featured_items[$index]['thumbnail'] ?? (get_template_directory_uri() . '/assets/images/thumbnail_placeholder.png');

        $slides .= '<div class="carousel-item' . $active . '" role="group" aria-label="Slide ' . ($index + 1) . ' - ' . esc_attr($card['title']) . '">'
            . '<a href="' . esc_url($card['url']) . '" class="d-block text-decoration-none">'
            . '<div class="cedoc-carousel-card">'
            . '<img src="' . esc_url($thumbnail) . '" alt="' . esc_attr($card['title']) . ' - Categoria do acervo" role="img">'
            . '<div class="cedoc-carousel-overlay">'
            . '<h3>' . esc_html($card['title']) . '</h3>'
            . '</div></div></a></div>';
    }

    return '<section class="cedoc-categories-carousel" role="region" aria-label="Carrossel de categorias">'
        . '<div id="cedocCategoriesCarousel" class="carousel slide" data-ride="carousel">'
        . '<ol class="carousel-indicators">' . $indicators . '</ol>'
        . '<div class="carousel-inner">' . $slides . '</div>'
        . '<a class="carousel-control-prev" href="#cedocCategoriesCarousel" role="button" data-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="sr-only">Categoria anterior</span></a>'
        . '<a class="carousel-control-next" href="#cedocCategoriesCarousel" role="button" data-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="sr-only">Proxima categoria</span></a>'
        . '</div></section>';
}

function cedoc_acervo_callout_html(array $home_links): string {
    return '<section class="cedoc-acervo-callout" role="region" aria-label="Chamada para o acervo">'
        . '<div class="cedoc-callout-content">'
        . '<div class="cedoc-callout-text">'
        . '<h2>Explorar o Acervo Completo</h2>'
        . '<p>Acesse a coleção completa do CEDOC CEACA com todas as categorias, subcategorias e itens documentados.</p>'
        . '</div>'
        . '<a href="' . esc_url($home_links['acervo']) . '" class="cedoc-callout-button">Abrir Acervo</a>'
        . '</div>'
        . '</section>';
}

function cedoc_home_content(array $home_links, array $featured_items, array $taxonomy_terms): string {
    // Hero
    $hero = '<section class="cedoc-hero mb-4">'
        . '<h1>Centro de Documentacao CEACA</h1>'
        . '<p>O site foi reorganizado como um Tainacan interno, com acesso por categorias, paginas de apoio e colecao central.</p>'
        . '<div class="cedoc-hero-buttons">'
        . '<a class="btn btn-primary" href="' . esc_url($home_links['acervo']) . '">Abrir acervo</a>'
        . '<a class="btn btn-secondary" href="' . esc_url($home_links['institucional']) . '">Ver estrutura do CEACA</a>'
        . '</div></section>';

    // Carousel right after hero
    $carousel_html = cedoc_carousel_html($featured_items);

    // Categories dropdown with previews - main navigation element
    $categories_dropdown = cedoc_categories_dropdown_html($taxonomy_terms, $featured_items);

    // Acervo callout section
    $acervo_callout = cedoc_acervo_callout_html($home_links);

    // Return: hero -> carousel -> categories dropdown -> acervo callout
    return $hero
        . $carousel_html
        . $categories_dropdown
        . $acervo_callout;
}

function cedoc_set_page_template(int $page_id, string $template): void {
    update_post_meta($page_id, '_wp_page_template', $template);
}

function cedoc_ensure_nav_menu(array $terms_tree): void {
    $menu_name = 'CEACA Principal';
    $menu = wp_get_nav_menu_object($menu_name);
    $menu_id = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu($menu_name);

    $existing_items = wp_get_nav_menu_items($menu_id) ?: [];
    foreach ($existing_items as $existing_item) {
        wp_delete_post($existing_item->ID, true);
    }

    foreach ($terms_tree as $index => $term) {
        $term_name = (string) ($term['name'] ?? 'Categoria');
        $term_slug = cedoc_slug($term_name);
        $parent_url = home_url('/#categoria-' . $term_slug);

        $parent_item_id = wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title' => $term_name,
            'menu-item-url' => $parent_url,
            'menu-item-type' => 'custom',
            'menu-item-status' => 'publish',
            'menu-item-classes' => 'cedoc-menu-topic cedoc-topic-' . $term_slug,
        ]);

        $children = $term['children'] ?? [];
        foreach ($children as $child) {
            $child_name = (string) ($child['name'] ?? 'Subcategoria');
            $child_slug = cedoc_slug($child_name);

            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => $child_name,
                'menu-item-url' => cedoc_subcategory_page_url($term_name, $child_name),
                'menu-item-type' => 'custom',
                'menu-item-status' => 'publish',
                'menu-item-parent-id' => (int) $parent_item_id,
                'menu-item-classes' => 'cedoc-menu-subtopic cedoc-subtopic-' . $term_slug,
            ]);
        }
    }

    set_theme_mod('nav_menu_locations', [
        'navMenubelowHeader' => $menu_id,
    ]);
}

$author_id = cedoc_first_admin_id();

$collection = cedoc_ensure_collection(
    'acervo-cedoc',
    'Acervo CEDOC',
    'Colecao de demonstracao do Centro de Documentacao do CEACA, com itens publicos de referencia e arquivos baixaveis.',
    $author_id
);

if (method_exists($collection, 'register_collection_item_post_type')) {
    $collection->register_collection_item_post_type();
}

// Create taxonomy structure
$taxonomy_terms = [
    ['name' => 'Educacao e Cultura Tradicional', 'description' => 'Reúne documentos sobre a Capoeira e demais culturas tradicionais como práticas pedagógicas transmitidas pela oralidade.', 'children' => [
        ['name' => 'Articulacao Educacao', 'description' => ''],
        ['name' => 'Documentacao de saberes Educacao', 'description' => ''],
        ['name' => 'Manifestacoes Educacao', 'description' => ''],
        ['name' => 'Politicas Publicas', 'description' => 'Leis, decretos, portarias, editais e políticas culturais e educacionais.'],
    ]],
    ['name' => 'CEACA', 'description' => 'Reúne tudo que o CEACA produziu, protagonizou ou que conta sua história.', 'children' => [
        ['name' => 'Memoria e oralidade CEACA', 'description' => 'A história do CEACA como organização: fundação, trajetória, marcos institucionais.'],
        ['name' => 'Mestre Alcides', 'description' => 'Dossiê dedicado ao idealizador e fundador do CEACA.'],
        ['name' => 'Formacao de Aprendizes CEACA', 'description' => 'Eixo central do CEACA: quem são os aprendizes, percursos, atuação.'],
        ['name' => 'Eventos e batizados CEACA', 'description' => 'Registro dos eventos protagonizados pelo CEACA.'],
        ['name' => 'Materiais didaticos CEACA', 'description' => 'Tudo que o CEACA criou como ferramenta de ensino.'],
    ]],
    ['name' => 'Articulacao', 'description' => 'Redes, parcerias, e movimentos em que o CEACA atua.', 'children' => [
        ['name' => 'Parcerias institucionais', 'description' => 'Acordos, convênios e relações formais e informais.'],
        ['name' => 'Redes de cultura', 'description' => 'Participação do CEACA em redes culturais diferentes níveis.'],
        ['name' => 'Acao Grio', 'description' => 'Documentos da relação do CEACA com a Ação Griô.'],
        ['name' => 'Forum das Culturas', 'description' => 'Participação do CEACA no Fórum.'],
    ]],
    ['name' => 'Documentacao de Saberes', 'description' => 'Registros sistemáticos de conhecimento sobre Capoeira e culturas tradicionais.', 'children' => [
        ['name' => 'Teses Dissertacoes Artigos', 'description' => 'Produção acadêmica formal sobre capoeira e culturas tradicionais.'],
        ['name' => 'Projetos pesquisa cultural', 'description' => 'Projetos de pesquisa e culturais com metodologia e resultados.'],
        ['name' => 'Livros Documentacao', 'description' => 'Publicações em formato de livro.'],
        ['name' => 'Pesquisas campo', 'description' => 'Registros etnográficos, cadernos de campo, entrevistas.'],
        ['name' => 'Musicalidade instrumentos', 'description' => 'Documentação sonora, visual e escrita da musicalidade.'],
    ]],
    ['name' => 'Manifestacoes culturais', 'description' => 'Registros das práticas da capoeira e outras manifestações.', 'children' => [
        ['name' => 'Rodas apresentacoes', 'description' => 'Registro das rodas e apresentações.'],
        ['name' => 'Rodas capoeira', 'description' => 'Registros de rodas de grupos de capoeira e batizados diversos.'],
        ['name' => 'Dancas corporalidades', 'description' => 'Registros de danças e expressões corporais.'],
        ['name' => 'Festas cortejos rituais', 'description' => 'Registro de festas populares, cortejos rituais e comemorações.'],
    ]],
];

cedoc_ensure_taxonomy(
    'categorias-do-acervo',
    'Categorias do Acervo',
    'Estrutura hierárquica de categorias e subcategorias do acervo CEACA CEDOC',
    $author_id,
    $taxonomy_terms
);

$items_created = [];

$source_items = [
    [
        'title' => 'FDADM01',
        'source_url' => 'https://ceacacedoc.com.br/wp-content/uploads/tainacan-items/42/1374/FDADM-01.jpeg',
    ],
    [
        'title' => 'FDAFDM02',
        'source_url' => 'https://ceacacedoc.com.br/wp-content/uploads/tainacan-items/42/1398/FDAFDM-02.jpg',
    ],
    [
        'title' => 'FDAFDM03',
        'source_url' => 'https://ceacacedoc.com.br/wp-content/uploads/tainacan-items/42/1429/FDAFDM-03.jpg',
    ],
    [
        'title' => 'FDAFDM04',
        'source_url' => 'https://ceacacedoc.com.br/wp-content/uploads/tainacan-items/42/1458/FDAFDM-04.jpg',
    ],
    [
        'title' => 'FDAFDM05',
        'source_url' => 'https://ceacacedoc.com.br/wp-content/uploads/tainacan-items/42/1492/FDAFDM-05.jpg',
    ],
    [
        'title' => 'FDAFDM06',
        'source_url' => 'https://ceacacedoc.com.br/wp-content/uploads/tainacan-items/42/1522/FDAFDM-06.jpg',
    ],
    [
        'title' => 'FDAFDM07',
        'source_url' => 'https://ceacacedoc.com.br/wp-content/uploads/tainacan-items/42/1547/FDAFDM-07.jpg',
    ],
    [
        'title' => 'FDAFDM08',
        'source_url' => 'https://ceacacedoc.com.br/wp-content/uploads/tainacan-items/42/1575/FDAFDM-08.jpg',
    ],
    [
        'title' => 'FDAFDM09',
        'source_url' => 'https://ceacacedoc.com.br/wp-content/uploads/tainacan-items/42/1602/FDAFDM-09.jpg',
    ],
    [
        'title' => 'FDAFDM10',
        'source_url' => 'https://ceacacedoc.com.br/wp-content/uploads/tainacan-items/42/1631/FDAFDM-10.jpg',
    ],
    [
        'title' => 'FDACFC39',
        'source_url' => 'https://ceacacedoc.com.br/wp-content/uploads/tainacan-items/42/1346/FDACFC39-scaled.jpg',
    ],
    [
        'title' => 'FDACFC38',
        'source_url' => 'https://ceacacedoc.com.br/wp-content/uploads/tainacan-items/42/1318/FDACFC38-scaled.jpg',
    ],
    [
        'title' => 'FDACFC37',
        'source_url' => 'https://ceacacedoc.com.br/wp-content/uploads/tainacan-items/42/1290/FDACFC37-scaled.jpg',
    ],
    [
        'title' => 'FDACFC36',
        'source_url' => 'https://ceacacedoc.com.br/wp-content/uploads/tainacan-items/42/1262/FDACFC36-scaled.jpg',
    ],
    [
        'title' => 'FDACFC35',
        'source_url' => 'https://ceacacedoc.com.br/wp-content/uploads/tainacan-items/42/1232/FDACFC35.jpg',
    ],
];

foreach ($source_items as $source_item) {
    $items_created[] = [
        'title' => $source_item['title'],
        'id' => cedoc_ensure_item(
            $collection,
            [
                'title' => $source_item['title'],
                'description' => 'Item de referencia importado automaticamente do acervo publico do CEDOC. Arquivo original: ' . $source_item['source_url'],
                'source_url' => $source_item['source_url'],
            ],
            $author_id
        ),
    ];
}

$collection_url = get_permalink($collection->get_id());

$subcategory_page_definitions = cedoc_build_subcategory_page_definitions($taxonomy_terms, $collection_url);

 $source_page_definitions = [
    'institucional' => [
        'slug' => 'institucional',
        'title' => 'CEACA',
        'content' => cedoc_source_page_content(
            'CEACA',
            'https://capoeiraceaca.wordpress.com/institucional/',
            cedoc_category_anchor_url('ceaca'),
            'Texto institucional do CEACA.'
        ),
    ],
    'equipe' => [
        'slug' => 'equipe',
        'title' => 'Equipe',
        'content' => cedoc_source_page_content(
            'Equipe',
            'https://capoeiraceaca.wordpress.com/institucional/equipeceaca/',
            cedoc_category_anchor_url('ceaca'),
            'Conteudo da equipe CEACA.'
        ),
    ],
    'historico' => [
        'slug' => 'historico',
        'title' => 'Histórico',
        'content' => cedoc_source_page_content(
            'Histórico',
            'https://capoeiraceaca.wordpress.com/institucional/historico/',
            cedoc_category_anchor_url('ceaca'),
            'Historico do CEACA.'
        ),
    ],
    'missao' => [
        'slug' => 'missao',
        'title' => 'Missão',
        'content' => cedoc_source_page_content(
            'Missão',
            'https://capoeiraceaca.wordpress.com/institucional/missao/',
            cedoc_category_anchor_url('ceaca'),
            'Missao do CEACA.'
        ),
    ],
    'imprensa' => [
        'slug' => 'imprensa',
        'title' => 'Na mídia',
        'content' => cedoc_source_page_content(
            'Na mídia',
            'https://capoeiraceaca.wordpress.com/institucional/imprensa/',
            cedoc_category_anchor_url('articulacao'),
            'Conteudo de imprensa do CEACA.'
        ),
    ],
    'colabore' => [
        'slug' => 'colabore',
        'title' => 'Colabore!',
        'content' => cedoc_source_page_content(
            'Colabore!',
            'https://capoeiraceaca.wordpress.com/colabore/',
            cedoc_category_anchor_url('articulacao'),
            'Pagina de colaboracao do CEACA.'
        ),
    ],
    'memorias-e-projetos' => [
        'slug' => 'memorias-e-projetos',
        'title' => 'Memórias',
        'content' => cedoc_source_page_content(
            'Memórias',
            'https://capoeiraceaca.wordpress.com/memorias-projetos/',
            cedoc_category_anchor_url('documentacao-saberes'),
            'Memorias e projetos do CEACA.'
        ),
    ],
    'capoeira-coco-e-ciranda-na-escola' => [
        'slug' => 'capoeira-coco-e-ciranda-na-escola',
        'title' => 'Capoeira, Coco e Ciranda na Escola',
        'content' => cedoc_source_page_content(
            'Capoeira, Coco e Ciranda na Escola',
            'https://capoeiraceaca.wordpress.com/memorias-projetos/capoeira-coco-e-ciranda-na-escola/',
            cedoc_category_anchor_url('documentacao-saberes'),
            'Projeto educacional do CEACA.'
        ),
    ],
    'cultura-e-saude' => [
        'slug' => 'cultura-e-saude',
        'title' => 'Cultura e Saúde',
        'content' => cedoc_source_page_content(
            'Cultura e Saúde',
            'https://capoeiraceaca.wordpress.com/memorias-projetos/cultura-e-saude/',
            cedoc_category_anchor_url('documentacao-saberes'),
            'Conteudo de cultura e saude do CEACA.'
        ),
    ],
    'minha-historia' => [
        'slug' => 'minha-historia',
        'title' => 'Minha História',
        'content' => cedoc_source_page_content(
            'Minha História',
            'https://capoeiraceaca.wordpress.com/memorias-projetos/minha-historia/',
            cedoc_category_anchor_url('ceaca'),
            'Historia pessoal e memoria oral.'
        ),
    ],
    'mestres' => [
        'slug' => 'mestres',
        'title' => 'Mestres Griôs',
        'content' => cedoc_source_page_content(
            'Mestres Griôs',
            'https://capoeiraceaca.wordpress.com/mestres/',
            cedoc_category_anchor_url('ceaca'),
            'Pagina dos mestres do CEACA.'
        ),
    ],
    'mestre-alcides-de-lima' => [
        'slug' => 'mestre-alcides-de-lima',
        'title' => 'Alcides de Lima',
        'content' => cedoc_source_page_content(
            'Alcides de Lima',
            'https://capoeiraceaca.wordpress.com/mestres/mestre-alcides-de-lima/',
            cedoc_category_anchor_url('ceaca'),
            'Conteudo sobre Mestre Alcides.'
        ),
    ],
    'dorival-dos-santos' => [
        'slug' => 'dorival-dos-santos',
        'title' => 'Dorival dos Santos',
        'content' => cedoc_source_page_content(
            'Dorival dos Santos',
            'https://capoeiraceaca.wordpress.com/mestres/dorival-dos-santos/',
            cedoc_category_anchor_url('ceaca'),
            'Conteudo sobre Mestre Dorival.'
        ),
    ],
    'durval-do-coco' => [
        'slug' => 'durval-do-coco',
        'title' => 'Durval do Coco',
        'content' => cedoc_source_page_content(
            'Durval do Coco',
            'https://capoeiraceaca.wordpress.com/mestres/durval-do-coco/',
            cedoc_category_anchor_url('ceaca'),
            'Conteudo sobre Mestre Durval.'
        ),
    ],
    'eventos' => [
        'slug' => 'eventos',
        'title' => 'Eventos',
        'content' => cedoc_source_page_content(
            'Eventos',
            'https://capoeiraceaca.wordpress.com/eventos/',
            cedoc_category_anchor_url('manifestacoes-culturais'),
            'Eventos e batizados do CEACA.'
        ),
    ],
    'abril-pra-angola-2017' => [
        'slug' => 'abril-pra-angola-2017',
        'title' => 'Abril pra Angola',
        'content' => cedoc_source_page_content(
            'Abril pra Angola',
            'https://capoeiraceaca.wordpress.com/eventos/abril-pra-angola-2017/',
            cedoc_category_anchor_url('manifestacoes-culturais'),
            'Evento Abril pra Angola.'
        ),
    ],
    'seminario-capoeira-e-cidadania-2014' => [
        'slug' => 'seminario-capoeira-e-cidadania-2014',
        'title' => 'Capoeira e Cidadania',
        'content' => cedoc_source_page_content(
            'Capoeira e Cidadania',
            'https://capoeiraceaca.wordpress.com/eventos/seminario-capoeira-e-cidadania-2014/',
            cedoc_category_anchor_url('manifestacoes-culturais'),
            'Seminario Capoeira e Cidadania.'
        ),
    ],
    'encontro-usp-e-escola' => [
        'slug' => 'encontro-usp-e-escola',
        'title' => 'Encontro USP-Escola',
        'content' => cedoc_source_page_content(
            'Encontro USP-Escola',
            'https://capoeiraceaca.wordpress.com/eventos/encontro-usp-e-escola/',
            cedoc_category_anchor_url('manifestacoes-culturais'),
            'Encontro USP-Escola.'
        ),
    ],
    'participacoes' => [
        'slug' => 'participacoes',
        'title' => 'Teia da Diversidade',
        'content' => cedoc_source_page_content(
            'Teia da Diversidade',
            'https://capoeiraceaca.wordpress.com/eventos/participacoes/',
            cedoc_category_anchor_url('manifestacoes-culturais'),
            'Participacoes do CEACA.'
        ),
    ],
    'viraamorim' => [
        'slug' => 'viraamorim',
        'title' => 'Vira Amorim 2013',
        'content' => cedoc_source_page_content(
            'Vira Amorim 2013',
            'https://capoeiraceaca.wordpress.com/eventos/viraamorim/',
            cedoc_category_anchor_url('manifestacoes-culturais'),
            'Vira Amorim 2013.'
        ),
    ],
    'infoteca' => [
        'slug' => 'infoteca',
        'title' => 'Infoteca',
        'content' => cedoc_source_page_content(
            'Infoteca',
            'https://capoeiraceaca.wordpress.com/infoteca/',
            cedoc_category_anchor_url('documentacao-saberes'),
            'Area de apoio documental.'
        ),
    ],
    'audiovisuais' => [
        'slug' => 'audiovisuais',
        'title' => 'Audiovisuais',
        'content' => cedoc_source_page_content(
            'Audiovisuais',
            'https://capoeiraceaca.wordpress.com/infoteca/audiovisuais/',
            cedoc_category_anchor_url('documentacao-saberes'),
            'Material audiovisual.'
        ),
    ],
    'imagens' => [
        'slug' => 'imagens',
        'title' => 'Imagens',
        'content' => cedoc_source_page_content(
            'Imagens',
            'https://capoeiraceaca.wordpress.com/infoteca/iconografias/',
            cedoc_category_anchor_url('documentacao-saberes'),
            'Iconografias e imagens.'
        ),
    ],
    'livros' => [
        'slug' => 'livros',
        'title' => 'Livros',
        'content' => cedoc_source_page_content(
            'Livros',
            'https://capoeiraceaca.wordpress.com/infoteca/bibliografias/',
            cedoc_category_anchor_url('documentacao-saberes'),
            'Bibliografias e livros.'
        ),
    ],
    'educacao-e-publicacoes' => [
        'slug' => 'educacao-e-publicacoes',
        'title' => 'Educação',
        'content' => cedoc_source_page_content(
            'Educação',
            'https://capoeiraceaca.wordpress.com/publicacoes/',
            cedoc_category_anchor_url('educacao-cultura'),
            'Pagina de educação e publicações.'
        ),
    ],
    'amorim-lima' => [
        'slug' => 'amorim-lima',
        'title' => 'Amorim Lima',
        'content' => cedoc_source_page_content(
            'Amorim Lima',
            'https://capoeiraceaca.wordpress.com/publicacoes/amorim-lima/',
            cedoc_category_anchor_url('educacao-cultura'),
            'Conteudo do projeto Amorim Lima.'
        ),
    ],
    'capoeira-na-usp' => [
        'slug' => 'capoeira-na-usp',
        'title' => 'Capoeira na USP',
        'content' => cedoc_source_page_content(
            'Capoeira na USP',
            'https://capoeiraceaca.wordpress.com/publicacoes/capoeiranausp-io/',
            cedoc_category_anchor_url('educacao-cultura'),
            'Conteudo do projeto Capoeira na USP.'
        ),
    ],
    'espaco-girassol' => [
        'slug' => 'espaco-girassol',
        'title' => 'Espaço Girassol',
        'content' => cedoc_source_page_content(
            'Espaço Girassol',
            'https://capoeiraceaca.wordpress.com/publicacoes/espaco-girassol/',
            cedoc_category_anchor_url('educacao-cultura'),
            'Conteudo do Espaco Girassol.'
        ),
    ],
    'leis' => [
        'slug' => 'leis',
        'title' => 'Leis',
        'content' => cedoc_source_page_content(
            'Leis',
            'https://capoeiraceaca.wordpress.com/leis/',
            cedoc_category_anchor_url('educacao-cultura'),
            'Referencias legais do CEACA.'
        ),
    ],
    'lei-cultura-viva' => [
        'slug' => 'lei-cultura-viva',
        'title' => 'Lei Cultura Viva',
        'content' => cedoc_source_page_content(
            'Lei Cultura Viva',
            'https://capoeiraceaca.wordpress.com/leis/lei-cultura-viva/',
            cedoc_category_anchor_url('educacao-cultura'),
            'Conteudo da Lei Cultura Viva.'
        ),
    ],
    'lei-grio' => [
        'slug' => 'lei-grio',
        'title' => 'Lei Griô',
        'content' => cedoc_source_page_content(
            'Lei Griô',
            'https://capoeiraceaca.wordpress.com/leis/lei-grio/',
            cedoc_category_anchor_url('educacao-cultura'),
            'Conteudo da Lei Griô.'
        ),
    ],
    'lei-no-10-639-2003' => [
        'slug' => 'lei-no-10-639-2003',
        'title' => 'Lei n. 10.639/2003',
        'content' => cedoc_source_page_content(
            'Lei n. 10.639/2003',
            'https://capoeiraceaca.wordpress.com/leis/lei-no-10-639-2003/',
            cedoc_category_anchor_url('educacao-cultura'),
            'Conteudo da Lei 10.639/2003.'
        ),
    ],
    'premios' => [
        'slug' => 'premios',
        'title' => 'Prêmios',
        'content' => cedoc_source_page_content(
            'Prêmios',
            'https://capoeiraceaca.wordpress.com/premios/',
            cedoc_category_anchor_url('ceaca'),
            'Premios e reconhecimentos do CEACA.'
        ),
    ],
    'redes' => [
        'slug' => 'redes',
        'title' => 'Rede',
        'content' => cedoc_source_page_content(
            'Rede',
            'https://capoeiraceaca.wordpress.com/redes/',
            cedoc_category_anchor_url('articulacao'),
            'Redes e articulacoes.'
        ),
    ],
    'links' => [
        'slug' => 'links',
        'title' => 'Links',
        'content' => cedoc_source_page_content(
            'Links',
            'https://capoeiraceaca.wordpress.com/redes/links/',
            cedoc_category_anchor_url('articulacao'),
            'Links de referencia e articulacao.'
        ),
    ],
    'referencias' => [
        'slug' => 'referencias',
        'title' => 'Referências',
        'content' => cedoc_source_page_content(
            'Referências',
            'https://capoeiraceaca.wordpress.com/referencias/',
            cedoc_category_anchor_url('documentacao-saberes'),
            'Referencias e fontes do CEACA.'
        ),
    ],
    'solicitacao-de-conteudo' => [
        'slug' => 'solicitacao-de-conteudo',
        'title' => 'Solicitação de Conteúdo',
        'content' => cedoc_contact_content(),
    ],
    'centro-de-documentacao' => [
        'slug' => 'centro-de-documentacao',
        'title' => 'Centro de Documentação',
        'content' => '',
    ],
    'catalogo-cedoc' => [
        'slug' => 'acervo-cedoc',
        'title' => 'Acervo CEDOC',
        'content' => '',
    ],
    'oficinas' => [
        'slug' => 'oficinas',
        'title' => 'Oficinas',
        'content' => cedoc_source_page_content(
            'Oficinas',
            'https://capoeiraceaca.wordpress.com/oficinas/',
            cedoc_category_anchor_url('educacao-cultura'),
            'Oficinas e cursos promovidos pelo CEACA.'
        ),
    ],
    'oficina-audiovisual' => [
        'slug' => 'oficina-audiovisual',
        'title' => 'Oficina: Audiovisual',
        'content' => cedoc_source_page_content(
            'Oficina: Audiovisual',
            'https://capoeiraceaca.wordpress.com/oficinas/audiovisual-edicao-de-imagens-e-videos/',
            cedoc_category_anchor_url('educacao-cultura'),
            'Oficina de audiovisual e edicao de imagens e videos.'
        ),
    ],
    'oficina-wordpress' => [
        'slug' => 'oficina-wordpress',
        'title' => 'Oficina: WordPress',
        'content' => cedoc_source_page_content(
            'Oficina: WordPress',
            'https://capoeiraceaca.wordpress.com/oficinas/wordpress/',
            cedoc_category_anchor_url('educacao-cultura'),
            'Oficina sobre WordPress e publicacao de conteudo.'
        ),
    ],
 ];

$page_definitions = array_merge($source_page_definitions, $subcategory_page_definitions);

$created_pages = [];

foreach ($page_definitions as $key => $definition) {
    $created_pages[$key] = cedoc_ensure_page($definition['slug'], $definition['title'], $definition['content'], $author_id);
}

cedoc_ensure_nav_menu($taxonomy_terms);

$home_links = [
    'inicio' => home_url('/'),
    'institucional' => home_url('/institucional/'),
    'acervo' => $collection_url,
    'historico' => home_url('/historico/'),
    'missao' => home_url('/missao/'),
    'memorias' => home_url('/memorias-e-projetos/'),
    'mestres' => home_url('/mestres/'),
    'eventos' => home_url('/eventos/'),
    'infoteca' => home_url('/infoteca/'),
    'educacao' => home_url('/educacao-e-publicacoes/'),
    'leis' => home_url('/leis/'),
    'premios' => home_url('/premios/'),
    'referencias' => home_url('/referencias/'),
    'contato' => home_url('/solicitacao-de-conteudo/'),
];

$featured_items = [];
foreach ($items_created as $item_data) {
    $item_id = (int) $item_data['id'];
    $featured_items[] = [
        'title' => get_the_title($item_id),
        'url' => get_permalink($item_id),
        'thumbnail' => get_the_post_thumbnail_url($item_id, 'medium') ?: esc_url(get_template_directory_uri() . '/assets/images/thumbnail_placeholder.png'),
    ];
}

$home_page_id = $created_pages['centro-de-documentacao'];
cedoc_set_page_template($home_page_id, 'page-templates/landing.php');
wp_update_post([
    'ID' => $home_page_id,
    'post_content' => cedoc_home_content($home_links, $featured_items, $taxonomy_terms),
    'post_status' => 'publish',
]);

$catalog_page_id = $created_pages['catalogo-cedoc'];
wp_update_post([
    'ID' => $catalog_page_id,
    'post_content' => cedoc_catalog_content($collection_url),
    'post_status' => 'publish',
]);

update_option('show_on_front', 'page');
update_option('page_on_front', $home_page_id);
update_option('page_for_posts', $catalog_page_id);

echo wp_json_encode([
    'collection' => [
        'id' => $collection->get_id(),
        'title' => $collection->get_name(),
        'url' => $collection_url,
    ],
    'pages' => [
        'home' => $home_page_id,
        'catalog' => $catalog_page_id,
        'contact' => $created_pages['solicitacao-de-conteudo'],
    ],
    'items' => $items_created,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
