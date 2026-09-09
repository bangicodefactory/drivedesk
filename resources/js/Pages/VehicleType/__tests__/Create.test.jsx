import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import VehicleTypeCreate from '@/Pages/VehicleType/Create';

vi.mock('@inertiajs/react', () => ({
    usePage: () => ({ props: {} }),
    Link: ({ href, children, ...rest }) => <a href={href} {...rest}>{children}</a>,
    router: { post: vi.fn(), on: vi.fn() },
}));

globalThis.route = (name) => `/${name}`;

describe('VehicleType create form', () => {
    it('wires validation errors to the input for assistive tech', async () => {
        render(<VehicleTypeCreate />);
        const input = screen.getByLabelText('Type');
        expect(input).not.toHaveAttribute('aria-invalid');

        fireEvent.click(screen.getByRole('button', { name: 'Create' }));

        const alert = await screen.findByRole('alert');
        expect(alert).toHaveTextContent('The type field is required.');
        expect(alert).toHaveAttribute('id', 'type-error');
        expect(input).toHaveAttribute('aria-invalid', 'true');
        expect(input).toHaveAttribute('aria-describedby', 'type-error');
    });
});
