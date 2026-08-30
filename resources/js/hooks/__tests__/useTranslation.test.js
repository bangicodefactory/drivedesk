import { describe, it, expect, vi } from 'vitest';
import { renderHook } from '@testing-library/react';
import { useTranslation } from '@/hooks/useTranslation';

const translations = {
    'All Drivers': 'Tous les conducteurs',
    'Page :current of :last': 'Page :current sur :last',
};

vi.mock('@inertiajs/react', () => ({
    usePage: () => ({ props: { translations } }),
}));

describe('useTranslation', () => {
    const t = () => renderHook(() => useTranslation()).result.current;

    it('looks up the key and falls back to the key itself', () => {
        expect(t()('All Drivers')).toBe('Tous les conducteurs');
        expect(t()('Not there')).toBe('Not there');
        expect(t()('Not there', 'Fallback')).toBe('Fallback');
    });

    it('fills Laravel-style :name placeholders', () => {
        expect(t()('Page :current of :last', { current: 2, last: 3 })).toBe('Page 2 sur 3');
    });

    it('renders null replacements empty and leaves unknown placeholders alone', () => {
        expect(t()('Untranslated :a of :b', { a: null })).toBe('Untranslated  of :b');
    });
});
