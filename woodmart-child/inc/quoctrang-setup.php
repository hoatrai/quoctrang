<?php
/**
 * Plugin Name: Quốc Trang - Cài đặt tiệm điện thoại (chạy 1 lần)
 * Description: Chuyển site sang tiệm điện thoại tiếng Việt.
 *   Xem trước : /wp-admin/?qt_setup=preview   (không thay đổi gì)
 *   Áp dụng   : /wp-admin/?qt_setup=apply
 *   Hoàn tác  : /wp-admin/?qt_setup=undo      (bật lại các trang/bài/sản phẩm đã ẩn)
 *   WP-CLI    : wp qt-setup [--apply] [--undo]
 * Cần đăng nhập quản trị. Xong việc thì xóa file này khỏi mu-plugins.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ---------- THÔNG TIN CỬA HÀNG: sửa lại cho đúng trước khi chạy ---------- */
define( 'QT_SHOP',  'Quốc Trang Mobile' );
define( 'QT_PHONE', '0906 717 457' );
define( 'QT_TEL',   '0906717457' );
define( 'QT_ZALO',  'https://zalo.me/0906717457' );
define( 'QT_ADDR',  '128 Bạch Đằng, P.2, Q. Tân Bình, TP. Hồ Chí Minh' );
define( 'QT_HOURS', '8:00 - 21:00 (Thứ 2 - Chủ nhật)' );
define( 'QT_HIDE_LEGACY_PAGES',    true ); // ẩn (chuyển nháp) trang demo + trang của dự án khác
define( 'QT_HIDE_LEGACY_POSTS',    true ); // ẩn bài blog cũ không liên quan
define( 'QT_HIDE_DEMO_PRODUCTS',   true ); // ẩn sản phẩm demo laptop/linh kiện

function qt_log( $m ) { $GLOBALS['qt_log'][] = $m; }
function qt_min( $s ) { return trim( preg_replace( '/\s*\n\s*/', ' ', $s ) ); }

/* ================= CHẠY ================= */
function qt_run( $apply ) {
	$GLOBALS['qt_log'] = array();
	qt_log( $apply ? '=== ÁP DỤNG ===' : '=== XEM TRƯỚC (chưa thay đổi gì) ===' );
	if ( $apply && function_exists( 'kses_remove_filters' ) ) { kses_remove_filters(); }

	$ids   = qt_step_categories( $apply );
	$pages = qt_step_pages( $apply );
	qt_step_home( $apply, $ids, $pages );
	qt_step_footer( $apply, $ids, $pages );
	qt_step_menus( $apply, $ids, $pages );
	qt_step_header( $apply );
	qt_step_options( $apply );
	qt_step_legacy( $apply );

	if ( $apply ) {
		if ( function_exists( 'kses_init' ) ) { kses_init(); }
		delete_transient( 'wc_term_counts' );
		wp_cache_flush();
		qt_log( 'Xong. Vào Cài đặt > Đường dẫn tĩnh bấm "Lưu thay đổi" 1 lần, rồi xóa cache (nếu có).' );
	}
	return $GLOBALS['qt_log'];
}

function qt_undo() {
	$GLOBALS['qt_log'] = array();
	$ids = get_option( 'qt_setup_drafted', array() );
	foreach ( (array) $ids as $id ) {
		wp_update_post( array( 'ID' => (int) $id, 'post_status' => 'publish' ) );
		qt_log( "Bật lại #$id" );
	}
	delete_option( 'qt_setup_drafted' );
	qt_log( 'Hoàn tác xong phần ẩn nội dung. (Tên danh mục/menu/trang chủ đã đổi thì sửa tay hoặc khôi phục từ bản sao lưu DB; nội dung Elementor cũ được giữ trong meta _qt_backup_elementor_data.)' );
	return $GLOBALS['qt_log'];
}

add_action( 'admin_init', function () {
	if ( ! isset( $_GET['qt_setup'] ) || ! current_user_can( 'manage_options' ) ) { return; }
	$mode = sanitize_key( wp_unslash( $_GET['qt_setup'] ) );
	if ( 'undo' === $mode )      { $log = qt_undo(); }
	elseif ( 'apply' === $mode ) { $log = qt_run( true ); }
	else                         { $log = qt_run( false ); }
	wp_die( '<pre style="white-space:pre-wrap">' . esc_html( implode( "\n", $log ) ) . '</pre>', 'Quốc Trang setup' );
} );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'qt-setup', function ( $args, $assoc ) {
		$log = isset( $assoc['undo'] ) ? qt_undo() : qt_run( isset( $assoc['apply'] ) );
		foreach ( $log as $l ) { WP_CLI::log( $l ); }
	} );
}

/* ================= DANH MỤC ================= */
function qt_cat_defs() {
	// key => [id có sẵn (0 = tạo mới), tên, slug, key cha]
	return array(
		'phone'    => array( 189, 'Điện thoại', 'dien-thoai', '' ),
		'iphone'   => array( 193, 'iPhone', 'iphone', 'phone' ),
		'android'  => array( 192, 'Điện thoại Android', 'dien-thoai-android', 'phone' ),
		'samsung'  => array( 0, 'Samsung', 'samsung', 'android' ),
		'xiaomi'   => array( 0, 'Xiaomi', 'xiaomi', 'android' ),
		'oppo'     => array( 0, 'OPPO', 'oppo', 'android' ),
		'vivo'     => array( 0, 'vivo', 'vivo', 'android' ),
		'tablet'   => array( 181, 'Máy tính bảng', 'may-tinh-bang', '' ),
		'ipad'     => array( 183, 'iPad', 'ipad', 'tablet' ),
		'tabdroid' => array( 182, 'Tablet Android', 'tablet-android', 'tablet' ),
		'watch'    => array( 0, 'Đồng hồ thông minh', 'dong-ho-thong-minh', '' ),
		'acc'      => array( 190, 'Phụ kiện', 'phu-kien', '' ),
		'case'     => array( 191, 'Ốp lưng và bao da', 'op-lung-bao-da', 'acc' ),
		'charger'  => array( 158, 'Sạc và cáp', 'sac-cap', 'acc' ),
		'headset'  => array( 137, 'Tai nghe', 'tai-nghe', 'acc' ),
		'power'    => array( 0, 'Pin dự phòng', 'pin-du-phong', 'acc' ),
		'glass'    => array( 0, 'Kính cường lực', 'kinh-cuong-luc', 'acc' ),
		'uncat'    => array( 133, 'Chưa phân loại', 'chua-phan-loai', '' ),
	);
}

function qt_step_categories( $apply ) {
	$ids = array();
	foreach ( qt_cat_defs() as $key => $d ) {
		list( $id, $name, $slug, $pkey ) = $d;
		$parent = ( $pkey && isset( $ids[ $pkey ] ) ) ? (int) $ids[ $pkey ] : 0;
		$term   = $id ? get_term( $id, 'product_cat' ) : null;
		if ( $term && ! is_wp_error( $term ) ) {
			qt_log( "Danh mục #$id: {$term->name} -> $name" );
			if ( $apply ) {
				$r = wp_update_term( $id, 'product_cat', array( 'name' => $name, 'slug' => $slug, 'parent' => $parent ) );
				if ( is_wp_error( $r ) ) { qt_log( '  LỖI: ' . $r->get_error_message() ); }
			}
			$ids[ $key ] = (int) $id;
			continue;
		}
		$ex = term_exists( $slug, 'product_cat' );
		if ( $ex ) {
			$ids[ $key ] = is_array( $ex ) ? (int) $ex['term_id'] : (int) $ex;
			qt_log( "Danh mục đã có: $name" );
			continue;
		}
		qt_log( "Tạo danh mục: $name" );
		if ( $apply ) {
			$r = wp_insert_term( $name, 'product_cat', array( 'slug' => $slug, 'parent' => $parent ) );
			if ( is_wp_error( $r ) ) { qt_log( '  LỖI: ' . $r->get_error_message() ); continue; }
			$ids[ $key ] = (int) $r['term_id'];
		}
	}
	return $ids;
}

function qt_term_url( $ids, $key ) {
	if ( empty( $ids[ $key ] ) ) { return '#'; }
	$l = get_term_link( (int) $ids[ $key ], 'product_cat' );
	return is_wp_error( $l ) ? '#' : $l;
}

/* ================= ELEMENTOR ================= */
function qt_detach_elementor( $id ) {
	$data = get_post_meta( $id, '_elementor_data', true );
	if ( '' !== $data && ! get_post_meta( $id, '_qt_backup_elementor_data', true ) ) {
		update_post_meta( $id, '_qt_backup_elementor_data', wp_slash( $data ) );
	}
	foreach ( array( '_elementor_edit_mode', '_elementor_data', '_elementor_element_cache', '_elementor_css', '_elementor_page_assets' ) as $k ) {
		delete_post_meta( $id, $k );
	}
}

/* ================= TRANG ================= */
function qt_page_wrap( $inner ) {
	$css = '.qt-page{max-width:880px;margin:0 auto;line-height:1.7;color:#333}.qt-page h2{font-size:24px;margin:26px 0 10px}.qt-page ul{margin:0 0 16px 20px}.qt-page li{margin-bottom:6px}.qt-box{background:#f3f6fd;border-radius:12px;padding:20px 24px;margin:18px 0}.qt-btn2{display:inline-block;padding:12px 24px;border-radius:8px;background:#1c61e7;color:#fff!important;font-weight:700;text-decoration:none;margin:0 10px 10px 0}';
	return qt_min( '<div class="qt-page"><style>' . $css . '</style>' . $inner . '</div>' );
}

function qt_contact_box() {
	$map = 'https://maps.google.com/maps?q=' . rawurlencode( QT_ADDR );
	return '<div class="qt-box"><strong>' . QT_SHOP . '</strong><br>Địa chỉ: ' . QT_ADDR . '<br>Hotline: <a href="tel:' . QT_TEL . '">' . QT_PHONE . '</a><br>Giờ mở cửa: ' . QT_HOURS . '</div>'
		. '<a class="qt-btn2" href="tel:' . QT_TEL . '">Gọi ' . QT_PHONE . '</a><a class="qt-btn2" href="' . QT_ZALO . '">Chat Zalo</a><a class="qt-btn2" href="' . esc_url( $map ) . '">Chỉ đường</a>';
}

function qt_page_defs() {
	$d = array();
	$d['about'] = array( 0, 'gioi-thieu', 'Giới thiệu', qt_page_wrap(
		'<h2>Về ' . QT_SHOP . '</h2><p>' . QT_SHOP . ' là cửa hàng chuyên điện thoại di động, máy tính bảng và phụ kiện, phục vụ khách hàng tại TP. Hồ Chí Minh và giao hàng toàn quốc. Chúng tôi chọn sản phẩm kỹ, báo giá rõ ràng và hỗ trợ khách cả sau khi mua.</p>'
		. '<h2>Cam kết của chúng tôi</h2><ul><li>Sản phẩm chính hãng, nguồn gốc rõ ràng.</li><li>Giá niêm yết minh bạch, không phát sinh chi phí.</li><li>Bảo hành và hỗ trợ kỹ thuật tận nơi.</li><li>Tư vấn đúng nhu cầu, đúng ngân sách.</li></ul>' . qt_contact_box() ) );
	$d['promo'] = array( 25, 'khuyen-mai', 'Khuyến mãi', qt_page_wrap(
		'<h2>Ưu đãi đang áp dụng</h2><p>Các sản phẩm đang giảm giá được cập nhật tự động bên dưới. Gọi hotline <a href="tel:' . QT_TEL . '">' . QT_PHONE . '</a> để biết thêm ưu đãi tại cửa hàng.</p>[products on_sale="true" limit="12" columns="4"]' ) );
	$d['stores'] = array( 27, 'cua-hang', 'Cửa hàng', qt_page_wrap( '<h2>Hệ thống cửa hàng</h2>' . qt_contact_box() ) );
	$d['contact'] = array( 29, 'lien-he', 'Liên hệ', qt_page_wrap( '<h2>Liên hệ ' . QT_SHOP . '</h2><p>Bạn cần tư vấn chọn máy, báo giá hoặc hỗ trợ bảo hành? Hãy liên hệ ngay, chúng tôi phản hồi trong giờ làm việc.</p>' . qt_contact_box() ) );
	$d['delivery'] = array( 52, 'giao-hang-doi-tra', 'Giao hàng và đổi trả', qt_page_wrap(
		'<h2>Giao hàng</h2><ul><li>Giao hàng toàn quốc, đóng gói chống sốc.</li><li>Nội thành TP. Hồ Chí Minh có thể giao trong ngày.</li><li>Khách được kiểm tra máy khi nhận hàng.</li></ul>'
		. '<h2>Đổi trả</h2><ul><li>Đổi trả trong 7 ngày nếu máy lỗi do nhà sản xuất.</li><li>Sản phẩm cần đầy đủ hộp, phụ kiện và hóa đơn.</li><li>Không áp dụng với máy rơi vỡ, vào nước hoặc can thiệp phần mềm/phần cứng.</li></ul>' . qt_contact_box() ) );
	$d['tradein'] = array( 33, 'thu-cu-doi-moi', 'Thu cũ đổi mới', qt_page_wrap(
		'<h2>Thu cũ đổi mới</h2><p>Mang máy cũ đến cửa hàng để được định giá nhanh và trừ trực tiếp vào máy mới.</p><ul><li>Định giá minh bạch, kiểm tra máy trước mặt khách.</li><li>Hỗ trợ sao lưu và chuyển dữ liệu sang máy mới.</li><li>Nhận hầu hết các dòng iPhone, Samsung, Xiaomi, OPPO, vivo.</li></ul>' . qt_contact_box() ) );
	$d['warranty'] = array( 0, 'chinh-sach-bao-hanh', 'Chính sách bảo hành', qt_page_wrap(
		'<h2>Chính sách bảo hành</h2><ul><li>Bảo hành chính hãng 12 tháng đối với điện thoại và máy tính bảng mới.</li><li>Phụ kiện bảo hành theo quy định từng sản phẩm.</li><li>Hỗ trợ tiếp nhận và theo dõi bảo hành tại cửa hàng.</li></ul><p>Vui lòng mang theo hóa đơn hoặc phiếu bảo hành khi cần hỗ trợ.</p>' . qt_contact_box() ) );
	$d['installment'] = array( 0, 'tra-gop', 'Mua trả góp', qt_page_wrap(
		'<h2>Mua trả góp</h2><p>Hỗ trợ trả góp qua thẻ tín dụng và công ty tài chính với thủ tục nhanh gọn.</p><ul><li>Chuẩn bị CCCD và giấy tờ theo yêu cầu.</li><li>Duyệt hồ sơ nhanh, nhận máy ngay tại cửa hàng.</li><li>Gọi hotline để được tính kỳ hạn và mức trả hàng tháng phù hợp.</li></ul>' . qt_contact_box() ) );
	$d['repair'] = array( 0, 'sua-chua-dien-thoai', 'Sửa chữa điện thoại', qt_page_wrap(
		'<h2>Dịch vụ sửa chữa</h2><ul><li>Thay màn hình, thay pin, sửa lỗi nguồn và sạc.</li><li>Cài đặt phần mềm, cứu dữ liệu.</li><li>Báo giá trước khi sửa, linh kiện rõ nguồn gốc.</li></ul>' . qt_contact_box() ) );
	$d['guide'] = array( 0, 'huong-dan-mua-hang', 'Hướng dẫn mua hàng', qt_page_wrap(
		'<h2>Cách mua hàng</h2><ul><li>Chọn sản phẩm trên website, thêm vào giỏ hàng và đặt hàng.</li><li>Hoặc gọi hotline / nhắn Zalo để được tư vấn và giữ máy.</li><li>Nhận hàng, kiểm tra máy rồi thanh toán.</li></ul>' . qt_contact_box() ) );
	return $d;
}

function qt_step_pages( $apply ) {
	$out = array();
	$legacy = get_post( 8905 );
	if ( $legacy && 'lien-he' === $legacy->post_name ) {
		qt_log( 'Trang cũ #8905 chiếm slug lien-he: đổi thành lien-he-cu và chuyển nháp' );
		if ( $apply ) {
			wp_update_post( array( 'ID' => 8905, 'post_name' => 'lien-he-cu', 'post_status' => 'draft' ) );
			qt_remember( array( 8905 ) );
		}
	}
	foreach ( qt_page_defs() as $key => $d ) {
		list( $id, $slug, $title, $html ) = $d;
		$post = $id ? get_post( $id ) : null;
		if ( ! $post ) {
			$found = get_page_by_path( $slug );
			if ( $found ) { $post = $found; }
		}
		if ( $post ) {
			qt_log( "Cập nhật trang #{$post->ID}: {$post->post_title} -> $title" );
			if ( $apply ) {
				qt_detach_elementor( $post->ID );
				wp_update_post( array( 'ID' => $post->ID, 'post_title' => $title, 'post_name' => $slug, 'post_content' => $html, 'post_status' => 'publish' ) );
			}
			$out[ $key ] = (int) $post->ID;
		} else {
			qt_log( "Tạo trang mới: $title" );
			if ( $apply ) {
				$nid = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug, 'post_content' => $html ) );
				if ( $nid && ! is_wp_error( $nid ) ) { $out[ $key ] = (int) $nid; }
			}
		}
	}
	$blog = get_post( 23 );
	if ( $blog ) {
		qt_log( 'Trang blog #23 -> Tin tức' );
		if ( $apply ) { wp_update_post( array( 'ID' => 23, 'post_title' => 'Tin tức', 'post_name' => 'tin-tuc' ) ); }
		$out['news'] = 23;
	}
	return $out;
}

function qt_page_url( $pages, $key ) {
	return ! empty( $pages[ $key ] ) ? get_permalink( $pages[ $key ] ) : '#';
}

/* ================= TRANG CHỦ ================= */
function qt_home_html( $ids, $pages ) {
	$css = '.qt-home{color:#222}.qt-home *{box-sizing:border-box}'
		. '.qt-hero{background:linear-gradient(135deg,#0b1f4d 0%,#1c61e7 100%);color:#fff;border-radius:16px;padding:56px 40px;margin:0 0 28px}'
		. '.qt-hero h1{color:#fff;font-size:40px;line-height:1.2;margin:0 0 14px}.qt-hero p{font-size:18px;opacity:.92;margin:0 0 24px;max-width:560px}'
		. '.qt-btn{display:inline-block;padding:13px 26px;border-radius:8px;font-weight:700;text-decoration:none;margin:0 10px 10px 0}'
		. '.qt-btn-w{background:#fff;color:#0b1f4d!important}.qt-btn-o{border:2px solid #fff;color:#fff!important}'
		. '.qt-usp{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin:0 0 36px}'
		. '.qt-usp div{border:1px solid #e6e9ef;border-left:4px solid #1c61e7;border-radius:10px;padding:16px 18px;background:#fff}'
		. '.qt-usp b{display:block;font-size:16px;margin-bottom:4px}.qt-usp span{font-size:13px;color:#667}'
		. '.qt-h2{font-size:26px;margin:0 0 18px;font-weight:700}'
		. '.qt-cats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin:0 0 40px}'
		. '.qt-cats a{display:flex;align-items:center;justify-content:center;min-height:90px;border-radius:12px;background:#f3f6fd;color:#0b1f4d;font-size:18px;font-weight:700;text-decoration:none;border:1px solid #dfe7fb;transition:.2s}'
		. '.qt-cats a:hover{background:#1c61e7;color:#fff}.qt-sec{margin:0 0 44px}'
		. '.qt-svc{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:18px;margin:0 0 44px}'
		. '.qt-svc a{display:block;padding:24px;border-radius:12px;background:#0b1f4d;color:#fff!important;text-decoration:none}'
		. '.qt-svc strong{display:block;font-size:20px;margin-bottom:8px}.qt-svc span{opacity:.85;font-size:14px;line-height:1.5}'
		. '.qt-store{background:#f3f6fd;border-radius:14px;padding:28px;margin:0 0 20px}.qt-store h3{margin:0 0 10px;font-size:20px}.qt-store p{margin:0 0 6px}'
		. '@media(max-width:767px){.qt-hero{padding:34px 22px}.qt-hero h1{font-size:28px}}';
	$map = 'https://maps.google.com/maps?q=' . rawurlencode( QT_ADDR );
	$h  = '<div class="qt-home"><style>' . $css . '</style>';
	$h .= '<section class="qt-hero"><h1>Điện thoại chính hãng<br>Giá tốt mỗi ngày</h1><p>iPhone, Samsung, Xiaomi, OPPO, vivo và phụ kiện. Tư vấn đúng nhu cầu, bảo hành rõ ràng, hỗ trợ trả góp.</p>'
		. '<a class="qt-btn qt-btn-w" href="' . esc_url( qt_term_url( $ids, 'phone' ) ) . '">Xem điện thoại</a><a class="qt-btn qt-btn-o" href="tel:' . QT_TEL . '">Gọi ' . QT_PHONE . '</a></section>';
	$h .= '<section class="qt-usp"><div><b>Hàng chính hãng</b><span>Nguồn gốc rõ ràng, máy nguyên seal</span></div><div><b>Bảo hành uy tín</b><span>Hỗ trợ tiếp nhận ngay tại cửa hàng</span></div><div><b>Trả góp linh hoạt</b><span>Thủ tục nhanh, nhận máy trong ngày</span></div><div><b>Giao hàng toàn quốc</b><span>Kiểm tra máy khi nhận hàng</span></div></section>';
	$h .= '<section class="qt-sec"><h2 class="qt-h2">Mua theo hãng</h2><div class="qt-cats">';
	foreach ( array( 'iphone' => 'iPhone', 'samsung' => 'Samsung', 'xiaomi' => 'Xiaomi', 'oppo' => 'OPPO', 'vivo' => 'vivo', 'tablet' => 'Máy tính bảng', 'acc' => 'Phụ kiện' ) as $k => $label ) {
		$h .= '<a href="' . esc_url( qt_term_url( $ids, $k ) ) . '">' . $label . '</a>';
	}
	$h .= '</div></section>';
	$h .= '<section class="qt-sec"><h2 class="qt-h2">Điện thoại bán chạy</h2>[products best_selling="true" limit="8" columns="4" category="dien-thoai"]</section>';
	$h .= '<section class="qt-sec"><h2 class="qt-h2">Hàng mới về</h2>[products orderby="date" order="DESC" limit="8" columns="4" category="dien-thoai"]</section>';
	$h .= '<section class="qt-sec"><h2 class="qt-h2">Phụ kiện</h2>[products orderby="date" order="DESC" limit="4" columns="4" category="phu-kien"]</section>';
	$h .= '<section class="qt-svc"><a href="' . esc_url( qt_page_url( $pages, 'tradein' ) ) . '"><strong>Thu cũ đổi mới</strong><span>Định giá nhanh, trừ trực tiếp vào máy mới.</span></a>'
		. '<a href="' . esc_url( qt_page_url( $pages, 'installment' ) ) . '"><strong>Mua trả góp</strong><span>Thủ tục gọn, duyệt hồ sơ nhanh.</span></a>'
		. '<a href="' . esc_url( qt_page_url( $pages, 'repair' ) ) . '"><strong>Sửa chữa điện thoại</strong><span>Thay màn hình, pin, sửa lỗi phần cứng và phần mềm.</span></a></section>';
	$h .= '<section class="qt-store"><h3>' . QT_SHOP . '</h3><p>Địa chỉ: ' . QT_ADDR . '</p><p>Hotline: <a href="tel:' . QT_TEL . '">' . QT_PHONE . '</a> &nbsp;|&nbsp; <a href="' . QT_ZALO . '">Zalo</a> &nbsp;|&nbsp; <a href="' . esc_url( $map ) . '">Chỉ đường</a></p><p>Giờ mở cửa: ' . QT_HOURS . '</p></section></div>';
	return qt_min( $h );
}

function qt_step_home( $apply, $ids, $pages ) {
	if ( ! get_post( 14 ) ) { qt_log( 'Không thấy trang chủ #14, bỏ qua' ); return; }
	qt_log( 'Thay nội dung trang chủ #14 bằng trang chủ tiệm điện thoại (bản Elementor cũ được lưu vào meta _qt_backup_elementor_data)' );
	if ( $apply ) {
		qt_detach_elementor( 14 );
		wp_update_post( array( 'ID' => 14, 'post_title' => 'Trang chủ', 'post_name' => 'trang-chu', 'post_content' => qt_home_html( $ids, $pages ) ) );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', 14 );
	}
}

/* ================= FOOTER (cms_block #128) ================= */
function qt_step_footer( $apply, $ids, $pages ) {
	if ( ! get_post( 128 ) ) { qt_log( 'Không thấy footer #128, bỏ qua' ); return; }
	qt_log( 'Thay nội dung footer #128' );
	if ( ! $apply ) { return; }
	$col = function ( $title, $links ) {
		$s = '<div><h4 style="margin:0 0 12px">' . $title . '</h4>';
		foreach ( $links as $l ) { $s .= '<div style="margin-bottom:6px"><a href="' . esc_url( $l[1] ) . '">' . $l[0] . '</a></div>'; }
		return $s . '</div>';
	};
	$h  = '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:28px;padding:30px 0">';
	$h .= '<div><h4 style="margin:0 0 12px">' . QT_SHOP . '</h4><p style="margin:0 0 6px">Điện thoại chính hãng, giá tốt, bảo hành uy tín.</p><p style="margin:0 0 6px">' . QT_ADDR . '</p><p style="margin:0">Hotline: <a href="tel:' . QT_TEL . '">' . QT_PHONE . '</a></p></div>';
	$h .= $col( 'Sản phẩm', array( array( 'iPhone', qt_term_url( $ids, 'iphone' ) ), array( 'Samsung', qt_term_url( $ids, 'samsung' ) ), array( 'Xiaomi', qt_term_url( $ids, 'xiaomi' ) ), array( 'OPPO', qt_term_url( $ids, 'oppo' ) ), array( 'Phụ kiện', qt_term_url( $ids, 'acc' ) ) ) );
	$h .= $col( 'Chính sách', array( array( 'Chính sách bảo hành', qt_page_url( $pages, 'warranty' ) ), array( 'Giao hàng và đổi trả', qt_page_url( $pages, 'delivery' ) ), array( 'Mua trả góp', qt_page_url( $pages, 'installment' ) ), array( 'Hướng dẫn mua hàng', qt_page_url( $pages, 'guide' ) ) ) );
	$h .= $col( 'Dịch vụ', array( array( 'Thu cũ đổi mới', qt_page_url( $pages, 'tradein' ) ), array( 'Sửa chữa điện thoại', qt_page_url( $pages, 'repair' ) ), array( 'Khuyến mãi', qt_page_url( $pages, 'promo' ) ), array( 'Liên hệ', qt_page_url( $pages, 'contact' ) ) ) );
	$h .= '</div>';
	qt_detach_elementor( 128 );
	wp_update_post( array( 'ID' => 128, 'post_title' => 'Footer Quốc Trang', 'post_content' => qt_min( $h ) ) );
}

/* ================= MENU ================= */
function qt_item_cat( $ids, $key, $title, $kids = array() ) {
	if ( empty( $ids[ $key ] ) ) { return null; }
	return array( 'k' => 'cat', 'id' => (int) $ids[ $key ], 't' => $title, 'c' => array_filter( $kids ) );
}
function qt_item_page( $pages, $key, $title ) {
	if ( empty( $pages[ $key ] ) ) { return null; }
	return array( 'k' => 'page', 'id' => (int) $pages[ $key ], 't' => $title, 'c' => array() );
}
function qt_add_items( $menu_id, $items, $parent = 0 ) {
	foreach ( $items as $it ) {
		$args = array( 'menu-item-title' => $it['t'], 'menu-item-status' => 'publish', 'menu-item-parent-id' => $parent );
		if ( 'cat' === $it['k'] ) {
			$args['menu-item-type'] = 'taxonomy'; $args['menu-item-object'] = 'product_cat'; $args['menu-item-object-id'] = $it['id'];
		} elseif ( 'page' === $it['k'] ) {
			$args['menu-item-type'] = 'post_type'; $args['menu-item-object'] = 'page'; $args['menu-item-object-id'] = $it['id'];
		} else {
			$args['menu-item-type'] = 'custom'; $args['menu-item-url'] = $it['u'];
		}
		$mid = wp_update_nav_menu_item( $menu_id, 0, $args );
		if ( ! is_wp_error( $mid ) && ! empty( $it['c'] ) ) { qt_add_items( $menu_id, $it['c'], $mid ); }
	}
}
function qt_rebuild_menu( $apply, $menu_id, $label, $items ) {
	if ( ! wp_get_nav_menu_object( $menu_id ) ) { qt_log( "Không thấy menu #$menu_id ($label), bỏ qua" ); return; }
	qt_log( "Làm lại menu #$menu_id ($label): " . count( $items ) . ' mục cấp 1' );
	if ( ! $apply ) { return; }
	foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $old ) { wp_delete_post( $old->ID, true ); }
	qt_add_items( $menu_id, $items );
}

function qt_step_menus( $apply, $ids, $pages ) {
	$phone_kids = array(
		qt_item_cat( $ids, 'iphone', 'iPhone' ), qt_item_cat( $ids, 'samsung', 'Samsung' ), qt_item_cat( $ids, 'xiaomi', 'Xiaomi' ),
		qt_item_cat( $ids, 'oppo', 'OPPO' ), qt_item_cat( $ids, 'vivo', 'vivo' ), qt_item_cat( $ids, 'android', 'Android khác' ),
	);
	$tab_kids = array( qt_item_cat( $ids, 'ipad', 'iPad' ), qt_item_cat( $ids, 'tabdroid', 'Tablet Android' ) );
	$acc_kids = array(
		qt_item_cat( $ids, 'case', 'Ốp lưng và bao da' ), qt_item_cat( $ids, 'charger', 'Sạc và cáp' ), qt_item_cat( $ids, 'headset', 'Tai nghe' ),
		qt_item_cat( $ids, 'power', 'Pin dự phòng' ), qt_item_cat( $ids, 'glass', 'Kính cường lực' ),
	);
	$cats = array_values( array_filter( array(
		qt_item_cat( $ids, 'phone', 'Điện thoại', $phone_kids ),
		qt_item_cat( $ids, 'tablet', 'Máy tính bảng', $tab_kids ),
		qt_item_cat( $ids, 'watch', 'Đồng hồ thông minh' ),
		qt_item_cat( $ids, 'acc', 'Phụ kiện', $acc_kids ),
	) ) );
	$main = array_values( array_filter( array_merge(
		array( array( 'k' => 'url', 'u' => home_url( '/' ), 't' => 'Trang chủ', 'c' => array() ) ),
		$cats,
		array( qt_item_page( $pages, 'promo', 'Khuyến mãi' ), qt_item_page( $pages, 'news', 'Tin tức' ), qt_item_page( $pages, 'contact', 'Liên hệ' ) )
	) ) );
	$top = array_values( array_filter( array(
		qt_item_page( $pages, 'promo', 'Khuyến mãi' ), qt_item_page( $pages, 'tradein', 'Thu cũ đổi mới' ), qt_item_page( $pages, 'installment', 'Mua trả góp' ),
		qt_item_page( $pages, 'repair', 'Sửa chữa' ), qt_item_page( $pages, 'contact', 'Liên hệ' ),
	) ) );
	qt_rebuild_menu( $apply, 195, 'Menu đầu trang', $top );
	qt_rebuild_menu( $apply, 196, 'Ngôn ngữ/tiền tệ (để trống)', array() );
	qt_rebuild_menu( $apply, 197, 'Menu chính', $main );
	qt_rebuild_menu( $apply, 198, 'Danh mục trên mobile', $cats );
	qt_rebuild_menu( $apply, 199, 'Menu mobile', $main );
	qt_rebuild_menu( $apply, 200, 'Danh mục (Tất cả danh mục)', $cats );
}

/* ================= HEADER BUILDER ================= */
function qt_walk_header( &$node, &$changed ) {
	if ( ! is_array( $node ) ) { return; }
	if ( isset( $node['type'], $node['params'] ) && is_array( $node['params'] ) ) {
		if ( 'infobox' === $node['type'] && isset( $node['params']['content']['value'] ) && is_string( $node['params']['content']['value'] ) ) {
			$v = $node['params']['content']['value'];
			if ( false !== strpos( $v, '24 Support' ) ) {
				$node['params']['content']['value'] = '<h6 style="margin-bottom: 0; font-size: 14px;">Hotline tư vấn</h6>' . "\n" . '<span style="color: #1c61e7; font-size: 14px;">' . QT_PHONE . '</span>';
				$changed++;
			} elseif ( false !== strpos( $v, 'Worldwide' ) ) {
				$node['params']['content']['value'] = '<h6 style="margin-bottom: 0; font-size: 14px;">Giao hàng toàn quốc</h6>' . "\n" . '<span style="color: #1c61e7; font-size: 14px;">Nhanh chóng - An toàn</span>';
				$changed++;
			}
		}
		if ( 'stickynavigation' === $node['type'] && isset( $node['params']['title']['value'] ) && 'All Categories' === $node['params']['title']['value'] ) {
			$node['params']['title']['value'] = 'Danh mục sản phẩm';
			$changed++;
		}
	}
	foreach ( $node as $k => &$child ) { qt_walk_header( $child, $changed ); }
	unset( $child );
}

function qt_step_header( $apply ) {
	$h = get_option( 'whb_header_331220' );
	if ( ! is_array( $h ) ) { qt_log( 'Không đọc được cấu hình header 331220, bỏ qua (tự sửa trong Header builder)' ); return; }
	$changed = 0;
	qt_walk_header( $h, $changed );
	qt_log( "Header: $changed chữ sẽ được đổi (hotline, giao hàng, Danh mục sản phẩm). Logo phải thay tay trong Header builder." );
	if ( $apply && $changed ) {
		if ( ! get_option( 'qt_backup_whb_header_331220' ) ) { update_option( 'qt_backup_whb_header_331220', get_option( 'whb_header_331220' ), false ); }
		update_option( 'whb_header_331220', $h );
	}
}

/* ================= TÙY CHỌN SITE ================= */
function qt_step_options( $apply ) {
	qt_log( 'Tên site: ' . QT_SHOP . ' | Mô tả: Điện thoại chính hãng - Giá tốt - Bảo hành uy tín' );
	if ( $apply ) {
		update_option( 'blogname', QT_SHOP );
		update_option( 'blogdescription', 'Điện thoại chính hãng - Giá tốt - Bảo hành uy tín' );
		update_option( 'woocommerce_store_address', '128 Bạch Đằng, P.2' );
		update_option( 'woocommerce_store_city', 'TP. Hồ Chí Minh' );
	}
}

/* ================= ẨN NỘI DUNG CŨ ================= */
function qt_remember( $ids ) {
	$all = get_option( 'qt_setup_drafted', array() );
	update_option( 'qt_setup_drafted', array_values( array_unique( array_merge( (array) $all, $ids ) ) ), false );
}
function qt_step_legacy( $apply ) {
	$todo = array();
	if ( QT_HIDE_LEGACY_PAGES ) {
		// trang demo Mega Electronics (cửa hàng mẫu, Apple event) + trang du lịch/vé máy bay + khóa học/thần số học
		$page_ids = array( 2, 110, 112, 114, 116, 454, 8907, 9018, 9043, 9065, 9071, 9073, 9075, 9077, 9079, 9082, 9085, 9092, 9095, 9101, 9107, 9112, 9115, 9119, 9155, 9436, 9437, 9448, 9449, 9465, 9473 );
		foreach ( $page_ids as $id ) {
			$p = get_post( $id );
			if ( $p && 'page' === $p->post_type && 'publish' === $p->post_status ) { $todo[] = $id; }
		}
	}
	if ( QT_HIDE_LEGACY_POSTS ) {
		foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids' ) ) as $id ) { $todo[] = (int) $id; }
	}
	if ( QT_HIDE_DEMO_PRODUCTS ) {
		foreach ( get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids' ) ) as $id ) { $todo[] = (int) $id; }
	}
	qt_log( 'Ẩn (chuyển nháp, không xóa) ' . count( $todo ) . ' mục cũ: ' . implode( ', ', $todo ) );
	if ( $apply && $todo ) {
		qt_remember( $todo );
		foreach ( $todo as $id ) { wp_update_post( array( 'ID' => $id, 'post_status' => 'draft' ) ); }
	}
	qt_log( 'Lưu ý: plugin Tutor LMS (khóa học) vẫn đang bật. Nếu shop không bán khóa học, anh tắt plugin này.' );
}
