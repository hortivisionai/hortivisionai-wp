<?php
/**
 * Kadence functions and definitions
 *
 * This file must be parseable by PHP 5.2.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package kadence
 */

define( 'KADENCE_VERSION', '1.5.0' );
define( 'KADENCE_MINIMUM_WP_VERSION', '6.0' );
define( 'KADENCE_MINIMUM_PHP_VERSION', '7.4' );

// Bail if requirements are not met.
if ( version_compare( $GLOBALS['wp_version'], KADENCE_MINIMUM_WP_VERSION, '<' ) || version_compare( phpversion(), KADENCE_MINIMUM_PHP_VERSION, '<' ) ) {
	require get_template_directory() . '/inc/back-compat.php';
	return;
}
// Include WordPress shims.
require get_template_directory() . '/inc/wordpress-shims.php';

// Load the `kadence()` entry point function.
require get_template_directory() . '/inc/class-theme.php';

// Load the `kadence()` entry point function.
require get_template_directory() . '/inc/functions.php';

// Initialize the theme.
call_user_func( 'Kadence\kadence' );

// Log out user immediately after registration and redirect to login
add_action('user_registration_after_register_user_action', function() {
    if (is_user_logged_in()) {
        wp_logout();
        wp_redirect(home_url('/login'));
        exit;
    }
});

function hortivision_test_predict() {
    if (!isset($_GET['test_predict'])) return;

    $bil_path = '/Users/sam/Downloads/HSI_PACKAGE_6-10-2026/sample_input/A1_copper.bil';
    $hdr_path = '/Users/sam/Downloads/HSI_PACKAGE_6-10-2026/sample_input/A1_copper.bil.hdr';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://hortivision-ai-inference.onrender.com/predict');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array(
        'bil_file' => new CURLFile($bil_path, 'application/octet-stream', 'A1_copper.bil'),
        'hdr_file' => new CURLFile($hdr_path, 'application/octet-stream', 'A1_copper.bil.hdr'),
    ));

    $result = curl_exec($ch);
    $err    = curl_error($ch);
    curl_close($ch);

    echo '<pre>';
    echo $err ? 'CURL Error: ' . $err : $result;
    echo '</pre>';
    die();
}
add_action('init', 'hortivision_test_predict');

add_action('wp_logout', function() {
    wp_redirect(home_url('/'));
    exit;
});

// Redirect to Account page after login
add_filter('login_redirect', function($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
        return home_url('/account/');
    }
    return $redirect_to;
}, 10, 3);