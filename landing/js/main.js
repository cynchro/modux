// ── Tab switching ─────────────────────────────────────────────────────────
document.querySelectorAll('[data-tabs]').forEach(container => {
  const btns   = container.querySelectorAll('[data-tab]');
  const panels = container.querySelectorAll('[data-panel]');

  btns.forEach(btn => {
    btn.addEventListener('click', () => {
      const target = btn.dataset.tab;

      btns.forEach(b => b.classList.toggle('active', b.dataset.tab === target));
      panels.forEach(p => p.classList.toggle('active', p.dataset.panel === target));
    });
  });
});

// ── Hero code tabs ────────────────────────────────────────────────────────
document.querySelectorAll('.hero-code-tab').forEach(tab => {
  tab.addEventListener('click', () => {
    const group  = tab.closest('.hero-code');
    const target = tab.dataset.tab;

    group.querySelectorAll('.hero-code-tab').forEach(t =>
      t.classList.toggle('active', t.dataset.tab === target));
    group.querySelectorAll('.hero-code-panel').forEach(p =>
      p.classList.toggle('active', p.dataset.panel === target));
  });
});

// ── Nav active link on scroll ─────────────────────────────────────────────
const sections = document.querySelectorAll('section[id]');
const navLinks  = document.querySelectorAll('.nav-links a[href^="#"]');

const observer = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      navLinks.forEach(a => {
        a.style.color = a.getAttribute('href') === `#${entry.target.id}`
          ? 'var(--text)'
          : '';
      });
    }
  });
}, { rootMargin: '-60px 0px -60% 0px' });

sections.forEach(s => observer.observe(s));

// ── Copy to clipboard ─────────────────────────────────────────────────────
document.querySelectorAll('.copy-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const code = btn.closest('.code-block').querySelector('code').textContent;
    navigator.clipboard.writeText(code).then(() => {
      const orig = btn.textContent;
      btn.textContent = 'Copied!';
      btn.style.color = 'var(--green)';
      setTimeout(() => { btn.textContent = orig; btn.style.color = ''; }, 1500);
    });
  });
});
