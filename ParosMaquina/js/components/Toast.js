/**
 * Toast.js — Componente de notificaciones
 * 
 * Uso:
 *   import { Toast } from './components/Toast.js';
 *   Toast.show('Registro guardado');
 *   Toast.show('Eliminado', 'error');
 *   Toast.show('Atención', 'warning');
 */

export class Toast {

    static #el     = null; // elemento del DOM
    static #timer  = null; // timer para auto-ocultar

    // ── Tipos disponibles ──────────────────────────────────────
    static #types = {
        success: { icon: '✓', color: '#4ADE80' },
        error:   { icon: '✕', color: '#FCA5A5' },
        warning: { icon: '⚠', color: '#FCD34D' },
        info:    { icon: 'ℹ', color: '#93C5FD' },
    };

    // ── Inicializa el elemento en el DOM (se llama automático) ──
    static #init() {
        if (Toast.#el) return; // ya existe, no crear de nuevo

        const el = document.createElement('div');
        el.id = 'toast-component';
        el.style.cssText = `
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #1E2435;
            color: #fff;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            display: flex;
            align-items: center;
            gap: 8px;
            opacity: 0;
            transform: translateY(8px);
            transition: opacity 0.2s ease, transform 0.2s ease;
            pointer-events: none;
            z-index: 9999;
            min-width: 200px;
            max-width: 360px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.25);
        `;

        el.innerHTML = `
            <span id="toast-icon" style="font-size:15px;flex-shrink:0"></span>
            <span id="toast-msg"  style="flex:1;line-height:1.4"></span>
        `;

        document.body.appendChild(el);
        Toast.#el = el;
    }

    // ── Método principal ───────────────────────────────────────
    /**
     * @param {string} message  — Texto a mostrar
     * @param {string} type     — 'success' | 'error' | 'warning' | 'info'  (default: 'success')
     * @param {number} duration — Milisegundos antes de ocultarse             (default: 2500)
     */
    static show(message, type = 'success', duration = 2500) {
        Toast.#init();

        const cfg = Toast.#types[type] ?? Toast.#types.success;

        // Actualiza contenido
        Toast.#el.querySelector('#toast-icon').textContent   = cfg.icon;
        Toast.#el.querySelector('#toast-icon').style.color   = cfg.color;
        Toast.#el.querySelector('#toast-msg').textContent    = message;

        // Muestra
        Toast.#el.style.opacity   = '1';
        Toast.#el.style.transform = 'translateY(0)';

        // Reinicia el timer si ya había uno corriendo
        clearTimeout(Toast.#timer);
        Toast.#timer = setTimeout(() => Toast.hide(), duration);
    }

    // ── Ocultar manualmente ────────────────────────────────────
    static hide() {
        if (!Toast.#el) return;
        Toast.#el.style.opacity   = '0';
        Toast.#el.style.transform = 'translateY(8px)';
    }

    // ── Shortcuts semánticos ───────────────────────────────────
    static success(message, duration)  { Toast.show(message, 'success', duration); }
    static error(message, duration)    { Toast.show(message, 'error',   duration); }
    static warning(message, duration)  { Toast.show(message, 'warning', duration); }
    static info(message, duration)     { Toast.show(message, 'info',    duration); }
}