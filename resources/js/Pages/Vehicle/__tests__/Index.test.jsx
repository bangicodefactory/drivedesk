import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import { usePage, router } from '@inertiajs/react';
import VehicleIndex from '../Index';

// Mock Inertia: usePage feeds props, Link renders a plain anchor, router.delete
// is a spy so we can assert the confirm-gated destroy flow.
vi.mock('@inertiajs/react', () => ({
    usePage: vi.fn(),
    Link: ({ href, children, ...rest }) => <a href={href} {...rest}>{children}</a>,
    Head: ({ title }) => <title>{title}</title>,
    router: { delete: vi.fn() },
}));

// route() is a global Ziggy helper in the app; stub it for the test.
globalThis.route = (name, param) => `/${name}/${param ?? ''}`;

const baseVehicle = {
    id: 7,
    vehicle_id: 1,
    name: 'Test Car',
    model: 'Model X',
    license_plate: 'ABC-123',
    daily_rate: '50',
    daily_rate_formatted: '$ 50.00',
};

function renderWith(permissions) {
    usePage.mockReturnValue({
        props: {
            translations: {},
            permissions,
            vehicles: [baseVehicle],
        },
    });
    return render(<VehicleIndex />);
}

describe('Vehicle/Index permission gating', () => {
    beforeEach(() => {
        router.delete.mockClear();
    });

    it('hides the create button when create vehicle permission is absent', () => {
        renderWith({});
        expect(screen.queryByText('Create Vehicle')).toBeNull();
    });

    it('shows the create button when create vehicle permission is present', () => {
        renderWith({ 'create vehicle': true });
        expect(screen.getByText('Create Vehicle')).toBeInTheDocument();
    });

    it('renders the formatted daily rate from the prop', () => {
        renderWith({ 'manage vehicle': true });
        expect(screen.getByText('$ 50.00')).toBeInTheDocument();
    });

    it('renders the delete control only with delete vehicle permission and calls router.delete on confirm', () => {
        const confirmSpy = vi.spyOn(window, 'confirm').mockReturnValue(true);
        const { container } = renderWith({ 'delete vehicle': true });
        const deleteButton = container.querySelector('button.btn-danger');
        expect(deleteButton).not.toBeNull();
        fireEvent.click(deleteButton);
        expect(router.delete).toHaveBeenCalledWith('/vehicle.destroy/7');
        confirmSpy.mockRestore();
    });

    it('does not call router.delete when confirm is cancelled', () => {
        const confirmSpy = vi.spyOn(window, 'confirm').mockReturnValue(false);
        const { container } = renderWith({ 'delete vehicle': true });
        fireEvent.click(container.querySelector('button.btn-danger'));
        expect(router.delete).not.toHaveBeenCalled();
        confirmSpy.mockRestore();
    });
});
