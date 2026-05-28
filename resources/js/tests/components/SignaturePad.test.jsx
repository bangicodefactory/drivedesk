import { render, screen, fireEvent } from '@testing-library/react';
import { vi } from 'vitest';

// react-signature-canvas uses Canvas APIs unavailable in jsdom — mock the library.
vi.mock('react-signature-canvas', () => ({
    default: vi.fn(function MockCanvas({ canvasProps, onEnd }) {
        return (
            <canvas
                {...canvasProps}
                onClick={() => onEnd?.()}
            />
        );
    }),
}));

import SignaturePad from '@/components/SignaturePad';

describe('SignaturePad', () => {
    it('renders the canvas and Clear button', () => {
        render(<SignaturePad />);
        expect(screen.getByTestId('signature-canvas')).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Clear' })).toBeInTheDocument();
    });

    it('calls onChange(null) when Clear is clicked', () => {
        const onChange = vi.fn();
        render(<SignaturePad onChange={onChange} />);
        fireEvent.click(screen.getByRole('button', { name: 'Clear' }));
        expect(onChange).toHaveBeenCalledWith(null);
    });

    it('shows an error message when error prop is provided', () => {
        render(<SignaturePad error="Signature is required" />);
        expect(screen.getByRole('alert')).toHaveTextContent('Signature is required');
    });

    it('Clear button is disabled when disabled prop is set', () => {
        render(<SignaturePad disabled />);
        expect(screen.getByRole('button', { name: 'Clear' })).toBeDisabled();
    });

    it('does not render an error alert when error is absent', () => {
        render(<SignaturePad />);
        expect(screen.queryByRole('alert')).not.toBeInTheDocument();
    });
});
