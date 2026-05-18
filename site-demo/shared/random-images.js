(function () {
  function pickRandom(pool) {
    return pool[Math.floor(Math.random() * pool.length)];
  }

  function buildPool(basePath) {
    var cleanBase = basePath.endsWith('/') ? basePath : basePath + '/';
    return [
      cleanBase + 'capa.png',
      cleanBase + 'section_default.png',
      cleanBase + 'section_tabs.png',
      cleanBase + 'section_accordion.png',
      cleanBase + 'logo.svg',
      cleanBase + 'logo-footer.svg'
    ];
  }

  function applyRandomFallback() {
    var basePath = document.body.getAttribute('data-image-base') || 'assets/images/';
    var pool = buildPool(basePath);
    var images = Array.prototype.slice.call(document.querySelectorAll('img'));

    images.forEach(function (img) {
      img.addEventListener('error', function () {
        img.src = pickRandom(pool);
      });

      if (img.hasAttribute('data-random') || !img.getAttribute('src')) {
        img.src = pickRandom(pool);
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', applyRandomFallback);
  } else {
    applyRandomFallback();
  }
})();
