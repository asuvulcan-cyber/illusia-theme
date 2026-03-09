<?php
/**
 * Partial: Archive Loop — Illusia Override
 *
 * Substitui o _archive-loop.php do Fictioneer por markup Illusia.
 * Renderiza lista de cards tipados com sort UI (via hook do pai),
 * paginação estilizada e staggered entry animations.
 *
 * O hook fictioneer_archive_loop_before (prio 10) renderiza
 * a sort UI automaticamente — não precisamos chamá-la aqui.
 *
 * @package Illusia Theme
 * @since 1.11.0
 * @see fictioneer/partials/_archive-loop.php (partial pai)
 *
 * @internal $args['taxonomy']  A taxonomy atual do archive.
 */

use Fictioneer\Sanitizer;

// No direct access!
defined( 'ABSPATH' ) OR exit;

// Setup
$page = get_query_var( 'paged', 1 ) ?: 1;
$order = Sanitizer::sanitize_query_var( $_GET['order'] ?? 0, ['desc', 'asc'], 'desc' );
$orderby = Sanitizer::sanitize_query_var( $_GET['orderby'] ?? 0, fictioneer_allowed_orderby(), 'modified' );
$ago = $_GET['ago'] ?? 0;
$ago = is_numeric( $ago ) ? absint( $ago ) : sanitize_text_field( $ago );

// Sort-order-filter hook args
$hook_args = array(
  'page' => $page,
  'order' => $order,
  'orderby' => $orderby,
  'ago' => $ago,
  'taxonomy' => $args['taxonomy'],
);

?>

<?php do_action( 'fictioneer_archive_loop_before', $hook_args ); ?>

<?php if ( have_posts() ) : ?>

  <section class="illusia-list-page__list">
    <ul id="archive-list" class="illusia-list-page__cards scroll-margin-top">
      <?php
        while ( have_posts() ) {
          the_post();

          // Setup
          $type = get_post_type();
          $card_args = array(
            'cache' => fictioneer_caching_active( 'card_args' ) && ! fictioneer_private_caching_active(),
            'show_type' => true,
            'order' => $order,
            'orderby' => $orderby,
            'ago' => $ago,
          );

          // Skip hidden chapters
          if ( $type === 'fcn_chapter' ) {
            if (
              get_post_meta( $post->ID, 'fictioneer_chapter_no_chapter', true ) ||
              get_post_meta( $post->ID, 'fictioneer_chapter_hidden', true )
            ) {
              continue;
            }
          }

          // Echo correct card
          fictioneer_echo_card( $card_args );
        }

        // Pagination
        if ( $GLOBALS['wp_query']->found_posts > get_option( 'posts_per_page' ) ) {
          ?>
          <li class="illusia-list-page__pagination">
            <?php
              echo wp_kses_post( fictioneer_paginate_links( array(
                'prev_text' => fcntr( 'previous' ),
                'next_text' => fcntr( 'next' ),
                'add_fragment' => '#archive-list',
              ) ) );
            ?>
          </li>
          <?php
        }
      ?>
    </ul>
  </section>

<?php else : ?>

  <section class="illusia-list-page__list">
    <ul id="archive-list" class="illusia-list-page__cards scroll-margin-top">
      <li class="illusia-list-page__no-results">
        <span><?php esc_html_e( 'No matching posts found.', 'fictioneer' ); ?></span>
      </li>
    </ul>
  </section>

<?php endif; ?>

<?php do_action( 'fictioneer_archive_loop_after', $hook_args ); ?>
