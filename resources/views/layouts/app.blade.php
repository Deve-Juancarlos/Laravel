<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SIFANO') - Distribuidora</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    @stack('head')

    <style>
      /* Estilos básicos del layout para integrarse con frontend_sidebar_menu */
      body { font-family: 'Segoe UI', Roboto, Arial, sans-serif; background: #f8fafc; margin:0; }
      .main-content { margin-left: 260px; transition: all .2s ease; min-height:100vh; }
      .topbar { background: #fff; border-bottom: 1px solid #e6edf3; position: sticky; top: 0; z-index: 999; }
      .container-fluid { max-width: 1200px; margin: 0 auto; padding: .6rem 1rem; }
      @media (max-width: 768px) { .main-content { margin-left: 0; } }
    </style>
</head>
<body>
    {{-- Sidebar (archivo parcial) --}}
    @includeWhen(View::exists('frontend_sidebar_menu'), 'frontend_sidebar_menu')

    {{-- Main --}}
    <main class="main-content">
        {{-- Topbar --}}
        <header class="topbar">
            <div class="container-fluid d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-link d-md-none" id="sidebarToggle" aria-label="Toggle sidebar">
                        <i class="fas fa-bars"></i>
                    </button>

                    <a href="{{ route('dashboard.contador') }}" class="text-decoration-none">
                        <strong style="font-size:1.05rem; color:#1f2937;">SIFANO</strong>
                        <small class="text-muted d-block">Distribuidora de Fármacos</small>
                    </a>
                </div>

                {{-- Search (dinámico con AJAX) --}}
                <div class="topbar-search d-none d-md-block position-relative" style="flex:1; max-width:520px;">
                    <div class="input-group position-relative">
                        <span class="input-group-text position-absolute" style="left:8px; top:50%; transform:translateY(-50%); border:none; background:transparent;">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input id="globalSearch" type="text" class="form-control" placeholder="Buscar productos, clientes, facturas..." aria-label="Buscar"
                               style="padding-left:34px;">
                    </div>

                    <div id="searchResults" class="list-group position-absolute" style="z-index:1200; width:100%; display:none; max-height:320px; overflow:auto;" role="listbox" aria-live="polite"></div>
                </div>

                {{-- Right area: notificaciones y usuario --}}
                <div class="topbar-right d-flex align-items-center gap-2">
                    {{-- Notificaciones dinámicas --}}
                    <div class="dropdown">
                        <button class="btn btn-link position-relative" id="notificationsBtn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notificaciones">
                            <i class="fas fa-bell fa-lg text-muted"></i>
                            <span id="notif-badge" class="badge bg-danger rounded-circle" style="position:absolute; top:0; right:0; font-size:.65rem; display:none;">0</span>
                        </button>

                        <div class="dropdown-menu dropdown-menu-end p-0" style="width:360px; max-height:420px; overflow:auto;" aria-labelledby="notificationsBtn">
                            <div id="notif-container">
                                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                                    <strong>Notificaciones</strong>
                                    <a id="markAllRead" href="#" class="small">Marcar leídas</a>
                                </div>
                                <div id="notif-list" class="list-group list-group-flush">
                                    <div class="list-group-item text-center py-4" id="notif-loading">
                                        <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                                        <small class="text-muted ms-2">Cargando...</small>
                                    </div>
                                </div>
                                <div class="p-2 border-top text-center">
                                    <a href="#" class="text-primary text-decoration-none">Ver todas</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Usuario --}}
                    <div class="dropdown">
                        <a class="d-flex align-items-center text-decoration-none" id="userMenuBtn" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                            <div class="user-avatar rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:linear-gradient(135deg,#1e40af,#3b82f6);color:#fff;font-weight:700;">
                                {{ strtoupper(substr(auth()->user()->name ?? (session('usuario_logged') ?? 'U'), 0, 1)) }}
                            </div>
                            <div class="ms-2 d-none d-md-block text-start">
                                <div style="font-weight:700; font-size:.95rem; color:#1f2937;">{{ auth()->user()->name ?? 'Usuario' }}</div>
                                <div style="font-size:.75rem; color:#6b7280;">{{ auth()->user()->tipousuario ?? auth()->user()->role ?? 'Sistema' }}</div>
                            </div>
                            <i class="fas fa-chevron-down ms-2 text-muted"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuBtn">
                            <li><a class="dropdown-item" href="{{ route('profile.show') ?? '#' }}"><i class="fas fa-user me-2"></i>Mi Perfil</a></li>
                            <li><a class="dropdown-item" href="{{ route('settings') ?? '#' }}"><i class="fas fa-cog me-2"></i>Configuración</a></li>
                            <li><a class="dropdown-item" href="{{ route('help') ?? '#' }}"><i class="fas fa-question-circle me-2"></i>Ayuda</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </header>

        {{-- Content area --}}
        <div class="container-fluid py-3">
            {{-- Alert flashes --}}
            @if(session('status')) <div class="alert alert-info">{{ session('status') }}</div> @endif
            @yield('content')
        </div>
    </main>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    (function(){
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // --------- Notifications: cargar y pintar ----------
        const notifBadge = document.getElementById('notif-badge');
        const notifList  = document.getElementById('notif-list');
        const notifLoading = document.getElementById('notif-loading');
        const notificationsBtn = document.getElementById('notificationsBtn');

        async function loadNotifications() {
            try {
                const res = await fetch("#", {
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                if (!res.ok) throw new Error('Error fetching notifications');
                const json = await res.json();

                // json expected: { count: number, items: [{id, title, text, url, type, read_at}] }
                renderNotifications(json);
            } catch (err) {
                console.error(err);
                if (notifLoading) notifLoading.innerHTML = '<div class="text-danger small py-2">No se pudieron cargar notificaciones</div>';
            }
        }

        function renderNotifications(data) {
            if (!notifList) return;
            notifList.innerHTML = '';

            const count = (data && data.count) ? data.count : 0;
            if (count > 0) {
                notifBadge.style.display = 'inline-block';
                notifBadge.textContent = count;
            } else {
                notifBadge.style.display = 'none';
            }

            const items = (data && data.items && data.items.length) ? data.items : [];
            if (items.length === 0) {
                notifList.innerHTML = '<div class="list-group-item text-center py-4 text-muted">Sin notificaciones</div>';
                return;
            }

            items.forEach(n => {
                const a = document.createElement('a');
                a.href = n.url || '#';
                a.className = 'list-group-item list-group-item-action d-flex align-items-start';
                a.innerHTML = `
                    <div class="me-3">
                        <i class="fas fa-${n.icon || 'info-circle'} fa-lg text-${n.type === 'danger' ? 'danger' : (n.type === 'warning' ? 'warning' : 'primary')}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div style="font-weight:700;">${escapeHtml(n.title)}</div>
                        <small class="text-muted">${escapeHtml(n.message)}</small>
                        <div class="text-muted small mt-1">${n.time || ''}</div>
                    </div>
                `;
                notifList.appendChild(a);
            });
        }

        // Mark all as read (calls API)
        document.getElementById('markAllRead')?.addEventListener('click', async function(e) {
            e.preventDefault();
            try {
                const res = await fetch("#", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({})
                });
                if (res.ok) {
                    notifBadge.style.display = 'none';
                    loadNotifications();
                } else {
                    Swal.fire('Error', 'No se pudo marcar como leídas', 'error');
                }
            } catch (err) { console.error(err); Swal.fire('Error','Error de conexión','error'); }
        });

        // Lazy load notifications when dropdown shown
        notificationsBtn?.addEventListener('click', function(){
            // only load once per session or when reopened
            loadNotifications();
        });

        // Escape helper
        function escapeHtml(unsafe) {
            if (!unsafe) return '';
            return unsafe.replace(/[&<>"'`=\/]/g, function (s) {
                return ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;',
                    '/': '&#x2F;',
                    '`': '&#x60;',
                    '=': '&#x3D;'
                })[s];
            });
        }

        // --------- Global Search with debounce & AJAX ----------
        const searchInput = document.getElementById('globalSearch');
        const searchResults = document.getElementById('searchResults');
        let debounceTimer;

        async function performSearch(q) {
            if (!q || q.length < 2) { searchResults.style.display = 'none'; searchResults.innerHTML = ''; return; }
            searchResults.style.display = 'block';
            searchResults.innerHTML = '<div class="list-group-item text-center py-2"><small class="text-muted">Buscando...</small></div>';
            try {
                const res = await fetch("#" + encodeURIComponent(q), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                if (!res.ok) throw new Error('search error');
                const json = await res.json();
                renderSearchResults(json);
            } catch (err) {
                searchResults.innerHTML = '<div class="list-group-item text-danger">Error al buscar</div>';
                console.error(err);
            }
        }

        function renderSearchResults(json) {
            searchResults.innerHTML = '';
            if (!json || (!json.products && !json.clients && !json.docs)) {
                searchResults.innerHTML = '<div class="list-group-item text-muted">No hay resultados</div>';
                return;
            }
            const fragment = document.createDocumentFragment();

            if (json.products && json.products.length) {
                const header = document.createElement('div');
                header.className = 'px-3 pt-2 small text-uppercase text-muted';
                header.textContent = 'Productos';
                fragment.appendChild(header);
                json.products.slice(0,6).forEach(p => {
                    const a = document.createElement('a');
                    a.href = p.url || '#';
                    a.className = 'list-group-item list-group-item-action';
                    a.innerHTML = `<strong>${escapeHtml(p.code || p.name)}</strong><div class="small text-muted">${escapeHtml(p.name || '')}</div>`;
                    fragment.appendChild(a);
                });
            }

            if (json.clients && json.clients.length) {
                const header = document.createElement('div');
                header.className = 'px-3 pt-2 small text-uppercase text-muted';
                header.textContent = 'Clientes';
                fragment.appendChild(header);
                json.clients.slice(0,6).forEach(c => {
                    const a = document.createElement('a');
                    a.href = c.url || '#';
                    a.className = 'list-group-item list-group-item-action';
                    a.innerHTML = `<strong>${escapeHtml(c.name)}</strong><div class="small text-muted">${escapeHtml(c.document || '')}</div>`;
                    fragment.appendChild(a);
                });
            }

            if (json.docs && json.docs.length) {
                const header = document.createElement('div');
                header.className = 'px-3 pt-2 small text-uppercase text-muted';
                header.textContent = 'Documentos';
                fragment.appendChild(header);
                json.docs.slice(0,6).forEach(d => {
                    const a = document.createElement('a');
                    a.href = d.url || '#';
                    a.className = 'list-group-item list-group-item-action';
                    a.innerHTML = `<strong>${escapeHtml(d.number)}</strong><div class="small text-muted">${escapeHtml(d.type || '')} - S/ ${Number(d.total||0).toLocaleString('es-PE',{minimumFractionDigits:2})}</div>`;
                    fragment.appendChild(a);
                });
            }

            searchResults.appendChild(fragment);
        }

        searchInput?.addEventListener('input', function(e){
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => performSearch(e.target.value.trim()), 250);
        });

        // Close search dropdown clicking outside
        document.addEventListener('click', function(ev){
            if (!document.getElementById('topbar')?.contains(ev.target)) {
                // do nothing; keep it simple
            }
            if (searchResults && !searchResults.contains(ev.target) && eNotChild(ev.target, searchInput)) {
                // hide results if clicked outside input or results
                if (ev.target !== searchInput) searchResults.style.display = 'none';
            }
        });

        function eNotChild(target, parentEl) {
            if (!parentEl) return true;
            return !(parentEl === target || parentEl.contains(target));
        }

        // Sidebar toggle for mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function(){
            document.getElementById('sidebar')?.classList.toggle('active');
        });

        // Inicializar carga de notificaciones ligera (no bloquear render)
        document.addEventListener('DOMContentLoaded', function(){
            // load only badge count first (fast endpoint)
            fetch("#", { headers: { 'Accept': 'application/json' }, credentials:'same-origin' })
                .then(r => r.json())
                .then(j => {
                    if (j && j.count && j.count > 0) {
                        notifBadge.style.display = 'inline-block';
                        notifBadge.textContent = j.count;
                    }
                }).catch(()=>{});
        });

    })();
    </script>

    @stack('scripts')
</body>
</html>