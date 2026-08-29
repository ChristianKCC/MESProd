/**
 * SearchableSelect.js — Dropdown con búsqueda integrada
 *
 * Uso básico:
 *   import { SearchableSelect } from './components/SearchableSelect.js';
 *
 *   const ss = new SearchableSelect({
 *       container:   document.getElementById('mi-contenedor'),
 *       placeholder: 'Selecciona una sección...',
 *       items:       [{ id: 1, label: 'Sellado', sub: 'SEC-01' }],
 *       onChange:    (item) => console.log(item),
 *   });
 *
 *   // API pública:
 *   ss.setValue({ id: 1, label: 'Sellado' });
 *   ss.clear();
 *   ss.setItems(nuevosItems);
 *   ss.setDisabled(true);
 *   ss.getValue();   // → item seleccionado o null
 *   ss.destroy();    // limpia el DOM y los listeners
 */

export class SearchableSelect {

    // ── Estilos base inyectados una sola vez en el <head> ──────
    static #stylesInjected = false;

    static #injectStyles() {
        if (SearchableSelect.#stylesInjected) return;
        SearchableSelect.#stylesInjected = true;

        const style = document.createElement('style');
        style.textContent = `
            .ss-wrap { position: relative; user-select: none; }

            .ss-trigger {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 7px 10px;
                font-size: 13px;
                border: 1px solid #C8CDD6;
                border-radius: 8px;
                background: #fff;
                color: #1A1D23;
                cursor: pointer;
                transition: border-color .15s, box-shadow .15s;
                min-height: 34px;
                font-family: inherit;
            }
            .ss-trigger:focus {
                outline: none;
                border-color: #2563EB;
                box-shadow: 0 0 0 3px #2563EB18;
            }
            .ss-trigger.ss-open {
                border-color: #2563EB;
                box-shadow: 0 0 0 3px #2563EB18;
                border-radius: 8px 8px 0 0;
            }
            .ss-trigger.ss-disabled {
                background: #F8F9FB;
                color: #9AA0AD;
                cursor: not-allowed;
                pointer-events: none;
            }
            .ss-trigger .ss-placeholder { color: #9AA0AD; }
            .ss-trigger .ss-arrow {
                font-size: 11px;
                color: #9AA0AD;
                margin-left: 6px;
                flex-shrink: 0;
                transition: transform .15s;
            }
            .ss-trigger.ss-open .ss-arrow { transform: rotate(180deg); }

            .ss-dropdown {
                position: absolute;
                top: 100%;
                left: 0; right: 0;
                background: #fff;
                border: 1px solid #2563EB;
                border-top: none;
                border-radius: 0 0 8px 8px;
                box-shadow: 0 6px 20px rgba(0,0,0,.10);
                z-index: 500;
                display: none;
            }
            .ss-dropdown.ss-open { display: block; }

            .ss-search-wrap {
                padding: 8px;
                border-bottom: 1px solid #E2E5EA;
            }
            .ss-search-input {
                width: 100%;
                padding: 6px 9px;
                font-size: 12px;
                border: 1px solid #C8CDD6;
                border-radius: 6px;
                outline: none;
                background: #F8F9FB;
                color: #1A1D23;
                font-family: inherit;
                transition: border-color .15s;
            }
            .ss-search-input:focus { border-color: #2563EB; }

            .ss-list { max-height: 190px; overflow-y: auto; }

            .ss-item {
                padding: 8px 12px;
                font-size: 13px;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 8px;
                border-bottom: 1px solid #E2E5EA;
                transition: background .1s;
            }
            .ss-item:last-child { border-bottom: none; }
            .ss-item:hover,
            .ss-item.ss-selected { background: #EEF3FD; color: #1D54D4; }
            .ss-item .ss-check { font-size: 11px; width: 13px; flex-shrink: 0; }
            .ss-item .ss-label { flex: 1; font-weight: 500; }
            .ss-item .ss-sub {
                font-size: 11px;
                color: #9AA0AD;
                background: #F8F9FB;
                padding: 1px 6px;
                border-radius: 4px;
                white-space: nowrap;
            }
            .ss-item:hover .ss-sub,
            .ss-item.ss-selected .ss-sub { color: #2563EB; background: #fff; }

            .ss-empty {
                padding: 12px;
                font-size: 12px;
                color: #9AA0AD;
                text-align: center;
            }
        `;
        document.head.appendChild(style);
    }

    // ── Constructor ────────────────────────────────────────────
    /**
     * @param {Object}      options
     * @param {HTMLElement} options.container   — Elemento donde se monta
     * @param {string}      options.placeholder — Texto cuando no hay selección
     * @param {Array}       options.items       — [{ id, label, sub? }]
     * @param {Function}    options.onChange    — Callback cuando cambia (item | null)
     */
    constructor({ container, placeholder = 'Selecciona...', items = [], onChange = null }) {
        SearchableSelect.#injectStyles();

        this._container   = container;
        this._placeholder = placeholder;
        this._items       = items;
        this._onChange    = onChange;
        this._selected    = null;
        this._isOpen      = false;

        this._render();
        this._bindEvents();
    }

    // ── Render del DOM ─────────────────────────────────────────
    _render() {
        this._container.innerHTML = '';
        this._container.className = 'ss-wrap';

        // Trigger (parece un <select> normal)
        this._trigger = document.createElement('div');
        this._trigger.className  = 'ss-trigger';
        this._trigger.tabIndex   = 0;
        this._trigger.innerHTML  = `
            <span class="ss-val ss-placeholder">${this._placeholder}</span>
            <span class="ss-arrow">▼</span>
        `;

        // Dropdown
        this._dropdown = document.createElement('div');
        this._dropdown.className = 'ss-dropdown';

        // Input de búsqueda dentro del dropdown
        const searchWrap = document.createElement('div');
        searchWrap.className = 'ss-search-wrap';
        this._searchInput = document.createElement('input');
        this._searchInput.type        = 'text';
        this._searchInput.className   = 'ss-search-input';
        this._searchInput.placeholder = 'Buscar...';
        searchWrap.appendChild(this._searchInput);

        // Lista de items
        this._list = document.createElement('div');
        this._list.className = 'ss-list';

        this._dropdown.appendChild(searchWrap);
        this._dropdown.appendChild(this._list);
        this._container.appendChild(this._trigger);
        this._container.appendChild(this._dropdown);

        this._renderList('');
    }

    // ── Renderiza los items filtrados ──────────────────────────
    _renderList(query) {
        const q = query.toLowerCase().trim();

        const filtered = this._items.filter(item =>
            item.label.toLowerCase().includes(q) ||
            (item.sub && item.sub.toLowerCase().includes(q))
        );

        this._list.innerHTML = '';

        if (!filtered.length) {
            this._list.innerHTML = '<div class="ss-empty">Sin resultados</div>';
            return;
        }

        filtered.forEach(item => {
            const el = document.createElement('div');
            el.className = 'ss-item' + (this._selected?.id === item.id ? ' ss-selected' : '');
            el.innerHTML = `
                <span class="ss-check">${this._selected?.id === item.id ? '✓' : ''}</span>
                <span class="ss-label">${item.label}</span>
                ${item.sub ? `<span class="ss-sub">${item.sub}</span>` : ''}
            `;
            el.addEventListener('mousedown', e => {
                e.preventDefault();
                this._selectItem(item);
            });
            this._list.appendChild(el);
        });
    }

    // ── Selecciona un item ─────────────────────────────────────
    _selectItem(item) {
        this._selected = item;
        this._trigger.querySelector('.ss-val').textContent = item.label;
        this._trigger.querySelector('.ss-val').classList.remove('ss-placeholder');
        this.close();
        this._onChange?.(item);
    }

    // ── Abrir / Cerrar ─────────────────────────────────────────
    open() {
        if (this._isOpen) return;
        this._isOpen = true;
        this._trigger.classList.add('ss-open');
        this._dropdown.classList.add('ss-open');
        this._searchInput.value = '';
        this._renderList('');
        setTimeout(() => this._searchInput.focus(), 40);
    }

    close() {
        if (!this._isOpen) return;
        this._isOpen = false;
        this._trigger.classList.remove('ss-open');
        this._dropdown.classList.remove('ss-open');
    }

    // ── Eventos ────────────────────────────────────────────────
    _bindEvents() {
        // Abrir/cerrar al hacer click en el trigger
        this._trigger.addEventListener('click', () => {
            this._isOpen ? this.close() : this.open();
        });

        // Teclado — Enter o Espacio abre/cierra
        this._trigger.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this._isOpen ? this.close() : this.open();
            }
            if (e.key === 'Escape') this.close();
        });

        // Filtrar al escribir
        this._searchInput.addEventListener('input', () => {
            this._renderList(this._searchInput.value);
        });

        // Cerrar al hacer click fuera
        this._outsideHandler = e => {
            if (!this._container.contains(e.target)) this.close();
        };
        document.addEventListener('mousedown', this._outsideHandler);
    }

    // ══ API PÚBLICA ════════════════════════════════════════════

    /**
     * Establece un valor desde código
     * @param {{ id, label, sub? }} item
     */
    setValue(item) {
        this._selectItem(item);
    }

    /**
     * Limpia la selección actual
     */
    clear() {
        this._selected = null;
        this._trigger.querySelector('.ss-val').textContent = this._placeholder;
        this._trigger.querySelector('.ss-val').classList.add('ss-placeholder');
        this._renderList('');
        this._onChange?.(null);
    }

    /**
     * Reemplaza los items del listado
     * @param {Array} items — [{ id, label, sub? }]
     */
    setItems(items) {
        this._items = items;
        this._renderList(this._searchInput?.value ?? '');
    }

    /**
     * Habilita o deshabilita el componente
     * @param {boolean} disabled
     */
    setDisabled(disabled) {
        this._trigger.classList.toggle('ss-disabled', disabled);
        if (disabled) this.close();
    }

    /**
     * Retorna el item seleccionado o null
     * @returns {{ id, label, sub? } | null}
     */
    getValue() {
        return this._selected;
    }

    /**
     * Limpia el DOM y los event listeners
     */
    destroy() {
        document.removeEventListener('mousedown', this._outsideHandler);
        this._container.innerHTML = '';
    }
}