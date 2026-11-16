// Click-to-sort for every shared table (.ui-table, and .cs-table on the case page).
//
// Sorting works on the rows already on the page, so it needs no server change.
// A cell can give an exact sort key with data-sort-value (e.g. an ISO date when
// the visible text is an Ethiopian date). Opt a table out with data-no-sort, or a
// column with data-no-sort on its <th>. Columns whose cells only hold buttons or
// forms (actions) are skipped automatically, as are tables with merged cells.

const TABLE_SELECTOR = 'table.ui-table, table.cs-table';
const collator = new Intl.Collator(document.documentElement.lang || undefined, { numeric: true, sensitivity: 'base' });

const cellKey = (cell) => {
    if (!cell) return '';
    if (cell.dataset.sortValue !== undefined) return cell.dataset.sortValue.trim();
    return cell.textContent.replace(/\s+/g, ' ').trim();
};

const numeric = (value) => {
    if (!/^[-+]?[\d,]*\.?\d+%?$/.test(value)) return null;
    const n = parseFloat(value.replace(/[,%]/g, ''));
    return Number.isFinite(n) ? n : null;
};

const compare = (a, b) => {
    if (a === b) return 0;
    if (a === '' || a === '-' || a === '—') return 1;
    if (b === '' || b === '-' || b === '—') return -1;
    const na = numeric(a);
    const nb = numeric(b);
    if (na !== null && nb !== null) return na - nb;
    return collator.compare(a, b);
};

const isActionColumn = (rows, index) => rows.every((row) => {
    const cell = row.cells[index];
    if (!cell) return true;
    const text = [...cell.childNodes]
        .filter((n) => n.nodeType === Node.TEXT_NODE)
        .map((n) => n.textContent.trim())
        .join('');
    return text === '' && cell.querySelector('form, button, a.btn, a[class*="btn"]') !== null;
});

const enhance = (table) => {
    if (table.dataset.sortReady || table.hasAttribute('data-no-sort')) return;
    const head = table.tHead;
    const body = table.tBodies[0];
    if (!head || head.rows.length !== 1 || !body) return;

    const headers = [...head.rows[0].cells];
    const rows = [...body.rows];
    if (rows.length < 2) return;
    if (rows.some((row) => row.cells.length !== headers.length || [...row.cells].some((c) => c.colSpan > 1 || c.rowSpan > 1))) return;
    if (headers.some((th) => th.colSpan > 1)) return;

    table.dataset.sortReady = '1';

    headers.forEach((th, index) => {
        const label = th.textContent.trim();
        if (!label || label === '#' || th.hasAttribute('data-no-sort') || isActionColumn(rows, index)) return;

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'ui-sort';
        while (th.firstChild) button.appendChild(th.firstChild);
        const icon = document.createElement('span');
        icon.className = 'ui-sort-icon';
        icon.setAttribute('aria-hidden', 'true');
        button.appendChild(icon);
        th.appendChild(button);
        th.setAttribute('aria-sort', 'none');

        button.addEventListener('click', () => {
            const direction = th.getAttribute('aria-sort') === 'ascending' ? 'descending' : 'ascending';
            headers.forEach((other) => other.hasAttribute('aria-sort') && other.setAttribute('aria-sort', 'none'));
            th.setAttribute('aria-sort', direction);

            const sign = direction === 'ascending' ? 1 : -1;
            const sorted = [...body.rows].sort((a, b) => sign * compare(cellKey(a.cells[index]), cellKey(b.cells[index])));
            body.append(...sorted);
        });
    });
};

export const initTableSort = (root = document) => {
    root.querySelectorAll(TABLE_SELECTOR).forEach(enhance);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initTableSort());
} else {
    initTableSort();
}
