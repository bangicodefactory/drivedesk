import { describe, it, expect } from 'vitest';
import { readFileSync, readdirSync } from 'node:fs';
import { join, sep } from 'node:path';

// Pages must use direction-aware utilities (text-end, ms-/me-/ps-/pe-,
// start-/end-) so Arabic (RTL) mirrors correctly. Physical utilities are
// allowed only on a line that pins an explicit dir="ltr" island. Fractions
// (left-1/2 + -translate-x-1/2) centre symmetrically and are allowed.
const ROOT = join(__dirname, '..', '..');
const PHYSICAL = /(?<![\w-])(?:text-right|text-left|-?(?:ml|mr|pl|pr|left|right)-(?:\d+(?:\.\d+)?|px|auto|\[[^\]]+\]))(?![\w/-])/;

const EXEMPT = new Set([
    // Inline-styled marketing page, restyled separately (roadmap Tranche 2).
    'Pages/Public/DemoGateway.jsx',
]);

describe('logical direction utilities', () => {
    // readdirSync({ recursive }) rather than fs.globSync: CI runs Node 20,
    // where globSync is still experimental.
    const files = readdirSync(join(ROOT, 'Pages'), { recursive: true })
        .map((f) => 'Pages/' + String(f).split(sep).join('/'))
        .filter((f) => f.endsWith('.jsx') && !f.includes('__tests__') && !EXEMPT.has(f));

    it('finds page files', () => {
        expect(files.length).toBeGreaterThan(50);
    });

    it.each(files)('%s uses no physical direction utilities', (file) => {
        const src = readFileSync(join(ROOT, file), 'utf8');
        const offenders = src
            .split('\n')
            .map((text, i) => ({ line: i + 1, text }))
            .filter(({ text }) => PHYSICAL.test(text) && !text.includes('dir="ltr"'))
            .map(({ line, text }) => `L${line}: ${text.trim()}`);
        expect(offenders).toEqual([]);
    });
});
