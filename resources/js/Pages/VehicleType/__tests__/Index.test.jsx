import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import { usePage, router } from '@inertiajs/react';
import VehicleTypeIndex from '@/Pages/VehicleType/Index';

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

    it('renders the delete control only with delete vehicle type permission and calls router.delete on confirm', () => {
        const confirmSpy = vi.spyOn(window, 'confirm').mockReturnValue(true);
        renderWith(['delete vehicle type']);
        fireEvent.click(screen.getByLabelText('Delete'));
        expect(router.delete).toHaveBeenCalledWith('/vehicle-type.destroy/5');
        confirmSpy.mockRestore();
    });

    it('does not call router.delete when confirm is cancelled', () => {
        const confirmSpy = vi.spyOn(window, 'confirm').mockReturnValue(false);
        renderWith(['delete vehicle type']);
        fireEvent.click(screen.getByLabelText('Delete'));
        expect(router.delete).not.toHaveBeenCalled();
        confirmSpy.mockRestore();
    });

    it('hides the action column entirely without edit or delete permission', () => {
        renderWith(['manage vehicle type']);
        expect(screen.queryByLabelText('Edit')).toBeNull();
        expect(screen.queryByLabelText('Delete')).toBeNull();
    });
});
