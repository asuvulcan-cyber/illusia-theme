<?php
/**
 * Character Sheet System — Admin UI
 *
 * Form fields para add/edit de termos fcn_character,
 * save handlers, colunas no term list e AJAX search.
 *
 * @package Illusia Theme
 * @since 1.12.0
 */

// No direct access!
defined( 'ABSPATH' ) OR exit;

// =============================================================================
// ADMIN ENQUEUE
// =============================================================================

/**
 * Enqueue admin CSS/JS on fcn_character taxonomy screens only.
 *
 * @since 1.12.0
 *
 * @param string $hook Current admin page hook.
 */
function illusia_char_admin_enqueue( string $hook ): void {
  $screen = get_current_screen();

  if ( ! $screen || $screen->taxonomy !== 'fcn_character' ) {
    return;
  }

  wp_enqueue_style(
    'illusia-char-admin',
    get_stylesheet_directory_uri() . '/css/admin/illusia-char-admin.css',
    [],
    CHILD_VERSION
  );

  wp_enqueue_script(
    'illusia-char-admin',
    get_stylesheet_directory_uri() . '/js/illusia-char-admin.js',
    [],
    CHILD_VERSION,
    true
  );

  wp_localize_script( 'illusia-char-admin', 'illusiaCharAdmin', array(
    'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
    'searchNonce'   => wp_create_nonce( 'illusia_search_characters' ),
    'relationTypes' => illusia_get_relation_types(),
  ) );
}
add_action( 'admin_enqueue_scripts', 'illusia_char_admin_enqueue' );

// =============================================================================
// ADD FORM FIELDS (new term screen)
// =============================================================================

/**
 * Render fields on the "Add New Character" form.
 *
 * Shows only type + image (personagem fields appear on edit).
 *
 * @since 1.12.0
 *
 * @param string $taxonomy Taxonomy slug.
 */
function illusia_char_add_form_fields( string $taxonomy ): void {
  wp_nonce_field( 'illusia_char_save', 'illusia_char_nonce' );
  ?>

  <div class="form-field illusia-char-field">
    <label for="illusia_char_type">Tipo</label>
    <select name="illusia_char_type" id="illusia_char_type">
      <option value="">— Selecionar —</option>
      <?php foreach ( ILLUSIA_CHAR_TYPES as $slug => $label ) : ?>
        <option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></option>
      <?php endforeach; ?>
    </select>
    <p>Define o que este termo representa na hierarquia.</p>
  </div>

  <div class="form-field illusia-char-field">
    <label for="illusia_char_image">Imagem (URL)</label>
    <input type="url" name="illusia_char_image" id="illusia_char_image" value="" />
    <p>URL externa da imagem de referência.</p>
  </div>

  <?php
}
add_action( 'fcn_character_add_form_fields', 'illusia_char_add_form_fields' );

// =============================================================================
// EDIT FORM FIELDS (edit term screen)
// =============================================================================

/**
 * Render all fields on the "Edit Character" form.
 *
 * @since 1.12.0
 *
 * @param WP_Term $term Current term being edited.
 */
function illusia_char_edit_form_fields( WP_Term $term ): void {
  $meta = illusia_get_char_meta( $term->term_id );
  $is_personagem = ( $meta['type'] === 'personagem' );
  wp_nonce_field( 'illusia_char_save', 'illusia_char_nonce' );
  ?>

  <!-- Tipo -->
  <tr class="form-field illusia-char-field">
    <th scope="row"><label for="illusia_char_type">Tipo</label></th>
    <td>
      <select name="illusia_char_type" id="illusia_char_type">
        <option value="">— Selecionar —</option>
        <?php foreach ( ILLUSIA_CHAR_TYPES as $slug => $label ) : ?>
          <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $meta['type'], $slug ); ?>>
            <?php echo esc_html( $label ); ?>
          </option>
        <?php endforeach; ?>
      </select>
      <p class="description">Define o que este termo representa na hierarquia.</p>
    </td>
  </tr>

  <!-- Imagem (URL) -->
  <tr class="form-field illusia-char-field">
    <th scope="row"><label for="illusia_char_image">Imagem (URL)</label></th>
    <td>
      <input type="url" name="illusia_char_image" id="illusia_char_image"
        value="<?php echo esc_url( $meta['image'] ); ?>" class="large-text" />
      <?php if ( ! empty( $meta['image'] ) ) : ?>
        <div class="illusia-char-image-preview">
          <img src="<?php echo esc_url( $meta['image'] ); ?>" alt="" />
        </div>
      <?php endif; ?>
      <p class="description">URL externa da imagem de referência.</p>
    </td>
  </tr>

  <!-- Template de exibição (personagem only) -->
  <tr class="form-field illusia-char-field illusia-char-personagem-field" <?php echo $is_personagem ? '' : 'style="display:none"'; ?>>
    <th scope="row"><label for="illusia_char_template">Template</label></th>
    <td>
      <select name="illusia_char_template" id="illusia_char_template">
        <?php foreach ( ILLUSIA_CHAR_TEMPLATES as $tslug => $tlabel ) : ?>
          <option value="<?php echo esc_attr( $tslug ); ?>" <?php selected( $meta['template'], $tslug ); ?>>
            <?php echo esc_html( $tlabel ); ?>
          </option>
        <?php endforeach; ?>
      </select>
      <p class="description">Estilo visual da ficha no front-end.</p>
    </td>
  </tr>

  <!-- === Galeria (personagem only) === -->
  <tr class="form-field illusia-char-field illusia-char-personagem-field" <?php echo $is_personagem ? '' : 'style="display:none"'; ?>>
    <th scope="row">Galeria</th>
    <td>
      <div class="illusia-char-gallery" id="illusia-char-gallery">
        <div class="illusia-char-gallery-items" id="illusia-char-gallery-items">
          <?php foreach ( $meta['gallery'] as $url ) : ?>
            <div class="illusia-char-gallery-item" data-url="<?php echo esc_url( $url ); ?>">
              <img src="<?php echo esc_url( $url ); ?>" alt="" />
              <button type="button" class="illusia-char-gallery-remove">&times;</button>
              <input type="hidden" name="illusia_char_gallery[]" value="<?php echo esc_url( $url ); ?>" />
            </div>
          <?php endforeach; ?>
        </div>
        <div class="illusia-char-gallery-add">
          <input type="url" id="illusia-char-gallery-url" placeholder="Cole a URL da imagem..." class="regular-text" />
          <button type="button" class="button" id="illusia-char-gallery-add-btn">Adicionar</button>
        </div>
      </div>
      <p class="description">Imagens adicionais do personagem (além da imagem principal).</p>
    </td>
  </tr>

  <!-- === Campos exclusivos de Personagem === -->
  <tr class="form-field illusia-char-field illusia-char-personagem-field" <?php echo $is_personagem ? '' : 'style="display:none"'; ?>>
    <th scope="row"><label for="illusia_char_full_name">Nome Completo</label></th>
    <td>
      <input type="text" name="illusia_char_full_name" id="illusia_char_full_name"
        value="<?php echo esc_attr( $meta['full_name'] ); ?>" class="large-text" />
      <p class="description">Nome completo caso o nome do termo seja abreviado.</p>
    </td>
  </tr>

  <tr class="form-field illusia-char-field illusia-char-personagem-field" <?php echo $is_personagem ? '' : 'style="display:none"'; ?>>
    <th scope="row"><label for="illusia_char_titles">Títulos / Epítetos</label></th>
    <td>
      <input type="text" name="illusia_char_titles" id="illusia_char_titles"
        value="<?php echo esc_attr( $meta['titles'] ); ?>" class="large-text" />
      <p class="description">Ex: "Capitão do Navio Banido", "A Guilhotina"</p>
    </td>
  </tr>

  <tr class="form-field illusia-char-field illusia-char-personagem-field" <?php echo $is_personagem ? '' : 'style="display:none"'; ?>>
    <th scope="row"><label for="illusia_char_appearance">Aparência</label></th>
    <td>
      <textarea name="illusia_char_appearance" id="illusia_char_appearance"
        rows="4" class="large-text"><?php echo esc_textarea( $meta['appearance'] ); ?></textarea>
      <p class="description">Descrição física do personagem.</p>
    </td>
  </tr>

  <tr class="form-field illusia-char-field illusia-char-personagem-field" <?php echo $is_personagem ? '' : 'style="display:none"'; ?>>
    <th scope="row"><label for="illusia_char_personality">Personalidade</label></th>
    <td>
      <textarea name="illusia_char_personality" id="illusia_char_personality"
        rows="4" class="large-text"><?php echo esc_textarea( $meta['personality'] ); ?></textarea>
      <p class="description">Traços marcantes de personalidade.</p>
    </td>
  </tr>

  <tr class="form-field illusia-char-field illusia-char-personagem-field" <?php echo $is_personagem ? '' : 'style="display:none"'; ?>>
    <th scope="row"><label for="illusia_char_notes">Notas Extras</label></th>
    <td>
      <textarea name="illusia_char_notes" id="illusia_char_notes"
        rows="3" class="large-text"><?php echo esc_textarea( $meta['notes'] ); ?></textarea>
      <p class="description">Informações adicionais livres.</p>
    </td>
  </tr>

  <!-- Relacionamentos (repeater) -->
  <tr class="form-field illusia-char-field illusia-char-personagem-field" <?php echo $is_personagem ? '' : 'style="display:none"'; ?>>
    <th scope="row">Relacionamentos</th>
    <td>
      <div class="illusia-char-relationships" id="illusia-char-relationships">
        <?php
        $rel_types = illusia_get_relation_types();

        if ( ! empty( $meta['relationships'] ) ) :
          foreach ( $meta['relationships'] as $i => $rel ) :
            $rel_term = get_term( $rel['term_id'], 'fcn_character' );
            $rel_name = ( $rel_term && ! is_wp_error( $rel_term ) ) ? $rel_term->name : '(termo removido)';
            ?>
            <div class="illusia-char-rel-row" data-index="<?php echo esc_attr( $i ); ?>" draggable="true">
              <span class="illusia-char-rel-handle" title="Arrastar para reordenar">⠿</span>
              <select name="illusia_char_rel[<?php echo esc_attr( $i ); ?>][rel]" class="illusia-char-rel-type">
                <option value="">— Tipo —</option>
                <?php foreach ( $rel_types as $rslug => $rlabel ) : ?>
                  <option value="<?php echo esc_attr( $rslug ); ?>" <?php selected( $rel['rel'], $rslug ); ?>>
                    <?php echo esc_html( $rlabel ); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <input type="hidden" name="illusia_char_rel[<?php echo esc_attr( $i ); ?>][term_id]"
                value="<?php echo esc_attr( $rel['term_id'] ); ?>" class="illusia-char-rel-term-id" />
              <input type="text" class="illusia-char-rel-search" value="<?php echo esc_attr( $rel_name ); ?>"
                placeholder="Buscar personagem..." autocomplete="off" />
              <div class="illusia-char-rel-results" hidden></div>
              <button type="button" class="illusia-char-rel-remove button-link-delete">&times;</button>
            </div>
          <?php endforeach;
        endif;
        ?>
      </div>
      <button type="button" class="button illusia-char-rel-add" id="illusia-char-rel-add">
        + Adicionar Relacionamento
      </button>
      <p class="description">Vínculos com outros personagens. Busque pelo nome para vincular.</p>

      <?php if ( current_user_can( 'manage_options' ) ) : ?>
        <details class="illusia-char-rel-types-admin" style="margin-top: 12px;">
          <summary>Gerenciar tipos de relacionamento</summary>
          <textarea name="illusia_char_custom_rel_types" rows="5" class="large-text"
            placeholder="slug|Rótulo (um por linha)"><?php
            $custom = get_option( ILLUSIA_CHAR_RELATION_OPTION, array() );
            if ( is_array( $custom ) && ! empty( $custom ) ) {
              foreach ( $custom as $cslug => $clabel ) {
                echo esc_textarea( $cslug . '|' . $clabel ) . "\n";
              }
            }
          ?></textarea>
          <p class="description">Formato: <code>slug|Rótulo Visível</code>, um por linha. Administradores apenas.</p>
        </details>
      <?php endif; ?>
    </td>
  </tr>

  <!-- Template row for JS cloning -->
  <template id="illusia-char-rel-template">
    <div class="illusia-char-rel-row" data-index="__INDEX__" draggable="true">
      <span class="illusia-char-rel-handle" title="Arrastar para reordenar">⠿</span>
      <select name="illusia_char_rel[__INDEX__][rel]" class="illusia-char-rel-type">
        <option value="">— Tipo —</option>
        <?php foreach ( illusia_get_relation_types() as $rslug => $rlabel ) : ?>
          <option value="<?php echo esc_attr( $rslug ); ?>"><?php echo esc_html( $rlabel ); ?></option>
        <?php endforeach; ?>
      </select>
      <input type="hidden" name="illusia_char_rel[__INDEX__][term_id]" value="" class="illusia-char-rel-term-id" />
      <input type="text" class="illusia-char-rel-search" value="" placeholder="Buscar personagem..." autocomplete="off" />
      <div class="illusia-char-rel-results" hidden></div>
      <button type="button" class="illusia-char-rel-remove button-link-delete">&times;</button>
    </div>
  </template>

  <!-- Já Pertenceu a (former organizations — personagem only) -->
  <tr class="form-field illusia-char-field illusia-char-personagem-field" <?php echo $is_personagem ? '' : 'style="display:none"'; ?>>
    <th scope="row">Já Pertenceu a</th>
    <td>
      <div class="illusia-char-former-orgs" id="illusia-char-former-orgs">
        <div class="illusia-char-former-orgs-items" id="illusia-char-former-orgs-items">
          <?php foreach ( $meta['former_orgs'] as $org_id ) :
            $org_term = get_term( $org_id, 'fcn_character' );
            if ( ! $org_term || is_wp_error( $org_term ) ) continue;
            ?>
            <span class="illusia-char-former-org-tag" data-id="<?php echo esc_attr( $org_id ); ?>">
              <?php echo esc_html( $org_term->name ); ?>
              <button type="button" class="illusia-char-former-org-remove">&times;</button>
              <input type="hidden" name="illusia_char_former_orgs[]" value="<?php echo esc_attr( $org_id ); ?>" />
            </span>
          <?php endforeach; ?>
        </div>
        <div class="illusia-char-former-orgs-search-wrap">
          <input type="text" id="illusia-char-former-orgs-search" placeholder="Buscar organização..." autocomplete="off" class="regular-text" />
          <div class="illusia-char-former-orgs-results" id="illusia-char-former-orgs-results" hidden></div>
        </div>
      </div>
      <p class="description">Organizações das quais o personagem já fez parte.</p>
    </td>
  </tr>

  <?php
}
add_action( 'fcn_character_edit_form_fields', 'illusia_char_edit_form_fields' );

// =============================================================================
// SAVE HANDLER
// =============================================================================

/**
 * Save character term meta on create/edit.
 *
 * @since 1.12.0
 *
 * @param int $term_id Term ID.
 */
function illusia_char_save_meta( int $term_id ): void {
  // Verify nonce
  if ( ! isset( $_POST['illusia_char_nonce'] )
    || ! wp_verify_nonce( $_POST['illusia_char_nonce'], 'illusia_char_save' ) ) {
    return;
  }

  // Check capability
  if ( ! current_user_can( 'manage_fcn_characters' ) ) {
    return;
  }

  // Type
  $type = sanitize_key( $_POST['illusia_char_type'] ?? '' );
  if ( array_key_exists( $type, ILLUSIA_CHAR_TYPES ) ) {
    update_term_meta( $term_id, 'illusia_char_type', $type );
  } else {
    delete_term_meta( $term_id, 'illusia_char_type' );
  }

  // Image URL
  $image = esc_url_raw( $_POST['illusia_char_image'] ?? '' );
  if ( ! empty( $image ) ) {
    update_term_meta( $term_id, 'illusia_char_image', $image );
  } else {
    delete_term_meta( $term_id, 'illusia_char_image' );
  }

  // Personagem-only fields — clean up orphaned meta when type changes away
  $personagem_keys = array(
    'illusia_char_full_name', 'illusia_char_titles',
    'illusia_char_appearance', 'illusia_char_personality',
    'illusia_char_notes', 'illusia_char_relationships',
    'illusia_char_gallery', 'illusia_char_former_orgs',
    'illusia_char_template',
  );

  if ( $type === 'personagem' ) {
    $text_fields = array( 'illusia_char_full_name', 'illusia_char_titles' );
    foreach ( $text_fields as $key ) {
      $val = sanitize_text_field( $_POST[ $key ] ?? '' );
      if ( ! empty( $val ) ) {
        update_term_meta( $term_id, $key, $val );
      } else {
        delete_term_meta( $term_id, $key );
      }
    }

    $textarea_fields = array( 'illusia_char_appearance', 'illusia_char_personality', 'illusia_char_notes' );
    foreach ( $textarea_fields as $key ) {
      $val = wp_kses_post( $_POST[ $key ] ?? '' );
      if ( ! empty( $val ) ) {
        update_term_meta( $term_id, $key, $val );
      } else {
        delete_term_meta( $term_id, $key );
      }
    }

    // Relationships
    $rels_raw = $_POST['illusia_char_rel'] ?? array();
    $rels_clean = array();

    if ( is_array( $rels_raw ) ) {
      foreach ( $rels_raw as $entry ) {
        $rtid = absint( $entry['term_id'] ?? 0 );
        $rrel = sanitize_key( $entry['rel'] ?? '' );

        if ( $rtid > 0 && ! empty( $rrel ) ) {
          $rels_clean[] = array(
            'term_id' => $rtid,
            'rel'     => $rrel,
          );
        }
      }
    }

    if ( ! empty( $rels_clean ) ) {
      update_term_meta( $term_id, 'illusia_char_relationships', wp_json_encode( $rels_clean ) );
    } else {
      delete_term_meta( $term_id, 'illusia_char_relationships' );
    }

    // Gallery
    $gallery_raw = $_POST['illusia_char_gallery'] ?? array();
    if ( is_array( $gallery_raw ) ) {
      $gallery_clean = array_values( array_filter( array_map( 'esc_url_raw', $gallery_raw ) ) );
      if ( ! empty( $gallery_clean ) ) {
        update_term_meta( $term_id, 'illusia_char_gallery', wp_json_encode( $gallery_clean ) );
      } else {
        delete_term_meta( $term_id, 'illusia_char_gallery' );
      }
    }

    // Former organizations
    $former_raw = $_POST['illusia_char_former_orgs'] ?? array();
    if ( is_array( $former_raw ) ) {
      $former_clean = array_values( array_filter( array_map( 'absint', $former_raw ) ) );
      if ( ! empty( $former_clean ) ) {
        update_term_meta( $term_id, 'illusia_char_former_orgs', wp_json_encode( $former_clean ) );
      } else {
        delete_term_meta( $term_id, 'illusia_char_former_orgs' );
      }
    }

    // Template
    $template = sanitize_key( $_POST['illusia_char_template'] ?? 'padrao' );
    update_term_meta( $term_id, 'illusia_char_template', $template );
  } else {
    // Clean up personagem-only meta when type changes away
    foreach ( $personagem_keys as $k ) {
      delete_term_meta( $term_id, $k );
    }
  }

  // Custom relationship types (admin only)
  if ( current_user_can( 'manage_options' ) && isset( $_POST['illusia_char_custom_rel_types'] ) ) {
    $lines = explode( "\n", sanitize_textarea_field( $_POST['illusia_char_custom_rel_types'] ) );
    $custom_types = array();

    foreach ( $lines as $line ) {
      $line = trim( $line );
      if ( empty( $line ) || strpos( $line, '|' ) === false ) {
        continue;
      }

      list( $cslug, $clabel ) = explode( '|', $line, 2 );
      $cslug  = sanitize_key( trim( $cslug ) );
      $clabel = sanitize_text_field( trim( $clabel ) );

      if ( ! empty( $cslug ) && ! empty( $clabel ) ) {
        $custom_types[ $cslug ] = $clabel;
      }
    }

    update_option( ILLUSIA_CHAR_RELATION_OPTION, $custom_types );
  }

}
add_action( 'created_fcn_character', 'illusia_char_save_meta' );
add_action( 'edited_fcn_character', 'illusia_char_save_meta' );

// =============================================================================
// ADMIN LIST COLUMNS
// =============================================================================

/**
 * Add custom columns to the fcn_character term list.
 *
 * @since 1.12.0
 *
 * @param array $columns Existing columns.
 * @return array Modified columns.
 */
function illusia_char_manage_columns( array $columns ): array {
  $new = array();

  foreach ( $columns as $key => $label ) {
    $new[ $key ] = $label;

    // Insert after 'name' column
    if ( $key === 'name' ) {
      $new['illusia_type']    = 'Tipo';
      $new['illusia_creator'] = 'Criador';
    }
  }

  return $new;
}
add_filter( 'manage_edit-fcn_character_columns', 'illusia_char_manage_columns' );

/**
 * Render custom column content for fcn_character terms.
 *
 * @since 1.12.0
 *
 * @param string $content    Column content.
 * @param string $column     Column name.
 * @param int    $term_id    Term ID.
 * @return string Modified content.
 */
function illusia_char_column_content( string $content, string $column, int $term_id ): string {
  if ( $column === 'illusia_type' ) {
    $type = illusia_get_char_type( $term_id );
    $label = illusia_get_char_type_label( $type );

    return ! empty( $label )
      ? '<span class="illusia-char-type-badge illusia-char-type-badge--' . esc_attr( $type ) . '">' . esc_html( $label ) . '</span>'
      : '<span class="illusia-char-type-badge illusia-char-type-badge--none">—</span>';
  }

  if ( $column === 'illusia_creator' ) {
    $creator_id = absint( get_term_meta( $term_id, 'illusia_char_creator', true ) );

    if ( $creator_id > 0 ) {
      $user = get_userdata( $creator_id );
      return $user ? esc_html( $user->display_name ) : '(usuário removido)';
    }

    return '<span style="color:#999">—</span>';
  }

  return $content;
}
add_filter( 'manage_fcn_character_custom_column', 'illusia_char_column_content', 10, 3 );

// =============================================================================
// AJAX: SEARCH CHARACTERS
// =============================================================================

/**
 * AJAX handler for character term search (used by relationship repeater).
 *
 * @since 1.12.0
 */
function illusia_ajax_search_characters(): void {
  check_ajax_referer( 'illusia_search_characters', 'nonce' );

  if ( ! current_user_can( 'assign_fcn_characters' ) ) {
    wp_send_json_error( 'Sem permissão.' );
  }

  $query = sanitize_text_field( $_POST['q'] ?? '' );

  if ( strlen( $query ) < 2 ) {
    wp_send_json_success( array() );
  }

  $args = array(
    'taxonomy'   => 'fcn_character',
    'search'     => $query,
    'number'     => 20,
    'hide_empty' => false,
    'orderby'    => 'name',
    'order'      => 'ASC',
  );

  // Optional type filter (used by former orgs to filter to organizacao)
  $type_filter = sanitize_key( $_POST['type'] ?? '' );
  if ( ! empty( $type_filter ) && array_key_exists( $type_filter, ILLUSIA_CHAR_TYPES ) ) {
    $args['meta_query'] = array(
      array(
        'key'   => 'illusia_char_type',
        'value' => $type_filter,
      ),
    );
  }

  $terms = get_terms( $args );

  if ( is_wp_error( $terms ) ) {
    wp_send_json_success( array() );
  }

  $results = array();

  foreach ( $terms as $t ) {
    $type = illusia_get_char_type( $t->term_id );
    $results[] = array(
      'id'    => $t->term_id,
      'name'  => $t->name,
      'type'  => illusia_get_char_type_label( $type ) ?: '—',
    );
  }

  wp_send_json_success( $results );
}
add_action( 'wp_ajax_illusia_search_characters', 'illusia_ajax_search_characters' );
