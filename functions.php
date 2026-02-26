<?php

// =============================================================================
// CONSTANTS
// =============================================================================

define( 'CHILD_VERSION', '1.0.3' );
define( 'CHILD_NAME', 'Fictioneer Child Theme' );

// =============================================================================
// CHILD THEME SETUP
// =============================================================================

/**
 * Enqueue child theme styles and scripts
 */

function fictioneer_child_enqueue_styles_and_scripts() {
  /*

  // Example: Enqueue styles

  wp_enqueue_style(
    'fictioneer-child-style',
    get_stylesheet_directory_uri() . '/css/fictioneer-child-style.css',
    ['fictioneer-application']
  );

  // Example: Register and enqueue script in the footer

  wp_register_script(
    'fictioneer-child-script',
    get_stylesheet_directory_uri() . '/js/fictioneer-child-script.js',
    [],
    false,
    true // Or use array( 'strategy' => 'defer' )
  );

  wp_enqueue_script( 'fictioneer-child-script' );

  // Example: Register and enqueue deferred script with dependency

  wp_register_script(
    'fictioneer-another-child-script',
    get_stylesheet_directory_uri() . '/js/fictioneer-child-script.js',
    ['fictioneer-application-scripts'], // Parent theme dependency
    false,
    array( 'strategy' => 'defer' ) // Must be deferred or everything breaks
  );

  wp_enqueue_script( 'fictioneer-another-child-script' );

  */
}
add_action( 'wp_enqueue_scripts', 'fictioneer_child_enqueue_styles_and_scripts', 99 );

/**
 * Add or remove parent filters and actions on the frontend
 */

function fictioneer_child_customize_parent() {
  /*

  // Example: Prevent custom story/chapter CSS from being applied

  remove_action( 'wp_head', 'fictioneer_add_fiction_css', 10 );

  */
}
add_action( 'init', 'fictioneer_child_customize_parent' );

/**
 * Use the following hook if 'init' does not work
 */

// add_action( 'wp', 'fictioneer_child_customize_parent', 11 );

/**
 * Add or remove filters and actions in the admin panel
 */

function fictioneer_child_customize_admin() {
  /*

  // Example: Remove SEO meta box for non-administrators

  if ( ! current_user_can( 'administrator' ) ) {
    remove_action( 'add_meta_boxes', 'fictioneer_add_seo_metabox', 10 );
  }

  */
}
add_action( 'admin_init', 'fictioneer_child_customize_admin' );

?>
