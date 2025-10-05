require('./bootstrap');

document.addEventListener('DOMContentLoaded', function () {
  // Confirmación de eliminación en admin (si existe)
  document.querySelectorAll('form.delete-form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          title: '¿Confirmar eliminación?',
          text: 'Esta acción no se puede deshacer.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar'
        }).then((result) => {
          if (result.isConfirmed) {
            form.submit();
          }
        });
      } else {
        if (confirm('¿Eliminar?')) form.submit();
      }
    });
  });

  // Filtro por categorías en la página principal
  const categoryButtons = document.querySelectorAll('.category-btn');
  const cardColumns = document.querySelectorAll('#cards-grid .card-column');

  function applyFilter(catId) {
    cardColumns.forEach(function (col) {
      if (!catId || col.dataset.category === String(catId)) {
        col.style.display = '';
      } else {
        col.style.display = 'none';
      }
    });
  }

  const firstActive = document.querySelector('.category-btn.active');
  if (firstActive) applyFilter(firstActive.dataset.category);

  categoryButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      categoryButtons.forEach(function (b) { b.classList.remove('active'); });
      btn.classList.add('active');
      applyFilter(btn.dataset.category);
    });
  });

  // Visor de medios (modal) para tarjetas desbloqueadas
  const mediaViewer = document.getElementById('mediaViewer');
  const viewerSlides = document.getElementById('viewerSlides');
  const prevBtn = document.getElementById('prevSlide');
  const nextBtn = document.getElementById('nextSlide');
  let mediaItems = [];
  let currentIndex = 0;

  function renderSlide(index) {
    if (!mediaItems.length) return;
    currentIndex = (index + mediaItems.length) % mediaItems.length;
    const item = mediaItems[currentIndex];
    viewerSlides.innerHTML = '';
    let el;
    if (item.type === 'image') {
      el = document.createElement('img');
      el.src = item.src;
      el.alt = 'Imagen';
      el.style.maxHeight = '80vh';
      el.style.maxWidth = '100%';
      el.style.width = 'auto';
      el.style.objectFit = 'contain';
      el.style.objectPosition = 'center';
    } else {
      el = document.createElement('video');
      el.controls = true; // mostrar controles reales
      el.autoplay = false; // evitar autoplay que puede ser bloqueado
      el.muted = false; // permitir audio, el usuario controla
      el.playsInline = true;
      el.preload = 'auto'; // cargar suficiente para poder reproducir
      el.style.maxHeight = '80vh';
      el.style.maxWidth = '100%';
      el.style.width = '100%';
      el.style.height = 'auto';
      el.style.objectFit = 'contain';
      const source = document.createElement('source');
      source.src = item.src;
      // no establecer type aquí; dejamos que el navegador detecte
      el.appendChild(source);
      // toggle play/pause al hacer click sobre el video
      el.addEventListener('click', function(){
        if (el.paused) {
          const p = el.play();
          if (p && p.catch) p.catch(() => {});
        } else {
          el.pause();
        }
      });
    }
    viewerSlides.appendChild(el);
    prevBtn.style.visibility = mediaItems.length > 1 ? 'visible' : 'hidden';
    nextBtn.style.visibility = mediaItems.length > 1 ? 'visible' : 'hidden';

    // Cargar metadata para habilitar controles y duración
    if (el.tagName === 'VIDEO') {
      el.load();
    }
  }

  // Delegación para asegurar captura del click en cualquier hijo
  document.addEventListener('click', function (e) {
    const container = e.target.closest('.upload-card .media-container');
    if (!container) return;
    if (container.getAttribute('data-clickable') !== 'true') return; // Bloqueado
    mediaItems = Array.from(container.querySelectorAll('.media-list .media-item')).map(function (el) {
      return { type: el.dataset.type, src: el.dataset.src, mime: el.dataset.mime };
    });
    if (!mediaItems.length) return;
    currentIndex = 0;
    renderSlide(currentIndex);
    try {
      const Modal = window.bootstrap ? window.bootstrap.Modal : null;
      if (Modal) {
        new Modal(mediaViewer).show();
      } else {
        // Fallback mínimo: mostrar como bloque
        mediaViewer.classList.add('show');
        mediaViewer.style.display = 'block';
      }
    } catch (err) {
      console.error('No se pudo abrir el visor:', err);
    }
  });

  prevBtn && prevBtn.addEventListener('click', function () { renderSlide(currentIndex - 1); });
  nextBtn && nextBtn.addEventListener('click', function () { renderSlide(currentIndex + 1); });

  mediaViewer && mediaViewer.addEventListener('hidden.bs.modal', function () {
    // Pausar cualquier video al cerrar
    viewerSlides.querySelectorAll('video').forEach(function (v) { v.pause(); });
    viewerSlides.innerHTML = '';
  });

  // -----------------
  // Carrito (frontend)
  // -----------------
  const cartIcon = document.getElementById('cartIcon');
  const cartBadge = document.getElementById('cartBadge');
  const cartModalEl = document.getElementById('cartModal');
  const checkoutModalEl = document.getElementById('checkoutModal');
  const cartItemsEl = document.getElementById('cartItems');
  const cartTotalStarsEl = document.getElementById('cartTotalStars');
  const cartTotalUsdEl = document.getElementById('cartTotalUsd');
  const checkoutBtn = document.getElementById('checkoutBtn');
  const confirmCheckoutBtn = document.getElementById('confirmCheckoutBtn');
  const checkoutTotalStarsEl = document.getElementById('checkoutTotalStars');
  const checkoutTotalUsdEl = document.getElementById('checkoutTotalUsd');

  let cart = [];
  try { cart = JSON.parse(localStorage.getItem('cart') || '[]'); } catch (_) { cart = []; }

  function saveCart() {
    localStorage.setItem('cart', JSON.stringify(cart));
    updateBadge();
  }
  function updateBadge() {
    const count = cart.reduce((acc, it) => acc + (it.cantidad || 1), 0);
    if (!cartBadge) return;
    cartBadge.textContent = String(count);
    cartBadge.classList.toggle('d-none', count === 0);
  }
  function calcTotals() {
    const stars = cart.reduce((acc, it) => acc + (it.stars * (it.cantidad || 1)), 0);
    const usd = stars / (window.STARS_PER_DOLLAR || 1);
    return { stars, usd };
  }
  function renderCart() {
    if (!cartItemsEl) return;
    if (cart.length === 0) {
      cartItemsEl.innerHTML = '<div class="text-muted">Tu carrito está vacío.</div>';
    } else {
      cartItemsEl.innerHTML = cart.map(function(it, idx){
        return `<div class="d-flex justify-content-between align-items-center border-bottom py-2">
          <div>
            <div class="fw-bold">${it.title}</div>
            <div class="small text-muted"><i class="fas fa-star text-warning"></i> ${it.stars}</div>
          </div>
          <button class="btn btn-outline-danger btn-sm" data-remove-index="${idx}"><i class="fas fa-trash"></i></button>
        </div>`;
      }).join('');
    }
    const totals = calcTotals();
    cartTotalStarsEl && (cartTotalStarsEl.textContent = String(totals.stars));
    cartTotalUsdEl && (cartTotalUsdEl.textContent = `$${totals.usd.toFixed(2)}`);
  }
  function openCart() {
    renderCart();
    const Modal = window.bootstrap ? window.bootstrap.Modal : null;
    if (Modal && cartModalEl) new Modal(cartModalEl).show();
  }

  // Click en icono del carrito
  cartIcon && cartIcon.addEventListener('click', function(e){ e.preventDefault(); openCart(); });
  updateBadge();

  // Agregar al carrito (delegación)
  document.addEventListener('click', function(e){
    const btn = e.target.closest('.add-to-cart');
    if (!btn) return;
    const id = Number(btn.dataset.id);
    const title = btn.dataset.title;
    const stars = Number(btn.dataset.stars);
    if (!id || !stars) return;
    // Evitar duplicados: si existe, ignoramos o podríamos incrementar cantidad
    const exists = cart.find(it => it.id === id);
    if (!exists) cart.push({ id, title, stars, cantidad: 1 });
    saveCart();
    openCart();
  });

  // -----------------
  // Reacciones
  // -----------------
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  let reactionPopoverEl;

  function ensurePopover() {
    if (reactionPopoverEl) return reactionPopoverEl;
    reactionPopoverEl = document.createElement('div');
    reactionPopoverEl.className = 'reaction-popover';
    document.body.appendChild(reactionPopoverEl);
    return reactionPopoverEl;
  }

  function positionPopover(el, anchor) {
    const rect = anchor.getBoundingClientRect();
    const top = window.scrollY + rect.top - 10; // encima un poco
    const left = window.scrollX + rect.left + (rect.width/2);
    el.style.top = `${top}px`;
    el.style.left = `${left - el.offsetWidth/2}px`;
  }

  function renderShortEmojis(uploadId) {
    const list = (window.REACTION_EMOJIS || ['❤','😍','🔥','👍','😘']).slice(0,5);
    const moreBtn = '<span class="more">▾</span>';
    return list.map(e => `<span class="emoji" data-upload-id="${uploadId}" data-emoji="${e}">${e}</span>`).join('') + moreBtn;
  }

  function openPopover(anchor) {
    const uploadId = Number(anchor.dataset.uploadId);
    if (!uploadId) return;
    const el = ensurePopover();
    el.innerHTML = renderShortEmojis(uploadId);
    el.style.display = 'block';
    // Primero posicionamos, luego re-posicionamos por ancho calculado
    positionPopover(el, anchor);
    positionPopover(el, anchor);
  }

  function closePopover() {
    if (reactionPopoverEl) reactionPopoverEl.style.display = 'none';
  }

  async function refreshReactions(uploadId) {
    try {
      const res = await fetch(`/reactions/${uploadId}`);
      if (!res.ok) return;
      const data = await res.json();
      const cont = document.getElementById(`reactions-${uploadId}`);
      if (!cont) return;
      const counts = {};
      (data.reactions || []).forEach(function(r){ counts[r] = (counts[r] || 0) + 1; });
      cont.innerHTML = Object.keys(counts).map(function(emoji){
        const c = counts[emoji];
        return `<span class="reaction">${emoji} <span class="badge bg-light text-dark">${c}</span></span>`;
      }).join('');
    } catch (err) { /* noop */ }
  }

  async function saveReaction(uploadId, emoji) {
    if (!window.IS_AUTH) {
      // ignorar si no autenticado
      return;
    }
    try {
      const res = await fetch('/reactions', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN || ''
        },
        body: JSON.stringify({ upload_id: uploadId, reaction: emoji })
      });
      if (res.ok) {
        await refreshReactions(uploadId);
      }
    } catch (err) { /* noop */ }
  }

  // Delegación: abrir popover en escritorio con click
  document.addEventListener('click', function(e){
    const btn = e.target.closest('.reaction-btn');
    if (btn) {
      openPopover(btn);
    }
    // Cerrar si click fuera
    const insidePopover = e.target.closest('.reaction-popover');
    if (!btn && !insidePopover) closePopover();
  });

  // Delegación: elegir emoji o abrir listado completo
  document.addEventListener('click', function(e){
    const emojiEl = e.target.closest('.reaction-popover .emoji');
    if (emojiEl) {
      const uploadId = Number(emojiEl.dataset.uploadId);
      const emoji = emojiEl.dataset.emoji;
      closePopover();
      saveReaction(uploadId, emoji);
      return;
    }
    const moreEl = e.target.closest('.reaction-popover .more');
    if (moreEl) {
      // Abrir modal de selector completo
      const picker = document.getElementById('emojiPickerModal');
      if (picker && window.bootstrap) {
        const modal = new window.bootstrap.Modal(picker);
        picker.dataset.uploadId = String(document.querySelector('.reaction-popover .emoji')?.dataset.uploadId || '');
        modal.show();
      }
    }
  });

  // Long press en móvil para abrir popover
  let pressTimer = null;
  document.addEventListener('touchstart', function(e){
    const btn = e.target.closest('.reaction-btn');
    if (!btn) return;
    pressTimer = setTimeout(function(){ openPopover(btn); }, 400);
  });
  document.addEventListener('touchend', function(){ if (pressTimer) { clearTimeout(pressTimer); pressTimer = null; } });

  // Selección desde modal completo
  document.addEventListener('click', function(e){
    const fullEmoji = e.target.closest('#emojiPickerModal .emoji-option');
    if (!fullEmoji) return;
    const picker = document.getElementById('emojiPickerModal');
    const uploadId = Number(picker?.dataset.uploadId || '0');
    const emoji = fullEmoji.dataset.emoji;
    if (window.bootstrap && picker) {
      const m = window.bootstrap.Modal.getInstance(picker);
      m && m.hide();
    }
    saveReaction(uploadId, emoji);
  });

  // Remove item from cart
  cartItemsEl && cartItemsEl.addEventListener('click', function(e){
    const idxStr = e.target.closest('[data-remove-index]')?.getAttribute('data-remove-index');
    if (idxStr == null) return;
    const idx = Number(idxStr);
    cart.splice(idx, 1);
    saveCart();
    renderCart();
  });

  // Checkout flow: redirigir a página dedicada
  checkoutBtn && checkoutBtn.addEventListener('click', function(){
    window.location.href = '/pago';
  });

  confirmCheckoutBtn && confirmCheckoutBtn.addEventListener('click', function(){
    const method = document.querySelector('input[name="paymentMethod"]:checked')?.value;
    if (!method) { alert('Selecciona un método de pago'); return; }
    if (!window.IS_AUTH) { window.location.href = '/login'; return; }
    if (cart.length === 0) { alert('Tu carrito está vacío'); return; }
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    fetch('/checkout', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
      body: JSON.stringify({ items: cart.map(it => ({ id: it.id, cantidad: it.cantidad })), metodo: method })
    }).then(r => r.json()).then(resp => {
      if (resp.success) {
        cart = [];
        saveCart();
        alert('Pago realizado y orden registrada.');
        window.location.reload();
      } else {
        alert(resp.message || 'No se pudo procesar el pago');
      }
    }).catch(err => {
      alert('Error de red en checkout');
      console.error(err);
    });
  });
});