<?php
/**
 * Template Name: Stories
 *
 * Override do template Fictioneer para a página /stories/.
 * Renderiza lista paginada de story cards com stats panel Illusia
 * e layout "Dark Editorial Observatory".
 *
 * Preserva a query logic, caching, filtros e sort UI do Fictioneer.
 * Substitui stats (prioridade 10) e card list (prioridade 30) do
 * hook fictioneer_stories_after_content por markup Illusia.
 * Sort UI (prioridade 20) renderizada diretamente via função do pai.
 *
 * @package Illusia Theme
 * @since 1.6.0
 * @see fictioneer/stories.php (template pai)
 */

use Fictioneer\Sanitizer;
use Fictioneer\Utils;

// =============================================================================
// QUERY SETUP (espelhado do tema pai)
// =============================================================================

$post_id = get_the_ID();
$current_page = Utils::get_global_page();
$order = Sanitizer::sanitize_query_var( $_GET['order'] ?? 0, ['desc', 'asc'], 'desc' );
$orderby = Sanitizer::sanitize_query_var( $_GET['orderby'] ?? 0, fictioneer_allowed_orderby(), 'modified' );
$ago = $_GET['ago'] ?? 0;
$ago = is_numeric( $ago ) ? absint( $ago ) : sanitize_text_field( $ago );
$meta_query_stack = [];

$query_args = array(
  'fictioneer_query_name' => 'stories_list',
  'post_type' => 'fcn_story',
  'post_status' => 'publish',
  'order' => $order,
  'orderby' => $orderby,
  'paged' => $current_page,
  'posts_per_page' => get_option( 'posts_per_page', 8 ),
  'update_post_term_cache' => ! get_option( 'fictioneer_hide_taxonomies_on_story_cards' ),
);

// Meta query: hidden stories
if ( get_option( 'fictioneer_disable_extended_story_list_meta_queries' ) ) {
  $meta_query_stack[] = array(
    array(
      'key' => 'fictioneer_story_hidden',
      'value' => '0',
    ),
  );
} else {
  $meta_query_stack[] = array(
    'relation' => 'OR',
    array(
      'key' => 'fictioneer_story_hidden',
      'value' => '0',
    ),
    array(
      'key' => 'fictioneer_story_hidden',
      'compare' => 'NOT EXISTS',
    ),
  );
}

$query_args['meta_query'] = [];

if ( count( $meta_query_stack ) > 1 ) {
  $query_args['meta_query']['relation'] = 'AND';
}

foreach ( $meta_query_stack as $part ) {
  $query_args['meta_query'][] = $part;
}

// Order by words
if ( $orderby === 'words' ) {
  $query_args['orderby'] = 'meta_value_num modified';
  $query_args['meta_key'] = 'fictioneer_story_total_word_count';
}

// Order by latest chapter update
if ( FICTIONEER_ORDER_STORIES_BY_LATEST_CHAPTER && $orderby === 'modified' ) {
  $query_args['orderby'] = 'meta_value modified';
  $query_args['meta_key'] = 'fictioneer_chapters_added';
}

// Date query
$query_args = fictioneer_append_date_query( $query_args, $ago, $orderby );

// Filter (compatibilidade com plugins)
$query_args = apply_filters( 'fictioneer_filter_stories_query_args', $query_args, $post_id );

// Execute query
$list_of_stories = new WP_Query( $query_args );

// Prime caches
if ( function_exists( 'update_post_thumbnail_cache' ) ) {
  update_post_thumbnail_cache( $list_of_stories );
}

if ( function_exists( 'update_post_author_caches' ) ) {
  update_post_author_caches( $list_of_stories->posts );
}

// =============================================================================
// STATISTICS (reutiliza transient do Fictioneer)
// =============================================================================

$statistics = get_transient( 'fictioneer_stories_statistics' );

if ( ! $statistics ) {
  $words = \Fictioneer\Stats::get_stories_total_word_count();

  $statistics = array(
    'stories' => array(
      'label' => __( 'Stories', 'fictioneer' ),
      'content' => number_format_i18n( wp_count_posts( 'fcn_story' )->publish ),
    ),
    'words' => array(
      'label' => _x( 'Words', 'Word count caption in statistics.', 'fictioneer' ),
      'content' => fictioneer_shorten_number( $words ),
    ),
    'comments' => array(
      'label' => __( 'Comments', 'fictioneer' ),
      'content' => number_format_i18n(
        get_comments(
          array(
            'post_type' => 'fcn_chapter',
            'status' => 1,
            'count' => true,
            'update_comment_meta_cache' => false,
          )
        )
      ),
    ),
    'reading' => array(
      'label' => __( 'Reading', 'fictioneer' ),
      'content' => fictioneer_get_reading_time_nodes( $words ),
    ),
  );

  $statistics = apply_filters( 'fictioneer_filter_stories_statistics', $statistics, array(
    'current_page' => $current_page,
    'post_id' => $post_id,
    'stories' => $list_of_stories,
    'queried_type' => 'fcn_story',
  ) );

  set_transient( 'fictioneer_stories_statistics', $statistics, HOUR_IN_SECONDS );
}

// Card arguments
$card_args = array(
  'cache' => fictioneer_caching_active( 'card_args' ) && ! fictioneer_private_caching_active(),
  'order' => $order,
  'orderby' => $orderby,
  'ago' => $ago,
);

$card_args = apply_filters( 'fictioneer_filter_stories_card_args', $card_args, array(
  'current_page' => $current_page,
  'post_id' => $post_id,
  'stories' => $list_of_stories,
  'queried_type' => 'fcn_story',
  'query_args' => $query_args,
  'order' => $order,
  'orderby' => $orderby,
  'ago' => $ago,
) );

// Header
get_header();

?>

<main id="main" class="main singular stories illusia-stories">

  <?php do_action( 'fictioneer_main', 'stories' ); ?>

  <div class="main__wrapper">

    <?php do_action( 'fictioneer_main_wrapper' ); ?>

    <?php while ( have_posts() ) : the_post(); ?>

      <?php
        $title = trim( get_the_title() );
        $breadcrumb_name = empty( $title ) ? __( 'Stories', 'fictioneer' ) : $title;
        $this_breadcrumb = [ $breadcrumb_name, get_the_permalink() ];
      ?>

      <article id="singular-<?php echo esc_attr( $post_id ); ?>" class="singular__article stories__article illusia-stories__article">

        <?php // ── Page Header ── ?>

        <?php if ( ! empty( $title ) ) : ?>
          <header class="illusia-stories__header">
            <h1 class="illusia-stories__title"><?php echo esc_html( $title ); ?></h1>
          </header>
        <?php endif; ?>

        <?php if ( get_the_content() ) : ?>
          <section class="singular__content stories__content illusia-stories__content content-section">
            <?php the_content(); ?>
          </section>
        <?php endif; ?>

        <?php // ── Stats Panel ── ?>

        <div class="illusia-stories__stats" role="region" aria-label="<?php esc_attr_e( 'Stories statistics', 'fictioneer' ); ?>">
          <?php foreach ( $statistics as $key => $stat ) : ?>
            <div class="illusia-stories__stat-cell">
              <span class="illusia-stories__stat-value"><?php
                // wp_kses é seguro tanto para HTML (reading) quanto texto plano (demais stats)
                echo wp_kses( $stat['content'], array( 'span' => array( 'class' => true ) ) );
              ?></span>
              <span class="illusia-stories__stat-label"><?php echo esc_html( $stat['label'] ); ?></span>
            </div>
          <?php endforeach; ?>
        </div>

        <?php // ── Sort/Order UI (do tema pai) ── ?>

        <?php
          $hook_args = array(
            'current_page' => $current_page,
            'post_id' => $post->ID,
            'stories' => $list_of_stories,
            'queried_type' => 'fcn_story',
            'query_args' => $query_args,
            'order' => $order,
            'orderby' => $orderby,
            'ago' => $ago,
          );

          if ( function_exists( 'fictioneer_sort_order_filter_interface' ) ) {
            fictioneer_sort_order_filter_interface( $hook_args );
          }
        ?>

        <?php // ── Card List ── ?>

        <section class="illusia-stories__list">
          <ul id="list-of-stories" class="illusia-stories__cards scroll-margin-top">

            <?php if ( $list_of_stories->have_posts() ) : ?>

              <?php
                while ( $list_of_stories->have_posts() ) {
                  $list_of_stories->the_post();

                  if ( get_post_meta( get_the_ID(), 'fictioneer_story_hidden', true ) ) {
                    fictioneer_get_template_part( 'partials/_card-hidden', null, $card_args );
                  } else {
                    fictioneer_get_template_part( 'partials/_card-story', null, $card_args );
                  }
                }

                do_action( 'fictioneer_stories_end_of_results', array(
                  'current_page' => $current_page,
                  'post_id' => $post_id,
                  'stories' => $list_of_stories,
                  'queried_type' => 'fcn_story',
                  'query_args' => $query_args,
                  'order' => $order,
                  'orderby' => $orderby,
                  'ago' => $ago,
                ) );
              ?>

            <?php else : ?>

              <?php do_action( 'fictioneer_stories_no_results', array(
                'current_page' => $current_page,
                'post_id' => $post_id,
                'stories' => $list_of_stories,
                'queried_type' => 'fcn_story',
                'query_args' => $query_args,
                'order' => $order,
                'orderby' => $orderby,
                'ago' => $ago,
              ) ); ?>

              <li class="illusia-stories__no-results">
                <span><?php esc_html_e( 'No stories found.', 'fictioneer' ); ?></span>
              </li>

            <?php endif; wp_reset_postdata(); ?>

            <?php // ── Pagination ── ?>

            <?php if ( $list_of_stories->max_num_pages > 1 ) : ?>
              <li class="illusia-stories__pagination">
                <?php
                  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fictioneer_paginate_links() wraps core paginate_links()
                  echo wp_kses_post( fictioneer_paginate_links( array(
                    'current' => $current_page,
                    'total' => $list_of_stories->max_num_pages,
                    'prev_text' => fcntr( 'previous' ),
                    'next_text' => fcntr( 'next' ),
                    'add_fragment' => '#list-of-stories',
                  ) ) );
                ?>
              </li>
            <?php endif; ?>

          </ul>
        </section>

      </article>

    <?php endwhile; ?>

  </div>

  <?php do_action( 'fictioneer_main_end', 'stories' ); ?>

</main>

<?php
  $footer_args = array(
    'post_type' => 'page',
    'post_id' => $post_id,
    'current_page' => $current_page,
    'stories' => $list_of_stories,
    'breadcrumbs' => array(
      [ fcntr( 'frontpage' ), get_home_url() ],
    ),
  );

  $footer_args['breadcrumbs'][] = $this_breadcrumb;

  get_footer( null, $footer_args );
?>
