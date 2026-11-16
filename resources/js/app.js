import './bootstrap';

// Chart.js (~70 KB gzipped) is only needed by the admin dashboard, so it is
// fetched on demand instead of being part of every page load.
let chartPromise = null;
window.loadChart = () => {
    chartPromise ??= import('chart.js/auto').then(({ default: Chart }) => {
        window.Chart = Chart;
        return Chart;
    });
    return chartPromise;
};

import Alpine from 'alpinejs';
import { ethDatePicker } from './eth-date-picker';
import { ethTimePicker } from './eth-time-picker';
import { chromeColor } from './chrome-color';
import { initTableSort } from './table-sort';
window.initTableSort = initTableSort;

const registerToastStore = (alpine) => {
    if (alpine.store('toasts')) {
        return;
    }

    alpine.store('toasts', {
        items: [],
        _id: 0,

        initFromServer(serverToasts = []) {
            serverToasts.forEach((toast, index) => {
                const id = ++this._id;
                this.items.push({
                    id,
                    message: toast.message,
                    type: toast.type || 'success',
                    details: toast.details || [],
                    show: true,
                });
                setTimeout(() => this.dismiss(id), 4500 + index * 400);
            });
        },

        add(message, type = 'success', duration = 4500) {
            const id = ++this._id;
            this.items.push({ id, message, type, details: [], show: true });
            setTimeout(() => this.dismiss(id), duration);
        },

        dismiss(id) {
            const item = this.items.find((toast) => toast.id === id);
            if (item) {
                item.show = false;
            }
            setTimeout(() => {
                this.items = this.items.filter((toast) => toast.id !== id);
            }, 350);
        },
    });

    window.toast = (message, type, duration) => alpine.store('toasts').add(message, type, duration);
};

// Defaults from System Settings → Appearance, published by partials/theme-boot.
const uiThemeConfig = () => window.__uiTheme || {
    style: 'classic', enabled: ['classic'], userChoice: false, mode: 'system', accent: 'blue',
};

const readPref = (key) => {
    try { return localStorage.getItem(key); } catch (e) { return null; }
};

const writePref = (key, value) => {
    try { localStorage.setItem(key, value); } catch (e) { /* private mode: keep for this page only */ }
};

window.themeSystem = function themeSystem() {
    const cfg = uiThemeConfig();
    const savedStyle = cfg.userChoice ? readPref('ui_style') : null;

    return {
        mode: readPref('theme') || cfg.mode,
        accent: readPref('accent') || cfg.accent,
        uiStyle: savedStyle && cfg.enabled.includes(savedStyle) ? savedStyle : cfg.style,
        uiStyles: cfg.enabled,
        canChooseStyle: cfg.userChoice && cfg.enabled.length > 1,
        media: window.matchMedia('(prefers-color-scheme: dark)'),
        init() {
            this.apply();
            this.applyAccent();
            document.documentElement.dataset.uiStyle = this.uiStyle;

            const sync = () => {
                if (this.mode === 'system') {
                    this.apply();
                }
            };

            if (typeof this.media.addEventListener === 'function') {
                this.media.addEventListener('change', sync);
            } else if (typeof this.media.addListener === 'function') {
                this.media.addListener(sync);
            }
        },
        apply() {
            const shouldUseDark = this.mode === 'dark'
                || (this.mode === 'system' && this.media.matches);

            document.documentElement.classList.toggle('dark', shouldUseDark);
            document.documentElement.dataset.theme = this.mode;
        },
        applyAccent() {
            document.documentElement.dataset.accent = this.accent;
        },
        set(mode) {
            this.mode = mode;
            writePref('theme', mode);
            this.apply();
        },
        setAccent(accent) {
            this.accent = accent;
            writePref('accent', accent);
            this.applyAccent();
        },
        setStyle(style) {
            if (!this.canChooseStyle || !this.uiStyles.includes(style)) {
                return;
            }
            this.uiStyle = style;
            writePref('ui_style', style);
            document.documentElement.dataset.uiStyle = style;
        },
        isStyle(style) {
            return this.uiStyle === style;
        },
        isActive(mode) {
            return this.mode === mode;
        },
        isAccent(accent) {
            return this.accent === accent;
        },
    };
};

if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.data('themeSystem', window.themeSystem);
    Alpine.data('ethDatePicker', ethDatePicker);
    Alpine.data('ethTimePicker', ethTimePicker);
    Alpine.data('chromeColor', chromeColor);
    registerToastStore(Alpine);
    Alpine.start();
} else {
    if (typeof window.Alpine.data === 'function') {
        window.Alpine.data('ethDatePicker', ethDatePicker);
        window.Alpine.data('ethTimePicker', ethTimePicker);
        window.Alpine.data('chromeColor', chromeColor);
    }
    registerToastStore(window.Alpine);
}
