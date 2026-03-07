<?php
/**
 * Partial: Story Card — Illusia Theme Override
 *
 * Redesenho completo do card de história com markup original Illusia,
 * consumindo a mesma lógica PHP do Fictioneer (Story::get_data, hooks,
 * caching, Stimulus controllers).
 *
 * Abordagem: template override (child theme > parent theme via
 * get_template_part). Todo o HTML é original; toda a lógica é do pai.
 *
 * @package Illusia
 * @since 1.5.0
 *
 * @internal $args['show_type']    Whether to show the post type label. Unsafe.
 * @internal $args['cache']        Whether to account for active caching. Unsafe.
 * @internal $args['hide_author']  Whether to hide the author. Unsafe.
 * @internal $args['show_latest']  Whether to show (up to) the latest 3 chapters. Unsafe.
 * @internal $args['order']        Current order. Default 'desc'. Unsafe.
 * @internal $args['orderby']      Current orderby. Default 'modified'. Unsafe.
 */

use Fictioneer\Utils;

// No direct access!
defined( 'ABSPATH' ) OR exit;

// ─── Card cache ─────────────────────────────────────────────────────────────
$card_cache_active = get_option( 'fictioneer_enable_story_card_caching' );

if ( $card_cache_active ) {
  $cache_key = $post->ID . '_' . date( 'Y-m-d-H-i-s', strtotime( $post->post_modified_gmt ) ) .
    '_' . md5( json_encode( $args ) );

  if ( $cache = fictioneer_get_cached_story_card( $cache_key ) ) {
    echo $cache;
    return;
  }
}

// ─── Setup ──────────────────────────────────────────────────────────────────
$post_id    = $post->ID;
$story      = \Fictioneer\Story::get_data( $post_id );
$story_link = ( $story['redirect'] ?? 0 ) ?: get_permalink( $post_id );
$latest     = $args['show_latest'] ?? FICTIONEER_SHOW_LATEST_CHAPTERS_ON_STORY_CARDS;

$chapter_limit = max( 0, FICTIONEER_STORY_CARD_CHAPTER_LIMIT );
$chapter_ids   = array_slice(
  $story['chapter_ids'],
  $latest ? -1 * $chapter_limit : 0,
  $chapter_limit,
  true
);
$chapter_count = count( $chapter_ids );

$excerpt = fictioneer_first_paragraph_as_excerpt(
  fictioneer_get_content_field( 'fictioneer_story_short_description', $post_id )
);
$excerpt = empty( $excerpt ) ? fictioneer_get_excerpt( $post_id ) : $excerpt;

$tags = false;

if (
  get_option( 'fictioneer_show_tags_on_story_cards' ) &&
  ! get_option( 'fictioneer_hide_taxonomies_on_story_cards' )
) {
  $tags = get_the_tags();
}

// ─── Flags ──────────────────────────────────────────────────────────────────
$hide_author = ( $args['hide_author'] ?? false ) || ! get_option( 'fictioneer_show_authors' );
$show_terms  = ! get_option( 'fictioneer_hide_taxonomies_on_story_cards' ) &&
  ( $story['has_taxonomies'] || $tags );
$is_sticky   = FICTIONEER_ENABLE_STICKY_CARDS &&
  get_post_meta( $post_id, 'fictioneer_story_sticky', true ) &&
  ! is_search() && ! is_archive();
$has_cover   = has_post_thumbnail() &&
  get_theme_mod( 'card_image_style', 'default' ) !== 'none';

// ─── Card classes (Illusia BEM) ─────────────────────────────────────────────
$card_classes = [ 'illusia-card', 'illusia-card--story' ];

if ( $is_sticky ) {
  $card_classes[] = 'illusia-card--sticky';
}

if ( ! $has_cover ) {
  $card_classes[] = 'illusia-card--no-cover';
}

if ( ! $show_terms ) {
  $card_classes[] = 'illusia-card--no-tax';
}

// Keep Fictioneer's card + _large + _story for Stimulus controller compatibility
$card_classes[] = 'card';
$card_classes[] = '_large';
$card_classes[] = '_story';

// ─── Card attributes (hook) ────────────────────────────────────────────────
$attributes = apply_filters( 'fictioneer_filter_card_attributes', [], $post, 'card-story' );
$card_attributes = '';

foreach ( $attributes as $key => $value ) {
  $card_attributes .= esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
}

// ─── Thumbnail attributes ──────────────────────────────────────────────────
$thumbnail_args = array(
  'alt'   => sprintf( __( '%s Cover', 'fictioneer' ), $story['title'] ),
  'class' => 'illusia-card__cover-img no-auto-lightbox'
);

// ─── Footer items (built + filtered like Fictioneer) ───────────────────────
$icon_words = Utils::get_theme_icon(
  'icon_words',
  '<i class="fa-solid fa-font"></i>',
  array(
    'class' => 'illusia-meta-icon',
    'title' => __( 'Total Words', 'fictioneer' )
  )
);

$footer_items = [];

if ( $story['status'] !== 'Oneshot' || $story['chapter_count'] > 1 ) {
  $footer_items['chapters'] = '<span class="illusia-meta-item illusia-meta-item--in-stats"><i class="illusia-meta-icon fa-solid fa-list" title="' . esc_attr__( 'Chapters', 'fictioneer' ) . '"></i> ' . esc_html( $story['chapter_count'] ) . '</span>';
}

if ( $story['word_count'] > 1000 || $story['status'] === 'Oneshot' ) {
  $footer_items['words'] = '<span class="illusia-meta-item illusia-meta-item--in-stats">' . $icon_words . ' ' . esc_html( $story['word_count_short'] ) . '</span>';
}

if ( ( $args['orderby'] ?? 0 ) === 'date' ) {
  $footer_date_human = sprintf(
    /* translators: %s = human time diff, e.g. "há 3 dias" */
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

$footer_items['comments'] = '<span class="illusia-meta-item illusia-meta-item--in-stats"><i class="illusia-meta-icon fa-solid fa-message" title="' . esc_attr__( 'Comments', 'fictioneer' ) . '"></i> ' . esc_html( $story['comment_count'] ) . '</span>';

$footer_items = apply_filters( 'fictioneer_filter_story_card_footer', $footer_items, $post, $story, $args );

// ─── Status badge ──────────────────────────────────────────────────────────
$status_lower = strtolower( $story['status'] );
$status_label = fictioneer_get_story_status_label( $story['id'], $story['status'] );
$status_icon  = $story['icon'];

// ─── Rating badge ──────────────────────────────────────────────────────────
$rating_lower  = strtolower( $story['rating'] );
$rating_letter = $story['rating_letter'] ?? '';

// ─── Buffer HTML for cache ─────────────────────────────────────────────────
if ( $card_cache_active ) {
  ob_start();
}

?>

<li
  id="story-card-<?php echo $post_id; ?>"
  class="post-<?php echo $post_id; ?> <?php echo implode( ' ', $card_classes ); ?>"
  data-controller="fictioneer-large-card"
  data-fictioneer-large-card-post-id-value="<?php echo $post_id; ?>"
  data-fictioneer-large-card-story-id-value="<?php echo $post_id; ?>"
  data-action="click->fictioneer-large-card#cardClick"
  <?php echo $card_attributes; ?>
>
  <article class="illusia-card__body">

    <?php
      // ─── Action hook (after header, before content) ────────────────────
      do_action( 'fictioneer_large_card_body_story', $post, $story, $args );
    ?>

    <?php if ( $has_cover ) : ?>
      <div class="illusia-card__cover">
        <a
          href="<?php echo esc_url( $story_link ); ?>"
          class="illusia-card__cover-link"
          title="<?php echo esc_attr( sprintf( __( '%s Cover', 'fictioneer' ), $story['title'] ) ); ?>"
          <?php echo fictioneer_get_lightbox_attribute(); ?>
        >
          <?php echo get_the_post_thumbnail( null, 'cover', $thumbnail_args ); ?>
          <div class="illusia-card__cover-overlay" aria-hidden="true"></div>
          <?php if ( $rating_letter ) : ?>
            <span
              class="illusia-card__rating-ribbon illusia-card__rating-ribbon--<?php echo esc_attr( $rating_lower ); ?>"
              title="<?php echo esc_attr( fcntr( $story['rating'], true ) ); ?>"
            ><?php echo esc_html( fcntr( $rating_letter ) ); ?></span>
          <?php endif; ?>
        </a>

        <div class="illusia-card__cover-stats" aria-label="<?php esc_attr_e( 'Story stats', 'fictioneer' ); ?>">
          <?php if ( $story['status'] !== 'Oneshot' || $story['chapter_count'] > 1 ) : ?>
            <div class="illusia-card__stat-cell">
              <span class="illusia-card__stat-value"><?php echo esc_html( $story['chapter_count'] ); ?></span>
              <span class="illusia-card__stat-label"><?php esc_html_e( 'Caps', 'fictioneer' ); ?></span>
            </div>
          <?php endif; ?>
          <div class="illusia-card__stat-cell">
            <span class="illusia-card__stat-value"><?php echo esc_html( $story['word_count_short'] ); ?></span>
            <span class="illusia-card__stat-label"><?php esc_html_e( 'Words', 'fictioneer' ); ?></span>
          </div>
          <div class="illusia-card__stat-cell">
            <span class="illusia-card__stat-value"><?php echo esc_html( $story['comment_count'] ); ?></span>
            <span class="illusia-card__stat-label"><?php esc_html_e( 'Comments', 'fictioneer' ); ?></span>
          </div>
          <div class="illusia-card__stat-cell">
            <span class="illusia-card__stat-value illusia-card__stat-value--status-<?php echo esc_attr( $status_lower ); ?>"><?php echo wp_kses( $status_icon, array( 'i' => array( 'class' => true ) ) ); ?></span>
            <span class="illusia-card__stat-label"><?php echo esc_html( $status_label ); ?></span>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <header class="illusia-card__header">
      <div class="illusia-card__title-group">

        <?php if ( $args['show_type'] ?? false ) : ?>
          <span class="illusia-card__label"><?php
            _ex( 'Story', 'Story card label.', 'fictioneer' );
          ?></span>
        <?php endif; ?>

        <h3 class="illusia-card__title">
          <a href="<?php echo esc_url( $story_link ); ?>" class="illusia-card__title-link"><?php
            if ( ! empty( $post->post_password ) ) {
              echo '<i class="fa-solid fa-lock illusia-card__password-icon"></i> ';
            }
            echo esc_html( $story['title'] );
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

      <?php echo fictioneer_get_card_controls( $post_id ); ?>

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

      <?php if ( $chapter_count > 0 && ! get_option( 'fictioneer_hide_large_card_chapter_list' ) ) : ?>
        <div class="illusia-card__chapters">

          <div class="illusia-card__readings-label"><?php
            esc_html_e( 'Capítulos Recentes', 'fictioneer' );
          ?></div>

          <ol class="illusia-chapter-list">
            <?php
              $chapter_query_args = array(
                'fictioneer_query_name'  => 'card_story_chapters',
                'post_type'              => 'fcn_chapter',
                'post_status'            => 'publish',
                'post__in'               => $chapter_ids ?: [0],
                'orderby'                => 'post__in',
                'posts_per_page'         => FICTIONEER_STORY_CARD_CHAPTER_LIMIT,
                'no_found_rows'          => true,
                'update_post_term_cache' => false
              );

              if ( get_option( 'fictioneer_show_scheduled_chapters' ) ) {
                $chapter_query_args['post_status'] = [ 'publish', 'future' ];
              }

              $chapter_query_args = apply_filters(
                'fictioneer_filter_story_card_chapter_query_args',
                $chapter_query_args,
                $post,
                $story
              );

              $chapters     = new WP_Query( $chapter_query_args );
              $ts_words     = __( 'Words', 'fictioneer' );
              $ts_new       = __( 'New', 'fictioneer' );
              $current_time = time();
            ?>

            <?php foreach ( $chapters->posts as $chapter ) : ?>
              <?php
                $list_title = get_post_meta( $chapter->ID, 'fictioneer_chapter_list_title', true );
                $list_title = trim( wp_strip_all_tags( $list_title ) );

                if ( empty( $list_title ) ) {
                  $chapter_title = fictioneer_get_safe_title( $chapter->ID, 'card-story-chapter-list' );
                } else {
                  $chapter_title = $list_title;
                }

                $is_password  = ! empty( $chapter->post_password );
                $is_new       = $current_time - get_post_time( 'U', false, $chapter->ID ) < DAY_IN_SECONDS;
                $item_classes = [ 'illusia-chapter-list__item' ];

                if ( $is_password ) {
                  $item_classes[] = 'illusia-chapter-list__item--password';
                }
              ?>
              <li class="<?php echo implode( ' ', $item_classes ); ?>">
                <div class="illusia-chapter-list__left">
                  <?php if ( $is_password ) : ?>
                    <i class="fa-solid fa-lock illusia-chapter-list__icon"></i>
                  <?php else : ?>
                    <span class="illusia-chapter-list__icon" aria-hidden="true">&rsaquo;</span>
                  <?php endif; ?>
                  <a href="<?php the_permalink( $chapter->ID ); ?>" class="illusia-chapter-list__link"><?php
                    echo esc_html( $chapter_title );
                  ?></a>
                </div>
                <div class="illusia-chapter-list__right">
                  <?php if ( ! $is_password ) : ?>
                    <span class="illusia-chapter-list__words"><?php
                      echo fictioneer_shorten_number( fictioneer_get_word_count( $chapter->ID ) );
                    ?><span class="illusia-chapter-list__label"> <?php echo $ts_words; ?></span></span>
                    <span class="illusia-chapter-list__separator" aria-hidden="true">&middot;</span>
                  <?php endif; ?>
                  <?php if ( $is_new ) : ?>
                    <span class="illusia-chapter-list__date illusia-chapter-list__date--new"><?php
                      echo $ts_new;
                    ?></span>
                  <?php else : ?>
                    <span class="illusia-chapter-list__date"><?php
                      echo esc_html( sprintf(
                        _x( 'há %s', 'Illusia card: time ago.', 'fictioneer' ),
                        human_time_diff( get_post_time( 'U', false, $chapter->ID ), $current_time )
                      ) );
                    ?></span>
                  <?php endif; ?>
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
          $story['fandoms'] ? fictioneer_get_term_nodes( $story['fandoms'], '_inline _fandom' ) : [],
          $story['genres'] ? fictioneer_get_term_nodes( $story['genres'], '_inline _genre' ) : [],
          $tags ? fictioneer_get_term_nodes( $tags, '_inline _tag' ) : [],
          $story['characters'] ? fictioneer_get_term_nodes( $story['characters'], '_inline _character' ) : []
        );

        $terms = apply_filters(
          'fictioneer_filter_card_story_terms',
          $terms, $post, $args, $story
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
      <div class="illusia-card__status-badges">
        <span class="illusia-badge illusia-badge--status illusia-badge--status-<?php echo esc_attr( $status_lower ); ?> illusia-badge--in-stats">
          <?php echo wp_kses( $status_icon, array( 'i' => array( 'class' => true ) ) ); ?> <?php echo esc_html( $status_label ); ?>
        </span>
        <span
          class="illusia-badge illusia-badge--rating illusia-badge--rating-<?php echo esc_attr( $rating_lower ); ?>"
          title="<?php echo esc_attr( fcntr( $story['rating'], true ) ); ?>"
        ><?php echo esc_html( fcntr( $story['rating_letter'] ) ); ?></span>
      </div>
    </footer>

  </article>
</li>

<?php

// ─── Cache save ─────────────────────────────────────────────────────────────
if ( $card_cache_active ) {
  $cache = fictioneer_minify_html( ob_get_clean() );
  fictioneer_cache_story_card( $cache_key, $cache );
  echo $cache;
}
