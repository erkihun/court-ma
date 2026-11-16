// Generates resources/css/compat-spacing.css: Tailwind v3-equivalent rules for every
// space-*/divide-* token used in the Blade views, plus `@source not inline(...)`
// lines so Tailwind v4 does not also generate its own (zero-specificity) versions.
// Usage (from the project root): node scripts/generate-spacing-compat.mjs
// then `npm run build`. Re-run whenever views gain new space-y/space-x/divide classes.
import fs from 'node:fs';
import path from 'node:path';

const [viewsDir = 'resources/views', outFile = 'resources/css/compat-spacing.css'] = process.argv.slice(2);
const walk = (d) => fs.readdirSync(d, { withFileTypes: true }).flatMap((e) =>
  e.isDirectory() ? walk(path.join(d, e.name)) : e.name.endsWith('.blade.php') ? [path.join(d, e.name)] : []);
const src = walk(viewsDir).map((f) => fs.readFileSync(f, 'utf8')).join('\n');

const BREAKPOINTS = { sm: '40rem', md: '48rem', lg: '64rem', xl: '80rem', '2xl': '96rem' };
const tokenRe = /(?<=^|[\s"'`{,])((?:[a-z0-9]+:)*)(space-[xy]-(?:[0-9]+(?:\.5)?|px)|divide-[xy](?:-[0-9]+)?|divide-[a-z]+-[0-9]{2,3}(?:\/[0-9]+)?|divide-(?:white|black|transparent)(?:\/[0-9]+)?|divide-\[[^\]\s"'`:]+\])(?=$|[\s"'`},])/g;

const tokens = new Set();
for (const m of src.matchAll(tokenRe)) tokens.add(m[1] + m[2]);

const esc = (t) => t.replace(/([:./\[\]()])/g, '\\$1');
const SIB = ' > :not([hidden]) ~ :not([hidden])';
const skipped = [];
const rules = [];

const declFor = (base) => {
  let m;
  if ((m = base.match(/^space-([xy])-(.+)$/))) {
    const v = m[2] === 'px' ? '1px' : `calc(var(--spacing) * ${m[2]})`;
    return m[1] === 'y' ? `margin-top: ${v}; margin-bottom: 0;` : `margin-left: ${v}; margin-right: 0;`;
  }
  if ((m = base.match(/^divide-([xy])(?:-([0-9]+))?$/))) {
    const w = `${m[2] ?? 1}px`;
    return m[1] === 'y' ? `border-top-width: ${w}; border-bottom-width: 0;` : `border-left-width: ${w}; border-right-width: 0;`;
  }
  if ((m = base.match(/^divide-\[([^\]]+)\]$/))) {
    return `border-color: ${m[1].replace(/_/g, ' ')};`;
  }
  if ((m = base.match(/^divide-([a-z]+(?:-[0-9]{2,3})?)(?:\/([0-9]+))?$/))) {
    const c = ['white', 'black', 'transparent'].includes(m[1]) ? (m[1] === 'transparent' ? 'transparent' : `var(--color-${m[1]})`) : `var(--color-${m[1]})`;
    return `border-color: ${m[2] ? `color-mix(in oklab, ${c} ${m[2]}%, transparent)` : c};`;
  }
  return null;
};

for (const t of [...tokens].sort()) {
  const parts = t.split(':');
  const base = parts.pop();
  const decl = declFor(base);
  if (!decl) { skipped.push(t); continue; }
  let selector = `.${esc(t)}`;
  let media = null;
  let ok = true;
  for (const v of parts) {
    if (BREAKPOINTS[v]) media = `(width >= ${BREAKPOINTS[v]})`;
    else if (v === 'dark') selector = `${selector}:is(.dark *)`;
    else ok = false;
  }
  if (!ok) { skipped.push(t); continue; }
  const rule = `${selector}${SIB} { ${decl} }`;
  rules.push(media ? `@media ${media} {\n  ${rule}\n}` : rule);
}

const handled = [...tokens].filter((t) => !skipped.includes(t)).sort();
const header = `/*
  Tailwind v3 space-between and divide behaviour, kept on purpose.
  Generated from the tokens used in resources/views (${handled.length} tokens).

  v4 rewrote these utilities as :where(.space-y-4 > :not(:last-child)) with zero
  specificity and margins on the bottom. Views here rely on the v3 form
  (.space-y-4 > :not([hidden]) ~ :not([hidden]), specificity 0,3,0): it skips
  children hidden by Alpine x-show and overrides child mt-* and inline margins.
  The matching v4 utilities are excluded below so only these rules apply.
  Regenerate with "node scripts/generate-spacing-compat.mjs" when views gain new
  space or divide classes.
*/
`;
const sourceNot = handled.map((t) => `@source not inline("${t}");`).join('\n');
fs.writeFileSync(outFile, `${header}\n${sourceNot}\n\n${rules.join('\n')}\n`);
console.log(`tokens=${tokens.size} rules=${rules.length} skipped=${skipped.join(', ') || 'none'}`);
