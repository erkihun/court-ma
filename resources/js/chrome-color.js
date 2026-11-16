// Sidebar / top bar / footer colour picker for System Settings → Appearance.
// Mirrors App\Support\UiTheme: text tone and the 4.5:1 contrast rule are
// computed the same way, and the page previews the colour live.

const INK = '#0f172a';
const PAPER = '#ffffff';
const MIN_CONTRAST = 4.5;
const HEX = /^#[0-9a-f]{6}$/i;

// Default sidebar colours per interface style (keep in sync with themes.css).
const SIDEBAR_DEFAULTS = {
    classic: '#0c1527',
    authority: '#0b1f4d',
    swiss: '#09090b',
    compact: '#0c1527',
    inclusive: '#000000',
    bento: '#1c1c1e',
};

const luminance = (hex) => {
    const channel = (i) => {
        const c = parseInt(hex.slice(i, i + 2), 16) / 255;
        return c <= 0.03928 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4;
    };
    return 0.2126 * channel(1) + 0.7152 * channel(3) + 0.0722 * channel(5);
};

const contrast = (a, b) => {
    const [hi, lo] = [luminance(a), luminance(b)].sort((x, y) => y - x);
    return (hi + 0.05) / (lo + 0.05);
};

export const chromeColor = (area, saved, fallback) => ({
    area,
    custom: HEX.test(saved || ''),
    value: HEX.test(saved || '') ? saved.toLowerCase() : fallback,

    init() {
        if (!this.custom && area === 'sidebar') {
            this.value = SIDEBAR_DEFAULTS[document.documentElement.dataset.uiStyle] || fallback;
        }
    },

    get valid() {
        return HEX.test(this.value || '');
    },

    get ratio() {
        return this.valid ? Math.max(contrast(this.value, PAPER), contrast(this.value, INK)) : 0;
    },

    get ok() {
        return this.ratio >= MIN_CONTRAST;
    },

    get tone() {
        return contrast(this.value, PAPER) >= contrast(this.value, INK) ? 'dark' : 'light';
    },

    apply() {
        const html = document.documentElement;
        const key = 'chrome' + area.charAt(0).toUpperCase() + area.slice(1);
        const active = this.custom && this.valid;

        if (area === 'sidebar') {
            const color = active ? this.value : SIDEBAR_DEFAULTS[html.dataset.uiStyle] || fallback;
            html.style.setProperty('--chrome-sidebar-bg', color);
            html.dataset[key] = active ? this.tone : 'dark';
            return;
        }

        if (active) {
            html.style.setProperty(`--chrome-${area}-bg`, this.value);
            html.dataset[key] = this.tone;
        } else {
            html.style.removeProperty(`--chrome-${area}-bg`);
            delete html.dataset[key];
        }

        document.querySelectorAll(`.chrome-${area}`).forEach((el) => {
            el.classList.toggle('dark', active && this.tone === 'dark');
        });
    },
});
