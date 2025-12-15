// ventas_status_edit.js
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.action-edit');
    if (!buttons.length) return;

    let cachedStates = null;

    async function fetchStates() {
        if (cachedStates) return cachedStates;
        try {
            const base = (window.APP && window.APP.base) ? window.APP.base : '';
            const res = await fetch(base + 'index.php?r=/admin/estados_list', { method: 'GET', credentials: 'same-origin' });
            const json = await res.json();
            if (json && json.success && Array.isArray(json.data)) {
                cachedStates = json.data;
                return cachedStates;
            }
        } catch (e) {
            console.error('Error fetching estados', e);
        }
        // fallback
        cachedStates = [
            { id_estado: 1, estado: 'Pendiente' },
            { id_estado: 2, estado: 'En Proceso' },
            { id_estado: 3, estado: 'Enviado' },
            { id_estado: 4, estado: 'Entregado' },
            { id_estado: 5, estado: 'Cancelado' },
            { id_estado: 6, estado: 'Devuelto' }
        ];
        return cachedStates;
    }

    function createEditor(currentEstadoId, states) {
        const wrapper = document.createElement('div');
        wrapper.className = 'status-editor-inline';
        const select = document.createElement('select');
        select.className = 'form-select form-select-sm';

        states.forEach(s => {
            const val = s.id_estado ?? s.id ?? s[Object.keys(s)[0]];
            const label = s.estado ?? s.label ?? s[Object.keys(s)[1]] ?? String(val);
            const o = document.createElement('option');
            o.value = val;
            o.textContent = label;
            if (String(val) === String(currentEstadoId)) o.selected = true;
            select.appendChild(o);
        });

        const save = document.createElement('button'); save.className = 'btn btn-sm btn-primary ms-2'; save.textContent = 'Guardar';
        const cancel = document.createElement('button'); cancel.className = 'btn btn-sm btn-secondary ms-1'; cancel.textContent = 'Cancelar';

        wrapper.appendChild(select); wrapper.appendChild(save); wrapper.appendChild(cancel);
        return { wrapper, select, save, cancel };
    }

    function showToast(message, type = 'success', timeout = 3000) {
        let container = document.querySelector('.app-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'app-toast-container';
            document.body.appendChild(container);
        }
        const t = document.createElement('div');
        t.className = 'app-toast ' + (type === 'error' ? 'error' : 'success');
        t.textContent = message;
        container.appendChild(t);
        setTimeout(() => { t.style.transition = 'opacity 300ms'; t.style.opacity = '0'; setTimeout(() => t.remove(), 350); }, timeout);
    }

    // Modal builder
    function buildModal(states, currentEstadoId) {
        const overlay = document.createElement('div');
        overlay.className = 'vs-modal-overlay';
        const dialog = document.createElement('div');
        dialog.className = 'vs-modal-dialog';

        const title = document.createElement('h4'); title.textContent = 'Cambiar estado del pedido';
        const select = document.createElement('select'); select.className = 'form-select';
        states.forEach(s => {
            const val = s.id_estado ?? s.id ?? s[Object.keys(s)[0]];
            const label = s.estado ?? s.label ?? s[Object.keys(s)[1]] ?? String(val);
            const o = document.createElement('option'); o.value = val; o.textContent = label; if (String(val) === String(currentEstadoId)) o.selected = true; select.appendChild(o);
        });

        const actions = document.createElement('div'); actions.className = 'vs-modal-actions';
        const btnSave = document.createElement('button'); btnSave.className = 'btn btn-primary'; btnSave.textContent = 'Guardar';
        const btnCancel = document.createElement('button'); btnCancel.className = 'btn btn-secondary ms-2'; btnCancel.textContent = 'Cancelar';
        actions.appendChild(btnSave); actions.appendChild(btnCancel);

        dialog.appendChild(title); dialog.appendChild(select); dialog.appendChild(actions);
        overlay.appendChild(dialog);

        // basic styles
        const style = document.createElement('style'); style.textContent = `
            .vs-modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.35); display:flex; align-items:center; justify-content:center; z-index:1200; }
            .vs-modal-dialog { background:#fff; padding:18px; border-radius:8px; width:320px; box-shadow:0 6px 20px rgba(0,0,0,0.2); }
            .vs-modal-dialog h4 { margin:0 0 10px 0; font-size:1.1rem }
            .vs-modal-actions { margin-top:12px; text-align:right }
            .vs-modal-dialog .form-select { width:100%; }
        `;
        overlay.appendChild(style);

        return { overlay, select, btnSave, btnCancel };
    }

    buttons.forEach(btn => {
        btn.addEventListener('click', async function () {
            if (this._editing) return; this._editing = true;
            const id = this.dataset.id;
            const curr = this.dataset.estado || 1;
            const states = (window.APP && Array.isArray(window.APP.estados) && window.APP.estados.length) ? window.APP.estados : await fetchStates();

            const modal = buildModal(states, curr);
            document.body.appendChild(modal.overlay);

            modal.btnCancel.addEventListener('click', function (e) { e.preventDefault(); modal.overlay.remove(); btn._editing = false; });

            modal.btnSave.addEventListener('click', function (e) {
                e.preventDefault();
                const newEstado = modal.select.value;
                const fd = new FormData(); fd.append('id', id); fd.append('estado', newEstado);
                if (window.APP && window.APP.csrf) fd.append('csrf', window.APP.csrf);
                // Resolve post URL robustly using current pathname to avoid duplicated segments
                function resolvePostUrl() {
                    // If APP.base looks correct and starts with '/', use it
                    try {
                        const origin = window.location.origin;
                        // Try to find the directory that contains index.php in the current path
                        const p = window.location.pathname;
                        const idx = p.indexOf('index.php');
                        const root = idx !== -1 ? p.slice(0, idx) : p.replace(/\/$/, '') + '/';
                        return origin + root + 'index.php?r=/admin/pedido_update_status';
                    } catch (e) {
                        return (window.APP && window.APP.base ? window.APP.base : '') + 'index.php?r=/admin/pedido_update_status';
                    }
                }

                // Prefer server-provided absolute API URL if available
                const postUrl = (window.APP && window.APP.apiPedidoUpdateUrl) ? window.APP.apiPedidoUpdateUrl : resolvePostUrl();
                console.debug('ventas_status_edit: POST URL =', postUrl);

                fetch(postUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(async res => {
                        const txt = await res.text();
                        // Log status and body for debugging
                        console.debug('ventas_status_edit: response status', res.status, res.statusText);
                        console.debug('ventas_status_edit: response body', txt);
                        if (!res.ok) {
                            // show server error with status
                            showToast('Error servidor: ' + res.status + ' ' + res.statusText, 'error');
                            throw new Error('HTTP ' + res.status);
                        }
                        // Try parse JSON
                        try {
                            return JSON.parse(txt);
                        } catch (err) {
                            console.error('ventas_status_edit: JSON parse error', err);
                            throw err;
                        }
                    })
                    .then(data => {
                        if (data && data.success) {
                            const row = btn.closest('tr');
                            const badge = row.querySelector('.estado-badge');
                            if (badge) {
                                badge.textContent = data.estado_nombre || badge.textContent;
                                badge.className = 'estado-badge estado-' + (data.estado_nombre || '').toLowerCase().replace(/\s+/g, '-');
                            }
                            btn.dataset.estado = newEstado;
                            modal.overlay.remove();
                            showToast('Estado actualizado', 'success');
                        } else {
                            showToast('Error: ' + (data.message || 'respuesta inesperada'), 'error');
                        }
                        btn._editing = false;
                    }).catch(err => {
                        console.error('ventas_status_edit: fetch error', err);
                        showToast('Error de red o respuesta inválida (ver consola)', 'error');
                        btn._editing = false;
                    });
            });
        });
    });
});
