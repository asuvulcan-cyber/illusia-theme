<?php
/**
 * Character Sheet System — Ownership & Capabilities
 *
 * Rastreia criador de termos fcn_character, concede capabilities
 * a autores para editar/deletar termos próprios e filtra a lista
 * no admin para mostrar apenas termos do autor.
 *
 * @package Illusia Theme
 * @since 1.12.0
 */

// No direct access!
defined( 'ABSPATH' ) OR exit;

// =============================================================================
// GRANT CAPABILITIES TO AUTHORS
// =============================================================================

/**
 * Grant edit_fcn_characters and delete_fcn_characters to author role.
 *
 * Runs once — capabilities persist in the DB. Checks with has_cap()
 * to avoid repeated writes.
 *
 * @since 1.12.0
 */
function illusia_char_grant_author_caps(): void {
  // Only run once per version — capabilities persist in the DB
  $option_key = 'illusia_char_caps_version';
  $current = get_option( $option_key, '' );

  if ( $current === CHILD_VERSION ) {
    return;
  }

  $roles = array( 'author', 'contributor' );
  $caps  = array( 'edit_fcn_characters', 'delete_fcn_characters' );

  foreach ( $roles as $role_name ) {
    $role = get_role( $role_name );
    if ( ! $role ) {
      continue;
    }

    foreach ( $caps as $cap ) {
      if ( ! $role->has_cap( $cap ) ) {
        $role->add_cap( $cap );
      }
    }
  }

  update_option( $option_key, CHILD_VERSION );
}
add_action( 'after_setup_theme', 'illusia_char_grant_author_caps' );

// =============================================================================
// SET CREATOR ON TERM CREATION
// =============================================================================

/**
 * Automatically set the current user as creator when a character term is created.
 *
 * Priority 5 — runs before illusia_char_save_meta (priority 10).
 *
 * @since 1.12.0
 *
 * @param int $term_id Term ID.
 */
function illusia_char_set_creator( int $term_id ): void {
  $user_id = get_current_user_id();

  if ( $user_id > 0 ) {
    update_term_meta( $term_id, 'illusia_char_creator', $user_id );
  }
}
add_action( 'created_fcn_character', 'illusia_char_set_creator', 5 );

// =============================================================================
// OWNERSHIP CHECK HELPER
// =============================================================================

/**
 * Check if a user can edit a specific character term.
 *
 * Editors and admins can edit any term. Authors can only edit
 * terms they created.
 *
 * @since 1.12.0
 *
 * @param int $term_id Term ID.
 * @param int $user_id User ID. Defaults to current user.
 * @return bool Whether the user can edit this term.
 */
function illusia_user_can_edit_character( int $term_id, int $user_id = 0 ): bool {
  if ( $user_id <= 0 ) {
    $user_id = get_current_user_id();
  }

  if ( $user_id <= 0 ) {
    return false;
  }

  // Editors and admins bypass ownership check
  if ( user_can( $user_id, 'manage_options' ) || user_can( $user_id, 'edit_others_posts' ) ) {
    return true;
  }

  // Check ownership
  $creator = absint( get_term_meta( $term_id, 'illusia_char_creator', true ) );

  return $creator === $user_id;
}

// =============================================================================
// MAP META CAP — RESTRICT EDIT/DELETE TO OWNERS
// =============================================================================

/**
 * Filter map_meta_cap to restrict character term editing to owners.
 *
 * Intercepts edit_term and delete_term capability checks for
 * fcn_character terms. Authors pass only if they own the term.
 *
 * @since 1.12.0
 *
 * @param string[] $caps    Required primitive capabilities.
 * @param string   $cap     Capability being checked.
 * @param int      $user_id User ID.
 * @param array    $args    Additional arguments (term ID in $args[0]).
 * @return string[] Modified capabilities.
 */
function illusia_char_map_meta_cap( array $caps, string $cap, int $user_id, array $args ): array {
  // Only intercept term edit/delete caps
  if ( ! in_array( $cap, array( 'edit_term', 'delete_term' ), true ) ) {
    return $caps;
  }

  // Need a term ID
  if ( empty( $args[0] ) ) {
    return $caps;
  }

  $term_id = absint( $args[0] );
  $term = get_term( $term_id );

  // Only affect fcn_character terms
  if ( ! $term || is_wp_error( $term ) || $term->taxonomy !== 'fcn_character' ) {
    return $caps;
  }

  // Editors and admins pass through
  if ( user_can( $user_id, 'manage_options' ) || user_can( $user_id, 'edit_others_posts' ) ) {
    $primitive = ( $cap === 'edit_term' ) ? 'edit_fcn_characters' : 'delete_fcn_characters';
    return array( $primitive );
  }

  // Authors — check ownership
  $creator = absint( get_term_meta( $term_id, 'illusia_char_creator', true ) );

  if ( $creator > 0 && $creator === $user_id ) {
    $primitive = ( $cap === 'edit_term' ) ? 'edit_fcn_characters' : 'delete_fcn_characters';
    return array( $primitive );
  }

  // Deny — term has no creator or different creator
  return array( 'do_not_allow' );
}
add_filter( 'map_meta_cap', 'illusia_char_map_meta_cap', 10000, 4 );

// =============================================================================
// FILTER TERM LIST FOR AUTHORS
// =============================================================================

/**
 * Filter the admin term list to show only own terms for authors.
 *
 * Editors and admins see all terms.
 *
 * @since 1.12.0
 *
 * @param WP_Term_Query $query Term query instance.
 */
function illusia_char_filter_term_list( WP_Term_Query $query ): void {
  // Only on the admin term list screen (not AJAX, not other admin pages)
  if ( ! is_admin() || wp_doing_ajax() ) {
    return;
  }

  // Scope to the term list screen only (not edit-term, not other screens)
  $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
  if ( $screen && $screen->base !== 'edit-tags' ) {
    return;
  }

  // Only for fcn_character
  $taxonomies = $query->query_vars['taxonomy'] ?? array();
  if ( ! is_array( $taxonomies ) ) {
    $taxonomies = array( $taxonomies );
  }

  if ( ! in_array( 'fcn_character', $taxonomies, true ) ) {
    return;
  }

  // Editors and admins see everything
  if ( current_user_can( 'manage_options' ) || current_user_can( 'edit_others_posts' ) ) {
    return;
  }

  // Authors see only their own terms
  $user_id = get_current_user_id();
  if ( $user_id <= 0 ) {
    return;
  }

  $query->query_vars['meta_query'] = array_merge(
    $query->query_vars['meta_query'] ?? array(),
    array(
      array(
        'key'   => 'illusia_char_creator',
        'value' => $user_id,
        'type'  => 'NUMERIC',
      ),
    )
  );
}
add_action( 'pre_get_terms', 'illusia_char_filter_term_list' );

// =============================================================================
// OWNERSHIP ASSIGNMENT (edit form — editors+ only)
// =============================================================================

/**
 * Render owner assignment field on the edit term form (editors+ only).
 *
 * @since 1.12.0
 *
 * @param WP_Term $term Current term being edited.
 */
function illusia_char_ownership_field( WP_Term $term ): void {
  // Only editors and admins can reassign ownership
  if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_others_posts' ) ) {
    return;
  }

  $creator_id = absint( get_term_meta( $term->term_id, 'illusia_char_creator', true ) );
  $creator = $creator_id > 0 ? get_userdata( $creator_id ) : null;
  ?>

  <tr class="form-field illusia-char-field">
    <th scope="row"><label for="illusia_char_creator">Criador / Dono</label></th>
    <td>
      <input type="number" name="illusia_char_creator" id="illusia_char_creator"
        value="<?php echo esc_attr( $creator_id ); ?>" min="0" style="width:80px" />
      <?php if ( $creator ) : ?>
        <span class="description">(<?php echo esc_html( $creator->display_name ); ?>)</span>
      <?php elseif ( $creator_id > 0 ) : ?>
        <span class="description">(usuário ID <?php echo esc_html( $creator_id ); ?> não encontrado)</span>
      <?php else : ?>
        <span class="description">(sem dono — defina o ID do usuário)</span>
      <?php endif; ?>
      <p class="description">ID do usuário dono deste termo. Apenas editores/admins podem alterar.</p>
    </td>
  </tr>

  <?php
}
add_action( 'fcn_character_edit_form_fields', 'illusia_char_ownership_field', 20 );

/**
 * Save ownership assignment (editors+ only).
 *
 * @since 1.12.0
 *
 * @param int $term_id Term ID.
 */
function illusia_char_save_ownership( int $term_id ): void {
  // Nonce first, then capability
  if ( ! isset( $_POST['illusia_char_nonce'] )
    || ! wp_verify_nonce( $_POST['illusia_char_nonce'], 'illusia_char_save' ) ) {
    return;
  }

  if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_others_posts' ) ) {
    return;
  }

  if ( isset( $_POST['illusia_char_creator'] ) ) {
    $new_creator = absint( $_POST['illusia_char_creator'] );

    if ( $new_creator > 0 ) {
      update_term_meta( $term_id, 'illusia_char_creator', $new_creator );
    } else {
      delete_term_meta( $term_id, 'illusia_char_creator' );
    }
  }
}
add_action( 'edited_fcn_character', 'illusia_char_save_ownership', 5 );
