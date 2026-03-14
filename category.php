<?php
/**
 * Category archive — Illusia Override
 *
 * Redesenho do archive de categorias com header editorial,
 * tax cloud e card list Illusia.
 *
 * @package Illusia Theme
 * @since 1.11.0
 * @see fictioneer/category.php (template pai)
 */

// No direct access!
defined( 'ABSPATH' ) OR exit;

// Header
get_header();

// Setup
$term = get_queried_object();
$parent = get_term_by( 'id', $term->parent, 'category' );
$taxonomy_slug = 'category';
$taxonomy_label = 'Categoria';
$taxonomy_color = 'neutral';

// Tax cloud — related terms with at least 1 published item
$cloud_terms = get_terms( array(
  'taxonomy'   => $taxonomy_slug,
  'exclude'    => $term->term_id,
  'hide_empty' => true,
  'pad_counts' => true,
  'orderby'    => 'count',
  'order'      => 'DESC',
) );

if ( is_wp_error( $cloud_terms ) ) {
  $cloud_terms = array();
}

$cloud_terms = array_filter( $cloud_terms, function( $t ) {
  return $t->count > 0;
} );

$tax_cloud = '';

if ( ! empty( $cloud_terms ) ) {
  // wp_generate_tag_cloud() needs ->link on each term (wp_tag_cloud sets it automatically)
  foreach ( $cloud_terms as $ct ) {
    $ct->link = get_term_link( $ct );
  }

  $tax_cloud = wp_generate_tag_cloud( $cloud_terms, array(
    'smallest'   => .75,
    'largest'    => .75,
    'unit'       => 'rem',
    'show_count' => true,
    'orderby'    => 'count',
    'order'      => 'DESC',
  ) );
}

?>

<main id="main" class="main archive illusia-list-page illusia-archive illusia-archive--<?php echo esc_attr( $taxonomy_color ); ?> category-archive">

  <?php do_action( 'fictioneer_main', 'category-archive' ); ?>

  <div class="main__wrapper">

    <?php do_action( 'fictioneer_main_wrapper' ); ?>

    <article class="archive__article illusia-archive__article">

      <header class="illusia-archive__header">
        <span class="illusia-archive__overline illusia-archive__overline--<?php echo esc_attr( $taxonomy_color ); ?>"><?php
          echo esc_html( $taxonomy_label );
        ?></span>

        <h1 class="illusia-archive__title"><?php echo esc_html( single_cat_title( '', false ) ); ?></h1>

        <?php if ( $parent ) : ?>
          <span class="illusia-archive__parent"><?php
            echo esc_html( sprintf( _x( '(%s)', 'Taxonomy page parent suffix.', 'fictioneer' ), $parent->name ) );
          ?></span>
        <?php endif; ?>

        <span class="illusia-archive__count"><?php
          echo esc_html( sprintf(
            _n( '%s resultado', '%s resultados', $wp_query->found_posts, 'fictioneer' ),
            number_format_i18n( $wp_query->found_posts )
          ) );
        ?></span>

        <?php if ( ! empty( $term->description ) ) : ?>
          <p class="illusia-archive__description"><?php echo esc_html( $term->description ); ?></p>
        <?php endif; ?>
      </header>

      <div class="illusia-archive__divider" aria-hidden="true">
        <span class="illusia-archive__diamond"></span>
      </div>

      <?php if ( ! empty( $tax_cloud ) ) : ?>
        <nav class="illusia-archive__cloud illusia-archive__cloud--<?php echo esc_attr( $taxonomy_color ); ?>"
             aria-label="<?php esc_attr_e( 'Related categories', 'fictioneer' ); ?>">
          <span class="illusia-archive__cloud-label"><?php
            esc_html_e( 'Ver também', 'fictioneer' );
          ?></span>
          <div class="illusia-archive__cloud-items">
            <?php echo $tax_cloud; // phpcs:ignore -- wp_tag_cloud() returns safe HTML ?>
          </div>
          <button class="illusia-archive__cloud-toggle" type="button" hidden>
            <span class="illusia-archive__cloud-toggle-more"><?php esc_html_e( 'Ver todos', 'fictioneer' ); ?></span>
            <span class="illusia-archive__cloud-toggle-less"><?php esc_html_e( 'Recolher', 'fictioneer' ); ?></span>
          </button>
        </nav>
      <?php endif; ?>

      <?php fictioneer_get_template_part( 'partials/_archive-loop', null, array( 'taxonomy' => $taxonomy_slug ) ); ?>

    </article>

  </div>

  <?php do_action( 'fictioneer_main_end', 'category-archive' ); ?>

</main>

<?php
  $footer_args = array(
    'post_type' => null,
    'post_id' => null,
    'template' => 'category.php',
    'breadcrumbs' => array(
      [ fcntr( 'frontpage' ), get_home_url() ],
      [ sprintf( __( 'Category: %s', 'fictioneer' ), single_cat_title( '', false ) ), null ],
    ),
  );

  get_footer( null, $footer_args );
?>
