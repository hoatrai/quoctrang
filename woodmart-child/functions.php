<?php
/**
 * Enqueue script and styles for child theme
 */
function woodmart_child_enqueue_styles() {
	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'woodmart-style' ), woodmart_get_theme_info( 'Version' ) );
}
add_action( 'wp_enqueue_scripts', 'woodmart_child_enqueue_styles', 10010 );

/**
 * Quốc Trang: tiệm điện thoại tiếng Việt
 */

// 1) Script cài đặt một lần (xem HUONG-DAN.md). Xong việc có thể xóa dòng require này.
require_once get_stylesheet_directory() . '/inc/quoctrang-setup.php';

// 2) Dùng bản dịch WooCommerce/Elementor đi kèm theme nếu site chưa có gói ngôn ngữ chính thức.
add_filter( 'load_textdomain_mofile', function ( $mofile, $domain ) {
	if ( in_array( $domain, array( 'woocommerce', 'elementor' ), true ) && 0 === strpos( get_locale(), 'vi' ) && ! is_readable( $mofile ) ) {
		$bundled = get_stylesheet_directory() . '/languages/' . $domain . '-vi.mo';
		if ( is_readable( $bundled ) ) {
			return $bundled;
		}
	}
	return $mofile;
}, 10, 2 );

// 3) Hiện giá thật khi sản phẩm có giá; chưa có giá thì hiện "Liên hệ".
add_action( 'init', function () {
	remove_filter( 'woocommerce_get_price_html', 'njengah_replace_text_with_call_for_price' );
	add_filter( 'woocommerce_get_price_html', function ( $price_html, $product ) {
		if ( $product && '' !== (string) $product->get_price() ) {
			return $price_html;
		}
		return 'Liên hệ: <a href="tel:0939102209">0939 102 209</a>';
	}, 10, 2 );
}, 20 );
