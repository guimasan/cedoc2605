(() => {
  const pageUrl = encodeURIComponent(window.location.href);
  const pageTitle = encodeURIComponent(document.title);

  const shareTargets = {
    whatsapp: `https://wa.me/?text=${pageTitle}%20${pageUrl}`,
    facebook: `https://www.facebook.com/sharer/sharer.php?u=${pageUrl}`,
    linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${pageUrl}`,
    x: `https://twitter.com/intent/tweet?text=${pageTitle}&url=${pageUrl}`,
  };

  document.querySelectorAll('[data-share-bar]').forEach((bar) => {
    bar.querySelectorAll('[data-share-network]').forEach((link) => {
      const network = link.getAttribute('data-share-network');
      const target = shareTargets[network];

      if (target) {
        link.href = target;
      }
    });
  });
})();