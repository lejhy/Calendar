<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Calendar
 *
 * @wordpress-plugin
 * Plugin Name:       Calendar
 * Plugin URI:        http://example.com/calendar-uri/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Lejhy
 * Author URI:        http://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       calendar
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define all constants
 */
global $wpdb;
define('CALENDAR_APARTMENTS', $wpdb->prefix .'calendar_apartments');
define('CALENDAR_PRICES', $wpdb->prefix .'calendar_prices');
define('CALENDAR_RESERVATIONS', $wpdb->prefix .'calendar_reservations');
define('CALENDAR_OPTIONS', 'calendar_options');

/**
 * Activate, Deactivate, Uninstall
 */
function activate_calendar() {
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE ".CALENDAR_APARTMENTS." (
		id int NOT NULL AUTO_INCREMENT,
		apartment VARCHAR(63) NOT NULL,
		description TEXT NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";
	dbDelta( $sql );

    $sql = "CREATE TABLE ".CALENDAR_PRICES." (
		id int NOT NULL AUTO_INCREMENT,
		apartment_id int NOT NULL,
		start_date DATE NOT NULL,
		end_date DATE NOT NULL,
		price FLOAT NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";
    dbDelta( $sql );

    $sql = "CREATE TABLE ".CALENDAR_RESERVATIONS." (
		id int NOT NULL AUTO_INCREMENT,
		apartment_id int NOT NULL,
		start_date DATE NOT NULL,
		end_date DATE NOT NULL,
		price FLOAT NOT NULL,
		description TEXT NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";
    dbDelta( $sql );

	add_option("");

  flush_rewrite_rules();

}

function deactivate_calendar() {

	flush_rewrite_rules();

}

function uninstall_calendar() {
	global $wpdb;
    //$wpdb->query("DROP TABLE IF EXISTS ".CALENDAR_APARTMENTS);
    //$wpdb->query("DROP TABLE IF EXISTS ".CALENDAR_PRICES);
    //$wpdb->query("DROP TABLE IF EXISTS ".CALENDAR_RESERVATIONS);

}

register_activation_hook( __FILE__, 'activate_calendar' );
register_deactivation_hook( __FILE__, 'deactivate_calendar' );
register_uninstall_hook(__FILE__, 'uninstall_calendar');

/**
 * Shortcode
 */
function calendar_shortcodes_init() {
    function calendar_shortcode($atts, $content) {
        global $wpdb;
        $options = get_option(CALENDAR_OPTIONS);
        $per_week_start = $options['per_week_start'];
        $per_week_end = $options['per_week_end'];
        $sql = "SELECT * FROM ".CALENDAR_APARTMENTS.";";
        $apartments = $wpdb->get_results($sql);
        $sql = "SELECT id, apartment_id, start_date, end_date FROM ".CALENDAR_RESERVATIONS.";";
        $reservations = $wpdb->get_results($sql);
        $sql = "SELECT * FROM ".CALENDAR_PRICES.";";
        $prices = $wpdb->get_results($sql);

		$content = "
            <div id='calendar'>
                <script>
                    var calendar_data = {
                        per_week_start:".json_encode($per_week_start).",
                        per_week_end:".json_encode($per_week_end).",
                        apartments:".json_encode($apartments).",
                        reservations:".json_encode($reservations).",
                        prices:".json_encode($prices)."
                    };
                    window.onload = function() {
                        var contact = new Contact('calendar', '".admin_url('admin-post.php')."');
                        contact.init();
                        var calendar = new Calendar(calendar_data, 'calendar', contact);
                        calendar.init();
                    }
                </script>
        ";

		$content .= "</div>";

        wp_enqueue_script( 'calendar.js' );
        wp_enqueue_script( 'contact.js' );
        wp_enqueue_style( 'calendar.css' );
        wp_enqueue_style( 'contact.css' );
        return $content;
    }
    add_shortcode('calendar', 'calendar_shortcode');
}
add_action('init', 'calendar_shortcodes_init');

/**
 * Register scripts
 */

function calendar_register_scripts() {
    wp_register_script( 'calendar.js', plugins_url( '/calendar.js', __FILE__ ) );
    wp_register_script( 'contact.js', plugins_url( '/contact.js', __FILE__ ) );
    wp_register_script( 'calendar_admin.js', plugins_url( '/calendar_admin.js', __FILE__) );
}
add_action( 'wp_enqueue_scripts', 'calendar_register_scripts' );

/**
 * Register stylesheets
 */

function calendar_register_styles() {
    wp_register_style( 'calendar.css', plugins_url( '/calendar.css', __FILE__ ) );
    wp_register_style( 'contact.css', plugins_url( '/contact.css', __FILE__ ) );
}
add_action( 'wp_enqueue_scripts', 'calendar_register_styles' );

/**
 * Admin menu
 */
function calendar_admin_menu_page_init() {
	function calendar_admin_menu_page() {
		global $wpdb;
		$options = get_option(CALENDAR_OPTIONS);
		$per_week_start = $options['per_week_start'];
		$per_week_end = $options['per_week_end'];
		$sql = "SELECT * FROM ".CALENDAR_APARTMENTS.";";
		$apartments = $wpdb->get_results($sql);
        $sql = "SELECT * FROM ".CALENDAR_RESERVATIONS.";";
        $reservations = $wpdb->get_results($sql);
        $sql = "SELECT * FROM ".CALENDAR_PRICES.";";
        $prices = $wpdb->get_results($sql);
		?>
            <script>
                var admin_calendar_data = {
                    per_week_start:<?php echo json_encode($per_week_start); ?>,
                    per_week_end:<?php echo json_encode($per_week_end); ?>,
                    apartments:<?php echo json_encode($apartments); ?>,
                    reservations:<?php echo json_encode($reservations); ?>,
                    prices:<?php echo json_encode($prices); ?>
                };
            </script>
			<div class="wrap">
                <h1>Rezervace</h1>
				<form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
                    <table class="form-table">

                        <tr valign="top">
                            <th scope="row">Apartment</th>
                            <td>
                                <select name="apartment">
                                    <?php
                                    foreach ($apartments as $apartment) {
                                        echo "<option value=\"$apartment->id\">$apartment->apartment</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">Reservace od</th>
                            <td><input type="date" name="reservation-start" /></td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">Reservace do</th>
                            <td><input type="date" name="reservation-end" /></td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">Cena</th>
                            <td><input type="number" name="price" /></td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">Popis</th>
                            <td><textarea name="description" rows="10" cols="60"></textarea></td>
                        </tr>

                        <input type="hidden" name="action" value="calendar_update_settings">
                        <input type="hidden" name="calendar-source-url" value="<?php echo($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>">
                    </table>
                    <?php submit_button("Uložit"); ?>
				</form>
			</div>
            <div class="wrap">
                <h1>Cena</h1>
                <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
                    <table class="form-table">

                        <tr valign="top">
                            <th scope="row">Apartment</th>
                            <td>
                                <select name="apartment">
                                    <?php
                                    foreach ($apartments as $apartment) {
                                        echo "<option value=\"$apartment->id\">$apartment->apartment</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">Cena od</th>
                            <td><input type="date" name="price-start" /></td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">Cena do</th>
                            <td><input type="date" name="price-end" /></td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">Cena</th>
                            <td><input type="number" name="price" /></td>
                        </tr>

                        <input type="hidden" name="action" value="calendar_update_settings">
                        <input type="hidden" name="calendar-source-url" value="<?php echo($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>">
                    </table>
                    <?php submit_button("Uložit"); ?>
                </form>
            </div>
            <div class="wrap">
                <h1>Apartmán</h1>
                <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
                    <table class="form-table">

                        <tr valign="top">
                            <th scope="row">Jméno</th>
                            <td><input type="text" name="apartment-name" /></td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">Popis</th>
                            <td><textarea name="description" rows="10" cols="60"></textarea></td>
                        </tr>

                        <input type="hidden" name="action" value="calendar_update_settings">
                        <input type="hidden" name="calendar-source-url" value="<?php echo($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>">
                    </table>
                    <?php submit_button("Uložit"); ?>
                </form>
            </div>
		<?php
        wp_register_script( 'calendar_admin.js', plugins_url( '/calendar_admin.js', __FILE__ ) );
        wp_enqueue_script( 'calendar_admin.js' );
	}

	function calendar_settings_admin_submenu_page() {
		$options = get_option(CALENDAR_OPTIONS);
		?>
		<div class="wrap">
			<h1>Nastavení</h1>

			<form method="post" action="options.php">
				<?php
				settings_fields( CALENDAR_OPTIONS );
				do_settings_sections( CALENDAR_OPTIONS );
				?>
				<table class="form-table">

                    <tr valign="top">
                        <th scope="row">Per Week Start</th>
                        <td><input type="date" name="<?php echo(CALENDAR_OPTIONS) ?>[per_week_start]" value="<?php echo $options['per_week_start']; ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Per Week End</th>
                        <td><input type="date" name="<?php echo(CALENDAR_OPTIONS) ?>[per_week_end]" value="<?php echo $options['per_week_end']; ?>" /></td>
                    </tr>

                </table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
	add_menu_page("Kalendář", "Kalendář", "edit_pages", "calendar", "calendar_admin_menu_page",'', 10 );
	add_submenu_page("calendar", "Nastavení", "Nastavení", "edit_pages", "settings", "calendar_settings_admin_submenu_page");
}

function calendar_register_settings() {
	register_setting( CALENDAR_OPTIONS, CALENDAR_OPTIONS );
}

add_action( 'admin_menu', 'calendar_admin_menu_page_init' );
add_action( 'admin_init', 'calendar_register_settings');

/**
 * Handle POST requests where calendar_update_table action was supplied
 */

function calendar_update_settings_process_posted_data() {

	global $wpdb;
	$wasSuccessful = false;

	function isArraySet($array) {
	    $isSet = true;
	    foreach ($array as $item) {
	        if (!isset($item)) $isSet = false;
        }
        return $isSet;
    }

	$reservationData = array(
        'apartment_id' => $_POST['apartment'],
        'start_date' => $_POST['reservation-start'],
        'end_date' => $_POST['reservation-end'],
        'price' => $_POST['price'],
        'description' => $_POST['description']
    );
	$priceData = array(
        'apartment_id' => $_POST['apartment'],
        'start_date' => $_POST['price-start'],
        'end_date' => $_POST['price-end'],
        'price' => $_POST['price']
    );
    $apartmentData = array(
        'apartment' => $_POST['apartment-name'],
        'description' => $_POST['description']
    );

	if (isArraySet($reservationData)) {
        $wasSuccessful = $wpdb->insert(
                CALENDAR_RESERVATIONS,
                $reservationData
        );
	} else if (isArraySet($priceData)) {
	    $wasSuccessful = $wpdb->insert(
	            CALENDAR_PRICES,
                $priceData
        );
    } else if (isArraySet($apartmentData)) {
	    $wasSuccessful = $wpdb->insert(
	            CALENDAR_APARTMENTS,
                $apartmentData
        );
    }

    $argKey = $wasSuccessful ? "success" : "error";
	$argValue = $wasSuccessful ? "The%20data%20was%20inserted" : "An%20error%20occurred%20while%20inserting%20data";
	if (isset($_POST['calendar-source-url'])) {
		wp_redirect(esc_url(add_query_arg($argKey, $argValue, $_POST['calendar-source-url'])));
	} else {
		wp_redirect(add_query_arg($argKey, $argValue, admin_url()));
	}
}
add_action( 'admin_post_calendar_update_settings', 'calendar_update_settings_process_posted_data');

/**
 * Handle POST requests where calendar_contact_form action was supplied
 */

function calendar_contact_form_process_posted_data() {
	$to = "flejhanec@seznam.cz";
	$subject = "New reservation";
	$message = "New reservation\r\n";
	$form_items = array($_POST['calendar-name'], $_POST['calendar-mail'], $_POST['calendar-apartment'], $_POST['calendar-start-date'], $_POST['calendar-end-date'], $_POST['calendar-comment']);
	$form_items_names = array("From", "E-mail", "Apartment", "Start Date", "End Date", "Comment");

	for ($i = 0; $i < count($form_items); $i++) {
		if (isset($form_items[$i])) {
			$message .= $form_items_names[$i] . ": " . $form_items[$i] . "\r\n";
		}
	}

	if (wp_mail($to, $subject, $message)) {
		echo ("Zpráva byla úspěšně odeslána");
	} else {
		echo ("Něco se pokazilo, kontaktujte adresáta přímo na " . $to);
	}
}
add_action( 'admin_post_calendar_contact_form', 'calendar_contact_form_process_posted_data');
add_action( 'admin_post_nopriv_calendar_contact_form', 'calendar_contact_form_process_posted_data');
