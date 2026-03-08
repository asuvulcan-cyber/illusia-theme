<?php
/**
 * Partial: Chapter Card — Illusia Theme Override
 *
 * Redesenho completo do card de capítulo com markup original Illusia,
 * consumindo a mesma lógica PHP do Fictioneer (taxonomias, hooks,
 * Stimulus controllers).
 *
 * Abordagem: template override (child theme > parent theme via
 * get_template_part). Todo o HTML é original; toda a lógica é do pai.
 *
 * @package Illusia
 * @since 1.8.0
 *
 * @internal $args['show_type']    Whether to show the post type label. Unsafe.
 * @internal $args['cache']        Whether to account for active caching. Unsafe.
 * @internal $args['hide_author']  Whether to hide the author. Unsafe.
 * @internal $args['order']        Current order. Default 'desc'. Unsafe.
 * @internal $args['orderby']      Current orderby. Default 'modified'. Unsafe.
 */

use Fictioneer\Utils;

// No direct access!
defined( 'ABSPATH' ) OR exit;

// ─── Setup ──────────────────────────────────────────────────────────────────
$post_id   = $post->ID;
$title     = fictioneer_get_safe_title( $post_id, 'card-chapter' );
$story_id  = fictioneer_get_chapter_story_id( $post_id );
$story_post = get_post( $story_id );
$story_unpublished = get_post_status( $story_id ) !== 'publish';
$story_data = $story_id ? \Fictioneer\Story::get_data( $story_id, false ) : null;

$chapter_rating = get_post_meta( $post_id, 'fictioneer_chapter_rating', true );

if ( ! $chapter_rating && $story_id ) {
  $chapter_rating = get_post_meta( $story_id, 'fictioneer_story_rating', true );
}

$story_thumbnail_url_full = $story_id ? get_the_post_thumbnail_url( $story_id, 'full' ) : null;
$text_icon = get_post_meta( $post_id, 'fictioneer_chapter_text_icon', true );

$list_title = trim( wp_strip_all_tags(
  get_post_meta( $post_id, 'fictioneer_chapter_list_title', true )
) );

$excerpt = fictioneer_get_forced_excerpt( $post, 512, true );

// ─── Taxonomies ─────────────────────────────────────────────────────────────
$tags       = false;
$fandoms    = false;
$characters = false;
$genres     = false;

if (
  get_option( 'fictioneer_show_tags_on_chapter_cards' ) &&
  ! get_option( 'fictioneer_hide_taxonomies_on_chapter_cards' )
) {
  $tags = get_the_tags();
}

if ( ! get_option( 'fictioneer_hide_taxonomies_on_chapter_cards' ) ) {
  $fandoms    = get_the_terms( $post_id, 'fcn_fandom' );
  $characters = get_the_terms( $post_id, 'fcn_character' );
  $genres     = get_the_terms( $post_id, 'fcn_genre' );
}

// ─── Flags ──────────────────────────────────────────────────────────────────
$hide_author = ( $args['hide_author'] ?? false ) || ! get_option( 'fictioneer_show_authors' );
$show_terms  = ! get_option( 'fictioneer_hide_taxonomies_on_chapter_cards' ) &&
  ( $tags || $fandoms || $characters || $genres );

$has_chapter_thumb = has_post_thumbnail();
$has_story_thumb   = ! empty( $story_thumbnail_url_full );
$has_text_icon     = ! empty( $text_icon );
$card_image_style  = get_theme_mod( 'card_image_style', 'default' );
$has_cover         = $card_image_style !== 'none' &&
  ( $has_chapter_thumb || $has_story_thumb || $has_text_icon );

// ─── Card classes (Illusia BEM) ─────────────────────────────────────────────
$card_classes = [ 'illusia-card', 'illusia-card--chapter' ];

if ( $story_unpublished ) {
  $card_classes[] = 'illusia-card--story-unpublished';
}

if ( ! $has_cover ) {
  $card_classes[] = 'illusia-card--no-cover';
}

if ( ! $show_terms ) {
  $card_classes[] = 'illusia-card--no-tax';
}

// Keep Fictioneer's card + _large + _chapter for Stimulus controller compatibility
$card_classes[] = 'card';
$card_classes[] = '_large';
$card_classes[] = '_chapter';

// ─── Card attributes (hook) ────────────────────────────────────────────────
$attributes = apply_filters( 'fictioneer_filter_card_attributes', [], $post, 'card-chapter' );
$card_attributes = '';

foreach ( $attributes as $key => $value ) {
  $card_attributes .= esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
}

// ─── Thumbnail attributes ──────────────────────────────────────────────────
$thumbnail_args = array(
  'alt'   => sprintf( __( '%s Cover', 'fictioneer' ), $title ),
  'class' => 'illusia-card__cover-img no-auto-lightbox'
);

// ─── Footer items ───────────────────────────────────────────────────────────
$icon_words = Utils::get_theme_icon(
  'icon_words',
  '<i class="fa-solid fa-font"></i>',
  array(
    'class' => 'illusia-meta-icon',
    'title' => __( 'Words', 'fictioneer' )
  )
);

$footer_items = [];
$words = fictioneer_get_word_count( $post_id );

if ( $words ) {
  $footer_items['words'] = '<span class="illusia-meta-item">' . $icon_words . ' ' . fictioneer_shorten_number( $words ) . '</span>';
}

if ( ( $args['orderby'] ?? 0 ) === 'date' ) {
  $footer_date_human = sprintf(
    _x( 'há %s', 'Illusia card: time ago.', 'fictioneer' ),
    human_time_diff( get_the_time( 'U' ), time() )
  );
  $footer_items['publish_date'] = '<span class="illusia-meta-item"><i class="illusia-meta-icon fa-solid fa-clock" title="' . esc_attr( get_the_date() ) . '"></i> ' . esc_html( $footer_date_human ) . '</span>';
} else {
  $footer_date_human = sprintf(
    _x( 'há %s', 'Illusia card: time ago.', 'fictioneer' ),
    human_time_diff( get_the_modified_time( 'U' ), time() )
  );
  $footer_items['modified_date'] = '<span class="illusia-meta-item"><i class="illusia-meta-icon fa-regular fa-clock" title="' . esc_attr( get_the_modified_date() ) . '"></i> ' . esc_html( $footer_date_human ) . '</span>';
}

if ( get_option( 'fictioneer_show_authors' ) && ! $hide_author ) {
  $footer_items['author'] = '<span class="illusia-meta-item"><i class="illusia-meta-icon fa-solid fa-circle-user"></i> ' . fictioneer_get_author_node( get_the_author_meta( 'ID' ) ) . '</span>';
}

$footer_items['comments'] = '<span class="illusia-meta-item"><i class="illusia-meta-icon fa-solid fa-message" title="' . esc_attr__( 'Comments', 'fictioneer' ) . '"></i> ' . get_comments_number( $post ) . '</span>';

$footer_items = apply_filters( 'fictioneer_filter_chapter_card_footer', $footer_items, $post, $story_data, $args );

// ─── Rating badge ───────────────────────────────────────────────────────────
$rating_lower  = $chapter_rating ? strtolower( $chapter_rating ) : '';
$rating_letter = $chapter_rating ? $chapter_rating[0] : '';

?>

<li
  id="chapter-card-<?php echo esc_attr( $post_id ); ?>"
  class="post-<?php echo esc_attr( $post_id ); ?> <?php echo implode( ' ', $card_classes ); ?>"
  data-unavailable="<?php esc_attr_e( 'Unavailable', 'fictioneer' ); ?>"
  data-controller="fictioneer-large-card"
  data-fictioneer-large-card-post-id-value="<?php echo esc_attr( $post_id ); ?>"
  data-fictioneer-large-card-story-id-value="<?php echo esc_attr( $story_id ); ?>"
  data-action="click->fictioneer-large-card#cardClick"
  <?php echo $card_attributes; ?>
>
  <article class="illusia-card__body">

    <?php
      // ─── Action hook ──────────────────────────────────────────────────
      do_action( 'fictioneer_large_card_body_chapter', $post, $story_data, $args );
    ?>

    <?php if ( $has_cover ) : ?>
      <div class="illusia-card__cover">

        <?php if ( $has_text_icon && ! $has_chapter_thumb && ! $has_story_thumb ) : ?>
          <?php // ─── Text icon cover ──────────────────────────────────── ?>
          <a
            href="<?php the_permalink(); ?>"
            class="illusia-card__cover-link illusia-card__cover-link--text-icon"
            title="<?php echo esc_attr( $title ); ?>"
          >
            <span class="illusia-card__text-icon"><?php echo esc_html( $text_icon ); ?></span>
          </a>
        <?php else : ?>
          <?php // ─── Image cover (chapter or story fallback) ──────────── ?>
          <a
            href="<?php the_permalink(); ?>"
            class="illusia-card__cover-link"
            title="<?php echo esc_attr( sprintf( __( '%s Cover', 'fictioneer' ), $title ) ); ?>"
            <?php echo fictioneer_get_lightbox_attribute(); ?>
          >
            <?php if ( $has_chapter_thumb ) : ?>
              <?php echo get_the_post_thumbnail( null, 'cover', $thumbnail_args ); ?>
            <?php else : ?>
              <?php echo get_the_post_thumbnail( $story_id, 'cover', $thumbnail_args ); ?>
            <?php endif; ?>
            <div class="illusia-card__cover-overlay" aria-hidden="true"></div>
            <?php if ( $rating_letter ) : ?>
              <span
                class="illusia-card__rating-ribbon illusia-card__rating-ribbon--<?php echo esc_attr( $rating_lower ); ?>"
                title="<?php echo esc_attr( fcntr( $chapter_rating, true ) ); ?>"
              ><?php echo esc_html( fcntr( $rating_letter ) ); ?></span>
            <?php endif; ?>
          </a>
        <?php endif; ?>

        <?php // ─── Cover stats: Words + Comments ──────────────────────── ?>
        <div class="illusia-card__cover-stats illusia-card__cover-stats--compact" aria-label="<?php esc_attr_e( 'Estatísticas do capítulo', 'fictioneer' ); ?>">
          <?php if ( $words ) : ?>
            <div class="illusia-card__stat-cell">
              <span class="illusia-card__stat-value"><?php echo esc_html( fictioneer_shorten_number( $words ) ); ?></span>
              <span class="illusia-card__stat-label"><?php esc_html_e( 'Words', 'fictioneer' ); ?></span>
            </div>
          <?php endif; ?>
          <div class="illusia-card__stat-cell">
            <span class="illusia-card__stat-value"><?php echo esc_html( get_comments_number( $post ) ); ?></span>
            <span class="illusia-card__stat-label"><?php esc_html_e( 'Comments', 'fictioneer' ); ?></span>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <header class="illusia-card__header">
      <div class="illusia-card__title-group">

        <?php if ( $args['show_type'] ?? false ) : ?>
          <span class="illusia-card__label"><?php
            _ex( 'Chapter', 'Chapter card label.', 'fictioneer' );
          ?></span>
        <?php endif; ?>

        <h3 class="illusia-card__title">
          <a href="<?php the_permalink(); ?>" class="illusia-card__title-link"><?php
            if ( ! empty( $post->post_password ) ) {
              echo '<i class="fa-solid fa-lock illusia-card__password-icon"></i> ';
            }
            echo esc_html( $list_title ?: $title );
          ?></a>
        </h3>

        <?php if ( ! $hide_author ) : ?>
          <p class="illusia-card__author"><?php
            printf(
              _x( 'por %s', 'Illusia card: por {Author}.', 'fictioneer' ),
              fictioneer_get_author_node()
            );
          ?></p>
        <?php endif; ?>

      </div>

      <?php
        if ( ! empty( $story_id ) && ! empty( $story_data ) ) {
          echo fictioneer_get_card_controls( $story_id, $post_id );
        }
      ?>

    </header>

    <?php if ( $has_cover ) : ?>
      <div class="illusia-card__excerpt-inline"><?php
        echo $excerpt ? wp_kses_post( $excerpt ) : esc_html__( 'No description provided yet.', 'fictioneer' );
      ?></div>
    <?php endif; ?>

    <div class="illusia-card__middle">

      <div class="illusia-card__excerpt<?php echo empty( $excerpt ) ? ' illusia-card__excerpt--empty' : ''; ?>"><?php
        echo $excerpt ? wp_kses_post( $excerpt ) : esc_html__( 'No description provided yet.', 'fictioneer' );
      ?></div>

      <?php if ( ! empty( $story_id ) && ! empty( $story_data ) && ! $story_unpublished ) : ?>
        <div class="illusia-card__parent-story">

          <div class="illusia-card__readings-label"><?php
            esc_html_e( 'História Principal', 'fictioneer' );
          ?></div>

          <div class="illusia-card__parent-story-row<?php echo ! empty( $story_post->post_password ) ? ' illusia-card__parent-story-row--password' : ''; ?>">
            <div class="illusia-card__parent-story-left">
              <span class="illusia-card__parent-story-icon" aria-hidden="true">&rsaquo;</span>
              <a href="<?php the_permalink( $story_id ); ?>" class="illusia-card__parent-story-link"><?php
                echo esc_html( $story_data['title'] );
              ?></a>
            </div>
            <div class="illusia-card__parent-story-right">
              <span class="illusia-card__parent-story-words"><?php
                echo esc_html( $story_data['word_count_short'] );
              ?></span>
              <span class="illusia-card__parent-story-separator" aria-hidden="true">&middot;</span>
              <span class="illusia-card__parent-story-status"><?php
                echo esc_html( fcntr( $story_data['status'] ) );
              ?></span>
            </div>
          </div>

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
          'fictioneer_filter_card_chapter_terms',
          $terms, $post, $args, $story_data
        );
      }
    ?>

    <?php if ( $show_terms && ! empty( $terms ) ) : ?>
      <div class="illusia-card__taxonomies"><?php
        echo implode( ' ', $terms );
      ?></div>
    <?php endif; ?>

    <footer class="illusia-card__footer">
      <div class="illusia-card__meta">
        <?php echo implode( ' ', $footer_items ); ?>
      </div>
      <?php if ( ! empty( $chapter_rating ) ) : ?>
        <div class="illusia-card__status-badges">
          <span
            class="illusia-badge illusia-badge--rating illusia-badge--rating-<?php echo esc_attr( $rating_lower ); ?>"
            title="<?php echo esc_attr( fcntr( $chapter_rating, true ) ); ?>"
          ><?php echo esc_html( fcntr( $rating_letter ) ); ?></span>
        </div>
      <?php endif; ?>
    </footer>

  </article>
</li>
