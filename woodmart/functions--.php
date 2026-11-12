<?php
/**
 *
 * The framework's functions and definitions
 */

define( 'WOODMART_THEME_DIR', get_template_directory_uri() );
define( 'WOODMART_THEMEROOT', get_template_directory() );
define( 'WOODMART_IMAGES', WOODMART_THEME_DIR . '/images' );
define( 'WOODMART_SCRIPTS', WOODMART_THEME_DIR . '/js' );
define( 'WOODMART_STYLES', WOODMART_THEME_DIR . '/css' );
define( 'WOODMART_FRAMEWORK', '/inc' );
define( 'WOODMART_DUMMY', WOODMART_THEME_DIR . '/inc/dummy-content' );
define( 'WOODMART_CLASSES', WOODMART_THEMEROOT . '/inc/classes' );
define( 'WOODMART_CONFIGS', WOODMART_THEMEROOT . '/inc/configs' );
define( 'WOODMART_HEADER_BUILDER', WOODMART_THEME_DIR . '/inc/header-builder' );
define( 'WOODMART_ASSETS', WOODMART_THEME_DIR . '/inc/admin/assets' );
define( 'WOODMART_ASSETS_IMAGES', WOODMART_ASSETS . '/images' );
define( 'WOODMART_API_URL', 'https://xtemos.com/licenses/api/' );
define( 'WOODMART_DEMO_URL', 'https://woodmart.xtemos.com/' );
define( 'WOODMART_PLUGINS_URL', WOODMART_DEMO_URL . 'plugins/' );
define( 'WOODMART_DUMMY_URL', WOODMART_DEMO_URL . 'dummy-content-new/' );
define( 'WOODMART_TOOLTIP_URL', WOODMART_DEMO_URL . 'theme-settings-tooltips/' );
define( 'WOODMART_SLUG', 'woodmart' );
define( 'WOODMART_CORE_VERSION', '1.0.36' );
define( 'WOODMART_WPB_CSS_VERSION', '1.0.2' );

if ( ! function_exists( 'woodmart_load_classes' ) ) {
	function woodmart_load_classes() {
		$classes = array(
			'Singleton.php',
			'Api.php',
			'Googlefonts.php',
			'Config.php',
			'Layout.php',
			'License.php',
			'Notices.php',
			'Options.php',
			'Stylesstorage.php',
			'Theme.php',
			'Themesettingscss.php',
			'Vctemplates.php',
			'Wpbcssgenerator.php',
			'Registry.php',
			'Pagecssfiles.php',
		);

		foreach ( $classes as $class ) {
			require WOODMART_CLASSES . DIRECTORY_SEPARATOR . $class;
		}
	}
}

woodmart_load_classes();

new WOODMART_Theme();

define( 'WOODMART_VERSION', woodmart_get_theme_info( 'Version' ) );

//change logo admin
// Change Admin Bar Logo
function custom_admin_logo() {
    echo '
    <style>
        #wp-admin-bar-wp-logo > .ab-item .ab-icon {
            background-image: url("https://suamaylanhquangminh.com/wp-content/uploads/2025/03/LogoHead.svg") !important;
            background-size: cover !important;
            background-position: center !important;
            width: 20px !important;
            height: 20px !important;
        }
    </style>';
}
add_action("admin_head", "custom_admin_logo");

// Change Login Page Logo
function custom_login_logo() {
    echo '
    <style>
        #login h1 a {
            background-image: url("https://suamaylanhquangminh.com/wp-content/uploads/2025/03/LogoHead.svg") !important;
            background-size: contain !important;
            width: 300px !important;
            height: 80px !important;
            display: block;
        }
    </style>';
}
add_action("login_head", "custom_login_logo");



// Change Login Logo Link
function custom_login_logo_url() {
    return home_url(); // Redirect to your site
}
add_filter("login_headerurl", "custom_login_logo_url");


//custom menu admin
function my_custom_admin_menu() {
    add_menu_page(
        'Dự án',       // Page title
        'Dự Án',              // Menu title
        'manage_options',       // Capability
        'duan',       // Menu slug
        'my_custom_page_content', // Function to display content
        'dashicons-admin-generic', // Dashicon icon
        2                        // Position in menu order
    );
    add_menu_page(
        'Dữ liệu nhà đất',
        '<span class="dulieu">Dữ Liệu</span> <span class="nhadat">Nhà Đất</span>',
        'manage_options',
        'dulieunhadat',
        'my_custom_page_content',
        'dashicons-building', // Dashicon icon
        3
    );
}


function my_custom_admin_scripts($hook) {
    if ($hook !== 'toplevel_page_dulieunhadat') {
        return;
    }

    // Ensure jQuery UI CSS and JS are loaded
    wp_enqueue_style('jquery-ui-css', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.min.css');
    wp_enqueue_script('jquery-ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', array('jquery'), null, true);

    // jqGrid CSS & JS
    wp_enqueue_style('jqgrid-css', 'https://cdn.jsdelivr.net/npm/free-jqgrid@4.15.5/css/ui.jqgrid.min.css');
    wp_enqueue_script('jqgrid-js', 'https://cdn.jsdelivr.net/npm/free-jqgrid@4.15.5/js/jquery.jqgrid.min.js', array('jquery', 'jquery-ui'), null, true);

    // Enqueue Select2 if needed
    wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css');
    wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js', array('jquery'), null, true);

    // Enqueue custom script for jqGrid
    wp_enqueue_script('my-admin-jqgrid', get_template_directory_uri() . '/admin-jqgrid.js', array('jquery', 'jqgrid-js', 'jquery-ui'), null, true);

    // Localize script for AJAX if needed
    wp_localize_script('my-admin-jqgrid', 'ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
}

add_action('admin_menu', 'my_custom_admin_menu');
add_action('admin_enqueue_scripts', 'my_custom_admin_scripts');

function enqueue_fontawesome() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
}
add_action('admin_enqueue_scripts', 'enqueue_fontawesome');

function add_custom_class_to_admin_menu() {
    global $menu;

    foreach ($menu as $key => $value) {
        if ($value[2] === 'dulieunhadat') { // Match menu slug
            $menu[$key][4] .= ' dulieunhadat'; // Append custom class
        }
    }
}
add_action('admin_menu', 'add_custom_class_to_admin_menu', 999);

//collapse menu admin
function collapse_admin_menu_script() {
    wp_enqueue_script('collapse-menu', get_template_directory_uri() . '/collapse-menu.js', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'collapse_admin_menu_script');


function my_custom_admin_styles() {
    echo '<style>
        #adminmenu .menu-top .dulieu {
            color: green !important;
            font-weight: bold;
        }
        #adminmenu .menu-top .nhadat {
            color: orange !important;
            font-weight: bold;
        }
        /* Make the menu icon orange */
        #toplevel_page_dulieunhadat .wp-menu-image img {
            filter: invert(35%) sepia(94%) saturate(560%) hue-rotate(3deg) brightness(98%) contrast(99%);
        }
    </style>';
}
add_action('admin_head', 'my_custom_admin_styles');




function my_custom_page_content() {
    ?>
    <div class="wrap">
          <div class="header-container">
        <h1  style="padding:10px 0px 10px 0px;">
            <span class="dashicons dashicons-admin-home" style="font-size: 24px; vertical-align: middle; margin-right: 8px; padding:0px 0px 10px 0px"></span>
            <span style="color: #4CAF50; font-weight:bold;">Dữ Liệu</span>
            <span style="color: #FFA500;  font-weight:bold;">Nhà Đất</span>
        </h1>
              <div class="button-group">
                  <button id="createNewBtn">➕ Tạo Mới</button>
                  <button id="deleteBtn">🗑️ Thùng Rác</button>
              </div>
        <select id="tableSelector"></select>
          </div>
        <table id="jqGrid"></table>
        <div id="jqGridPager"></div>
    </div>
    <?php
}

function remove_admin_notices_on_custom_page() {
    $screen = get_current_screen();

    if ($screen && $screen->id === 'toplevel_page_dulieunhadat') {
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
    }
}

add_action('admin_head', 'remove_admin_notices_on_custom_page');


//hide select
function hide_table_selector() {
    echo '<style>
#tableSelector { display: none !important;}
 .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .button-group {
            display: flex;
            gap: 10px;
        }
        button {
            padding: 10px 15px;
            font-size: 16px;
            font-weight: bold;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }
        #createNewBtn, #deleteBtn {
            background: linear-gradient(145deg, #0073aa, #005e8b);
            box-shadow: 3px 3px 6px rgba(0, 0, 0, 0.2), 
                        -3px -3px 6px rgba(255, 255, 255, 0.2);
                        margin: 13px 0px 18px 8px;
        }
        #createNewBtn:hover, #deleteBtn:hover {
            background: linear-gradient(145deg, #005e8b, #00496d);
            margin: 13px 0px 18px 8px;
        }
        #createNewBtn:active, #deleteBtn:active {
            transform: translateY(3px);
            box-shadow: inset 3px 3px 6px rgba(0, 0, 0, 0.2);
            margin: 13px 0px 18px 8px;
        }
</style>';
}
add_action('admin_head', 'hide_table_selector');





function add_ajaxurl_script() {
    ?>
    <script type="text/javascript">
        var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
    </script>
    <?php
}
add_action('wp_head', 'add_ajaxurl_script');

function get_jqgrid_tables() {
    global $wpdb;
    $table_name = "jq_sys_format";

    // Fetch unique table names from the 'tables' column
    $tables = $wpdb->get_col("SELECT DISTINCT `tables` FROM $table_name");

    if (!$tables) {
        wp_send_json_error(["message" => "No tables found"]);
    }

    wp_send_json(["success" => true, "tables" => $tables]);
}
add_action("wp_ajax_get_jqgrid_tables", "get_jqgrid_tables");
add_action("wp_ajax_nopriv_get_jqgrid_tables", "get_jqgrid_tables");

function format_number_to_vietnamese($number) {
    // Remove commas or non-numeric characters
    $number = str_replace(',', '', $number);

    if ($number >= 1000000000) {
        // Convert to billion (tỷ)
        return number_format($number / 1000000000, 1) . ' tỷ';
    } elseif ($number >= 1000000) {
        // Convert to million (triệu)
        return number_format($number / 1000000, 1) . ' triệu';
    } elseif ($number >= 1000) {
        // Convert to thousand (nghìn)
        return number_format($number / 1000, 1) . ' nghìn';
    } else {
        // Return the number as is if it's smaller than 1000
        return number_format($number);
    }
}
// load content data
function load_jqgrid_data() {
    global $wpdb;

    if (!isset($_POST['table']) || empty($_POST['table'])) {
        wp_send_json_error(["message" => "No table specified"]);
    }

    $selected_table = esc_sql($_POST['table']); // Sanitize input
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $limit = isset($_POST['rows']) ? intval($_POST['rows']) : 15;
    $start = ($page - 1) * $limit;

    // Handle sorting
    $sort_field = isset($_POST['sidx']) && !empty($_POST['sidx']) ? esc_sql($_POST['sidx']) : "id";
    $sort_order = isset($_POST['sord']) && !empty($_POST['sord']) ? esc_sql($_POST['sord']) : "asc";

    // Initialize filtering
    $where = "WHERE 1=1"; // Always true condition to append filters

    if (!empty($_POST['filters'])) {
        $filters = json_decode(stripslashes($_POST['filters']), true);
        if ($filters && isset($filters['rules'])) {
            foreach ($filters['rules'] as $filter) {
                $field = esc_sql($filter['field']);
                $value = esc_sql($filter['data']);
                $where .= " AND $field LIKE '%$value%'"; // Use LIKE for "contains" search
            }
        }
    }


    // Get total records count with filters
    $total_records = $wpdb->get_var("SELECT COUNT(*) FROM `$selected_table` $where");
    //$total_records = $wpdb->get_var("SELECT COUNT(*) FROM `$selected_table`");
    $total_pages = ($total_records > 0) ? ceil($total_records / $limit) : 1;

    // Fetch paginated and sorted data
    $rows = $wpdb->get_results("SELECT * FROM `$selected_table` $where ORDER BY `$sort_field` $sort_order LIMIT $start, $limit", ARRAY_A);
    // Post-process: Attach category name
    $transactionTypeCache = [];
    $propertyTypeCache = [];
    $vitriCache = [];

    foreach ($rows as &$row) {
        // Process transaction_type
        if (isset($row['transaction_type'])) {
            $term_id = $row['transaction_type'];

            if (!isset($transactionTypeCache[$term_id])) {
                $term = get_term($term_id, 'category');
                $transactionTypeCache[$term_id] = (!is_wp_error($term) && !empty($term)) ? $term->name : "";
            }

            $row['transaction_type_name'] = $transactionTypeCache[$term_id];
        } else {
            $row['transaction_type_name'] = null;
        }

        // Process property_type
        if (isset($row['property_type'])) {
            $term_id = $row['property_type'];

            if (!isset($propertyTypeCache[$term_id])) {
                $term = get_term($term_id, 'category');
                $propertyTypeCache[$term_id] = (!is_wp_error($term) && !empty($term)) ? $term->name : "";
            }

            $row['property_type_name'] = $propertyTypeCache[$term_id];
        } else {
            $row['property_type_name'] = null;
        }
        // Process vitri
        if (isset($row['vitri'])) {
            $term_id = $row['vitri'];

            if (!isset($propertyTypeCache[$term_id])) {
                $term = get_term($term_id, 'category');
                $propertyTypeCache[$term_id] = (!is_wp_error($term) && !empty($term)) ? $term->name : "";
            }

            $row['vitri_name'] = $propertyTypeCache[$term_id];
        } else {
            $row['vitri_name'] = null;
        }

        // Combine the two into one label
        $row['getname_column_first'] = '<span><b style="    background: #ffa500;
    padding: 5px;
    color: #fff;
    border-radius: 5px;">'.$row['transaction_type_name'] . '</b><br><hr style="margin: 3px; 
width: 80%; border: none; border-top: 1px solid #ccc; ">' . $row['property_type_name'].'</span>';

        $row['getname_column_two'] = '<p style="
    font-size: 10px;
    background: #fff;
    padding: 2px 5px;
    width: max-content;
    color: #00974c;
    border-radius: 3px;
    text-transform: uppercase;
    box-shadow: 0px 0px 2px #ccc;
    margin-bottom: 2px;">'.$row['vitri_name'] .'</p><div style="color: #0000ca;
    font-style: italic;">'.$row['road'].'</div><p style="margin-top: 5px; background: #aeaeae; color: #fff; 
    padding: 2px; width: max-content; border-radius: 5px;">'.$row['khuvuc'].'</p>';

// Get district name based on district_id (assuming district_id is stored in your table)
        if (isset($row['district'])) {
            $district_id = $row['district']; // The ID of the district stored in the row
            $district_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM district WHERE id = %d", $district_id));

            if ($district_name) {
                $row['district_name'] = $district_name;
            } else {
                $row['district_name'] = "Not Found"; // Default message if no district is found
            }
        } else {
            $row['district_name'] = "No District ID"; // Default message if no district_id is set
        }
        if (isset($row['wards'])) {
            $ward_id = $row['wards']; // The ID of the district stored in the row
            $ward_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM wards WHERE id = %d", $ward_id));

            if ($ward_name) {
                $row['ward_name'] = $ward_name;
            } else {
                $row['ward_name'] = "Not Found"; // Default message if no district is found
            }
        } else {
            $row['ward_name'] = "No District ID"; // Default message if no district_id is set
        }


        if (isset($row['donvi_giaban'])) {
            $term_id = $row['donvi_giaban'];

            if (!isset($propertyTypeCache[$term_id])) {
                $term = get_term($term_id, 'category');
                $propertyTypeCache[$term_id] = (!is_wp_error($term) && !empty($term)) ? $term->name : "";
            }

            $row['donvi_giaban_name'] = $propertyTypeCache[$term_id];
        } else {
            $row['donvi_giaban_name'] = null;
        }

        $row['giaban'] = '<b style="
    color: #ff0000; font-weight:bold;">'.format_number_to_vietnamese($row['giaban']).' '.$row['donvi_giaban_name'].'</b>';

        $row['getname_column_three'] = '<p style="
        font-size: 14px;
    background: #fff;
    padding: 2px 5px;
    width: max-content;
    color: #ff0000;
    border-radius: 3px;
    box-shadow: 0px 0px 2px #ccc;
    margin-bottom: 2px;
    font-weight: bold;">'.$row['dtd_congnhan'] .' m²</p><p style="margin-top: 5px; background: #4caf50; color: #fff; 
    padding: 2px; width: max-content; border-radius: 5px;">('.$row['dtd_ngang'].'m x '.$row['dtd_dai'].'m )</p>';


    }
    unset($row);


    wp_send_json([
        "page" => $page,
        "total" => $total_pages,
        "records" => $total_records,
        "rows" => $rows
    ]);
}

add_action("wp_ajax_load_jqgrid_data", "load_jqgrid_data");
add_action("wp_ajax_nopriv_load_jqgrid_data", "load_jqgrid_data");




function get_provinces() {
    global $wpdb;

    // Query to get id and name from the province table
    $sql = "SELECT id, `name` FROM province";  // Replace 'province' with your actual table name

    // Execute the query and fetch the results
    $results = $wpdb->get_results($sql);

    // Initialize an array to store the options
    $options = [];

    // Loop through the results and store them in the options array
    if ($results) {
        foreach ($results as $row) {
            // Use the id as the key and the name as the value
            $options[$row->id] = $row->name;
        }
    }

    return $options;
}

function parseEditRules($rulesString) {
    $rulesArray = [];
    $rules = explode(";", $rulesString);

    foreach ($rules as $rule) {
        $parts = explode(":", $rule);
        if (count($parts) == 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);

            // Convert string "true"/"false" to boolean
            $rulesArray[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }
    }
    return $rulesArray;
}
// get colModel
function get_jqgrid_colmodel() {
    global $wpdb;

    // Ensure a table is specified
    if (!isset($_POST['table']) || empty($_POST['table'])) {
        wp_send_json_error(["message" => "No table specified"]);
    }

    $selected_table = esc_sql($_POST['table']); // Sanitize table name

    // Fetch column details from jq_sys_format
    $columns = $wpdb->get_results("SELECT tables, `name` AS field, label, width, align, searchoptions, editable, editrules,  edittype,`key`, hidden, `order`,`active`
FROM jq_sys_format WHERE active = 1 AND tables = '$selected_table'  ORDER BY `order` ASC", ARRAY_A);

    if (!$columns) {
        wp_send_json_error(["message" => "No column definitions found in jq_sys_format for table: $selected_table"]);
    }

    $colModel = array_map(function ($col) use ($wpdb, $selected_table) {
        $column_name = esc_sql($col["field"]);
        $search_options = isset($col["searchoptions"]) ? $col["searchoptions"] : "";

        $colConfig = [
            "tables" => $col["tables"],
            "name" => $column_name,
            "index" => $column_name,
            "key" => filter_var($col["key"], FILTER_VALIDATE_BOOLEAN),  // Convert "true"/"false" string to boolean
            "hidden" => filter_var($col["hidden"], FILTER_VALIDATE_BOOLEAN),  // Ensure hidden is boolean
            "sortable" => true,
            "sorttype" => "text",
            "width" => intval($col["width"]) ?: 100,
            "align" => !empty($col["align"]) ? $col["align"] : "left",
            "label" => $col["label"],
            "order" => intval($col["order"]),
            "active" => $col["active"],
            "editable" => filter_var($col["editable"], FILTER_VALIDATE_BOOLEAN),
            "edittype" => "text",
            "editrules" => parseEditRules($col['editrules']),
            "editoptions" => [], // initialize empty
        ];
       /* if ($column_name === 'id') {
            $colConfig["editable"] = false;
            $colConfig["hidden"] = true;
            $colConfig["editoptions"] = [
                "readonly" => true
            ];
        }*/
        // For date input
        if ($column_name === 'created_at') {
            $colConfig["edittype"] = "text";
            $colConfig["editoptions"] = [
                "dataInit" => "function(el) { $(el).datepicker({ dateFormat: 'yy-mm-dd' }); }"
            ];
        }
        if ($col['edittype'] === 'textarea') {
            $colConfig["edittype"] = "textarea";
            $colConfig["editoptions"] = [
                "rows" => 4,
                "cols" => 40
            ];
        }
        if ($column_name === 'getname_column_first' || $column_name === 'getname_column_two' || $column_name === 'getname_column_three' || $column_name === 'giaban') {
            $colConfig["formatter"] = "html";
        }

        if ($column_name === 'dtd_dai' || $column_name === 'dtd_ngang' || $column_name === 'dtd_mathau' ||
            $column_name === 'dtqh_dai' || $column_name === 'dtqh_ngang' || $column_name === 'dtqh_mathau'
            || $column_name === 'ttk_duongrong') {
            $colConfig["editoptions"] = [
                "placeholder" => "m",
                "title" => "Giá trị nhập: m"
            ];
        }
        if ($column_name === 'dtqh_xaydung' || $column_name === 'dtd_congnhan' || $column_name === 'ttk_dtsd') {
            $colConfig["editoptions"] = [
                "placeholder" => "m²",
                "title" => "Giá trị nhập: m²"
            ];
        }

        if (!empty($col["editrules"]) && strpos($col["editrules"], "required:true") !== false) {
            $colConfig["label"] = "<i class='fa fa-asterisk' style='color:red; margin-right:5px;'></i>" . $colConfig["label"];
        }
        // If the column has searchoptions set to 'select', fetch distinct values
        if ($search_options === "select") {

            if($column_name == 'district_name'){
                // Custom logic for district table
                $query = "SELECT DISTINCT `name` FROM `district` WHERE `name` IS NOT NULL ORDER BY `name` ASC";
                $distinct_values = $wpdb->get_col($query);

                if ($distinct_values) {
                    // Example: capitalize district names, or add "All" option, etc.
                    $options = array_map(fn($val) => ucfirst($val) . ":" . ucfirst($val), $distinct_values);
                    array_unshift($options, ":Tất cả"); // Optional blank/default option

                    $colConfig["stype"] = "select";
                    $colConfig["searchoptions"] = [
                        "value" => implode(";", $options),
                        "sopt" => ["eq", "ne"]
                    ];
                }
            }
            if($column_name == 'ward_name'){
                // Custom logic for district table
                $query = "SELECT DISTINCT `name` FROM `wards` WHERE `name` IS NOT NULL ORDER BY `name` ASC";
                $distinct_values = $wpdb->get_col($query);

                if ($distinct_values) {
                    // Example: capitalize district names, or add "All" option, etc.
                    $options = array_map(fn($val) => ucfirst($val) . ":" . ucfirst($val), $distinct_values);
                    array_unshift($options, ":Tất cả"); // Optional blank/default option

                    $colConfig["stype"] = "select";
                    $colConfig["searchoptions"] = [
                        "value" => implode(";", $options),
                        "sopt" => ["eq", "ne"]
                    ];
                }
            }
            else{
                $query = "SELECT DISTINCT `$column_name` FROM `$selected_table` WHERE `$column_name` IS NOT NULL ORDER BY `$column_name` ASC";
                $distinct_values = $wpdb->get_col($query);

                if ($distinct_values) {
                    $options = array_map(fn($val) => "$val:$val", $distinct_values);
                    $colConfig["stype"] = "select";
                    $colConfig["searchoptions"] = [
                        "value" => implode(";", $options),
                        "sopt" => ["eq", "ne"] // Equals, Not Equals
                    ];
                }
            }

        }

        // form edit
        if ($col["edittype"] === "select") {
            $categories = get_the_category();

            // Example static values for select options
            if($col["tables"] == 'dulieunhadat' && $col["field"] == "transaction_type"){
                $parent_id = 431; // Replace with the actual parent category ID
                $categories = get_categories([
                    'taxonomy'   => 'category',  // WordPress category taxonomy
                    'hide_empty' => false,       // Show even empty categories
                    'parent'     => $parent_id,  // Get child categories of this parent
                ]);

                $options = [];
                foreach ($categories as $category) {
                    $options[$category->term_id] = $category->name;
                }

            }
            else if($col["tables"] == 'dulieunhadat' && $col["field"] == "product_type"){
                $parent_id = 513; // Replace with the actual parent category ID
                $categories = get_categories([
                    'taxonomy'   => 'category',  // WordPress category taxonomy
                    'hide_empty' => false,       // Show even empty categories
                    'parent'     => $parent_id,  // Get child categories of this parent
                ]);

                $options = [];
                foreach ($categories as $category) {
                    $options[$category->term_id] = $category->name;
                }

            }
            else if($col["tables"] == 'dulieunhadat' && $col["field"] == "property_type"){
                $parent_id = 519; // Replace with the actual parent category ID
                $categories = get_categories([
                    'taxonomy'   => 'category',  // WordPress category taxonomy
                    'hide_empty' => false,       // Show even empty categories
                    'parent'     => $parent_id,  // Get child categories of this parent
                ]);

                $options = [];
                foreach ($categories as $category) {
                    $options[$category->term_id] = $category->name;
                }

            }
            else if($col["tables"] == 'dulieunhadat' && $col["field"] == "vitri"){
                $parent_id = 557; // Replace with the actual parent category ID
                $categories = get_categories([
                    'taxonomy'   => 'category',  // WordPress category taxonomy
                    'hide_empty' => false,       // Show even empty categories
                    'parent'     => $parent_id,  // Get child categories of this parent
                ]);

                $options = [];
                foreach ($categories as $category) {
                    $options[$category->term_id] = $category->name;
                }

            }
            else if($col["tables"] == 'dulieunhadat' && $col["field"] == "ttk_huong"){
                $parent_id = 567; // Replace with the actual parent category ID
                $categories = get_categories([
                    'taxonomy'   => 'category',  // WordPress category taxonomy
                    'hide_empty' => false,       // Show even empty categories
                    'parent'     => $parent_id,  // Get child categories of this parent
                ]);

                $options = [];
                foreach ($categories as $category) {
                    $options[$category->term_id] = $category->name;
                }

            }
            else if($col["tables"] == 'dulieunhadat' && $col["field"] == "ttk_loaidat"){
                $parent_id = 595; // Replace with the actual parent category ID
                $categories = get_categories([
                    'taxonomy'   => 'category',  // WordPress category taxonomy
                    'hide_empty' => false,       // Show even empty categories
                    'parent'     => $parent_id,  // Get child categories of this parent
                ]);

                $options = [];
                foreach ($categories as $category) {
                    $options[$category->term_id] = $category->name;
                }

            }
            else if($col["tables"] == 'dulieunhadat' && $col["field"] == "loaitaisan"){
                $parent_id = 609; // Replace with the actual parent category ID
                $categories = get_categories([
                    'taxonomy'   => 'category',  // WordPress category taxonomy
                    'hide_empty' => false,       // Show even empty categories
                    'parent'     => $parent_id,  // Get child categories of this parent
                ]);

                $options = [];
                foreach ($categories as $category) {
                    $options[$category->term_id] = $category->name;
                }

            }
            else if($col["tables"] == 'dulieunhadat' && $col["field"] == "vaitro"){
                $parent_id = 625; // Replace with the actual parent category ID
                $categories = get_categories([
                    'taxonomy'   => 'category',  // WordPress category taxonomy
                    'hide_empty' => false,       // Show even empty categories
                    'parent'     => $parent_id,  // Get child categories of this parent
                ]);

                $options = [];
                foreach ($categories as $category) {
                    $options[$category->term_id] = $category->name;
                }

            }
            else if($col["tables"] == 'dulieunhadat' && $col["field"] == "gioitinh"){
                $parent_id = 641; // Replace with the actual parent category ID
                $categories = get_categories([
                    'taxonomy'   => 'category',  // WordPress category taxonomy
                    'hide_empty' => false,       // Show even empty categories
                    'parent'     => $parent_id,  // Get child categories of this parent
                ]);

                $options = [];
                foreach ($categories as $category) {
                    $options[$category->term_id] = $category->name;
                }

            }
            else if($col["tables"] == 'dulieunhadat' && ($col["field"] == "donvi_giathue" || $col["field"] == "donvi_giaban")){
                $parent_id = 655; // Replace with the actual parent category ID
                $categories = get_categories([
                    'taxonomy'   => 'category',  // WordPress category taxonomy
                    'hide_empty' => false,       // Show even empty categories
                    'parent'     => $parent_id,  // Get child categories of this parent
                ]);

                $options = [];
                foreach ($categories as $category) {
                    $options[$category->term_id] = $category->name;
                }

            }
            else if($col["tables"] == 'dulieunhadat' && $col["field"] == "donvi_thoigiangia"){
                $parent_id = 661; // Replace with the actual parent category ID
                $categories = get_categories([
                    'taxonomy'   => 'category',  // WordPress category taxonomy
                    'hide_empty' => false,       // Show even empty categories
                    'parent'     => $parent_id,  // Get child categories of this parent
                ]);

                $options = [];
                foreach ($categories as $category) {
                    $options[$category->term_id] = $category->name;
                }

            }
            else if($col["tables"] == 'dulieunhadat' && $col["field"] == "donvi_thoigianthue"){
                $parent_id = 667; // Replace with the actual parent category ID
                $categories = get_categories([
                    'taxonomy'   => 'category',  // WordPress category taxonomy
                    'hide_empty' => false,       // Show even empty categories
                    'parent'     => $parent_id,  // Get child categories of this parent
                ]);

                $options = [];
                foreach ($categories as $category) {
                    $options[$category->term_id] = $category->name;
                }

            }
            else if($col["tables"] == 'dulieunhadat' && $col["field"] == "loaihoahong"){
                $parent_id = 647; // Replace with the actual parent category ID
                $categories = get_categories([
                    'taxonomy'   => 'category',  // WordPress category taxonomy
                    'hide_empty' => false,       // Show even empty categories
                    'parent'     => $parent_id,  // Get child categories of this parent
                ]);

                $options = [];
                foreach ($categories as $category) {
                    $options[$category->term_id] = $category->name;
                }

            }
            else if($col["tables"] == 'dulieunhadat' && $col["field"] == "province"){
                $options = get_provinces();
            }
            else if($col["tables"] == 'dulieunhadat' && ($col["field"] == "ttk_sotang" || $col["field"] == "ttk_sophongngu" || $col["field"] == "ttk_swc"
                    || $col["field"] == "ttk_sotangham")){
                $options = [];
                for ($i = 1; $i <= 50; $i++) {
                    $options[$i] = $i;
                }
            }
            else{

            }


            $colConfig["edittype"] = "select"; // Set the edit type to select

            // Map the options for the select input
            $optionString = '';
            foreach ($options as $value => $label) {
                $optionString .= "$value:$label;";
            }

            // Set editoptions to include the select options
            $colConfig["editoptions"] = [
                "value" => ":;" . rtrim($optionString, ';'), // Create a string like "value1:label1;value2:label2;"
            ];
        }
        return $colConfig;
    }, $columns);

    wp_send_json(["success" => true, "colModel" => $colModel]);
}

add_action("wp_ajax_get_jqgrid_colmodel", "get_jqgrid_colmodel");
add_action("wp_ajax_nopriv_get_jqgrid_colmodel", "get_jqgrid_colmodel");

// function add data
function add_jqgrid_data() {
    global $wpdb;

    if (!isset($_POST['table']) || empty($_POST['table'])) {
        wp_send_json_error(["message" => "No table specified"]);
    }

    $selected_table = esc_sql($_POST['table']);
    $data = $_POST;
    unset($data['action'], $data['table'], $data['id'], $data['oper'],$data['pll_ajax_backend']); // Remove unnecessary fields

    $wpdb->insert($selected_table, $data);

    if ($wpdb->last_error) {
        wp_send_json_error(["message" => $wpdb->last_error]);
    } else {
        wp_send_json_success(["message" => "Record added successfully"]);
    }
}

add_action("wp_ajax_add_jqgrid_data", "add_jqgrid_data");

//function edit data
function edit_jqgrid_data() {
    global $wpdb;
    error_log("Received POST data: " . print_r($_POST, true));
    if (!isset($_POST['table']) || empty($_POST['table'])) {
        wp_send_json_error(["message" => "No table specified"]);
    }

    $selected_table = esc_sql($_POST['table']);
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        wp_send_json_error(["message" => "Invalid ID: $id"]);
    }

    $data = $_POST;
    unset($data['action'], $data['table'], $data['id'], $data['oper']); // Remove unnecessary fields

    $columns = $wpdb->get_col("SHOW COLUMNS FROM `$selected_table`");

    // ❌ Xóa các key không thuộc bảng database
    foreach ($data as $key => $value) {
        if (!in_array($key, $columns)) {
            unset($data[$key]); // Xóa key không hợp lệ
        }
    }

    unset($data['id']); // Remove ID from update data
    // Log the data being updated
    error_log("Updating table: $selected_table, id: $id, Data: " . print_r($data, true));

    $result = $wpdb->update($selected_table, $data, ["id" => $id]);

    if ($result === false) {
        error_log("SQL ERROR: " . $wpdb->last_error);
        wp_send_json_error(["message" => "DB Error: " . $wpdb->last_error]);
    } else {
        error_log("SQL QUERY SUCCESS: " . $wpdb->last_query);
        wp_send_json_success(["message" => "Record updated successfully"]);
    }
}

add_action("wp_ajax_edit_jqgrid_data", "edit_jqgrid_data");



// function delete data
add_action('wp_ajax_delete_jqgrid_data', 'delete_jqgrid_data_callback');

function delete_jqgrid_data_callback() {
    // Validate nonce for security
    if ( !isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'delete_jqgrid_data_nonce') ) {
        wp_send_json_error("Invalid nonce");
        return;
    }

    // Check if the required parameters are set
    if ( !isset($_POST['table']) || !isset($_POST['id']) ) {
        wp_send_json_error("Missing parameters");
        return;
    }

    // Sanitize inputs
    $table = sanitize_text_field($_POST['table']);
    $id = intval($_POST['id']);  // Ensure the ID is an integer

    if ( !$table || !$id ) {
        wp_send_json_error("Invalid table or ID");
        return;
    }

    global $wpdb;

    // Perform the deletion query
    $result = $wpdb->delete($table, ['id' => $id]);

    if ($result !== false) {
        wp_send_json_success("Deleted successfully");
    } else {
        wp_send_json_error("Failed to delete");
    }
}



// ajax province, district, wards
// Hook for AJAX request
add_action('wp_ajax_load_districts', 'load_districts_callback');
add_action('wp_ajax_nopriv_load_districts', 'load_districts_callback');

function load_districts_callback() {
    // Check for the province_id passed from the AJAX request
    if (isset($_POST['province_id'])) {
        global $wpdb;

        // Sanitize the input
        $province_id = intval($_POST['province_id']);

        // Query to get the districts related to the selected province
        $sql = "
            SELECT id, `name` 
            FROM district  -- Replace with your actual districts table
            WHERE province_id = %d
        ";

        // Prepare and execute the query
        $districts = $wpdb->get_results($wpdb->prepare($sql, $province_id));

        // Prepare the response
        if ($districts) {
            $data = [];
            foreach ($districts as $district) {
                $data[] = [
                    'id' => $district->id,
                    'name' => $district->name
                ];
            }

            // Send the response back to the AJAX call
            wp_send_json_success(['data' => $data]);
        } else {
            wp_send_json_success(['data' => []]);
        }
    } else {
        wp_send_json_error(['message' => 'Province ID not provided']);
    }

    // Always die in AJAX functions to terminate correctly
    wp_die();
}


add_action('wp_ajax_load_wards', 'load_wards_callback');
add_action('wp_ajax_nopriv_load_wards', 'load_wards_callback');

function load_wards_callback() {
    // Check for the province_id passed from the AJAX request
    if (isset($_POST['district_id'])) {
        global $wpdb;

        // Sanitize the input
        $district_id = intval($_POST['district_id']);

        // Query to get the wards related to the selected district
        $sql = "
            SELECT id, `name` 
            FROM wards  -- Replace with your actual wards table
            WHERE district_id = %d
        ";

        // Prepare and execute the query
        $wards = $wpdb->get_results($wpdb->prepare($sql, $district_id));

        // Prepare the response
        if ($wards) {
            $data = [];
            foreach ($wards as $ward) {
                $data[] = [
                    'id' => $ward->id,
                    'name' => $ward->name
                ];
            }

            // Send the response back to the AJAX call
            wp_send_json_success(['data' => $data]);
        } else {
            wp_send_json_success(['data' => []]);
        }
    } else {
        wp_send_json_error(['message' => 'Province ID not provided']);
    }

    // Always die in AJAX functions to terminate correctly
    wp_die();
}
