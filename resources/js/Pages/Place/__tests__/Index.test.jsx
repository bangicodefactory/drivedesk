import { describe, it, expect, beforeEach, vi } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';

// Confirm dialog replaces window.confirm; mock useConfirm with a controllable result.
const { confirmState } = vi.hoisted(() => ({ confirmState: { result: true } }));
vi.mock('@/components/ui/confirm-dialog', () => ({
    useConfirm: () => () => Promise.resolve(confirmState.result),
    ConfirmProvider: ({ children }) => children,
}));

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

// AdminLayout is only attached as a static `.layout`; the default export render
// path does not invoke it, but stub it to be safe if imported.
vi.mock('@/Layouts/AdminLayout', () => ({
    default: ({ children }) => <div>{children}</div>,
}));

import { router } from '@inertiajs/react';
import PlaceIndex from '../Index.jsx';

const place = {
    id: 7,
    name: 'Airport Terminal',
    city: 'Casablanca',
    island: 'N/A',
    price_formatted: '$ 20.00',
    depo_name: 'Main Depo',
    depo_address: '12 Rue X',
};

describe('Place/Index', () => {
    beforeEach(() => {
        router.delete.mockClear();
    });

    it('renders place rows with mapped display fields', () => {
        permissionsRef.current = ['manage place'];
        render(<PlaceIndex places={[place]} />);
        expect(screen.getByText('Airport Terminal')).toBeInTheDocument();
        expect(screen.getByText('Casablanca')).toBeInTheDocument();
        expect(screen.getByText('$ 20.00')).toBeInTheDocument();
    });

    it('shows an empty state when there are no places', () => {
        permissionsRef.current = ['manage place'];
        render(<PlaceIndex places={[]} />);
        expect(screen.getByText('No places yet')).toBeInTheDocument();
    });

    it('shows the Create button only with manage place permission', () => {
        permissionsRef.current = [];
        const { rerender } = render(<PlaceIndex places={[]} />);
        expect(screen.queryByText('Create Place')).not.toBeInTheDocument();

        permissionsRef.current = ['manage place'];
        rerender(<PlaceIndex places={[]} />);
        expect(screen.getByText('Create Place')).toBeInTheDocument();
    });

    it('hides the Action column when the user lacks edit/delete', () => {
        permissionsRef.current = ['manage place'];
        render(<PlaceIndex places={[place]} />);
        expect(screen.queryByText('Action')).not.toBeInTheDocument();
    });

    it('renders per-row action buttons gated by permission', () => {
        permissionsRef.current = ['edit place', 'delete place'];
        render(<PlaceIndex places={[place]} />);
        expect(screen.getByLabelText('Edit')).toBeInTheDocument();
        expect(screen.getByLabelText('Delete')).toBeInTheDocument();
    });

    it('calls router.delete on confirm and skips it on cancel', async () => {
        permissionsRef.current = ['delete place'];
        render(<PlaceIndex places={[place]} />);

        confirmState.result = false;
        fireEvent.click(screen.getByLabelText('Delete'));
        await Promise.resolve();
        expect(router.delete).not.toHaveBeenCalled();

        confirmState.result = true;
        fireEvent.click(screen.getByLabelText('Delete'));
        await waitFor(() => expect(router.delete).toHaveBeenCalledWith('/place.destroy/7'));
    });

    it('falls back to a dash for missing optional depo fields', () => {
        permissionsRef.current = ['manage place'];
        render(<PlaceIndex places={[{ id: 1, name: 'No Depo', city: 'C', island: 'I', price_formatted: '$ 0' }]} />);
        const dashes = screen.getAllByText('-');
        expect(dashes.length).toBeGreaterThanOrEqual(2);
    });
});
