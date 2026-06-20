import { describe, it, expect } from 'vitest';
import { formatDt } from '@/lib/datetime';

describe('formatDt', () => {
    // Regression for JAVASCRIPT-4: a datetime-local value must be sent to the
    // backend as `Y/m/d H:i` (slashes), the format Carbon::createFromFormat
    // expects on /vehicle/available. Dashes used to 500 the request.
    it('converts a datetime-local value to Y/m/d H:i', () => {
        expect(formatDt('2026-06-20T09:00')).toBe('2026/06/20 09:00');
    });

    it('drops a seconds component', () => {
        expect(formatDt('2026-06-20T09:00:30')).toBe('2026/06/20 09:00');
    });

    it('returns empty string for falsy input', () => {
        expect(formatDt('')).toBe('');
        expect(formatDt(undefined)).toBe('');
        expect(formatDt(null)).toBe('');
    });

    it('never emits dashes in the output', () => {
        expect(formatDt('2026-12-31T23:59')).not.toContain('-');
    });
});
