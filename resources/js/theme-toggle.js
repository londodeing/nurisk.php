window.toggleNuriskTheme = function() {
  const html = document.documentElement;
  const current = html.getAttribute('data-theme');
  const next = current === 'dark' ? 'light' : 'dark';
  html.setAttribute('data-theme', next);
  localStorage.setItem('nurisk-theme', next);
};

document.addEventListener('DOMContentLoaded', () => {
  const saved = localStorage.getItem('nurisk-theme');
  if (saved) {
    document.documentElement.setAttribute('data-theme', saved);
  }
});
