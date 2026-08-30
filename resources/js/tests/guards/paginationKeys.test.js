import { describe, it, expect } from 'vitest';
import { readFileSync, readdirSync } from 'node:fs';
import { join } from 'node:path';

// Pagination reads these keys via useTranslation. Every locale bundle must
// carry them byte-identically (the range key embeds an en-dash that is easy
// to retype as a hyphen, which would silently fall back to English).
const KEYS = ['Pagination', 'Previous', 'Next', ':from–:to of :total', 'Page :current of :last'];
const LANG_DIR = join(__dirname, '..', '..', '..', 'lang');

describe('pagination translation keys', () => {
    const bundles = readdirSync(LANG_DIR).filter((f) => f.endsWith('.json'));

    it('finds the locale bundles', () => {
        expect(bundles.length).toBeGreaterThanOrEqual(13);
    });

    it.each(bundles)('%s carries all pagination keys', (bundle) => {
        const data = JSON.parse(readFileSync(join(LANG_DIR, bundle), 'utf8'));
        const missing = KEYS.filter((k) => typeof data[k] !== 'string' || data[k] === '');
        expect(missing).toEqual([]);
    });
});
