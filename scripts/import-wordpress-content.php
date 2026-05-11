<?php
/**
 * Import content from WordPress.com blog to local installation
 * 
 * Copies pages and posts from https://capoeiraceaca.wordpress.com
 * Maps them to corresponding pages in the local installation
 * 
 * Usage: wp eval-file scripts/import-wordpress-content.php
 * Or: php -d display_errors=1 scripts/import-wordpress-content.php (from Docker)
 */

declare(strict_types=1);

require '/var/www/html/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

// Source WordPress blog
$source_blog_url = 'https://capoeiraceaca.wordpress.com';

// Mapping of WordPress paths to local pages
$page_mapping = array(
    'institucional/equipeceaca' => 'equipe',
    'institucional' => 'institucional',
    'historico' => 'historico',
    'missao' => 'missao',
    'memorias-e-projetos' => 'memorias-e-projetos',
    'mestres' => 'mestres',
    'eventos' => 'eventos',
    'leis' => 'leis',
    'premios' => 'premios',
    'educacao-e-publicacoes' => 'educacao-e-publicacoes',
);

// Optional mapping from target page slug to cedoc category slug.
// If you want imported pages to appear as subcategories under a given
// category on the homepage, add an entry here. Example:
// 'equipe' => 'ceaca'
$page_category_map = array(
    'equipe' => 'ceaca',
    'institucional' => 'ceaca',
    // add other mappings as needed
);

// Additional mappings discovered on the source site. Add common pages and subpages.
$additional_mappings = array(
    // Publicações / Educação
    'publicacoes/amorim-lima' => 'amorim-lima',
    'publicacoes/capoeiranausp-io' => 'capoeira-na-usp',
    'publicacoes/espaco-girassol' => 'espaco-girassol',

    // Eventos (map top-level and examples)
    'eventos/abril-pra-angola-2017' => 'abril-pra-angola-2017',
    'eventos/seminario-capoeira-e-cidadania-2014' => 'seminario-capoeira-e-cidadania-2014',
    'eventos/encontro-usp-e-escola' => 'encontro-usp-e-escola',

    // Infoteca
    'infoteca/audiovisuais' => 'infoteca-audiovisuais',
    'infoteca/iconografias' => 'infoteca-imagens',
    'infoteca/bibliografias' => 'infoteca-bibliografias',

    // Memórias
    'memorias-projetos/capoeira-coco-e-ciranda-na-escola' => 'capoeira-coco-e-ciranda-na-escola',
    'memorias-projetos/cultura-e-saude' => 'cultura-e-saude',
    'memorias-projetos/minha-historia' => 'minha-historia',

    // Mestres (examples)
    'mestres/mestre-alcides-de-lima' => 'mestre-alcides-de-lima',
    'mestres/dorival-dos-santos' => 'dorival-dos-santos',
    'mestres/durval-do-coco' => 'durval-do-coco',

    // Oficinas
    'oficinas/audiovisual-edicao-de-imagens-e-videos' => 'oficina-audiovisual',
    'oficinas/wordpress' => 'oficina-wordpress',

    // Redes / Referências
    'redes/links' => 'redes-links',
    'referencias' => 'referencias',
);

// Merge additional mappings into page_mapping
$page_mapping = array_merge($page_mapping, $additional_mappings);

// Map categories for the additional pages
$page_category_map = array_merge($page_category_map, array(
    // Education-related
    'amorim-lima' => 'educacao-cultura',
    'capoeira-na-usp' => 'educacao-cultura',
    'espaco-girassol' => 'educacao-cultura',

    // Events -> manifestations
    'abril-pra-angola-2017' => 'manifestacoes-culturais',
    'seminario-capoeira-e-cidadania-2014' => 'manifestacoes-culturais',
    'encontro-usp-e-escola' => 'manifestacoes-culturais',

    // Infoteca -> documentation
    'infoteca-audiovisuais' => 'documentacao-saberes',
    'infoteca-imagens' => 'documentacao-saberes',
    'infoteca-bibliografias' => 'documentacao-saberes',

    // Memorias -> documentation
    'capoeira-coco-e-ciranda-na-escola' => 'documentacao-saberes',
    'cultura-e-saude' => 'documentacao-saberes',
    'minha-historia' => 'documentacao-saberes',

    // Mestres -> ceaca
    'mestre-alcides-de-lima' => 'ceaca',
    'dorival-dos-santos' => 'ceaca',
    'durval-do-coco' => 'ceaca',

    // Oficinas -> educacao
    'oficina-audiovisual' => 'educacao-cultura',
    'oficina-wordpress' => 'educacao-cultura',

    // Redes / referencias
    'redes-links' => 'articulacao',
    'referencias' => 'documentacao-saberes',
));

/**
 * Fetch content from WordPress.com blog
 */
function import_fetch_page_content($path) {
    $url = 'https://capoeiraceaca.wordpress.com/' . trim($path, '/') . '/';
    
    $response = wp_remote_get($url, array(
        'timeout' => 30,
        'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    ));
    
    if (is_wp_error($response)) {
        error_log('Failed to fetch ' . $url . ': ' . $response->get_error_message());
        return null;
    }
    
    $body = wp_remote_retrieve_body($response);
    if (empty($body)) {
        error_log('Empty response from ' . $url);
        return null;
    }
    
    return $body;
}

/**
 * Extract main content from HTML
 */
function import_extract_content($html) {
    // Try to find article content
    $patterns = array(
        '/<article[^>]*>(.*?)<\/article>/is',
        '/<div class="post-content">(.*?)<\/div>/is',
        '/<div class="entry-content">(.*?)<\/div>/is',
        '/<div class="content">(.*?)<\/div>/is',
    );
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $html, $matches)) {
            $content = $matches[1];
            
            // Remove navigation elements
            $content = preg_replace('/<nav[^>]*>.*?<\/nav>/is', '', $content);
            $content = preg_replace('/<div[^>]*class="[^"]*nav[^"]*"[^>]*>.*?<\/div>/is', '', $content);
            
            return $content;
        }
    }
    
    return null;
}

/**
 * Download and attach image to post
 */
function import_download_image($image_url, $post_id) {
    if (empty($image_url)) {
        return null;
    }
    
    // Make sure we have absolute URL
    if (strpos($image_url, 'http') !== 0) {
        $image_url = 'https://capoeiraceaca.wordpress.com' . $image_url;
    }
    
    // Don't re-download Gravatar images or other external assets
    if (strpos($image_url, 'gravatar') !== false) {
        return null;
    }
    
    // Download image
    $response = wp_remote_get($image_url, array(
        'timeout' => 30,
    ));
    
    if (is_wp_error($response)) {
        error_log('Failed to download image: ' . $image_url);
        return null;
    }
    
    $image_data = wp_remote_retrieve_body($response);
    if (empty($image_data)) {
        return null;
    }
    
    // Get filename from URL
    $filename = basename(parse_url($image_url, PHP_URL_PATH));
    if (empty($filename) || strlen($filename) < 5) {
        $filename = 'imported-image-' . time() . '.jpg';
    }
    
    // Save to temp file
    $upload_dir = wp_upload_dir();
    $temp_file = $upload_dir['basedir'] . '/' . $filename;
    
    file_put_contents($temp_file, $image_data);
    
    if (!file_exists($temp_file)) {
        return null;
    }
    
    // Import to media library
    $attachment_id = wp_insert_attachment(array(
        'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
        'post_content' => '',
        'post_status' => 'inherit',
    ), $temp_file, $post_id);
    
    if (is_wp_error($attachment_id)) {
        return null;
    }
    
    // Generate attachment metadata
    wp_generate_attachment_metadata($attachment_id, $temp_file);
    wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $temp_file));
    
    return $attachment_id;
}

/**
 * Download and replace images in content
 */
function import_replace_images_in_content($content, $post_id) {
    if (!preg_match_all('/<img[^>]+src="([^"]+)"[^>]*>/i', $content, $matches)) {
        return $content;
    }
    
    foreach ($matches[1] as $index => $image_url) {
        $attachment_id = import_download_image($image_url, $post_id);
        
        if ($attachment_id) {
            $new_src = wp_get_attachment_url($attachment_id);
            $content = str_replace($image_url, $new_src, $content);
        }
    }
    
    return $content;
}

/**
 * Main import function
 */
function import_pages_from_wordpress() {
    global $page_mapping;
    
    $imported = 0;
    $failed = 0;
    
    echo "Starting import from " . $page_mapping['institucional'] . "...\n";
    
    foreach ($page_mapping as $source_path => $target_slug) {
        echo "\nImporting: " . $source_path . " → " . $target_slug . "\n";
        
        // Fetch content
        $html = import_fetch_page_content($source_path);
        if (!$html) {
            echo "  ✗ Failed to fetch content\n";
            $failed++;
            continue;
        }
        
        // Extract content
        $content = import_extract_content($html);
        if (!$content) {
            echo "  ✗ Failed to extract content\n";
            $failed++;
            continue;
        }
        
        // Clean up content
        $content = wp_kses_post($content);
        $content = trim($content);
        
        // Find or create target page
        $page = get_page_by_path($target_slug, OBJECT, 'page');
        if (!$page) {
            // Try to create the page automatically using the source title as post title
            if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $tmatch)) {
                $page_title = trim(strip_tags($tmatch[1]));
            } else {
                $page_title = ucwords(str_replace(array('-', '_'), ' ', $target_slug));
            }

            $new_page_id = wp_insert_post(array(
                'post_title'   => $page_title,
                'post_name'    => $target_slug,
                'post_content' => '',
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ));

            if (is_wp_error($new_page_id) || !$new_page_id) {
                echo "  ✗ Failed to create page: " . $target_slug . "\n";
                $failed++;
                continue;
            }

            $page = get_post($new_page_id);
            echo "  → Created page: " . $target_slug . " (ID: " . $new_page_id . ")\n";
        }

        $post_id = $page->ID;
        
        // Replace images with local copies
        $content = import_replace_images_in_content($content, $post_id);
        
        // Update post content
        $updated = wp_update_post(array(
            'ID' => $post_id,
            'post_content' => $content,
            'post_status' => 'publish',
        ));
        
        if ($updated && !is_wp_error($updated)) {
            // If there's an explicit category mapping for this target page,
            // save it as post meta so the theme can list it under that category.
            global $page_category_map;
            if (isset($page_category_map[$target_slug]) && !empty($page_category_map[$target_slug])) {
                update_post_meta($post_id, 'cedoc_category', sanitize_text_field($page_category_map[$target_slug]));
            }

            echo "  ✓ Content imported successfully\n";
            $imported++;
        } else {
            echo "  ✗ Failed to update page\n";
            $failed++;
        }
    }
    
    echo "\n";
    echo "═══════════════════════════════════════\n";
    echo "Import Summary:\n";
    echo "  Imported: " . $imported . "\n";
    echo "  Failed: " . $failed . "\n";
    echo "═══════════════════════════════════════\n";
}

// Run import
if (is_admin() || php_sapi_name() === 'cli') {
    import_pages_from_wordpress();
} else {
    wp_die('This script can only be run from command line or WordPress admin.');
}
