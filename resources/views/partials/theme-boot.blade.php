{{--
  Applies light/dark mode, accent palette, interface style and custom chrome
  colours before first paint (no flash of the wrong theme). Defaults come from
  System Settings → Appearance; a user's own choice (stored in this browser) wins
  when the setting allows it and the chosen style is still enabled.
  Include once, early in <head>.
--}}
@php
    $uiThemeConfig = \App\Support\UiTheme::config();
    $uiChromeVars = collect($uiThemeConfig['chrome'])
        ->filter(fn (array $area): bool => $area['color'] !== null)
        // Colours are validated #rrggbb values, so they are safe inside CSS.
        ->map(fn (array $area, string $name): string => "--chrome-{$name}-bg:{$area['color']}")
        ->implode(';');
@endphp
@if ($uiChromeVars !== '')
    <style>html:root[data-ui-style]{ {{ $uiChromeVars }} }</style>
@endif
<script>
    window.__uiTheme = @json($uiThemeConfig);
    (() => {
        const cfg = window.__uiTheme;
        const html = document.documentElement;
        const read = (key) => { try { return localStorage.getItem(key); } catch (e) { return null; } };

        const mode = read('theme') || cfg.mode;
        const dark = mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        html.classList.toggle('dark', dark);
        html.dataset.theme = mode;
        html.dataset.accent = read('accent') || cfg.accent;

        const chosen = cfg.userChoice ? read('ui_style') : null;
        html.dataset.uiStyle = chosen && cfg.enabled.includes(chosen) ? chosen : cfg.style;

        html.dataset.chromeSidebar = cfg.chrome.sidebar.tone;
        if (cfg.chrome.topbar.color) { html.dataset.chromeTopbar = cfg.chrome.topbar.tone; }
        if (cfg.chrome.footer.color) { html.dataset.chromeFooter = cfg.chrome.footer.tone; }
    })();
</script>
