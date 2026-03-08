<?php
/**
 * Template Name: Collections
 *
 * Override do template Fictioneer para a página /collections/.
 * Renderiza lista paginada de collection cards com layout
 * "Dark Editorial Observatory".
 *
 * Preserva a query logic, caching, filtros e sort UI do Fictioneer.
 * Substitui card list (prioridade 30) do hook
 * fictioneer_collections_after_content por markup Illusia.
 * Sort UI (prioridade 20) renderizada diretamente via função do pai.
 *
 * @package Illusia Theme
 * @since 1.9.0
 * @see fictioneer/collections.php (template pai)
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

$query_args = array(
  'fictioneer_query_name' => 'collections_list',
  'post_type' => 'fcn_collection',
  'post_status' => 'publish',
  'order' => $order,
  'orderby' => $orderby,
  'paged' => $current_page,
  'posts_per_page' => get_option( 'posts_per_page', 8 ),
  'update_post_term_cache' => ! get_option( 'fictioneer_hide_taxonomies_on_collection_cards' ),
);

// Date query
$query_args = fictioneer_append_date_query( $query_args, $ago, $orderby );

// Filter (compatibilidade com plugins)
$query_args = apply_filters( 'fictioneer_filter_collections_query_args', $query_args, $post_id );

// Execute query
$list_of_collections = new WP_Query( $query_args );

// Prime caches
if ( function_exists( 'update_post_thumbnail_cache' ) ) {
  update_post_thumbnail_cache( $list_of_collections );
}

if ( function_exists( 'update_post_author_caches' ) ) {
  update_post_author_caches( $list_of_collections->posts );
}

// Card arguments
$card_args = array(
  'cache' => fictioneer_caching_active( 'card_args' ) && ! fictioneer_private_caching_active(),
  'order' => $order,
  'orderby' => $orderby,
  'ago' => $ago,
);

$card_args = apply_filters( 'fictioneer_filter_collections_card_args', $card_args, array(
  'current_page' => $current_page,
  'post_id' => $post_id,
  'collections' => $list_of_collections,
  'queried_type' => 'fcn_collection',
  'query_args' => $query_args,
  'order' => $order,
  'orderby' => $orderby,
  'ago' => $ago,
) );

// Header
get_header();

?>

<main id="main" class="main singular collections illusia-collections">

  <?php do_action( 'fictioneer_main', 'collections' ); ?>

  <div class="main__wrapper">

    <?php do_action( 'fictioneer_main_wrapper' ); ?>

    <?php while ( have_posts() ) : the_post(); ?>

      <?php
        $title = trim( get_the_title() );
        $breadcrumb_name = empty( $title ) ? __( 'Collections', 'fictioneer' ) : $title;
        $this_breadcrumb = [ $breadcrumb_name, get_the_permalink() ];
      ?>

      <article id="singular-<?php echo esc_attr( $post_id ); ?>" class="singular__article collections__article illusia-collections__article">

        <?php // ── Page Header ── ?>

        <?php if ( ! empty( $title ) ) : ?>
          <header class="illusia-collections__header">
            <h1 class="illusia-collections__title"><?php echo esc_html( $title ); ?></h1>
          </header>
        <?php endif; ?>

        <?php if ( get_the_content() ) : ?>
          <section class="singular__content collections__content illusia-collections__content content-section">
            <?php the_content(); ?>
          </section>
        <?php endif; ?>

        <?php // ── Sort/Order UI (do tema pai) ── ?>

        <?php
          $hook_args = array(
            'current_page' => $current_page,
            'post_id' => $post->ID,
            'collections' => $list_of_collections,
            'queried_type' => 'fcn_collection',
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

        <section class="illusia-collections__list">
          <ul id="list-of-collections" class="illusia-collections__cards scroll-margin-top">

            <?php if ( $list_of_collections->have_posts() ) : ?>

              <?php
                while ( $list_of_collections->have_posts() ) {
                  $list_of_collections->the_post();
                  fictioneer_get_template_part( 'partials/_card-collection', null, $card_args );
                }

                do_action( 'fictioneer_collections_end_of_results', array(
                  'current_page' => $current_page,
                  'post_id' => $post_id,
                  'collections' => $list_of_collections,
                  'queried_type' => 'fcn_collection',
                  'query_args' => $query_args,
                  'order' => $order,
                  'orderby' => $orderby,
                  'ago' => $ago,
                ) );
              ?>

            <?php else : ?>

              <?php do_action( 'fictioneer_collections_no_results', array(
                'current_page' => $current_page,
                'post_id' => $post_id,
                'collections' => $list_of_collections,
                'queried_type' => 'fcn_collection',
                'query_args' => $query_args,
                'order' => $order,
                'orderby' => $orderby,
                'ago' => $ago,
              ) ); ?>

              <li class="illusia-collections__no-results">
                <span><?php esc_html_e( 'No collections found.', 'fictioneer' ); ?></span>
              </li>

            <?php endif; wp_reset_postdata(); ?>

            <?php // ── Pagination ── ?>

            <?php if ( $list_of_collections->max_num_pages > 1 ) : ?>
              <li class="illusia-collections__pagination">
                <?php
                  echo wp_kses_post( fictioneer_paginate_links( array(
                    'current' => $current_page,
                    'total' => $list_of_collections->max_num_pages,
                    'prev_text' => fcntr( 'previous' ),
                    'next_text' => fcntr( 'next' ),
                    'add_fragment' => '#list-of-collections',
                  ) ) );
                ?>
              </li>
            <?php endif; ?>

          </ul>
        </section>

      </article>

    <?php endwhile; ?>

  </div>

  <?php do_action( 'fictioneer_main_end', 'collections' ); ?>

</main>

<?php
  $footer_args = array(
    'post_type' => 'page',
    'post_id' => $post_id,
    'current_page' => $current_page,
    'collections' => $list_of_collections,
    'breadcrumbs' => array(
      [ fcntr( 'frontpage' ), get_home_url() ],
    ),
  );

  $footer_args['breadcrumbs'][] = $this_breadcrumb;

  get_footer( null, $footer_args );
?>
