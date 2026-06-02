import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import { usePage, router } from '@inertiajs/react';
import VehicleIndex from '@/Pages/Vehicle/Index';

// Mock Inertia: usePage feeds auth.permissions, Link renders a plain anchor,
// router.delete is a spy so we can assert the confirm-gated destroy flow.
vi.mock('@inertiajs/react', () => ({
    usePage: vi.fn(),
    Link: ({ href, children, ...rest }) => <a href={href} {...rest}>{children}</a>,
    router: { delete: vi.fn(), get: vi.fn() },
}));

// route() is a global Ziggy helper in the app; stub it for the test.
globalThis.route = (name, param) => `/${name}/${param ?? ''}`;

const baseVehicle = {
    id: 7,
    vehicle_id: 1,
    vehicle_id_display: 'V-1',
    name: 'Test Car',
    type_label: 'Sedan',
    model: 'Model X',
    license_plate: 'ABC-123',
    engine_type: 'V8',
};

function makePaginator(items) {
    return {
        data: items,
        current_page: 1,
        last_page: 1,
        from: 1,
        to: items.length,
        total: items.length,
        prev_page_url: null,
        next_page_url: null,
    };
}

function renderWith(permissions) {
    usePage.mockReturnValue({ props: { auth: { permissions } } });
    return render(<VehicleIndex vehicles={makePaginator([baseVehicle])} />);
}

describe('Vehicle/Index permission gating', () => {
    beforeEach(() => {
        router.delete.mockClear();
    });

    it('hides the create button without manage vehicle permission', () => {
        renderWith([]);
        expect(screen.queryByText('Create Vehicle')).toBeNull();
    });

    it('shows the create button with manage vehicle permission', () => {
        renderWith(['manage vehicle']);
        expect(screen.getByText('Create Vehicle')).toBeInTheDocument();
    });

    it('renders the vehicle row name', () => {
        renderWith(['manage vehicle']);
        expect(screen.getByText('Test Car')).toBeInTheDocument();
    });

    it('renders the delete control only with delete vehicle permission and calls router.delete on confirm', () => {
        const confirmSpy = vi.spyOn(window, 'confirm').mockReturnValue(true);
        renderWith(['delete vehicle']);
        const deleteButton = screen.getByLabelText('Delete');
        fireEvent.click(deleteButton);
        expect(router.delete).toHaveBeenCalledWith('/vehicle.destroy/7');
        confirmSpy.mockRestore();
    });

    it('does not call router.delete when confirm is cancelled', () => {
        const confirmSpy = vi.spyOn(window, 'confirm').mockReturnValue(false);
        renderWith(['delete vehicle']);
        fireEvent.click(screen.getByLabelText('Delete'));
        expect(router.delete).not.toHaveBeenCalled();
        confirmSpy.mockRestore();
    });

    it('hides the delete control without delete vehicle permission', () => {
        renderWith(['show vehicle']);
        expect(screen.queryByLabelText('Delete')).toBeNull();
    });
});
