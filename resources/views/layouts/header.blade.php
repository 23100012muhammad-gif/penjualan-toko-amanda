<!-- top navigation -->
<div class="top_nav">
  <div class="nav_menu d-flex align-items-center justify-content-between">
    <div class="nav toggle">
      <a id="menu_toggle"><i class="fas fa-bars"></i></a>
    </div>
    <nav class="nav navbar-nav ms-auto">
      <ul class="navbar-right d-flex align-items-center gap-3 pe-3">

        <!-- Kalender / Tanggal Hari Ini -->
        <li class="nav-item dropdown">
          <a href="#" role="button" class="dropdown-toggle d-flex align-items-center gap-2"
             id="calendarDropdown" data-bs-toggle="dropdown" aria-expanded="false"
             style="text-decoration:none;color:inherit;">
            <span style="display:flex;align-items:center;justify-content:center;
                         width:32px;height:32px;background:linear-gradient(135deg,#1a73e8,#0d5bba);
                         border-radius:8px;flex-shrink:0;">
              <i class="bi bi-calendar3" style="font-size:14px;color:#fff;"></i>
            </span>
            <span id="header-date-short" style="font-size:12.5px;font-weight:600;color:#2a3f54;white-space:nowrap;"></span>
          </a>
          <div class="dropdown-menu dropdown-menu-end p-0" aria-labelledby="calendarDropdown"
               style="min-width:220px;border-radius:12px;overflow:hidden;box-shadow:0 8px 24px rgba(0,0,0,.12);border:none;">
            <!-- Header kalender -->
            <div style="background:linear-gradient(135deg,#1a73e8,#0d5bba);padding:16px 20px;text-align:center;">
              <div id="cal-hari-nama" style="font-size:12px;color:rgba(255,255,255,.75);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:4px;"></div>
              <div id="cal-tanggal" style="font-size:42px;font-weight:700;color:#fff;line-height:1;"></div>
              <div id="cal-bulan-tahun" style="font-size:14px;color:rgba(255,255,255,.85);margin-top:4px;"></div>
            </div>
            <!-- Info waktu -->
            <div style="padding:12px 20px;background:#fff;text-align:center;border-top:1px solid #f0f0f0;">
              <div style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Waktu Sekarang</div>
              <div id="cal-jam" style="font-size:22px;font-weight:700;color:#2a3f54;letter-spacing:2px;"></div>
            </div>
          </div>
        </li>

        @if(Auth::check() && Auth::user()->hasRole(['pemilik', 'admin']))
        <!-- Dropdown Notifikasi -->
        <li class="nav-item dropdown" id="notif-li">
          <a href="#" role="button" class="dropdown-toggle d-flex align-items-center position-relative"
             id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false"
             style="text-decoration:none;color:inherit;" onclick="resetUnreadBadge()">
            <span style="display:flex;align-items:center;justify-content:center;
                         width:32px;height:32px;background:#f3f4f6;border:1px solid #e5e7eb;
                         border-radius:8px;flex-shrink:0;">
              <i class="bi bi-bell" style="font-size:14px;color:#4b5563;"></i>
            </span>
            <span id="notif-badge" class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"
                  style="display:none;margin-top:4px;margin-left:-4px;"></span>
          </a>
          <div class="dropdown-menu dropdown-menu-end p-0" aria-labelledby="notifDropdown"
               style="width:320px;border-radius:12px;overflow:hidden;box-shadow:0 8px 24px rgba(0,0,0,.12);border:none;"
               onclick="event.stopPropagation()">
            
            <!-- Header Notifikasi -->
            <div class="d-flex align-items-center justify-content-between px-3 py-2" style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
              <span class="fw-bold" style="font-size:12.5px;color:#1f2937;">Notifikasi (<span id="notif-count">0</span>)</span>
              <div class="form-check m-0">
                <input class="form-check-input" type="checkbox" id="selectAllNotif" style="cursor:pointer;" onclick="toggleSelectAllNotif(this)">
                <label class="form-check-label text-muted" for="selectAllNotif" style="font-size:11px;cursor:pointer;user-select:none;margin-top:2px;">Semua</label>
              </div>
            </div>

            <!-- List Notifikasi -->
            <div id="notif-list-container" style="max-height:260px;overflow-y:auto;background:#fff;">
              <!-- Javascript will render notification items here -->
            </div>

            <!-- Footer Notifikasi -->
            <div class="d-flex gap-2 px-3 py-2" style="background:#f9fafb;border-top:1px solid #f0f0f0;">
              <button class="btn btn-sm btn-outline-secondary w-50 m-0" style="font-size:11px;padding:5px 0;" onclick="clearSelectedNotif()">Bersihkan Terpilih</button>
              <button class="btn btn-sm btn-danger w-50 m-0" style="font-size:11px;padding:5px 0;" onclick="clearAllNotif()">Bersihkan Semua</button>
            </div>

          </div>
        </li>

        @php
          $notifList = [];
          if (Auth::check() && Auth::user()->hasRole(['pemilik', 'admin'])) {
              // 1. Low stock items
              $lowStockItems = \App\Models\Barang::where('aktif', true)
                                                 ->where('stok', '<=', 5)
                                                 ->orderBy('stok', 'asc')
                                                 ->take(5)
                                                 ->get();
              foreach ($lowStockItems as $b) {
                  $notifList[] = [
                      'id' => 'stock-low-' . $b->id,
                      'type' => 'stock',
                      'title' => 'Stok Kritis',
                      'text' => $b->nama_barang . ' sisa ' . $b->stok . ' ' . ($b->satuan_jual ?? 'Pcs'),
                      'time' => 'Stok saat ini',
                      'link' => Auth::user()->role === 'admin' ? route('barang.index') : '#',
                      'badge' => $b->stok == 0 ? 'bg-danger' : 'bg-warning text-dark',
                  ];
              }

              // 2. Latest sales transactions
              $latestSales = \App\Models\Penjualan::orderBy('created_at', 'desc')
                                                  ->take(5)
                                                  ->get();
              foreach ($latestSales as $trx) {
                  $notifList[] = [
                      'id' => 'sale-new-' . $trx->id,
                      'type' => 'sale',
                      'title' => 'Penjualan Baru',
                      'text' => 'Faktur ' . $trx->kode_transaksi . ' - Rp ' . number_format($trx->total, 0, ',', '.'),
                      'time' => $trx->created_at?->diffForHumans() ?? '',
                      'link' => route('penjualan.histori'),
                      'badge' => 'bg-success',
                  ];
              }
          }
        @endphp

        <style>
          .notif-item:hover { background: #f9fafb; }
          .notif-checkbox:focus { box-shadow: none; }
        </style>

        <script>
          window.allNotificationsRaw = @json($notifList);
          let activeNotifications = [];

          function renderNotifications(notifs) {
            const dismissed = JSON.parse(localStorage.getItem('dismissed_notifs') || '[]');
            activeNotifications = notifs.filter(n => !dismissed.includes(n.id));

            const container = document.getElementById('notif-list-container');
            const countEl = document.getElementById('notif-count');
            const badgeEl = document.getElementById('notif-badge');
            const selectAllCheckbox = document.getElementById('selectAllNotif');

            if (countEl) countEl.textContent = activeNotifications.length;
            if (selectAllCheckbox) selectAllCheckbox.checked = false;

            // Manage unread dot
            const lastViewedCount = parseInt(localStorage.getItem('notif_last_count') || '0');
            if (activeNotifications.length > 0 && activeNotifications.length > lastViewedCount) {
              if (badgeEl) badgeEl.style.display = 'block';
            } else {
              if (badgeEl) badgeEl.style.display = 'none';
            }

            if (!container) return;

            if (activeNotifications.length === 0) {
              container.innerHTML = `
                <div class="text-center text-muted py-4 px-3" style="font-size:12px;">
                  <i class="bi bi-bell-slash d-block fs-4 text-gray-400 mb-2"></i>
                  Tidak ada notifikasi baru
                </div>
              `;
              return;
            }

            container.innerHTML = activeNotifications.map(n => {
              let iconHtml = '';
              if (n.type === 'stock') {
                iconHtml = '<i class="bi bi-exclamation-triangle-fill" style="font-size:10px;color:inherit;"></i>';
              } else if (n.type === 'sale') {
                iconHtml = '<i class="bi bi-cart-fill" style="font-size:10px;color:#fff;"></i>';
              }

              return `
                <div class="d-flex align-items-start px-3 py-2.5 notif-item" id="notif-item-${n.id}" 
                     style="border-bottom:1px solid #f3f4f6; transition:background .15s;">
                  <div class="form-check m-0 me-2 mt-1">
                    <input class="form-check-input notif-checkbox" type="checkbox" value="${n.id}" style="cursor:pointer;" onchange="updateSelectAllState()">
                  </div>
                  <a href="${n.link}" class="d-flex align-items-start text-decoration-none" style="color:inherit; flex-grow:1; min-width:0;">
                    <span class="badge ${n.badge} p-0 rounded-circle me-2 mt-1 d-flex align-items-center justify-content-center" 
                          style="width:22px; height:22px; flex-shrink:0; line-height:22px;">
                      ${iconHtml}
                    </span>
                    <div style="flex-grow:1; min-width:0;">
                      <div class="fw-bold text-dark" style="font-size:12px; line-height:1.25;">${n.title}</div>
                      <div class="text-muted" style="font-size:11px; line-height:1.3; margin-top:2px; word-break:break-word;">${n.text}</div>
                      <div class="text-muted" style="font-size:9.5px; margin-top:3px;">${n.time}</div>
                    </div>
                  </a>
                </div>
              `;
            }).join('');
          }

          function resetUnreadBadge() {
            const badgeEl = document.getElementById('notif-badge');
            if (badgeEl) badgeEl.style.display = 'none';
            localStorage.setItem('notif_last_count', activeNotifications.length.toString());
          }

          function toggleSelectAllNotif(el) {
            const checkboxes = document.querySelectorAll('.notif-checkbox');
            checkboxes.forEach(c => c.checked = el.checked);
          }

          function updateSelectAllState() {
            const checkboxes = document.querySelectorAll('.notif-checkbox');
            const checked = document.querySelectorAll('.notif-checkbox:checked');
            const selectAllCheckbox = document.getElementById('selectAllNotif');
            if (selectAllCheckbox) {
              selectAllCheckbox.checked = checkboxes.length > 0 && checkboxes.length === checked.length;
            }
          }

          function clearSelectedNotif() {
            const checked = document.querySelectorAll('.notif-checkbox:checked');
            if (checked.length === 0) return;

            const dismissed = JSON.parse(localStorage.getItem('dismissed_notifs') || '[]');
            checked.forEach(c => {
              dismissed.push(c.value);
            });

            localStorage.setItem('dismissed_notifs', JSON.stringify(dismissed));
            renderNotifications(window.allNotificationsRaw || []);
          }

          function clearAllNotif() {
            if (activeNotifications.length === 0) return;

            const dismissed = JSON.parse(localStorage.getItem('dismissed_notifs') || '[]');
            activeNotifications.forEach(n => {
              dismissed.push(n.id);
            });

            localStorage.setItem('dismissed_notifs', JSON.stringify(dismissed));
            renderNotifications(window.allNotificationsRaw || []);
          }

          document.addEventListener('DOMContentLoaded', function () {
            renderNotifications(window.allNotificationsRaw || []);
          });
        </script>
        @endif

        <!-- User Profile Dropdown -->
        <li class="nav-item dropdown">
          <a href="#" role="button" class="user-profile dropdown-toggle" aria-haspopup="true"
             id="navbarDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="{{ Auth::user()->avatarUrl() }}" alt=""
                 style="width:28px;height:28px;object-fit:cover;border-radius:50%;">
            {{ Auth::user()->name }}
          </a>
          <div class="dropdown-menu dropdown-menu-end dropdown-usermenu dropdown-menu-sm"
               aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="{{ route('account.edit') }}" role="button">
              <i class="fas fa-user-cog me-1"></i> Pengaturan Akun
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
              @csrf
            </form>
            <a class="dropdown-item" href="#"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
              <i class="fas fa-sign-out-alt float-end"></i> Log Out
            </a>
          </div>
        </li>

      </ul>
    </nav>
  </div>
</div>
<!-- /top navigation -->

<script>
(function () {
  var HARI  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  var BULAN = ['Januari','Februari','Maret','April','Mei','Juni',
               'Juli','Agustus','September','Oktober','November','Desember'];
  var BULAN_SINGKAT = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

  function pad(n) { return n < 10 ? '0' + n : n; }

  function update() {
    var now  = new Date();
    var h    = now.getHours();
    var m    = now.getMinutes();
    var s    = now.getSeconds();
    var tgl  = now.getDate();
    var bln  = now.getMonth();
    var thn  = now.getFullYear();
    var hari = now.getDay();

    // Header singkat di tombol
    var elShort = document.getElementById('header-date-short');
    if (elShort) elShort.textContent = tgl + ' ' + BULAN_SINGKAT[bln] + ' ' + thn;

    // Isi dropdown kalender
    var elHari = document.getElementById('cal-hari-nama');
    if (elHari) elHari.textContent = HARI[hari];

    var elTgl = document.getElementById('cal-tanggal');
    if (elTgl) elTgl.textContent = pad(tgl);

    var elBulanThn = document.getElementById('cal-bulan-tahun');
    if (elBulanThn) elBulanThn.textContent = BULAN[bln] + ' ' + thn;

    var elJam = document.getElementById('cal-jam');
    if (elJam) elJam.textContent = pad(h) + ':' + pad(m) + ':' + pad(s);
  }

  update();
  setInterval(update, 1000);
})();
</script>
