<?php
/**
 * Template Name: Chapters
 *
 * Override do template Fictioneer para a página /chapters/.
 * Renderiza lista paginada de chapter cards com layout
 * "Dark Editorial Observatory".
 *
 * Preserva a query logic, caching, filtros e sort UI do Fictioneer.
 * Sort UI renderizada diretamente via função do pai.
 *
 * @package Illusia Theme
 * @since 1.11.0
 * @see fictioneer/chapters.php (template pai)
 */

// No direct access!
defined( 'ABSPATH' ) OR exit;

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
  'fictioneer_query_name' => 'chapters_list',
  'post_type' => 'fcn_chapter',
  'post_status' => 'publish',
  'order' => $order,
  'orderby' => $orderby,
  'paged' => $current_page,
  'posts_per_page' => get_option( 'posts_per_page', 8 ),
  'update_post_term_cache' => ! get_option( 'fictioneer_hide_taxonomies_on_chapter_cards' ),
);

// Meta query: hidden chapters
if ( get_option( 'fictioneer_disable_extended_chapter_list_meta_queries' ) ) {
  $meta_query_stack[] = array(
    array(
      'key' => 'fictioneer_chapter_hidden',
      'value' => '0',
    ),
  );
} else {
  $meta_query_stack[] = array(
    'relation' => 'OR',
    array(
      'key' => 'fictioneer_chapter_hidden',
      'value' => '0',
    ),
    array(
      'key' => 'fictioneer_chapter_hidden',
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
  $query_args['meta_key'] = '_word_count';
}

// Date query
$query_args = fictioneer_append_date_query( $query_args, $ago, $orderby );

// Filter (compatibilidade com plugins)
$query_args = apply_filters( 'fictioneer_filter_chapters_query_args', $query_args, $post_id );

// Execute query
$list_of_chapters = new WP_Query( $query_args );

// Prime caches
if ( function_exists( 'update_post_thumbnail_cache' ) ) {
  update_post_thumbnail_cache( $list_of_chapters );
}

if ( function_exists( 'update_post_author_caches' ) ) {
  update_post_author_caches( $list_of_chapters->posts );
}

// Card arguments
$card_args = array(
  'cache' => fictioneer_caching_active( 'card_args' ) && ! fictioneer_private_caching_active(),
  'order' => $order,
  'orderby' => $orderby,
  'ago' => $ago,
);

$card_args = apply_filters( 'fictioneer_filter_chapters_card_args', $card_args, array(
  'current_page' => $current_page,
  'post_id' => $post_id,
  'chapters' => $list_of_chapters,
  'queried_type' => 'fcn_chapter',
  'query_args' => $query_args,
  'order' => $order,
  'orderby' => $orderby,
  'ago' => $ago,
) );

// Header
get_header();

?>

<main id="main" class="main singular chapters illusia-list-page illusia-chapters">

  <?php do_action( 'fictioneer_main', 'chapters' ); ?>

  <div class="main__wrapper">

    <?php do_action( 'fictioneer_main_wrapper' ); ?>

    <?php while ( have_posts() ) : the_post(); ?>

      <?php
        $title = trim( get_the_title() );
        $breadcrumb_name = empty( $title ) ? __( 'Chapters', 'fictioneer' ) : $title;
        $this_breadcrumb = [ $breadcrumb_name, get_the_permalink() ];
      ?>

      <article id="singular-<?php echo esc_attr( $post_id ); ?>" class="singular__article chapters__article illusia-list-page__article">

        <?php // ── Page Header ── ?>

        <?php if ( ! empty( $title ) ) : ?>
          <header class="illusia-list-page__header">
            <span class="illusia-list-page__overline"><?php
              esc_html_e( 'Capítulos', 'fictioneer' );
            ?></span>
            <h1 class="illusia-list-page__title"><?php echo esc_html( $title ); ?></h1>
          </header>
        <?php endif; ?>

        <?php if ( get_the_content() ) : ?>
          <section class="singular__content chapters__content illusia-list-page__content content-section">
            <?php the_content(); ?>
          </section>
        <?php endif; ?>

        <?php // ── Sort/Order UI (do tema pai) ── ?>

        <?php
          $hook_args = array(
            'current_page' => $current_page,
            'post_id' => $post->ID,
            'chapters' => $list_of_chapters,
            'queried_type' => 'fcn_chapter',
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

        <section class="illusia-list-page__list">
          <ul id="list-of-chapters" class="illusia-list-page__cards scroll-margin-top">

            <?php if ( $list_of_chapters->have_posts() ) : ?>

              <?php
                while ( $list_of_chapters->have_posts() ) {
                  $list_of_chapters->the_post();

                  if ( get_post_meta( get_the_ID(), 'fictioneer_chapter_hidden', true ) ) {
                    fictioneer_get_template_part( 'partials/_card-hidden', null, $card_args );
                  } else {
                    fictioneer_get_template_part( 'partials/_card-chapter', null, $card_args );
                  }
                }

                do_action( 'fictioneer_chapters_end_of_results', array(
                  'current_page' => $current_page,
                  'post_id' => $post_id,
                  'chapters' => $list_of_chapters,
                  'queried_type' => 'fcn_chapter',
                  'query_args' => $query_args,
                  'order' => $order,
                  'orderby' => $orderby,
                  'ago' => $ago,
                ) );
              ?>

            <?php else : ?>

              <?php do_action( 'fictioneer_chapters_no_results', array(
                'current_page' => $current_page,
                'post_id' => $post_id,
                'chapters' => $list_of_chapters,
                'queried_type' => 'fcn_chapter',
                'query_args' => $query_args,
                'order' => $order,
                'orderby' => $orderby,
                'ago' => $ago,
              ) ); ?>

              <li class="illusia-list-page__no-results">
                <span><?php esc_html_e( 'No chapters found.', 'fictioneer' ); ?></span>
              </li>

            <?php endif; wp_reset_postdata(); ?>

            <?php // ── Pagination ── ?>

            <?php if ( $list_of_chapters->max_num_pages > 1 ) : ?>
              <li class="illusia-list-page__pagination">
                <?php
                  echo wp_kses_post( fictioneer_paginate_links( array(
                    'current' => $current_page,
                    'total' => $list_of_chapters->max_num_pages,
                    'prev_text' => fcntr( 'previous' ),
                    'next_text' => fcntr( 'next' ),
                    'add_fragment' => '#list-of-chapters',
                  ) ) );
                ?>
              </li>
            <?php endif; ?>

          </ul>
        </section>

      </article>

    <?php endwhile; ?>

  </div>

  <?php do_action( 'fictioneer_main_end', 'chapters' ); ?>

</main>

<?php
  $footer_args = array(
    'post_type' => 'page',
    'post_id' => $post_id,
    'current_page' => $current_page,
    'chapters' => $list_of_chapters,
    'breadcrumbs' => array(
      [ fcntr( 'frontpage' ), get_home_url() ],
    ),
  );

  $footer_args['breadcrumbs'][] = $this_breadcrumb;

  get_footer( null, $footer_args );
?>
