<?php
/**
 * Partial: Character Index — Árvore Hierárquica
 *
 * Renderiza uma árvore de navegação para termos do tipo
 * obra/local/organização, mostrando filhos com badges inline
 * e linhas conectoras via CSS (sem agrupamento por tipo).
 *
 * Esperado no escopo: $term (WP_Term), $meta (array from illusia_get_char_meta),
 * $taxonomy_color (string).
 *
 * @package Illusia Theme
 * @since 1.12.0
 */

// No direct access!
defined( 'ABSPATH' ) OR exit;

// Build the recursive tree
$tree = illusia_get_char_tree( $term->term_id );
?>

<?php if ( ! empty( $tree ) ) : ?>
  <nav class="illusia-char-index" aria-label="Índice hierárquico">
    <h2 class="illusia-char-index__heading">Índice</h2>
    <?php illusia_render_char_tree( $tree, 0 ); ?>
  </nav>
<?php endif; ?>
