(function () {
    'use strict';

    // Mapeo: campo ACF → posibles nombres de columna en el Excel
    var MAPA = {
        parroquia:              ['Parroquia /Parrokia', 'PARROQUIA', 'Parroquia', 'Parrokia'],
        localidad:              ['Localidad/Herria', 'LOCALIDAD', 'Localidad', 'Herria'],
        direccion:              ['Helbidea/dirección', 'Helbidea/Direccion', 'Dirección', 'Direccion',
                                 'HELBIDEA/DIRECCIÓN', 'DIRECCION', 'DIRECCIÓN'],
        cp:                     ['C.P.', 'CP', 'CÓDIGO POSTAL', 'CODIGO POSTAL', 'Codigo Postal'],
        telefono:               ['TELÉFONO', 'TELEFONO', 'Teléfono', 'Telefono'],
        email:                  ['EMAIL', 'E-MAIL', 'Email'],
        parroco:                ['PÁRROCO', 'PARROCO', 'Párroco', 'Parroco'],
        up_sector:              ['UP / SECTOR', 'UP/SECTOR', 'UP', 'SECTOR'],
        horario_misas_invierno: ['Horario misas invierno', 'HORARIO MISAS INVIERNO'],
        horario_misas_verano:   ['Horario misas verano',  'HORARIO MISAS VERANO'],
        latitud:                ['LATITUD', 'Latitud'],
        longitud:               ['LONGITUD', 'Longitud']
    };

    function valorCampo(fila, posibles) {
        for (var i = 0; i < posibles.length; i++) {
            var k = posibles[i];
            if (Object.prototype.hasOwnProperty.call(fila, k) &&
                fila[k] !== null && fila[k] !== undefined &&
                String(fila[k]).trim() !== '') {
                return String(fila[k]).trim();
            }
        }
        return '';
    }

    function logMsg(log, msg, tipo) {
        var colores = { ok: '#0a6b2e', error: '#b00000', info: '#1e1e1e' };
        var p = document.createElement('p');
        p.style.color  = colores[tipo] || '#1e1e1e';
        p.style.margin = '3px 0';
        p.textContent  = msg;
        log.appendChild(p);
        log.scrollTop  = log.scrollHeight;
    }

    function postAjax(action, data) {
        var params = new URLSearchParams(data);
        params.append('action', action);
        params.append('nonce',  BP_IMPORT.nonce);
        return fetch(BP_IMPORT.ajaxurl, {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    params.toString()
        }).then(function (r) { return r.json(); });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var fileInput = document.getElementById('bp-import-file');
        var btn       = document.getElementById('bp-import-btn');
        var log       = document.getElementById('bp-import-log');
        var delCheck  = document.getElementById('bp-import-delete');

        if (!fileInput || !btn) return;

        fileInput.addEventListener('change', function () {
            btn.disabled = !fileInput.files.length;
        });

        btn.addEventListener('click', async function () {
            if (!fileInput.files.length) return;

            btn.disabled    = true;
            log.innerHTML   = '';
            log.style.display = 'block';

            try {
                var buffer = await fileInput.files[0].arrayBuffer();
                var wb     = XLSX.read(buffer, { type: 'array', raw: false });
                var ws     = wb.Sheets[wb.SheetNames[0]];
                var json   = XLSX.utils.sheet_to_json(ws, { defval: '' });

                logMsg(log, 'Filas leídas del Excel: ' + json.length, 'info');

                var filas = json.map(function (fila) {
                    var row = {};
                    for (var campo in MAPA) {
                        row[campo] = valorCampo(fila, MAPA[campo]);
                    }
                    return row;
                }).filter(function (f) {
                    return f.parroquia || f.localidad;
                });

                logMsg(log, 'Filas válidas a importar: ' + filas.length, 'info');

                if (delCheck.checked) {
                    logMsg(log, 'Eliminando parroquias existentes...', 'info');
                    var del = await postAjax('bp_delete_all', {});
                    logMsg(
                        log,
                        del.success
                            ? 'Eliminadas: ' + del.data + ' parroquias.'
                            : 'Error al eliminar: ' + (del.data || '?'),
                        del.success ? 'info' : 'error'
                    );
                }

                var BATCH   = 50;
                var creadas = 0;
                var errores = 0;
                var total   = Math.ceil(filas.length / BATCH);

                for (var i = 0; i < filas.length; i += BATCH) {
                    var lote = filas.slice(i, i + BATCH);
                    var res  = await postAjax('bp_import_batch', { data: JSON.stringify(lote) });

                    if (res.success) {
                        creadas += res.data.creadas;
                        errores += res.data.errores;
                        logMsg(
                            log,
                            'Lote ' + (Math.floor(i / BATCH) + 1) + '/' + total +
                            ' — creadas: ' + creadas + ', errores: ' + errores,
                            'info'
                        );
                    } else {
                        logMsg(log, 'Error en lote: ' + (res.data || '?'), 'error');
                        break;
                    }
                }

                logMsg(
                    log,
                    '✓ Importación completada — Creadas: ' + creadas + ' | Errores: ' + errores,
                    'ok'
                );

            } catch (e) {
                logMsg(log, 'Error inesperado: ' + e.message, 'error');
            }

            btn.disabled = false;
        });
    });
})();
