/* global BP_CONFIG */
(function () {
    'use strict';

    var cfg     = (typeof BP_CONFIG !== 'undefined') ? BP_CONFIG : {};
    var REST_URL = cfg.restUrl  || '';
    var RADIO_KM = cfg.radioKm  || 2;

    var iconChurch = [
        '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">',
        '<path d="M12 2L13.2 5.2H16L13.7 7.1L14.5 10L12 8.3L9.5 10L10.3 7.1L8 5.2H10.8L12 2Z" fill="currentColor"/>',
        '<path d="M11 8H13V11H16V22H8V11H11V8Z" fill="currentColor"/>',
        '<path d="M10 15H14V22H10V15Z" fill="#ffffff"/>',
        '<path d="M5 12L12 7L19 12V14H5V12Z" fill="currentColor"/>',
        '</svg>'
    ].join('');

    // ── Helpers ─────────────────────────────────────────────────────────────────

    function normalizarTexto(valor) {
        return (valor || '')
            .toString()
            .normalize('NFD')
            .replace(/[̀-ͯ]/g, '')
            .toLowerCase()
            .trim();
    }

    function escapeHtml(texto) {
        return (texto || '')
            .toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function calcularDistancia(lat1, lon1, lat2, lon2) {
        var R    = 6371;
        var dLat = (lat2 - lat1) * Math.PI / 180;
        var dLon = (lon2 - lon1) * Math.PI / 180;
        var a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function construirMapa(lat, lon, direccion) {
        if (lat !== null && lon !== null) {
            return 'https://www.google.com/maps?q=' + encodeURIComponent(lat + ',' + lon);
        }
        if (direccion) {
            return 'https://www.google.com/maps?q=' + encodeURIComponent(direccion);
        }
        return '';
    }

    function obtenerUbicacion() {
        return new Promise(function (resolve) {
            if (!navigator.geolocation) { resolve(null); return; }
            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    resolve({ lat: pos.coords.latitude, lon: pos.coords.longitude });
                },
                function () { resolve(null); },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 300000 }
            );
        });
    }

    // ── Render HTML ─────────────────────────────────────────────────────────────

    function renderApp(app) {
        var logoHtml = '';
        if (cfg.logoUrl) {
            var logoImg = '<img class="bp-logo" src="' + escapeHtml(cfg.logoUrl) + '" alt="' + escapeHtml(cfg.orgName) + '">';
            logoHtml = cfg.logoLink
                ? '<a href="' + escapeHtml(cfg.logoLink) + '" target="_blank" rel="noopener noreferrer">' + logoImg + '</a>'
                : logoImg;
        }

        var footerIconsHtml = '';
        if (cfg.footerAddress) footerIconsHtml += '<div class="bp-footer-line"><span>📍</span><span>' + escapeHtml(cfg.footerAddress) + '</span></div>';
        if (cfg.footerCity)    footerIconsHtml += '<div class="bp-footer-line"><span>🏙️</span><span>' + escapeHtml(cfg.footerCity) + '</span></div>';
        if (cfg.footerEmail)   footerIconsHtml += '<div class="bp-footer-line"><span>✉️</span><span><a href="mailto:' + escapeHtml(cfg.footerEmail) + '">' + escapeHtml(cfg.footerEmail) + '</a></span></div>';

        app.innerHTML =
            '<div class="bp-wrap">' +
                '<div class="bp-header">' +
                    '<div class="bp-header-top">' +
                        (logoHtml ? '<div class="bp-logo-wrap">' + logoHtml + '</div>' : '') +
                        '<div class="bp-header-copy">' +
                            '<h2>' + escapeHtml(cfg.headerTitle || 'Guía de parroquias') + '</h2>' +
                            (cfg.orgName ? '<h4><strong>' + escapeHtml(cfg.orgName) + '</strong></h4>' : '') +
                            (cfg.headerSubtitle ? '<p>' + escapeHtml(cfg.headerSubtitle) + '</p>' : '') +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="bp-body">' +
                    '<div class="bp-search-panel">' +
                        '<div class="bp-searchbar">' +
                            '<div class="bp-input-wrap">' +
                                '<div class="bp-input-box">' +
                                    '<input id="bp-input" class="bp-input" type="text" placeholder="Buscar por parroquia o localidad...">' +
                                '</div>' +
                            '</div>' +
                            '<button id="bp-btn-search" class="bp-btn">Buscar</button>' +
                            '<button id="bp-btn-near" class="bp-btn-secondary">Cerca de mí</button>' +
                            '<button id="bp-btn-clear" class="bp-btn-clear">Limpiar</button>' +
                        '</div>' +
                        '<div id="bp-status" class="bp-status">Cargando datos...</div>' +
                    '</div>' +
                    '<div id="bp-results" class="bp-results">' +
                        '<div class="bp-loading">Cargando...</div>' +
                    '</div>' +
                '</div>' +
                (cfg.footerOrg || footerIconsHtml
                    ? '<div class="bp-footer">' +
                        '<div class="bp-footer-grid">' +
                            '<div>' +
                                (cfg.footerOrg   ? '<div class="bp-footer-title">' + escapeHtml(cfg.footerOrg) + '</div>' : '') +
                                (cfg.footerLine1 ? '<div class="bp-footer-line">' + escapeHtml(cfg.footerLine1) + '</div>' : '') +
                            '</div>' +
                            '<div class="bp-footer-icons">' + footerIconsHtml + '</div>' +
                        '</div>' +
                      '</div>'
                    : '') +
            '</div>';
    }

    // ── Resultados ──────────────────────────────────────────────────────────────

    function pintarResultados(lista, mensaje, ubicacionUsuario) {
        var status  = document.getElementById('bp-status');
        var results = document.getElementById('bp-results');

        if (!lista || !lista.length) {
            status.textContent  = mensaje || 'No se han encontrado resultados.';
            results.innerHTML   = '<div class="bp-empty">No se han encontrado resultados.</div>';
            return;
        }

        status.textContent = mensaje || (lista.length + ' resultado(s) encontrado(s).');

        var iconChevron =
            '<svg class="bp-chevron-icon" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">' +
            '<path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
            '</svg>';

        results.innerHTML = lista.map(function (item) {
            var mapa      = construirMapa(item.latitud, item.longitud, item.direccion);
            var distancia = (ubicacionUsuario && item.latitud !== null && item.longitud !== null)
                ? calcularDistancia(ubicacionUsuario.lat, ubicacionUsuario.lon, item.latitud, item.longitud)
                : null;

            return (
                '<div class="bp-card">' +
                    '<div class="bp-card-top bp-card-header" role="button" tabindex="0" aria-expanded="false">' +
                        '<div class="bp-church-icon">' + iconChurch + '</div>' +
                        '<div style="flex:1;">' +
                            '<h3>' + escapeHtml(item.parroquia || 'Parroquia') + '</h3>' +
                            '<div class="bp-tags">' +
                                (item.localidad  ? '<span class="bp-tag">📍 ' + escapeHtml(item.localidad) + '</span>' : '') +
                                (item.upSector   ? '<span class="bp-tag">' + escapeHtml(item.upSector) + '</span>' : '') +
                                (distancia !== null ? '<span class="bp-tag bp-tag-distance">' + distancia.toFixed(2) + ' km</span>' : '') +
                            '</div>' +
                        '</div>' +
                        '<div class="bp-card-chevron">' + iconChevron + '</div>' +
                    '</div>' +

                    '<div class="bp-card-body">' +
                        '<div class="bp-info-card">' +
                            '<div class="bp-info-title">Contacto</div>' +
                            '<div class="bp-meta">' +
                                (item.direccion ? '<div class="bp-meta-row"><strong>Dirección:</strong><span>' + escapeHtml(item.direccion) + '</span></div>' : '') +
                                (item.cp        ? '<div class="bp-meta-row"><strong>C.P.:</strong><span>' + escapeHtml(item.cp) + '</span></div>' : '') +
                                (item.telefono  ? '<div class="bp-meta-row"><strong>Teléfono:</strong><span>' + escapeHtml(item.telefono) + '</span></div>' : '') +
                                (item.email     ? '<div class="bp-meta-row"><strong>Email:</strong><span><a href="mailto:' + escapeHtml(item.email) + '">' + escapeHtml(item.email) + '</a></span></div>' : '') +
                                (item.parroco   ? '<div class="bp-meta-row"><strong>Párroco:</strong><span>' + escapeHtml(item.parroco) + '</span></div>' : '') +
                            '</div>' +
                        '</div>' +

                        '<div class="bp-schedules">' +
                            '<div class="bp-schedules-title">Horarios de misa</div>' +
                            '<div class="bp-schedule-row"><div class="bp-schedule-label">Invierno</div><div>' + escapeHtml(item.horarioInvierno || 'No disponible') + '</div></div>' +
                            '<div class="bp-schedule-row"><div class="bp-schedule-label">Verano</div><div>'   + escapeHtml(item.horarioVerano   || 'No disponible') + '</div></div>' +
                        '</div>' +

                        (mapa ? '<div class="bp-actions"><a class="bp-link" href="' + mapa + '" target="_blank" rel="noopener">Ver en Google Maps</a></div>' : '') +
                    '</div>' +
                '</div>'
            );
        }).join('');

        // Lógica de colapso: clic o Enter/Espacio en la cabecera
        results.querySelectorAll('.bp-card-header').forEach(function (header) {
            function toggle() {
                var card     = header.closest('.bp-card');
                var expanded = card.classList.toggle('bp-card--expanded');
                header.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            }
            header.addEventListener('click', toggle);
            header.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(); }
            });
        });
    }

    function mostrarInicio() {
        var results = document.getElementById('bp-results');
        if (!results) return;
        results.innerHTML =
            '<div class="bp-start">' +
                iconChurch +
                '<div style="margin-top:6px;">Busca una parroquia o localidad, o usa tu ubicación para ver iglesias en un radio de ' + RADIO_KM + ' km.</div>' +
            '</div>';
    }

    // ── Carga de datos ──────────────────────────────────────────────────────────

    var datos            = [];
    var ubicacionUsuario = null;

    async function cargarDatos() {
        var status  = document.getElementById('bp-status');
        var results = document.getElementById('bp-results');

        try {
            var response = await fetch(REST_URL);
            if (!response.ok) throw new Error('HTTP ' + response.status);
            datos = await response.json();
            status.textContent = 'Escribe una localidad o parroquia y pulsa "Buscar", o usa "Cerca de mí".';
            mostrarInicio();
        } catch (error) {
            status.textContent  = 'Error al cargar los datos.';
            results.innerHTML   = '<div class="bp-error">No se pudieron cargar los datos. Recarga la página o contacta con el administrador.</div>';
        }
    }

    // ── Búsqueda ─────────────────────────────────────────────────────────────────

    function buscar(cercaDeMi) {
        var input  = document.getElementById('bp-input');
        var status = document.getElementById('bp-status');
        var termino = normalizarTexto(input ? input.value : '');

        if (!termino && !cercaDeMi) {
            mostrarInicio();
            return;
        }

        var filtrados = datos.filter(function (item) {
            var textoBusqueda = [item.parroquia, item.localidad].map(normalizarTexto).join(' ');
            return termino ? textoBusqueda.includes(termino) : true;
        });

        if (cercaDeMi) {
            if (!ubicacionUsuario) {
                pintarResultados([], 'No se pudo obtener tu ubicación.', null);
                return;
            }
            filtrados = filtrados
                .map(function (item) {
                    var dist = (item.latitud !== null && item.longitud !== null)
                        ? calcularDistancia(ubicacionUsuario.lat, ubicacionUsuario.lon, item.latitud, item.longitud)
                        : null;
                    return Object.assign({}, item, { _dist: dist });
                })
                .filter(function (item) { return item._dist !== null && item._dist <= RADIO_KM; })
                .sort(function (a, b) { return a._dist - b._dist; });
        }

        var mensaje = cercaDeMi
            ? (filtrados.length ? filtrados.length + ' resultado(s) en un radio de ' + RADIO_KM + ' km.' : 'No hay resultados en un radio de ' + RADIO_KM + ' km.')
            : (filtrados.length ? filtrados.length + ' resultado(s) encontrado(s).' : 'No se han encontrado resultados.');

        pintarResultados(filtrados, mensaje, ubicacionUsuario);
    }

    // ── Inicialización ───────────────────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function () {
        var app = document.getElementById('bp-app');
        if (!app || !REST_URL) return;

        renderApp(app);

        var input     = document.getElementById('bp-input');
        var btnSearch = document.getElementById('bp-btn-search');
        var btnNear   = document.getElementById('bp-btn-near');
        var btnClear  = document.getElementById('bp-btn-clear');
        var status    = document.getElementById('bp-status');

        btnSearch.addEventListener('click', function () { buscar(false); });

        btnNear.addEventListener('click', async function () {
            status.textContent = 'Obteniendo tu ubicación...';
            var ub = await obtenerUbicacion();
            if (!ub) {
                status.textContent = 'No se pudo obtener tu ubicación. Activa el permiso en el navegador.';
                return;
            }
            ubicacionUsuario = ub;
            buscar(true);
        });

        btnClear.addEventListener('click', function () {
            if (input) input.value = '';
            status.textContent = 'Escribe una localidad o parroquia y pulsa "Buscar", o usa "Cerca de mí".';
            mostrarInicio();
        });

        if (input) {
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') buscar(false);
            });
        }

        cargarDatos();
    });
})();
