<?php
declare(strict_types=1);

function esc_url($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_attr($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_html__($text) { return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8'); }
function the_title_attribute() { echo 'Item de teste'; }
function the_title() { echo 'Item de teste'; }
function get_theme_file_uri($path = '') { return 'https://via.placeholder.com/640x360?text=' . rawurlencode(basename((string) $path) ?: 'Placeholder'); }
function get_template_directory_uri() { return 'https://example.com/theme'; }
function get_query_var($name) { return $name === 'paged' ? 1 : ''; }
function sanitize_key($value) { return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $value)); }
function wp_reset_postdata() {}
function get_header() { echo "<header style='padding:12px;border-bottom:1px solid #ddd'>Header stub</header>\n"; }
function get_footer() { echo "<footer style='padding:12px;border-top:1px solid #ddd'>Footer stub</footer>\n"; }
function get_template_part() {}
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function wp_unslash($value) { return $value; }
function home_url($path = '/') { return 'http://localhost:8008' . $path; }
function __($text) { return $text; }
function esc_html_e($text) { echo htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8'); }
function paginate_links($args) { return '<div style="padding:12px 0">Página 1 de 1</div>'; }

class CedocQueryStub {
    public $max_num_pages = 1;
    private $posts;
    private $index = 0;

    public function __construct(array $posts) { $this->posts = $posts; }
    public function have_posts(): bool { return $this->index < count($this->posts); }
    public function the_post(): void { $this->index++; }
}

function cedoc_get_cedoc_collection_id() { return 42; }
function cedoc_get_cedoc_gallery_items($perPage, $paged, $searchTerm) {
    return new CedocQueryStub([
        ['ID' => 1, 'title' => 'Item 1'],
        ['ID' => 2, 'title' => 'Item 2'],
        ['ID' => 3, 'title' => 'Item 3'],
    ]);
}
function cedoc_get_item_thumbnail($id, $size) {
    return 'https://via.placeholder.com/640x360?text=' . rawurlencode('Item ' . $id);
}
function cedoc_get_item_synopsis($id, $words) { return 'Resumo do item ' . $id; }
function get_the_ID() { return 1; }
function the_permalink() { echo '#'; }
function wp_is_mobile() { return false; }
function get_theme_mod($name, $default = null) { return $default; }
function is_active_sidebar($name) { return false; }
function dynamic_sidebar($name) {}
function bloginfo($name) { echo 'CEACA'; }

$_SERVER['REQUEST_URI'] = '/acervo-cedoc/';
$_GET['layout'] = '1';
$_GET['search'] = '';

require __DIR__ . '/../tainacan-theme-master/src/tainacan/archive-items.php';
