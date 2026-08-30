import { describe, it, expect } from 'vitest';
import { resolveTheme } from '@/lib/theme';

describe('resolveTheme', () => {
    it('maps the branding layout mode onto next-themes values', () => {
        expect(resolveTheme({ layoutMode: 'lightmode' })).toBe('light');
        expect(resolveTheme({ layoutMode: 'darkmode' })).toBe('dark');
        expect(resolveTheme({ layoutMode: 'systemmode' })).toBe('system');
    });

    it('defaults to light when branding is missing or unknown', () => {
        expect(resolveTheme(undefined)).toBe('light');
        expect(resolveTheme({})).toBe('light');
        expect(resolveTheme({ layoutMode: 'purple' })).toBe('light');
    });
});
