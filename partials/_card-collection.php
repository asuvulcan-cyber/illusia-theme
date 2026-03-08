<?php
/**
 * Partial: Collection Card — Illusia Theme Override
 *
 * Redesenho completo do card de coleção com markup original Illusia,
 * consumindo a mesma lógica PHP do Fictioneer (Stats, featured items,
 * taxonomias, hooks).
 *
 * @package Illusia
 * @since 1.8.0
 *
 * @internal $args['show_type']  Whether to show the post type label. Unsafe.
 * @internal $args['cache']      Whether to account for active caching. Unsafe.
 * @internal $args['order']      Current order. Default 'desc'. Unsafe.
 * @internal $args['orderby']    Current orderby. Default 'modified'. Unsafe.
 */

use Fictioneer\Utils;

// No direct access!
defined( 'ABSPATH' ) OR exit;

// ─── Setup ──────────────────────────────────────────────────────────────────
$post_id    = $post->ID;
$list_title = trim( get_post_meta( $post_id, 'fictioneer_collection_list_title', true ) );
$title      = empty( $list_title ) ? fictioneer_get_safe_title( $post_id, 'card-collection' ) : $list_title;

$excerpt = fictioneer_first_paragraph_as_excerpt(
  fictioneer_get_content_field( 'fictioneer_collection_description', $post_id )
);
$excerpt = empty( $excerpt ) ? fictioneer_get_excerpt( $post_id ) : $excerpt;

$statistics = \Fictioneer\Stats::get_collection_statistics( $post_id );

$items = get_post_meta( $post_id, 'fictioneer_collection_items', true );
$items = empty( $items ) ? [] : $items;

// ─── Taxonomies ─────────────────────────────────────────────────────────────
$tags       = false;
$fandoms    = false;
$characters = false;
$genres     = false;

if (
  get_option( 'fictioneer_show_tags_on_collection_cards' ) &&
  ! get_option( 'fictioneer_hide_taxonomies_on_collection_cards' )
) {
  $tags = get_the_tags();
}

if ( ! get_option( 'fictioneer_hide_taxonomies_on_collection_cards' ) ) {
  $fandoms    = get_the_terms( $post_id, 'fcn_fandom' );
  $characters = get_the_terms( $post_id, 'fcn_character' );
  $genres     = get_the_terms( $post_id, 'fcn_genre' );
}

// ─── Query featured posts ───────────────────────────────────────────────────
if ( ! empty( $items ) ) {
  $items = new WP_Query(
    array(
      'fictioneer_query_name'  => 'card_collection_featured',
      'post_type'              => FICTIONEER_DEFAULT_POST_TYPES,
      'post_status'            => 'publish',
      'post__in'               => $items ?: [0],
      'ignore_sticky_posts'    => true,
      'orderby'                => 'modified',
      'posts_per_page'         => 3,
      'update_post_meta_cache' => false,
      'update_post_term_cache' => false,
      'no_found_rows'          => true
    )
  );

  $items = $items->posts;
}

// ─── Flags ──────────────────────────────────────────────────────────────────
$show_terms = ! get_option( 'fictioneer_hide_taxonomies_on_collection_cards' ) &&
  ( $fandoms || $characters || $genres || $tags );
$has_cover  = has_post_thumbnail() &&
  get_theme_mod( 'card_image_style', 'default' ) !== 'none';

// ─── Card classes (Illusia BEM) ─────────────────────────────────────────────
$card_classes = [ 'illusia-card', 'illusia-card--collection' ];

if ( ! $has_cover ) {
  $card_classes[] = 'illusia-card--no-cover';
}

if ( ! $show_terms ) {
  $card_classes[] = 'illusia-card--no-tax';
}

// Fictioneer compat classes
$card_classes[] = 'card';
$card_classes[] = '_large';
$card_classes[] = '_collection';

// ─── Card attributes (hook) ────────────────────────────────────────────────
$attributes = apply_filters( 'fictioneer_filter_card_attributes', [], $post, 'card-collection' );
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
    'title' => __( 'Total Words', 'fictioneer' )
  )
);

$footer_items = [];

$footer_items['stories'] = '<span class="illusia-meta-item"><i class="illusia-meta-icon fa-solid fa-book" title="' . esc_attr__( 'Stories', 'fictioneer' ) . '"></i> ' . esc_html( $statistics['story_count'] ) . '</span>';

$footer_items['chapters'] = '<span class="illusia-meta-item"><i class="illusia-meta-icon fa-solid fa-list" title="' . esc_attr__( 'Chapters', 'fictioneer' ) . '"></i> ' . esc_html( $statistics['chapter_count'] ) . '</span>';

$footer_items['words'] = '<span class="illusia-meta-item">' . $icon_words . ' ' . esc_html( fictioneer_shorten_number( $statistics['word_count'] ) ) . '</span>';

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

$footer_items['comments'] = '<span class="illusia-meta-item"><i class="illusia-meta-icon fa-solid fa-message" title="' . esc_attr__( 'Comments', 'fictioneer' ) . '"></i> ' . esc_html( $statistics['comment_count'] ) . '</span>';

$footer_items = apply_filters( 'fictioneer_filter_collection_card_footer', $footer_items, $post, $args, $items );

?>

<li
  id="collection-card-<?php echo esc_attr( $post_id ); ?>"
  class="post-<?php echo esc_attr( $post_id ); ?> <?php echo implode( ' ', $card_classes ); ?>"
  <?php echo $card_attributes; ?>
>
  <article class="illusia-card__body">

    <?php
      // ─── Action hook ──────────────────────────────────────────────────
      do_action( 'fictioneer_large_card_body_collection', $post, $items, $args );
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

        <?php // ─── Cover stats: Stories, Chapters, Words, Comments ────── ?>
        <div class="illusia-card__cover-stats" aria-label="<?php esc_attr_e( 'Estatísticas da coleção', 'fictioneer' ); ?>">
          <div class="illusia-card__stat-cell">
            <span class="illusia-card__stat-value"><?php echo esc_html( $statistics['story_count'] ); ?></span>
            <span class="illusia-card__stat-label"><?php esc_html_e( 'Stories', 'fictioneer' ); ?></span>
          </div>
          <div class="illusia-card__stat-cell">
            <span class="illusia-card__stat-value"><?php echo esc_html( $statistics['chapter_count'] ); ?></span>
            <span class="illusia-card__stat-label"><?php esc_html_e( 'Caps', 'fictioneer' ); ?></span>
          </div>
          <div class="illusia-card__stat-cell">
            <span class="illusia-card__stat-value"><?php echo esc_html( fictioneer_shorten_number( $statistics['word_count'] ) ); ?></span>
            <span class="illusia-card__stat-label"><?php esc_html_e( 'Words', 'fictioneer' ); ?></span>
          </div>
          <div class="illusia-card__stat-cell">
            <span class="illusia-card__stat-value"><?php echo esc_html( $statistics['comment_count'] ); ?></span>
            <span class="illusia-card__stat-label"><?php esc_html_e( 'Comments', 'fictioneer' ); ?></span>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <header class="illusia-card__header">
      <div class="illusia-card__title-group">

        <?php if ( $args['show_type'] ?? false ) : ?>
          <span class="illusia-card__label"><?php
            _ex( 'Collection', 'Collection card label.', 'fictioneer' );
          ?></span>
        <?php endif; ?>

        <h3 class="illusia-card__title">
          <a href="<?php the_permalink(); ?>" class="illusia-card__title-link"><?php
            if ( ! empty( $post->post_password ) ) {
              echo '<i class="fa-solid fa-lock illusia-card__password-icon"></i> ';
            }
            echo esc_html( $title );
          ?></a>
        </h3>

      </div>
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

      <?php if ( ! empty( $items ) ) : ?>
        <div class="illusia-card__featured-items">

          <div class="illusia-card__readings-label"><?php
            esc_html_e( 'Destaques', 'fictioneer' );
          ?></div>

          <ol class="illusia-featured-list">
            <?php foreach ( $items as $item ) : ?>
              <?php
                $item_classes = [ 'illusia-featured-list__item' ];

                if ( ! empty( $item->post_password ) ) {
                  $item_classes[] = 'illusia-featured-list__item--password';
                }

                // Get display title (chapters may have list_title)
                $item_title = $item->post_type === 'fcn_chapter'
                  ? trim( wp_strip_all_tags( get_post_meta( $item->ID, 'fictioneer_chapter_list_title', true ) ) )
                  : '';

                if ( empty( $item_title ) ) {
                  $item_title = fictioneer_get_safe_title( $item->ID, 'card-collection-list' );
                }

                $item_type = get_post_type_object( $item->post_type )->labels->singular_name;
              ?>
              <li class="<?php echo implode( ' ', $item_classes ); ?>">
                <div class="illusia-featured-list__left">
                  <span class="illusia-featured-list__icon" aria-hidden="true">&rsaquo;</span>
                  <a href="<?php the_permalink( $item->ID ); ?>" class="illusia-featured-list__link"><?php
                    echo esc_html( $item_title );
                  ?></a>
                </div>
                <div class="illusia-featured-list__right">
                  <span class="illusia-featured-list__type"><?php echo esc_html( $item_type ); ?></span>
                  <span class="illusia-featured-list__separator" aria-hidden="true">&middot;</span>
                  <span class="illusia-featured-list__date"><?php
                    echo esc_html( sprintf(
                      _x( 'há %s', 'Illusia card: time ago.', 'fictioneer' ),
                      human_time_diff( get_the_modified_time( 'U', false, $item ), time() )
                    ) );
                  ?></span>
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
          'fictioneer_filter_card_collection_terms',
          $terms, $post, $args, null
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
    </footer>

  </article>
</li>
