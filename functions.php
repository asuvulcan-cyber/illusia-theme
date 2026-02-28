<?php

// =============================================================================
// CONSTANTS
// =============================================================================

define( 'CHILD_VERSION', '1.1.3' );
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
 * Override Fictioneer customize.css (enqueued at priority 9999)
 * Only Illusia visual identity — fonts and title colors.
 * Layout/sizing vars are left to the Customizer.
 */

function illusia_override_customizer_css() {
  $css = ':root {
    --ff-base: var(--ff-ui);
    --ff-heading: var(--ff-display);
    --ff-note: var(--ff-ui);
    --ff-mono: "Fira Code", monospace;
    --ff-input: var(--ff-ui);
    --ff-card-title: var(--ff-display);
    --ff-site-title: var(--ff-display);
    --ff-story-title: var(--ff-display);
    --ff-chapter-title: var(--ff-display);
    --ff-chapter-list-title: var(--ff-ui);
    --ff-card-body: var(--ff-ui);
    --ff-nav-item: var(--ff-ui);
    --site-title-heading-color: var(--fg-100);
    --site-title-tagline-color: var(--fg-950);
    --site-title-text-shadow: none;
  }';

  wp_register_style( 'illusia-overrides', false, [ 'fictioneer-application' ], CHILD_VERSION );
  wp_enqueue_style( 'illusia-overrides' );
  wp_add_inline_style( 'illusia-overrides', $css );
}
add_action( 'wp_enqueue_scripts', 'illusia_override_customizer_css', 10000 );

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
