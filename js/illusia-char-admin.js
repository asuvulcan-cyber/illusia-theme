/**
 * Illusia Character Admin — Type Toggle, Drag Reorder,
 * Gallery, Former Orgs + Relationship Repeater
 *
 * @since 1.12.0
 * @modified 1.12.1 — drag-and-drop, gallery, former orgs
 */

(() => {
  'use strict';

  // =========================================================================
  // TYPE TOGGLE — show/hide personagem fields
  // =========================================================================

  function initTypeToggle() {
    const select = document.getElementById('illusia_char_type');
    if (!select) return;

    const fields = document.querySelectorAll('.illusia-char-personagem-field');

    function toggle() {
      const show = select.value === 'personagem';
      fields.forEach(f => { f.style.display = show ? '' : 'none'; });
    }

    select.addEventListener('change', toggle);
  }

  // =========================================================================
  // IMAGE PREVIEW — live preview on URL change
  // =========================================================================

  function initImagePreview() {
    const input = document.getElementById('illusia_char_image');
    if (!input) return;

    let preview = input.closest('td')?.querySelector('.illusia-char-image-preview');

    function updatePreview() {
      const url = input.value.trim();

      if (!url) {
        if (preview) preview.hidden = true;
        return;
      }

      if (!preview) {
        preview = document.createElement('div');
        preview.className = 'illusia-char-image-preview';
        preview.innerHTML = '<img src="" alt="" />';
        input.insertAdjacentElement('afterend', preview);
      }

      preview.hidden = false;
      preview.querySelector('img').src = url;
    }

    input.addEventListener('input', updatePreview);
  }

  // =========================================================================
  // RELATIONSHIP REPEATER — add/remove rows + AJAX search
  // =========================================================================

  function initRelationshipRepeater() {
    const container = document.getElementById('illusia-char-relationships');
    const addBtn = document.getElementById('illusia-char-rel-add');
    const template = document.getElementById('illusia-char-rel-template');

    if (!container || !addBtn || !template) return;

    let index = container.querySelectorAll('.illusia-char-rel-row').length;

    // Add new row
    addBtn.addEventListener('click', () => {
      const html = template.innerHTML.replace(/__INDEX__/g, String(index));
      const wrapper = document.createElement('div');
      wrapper.innerHTML = html.trim();
      const row = wrapper.firstElementChild;
      container.appendChild(row);
      initRowBehavior(row);
      index++;
    });

    // Init existing rows
    container.querySelectorAll('.illusia-char-rel-row').forEach(initRowBehavior);

    // Init drag-and-drop
    initDragAndDrop(container);
  }

  /**
   * Wire up a single relationship row: remove button + search.
   */
  function initRowBehavior(row) {
    // Remove button
    const removeBtn = row.querySelector('.illusia-char-rel-remove');
    if (removeBtn) {
      removeBtn.addEventListener('click', () => row.remove());
    }

    // AJAX search
    const searchInput = row.querySelector('.illusia-char-rel-search');
    const termIdInput = row.querySelector('.illusia-char-rel-term-id');
    const resultsDiv = row.querySelector('.illusia-char-rel-results');

    if (!searchInput || !termIdInput || !resultsDiv) return;

    let debounceTimer = null;

    searchInput.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      const q = searchInput.value.trim();

      if (q.length < 2) {
        resultsDiv.hidden = true;
        resultsDiv.innerHTML = '';
        return;
      }

      debounceTimer = setTimeout(() => fetchCharacters(q, resultsDiv, termIdInput, searchInput), 300);
    });

    // Close results on outside click
    document.addEventListener('click', (e) => {
      if (!row.contains(e.target)) {
        resultsDiv.hidden = true;
      }
    });
  }

  /**
   * Fetch character terms via AJAX and render results dropdown.
   */
  function fetchCharacters(query, resultsDiv, termIdInput, searchInput, typeFilter) {
    const { ajaxUrl, searchNonce } = window.illusiaCharAdmin || {};
    if (!ajaxUrl) return;

    const body = new FormData();
    body.append('action', 'illusia_search_characters');
    body.append('nonce', searchNonce);
    body.append('q', query);
    if (typeFilter) body.append('type', typeFilter);

    fetch(ajaxUrl, { method: 'POST', body })
      .then(r => r.json())
      .then(res => {
        if (!res.success || !res.data.length) {
          resultsDiv.innerHTML = '<div class="illusia-char-rel-no-results">Nenhum resultado</div>';
          resultsDiv.hidden = false;
          return;
        }

        resultsDiv.innerHTML = res.data.map(t =>
          `<div class="illusia-char-rel-result" data-id="${t.id}">
            <span class="illusia-char-rel-result-name">${escHtml(t.name)}</span>
            <span class="illusia-char-rel-result-type">${escHtml(t.type)}</span>
          </div>`
        ).join('');

        resultsDiv.hidden = false;

        // Click to select
        resultsDiv.querySelectorAll('.illusia-char-rel-result').forEach(el => {
          el.addEventListener('click', () => {
            termIdInput.value = el.dataset.id;
            searchInput.value = el.querySelector('.illusia-char-rel-result-name').textContent;
            resultsDiv.hidden = true;
          });
        });
      })
      .catch(() => {
        resultsDiv.hidden = true;
      });
  }

  // =========================================================================
  // DRAG AND DROP — reorder relationship rows
  // =========================================================================

  function initDragAndDrop(container) {
    let draggedRow = null;

    container.addEventListener('dragstart', (e) => {
      const row = e.target.closest('.illusia-char-rel-row');
      if (!row) return;
      draggedRow = row;
      row.classList.add('illusia-char-rel-row--dragging');
      e.dataTransfer.effectAllowed = 'move';
    });

    container.addEventListener('dragend', (e) => {
      if (draggedRow) {
        draggedRow.classList.remove('illusia-char-rel-row--dragging');
        draggedRow = null;
      }
      container.querySelectorAll('.illusia-char-rel-row--drag-over').forEach(el => {
        el.classList.remove('illusia-char-rel-row--drag-over');
      });
      renumberRelRows(container);
    });

    container.addEventListener('dragover', (e) => {
      e.preventDefault();
      e.dataTransfer.dropEffect = 'move';

      const target = e.target.closest('.illusia-char-rel-row');
      if (!target || target === draggedRow) return;

      // Clear previous drag-over states
      container.querySelectorAll('.illusia-char-rel-row--drag-over').forEach(el => {
        el.classList.remove('illusia-char-rel-row--drag-over');
      });
      target.classList.add('illusia-char-rel-row--drag-over');
    });

    container.addEventListener('drop', (e) => {
      e.preventDefault();
      const target = e.target.closest('.illusia-char-rel-row');
      if (!target || !draggedRow || target === draggedRow) return;

      // Determine insertion position
      const rect = target.getBoundingClientRect();
      const midY = rect.top + rect.height / 2;

      if (e.clientY < midY) {
        container.insertBefore(draggedRow, target);
      } else {
        container.insertBefore(draggedRow, target.nextSibling);
      }
    });
  }

  /**
   * Renumber relationship row name attributes after reorder.
   */
  function renumberRelRows(container) {
    const rows = container.querySelectorAll('.illusia-char-rel-row');
    rows.forEach((row, i) => {
      row.dataset.index = i;
      row.querySelectorAll('[name]').forEach(el => {
        el.name = el.name.replace(/\[\d+\]/, `[${i}]`);
      });
    });
  }

  // =========================================================================
  // GALLERY — add/remove image URLs
  // =========================================================================

  function initGallery() {
    const container = document.getElementById('illusia-char-gallery-items');
    const urlInput = document.getElementById('illusia-char-gallery-url');
    const addBtn = document.getElementById('illusia-char-gallery-add-btn');

    if (!container || !urlInput || !addBtn) return;

    // Add image
    addBtn.addEventListener('click', () => {
      const url = urlInput.value.trim();
      if (!url) return;

      addGalleryItem(container, url);
      urlInput.value = '';
    });

    // Allow Enter key
    urlInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        addBtn.click();
      }
    });

    // Init remove buttons for existing items
    container.querySelectorAll('.illusia-char-gallery-remove').forEach(btn => {
      btn.addEventListener('click', () => btn.closest('.illusia-char-gallery-item').remove());
    });
  }

  function addGalleryItem(container, url) {
    const item = document.createElement('div');
    item.className = 'illusia-char-gallery-item';
    item.dataset.url = url;
    item.innerHTML = `
      <img src="${escHtml(url)}" alt="" />
      <button type="button" class="illusia-char-gallery-remove">&times;</button>
      <input type="hidden" name="illusia_char_gallery[]" value="${escHtml(url)}" />
    `;

    item.querySelector('.illusia-char-gallery-remove').addEventListener('click', () => item.remove());
    container.appendChild(item);
  }

  // =========================================================================
  // FORMER ORGS — search + tag management
  // =========================================================================

  function initFormerOrgs() {
    const container = document.getElementById('illusia-char-former-orgs-items');
    const searchInput = document.getElementById('illusia-char-former-orgs-search');
    const resultsDiv = document.getElementById('illusia-char-former-orgs-results');

    if (!container || !searchInput || !resultsDiv) return;

    let debounceTimer = null;

    // AJAX search (filtered to organizacao type)
    searchInput.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      const q = searchInput.value.trim();

      if (q.length < 2) {
        resultsDiv.hidden = true;
        resultsDiv.innerHTML = '';
        return;
      }

      debounceTimer = setTimeout(() => {
        const { ajaxUrl, searchNonce } = window.illusiaCharAdmin || {};
        if (!ajaxUrl) return;

        const body = new FormData();
        body.append('action', 'illusia_search_characters');
        body.append('nonce', searchNonce);
        body.append('q', q);
        body.append('type', 'organizacao');

        fetch(ajaxUrl, { method: 'POST', body })
          .then(r => r.json())
          .then(res => {
            if (!res.success || !res.data.length) {
              resultsDiv.innerHTML = '<div class="illusia-char-rel-no-results">Nenhum resultado</div>';
              resultsDiv.hidden = false;
              return;
            }

            resultsDiv.innerHTML = res.data.map(t =>
              `<div class="illusia-char-rel-result" data-id="${t.id}">
                <span class="illusia-char-rel-result-name">${escHtml(t.name)}</span>
                <span class="illusia-char-rel-result-type">${escHtml(t.type)}</span>
              </div>`
            ).join('');

            resultsDiv.hidden = false;

            resultsDiv.querySelectorAll('.illusia-char-rel-result').forEach(el => {
              el.addEventListener('click', () => {
                const id = el.dataset.id;
                const name = el.querySelector('.illusia-char-rel-result-name').textContent;

                // Don't add duplicates
                if (container.querySelector(`[data-id="${id}"]`)) {
                  resultsDiv.hidden = true;
                  searchInput.value = '';
                  return;
                }

                addFormerOrgTag(container, id, name);
                resultsDiv.hidden = true;
                searchInput.value = '';
              });
            });
          })
          .catch(() => {
            resultsDiv.hidden = true;
          });
      }, 300);
    });

    // Close on outside click
    document.addEventListener('click', (e) => {
      if (!e.target.closest('#illusia-char-former-orgs')) {
        resultsDiv.hidden = true;
      }
    });

    // Init remove buttons for existing tags
    container.querySelectorAll('.illusia-char-former-org-remove').forEach(btn => {
      btn.addEventListener('click', () => btn.closest('.illusia-char-former-org-tag').remove());
    });
  }

  function addFormerOrgTag(container, id, name) {
    const tag = document.createElement('span');
    tag.className = 'illusia-char-former-org-tag';
    tag.dataset.id = id;
    tag.innerHTML = `
      ${escHtml(name)}
      <button type="button" class="illusia-char-former-org-remove">&times;</button>
      <input type="hidden" name="illusia_char_former_orgs[]" value="${id}" />
    `;

    tag.querySelector('.illusia-char-former-org-remove').addEventListener('click', () => tag.remove());
    container.appendChild(tag);
  }

  // =========================================================================
  // UTILS
  // =========================================================================

  function escHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  // =========================================================================
  // INIT
  // =========================================================================

  initTypeToggle();
  initImagePreview();
  initRelationshipRepeater();
  initGallery();
  initFormerOrgs();
})();
