<?php
/**
 * Illusia Atmosphere — Orbs de luz ambiente
 *
 * Injeta elementos decorativos fixos no body para criar a camada
 * atmosférica do Dark Editorial Observatory.
 *
 * @package Illusia_Theme
 * @since   1.2.0
 */

/**
 * Output ambient light orbs in the body.
 *
 * Hooked to 'fictioneer_body' to inject fixed-position decorative
 * elements that create subtle ambient lighting. Orbs are pure CSS
 * (styled in illusia-atmosphere.css) with pointer-events: none.
 *
 * @since 1.2.0
 *
 * @param array $args Optional. Action arguments from Fictioneer.
 */
function illusia_render_atmosphere_orbs( array $args = [] ): void {
  ?>
  <div class="illusia-orb illusia-orb--amber" aria-hidden="true"></div>
  <div class="illusia-orb illusia-orb--violet" aria-hidden="true"></div>
  <?php
}
