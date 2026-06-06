import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { usePage, router } from '@inertiajs/react';
import VehicleTypeIndex from '@/Pages/VehicleType/Index';

// Confirm dialog replaces window.confirm; mock useConfirm with a controllable result.
const { confirmState } = vi.hoisted(() => ({ confirmState: { result: true } }));
vi.mock('@/components/ui/confirm-dialog', () => ({
    useConfirm: () => () => Promise.resolve(confirmState.result),
    ConfirmProvider: ({ children }) => children,
}));

// Mock Inertia: usePage feeds auth.permissions, Link renders a plain anchor,
// router.delete is a spy so we can assert the confirm-gated destroy flow.
vi.mock('@inertiajs/react', () => ({
    usePage: vi.fn(),
    Link: ({ href, children, ...rest }) => <a href={href} {...rest}>{children}</a>,
    router: { delete: vi.fn(), get: vi.fn() },
}));

// route() is a global Ziggy helper in the app; stub it for the test.
globalThis.route = (name, param) => `/${name}/${param ?? ''}`;

const baseType = {
    id: 5,
    type: 'Sedan',
    notes: 'Four-door',
};

function renderWith(permissions) {
    usePage.mockReturnValue({ props: { auth: { permissions } } });
    return render(<VehicleTypeIndex types={[baseType]} />);
}

describe('VehicleType/Index permission gating', () => {
    beforeEach(() => {
        router.delete.mockClear();
    });

    it('hides the create button without manage vehicle type permission', () => {
        renderWith([]);
        expect(screen.queryByText('Create Type')).toBeNull();
    });

    it('shows the create button with manage vehicle type permission', () => {
        renderWith(['manage vehicle type']);
        expect(screen.getByText('Create Type')).toBeInTheDocument();
    });

    it('renders the vehicle type row', () => {
        renderWith(['manage vehicle type']);
        expect(screen.getByText('Sedan')).toBeInTheDocument();
        expect(screen.getByText('Four-door')).toBeInTheDocument();
    });

    it('renders the edit control only with edit vehicle type permission', () => {
        renderWith(['edit vehicle type']);
        expect(screen.getByLabelText('Edit')).toBeInTheDocument();
        expect(screen.queryByLabelText('Delete')).toBeNull();
    });

    it('renders the delete control only with delete vehicle type permission and calls router.delete on confirm', async () => {
        confirmState.result = true;
        renderWith(['delete vehicle type']);
        fireEvent.click(screen.getByLabelText('Delete'));
        await waitFor(() => expect(router.delete).toHaveBeenCalledWith('/vehicle-type.destroy/5'));
    });

    it('does not call router.delete when confirm is cancelled', async () => {
        confirmState.result = false;
        renderWith(['delete vehicle type']);
        fireEvent.click(screen.getByLabelText('Delete'));
        await Promise.resolve();
        expect(router.delete).not.toHaveBeenCalled();
    });

    it('hides the action column entirely without edit or delete permission', () => {
        renderWith(['manage vehicle type']);
        expect(screen.queryByLabelText('Edit')).toBeNull();
        expect(screen.queryByLabelText('Delete')).toBeNull();
    });
});
