<?php
/**
 * Partial: Character Sheet — Ficha de Personagem
 *
 * Renderiza a ficha completa de um personagem: breadcrumb da
 * ancestralidade, carrossel de imagens, bio, aparência/personalidade
 * (lado a lado), relacionamentos/orgs anteriores, filhos e histórias.
 *
 * Esperado no escopo: $term (WP_Term), $meta (array from illusia_get_char_meta),
 * $taxonomy_color (string).
 *
 * @package Illusia Theme
 * @since 1.12.0
 * @modified 1.12.1 — gallery, former orgs, two-column layout
 * @modified 1.12.2 — carousel, lightbox, children tree, template class,
 *                     type-colored breadcrumb badges
 * @modified 1.12.3 — TCG flip card (front/back with 3D animation)
 * @modified 1.12.5 — pagination in stories section (8 per page)
 */

// No direct access!
defined( 'ABSPATH' ) OR exit;

// Ancestral breadcrumb
$ancestors = illusia_get_char_ancestors_with_types( $term->term_id );

// Relationships hydrated
$relationships = array();
if ( ! empty( $meta['relationships'] ) ) {
  $rel_types = illusia_get_relation_types();

  foreach ( $meta['relationships'] as $rel ) {
    $rel_term = get_term( $rel['term_id'], 'fcn_character' );
    if ( ! $rel_term || is_wp_error( $rel_term ) ) {
      continue;
    }

    $rel_link = get_term_link( $rel_term );

    $relationships[] = array(
      'term'      => $rel_term,
      'rel_slug'  => $rel['rel'],
      'rel_label' => $rel_types[ $rel['rel'] ] ?? $rel['rel'],
      'link'      => is_wp_error( $rel_link ) ? '#' : $rel_link,
    );
  }
}

// Former organizations hydrated
$former_orgs = array();
if ( ! empty( $meta['former_orgs'] ) ) {
  foreach ( $meta['former_orgs'] as $org_id ) {
    $org_term = get_term( $org_id, 'fcn_character' );
    if ( ! $org_term || is_wp_error( $org_term ) ) {
      continue;
    }

    $org_link = get_term_link( $org_term );

    $former_orgs[] = array(
      'term' => $org_term,
      'link' => is_wp_error( $org_link ) ? '#' : $org_link,
    );
  }
}

// Build all images array (main + gallery) for carousel
$all_images = array();
if ( ! empty( $meta['image'] ) ) {
  $all_images[] = $meta['image'];
}
if ( ! empty( $meta['gallery'] ) ) {
  foreach ( $meta['gallery'] as $url ) {
    if ( $url !== $meta['image'] ) {
      $all_images[] = $url;
    }
  }
}

// Children tree for this personagem
$children_tree = illusia_get_char_tree( $term->term_id );

// Stories/chapters tagged with this character
$char_page = max( 1, absint( $_GET['char_page'] ?? 1 ) );

$stories_query = new WP_Query( array(
  'post_type'              => array( 'fcn_story', 'fcn_chapter', 'fcn_collection', 'fcn_recommendation' ),
  'tax_query'              => array(
    array(
      'taxonomy' => 'fcn_character',
      'terms'    => $term->term_id,
    ),
  ),
  'posts_per_page'         => 8,
  'paged'                  => $char_page,
  'update_post_term_cache' => false,
) );

$display_name = ! empty( $meta['full_name'] ) ? $meta['full_name'] : $term->name;
$template     = $meta['template'] ?? 'padrao';
?>

<!-- Breadcrumb — ancestral chain with type labels -->
<?php if ( ! empty( $ancestors ) ) : ?>
  <nav class="illusia-char-breadcrumb" aria-label="Hierarquia do personagem">
    <ol class="illusia-char-breadcrumb__list">
      <?php foreach ( $ancestors as $anc ) : ?>
        <li class="illusia-char-breadcrumb__item">
          <a href="<?php echo esc_url( get_term_link( $anc['term'] ) ); ?>"
             class="illusia-char-breadcrumb__link">
            <?php echo esc_html( $anc['term']->name ); ?>
          </a>
          <?php if ( ! empty( $anc['label'] ) ) : ?>
            <span class="illusia-char-breadcrumb__type illusia-char-breadcrumb__type--<?php echo esc_attr( $anc['type'] ); ?>">
              <?php echo esc_html( $anc['label'] ); ?>
            </span>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
      <li class="illusia-char-breadcrumb__item illusia-char-breadcrumb__item--current" aria-current="page">
        <?php echo esc_html( $term->name ); ?>
        <span class="illusia-char-breadcrumb__type illusia-char-breadcrumb__type--personagem">Personagem</span>
      </li>
    </ol>
  </nav>
<?php endif; ?>

<!-- Character Sheet Body -->
<div class="illusia-char-sheet illusia-char-sheet--<?php echo esc_attr( $template ); ?>">
  <div class="illusia-char-sheet__card">
    <div class="illusia-char-sheet__front">

  <!-- Carousel / Portrait + Identity -->
  <div class="illusia-char-sheet__identity">
    <?php if ( count( $all_images ) > 1 ) : ?>
      <div class="illusia-char-carousel" data-autoplay="5000" aria-label="Galeria do personagem">
        <div class="illusia-char-carousel__track">
          <?php foreach ( $all_images as $img_url ) : ?>
            <div class="illusia-char-carousel__slide">
              <img src="<?php echo esc_url( $img_url ); ?>"
                   alt="<?php echo esc_attr( $display_name ); ?>"
                   loading="lazy"
                   data-full="<?php echo esc_url( $img_url ); ?>" />
            </div>
          <?php endforeach; ?>
        </div>
        <button class="illusia-char-carousel__btn illusia-char-carousel__btn--prev" aria-label="Anterior">&#8249;</button>
        <button class="illusia-char-carousel__btn illusia-char-carousel__btn--next" aria-label="Próximo">&#8250;</button>
        <div class="illusia-char-carousel__dots">
          <?php foreach ( $all_images as $i => $img_url ) : ?>
            <button class="illusia-char-carousel__dot<?php echo $i === 0 ? ' illusia-char-carousel__dot--active' : ''; ?>"
                    aria-label="Imagem <?php echo esc_attr( $i + 1 ); ?>"
                    data-index="<?php echo esc_attr( $i ); ?>"></button>
          <?php endforeach; ?>
        </div>
      </div>
    <?php elseif ( ! empty( $all_images ) ) : ?>
      <figure class="illusia-char-sheet__portrait">
        <img src="<?php echo esc_url( $all_images[0] ); ?>"
             alt="<?php echo esc_attr( $display_name ); ?>"
             loading="lazy"
             data-full="<?php echo esc_url( $all_images[0] ); ?>"
             class="illusia-char-lightbox-trigger" />
      </figure>
    <?php endif; ?>

    <div class="illusia-char-sheet__info">
      <?php if ( ! empty( $meta['full_name'] ) && $meta['full_name'] !== $term->name ) : ?>
        <p class="illusia-char-sheet__full-name"><?php echo esc_html( $meta['full_name'] ); ?></p>
      <?php endif; ?>

      <?php if ( ! empty( $meta['titles'] ) ) : ?>
        <p class="illusia-char-sheet__titles"><?php echo esc_html( $meta['titles'] ); ?></p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Bio (term description) -->
  <?php if ( ! empty( $term->description ) ) : ?>
    <section class="illusia-char-sheet__section">
      <h2 class="illusia-char-sheet__heading">Biografia</h2>
      <div class="illusia-char-sheet__text"><?php echo wp_kses_post( $term->description ); ?></div>
    </section>
  <?php endif; ?>

    </div><!-- /.illusia-char-sheet__front -->
    <div class="illusia-char-sheet__back">

  <!-- Appearance + Personality (two columns on desktop) -->
  <?php if ( ! empty( $meta['appearance'] ) || ! empty( $meta['personality'] ) ) : ?>
    <div class="illusia-char-sheet__columns">
      <?php if ( ! empty( $meta['appearance'] ) ) : ?>
        <section class="illusia-char-sheet__section">
          <h2 class="illusia-char-sheet__heading">Aparência</h2>
          <div class="illusia-char-sheet__text"><?php echo wp_kses_post( $meta['appearance'] ); ?></div>
        </section>
      <?php endif; ?>

      <?php if ( ! empty( $meta['personality'] ) ) : ?>
        <section class="illusia-char-sheet__section">
          <h2 class="illusia-char-sheet__heading">Personalidade</h2>
          <div class="illusia-char-sheet__text"><?php echo wp_kses_post( $meta['personality'] ); ?></div>
        </section>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- Relationships + Former Orgs (two columns if both exist) -->
  <?php if ( ! empty( $relationships ) || ! empty( $former_orgs ) ) : ?>
    <div class="illusia-char-sheet__columns">
      <?php if ( ! empty( $relationships ) ) : ?>
        <section class="illusia-char-sheet__section">
          <h2 class="illusia-char-sheet__heading">Relacionamentos</h2>
          <ul class="illusia-char-sheet__relationships">
            <?php foreach ( $relationships as $rel ) : ?>
              <li class="illusia-char-sheet__rel-item">
                <span class="illusia-char-sheet__rel-type"><?php echo esc_html( $rel['rel_label'] ); ?></span>
                <a href="<?php echo esc_url( $rel['link'] ); ?>" class="illusia-char-sheet__rel-name">
                  <?php echo esc_html( $rel['term']->name ); ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </section>
      <?php endif; ?>

      <?php if ( ! empty( $former_orgs ) ) : ?>
        <section class="illusia-char-sheet__section">
          <h2 class="illusia-char-sheet__heading">Já Pertenceu a</h2>
          <ul class="illusia-char-sheet__former-orgs">
            <?php foreach ( $former_orgs as $org ) : ?>
              <li class="illusia-char-sheet__former-org-item">
                <a href="<?php echo esc_url( $org['link'] ); ?>" class="illusia-char-sheet__former-org-link">
                  <?php echo esc_html( $org['term']->name ); ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </section>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- Notes -->
  <?php if ( ! empty( $meta['notes'] ) ) : ?>
    <section class="illusia-char-sheet__section">
      <h2 class="illusia-char-sheet__heading">Notas</h2>
      <div class="illusia-char-sheet__text"><?php echo wp_kses_post( $meta['notes'] ); ?></div>
    </section>
  <?php endif; ?>

  <!-- Children tree (personagens that are children of this term) -->
  <?php if ( ! empty( $children_tree ) ) : ?>
    <section class="illusia-char-sheet__children">
      <h2 class="illusia-char-sheet__heading">Hierarquia</h2>
      <?php illusia_render_char_tree( $children_tree, 0 ); ?>
    </section>
  <?php endif; ?>

    </div><!-- /.illusia-char-sheet__back -->
  </div><!-- /.illusia-char-sheet__card -->
  <button class="illusia-char-sheet__flip-btn" aria-label="Virar carta">&#x21BB; Virar Carta</button>

</div>

<!-- Stories tagged with this character -->
<?php if ( $stories_query->have_posts() ) : ?>
  <section class="illusia-char-sheet__stories" id="aparicoes">
    <h2 class="illusia-char-sheet__heading">Aparições</h2>
    <div class="illusia-char-sheet__stories-grid">
      <?php
      while ( $stories_query->have_posts() ) {
        $stories_query->the_post();
        $card_type = get_post_type();

        $card_map = array(
          'fcn_story'          => 'story',
          'fcn_chapter'        => 'chapter',
          'fcn_collection'     => 'collection',
          'fcn_recommendation' => 'recommendation',
        );

        $card = $card_map[ $card_type ] ?? 'story';
        fictioneer_echo_card( array( 'type' => $card ) );
      }
      wp_reset_postdata();
      ?>
    </div>

    <?php if ( $stories_query->max_num_pages > 1 ) : ?>
      <nav class="illusia-list-page__pagination" aria-label="Paginação das aparições">
        <?php
          echo wp_kses_post( paginate_links( array(
            'base'         => add_query_arg( 'char_page', '%#%' ),
            'format'       => '',
            'current'      => $char_page,
            'total'        => $stories_query->max_num_pages,
            'prev_text'    => '&laquo; Anterior',
            'next_text'    => 'Próximo &raquo;',
            'add_fragment' => '#aparicoes',
          ) ) );
        ?>
      </nav>
    <?php endif; ?>

  </section>
<?php endif; ?>

<!-- Lightbox container (populated by JS) -->
<div class="illusia-char-lightbox" id="illusia-char-lightbox" aria-hidden="true">
  <button class="illusia-char-lightbox__close" aria-label="Fechar">&times;</button>
  <button class="illusia-char-lightbox__nav illusia-char-lightbox__nav--prev" aria-label="Anterior">&#8249;</button>
  <img class="illusia-char-lightbox__img" src="" alt="" />
  <button class="illusia-char-lightbox__nav illusia-char-lightbox__nav--next" aria-label="Próximo">&#8250;</button>
  <span class="illusia-char-lightbox__counter"></span>
</div>
