<?php
/**
 * Character taxonomy archive — Illusia Override
 *
 * Roteia por tipo de personagem (illusia_char_type):
 *   - 'personagem': renderiza ficha de personagem (character sheet)
 *   - 'obra'/'local'/'organizacao': renderiza índice hierárquico (tree)
 *   - sem tipo: fallback para archive padrão com tax cloud
 *
 * @package Illusia Theme
 * @since 1.11.0
 * @modified 1.12.0 — type-based routing + character sheet system
 * @see fictioneer/taxonomy-fcn_character.php (template pai)
 */

// No direct access!
defined( 'ABSPATH' ) OR exit;

// Header
get_header();

// Setup
$term = get_queried_object();
$parent = get_term_by( 'id', $term->parent, get_query_var( 'taxonomy' ) );
$taxonomy_slug = 'fcn_character';
$taxonomy_color = 'amber';

// Detect character type
$char_type = illusia_get_char_type( $term->term_id );
$taxonomy_label = ! empty( $char_type )
  ? illusia_get_char_type_label( $char_type )
  : 'Personagem';

// Get full meta for the current term
$meta = illusia_get_char_meta( $term->term_id );

// Tax cloud — only show 'personagem' type terms for character archives
$cloud_terms = get_terms( array(
  'taxonomy'   => $taxonomy_slug,
  'exclude'    => $term->term_id,
  'hide_empty' => true,
  'pad_counts' => true,
  'orderby'    => 'count',
  'order'      => 'DESC',
  'meta_query' => array(
    array(
      'key'   => 'illusia_char_type',
      'value' => 'personagem',
    ),
  ),
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

<main id="main" class="main archive illusia-list-page illusia-archive illusia-archive--<?php echo esc_attr( $taxonomy_color ); ?> character-archive">

  <?php do_action( 'fictioneer_main', 'character-archive' ); ?>

  <div class="main__wrapper">

    <?php do_action( 'fictioneer_main_wrapper' ); ?>

    <article class="archive__article illusia-archive__article">

      <header class="illusia-archive__header">
        <span class="illusia-archive__overline illusia-archive__overline--<?php echo esc_attr( $taxonomy_color ); ?>"><?php
          echo esc_html( $taxonomy_label );
        ?></span>

        <h1 class="illusia-archive__title"><?php echo esc_html( single_tag_title( '', false ) ); ?></h1>

        <?php if ( $parent ) : ?>
          <span class="illusia-archive__parent"><?php
            echo esc_html( sprintf( _x( '(%s)', 'Taxonomy page parent suffix.', 'fictioneer' ), $parent->name ) );
          ?></span>
        <?php endif; ?>

        <?php if ( ! empty( $meta['image'] ) && $char_type !== 'personagem' ) : ?>
          <div class="illusia-archive__header-image">
            <img src="<?php echo esc_url( $meta['image'] ); ?>"
                 alt="<?php echo esc_attr( $term->name ); ?>" />
          </div>
        <?php endif; ?>

        <span class="illusia-archive__count"><?php
          $result_count = $wp_query->found_posts;
          echo esc_html( sprintf(
            _n( '%s resultado', '%s resultados', $result_count, 'fictioneer' ),
            number_format_i18n( $result_count )
          ) );
        ?></span>

        <?php if ( ! empty( $term->description ) && $char_type !== 'personagem' ) : ?>
          <p class="illusia-archive__description"><?php echo esc_html( $term->description ); ?></p>
        <?php endif; ?>
      </header>

      <div class="illusia-archive__divider" aria-hidden="true">
        <span class="illusia-archive__diamond"></span>
      </div>

      <?php
      // =====================================================================
      // TYPE-BASED ROUTING
      // =====================================================================

      if ( $char_type === 'personagem' ) :
        // Character sheet — ficha de personagem
        include get_stylesheet_directory() . '/partials/_character-sheet.php';

        // Tax cloud below the sheet
        if ( ! empty( $tax_cloud ) ) : ?>
          <nav class="illusia-archive__cloud illusia-archive__cloud--<?php echo esc_attr( $taxonomy_color ); ?>"
               aria-label="Outros personagens">
            <span class="illusia-archive__cloud-label">Ver também</span>
            <div class="illusia-archive__cloud-items">
              <?php echo $tax_cloud; // phpcs:ignore -- wp_generate_tag_cloud() returns safe HTML ?>
            </div>
            <button class="illusia-archive__cloud-toggle" type="button" hidden>
              <span class="illusia-archive__cloud-toggle-more">Ver todos</span>
              <span class="illusia-archive__cloud-toggle-less">Recolher</span>
            </button>
          </nav>
        <?php endif;

      elseif ( in_array( $char_type, array( 'obra', 'local', 'organizacao' ), true ) ) :
        // Hierarchical index — árvore de filhos
        include get_stylesheet_directory() . '/partials/_character-index.php';

        // Archive loop below the index (stories tagged with this term)
        fictioneer_get_template_part( 'partials/_archive-loop', null, array( 'taxonomy' => $taxonomy_slug ) );

      else :
        // Fallback — no type set, show standard archive with tax cloud
        if ( ! empty( $tax_cloud ) ) : ?>
          <nav class="illusia-archive__cloud illusia-archive__cloud--<?php echo esc_attr( $taxonomy_color ); ?>"
               aria-label="Personagens relacionados">
            <span class="illusia-archive__cloud-label">Ver também</span>
            <div class="illusia-archive__cloud-items">
              <?php echo $tax_cloud; // phpcs:ignore ?>
            </div>
            <button class="illusia-archive__cloud-toggle" type="button" hidden>
              <span class="illusia-archive__cloud-toggle-more">Ver todos</span>
              <span class="illusia-archive__cloud-toggle-less">Recolher</span>
            </button>
          </nav>
        <?php endif;

        fictioneer_get_template_part( 'partials/_archive-loop', null, array( 'taxonomy' => $taxonomy_slug ) );

      endif;
      ?>

    </article>

  </div>

  <?php do_action( 'fictioneer_main_end', 'character-archive' ); ?>

</main>

<?php
  $footer_args = array(
    'post_type' => null,
    'post_id' => null,
    'template' => 'taxonomy-fcn_character.php',
    'breadcrumbs' => array(
      [ fcntr( 'frontpage' ), get_home_url() ],
      [ sprintf( __( 'Character: %s', 'fictioneer' ), single_tag_title( '', false ) ), null ],
    ),
  );

  get_footer( null, $footer_args );
?>
