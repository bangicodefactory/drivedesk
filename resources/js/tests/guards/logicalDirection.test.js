import { describe, it, expect } from 'vitest';
import { readFileSync, readdirSync } from 'node:fs';
import { join, sep } from 'node:path';

// The UI must use direction-aware utilities (text-end, ms-/me-/ps-/pe-,
// start-/end-) so Arabic (RTL) mirrors correctly. Physical utilities are
// allowed only on a line that either pins an explicit dir="ltr" island or
// carries an `rtl-ignore` marker comment explaining why the physical side is
// deliberate (e.g. shadcn's side-aware sheet/sidebar variants, keyed on a
// side prop that AppSidebar already flips for RTL). Fractions
// (left-1/2 + -translate-x-1/2) centre symmetrically and are allowed.
const ROOT = join(__dirname, '..', '..');
const SCOPES = ['Pages', 'Layouts', 'components'];
const PHYSICAL = /(?<![\w-])(?:text-right|text-left|-?(?:ml|mr|pl|pr|left|right)-(?:\d+(?:\.\d+)?|px|auto|\[[^\]]+\]))(?![\w/-])/;

describe('logical direction utilities', () => {
    // readdirSync({ recursive }) rather than fs.globSync: CI runs Node 20,
    // where globSync is still experimental.
    const files = SCOPES.flatMap((scope) =>
        readdirSync(join(ROOT, scope), { recursive: true })
            .map((f) => `${scope}/` + String(f).split(sep).join('/'))
            .filter((f) => f.endsWith('.jsx') && !f.includes('__tests__'))
    );

    it('finds source files in every scope', () => {
        expect(files.length).toBeGreaterThan(100);
    });

    it.each(files)('%s uses no physical direction utilities', (file) => {
        const src = readFileSync(join(ROOT, file), 'utf8');
        const offenders = src
            .split('\n')
            .map((text, i) => ({ line: i + 1, text }))
            .filter(({ text }) => PHYSICAL.test(text) && !text.includes('dir="ltr"') && !text.includes('rtl-ignore'))
            .map(({ line, text }) => `L${line}: ${text.trim()}`);
        expect(offenders).toEqual([]);
    });
});
