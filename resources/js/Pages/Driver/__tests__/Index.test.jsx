import { describe, it, expect, beforeEach, vi } from 'vitest';
import { render, screen } from '@testing-library/react';

// Mock Ziggy's global route() helper used inside the component.
beforeEach(() => {
    globalThis.route = (name, param) => `/${name}${param != null ? `/${param}` : ''}`;
});

// Stub Inertia so we can drive usePage().props.auth.permissions and avoid a
// real Inertia app context. Link renders a plain anchor; router.delete/get are spies.
const permissionsRef = { current: [] };
vi.mock('@inertiajs/react', () => ({
    Link: ({ href, children, ...rest }) => <a href={href} {...rest}>{children}</a>,
    router: { delete: vi.fn(), get: vi.fn() },
    usePage: () => ({ props: { auth: { permissions: permissionsRef.current } } }),
}));

// AdminLayout is only attached as a static `.layout`; the default export render
// path does not invoke it, but stub it to be safe if imported.
vi.mock('@/Layouts/AdminLayout', () => ({
    default: ({ children }) => <div>{children}</div>,
}));

import DriverIndex from '../Index.jsx';

const driver = {
    id: 7,
    name: 'Jane Doe',
    email: 'jane@example.com',
    phone_number: '0612345678',
    driver_id_display: 'DRV-7',
    license_number: 'LIC-99',
    issue_date_display: '2024-01-01',
    expiration_date_display: '2030-01-01',
};

// The controller now returns a Laravel paginator; wrap fixtures in its shape.
const paginate = (rows) => ({
    data: rows,
    total: rows.length,
    current_page: 1,
    last_page: 1,
    per_page: 25,
});

describe('Driver/Index', () => {
    it('renders driver rows with mapped display fields', () => {
        permissionsRef.current = ['manage driver'];
        render(<DriverIndex drivers={paginate([driver])} />);
        expect(screen.getByText('Jane Doe')).toBeInTheDocument();
        expect(screen.getByText('DRV-7')).toBeInTheDocument();
        expect(screen.getByText('LIC-99')).toBeInTheDocument();
    });

    it('shows an empty state when there are no drivers', () => {
        permissionsRef.current = ['manage driver'];
        render(<DriverIndex drivers={paginate([])} />);
        expect(screen.getByText('No drivers yet')).toBeInTheDocument();
    });

    it('shows the Create button only with manage driver permission', () => {
        permissionsRef.current = [];
        const { rerender } = render(<DriverIndex drivers={paginate([])} />);
        expect(screen.queryByText('Create Driver')).not.toBeInTheDocument();

        permissionsRef.current = ['manage driver'];
        rerender(<DriverIndex drivers={paginate([])} />);
        expect(screen.getByText('Create Driver')).toBeInTheDocument();
    });

    it('hides the Action column when the user lacks show/edit/delete', () => {
        permissionsRef.current = ['manage driver'];
        render(<DriverIndex drivers={paginate([driver])} />);
        expect(screen.queryByText('Action')).not.toBeInTheDocument();
    });

    it('renders per-row action buttons gated by permission', () => {
        permissionsRef.current = ['edit driver', 'delete driver', 'show driver'];
        render(<DriverIndex drivers={paginate([driver])} />);
        expect(screen.getByLabelText('Details')).toBeInTheDocument();
        expect(screen.getByLabelText('Edit')).toBeInTheDocument();
        expect(screen.getByLabelText('Delete')).toBeInTheDocument();
    });

    it('falls back to a dash for missing optional fields', () => {
        permissionsRef.current = ['manage driver'];
        render(<DriverIndex drivers={paginate([{ id: 1, name: 'No Data' }])} />);
        // license number, issue/expiration date all render '-'
        const dashes = screen.getAllByText('-');
        expect(dashes.length).toBeGreaterThanOrEqual(3);
    });
});
