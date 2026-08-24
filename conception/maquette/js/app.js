/* ═══════════════════════════════════════════
   symfoLivre — Logique du prototype
   ═══════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {

  // ─── NAVBAR: activer le lien courant ───
  const currentPage = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.navbar nav a').forEach(a => {
    if (a.getAttribute('href') === currentPage) a.classList.add('active');
  });

  // ─── HOMEPAGE: render books ───
  const grid = document.getElementById('book-grid');
  if (grid) {
    renderBooks(BOOKS, grid);
  }

  // ─── SEARCH ───
  const searchInput = document.getElementById('search-input');
  const searchBtn = document.getElementById('search-btn');
  if (searchBtn) {
    searchBtn.addEventListener('click', doSearch);
    searchInput?.addEventListener('keydown', e => { if (e.key === 'Enter') doSearch(); });
  }

  // ─── FILTER CHIPS ───
  document.querySelectorAll('.filter-chip').forEach(chip => {
    chip.addEventListener('click', () => {
      document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      const cat = chip.dataset.category;
      const filtered = cat === 'Tous' ? BOOKS : BOOKS.filter(b => b.category === cat);
      renderBooks(filtered, grid);
    });
  });

  // ─── BOOK DETAIL PAGE ───
  const detailEl = document.getElementById('book-detail');
  if (detailEl) {
    renderDetail();
  }

  // ─── BASKET PAGE ───
  const basketGrid = document.getElementById('basket-grid');
  if (basketGrid) {
    const basketBooks = BOOKS.filter(b => BASKET_DEMO.includes(b.id));
    renderBooks(basketBooks, basketGrid, true);
  }

  // ─── MY BOOKS (Auteur) ───
  const myBooksGrid = document.getElementById('my-books-grid');
  if (myBooksGrid) {
    const myBooks = BOOKS.filter(b => b.author === 'Bob Dupont' || true); // démo
    renderBooks(myBooks.slice(0, 2), myBooksGrid);
  }

  // ─── ADMIN TABS ───
  initTabs();

  // ─── UPLOAD DROPZONE ───
  const dropzone = document.querySelector('.dropzone');
  if (dropzone) {
    dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.classList.add('dragover'); });
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
    dropzone.addEventListener('drop', e => {
      e.preventDefault();
      dropzone.classList.remove('dragover');
      const file = e.dataTransfer.files[0];
      if (file) {
        dropzone.querySelector('.filename').textContent = file.name;
        dropzone.classList.add('has-file');
      }
    });
  }

  // ─── ROLE SELECT (Register) ───
  document.querySelectorAll('.role-option').forEach(opt => {
    opt.addEventListener('click', () => {
      document.querySelectorAll('.role-option').forEach(o => o.classList.remove('selected'));
      opt.classList.add('selected');
    });
  });

  // ─── TOAST helper ───
  window.showToast = function (msg, type = 'success') {
    const t = document.createElement('div');
    t.className = `alert alert-${type}`;
    t.textContent = msg;
    document.body.prepend(t);
    setTimeout(() => t.remove(), 3500);
  };

});

/* ─── Helpers ─── */

function renderBooks(books, container, isBasket = false) {
  container.innerHTML = books.map(b => `
    <div class="book-card">
      <div class="cover" style="background:${b.color}">
        <span>${b.icon}</span>
        <span class="badge ${b.type === 'TEXTE' ? 'badge-texte' : 'badge-audio'}">${b.type === 'TEXTE' ? '📄 Texte' : '🎵 Audio'}</span>
      </div>
      <div class="info">
        <span class="category">${b.category}</span>
        <h3>${b.title}</h3>
        <p class="author">${b.author}</p>
        <div class="meta">
          <span>📅 ${b.year}</span>
          <span>ISBN ${b.isbn.slice(0, 10)}…</span>
        </div>
      </div>
      <div class="actions">
        <a href="book.html?id=${b.id}" class="btn btn-primary btn-sm">Détails</a>
        ${b.type === 'TEXTE'
          ? `<a href="book.html?id=${b.id}&read=1" class="btn btn-outline btn-sm">📖 Lire</a>`
          : `<button class="btn btn-outline btn-sm" onclick="showToast('Téléchargement de ${b.title}…', 'info')">⬇ Télécharger</button>`
        }
        ${isBasket
          ? `<button class="btn btn-danger btn-sm" onclick="this.closest('.book-card').remove(); showToast('Retiré du panier')">✕</button>`
          : `<button class="btn btn-accent btn-sm" onclick="showToast('${b.title} ajouté au panier')">🛒</button>`
        }
      </div>
    </div>
  `).join('');
}

function renderDetail() {
  const params = new URLSearchParams(window.location.search);
  const id = parseInt(params.get('id')) || 1;
  const book = BOOKS.find(b => b.id === id) || BOOKS[0];
  const isRead = params.get('read') === '1';

  const container = document.getElementById('book-detail');
  container.innerHTML = `
    <div class="detail-layout">
      <div class="detail-cover">
        <div class="icon">${book.icon}</div>
        <div class="book-title">${book.title}</div>
        <div class="book-author">${book.author}</div>
        <br>
        <span class="badge ${book.type === 'TEXTE' ? 'badge-texte' : 'badge-audio'}" style="display:inline-block;padding:.3rem .8rem;font-size:.8rem;">
          ${book.type === 'TEXTE' ? '📄 Livre texte' : '🎵 Livre audio'}
        </span>
      </div>
      <div class="detail-info">
        <h1>${book.title}</h1>
        <p class="subtitle">par <strong>${book.author}</strong></p>

        <div class="detail-meta">
          <div class="field"><label>Catégorie</label><span>${book.category}</span></div>
          <div class="field"><label>ISBN</label><span>${book.isbn}</span></div>
          <div class="field"><label>Date de publication</label><span>${book.year}</span></div>
          <div class="field"><label>Format</label><span>${book.fileFormat === '.txt' ? 'Texte (.txt)' : book.fileFormat === '.md' ? 'Markdown (.md)' : 'Audio (.zip)'}</span></div>
        </div>

        <div class="detail-summary">
          <h3 class="mb-2">Résumé</h3>
          <p>${book.summary}</p>
        </div>

        ${isRead && book.type === 'TEXTE' ? renderReadingView(book) : ''}

        <div class="detail-actions">
          ${book.type === 'TEXTE'
            ? `<button class="btn btn-primary btn-lg" onclick="window.location.href='book.html?id=${book.id}&read=1'">📖 Lire en ligne</button>`
            : ''
          }
          <button class="btn btn-accent btn-lg" onclick="showToast('Téléchargement de «${book.title}» démarré…')">⬇ Télécharger</button>
          <button class="btn btn-outline btn-lg" onclick="showToast('${book.title} ajouté au panier de lecture')">🛒 Ajouter au panier</button>
          <a href="index.html" class="btn btn-outline btn-lg">← Retour</a>
        </div>
      </div>
    </div>
  `;
}

function renderReadingView(book) {
  return `
    <div class="detail-summary" style="margin-top:1.5rem;">
      <h3 class="mb-2">Aperçu du contenu (démo)</h3>
      <p>${book.summary}</p>
      <p class="text-muted text-sm mt-2">— Extraits du chapitre 1 —</p>
      <p>« ${book.title} » illustre avec une force narrative remarquable les tensions qui traversent la société de son époque. L'auteur ${book.author} y déploie une réflexion profonde, ancrée dans le concret, qui continue de résonner de nos jours.</p>
      <p>Les personnages, tour à tour lumineux et tourmentés, incarnent les aspirations et les doutes d'une humanité en perpétuelle recherche de sens.</p>
    </div>
  `;
}

function doSearch() {
  const q = searchInput.value.trim().toLowerCase();
  if (!q) { renderBooks(BOOKS, grid); return; }
  const results = BOOKS.filter(b =>
    b.title.toLowerCase().includes(q) ||
    b.author.toLowerCase().includes(q) ||
    b.category.toLowerCase().includes(q) ||
    b.isbn.includes(q)
  );
  renderBooks(results, grid);
  if (results.length === 0) {
    grid.innerHTML = '<p class="text-center text-muted" style="grid-column:1/-1;padding:2rem;">Aucun livre trouvé pour « ' + q + ' »</p>';
  }
}

function initTabs() {
  document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
      tab.classList.add('active');
      document.getElementById(tab.dataset.tab)?.classList.add('active');
    });
  });
}