// Keep the live form controls (including files and editors) in place.
(() => {
    let switching = false;
    const editable = 'textarea, [contenteditable]:not([contenteditable="false"]), .tox, [role="textbox"]';
    const captions = 'label, legend, button, h1, h2, h3, h4, th, a, option, .ar-section-label, .ar-hint, .al-heading p, .al-register-line';
    document.addEventListener('click', async event => {
        const link = event.target.closest('a[data-form-language]');
        if (!link || event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey ||
            event.shiftKey || event.altKey || !document.querySelector('form input:not([type="hidden"]), form textarea, form select')) return;
        event.preventDefault();
        const current = document.documentElement.lang;
        if (switching || link.dataset.formLanguage === current) return;
        switching = true;
        try {
            const response = await fetch(link.href, {
                headers: { Accept: 'application/json' }, redirect: 'error', cache: 'no-store'
            });
            if (!response.ok) throw new Error();
            const { locale, labels } = await response.json();
            if (locale !== link.dataset.formLanguage || !labels) throw new Error();
            const translate = value => {
                const key = value.trim();
                return Object.hasOwn(labels, key) ? value.replace(key, () => labels[key]) : value;
            };
            const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
            while (walker.nextNode()) {
                const node = walker.currentNode;
                const parent = node.parentElement;
                if (!parent?.closest(captions) || parent.closest(editable + ',script,style,[data-form-language]')) continue;
                // Options without an explicit value submit their text.
                if (parent.closest('option:not([value])')) continue;
                const next = translate(node.data);
                if (next !== node.data) node.data = next;
            }
            for (const el of document.querySelectorAll('[placeholder],[aria-label],[title],input[type="submit"]')) {
                if (el.closest(editable) && !el.matches('textarea')) continue;
                for (const attr of ['placeholder', 'aria-label', 'title']) {
                    if (el.hasAttribute(attr)) el.setAttribute(attr, translate(el.getAttribute(attr)));
                }
                if (el.matches('input[type="submit"]:not([name])[value]')) el.value = translate(el.value);
            }
            for (const selected of document.querySelectorAll('a[data-form-language="' + locale + '"]')) {
                const previous = selected.parentElement.querySelector('a[data-form-language="' + current + '"]');
                if (previous) [previous.className, selected.className] = [selected.className, previous.className];
                selected.setAttribute('aria-current', 'true');
                previous?.removeAttribute('aria-current');
            }
            document.documentElement.lang = locale;
        } catch {
            window.alert(current === 'am' ? 'ቋንቋውን መቀየር አልተቻለም። እባክዎ ደግመው ይሞክሩ።' : 'Could not switch languages. Please try again.');
        } finally {
            switching = false;
        }
    });
})();
