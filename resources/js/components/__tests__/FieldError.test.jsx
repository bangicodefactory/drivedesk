import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import FieldError from '@/components/FieldError';

describe('FieldError', () => {
    it('renders nothing when the field has no error', () => {
        const { container } = render(<FieldError name="type" errors={{}} />);
        expect(container).toBeEmptyDOMElement();
    });

    it('renders an alert with the id the input is described by', () => {
        render(<FieldError name="type" errors={{ type: { message: 'Required' } }} />);
        const alert = screen.getByRole('alert');
        expect(alert).toHaveTextContent('Required');
        expect(alert).toHaveAttribute('id', 'type-error');
        expect(alert).toHaveClass('text-destructive');
    });

    it('accepts a className override for dark surfaces', () => {
        render(<FieldError name="email" errors={{ email: { message: 'Bad' } }} className="text-xs text-red-400" />);
        expect(screen.getByRole('alert')).toHaveClass('text-red-400');
    });
});
