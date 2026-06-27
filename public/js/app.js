/**
 * =====================================================
 * Angkringan Songolimo POS - Frontend Application
 * =====================================================
 * 
 * Mengelola semua interaksi UI, keranjang pesanan,
 * komunikasi API, dan logika pembayaran.
 */

// ==========================================================
// STATE MANAGEMENT
// ==========================================================

const AppState = {
    menus: [],
    cart: [],
    activeView: 'pos',       // 'pos' | 'manage' | 'history'
    activeFilter: 'Semua',
    searchQuery: '',
    pajakPersen: 10,
    diskonPersen: 0,
};

// ==========================================================
// API HELPERS
// ==========================================================

const API = {
    baseUrl: 'api',

    async request(endpoint, method = 'GET', data = null) {
        const options = {
            method,
            headers: { 'Content-Type': 'application/json' },
        };

        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(`${this.baseUrl}/${endpoint}`, options);
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('API Error:', error);
            Toast.show('Gagal terhubung ke server', 'error');
            return { success: false, message: error.message };
        }
    },

// Menu CRUD
    getMenus(params = '') {
        return this.request(`menu${params ? '?' + params : ''}`);
    },
    createMenu(data) {
        return this.request('menu', 'POST', data);
    },
    updateMenu(data) {
        return this.request('menu', 'PUT', data);
    },
    deleteMenu(id) {
        return this.request('menu', 'DELETE', { id });
    },

    // Transaksi
    calculate(data) {
        return this.request('transaksi?action=calculate', 'POST', data);
    },
    checkout(data) {
        return this.request('transaksi?action=checkout', 'POST', data);
    },
    getReceipt(id) {
        return this.request(`transaksi?action=receipt&id=${id}`);
    },
    getHistory(limit = 20) {
        return this.request(`transaksi?action=history&limit=${limit}`);
    },
};

// ==========================================================
// UTILITY FUNCTIONS
// ==========================================================

function formatRupiah(amount) {
    return 'Rp ' + Number(amount).toLocaleString('id-ID');
}

function getMenuEmoji(kategori) {
    const emojis = {
        'Makanan': '🍚',
        'Minuman': '🥤',
        'Snack': '🍢',
        'Lainnya': '📦',
    };
    return emojis[kategori] || '📦';
}

function $(selector) {
    return document.querySelector(selector);
}

function $$(selector) {
    return document.querySelectorAll(selector);
}

// ==========================================================
// TOAST NOTIFICATIONS
// ==========================================================

const Toast = {
    container: null,

    init() {
        this.container = document.createElement('div');
        this.container.className = 'toast-container';
        document.body.appendChild(this.container);
    },

    show(message, type = 'info', duration = 3000) {
        const icons = { success: '✓', error: '✕', info: 'ℹ' };
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `<span>${icons[type] || 'ℹ'}</span><span>${message}</span>`;

        this.container.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('toast-out');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },
};

// ==========================================================
// CLOCK
// ==========================================================

function updateClock() {
    const el = $('#clock-time');
    if (el) {
        const now = new Date();
        el.textContent = now.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        });
    }
}

// ==========================================================
// NAVIGATION
// ==========================================================

function switchView(view) {
    AppState.activeView = view;

    // Update nav buttons
    $$('.nav-btn').forEach(btn => btn.classList.remove('active'));
    const activeBtn = document.querySelector(`[data-view="${view}"]`);
    if (activeBtn) activeBtn.classList.add('active');

    // Show/hide views
    $$('.view-section').forEach(section => section.classList.remove('active'));
    const activeSection = $(`#view-${view}`);
    if (activeSection) activeSection.classList.add('active');

    // Load data for view
    if (view === 'manage') loadMenuTable();
    if (view === 'history') loadHistory();
}

// ==========================================================
// MENU RENDERING (POS VIEW)
// ==========================================================

async function loadMenus() {
    const result = await API.getMenus();
    if (result.success) {
        AppState.menus = result.data;
        renderMenuGrid();
    }
}

function renderMenuGrid() {
    const grid = $('#menu-grid');
    if (!grid) return;

    let filtered = [...AppState.menus];

    // Filter by category
    if (AppState.activeFilter !== 'Semua') {
        filtered = filtered.filter(m => m.kategori === AppState.activeFilter);
    }

    // Filter by search
    if (AppState.searchQuery) {
        const q = AppState.searchQuery.toLowerCase();
        filtered = filtered.filter(m => m.nama.toLowerCase().includes(q));
    }

    if (filtered.length === 0) {
        grid.innerHTML = `
            <div style="grid-column: 1/-1; text-align: center; padding: 3rem; color: var(--color-text-muted);">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🔍</div>
                <p>Tidak ada menu yang ditemukan</p>
            </div>
        `;
        return;
    }

    grid.innerHTML = filtered.map(menu => `
        <div class="menu-item" onclick="addToCart(${menu.id})" title="Klik untuk menambah ke keranjang">
            <div class="item-actions">
                <button class="item-action-btn edit" onclick="event.stopPropagation(); openEditModal(${menu.id})" title="Edit">✎</button>
                <button class="item-action-btn delete" onclick="event.stopPropagation(); confirmDelete(${menu.id}, '${menu.nama.replace(/'/g, "\\'")}')" title="Hapus">✕</button>
            </div>
            <div class="item-emoji">${getMenuEmoji(menu.kategori)}</div>
            <div class="item-name">${escapeHtml(menu.nama)}</div>
            <div class="item-category">${menu.kategori}</div>
            <div class="item-price">${formatRupiah(menu.harga)}</div>
        </div>
    `).join('');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ==========================================================
// CART MANAGEMENT
// ==========================================================

function addToCart(menuId) {
    const menu = AppState.menus.find(m => m.id == menuId);
    if (!menu) return;

    const existing = AppState.cart.find(item => item.menu_id == menuId);

    if (existing) {
        existing.kuantitas++;
    } else {
        AppState.cart.push({
            menu_id: menu.id,
            nama: menu.nama,
            harga: parseFloat(menu.harga),
            kategori: menu.kategori,
            kuantitas: 1,
        });
    }

    renderCart();
    updateCartBadge();

    // Visual feedback on menu item
    const menuItems = $$('.menu-item');
    menuItems.forEach(el => {
        if (el.onclick && el.onclick.toString().includes(menuId)) {
            el.classList.add('added');
            setTimeout(() => el.classList.remove('added'), 500);
        }
    });

    Toast.show(`${menu.nama} ditambahkan`, 'success');
}

function updateQuantity(menuId, delta) {
    const item = AppState.cart.find(i => i.menu_id == menuId);
    if (!item) return;

    item.kuantitas += delta;

    if (item.kuantitas <= 0) {
        removeFromCart(menuId);
        return;
    }

    renderCart();
    updateCartBadge();
}

function removeFromCart(menuId) {
    AppState.cart = AppState.cart.filter(i => i.menu_id != menuId);
    renderCart();
    updateCartBadge();
}

function clearCart() {
    if (AppState.cart.length === 0) return;
    AppState.cart = [];
    AppState.diskonPersen = 0;
    renderCart();
    updateCartBadge();
    Toast.show('Keranjang dikosongkan', 'info');
}

function updateCartBadge() {
    const badge = $('#cart-badge');
    const total = AppState.cart.reduce((sum, i) => sum + i.kuantitas, 0);
    if (badge) {
        badge.textContent = total;
        badge.style.display = total > 0 ? 'inline' : 'none';
    }
}

function renderCart() {
    const wrapper = $('#cart-items');
    const summaryEl = $('#cart-summary');
    const actionsEl = $('#cart-actions');

    if (!wrapper) return;

    if (AppState.cart.length === 0) {
        wrapper.innerHTML = `
            <div class="cart-empty">
                <div class="empty-icon">🛒</div>
                <p>Keranjang kosong</p>
                <p style="font-size: 0.75rem; margin-top: 0.5rem;">Klik menu untuk menambahkan</p>
            </div>
        `;
        if (summaryEl) summaryEl.style.display = 'none';
        if (actionsEl) actionsEl.style.display = 'none';
        return;
    }

    if (summaryEl) summaryEl.style.display = 'block';
    if (actionsEl) actionsEl.style.display = 'flex';

    wrapper.innerHTML = AppState.cart.map(item => `
        <div class="cart-item">
            <div class="cart-item-info">
                <div class="name">${escapeHtml(item.nama)}</div>
                <div class="price">${formatRupiah(item.harga)}</div>
            </div>
            <div class="qty-controls">
                <button class="qty-btn remove" onclick="updateQuantity(${item.menu_id}, -1)">−</button>
                <span class="qty-value">${item.kuantitas}</span>
                <button class="qty-btn" onclick="updateQuantity(${item.menu_id}, 1)">+</button>
            </div>
            <div class="cart-item-total">${formatRupiah(item.harga * item.kuantitas)}</div>
        </div>
    `).join('');

    updateSummary();
}

function updateSummary() {
    const subtotal = AppState.cart.reduce((sum, i) => sum + (i.harga * i.kuantitas), 0);
    const pajak = AppState.pajakPersen;
    const diskon = AppState.diskonPersen;
    const pajakNominal = subtotal * (pajak / 100);
    const diskonNominal = subtotal * (diskon / 100);
    const grandTotal = Math.max(0, subtotal + pajakNominal - diskonNominal);

    const summaryEl = $('#cart-summary');
    if (summaryEl) {
        summaryEl.innerHTML = `
            <div class="summary-row">
                <span class="label">Subtotal</span>
                <span class="value">${formatRupiah(subtotal)}</span>
            </div>
            <div class="summary-row tax">
                <span class="label">
                    <span class="summary-inline-input">
                        Pajak (PPN
                        <input type="number" id="input-pajak" value="${pajak}" min="0" max="100" step="0.5" 
                               onchange="AppState.pajakPersen = parseFloat(this.value) || 0; updateSummary();">
                        <span>%)</span>
                    </span>
                </span>
                <span class="value">+ ${formatRupiah(pajakNominal)}</span>
            </div>
            <div class="summary-row discount">
                <span class="label">
                    <span class="summary-inline-input">
                        Diskon
                        <input type="number" id="input-diskon" value="${diskon}" min="0" max="100" step="0.5"
                               onchange="AppState.diskonPersen = parseFloat(this.value) || 0; updateSummary();">
                        <span>%)</span>
                    </span>
                </span>
                <span class="value">- ${formatRupiah(diskonNominal)}</span>
            </div>
            <div class="summary-divider"></div>
            <div class="summary-row grand-total">
                <span class="label">Grand Total</span>
                <span class="value">${formatRupiah(grandTotal)}</span>
            </div>
        `;
    }
}

// ==========================================================
// PAYMENT MODAL
// ==========================================================

function openPaymentModal() {
    if (AppState.cart.length === 0) {
        Toast.show('Keranjang masih kosong', 'error');
        return;
    }

    const subtotal = AppState.cart.reduce((sum, i) => sum + (i.harga * i.kuantitas), 0);
    const pajakNominal = subtotal * (AppState.pajakPersen / 100);
    const diskonNominal = subtotal * (AppState.diskonPersen / 100);
    const grandTotal = Math.max(0, subtotal + pajakNominal - diskonNominal);

    // Quick pay denominations
    const denoms = generateDenominations(grandTotal);

    const modal = $('#modal-overlay');
    const modalContent = $('#modal-content');

    modalContent.innerHTML = `
        <div class="modal-header">
            <h3>💰 Pembayaran</h3>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <div class="modal-body">
            <div class="payment-display">
                <div class="total-label">Total yang harus dibayar</div>
                <div class="total-amount">${formatRupiah(grandTotal)}</div>
            </div>
            
            <div class="form-group">
                <label for="input-bayar">Uang Diterima</label>
                <input type="number" id="input-bayar" class="form-control" 
                       placeholder="Masukkan nominal pembayaran"
                       oninput="calculateChange(${grandTotal})"
                       style="font-family: var(--font-mono); font-size: 1.2rem; text-align: center; font-weight: 700;">
            </div>
            
            <div class="quick-pay-grid">
                ${denoms.map(d => `
                    <button class="quick-pay-btn" onclick="setPayAmount(${d}, ${grandTotal})">
                        ${formatRupiah(d)}
                    </button>
                `).join('')}
                <button class="quick-pay-btn" onclick="setPayAmount(${grandTotal}, ${grandTotal})" style="border-color: var(--color-accent); color: var(--color-accent-light);">
                    Uang Pas
                </button>
            </div>
            
            <div id="change-display" style="display: none;"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal()">Batal</button>
            <button class="btn btn-success btn-lg" id="btn-process-pay" onclick="processPayment(${grandTotal})" disabled>
                ✓ Proses Pembayaran
            </button>
        </div>
    `;

    modal.classList.add('active');
    setTimeout(() => $('#input-bayar')?.focus(), 300);
}

function generateDenominations(total) {
    const denoms = [];
    const bases = [1000, 2000, 5000, 10000, 20000, 50000, 100000];

    for (const base of bases) {
        const rounded = Math.ceil(total / base) * base;
        if (rounded >= total && rounded > 0 && !denoms.includes(rounded)) {
            denoms.push(rounded);
        }
    }

    // Sort and take max 5
    denoms.sort((a, b) => a - b);
    return denoms.slice(0, 5);
}

function setPayAmount(amount, grandTotal) {
    const input = $('#input-bayar');
    if (input) {
        input.value = amount;
        calculateChange(grandTotal);
    }
}

function calculateChange(grandTotal) {
    const input = $('#input-bayar');
    const display = $('#change-display');
    const processBtn = $('#btn-process-pay');

    if (!input || !display) return;

    const bayar = parseFloat(input.value) || 0;

    if (bayar <= 0) {
        display.style.display = 'none';
        if (processBtn) processBtn.disabled = true;
        return;
    }

    display.style.display = 'block';
    const kembali = bayar - grandTotal;

    if (kembali >= 0) {
        display.className = 'change-display';
        display.innerHTML = `
            <div class="change-label">Uang Kembali</div>
            <div class="change-amount">${formatRupiah(kembali)}</div>
        `;
        if (processBtn) processBtn.disabled = false;
    } else {
        display.className = 'change-display error';
        display.innerHTML = `
            <div class="change-label">Uang Kurang</div>
            <div class="change-amount">${formatRupiah(Math.abs(kembali))}</div>
        `;
        if (processBtn) processBtn.disabled = true;
    }
}

async function processPayment(grandTotal) {
    const bayar = parseFloat($('#input-bayar')?.value) || 0;

    if (bayar < grandTotal) {
        Toast.show('Uang tidak cukup!', 'error');
        return;
    }

    const payload = {
        items: AppState.cart.map(i => ({
            menu_id: i.menu_id,
            kuantitas: i.kuantitas,
        })),
        pajak_persen: AppState.pajakPersen,
        diskon_persen: AppState.diskonPersen,
        uang_diterima: bayar,
    };

    const result = await API.checkout(payload);

    if (result.success) {
        Toast.show('Transaksi berhasil! 🎉', 'success');
        closeModal();

        // Clear cart
        AppState.cart = [];
        AppState.diskonPersen = 0;
        renderCart();
        updateCartBadge();

        // Reload menus (stok mungkin berubah)
        loadMenus();

        // Show receipt
        showReceipt(result.data);
    } else {
        Toast.show(result.message || 'Gagal memproses transaksi', 'error');
    }
}

// ==========================================================
// RECEIPT
// ==========================================================

function showReceipt(receiptData) {
    const modal = $('#modal-overlay');
    const modalContent = $('#modal-content');

    const { transaksi, items, toko } = receiptData;
    const date = new Date(transaksi.created_at);

    modalContent.innerHTML = `
        <div class="modal-header">
            <h3>🧾 Struk Pembayaran</h3>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <div class="modal-body" style="padding: 0;">
            <div class="receipt-container" id="receipt-printable">
                <div class="receipt-paper">
                    <div class="receipt-header">
                        <div class="store-name">☕ ${toko.nama}</div>
                        <div class="store-address">${toko.alamat}</div>
                        <div class="store-phone">${toko.telepon}</div>
                        <div class="receipt-no">${transaksi.no_struk}</div>
                        <div class="receipt-date">${date.toLocaleDateString('id-ID', {
                            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                        })} ${date.toLocaleTimeString('id-ID')}</div>
                    </div>
                    
                    <div class="receipt-items">
                        ${items.map(item => `
                            <div class="receipt-item">
                                <div class="item-detail">
                                    <div class="item-name">${escapeHtml(item.nama_menu)}</div>
                                    <div class="item-qty-price">${item.kuantitas} x ${formatRupiah(item.harga_satuan)}</div>
                                </div>
                                <div class="item-total">${formatRupiah(item.total_harga)}</div>
                            </div>
                        `).join('')}
                    </div>
                    
                    <div class="receipt-totals">
                        <div class="receipt-total-row">
                            <span>Subtotal</span>
                            <span>${formatRupiah(transaksi.subtotal)}</span>
                        </div>
                        <div class="receipt-total-row">
                            <span>Pajak (${transaksi.pajak_persen}%)</span>
                            <span>+ ${formatRupiah(transaksi.pajak_nominal)}</span>
                        </div>
                        ${parseFloat(transaksi.diskon_nominal) > 0 ? `
                        <div class="receipt-total-row" style="color: #10b981;">
                            <span>Diskon (${transaksi.diskon_persen}%)</span>
                            <span>- ${formatRupiah(transaksi.diskon_nominal)}</span>
                        </div>` : ''}
                        <div class="receipt-total-row grand">
                            <span>TOTAL</span>
                            <span>${formatRupiah(transaksi.grand_total)}</span>
                        </div>
                        <div class="receipt-total-row">
                            <span>Dibayar</span>
                            <span>${formatRupiah(transaksi.uang_diterima)}</span>
                        </div>
                        <div class="receipt-total-row" style="font-weight: 700;">
                            <span>Kembali</span>
                            <span>${formatRupiah(transaksi.uang_kembali)}</span>
                        </div>
                    </div>
                    
                    <div class="receipt-footer">
                        <div class="thank-you">${toko.pesan}</div>
                        <div class="footer-message">Powered by Angkringan Songolimo POS</div>
                    </div>
                </div>
                <div class="receipt-actions">
                    <button class="btn btn-primary" onclick="printReceipt()">🖨️ Cetak Struk</button>
                    <button class="btn btn-ghost" onclick="closeModal()">Tutup</button>
                </div>
            </div>
        </div>
    `;

    modal.classList.add('active');
}

function printReceipt() {
    window.print();
}

// ==========================================================
// MENU MANAGEMENT (CRUD VIEW)
// ==========================================================

async function loadMenuTable() {
    const result = await API.getMenus('show_all=1');
    if (!result.success) return;

    const container = $('#menu-table-body');
    if (!container) return;

    AppState.menus = result.data.filter(m => m.is_active == 1);

    container.innerHTML = result.data.map(menu => `
        <tr style="${menu.is_active == 0 ? 'opacity: 0.4;' : ''}">
            <td>${menu.id}</td>
            <td>
                <span style="margin-right: 6px;">${getMenuEmoji(menu.kategori)}</span>
                ${escapeHtml(menu.nama)}
                ${menu.is_active == 0 ? '<span style="color: var(--color-danger); font-size: 0.7rem;">(Nonaktif)</span>' : ''}
            </td>
            <td><span class="chip" style="cursor:default;">${menu.kategori}</span></td>
            <td class="price-col">${formatRupiah(menu.harga)}</td>
            <td>${menu.stok}</td>
            <td>
                <div class="actions-col">
                    <button class="item-action-btn edit" onclick="openEditModal(${menu.id})" title="Edit">✎</button>
                    <button class="item-action-btn delete" onclick="confirmDelete(${menu.id}, '${menu.nama.replace(/'/g, "\\'")}')" title="Hapus">✕</button>
                </div>
            </td>
        </tr>
    `).join('');
}

function openAddModal() {
    const modal = $('#modal-overlay');
    const modalContent = $('#modal-content');

    modalContent.innerHTML = `
        <div class="modal-header">
            <h3>➕ Tambah Menu Baru</h3>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="menu-nama">Nama Menu</label>
                <input type="text" id="menu-nama" class="form-control" placeholder="Contoh: Nasi Kucing Special">
            </div>
            <div class="form-group">
                <label for="menu-kategori">Kategori</label>
                <select id="menu-kategori" class="form-control">
                    <option value="Makanan">🍚 Makanan</option>
                    <option value="Minuman">🥤 Minuman</option>
                    <option value="Snack">🍢 Snack</option>
                    <option value="Lainnya">📦 Lainnya</option>
                </select>
            </div>
            <div class="form-group">
                <label for="menu-harga">Harga (Rp)</label>
                <input type="number" id="menu-harga" class="form-control" placeholder="0" min="0" step="500">
            </div>
            <div class="form-group">
                <label for="menu-stok">Stok</label>
                <input type="number" id="menu-stok" class="form-control" placeholder="100" min="0" value="100">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal()">Batal</button>
            <button class="btn btn-primary" onclick="submitAddMenu()">💾 Simpan</button>
        </div>
    `;

    modal.classList.add('active');
    setTimeout(() => $('#menu-nama')?.focus(), 300);
}

async function submitAddMenu() {
    const nama = $('#menu-nama')?.value.trim();
    const kategori = $('#menu-kategori')?.value;
    const harga = parseFloat($('#menu-harga')?.value) || 0;
    const stok = parseInt($('#menu-stok')?.value) || 100;

    if (!nama) {
        Toast.show('Nama menu wajib diisi', 'error');
        return;
    }

    if (harga <= 0) {
        Toast.show('Harga harus lebih dari 0', 'error');
        return;
    }

    const result = await API.createMenu({ nama, kategori, harga, stok });

    if (result.success) {
        Toast.show('Menu berhasil ditambahkan! 🎉', 'success');
        closeModal();
        loadMenus();
        if (AppState.activeView === 'manage') loadMenuTable();
    } else {
        Toast.show(result.message || 'Gagal menambahkan menu', 'error');
    }
}

async function openEditModal(menuId) {
    const result = await API.getMenus(`id=${menuId}`);
    if (!result.success) {
        Toast.show('Menu tidak ditemukan', 'error');
        return;
    }

    const menu = result.data;
    const modal = $('#modal-overlay');
    const modalContent = $('#modal-content');

    modalContent.innerHTML = `
        <div class="modal-header">
            <h3>✏️ Edit Menu</h3>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="edit-nama">Nama Menu</label>
                <input type="text" id="edit-nama" class="form-control" value="${escapeHtml(menu.nama)}">
            </div>
            <div class="form-group">
                <label for="edit-kategori">Kategori</label>
                <select id="edit-kategori" class="form-control">
                    <option value="Makanan" ${menu.kategori === 'Makanan' ? 'selected' : ''}>🍚 Makanan</option>
                    <option value="Minuman" ${menu.kategori === 'Minuman' ? 'selected' : ''}>🥤 Minuman</option>
                    <option value="Snack" ${menu.kategori === 'Snack' ? 'selected' : ''}>🍢 Snack</option>
                    <option value="Lainnya" ${menu.kategori === 'Lainnya' ? 'selected' : ''}>📦 Lainnya</option>
                </select>
            </div>
            <div class="form-group">
                <label for="edit-harga">Harga (Rp)</label>
                <input type="number" id="edit-harga" class="form-control" value="${menu.harga}" min="0" step="500">
            </div>
            <div class="form-group">
                <label for="edit-stok">Stok</label>
                <input type="number" id="edit-stok" class="form-control" value="${menu.stok}" min="0">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal()">Batal</button>
            <button class="btn btn-primary" onclick="submitEditMenu(${menu.id})">💾 Simpan Perubahan</button>
        </div>
    `;

    modal.classList.add('active');
    setTimeout(() => $('#edit-nama')?.focus(), 300);
}

async function submitEditMenu(menuId) {
    const nama = $('#edit-nama')?.value.trim();
    const kategori = $('#edit-kategori')?.value;
    const harga = parseFloat($('#edit-harga')?.value) || 0;
    const stok = parseInt($('#edit-stok')?.value) || 0;

    if (!nama) {
        Toast.show('Nama menu wajib diisi', 'error');
        return;
    }

    const result = await API.updateMenu({ id: menuId, nama, kategori, harga, stok });

    if (result.success) {
        Toast.show('Menu berhasil diperbarui! ✓', 'success');
        closeModal();
        loadMenus();
        if (AppState.activeView === 'manage') loadMenuTable();
    } else {
        Toast.show(result.message || 'Gagal mengupdate menu', 'error');
    }
}

function confirmDelete(menuId, menuName) {
    const modal = $('#modal-overlay');
    const modalContent = $('#modal-content');

    modalContent.innerHTML = `
        <div class="modal-header">
            <h3>⚠️ Konfirmasi Hapus</h3>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <div class="modal-body" style="text-align: center;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🗑️</div>
            <p style="font-size: 0.95rem; margin-bottom: 0.5rem;">Apakah Anda yakin ingin menghapus:</p>
            <p style="font-size: 1.1rem; font-weight: 700; color: var(--color-accent-light);">${escapeHtml(menuName)}</p>
            <p style="font-size: 0.78rem; color: var(--color-text-muted); margin-top: 0.5rem;">
                Menu yang sudah pernah digunakan di transaksi akan dinonaktifkan, bukan dihapus.
            </p>
        </div>
        <div class="modal-footer" style="justify-content: center;">
            <button class="btn btn-ghost" onclick="closeModal()">Batal</button>
            <button class="btn btn-danger" onclick="executeDelete(${menuId})">🗑️ Ya, Hapus</button>
        </div>
    `;

    modal.classList.add('active');
}

async function executeDelete(menuId) {
    const result = await API.deleteMenu(menuId);

    if (result.success) {
        Toast.show(result.message, 'success');
        closeModal();

        // Remove from cart if exists
        AppState.cart = AppState.cart.filter(i => i.menu_id != menuId);
        renderCart();
        updateCartBadge();

        loadMenus();
        if (AppState.activeView === 'manage') loadMenuTable();
    } else {
        Toast.show(result.message || 'Gagal menghapus menu', 'error');
    }
}

// ==========================================================
// TRANSACTION HISTORY
// ==========================================================

async function loadHistory() {
    const container = $('#history-list');
    if (!container) return;

    const result = await API.getHistory(50);

    if (!result.success || result.data.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 3rem; color: var(--color-text-muted);">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📋</div>
                <p>Belum ada riwayat transaksi</p>
            </div>
        `;
        return;
    }

    container.innerHTML = result.data.map(trx => {
        const date = new Date(trx.created_at);
        return `
            <div class="history-item" onclick="viewReceipt(${trx.id})">
                <div class="history-info">
                    <span class="invoice">${trx.no_struk}</span>
                    <span class="date">${date.toLocaleDateString('id-ID', { 
                        day: 'numeric', month: 'short', year: 'numeric' 
                    })} • ${date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}</span>
                </div>
                <span class="history-total">${formatRupiah(trx.grand_total)}</span>
            </div>
        `;
    }).join('');
}

async function viewReceipt(transactionId) {
    const result = await API.getReceipt(transactionId);
    if (result.success) {
        showReceipt(result.data);
    } else {
        Toast.show('Gagal memuat struk', 'error');
    }
}

// ==========================================================
// MODAL HELPERS
// ==========================================================

function closeModal() {
    const modal = $('#modal-overlay');
    if (modal) modal.classList.remove('active');
}

// Close modal on outside click
document.addEventListener('click', (e) => {
    if (e.target.id === 'modal-overlay') {
        closeModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeModal();
    }
});

// ==========================================================
// INITIALIZATION
// ==========================================================

document.addEventListener('DOMContentLoaded', () => {
    Toast.init();

    // Load menus
    loadMenus();

    // Clock
    updateClock();
    setInterval(updateClock, 1000);

    // Search input
    const searchInput = $('#search-input');
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                AppState.searchQuery = e.target.value;
                renderMenuGrid();
            }, 200);
        });
    }

    // Category filter chips
    $$('.chip[data-category]').forEach(chip => {
        chip.addEventListener('click', () => {
            $$('.chip[data-category]').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            AppState.activeFilter = chip.dataset.category;
            renderMenuGrid();
        });
    });

    // Initial render
    renderCart();
    updateCartBadge();
});
