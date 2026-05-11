/**
 * CEDOC Accordion/Dropdown Initialization
 * Ensures the first category opens by default and keeps only one dropdown open.
 */
document.addEventListener('DOMContentLoaded', function() {
  // If legacy accordion exists, open first
  const firstBtn = document.querySelector('.cedoc-categories-accordion .cedoc-category-btn');
  const firstCollapse = document.querySelector('.cedoc-categories-accordion .collapse');

  if (firstBtn && firstCollapse) {
    firstBtn.classList.remove('collapsed');
    firstCollapse.classList.add('show');
    firstBtn.setAttribute('aria-expanded', 'true');
  }

  // Details-based dropdown: ensure only one is open at a time
  const detailsList = Array.from(document.querySelectorAll('.cedoc-category-dropdown'));
  if (detailsList.length) {
    // Open the first by default if none open
    if (!detailsList.some(d => d.hasAttribute('open'))) {
      detailsList[0].setAttribute('open', '');
    }

    detailsList.forEach(detail => {
      const summary = detail.querySelector('summary');
      if (!summary) return;

      summary.addEventListener('click', function(e) {
        // If this detail was already open, let it close normally
        const wasOpen = detail.hasAttribute('open');
        // Close others
        detailsList.forEach(d => { if (d !== detail && d.hasAttribute('open')) d.removeAttribute('open'); });

        // Toggle current (allow default toggling of details to proceed)
        if (!wasOpen) {
          detail.setAttribute('open', '');
        } else {
          detail.removeAttribute('open');
        }

        // small visual feedback
        summary.style.transform = 'scale(0.995)';
        setTimeout(() => { summary.style.transform = 'scale(1)'; }, 120);
        e.preventDefault();
      });
    });
  }

  // Keep existing tiny feedback for old accordion buttons
  const categoryBtns = document.querySelectorAll('.cedoc-category-btn');
  categoryBtns.forEach(btn => {
    btn.addEventListener('click', function(e) {
      this.style.transform = 'scale(0.99)';
      setTimeout(() => { this.style.transform = 'scale(1)'; }, 100);
    });
  });
});
