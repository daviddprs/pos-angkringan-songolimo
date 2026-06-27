<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem Kasir (POS) Angkringan Songolimo - Point of Sale modern dengan antarmuka antigravity yang ringan dan elegan.">
    <title>Angkringan Songolimo — Sistem Kasir POS</title>
    
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>☕</text></svg>">
</head>
<body>
    <div class="app-wrapper">

        <header class="app-header">
            <div class="header-content">
                <div class="brand">
                    <div class="brand-icon">☕</div>
                    <div class="brand-text">
                        <h1>Angkringan Songolimo</h1>
                        <p>Point of Sale System</p>
                    </div>
                </div>

                <nav class="header-nav">
                    <button class="nav-btn active" data-view="pos" onclick="switchView('pos')">
                        🛒 Kasir
                        <span class="badge" id="cart-badge" style="display: none;">0</span>
                    </button>
                    <button class="nav-btn" data-view="manage" onclick="switchView('manage')">
                        📋 Kelola Menu
                    </button>
                    <button class="nav-btn" data-view="history" onclick="switchView('history')">
                        📊 Riwayat
                    </button>
                </nav>

                <div class="header-clock">
                    <div class="clock-dot"></div>
                    <span id="clock-time">00:00:00</span>
                </div>
            </div>
        </header>

        <main class="main-content">

            <div class="left-panel">

                <section id="view-pos" class="view-section active">
                    <div class="menu-toolbar">
                        <div class="search-box">
                            <span class="search-icon">🔍</span>
                            <input type="text" id="search-input" placeholder="Cari menu...">
                        </div>
                        <div class="filter-chips">
                            <button class="chip active" data-category="Semua">Semua</button>
                            <button class="chip" data-category="Makanan">🍚 Makanan</button>
                            <button class="chip" data-category="Minuman">🥤 Minuman</button>
                            <button class="chip" data-category="Snack">🍢 Snack</button>
                            <button class="chip" data-category="Lainnya">📦 Lainnya</button>
                        </div>
                    </div>

                    <div class="menu-grid" id="menu-grid">
                        <div style="grid-column: 1/-1; text-align: center; padding: 3rem;">
                            <div class="skeleton" style="width: 100%; height: 120px; margin-bottom: 1rem;"></div>
                            <div class="skeleton" style="width: 100%; height: 120px; margin-bottom: 1rem;"></div>
                            <div class="skeleton" style="width: 100%; height: 120px;"></div>
                        </div>
                    </div>
                </section>

                <section id="view-manage" class="view-section">
                    <div class="card card-float">
                        <div class="card-header">
                            <h2>📋 Manajemen Menu</h2>
                            <button class="btn btn-primary" onclick="openAddModal()">➕ Tambah Menu</button>
                        </div>
                        <div class="card-body" style="padding: 0; overflow-x: auto;">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Menu</th>
                                        <th>Kategori</th>
                                        <th>Harga</th>
                                        <th>Stok</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="menu-table-body">
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
                                            Memuat data...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section id="view-history" class="view-section">
                    <div class="card card-float">
                        <div class="card-header">
                            <h2>📊 Riwayat Transaksi</h2>
                        </div>
                        <div class="card-body" style="padding: 0;" id="history-list">
                            <div style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
                                Memuat data...
                            </div>
                        </div>
                    </div>
                </section>

            </div>

            <div class="cart-panel">
                <div class="card card-float">
                    <div class="card-header">
                        <h2>🛒 Pesanan</h2>
                        <button class="btn btn-ghost" onclick="clearCart()" style="font-size: 0.78rem; padding: 4px 12px;">
                            🗑️ Kosongkan
                        </button>
                    </div>

                    <div class="cart-items-wrapper" id="cart-items">
                        </div>

                    <div class="cart-summary" id="cart-summary" style="display: none;">
                        </div>

                    <div class="cart-actions" id="cart-actions" style="display: none;">
                        <button class="btn btn-success btn-block btn-lg" onclick="openPaymentModal()">
                            💰 Bayar Sekarang
                        </button>
                    </div>
                </div>
            </div>

        </main>

    </div>

    <div class="modal-overlay" id="modal-overlay">
        <div class="modal" id="modal-content">
            </div>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>