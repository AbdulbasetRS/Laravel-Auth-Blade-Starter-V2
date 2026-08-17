/**
 * Documentation page — in-page nav scrolling.
 *
 * Why this exists: the actual scrollable element in the Admin layout is
 * .admin-main (not <html>/<body> — .admin-body and .admin-content-col are
 * overflow:hidden). Native <a href="#id"> anchor jumps don't reliably
 * resolve against a nested overflow:auto ancestor across browsers, which
 * causes the page to snap back to the top instead of scrolling to the
 * clicked section. This replaces the native jump with an explicit,
 * contained smooth-scroll + active-link highlighting via IntersectionObserver.
 */
document.addEventListener('DOMContentLoaded', function () {
  var nav = document.querySelector('.docs-nav');
  var scrollContainer = document.querySelector('.admin-main');
  if (!nav || !scrollContainer) return;

  var links = Array.prototype.slice.call(nav.querySelectorAll('a[href^="#"]'));
  var sections = links
    .map(function (link) { return document.getElementById(link.getAttribute('href').slice(1)); })
    .filter(Boolean);

  function setActive(id) {
    links.forEach(function (link) {
      link.classList.toggle('active', link.getAttribute('href') === '#' + id);
    });
  }

  links.forEach(function (link) {
    link.addEventListener('click', function (e) {
      var id = link.getAttribute('href').slice(1);
      var target = document.getElementById(id);
      if (!target) return;

      e.preventDefault();

      var containerTop = scrollContainer.getBoundingClientRect().top;
      var targetTop = target.getBoundingClientRect().top;
      var offset = targetTop - containerTop + scrollContainer.scrollTop;

      scrollContainer.scrollTo({ top: offset, behavior: 'smooth' });
      history.replaceState(null, '', '#' + id);
      setActive(id);
    });
  });

  if ('IntersectionObserver' in window && sections.length) {
    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) setActive(entry.target.id);
        });
      },
      { root: scrollContainer, rootMargin: '-10% 0px -70% 0px', threshold: 0 }
    );
    sections.forEach(function (section) { observer.observe(section); });
  }
});