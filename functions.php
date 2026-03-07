<?php

// =============================================================================
// CONSTANTS
// =============================================================================

define( 'CHILD_VERSION', '1.6.0' );
define( 'CHILD_NAME', 'Illusia Theme' );

// =============================================================================
// CHILD THEME SETUP
// =============================================================================

/**
 * Enqueue child theme styles and scripts.
 *
 * @since 1.0.0
 */
function illusia_enqueue_styles_and_scripts(): void {
  // Override das custom properties do Fictioneer
  wp_enqueue_style(
    'illusia-properties',
    get_stylesheet_directory_uri() . '/css/illusia-properties.css',
    ['fictioneer-application'],
    CHILD_VERSION
  );

  // Atmosfera global (grain, scrollbar)
  wp_enqueue_style(
    'illusia-atmosphere',
    get_stylesheet_directory_uri() . '/css/illusia-atmosphere.css',
    ['illusia-properties'],
    CHILD_VERSION
  );

  // Componentes: botões
  wp_enqueue_style(
    'illusia-buttons',
    get_stylesheet_directory_uri() . '/css/components/illusia-buttons.css',
    ['illusia-properties'],
    CHILD_VERSION
  );

  // Componentes: badges, tags, rating labels
  wp_enqueue_style(
    'illusia-badges',
    get_stylesheet_directory_uri() . '/css/components/illusia-badges.css',
    ['illusia-properties'],
    CHILD_VERSION
  );

  // Componentes: story cards
  wp_enqueue_style(
    'illusia-cards',
    get_stylesheet_directory_uri() . '/css/components/illusia-cards.css',
    ['illusia-properties', 'illusia-badges'],
    CHILD_VERSION
  );

  // Componentes: stories page (archive) — só na página /stories/
  if ( is_page_template( 'stories.php' ) ) {
    wp_enqueue_style(
      'illusia-stories',
      get_stylesheet_directory_uri() . '/css/components/illusia-stories.css',
      ['illusia-properties', 'illusia-cards'],
      CHILD_VERSION
    );
  }
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
