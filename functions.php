<?php

// =============================================================================
// CONSTANTS
// =============================================================================

define( 'CHILD_VERSION', '1.11.2' );
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

  // Page style: Illusia Frame (moldura decorativa global)
  wp_enqueue_style(
    'illusia-page-style-frame',
    get_stylesheet_directory_uri() . '/css/illusia-page-style-frame.css',
    ['illusia-properties'],
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

  // Componentes: collections page (archive) — só na página /collections/
  if ( is_page_template( 'collections.php' ) ) {
    wp_enqueue_style(
      'illusia-collections',
      get_stylesheet_directory_uri() . '/css/components/illusia-collections.css',
      ['illusia-properties', 'illusia-cards'],
      CHILD_VERSION
    );
  }

  // Componentes: list pages base (chapters, recommendations, archives)
  $needs_list_pages = is_page_template( 'chapters.php' )
    || is_page_template( 'recommendations.php' )
    || is_archive();

  if ( $needs_list_pages ) {
    wp_enqueue_style(
      'illusia-list-pages',
      get_stylesheet_directory_uri() . '/css/components/illusia-list-pages.css',
      ['illusia-properties', 'illusia-cards'],
      CHILD_VERSION
    );
  }

  // Componentes: taxonomy archives (genre, fandom, character, warning, category, tag)
  if ( is_archive() && ! is_author() && ! is_date() && ! is_post_type_archive() ) {
    wp_enqueue_style(
      'illusia-archives',
      get_stylesheet_directory_uri() . '/css/components/illusia-archives.css',
      ['illusia-properties', 'illusia-list-pages'],
      CHILD_VERSION
    );

    // Collapsible tax cloud toggle
    wp_enqueue_script(
      'illusia-archive-cloud',
      get_stylesheet_directory_uri() . '/js/illusia-archive-cloud.js',
      [],
      CHILD_VERSION,
      true
    );
  }
}
add_action( 'wp_enqueue_scripts', 'illusia_enqueue_styles_and_scripts', 99 );

/**
 * Add "Illusia Frame" to the Customizer Page Style dropdown.
 *
 * Adds a decorative border + amber corner ornament option that
 * applies globally to `.main__wrapper` when selected.
 *
 * @since 1.10.1
 *
 * @param array $styles Existing page style choices.
 * @return array Modified choices with Illusia Frame added.
 */
function illusia_add_page_styles( array $styles ): array {
  // Insert before 'none' (last option) for logical ordering
  $position = array_search( 'none', array_keys( $styles ) );

  if ( $position !== false ) {
    $before = array_slice( $styles, 0, $position, true );
    $after  = array_slice( $styles, $position, null, true );

    return $before + ['illusia-frame' => _x( 'Illusia Frame', 'Customizer page style option.', 'fictioneer' )] + $after;
  }

  $styles['illusia-frame'] = _x( 'Illusia Frame', 'Customizer page style option.', 'fictioneer' );

  return $styles;
}
add_filter( 'fictioneer_filter_customizer_page_style', 'illusia_add_page_styles' );

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
