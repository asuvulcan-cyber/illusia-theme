<?php

// =============================================================================
// CONSTANTS
// =============================================================================

define( 'CHILD_VERSION', '1.1.4' );
define( 'CHILD_NAME', 'Illusia Theme' );

// =============================================================================
// CHILD THEME SETUP
// =============================================================================

/**
 * Enqueue child theme styles and scripts
 */

function illusia_enqueue_styles_and_scripts() {
  // Google Fonts: Playfair Display (headings) + Syne (UI)
  wp_enqueue_style(
    'illusia-google-fonts',
    'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Syne:wght@400;500;600;700&display=swap',
    [],
    null
  );

  // Override das custom properties do Fictioneer
  wp_enqueue_style(
    'illusia-properties',
    get_stylesheet_directory_uri() . '/css/illusia-properties.css',
    ['fictioneer-application', 'illusia-google-fonts'],
    CHILD_VERSION
  );
}
add_action( 'wp_enqueue_scripts', 'illusia_enqueue_styles_and_scripts', 99 );

/**
 * Add or remove parent filters and actions on the frontend
 */

function illusia_customize_parent() {
  /*

  // Example: Prevent custom story/chapter CSS from being applied

  remove_action( 'wp_head', 'fictioneer_add_fiction_css', 10 );

  */
}
add_action( 'init', 'illusia_customize_parent' );

/**
 * Use the following hook if 'init' does not work
 */

// add_action( 'wp', 'fictioneer_child_customize_parent', 11 );

/**
 * Add or remove filters and actions in the admin panel
 */

function illusia_customize_admin() {
  /*

  // Example: Remove SEO meta box for non-administrators

  if ( ! current_user_can( 'administrator' ) ) {
    remove_action( 'add_meta_boxes', 'fictioneer_add_seo_metabox', 10 );
  }

  */
}
add_action( 'admin_init', 'illusia_customize_admin' );
