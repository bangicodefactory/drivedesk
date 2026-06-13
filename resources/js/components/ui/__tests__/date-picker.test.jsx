import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import { DatePicker, formatDmy } from '../date-picker.jsx';

describe('formatDmy', () => {
    it('renders an ISO date as dd/mm/yyyy', () => {
        expect(formatDmy('2025-10-31')).toBe('31/10/2025');
    });
    it('handles datetime ISO strings', () => {
        expect(formatDmy('2025-03-09 00:00:00')).toBe('09/03/2025');
    });
    it('returns empty string for falsy input', () => {
        expect(formatDmy('')).toBe('');
        expect(formatDmy(null)).toBe('');
    });
});

describe('DatePicker', () => {
    it('shows the placeholder when empty and the dd/mm/yyyy value when set', () => {
        const { rerender } = render(<DatePicker value="" placeholder="dd/mm/yyyy" />);
        expect(screen.getByText('dd/mm/yyyy')).toBeInTheDocument();

        rerender(<DatePicker value="2025-10-31" placeholder="dd/mm/yyyy" />);
        expect(screen.getByText('31/10/2025')).toBeInTheDocument();
    });

    it('keeps the value in ISO and emits ISO on change (not the d/m/y display)', () => {
        const onChange = vi.fn();
        const { container } = render(<DatePicker value="2025-10-31" onChange={onChange} />);
        const input = container.querySelector('input[type="date"]');
        // native date input round-trips ISO
        expect(input.value).toBe('2025-10-31');
        fireEvent.change(input, { target: { value: '2024-01-02' } });
        expect(onChange).toHaveBeenCalledWith('2024-01-02');
    });
});
