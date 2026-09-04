import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import { router } from '@inertiajs/react';
import Booking from '@/Pages/Public/Booking/Index';

vi.mock('@inertiajs/react', () => ({
    usePage: vi.fn(() => ({ props: { translations: {} } })),
    Head: () => null,
    Link: ({ href, children, ...rest }) => <a href={href} {...rest}>{children}</a>,
    router: { get: vi.fn(), post: vi.fn(), delete: vi.fn() },
}));

globalThis.route = (name) => '/' + String(name).replace(/\./g, '/');

beforeEach(() => {
    // Each test sets its own router.get behavior; a leftover mockImplementation
    // from a prior test (e.g. one that always calls options.onSuccess) would
    // otherwise blow up a later test whose call site has no onSuccess at all.
    vi.mocked(router.get).mockReset();
});

const vehicles = [
    { id: 1, name: 'Renault Clio', model: '2024', daily_rate: 350, number_of_seats: 5, gearbox: 'Manuelle', fuel_type: 'Diesel', picture: null },
    { id: 2, name: 'Dacia Logan', model: '2025', daily_rate: 280, number_of_seats: 5, gearbox: 'Manuelle', fuel_type: 'Diesel', picture: null },
];
const places = [
    { id: 10, name: 'Bureau Principal', city: 'Tétouan' },
    { id: 11, name: 'Aéroport de Tanger', city: 'Tanger' },
];

function pickPlace(labelText, placeName) {
    fireEvent.click(screen.getByLabelText(labelText, { selector: 'button' }));
    fireEvent.click(screen.getByText(placeName));
}

function fillDatesStep() {
    fireEvent.change(screen.getByLabelText('Date de Prise en Charge'), { target: { value: '2026-07-01' } });
    fireEvent.change(screen.getByLabelText('Date de Retour'), { target: { value: '2026-07-04' } });
    pickPlace('Lieu de Prise en Charge', 'Bureau Principal');
    pickPlace('Lieu de Retour', 'Aéroport de Tanger');
}

describe('Public/Booking/Index', () => {
    it('renders every vehicle as a selectable card on step 1', () => {
        render(<Booking vehicles={vehicles} places={places} />);
        expect(screen.getByText('Renault Clio')).toBeInTheDocument();
        expect(screen.getByText('Dacia Logan')).toBeInTheDocument();
    });

    it('advances to the dates step after picking a car', () => {
        render(<Booking vehicles={vehicles} places={places} />);
        fireEvent.click(screen.getByText('Renault Clio'));

        expect(screen.getAllByText('Renault Clio').length).toBeGreaterThan(0);
        expect(screen.getByLabelText('Date de Prise en Charge')).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Continuer' })).toBeDisabled();
    });

    it('starts on the dates step directly when a vehicle is preselected via ?vehicle=', () => {
        render(<Booking vehicles={vehicles} places={places} preselectedVehicle="2" />);
        expect(screen.getByLabelText('Date de Prise en Charge')).toBeInTheDocument();
        expect(screen.queryByText('Renault Clio')).not.toBeInTheDocument();
    });

    it('disables the return date input until a pick-up date is chosen', () => {
        render(<Booking vehicles={vehicles} places={places} preselectedVehicle="1" />);

        expect(screen.getByLabelText('Date de Retour')).toBeDisabled();
        fireEvent.change(screen.getByLabelText('Date de Prise en Charge'), { target: { value: '2026-07-01' } });
        expect(screen.getByLabelText('Date de Retour')).toBeEnabled();
    });

    it('checks the selected car\'s availability for the chosen dates before advancing to step 3', () => {
        vi.mocked(router.get).mockImplementation((_url, _data, options) =>
            options.onSuccess({ props: { vehicles } }), // car 1 still present -> available
        );
        render(<Booking vehicles={vehicles} places={places} preselectedVehicle="1" />);

        fillDatesStep();
        fireEvent.click(screen.getByRole('button', { name: 'Continuer' }));

        expect(router.get).toHaveBeenCalledWith(
            '/reserve/create',
            expect.objectContaining({ start_date: '2026-07-01', end_date: '2026-07-04' }),
            expect.objectContaining({ only: ['vehicles'] }),
        );
        expect(screen.getByLabelText('Nom Complet')).toBeInTheDocument();
    });

    it('shows an error and stays on step 2 when the car is no longer available for those dates', () => {
        vi.mocked(router.get).mockImplementation((_url, _data, options) =>
            options.onSuccess({ props: { vehicles: [] } }), // car 1 no longer available
        );
        render(<Booking vehicles={vehicles} places={places} preselectedVehicle="1" />);

        fillDatesStep();
        fireEvent.click(screen.getByRole('button', { name: 'Continuer' }));

        expect(screen.getByText(/n'est plus disponible pour ces dates/)).toBeInTheDocument();
        expect(screen.queryByLabelText('Nom Complet')).not.toBeInTheDocument();
    });

    it('refetches the full fleet when going back from the dates step to the car step', () => {
        render(<Booking vehicles={vehicles} places={places} preselectedVehicle="1" />);

        fireEvent.click(screen.getByRole('button', { name: 'Retour' }));

        expect(router.get).toHaveBeenCalledWith(
            '/reserve/create',
            {},
            expect.objectContaining({ only: ['vehicles'] }),
        );
        expect(screen.getByText('Renault Clio')).toBeInTheDocument();
    });
});
