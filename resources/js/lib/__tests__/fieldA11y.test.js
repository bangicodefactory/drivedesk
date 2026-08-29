import { describe, it, expect } from 'vitest';
import { fieldA11y, fieldErrorId } from '@/lib/fieldA11y';

describe('fieldA11y', () => {
    it('returns no attributes when the field has no error', () => {
        expect(fieldA11y({}, 'type')).toEqual({});
        expect(fieldA11y(undefined, 'type')).toEqual({});
    });

    it('marks the field invalid and points at its error element', () => {
        const errors = { type: { message: 'The type field is required.' } };
        expect(fieldA11y(errors, 'type')).toEqual({
            'aria-invalid': true,
            'aria-describedby': 'type-error',
        });
    });

    it('derives a stable id from the field name', () => {
        expect(fieldErrorId('g-recaptcha-response')).toBe('g-recaptcha-response-error');
    });
});
