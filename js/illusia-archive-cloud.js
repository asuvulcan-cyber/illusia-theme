/**
 * Illusia Archive Cloud — Collapsible Tax Cloud
 *
 * Detects when the tax cloud exceeds its max-height threshold
 * and reveals a toggle button to expand/collapse. Progressive
 * enhancement: without JS, the full cloud is shown.
 *
 * @since 1.11.1
 */

(() => {
  'use strict';

  const OVERFLOWING = '_overflowing';
  const EXPANDED = '_expanded';

  /**
   * Initialise a single cloud nav element.
   *
   * @param {HTMLElement} cloud  The .illusia-archive__cloud nav.
   */
  function initCloud(cloud) {
    const items = cloud.querySelector('.illusia-archive__cloud-items');
    const toggle = cloud.querySelector('.illusia-archive__cloud-toggle');

    if (!items || !toggle) return;

    /** Apply constraint, then check if content overflows it. */
    function checkOverflow() {
      // Apply constraint so max-height kicks in
      cloud.classList.add(OVERFLOWING);

      // scrollHeight = full content; clientHeight = constrained box
      if (items.scrollHeight > items.clientHeight + 2) {
        toggle.hidden = false;
      } else {
        // Content fits — remove constraint
        cloud.classList.remove(OVERFLOWING);
      }
    }

    /** Handle toggle click. */
    toggle.addEventListener('click', () => {
      const isExpanded = cloud.classList.toggle(EXPANDED);

      if (!isExpanded) {
        cloud.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    });

    // Check after initial layout
    checkOverflow();

    // Re-check after web fonts finish loading (may change pill heights)
    if (document.fonts && document.fonts.ready) {
      document.fonts.ready.then(checkOverflow);
    }
  }

  // Init all clouds on the page
  document.querySelectorAll('.illusia-archive__cloud').forEach(initCloud);
})();
