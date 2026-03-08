<?php
/**
 * Partial: Recommendation Card — Illusia Theme Override
 *
 * Redesenho completo do card de recomendação com markup original Illusia,
 * consumindo a mesma lógica PHP do Fictioneer (links externos, taxonomias,
 * hooks).
 *
 * Nota: Recommendations NÃO têm footer (class _no-footer).
 * O autor vem de meta field (fictioneer_recommendation_author), não do WP.
 *
 * @package Illusia
 * @since 1.8.0
 *
 * @internal $args['show_type']  Whether to show the post type label. Unsafe.
 * @internal $args['cache']      Whether to account for active caching. Unsafe.
 * @internal $args['order']      Current order. Default 'desc'. Unsafe.
 * @internal $args['orderby']    Current orderby. Default 'modified'. Unsafe.
 */

// No direct access!
defined( 'ABSPATH' ) OR exit;

// ─── Setup ──────────────────────────────────────────────────────────────────
$post_id = $post->ID;
$title   = fictioneer_get_safe_title( $post_id, 'card-recommendation' );

$links = array_merge(
  fictioneer_url_list_to_array( get_post_meta( $post_id, 'fictioneer_recommendation_urls', true ) ),
  fictioneer_url_list_to_array( get_post_meta( $post_id, 'fictioneer_recommendation_support', true ) )
);

$excerpt      = get_the_excerpt();
$one_sentence = get_post_meta( $post_id, 'fictioneer_recommendation_one_sentence', true );
$rec_author   = get_post_meta( $post_id, 'fictioneer_recommendation_author', true );

// Use the longer text between excerpt and one_sentence
$display_text = ( mb_strlen( $one_sentence ) >= mb_strlen( $excerpt ) )
  ? wp_strip_all_tags( $one_sentence, true )
  : $excerpt;

// ─── Taxonomies ─────────────────────────────────────────────────────────────
$tags       = false;
$fandoms    = false;
$characters = false;
$genres     = false;

if (
  get_option( 'fictioneer_show_tags_on_recommendation_cards' ) &&
  ! get_option( 'fictioneer_hide_taxonomies_on_recommendation_cards' )
) {
  $tags = get_the_tags();
}

if ( ! get_option( 'fictioneer_hide_taxonomies_on_recommendation_cards' ) ) {
  $fandoms    = get_the_terms( $post_id, 'fcn_fandom' );
  $characters = get_the_terms( $post_id, 'fcn_character' );
  $genres     = get_the_terms( $post_id, 'fcn_genre' );
}

// ─── Flags ──────────────────────────────────────────────────────────────────
$show_terms = ! get_option( 'fictioneer_hide_taxonomies_on_recommendation_cards' ) &&
  ( $tags || $genres || $fandoms || $characters );
$has_cover  = has_post_thumbnail() &&
  get_theme_mod( 'card_image_style', 'default' ) !== 'none';

// ─── Card classes (Illusia BEM) ─────────────────────────────────────────────
$card_classes = [ 'illusia-card', 'illusia-card--recommendation', 'illusia-card--no-footer' ];

if ( ! $has_cover ) {
  $card_classes[] = 'illusia-card--no-cover';
}

if ( ! $show_terms ) {
  $card_classes[] = 'illusia-card--no-tax';
}

// Fictioneer compat classes
$card_classes[] = 'card';
$card_classes[] = '_recommendation';
$card_classes[] = '_large';
$card_classes[] = '_no-footer';

// ─── Card attributes (hook) ────────────────────────────────────────────────
$attributes = apply_filters( 'fictioneer_filter_card_attributes', [], $post, 'card-recommendation' );
$card_attributes = '';

foreach ( $attributes as $key => $value ) {
  $card_attributes .= esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
}

// ─── Thumbnail attributes ──────────────────────────────────────────────────
$thumbnail_args = array(
  'alt'   => sprintf( __( '%s Cover', 'fictioneer' ), $title ),
  'class' => 'illusia-card__cover-img no-auto-lightbox'
);

?>

<li
  id="recommendation-card-<?php echo esc_attr( $post_id ); ?>"
  class="post-<?php echo esc_attr( $post_id ); ?> <?php echo implode( ' ', $card_classes ); ?>"
  <?php echo $card_attributes; ?>
>
  <article class="illusia-card__body">

    <?php
      // ─── Action hook ──────────────────────────────────────────────────
      do_action( 'fictioneer_large_card_body_recommendation', $post, $args );
    ?>

    <?php if ( $has_cover ) : ?>
      <div class="illusia-card__cover">
        <a
          href="<?php the_permalink(); ?>"
          class="illusia-card__cover-link"
          title="<?php echo esc_attr( sprintf( __( '%s Cover', 'fictioneer' ), $title ) ); ?>"
          <?php echo fictioneer_get_lightbox_attribute(); ?>
        >
          <?php echo get_the_post_thumbnail( null, 'cover', $thumbnail_args ); ?>
          <div class="illusia-card__cover-overlay" aria-hidden="true"></div>
        </a>
      </div>
    <?php endif; ?>

    <header class="illusia-card__header">
      <div class="illusia-card__title-group">

        <?php if ( $args['show_type'] ?? false ) : ?>
          <span class="illusia-card__label"><?php
            _ex( 'Recommendation', 'Recommendation card label.', 'fictioneer' );
          ?></span>
        <?php endif; ?>

        <h3 class="illusia-card__title">
          <a href="<?php the_permalink(); ?>" class="illusia-card__title-link"><?php
            echo esc_html( $title );
          ?></a>
        </h3>

        <?php if ( ! empty( $rec_author ) ) : ?>
          <p class="illusia-card__author"><?php
            printf(
              _x( 'por %s', 'Illusia card: por {Author}.', 'fictioneer' ),
              esc_html( $rec_author )
            );
          ?></p>
        <?php endif; ?>

      </div>
    </header>

    <?php if ( $has_cover ) : ?>
      <div class="illusia-card__excerpt-inline"><?php
        echo ! empty( $display_text ) ? wp_kses_post( $display_text ) : esc_html__( 'No description provided yet.', 'fictioneer' );
      ?></div>
    <?php endif; ?>

    <div class="illusia-card__middle">

      <div class="illusia-card__excerpt<?php echo empty( $display_text ) ? ' illusia-card__excerpt--empty' : ''; ?>"><?php
        echo ! empty( $display_text ) ? wp_kses_post( $display_text ) : esc_html__( 'No description provided yet.', 'fictioneer' );
      ?></div>

      <?php if ( count( $links ) > 0 ) : ?>
        <div class="illusia-card__external-links">

          <div class="illusia-card__readings-label"><?php
            esc_html_e( 'Links', 'fictioneer' );
          ?></div>

          <ol class="illusia-links-list">
            <?php foreach ( $links as $link ) : ?>
              <li class="illusia-links-list__item">
                <div class="illusia-links-list__left">
                  <i class="fa-solid fa-arrow-up-right-from-square illusia-links-list__icon" aria-hidden="true"></i>
                  <a
                    href="<?php echo esc_url( $link['url'] ); ?>"
                    rel="noopener nofollow"
                    target="_blank"
                    class="illusia-links-list__link"
                  ><?php echo esc_html( $link['name'] ); ?></a>
                </div>
              </li>
            <?php endforeach; ?>
          </ol>
        </div>
      <?php endif; ?>

    </div>

    <?php
      if ( $show_terms ) {
        $terms = array_merge(
          $fandoms ? fictioneer_get_term_nodes( $fandoms, '_inline _fandom' ) : [],
          $genres ? fictioneer_get_term_nodes( $genres, '_inline _genre' ) : [],
          $tags ? fictioneer_get_term_nodes( $tags, '_inline _tag' ) : [],
          $characters ? fictioneer_get_term_nodes( $characters, '_inline _character' ) : []
        );

        $terms = apply_filters(
          'fictioneer_filter_card_recommendation_terms',
          $terms, $post, $args, null
        );
      }
    ?>

    <?php if ( $show_terms && ! empty( $terms ) ) : ?>
      <div class="illusia-card__taxonomies"><?php
        echo implode( ' ', $terms );
      ?></div>
    <?php endif; ?>

  </article>
</li>
