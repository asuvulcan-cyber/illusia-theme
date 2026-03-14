<?php
/**
 * Character Sheet System — Meta Foundation
 *
 * Registra term meta para fcn_character, define constantes
 * e helpers reutilizáveis em admin e front-end.
 *
 * @package Illusia Theme
 * @since 1.12.0
 */

// No direct access!
defined( 'ABSPATH' ) OR exit;

// =============================================================================
// CONSTANTS
// =============================================================================

/**
 * Tipos permitidos para illusia_char_type.
 */
define( 'ILLUSIA_CHAR_TYPES', array(
  'obra'         => 'Obra',
  'local'        => 'Local',
  'organizacao'  => 'Organização',
  'personagem'   => 'Personagem',
) );

/**
 * Option key para tipos de relacionamento customizados.
 */
define( 'ILLUSIA_CHAR_RELATION_OPTION', 'illusia_char_relation_types' );

/**
 * Tipos de relacionamento padrão (fallback quando option está vazio).
 */
define( 'ILLUSIA_CHAR_RELATION_DEFAULTS', array(
  'irmao_de'       => 'Irmão(ã) de',
  'pai_mae_de'     => 'Pai/Mãe de',
  'filho_de'       => 'Filho(a) de',
  'mentor_de'      => 'Mentor(a) de',
  'aprendiz_de'    => 'Aprendiz de',
  'aliado_de'      => 'Aliado(a) de',
  'rival_de'       => 'Rival de',
  'membro_de'      => 'Membro de',
  'lider_de'       => 'Líder de',
  'servo_de'       => 'Servo(a) de',
  'amigo_de'       => 'Amigo(a) de',
  'inimigo_de'     => 'Inimigo(a) de',
  'conjuge_de'     => 'Cônjuge de',
  'subordinado_de' => 'Subordinado(a) de',
  'superior_de'    => 'Superior de',
) );

/**
 * Templates de exibição para ficha de personagem.
 */
define( 'ILLUSIA_CHAR_TEMPLATES', array(
  'padrao'   => 'Padrão',
  'compacto' => 'Compacto',
  'wiki'     => 'Wiki',
  'tcg'      => 'Carta de TCG',
  'rpg'      => 'Ficha RPG',
) );

// =============================================================================
// REGISTER TERM META
// =============================================================================

/**
 * Register all illusia_char_* meta keys for fcn_character.
 *
 * @since 1.12.0
 */
function illusia_register_character_term_meta(): void {
  $string_args = array(
    'type'              => 'string',
    'single'            => true,
    'sanitize_callback' => 'sanitize_text_field',
    'show_in_rest'      => false,
  );

  $textarea_args = array(
    'type'              => 'string',
    'single'            => true,
    'sanitize_callback' => 'wp_kses_post',
    'show_in_rest'      => false,
  );

  // All types
  register_term_meta( 'fcn_character', 'illusia_char_type', array(
    'type'              => 'string',
    'single'            => true,
    'sanitize_callback' => 'illusia_sanitize_char_type',
    'show_in_rest'      => false,
  ) );

  register_term_meta( 'fcn_character', 'illusia_char_image', array(
    'type'              => 'string',
    'single'            => true,
    'sanitize_callback' => 'esc_url_raw',
    'show_in_rest'      => false,
  ) );

  // Personagem only
  register_term_meta( 'fcn_character', 'illusia_char_full_name', $string_args );
  register_term_meta( 'fcn_character', 'illusia_char_titles', $string_args );
  register_term_meta( 'fcn_character', 'illusia_char_appearance', $textarea_args );
  register_term_meta( 'fcn_character', 'illusia_char_personality', $textarea_args );
  register_term_meta( 'fcn_character', 'illusia_char_notes', $textarea_args );

  // Relationships — stored as JSON string
  register_term_meta( 'fcn_character', 'illusia_char_relationships', array(
    'type'              => 'string',
    'single'            => true,
    'sanitize_callback' => 'illusia_sanitize_char_relationships',
    'show_in_rest'      => false,
  ) );

  // Gallery — JSON array of image URLs (personagem only)
  register_term_meta( 'fcn_character', 'illusia_char_gallery', array(
    'type'              => 'string',
    'single'            => true,
    'sanitize_callback' => 'illusia_sanitize_char_gallery',
    'show_in_rest'      => false,
  ) );

  // Former organizations — JSON array of term IDs (personagem only)
  register_term_meta( 'fcn_character', 'illusia_char_former_orgs', array(
    'type'              => 'string',
    'single'            => true,
    'sanitize_callback' => 'illusia_sanitize_char_former_orgs',
    'show_in_rest'      => false,
  ) );

  // Display template (personagem only)
  register_term_meta( 'fcn_character', 'illusia_char_template', array(
    'type'              => 'string',
    'single'            => true,
    'sanitize_callback' => 'illusia_sanitize_char_template',
    'show_in_rest'      => false,
  ) );

  // Ownership
  register_term_meta( 'fcn_character', 'illusia_char_creator', array(
    'type'              => 'integer',
    'single'            => true,
    'sanitize_callback' => 'absint',
    'show_in_rest'      => false,
  ) );
}
add_action( 'init', 'illusia_register_character_term_meta' );

// =============================================================================
// SANITIZATION CALLBACKS
// =============================================================================

/**
 * Sanitize illusia_char_type against allowed values.
 *
 * @since 1.12.0
 *
 * @param string $value Raw value.
 * @return string Sanitized type or empty string.
 */
function illusia_sanitize_char_type( string $value ): string {
  $value = sanitize_key( $value );

  return array_key_exists( $value, ILLUSIA_CHAR_TYPES ) ? $value : '';
}

/**
 * Sanitize relationships JSON.
 *
 * Expects JSON string encoding an array of {term_id, rel} objects.
 * Returns sanitized JSON string or empty string.
 *
 * @since 1.12.0
 *
 * @param string $value Raw JSON string.
 * @return string Sanitized JSON string.
 */
function illusia_sanitize_char_relationships( string $value ): string {
  if ( empty( $value ) ) {
    return '';
  }

  $data = json_decode( $value, true );

  if ( ! is_array( $data ) ) {
    return '';
  }

  $clean = array();

  foreach ( $data as $entry ) {
    $term_id = absint( $entry['term_id'] ?? 0 );
    $rel     = sanitize_key( $entry['rel'] ?? '' );

    if ( $term_id > 0 && ! empty( $rel ) ) {
      $clean[] = array(
        'term_id' => $term_id,
        'rel'     => $rel,
      );
    }
  }

  return ! empty( $clean ) ? wp_json_encode( $clean ) : '';
}

/**
 * Sanitize gallery JSON (array of URLs).
 *
 * @since 1.12.1
 *
 * @param string $value Raw JSON string.
 * @return string Sanitized JSON string.
 */
function illusia_sanitize_char_gallery( string $value ): string {
  if ( empty( $value ) ) {
    return '';
  }

  $data = json_decode( $value, true );

  if ( ! is_array( $data ) ) {
    return '';
  }

  $clean = array_values( array_filter( array_map( 'esc_url_raw', $data ) ) );

  return ! empty( $clean ) ? wp_json_encode( $clean ) : '';
}

/**
 * Sanitize former organizations JSON (array of term IDs).
 *
 * @since 1.12.1
 *
 * @param string $value Raw JSON string.
 * @return string Sanitized JSON string.
 */
function illusia_sanitize_char_former_orgs( string $value ): string {
  if ( empty( $value ) ) {
    return '';
  }

  $data = json_decode( $value, true );

  if ( ! is_array( $data ) ) {
    return '';
  }

  $clean = array_values( array_filter( array_map( 'absint', $data ) ) );

  return ! empty( $clean ) ? wp_json_encode( $clean ) : '';
}

/**
 * Sanitize template slug against allowed values.
 *
 * @since 1.12.2
 *
 * @param string $value Raw value.
 * @return string Sanitized template slug or 'padrao'.
 */
function illusia_sanitize_char_template( string $value ): string {
  $value = sanitize_key( $value );

  return array_key_exists( $value, ILLUSIA_CHAR_TEMPLATES ) ? $value : 'padrao';
}

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

/**
 * Get the character type for a term.
 *
 * @since 1.12.0
 *
 * @param int $term_id Term ID.
 * @return string Type slug or empty string if not set.
 */
function illusia_get_char_type( int $term_id ): string {
  $type = get_term_meta( $term_id, 'illusia_char_type', true );

  return is_string( $type ) && array_key_exists( $type, ILLUSIA_CHAR_TYPES ) ? $type : '';
}

/**
 * Get the human-readable label for a character type.
 *
 * @since 1.12.0
 *
 * @param string $type Type slug.
 * @return string Label or empty string.
 */
function illusia_get_char_type_label( string $type ): string {
  return ILLUSIA_CHAR_TYPES[ $type ] ?? '';
}

/**
 * Get all meta for a character term, with defaults.
 *
 * @since 1.12.0
 *
 * @param int $term_id Term ID.
 * @return array Associative array of meta values.
 */
function illusia_get_char_meta( int $term_id ): array {
  return array(
    'type'          => illusia_get_char_type( $term_id ),
    'image'         => get_term_meta( $term_id, 'illusia_char_image', true ) ?: '',
    'full_name'     => get_term_meta( $term_id, 'illusia_char_full_name', true ) ?: '',
    'titles'        => get_term_meta( $term_id, 'illusia_char_titles', true ) ?: '',
    'appearance'    => get_term_meta( $term_id, 'illusia_char_appearance', true ) ?: '',
    'personality'   => get_term_meta( $term_id, 'illusia_char_personality', true ) ?: '',
    'notes'         => get_term_meta( $term_id, 'illusia_char_notes', true ) ?: '',
    'relationships' => illusia_get_char_relationships( $term_id ),
    'gallery'       => illusia_get_char_gallery( $term_id ),
    'former_orgs'   => illusia_get_char_former_orgs( $term_id ),
    'template'      => illusia_get_char_template( $term_id ),
    'creator'       => absint( get_term_meta( $term_id, 'illusia_char_creator', true ) ),
  );
}

/**
 * Get decoded relationships array for a term.
 *
 * @since 1.12.0
 *
 * @param int $term_id Term ID.
 * @return array Array of {term_id, rel} entries.
 */
function illusia_get_char_relationships( int $term_id ): array {
  $json = get_term_meta( $term_id, 'illusia_char_relationships', true );

  if ( empty( $json ) ) {
    return array();
  }

  $data = json_decode( $json, true );

  return is_array( $data ) ? $data : array();
}

/**
 * Get all available relationship types (defaults + custom from option).
 *
 * @since 1.12.0
 *
 * @return array Associative array of slug => label.
 */
function illusia_get_relation_types(): array {
  $custom = get_option( ILLUSIA_CHAR_RELATION_OPTION, array() );

  if ( ! is_array( $custom ) || empty( $custom ) ) {
    return ILLUSIA_CHAR_RELATION_DEFAULTS;
  }

  return array_merge( ILLUSIA_CHAR_RELATION_DEFAULTS, $custom );
}

/**
 * Get decoded gallery array for a term.
 *
 * @since 1.12.1
 *
 * @param int $term_id Term ID.
 * @return array Array of image URLs.
 */
function illusia_get_char_gallery( int $term_id ): array {
  $json = get_term_meta( $term_id, 'illusia_char_gallery', true );

  if ( empty( $json ) ) {
    return array();
  }

  $data = json_decode( $json, true );

  return is_array( $data ) ? $data : array();
}

/**
 * Get decoded former organizations array for a term.
 *
 * @since 1.12.1
 *
 * @param int $term_id Term ID.
 * @return array Array of term IDs.
 */
function illusia_get_char_former_orgs( int $term_id ): array {
  $json = get_term_meta( $term_id, 'illusia_char_former_orgs', true );

  if ( empty( $json ) ) {
    return array();
  }

  $data = json_decode( $json, true );

  return is_array( $data ) ? array_map( 'absint', $data ) : array();
}

/**
 * Get the display template for a character term.
 *
 * @since 1.12.2
 *
 * @param int $term_id Term ID.
 * @return string Template slug (defaults to 'padrao').
 */
function illusia_get_char_template( int $term_id ): string {
  $tpl = get_term_meta( $term_id, 'illusia_char_template', true );

  return is_string( $tpl ) && array_key_exists( $tpl, ILLUSIA_CHAR_TEMPLATES ) ? $tpl : 'padrao';
}

/**
 * Walk the parent chain of a term and return ancestors with their types.
 *
 * Returns array ordered from root (obra) to direct parent.
 *
 * @since 1.12.0
 *
 * @param int $term_id Term ID.
 * @return array Array of ['term' => WP_Term, 'type' => string, 'label' => string].
 */
function illusia_get_char_ancestors_with_types( int $term_id ): array {
  $ancestor_ids = get_ancestors( $term_id, 'fcn_character', 'taxonomy' );
  $chain = array();

  foreach ( $ancestor_ids as $aid ) {
    $term = get_term( $aid, 'fcn_character' );

    if ( ! $term || is_wp_error( $term ) ) {
      continue;
    }

    $type = illusia_get_char_type( $aid );

    $chain[] = array(
      'term'  => $term,
      'type'  => $type,
      'label' => illusia_get_char_type_label( $type ),
    );
  }

  // get_ancestors returns closest-first; reverse for root-first
  return array_reverse( $chain );
}

/**
 * Get direct children of a term, grouped by character type.
 *
 * @since 1.12.0
 *
 * @param int $term_id Term ID.
 * @return array Associative array of type => WP_Term[].
 */
function illusia_get_char_children_by_type( int $term_id ): array {
  $children = get_terms( array(
    'taxonomy'   => 'fcn_character',
    'parent'     => $term_id,
    'hide_empty' => false,
    'number'     => 200,
    'orderby'    => 'name',
    'order'      => 'ASC',
  ) );

  if ( is_wp_error( $children ) || empty( $children ) ) {
    return array();
  }

  $grouped = array();

  foreach ( $children as $child ) {
    $type = illusia_get_char_type( $child->term_id ) ?: 'sem_tipo';
    $grouped[ $type ][] = $child;
  }

  // Order groups by the ILLUSIA_CHAR_TYPES key order
  $ordered = array();
  foreach ( array_keys( ILLUSIA_CHAR_TYPES ) as $t ) {
    if ( isset( $grouped[ $t ] ) ) {
      $ordered[ $t ] = $grouped[ $t ];
    }
  }

  // Append untyped at the end
  if ( isset( $grouped['sem_tipo'] ) ) {
    $ordered['sem_tipo'] = $grouped['sem_tipo'];
  }

  return $ordered;
}

/**
 * Build a recursive tree of descendants for a term.
 *
 * @since 1.12.0
 *
 * @param int $term_id Term ID.
 * @param int $depth   Current depth (guard against infinite recursion).
 * @return array Array of nodes: ['term' => WP_Term, 'type' => string, 'children' => array].
 */
function illusia_get_char_tree( int $term_id, int $depth = 0 ): array {
  if ( $depth > 5 ) {
    return array();
  }

  $children = get_terms( array(
    'taxonomy'   => 'fcn_character',
    'parent'     => $term_id,
    'hide_empty' => false,
    'number'     => 200,
    'orderby'    => 'name',
    'order'      => 'ASC',
  ) );

  if ( is_wp_error( $children ) || empty( $children ) ) {
    return array();
  }

  $nodes = array();

  foreach ( $children as $child ) {
    $nodes[] = array(
      'term'     => $child,
      'type'     => illusia_get_char_type( $child->term_id ),
      'children' => illusia_get_char_tree( $child->term_id, $depth + 1 ),
    );
  }

  return $nodes;
}

// =============================================================================
// RENDER HELPERS
// =============================================================================

/**
 * Recursively render a character tree as nested HTML lists.
 *
 * Outputs a flat list sorted by type order then name, with inline
 * type badges. Uses simple nested <ul> with CSS connector lines
 * instead of grouping by type.
 *
 * @since 1.12.0
 * @modified 1.12.1 — removed type grouping for cleaner tree display
 *
 * @param array $nodes Array of tree nodes from illusia_get_char_tree().
 * @param int   $depth Current depth level.
 */
function illusia_render_char_tree( array $nodes, int $depth ): void {
  if ( empty( $nodes ) ) {
    return;
  }

  // Sort: by type order (obra → local → organizacao → personagem), then alphabetically
  $type_order = array_flip( array_keys( ILLUSIA_CHAR_TYPES ) );

  usort( $nodes, function( $a, $b ) use ( $type_order ) {
    $a_ord = $type_order[ $a['type'] ] ?? 99;
    $b_ord = $type_order[ $b['type'] ] ?? 99;

    if ( $a_ord !== $b_ord ) {
      return $a_ord - $b_ord;
    }

    return strcasecmp( $a['term']->name, $b['term']->name );
  } );

  ?>

  <ul class="illusia-char-index__tree">
    <?php foreach ( $nodes as $node ) :
      $child_term  = $node['term'];
      $child_type  = $node['type'];
      $child_label = illusia_get_char_type_label( $child_type );
      $has_children = ! empty( $node['children'] );
      $link  = get_term_link( $child_term );
      $count = $child_term->count;
      ?>

      <li class="<?php echo esc_attr( 'illusia-char-index__item' . ( $has_children ? ' illusia-char-index__item--parent' : '' ) ); ?>">
        <div class="illusia-char-index__node">
          <?php if ( ! empty( $child_type ) ) : ?>
            <span class="illusia-char-index__dot illusia-char-index__dot--<?php echo esc_attr( $child_type ); ?>"></span>
          <?php endif; ?>
          <a href="<?php echo esc_url( is_wp_error( $link ) ? '#' : $link ); ?>" class="illusia-char-index__link">
            <?php echo esc_html( $child_term->name ); ?>
          </a>
          <?php if ( ! empty( $child_label ) ) : ?>
            <span class="illusia-char-index__badge illusia-char-index__badge--<?php echo esc_attr( $child_type ); ?>">
              <?php echo esc_html( $child_label ); ?>
            </span>
          <?php endif; ?>
          <?php if ( $count > 0 ) : ?>
            <span class="illusia-char-index__count"><?php echo esc_html( number_format_i18n( $count ) ); ?></span>
          <?php endif; ?>
        </div>

        <?php if ( $has_children ) :
          illusia_render_char_tree( $node['children'], $depth + 1 );
        endif; ?>
      </li>

    <?php endforeach; ?>
  </ul>

  <?php
}
