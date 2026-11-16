/* ============================================================
   ShopZone — Main JavaScript
   ============================================================ */

// ── Dark Mode ─────────────────────────────────────────────────
const html = document.documentElement;
const themeToggle = document.getElementById('themeToggle');
const themeIcon   = document.getElementById('themeIcon');

function applyTheme(theme) {
  html.setAttribute('data-theme', theme);
  localStorage.setItem('sz_theme', theme);
  if (themeIcon) {
    themeIcon.setAttribute('data-feather', theme === 'dark' ? 'sun' : 'moon');
    if (window.feather) feather.replace();
  }
}

// Apply saved theme immediately
const savedTheme = localStorage.getItem('sz_theme') || 'light';
applyTheme(savedTheme);

if (themeToggle) {
  themeToggle.addEventListener('click', () => {
    const current = html.getAttribute('data-theme');
    applyTheme(current === 'dark' ? 'light' : 'dark');
  });
}

// ── Navbar scroll effect ───────────────────────────────────────
const nav = document.getElementById('mainNav');
if (nav) {
  window.addEventListener('scroll', () => {
    nav.classList.toggle('scrolled', window.scrollY > 20);
  });
}

// ── AJAX Cart helpers ──────────────────────────────────────────
async function cartAction(action, productId, qty = 1) {
  const res = await fetch('/shopzone/pages/cart_action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=${action}&product_id=${productId}&quantity=${qty}`
  });
  return await res.json();
}

function updateCartBadge(count) {
  const badge = document.getElementById('cartBadge');
  if (!badge) return;
  badge.textContent = count > 0 ? count : '';
  if (count > 0) badge.classList.add('pop');
  setTimeout(() => badge.classList.remove('pop'), 400);
}

function showNotification(message, type = 'success') {
  const existing = document.querySelector('.sz-notification');
  if (existing) existing.remove();

  const el = document.createElement('div');
  el.className = `sz-notification sz-notif-${type}`;
  el.innerHTML = `<span>${message}</span>`;
  Object.assign(el.style, {
    position: 'fixed', bottom: '1.5rem', right: '1.5rem',
    background: type === 'success' ? '#065F46' : '#991B1B',
    color: '#fff',
    padding: '.75rem 1.25rem',
    borderRadius: '10px',
    fontFamily: "'DM Sans', sans-serif",
    fontWeight: '600',
    fontSize: '.875rem',
    zIndex: '9999',
    boxShadow: '0 8px 24px rgba(0,0,0,.2)',
    animation: 'slideUp .3s ease',
    display: 'flex',
    alignItems: 'center',
    gap: '.5rem',
  });
  document.body.appendChild(el);
  setTimeout(() => el.remove(), 3000);
}

// Inject keyframe
const style = document.createElement('style');
style.textContent = `
  @keyframes slideUp { from { opacity:0; transform:translateY(12px) } to { opacity:1; transform:translateY(0) } }
  .badge.pop { animation: popBadge .3s ease; }
  @keyframes popBadge { 0%{transform:scale(1)} 50%{transform:scale(1.5)} 100%{transform:scale(1)} }
  .main-nav.scrolled { box-shadow: 0 4px 20px rgba(0,0,0,.3); }
`;
document.head.appendChild(style);

// ── Add to Cart buttons ────────────────────────────────────────
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('[data-add-cart]');
  if (!btn) return;
  e.preventDefault();
  const productId = btn.dataset.addCart;
  btn.disabled = true;
  const data = await cartAction('add', productId);
  btn.disabled = false;
  if (data.success) {
    updateCartBadge(data.cart_count);
    showNotification('Added to cart!', 'success');
  } else {
    showNotification(data.message || 'Login required', 'error');
    if (data.redirect) setTimeout(() => location.href = data.redirect, 1200);
  }
});

// ── Wishlist buttons ───────────────────────────────────────────
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('[data-toggle-wish]');
  if (!btn) return;
  e.preventDefault();
  const productId = btn.dataset.toggleWish;
  const res = await fetch('/shopzone/pages/wishlist_action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `product_id=${productId}`
  });
  const data = await res.json();
  if (data.success) {
    const svg = btn.querySelector('svg');
    if (svg) svg.style.fill = data.wishlisted ? 'var(--accent)' : 'none';
    showNotification(data.wishlisted ? 'Added to wishlist!' : 'Removed from wishlist');
  } else {
    showNotification(data.message || 'Login required', 'error');
    if (data.redirect) setTimeout(() => location.href = data.redirect, 1200);
  }
});

// ── Cart page quantity controls ────────────────────────────────
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('[data-cart-qty]');
  if (!btn) return;
  const { productId, dir } = btn.dataset.cartQty ? JSON.parse(btn.dataset.cartQty) : {};
  if (!productId || !dir) return;
  const numEl = document.getElementById(`qty-${productId}`);
  let qty = parseInt(numEl?.textContent || 1);
  qty = dir === 'up' ? qty + 1 : Math.max(1, qty - 1);
  const data = await cartAction('update', productId, qty);
  if (data.success) {
    if (numEl) numEl.textContent = qty;
    updateCartBadge(data.cart_count);
    // Update totals inline
    const totalEl = document.getElementById(`item-total-${productId}`);
    if (totalEl && data.item_total) totalEl.textContent = '$' + parseFloat(data.item_total).toFixed(2);
    const cartTotalEl = document.getElementById('cart-total');
    if (cartTotalEl && data.cart_total) cartTotalEl.textContent = '$' + parseFloat(data.cart_total).toFixed(2);
    const summaryTotalEl = document.getElementById('summary-total');
    if (summaryTotalEl && data.cart_total) summaryTotalEl.textContent = '$' + parseFloat(data.cart_total).toFixed(2);
  }
});

// ── Remove from cart ───────────────────────────────────────────
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('[data-remove-cart]');
  if (!btn) return;
  const productId = btn.dataset.removeCart;
  const row = document.getElementById(`cart-row-${productId}`);
  const data = await cartAction('remove', productId);
  if (data.success) {
    if (row) { row.style.opacity = '0'; setTimeout(() => row.remove(), 300); }
    updateCartBadge(data.cart_count);
    const cartTotalEl = document.getElementById('cart-total');
    if (cartTotalEl && data.cart_total !== undefined) cartTotalEl.textContent = '$' + parseFloat(data.cart_total).toFixed(2);
    if (data.cart_count === 0) setTimeout(() => location.reload(), 350);
  }
});

// ── Promo code ─────────────────────────────────────────────────
const promoForm = document.getElementById('promoForm');
if (promoForm) {
  promoForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const code = document.getElementById('promoCode').value.trim();
    const res = await fetch('/shopzone/pages/apply_promo.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `code=${encodeURIComponent(code)}`
    });
    const data = await res.json();
    const msgEl = document.getElementById('promoMsg');
    if (data.success) {
      msgEl.innerHTML = `<span style="color:#065F46">✓ ${data.message}</span>`;
      document.getElementById('discount-row').style.display = '';
      document.getElementById('discount-val').textContent = '-$' + parseFloat(data.discount).toFixed(2);
      document.getElementById('summary-total').textContent = '$' + parseFloat(data.new_total).toFixed(2);
    } else {
      msgEl.innerHTML = `<span style="color:#991B1B">✗ ${data.message}</span>`;
    }
  });
}

// ── Product gallery thumbnails ─────────────────────────────────
document.querySelectorAll('.thumb-img').forEach(thumb => {
  thumb.addEventListener('click', () => {
    const main = document.getElementById('mainProductImg');
    if (main) {
      main.src = thumb.src;
      document.querySelectorAll('.thumb-img').forEach(t => t.classList.remove('active'));
      thumb.classList.add('active');
    }
  });
});

// ── Auto-dismiss flash ─────────────────────────────────────────
const flash = document.getElementById('flash-msg');
if (flash) setTimeout(() => flash.remove(), 5000);

// ── Image preview on file input ────────────────────────────────
document.querySelectorAll('input[type="file"]').forEach(inp => {
  inp.addEventListener('change', function() {
    const preview = document.getElementById(this.dataset.preview);
    if (!preview) return;
    const file = this.files[0];
    if (file && file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
      reader.readAsDataURL(file);
    }
  });
});

// ── Chart.js accent color ──────────────────────────────────────
window.SZ_ACCENT = '#F59E0B';
