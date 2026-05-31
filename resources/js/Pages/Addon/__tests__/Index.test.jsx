import { describe, it, expect, beforeEach, vi } from 'vitest';
import { render, screen } from '@testing-library/react';

// Mock Ziggy's global route() helper used inside the component.
beforeEach(() => {
    globalThis.route = (name, param) => `/${name}${param != null ? `/${param}` : ''}`;
});

// Stub Inertia so we can drive usePage().props.auth.permissions and avoid a
// real Inertia app context. Link renders a plain anchor; router.delete is a spy.
const permissionsRef = { current: [] };
vi.mock('@inertiajs/react', () => ({
    Link: ({ href, children, ...rest }) => <a href={href} {...rest}>{children}</a>,
    router: { delete: vi.fn() },
    usePage: () => ({ props: { auth: { permissions: permissionsRef.current } } }),
}));

// AdminLayout is only attached as a static `.layout`; stub it to be safe.
vi.mock('@/Layouts/AdminLayout', () => ({
    default: ({ children }) => <div>{children}</div>,
}));

import AddonIndex from '../Index.jsx';

const addon = { id: 7, name: 'Child Seat', price: 15, price_formatted: '$15.00', billing_type: 'daily' };

describe('Addon/Index', () => {
    it('renders addon rows with formatted price and billing type', () => {
        permissionsRef.current = ['manage addon'];
        render(<AddonIndex addons={[addon]} />);
        expect(screen.getByText('Child Seat')).toBeInTheDocument();
        expect(screen.getByText('$15.00')).toBeInTheDocument();
        expect(screen.getByText('daily')).toBeInTheDocument();
    });

    it('falls back to raw price when price_formatted is absent', () => {
        permissionsRef.current = ['manage addon'];
        render(<AddonIndex addons={[{ id: 8, name: 'GPS', price: 9, billing_type: 'total' }]} />);
        expect(screen.getByText('9')).toBeInTheDocument();
    });

    it('shows an empty state when there are no addons', () => {
        permissionsRef.current = ['manage addon'];
        render(<AddonIndex addons={[]} />);
        expect(screen.getByText('No addons yet')).toBeInTheDocument();
    });

    it('shows the Create button only with manage addon permission', () => {
        permissionsRef.current = [];
        const { rerender } = render(<AddonIndex addons={[]} />);
        expect(screen.queryByText('Create Addon')).not.toBeInTheDocument();

        permissionsRef.current = ['manage addon'];
        rerender(<AddonIndex addons={[]} />);
        expect(screen.getByText('Create Addon')).toBeInTheDocument();
    });

    it('hides the Action column when the user lacks edit/delete addon', () => {
        permissionsRef.current = ['manage addon'];
        render(<AddonIndex addons={[addon]} />);
        expect(screen.queryByText('Action')).not.toBeInTheDocument();
    });

    it('renders per-row action buttons gated by permission', () => {
        permissionsRef.current = ['edit addon', 'delete addon'];
        render(<AddonIndex addons={[addon]} />);
        expect(screen.getByLabelText('Edit')).toBeInTheDocument();
        expect(screen.getByLabelText('Delete')).toBeInTheDocument();
    });
});
