import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { router } from '@inertiajs/react';
import Booking from '@/Pages/Public/Booking/Index';
import { usePage } from '@inertiajs/react';

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
    // Default: online payment off, spelled the way the server spells it.
    // Omitting `client` entirely -- which this used to do -- is a shape
    // HandleInertiaRequests never produces, so the negative test below would
    // have passed because the prop was absent rather than because the flag was
    // false. That is the same mistake this file's subject bug was made of.
    vi.mocked(usePage).mockReturnValue({
        props: { translations: {}, client: { features: { booking_payment: false } } },
    });
});

/**
 * Turn the online-payment option on for the tests that are about it.
 *
 * Shaped like the real shared props: HandleInertiaRequests puts the flags
 * under `client.features`, and there is no top-level `features`. The mock used
 * to invent one, which is how the page came to read a path that does not
 * exist -- the test agreed with the bug instead of catching it.
 */
function withOnlinePayment() {
    vi.mocked(usePage).mockReturnValue({
        props: { translations: {}, client: { features: { booking_payment: true } } },
    });
}

const vehicles = [
    // Lowercase slugs, the way the column actually stores them
    // (App\Models\Vehicle::$gearbox / ::$fuelType).
    { id: 1, name: 'Renault Clio', model: '2024', daily_rate: 350, number_of_seats: 5, gearbox: 'manual', fuel_type: 'diesel', picture: null },
    { id: 2, name: 'Dacia Logan', model: '2025', daily_rate: 280, number_of_seats: 5, gearbox: 'manual', fuel_type: 'diesel', picture: null },
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

function fillCustomerStep() {
    fireEvent.change(screen.getByLabelText('Nom Complet'), { target: { value: 'Alice Dupont' } });
    fireEvent.change(screen.getByLabelText('Nationalité'), { target: { value: 'Française' } });
    fireEvent.change(screen.getByLabelText('Numéro de Téléphone'), { target: { value: '+212600000000' } });
    fireEvent.change(screen.getByLabelText('Adresse Email'), { target: { value: 'alice@example.com' } });
    fireEvent.click(screen.getByRole('checkbox'));
}

/**
 * Drives the wizard from a preselected car straight to the payment step.
 * The step 3 -> 4 transition runs RHF's async `trigger()` validation, so
 * `fireEvent.click` alone returns before the step actually changes — waiting
 * for a step-4-only element is what actually lets that microtask settle.
 */
async function reachPaymentStep() {
    vi.mocked(router.get).mockImplementation((_url, _data, options) =>
        options.onSuccess({ props: { vehicles } }),
    );
    render(<Booking vehicles={vehicles} places={places} preselectedVehicle="1" />);
    fillDatesStep();
    fireEvent.click(screen.getByRole('button', { name: 'Continuer' }));
    await screen.findByLabelText('Nom Complet');
    fillCustomerStep();
    fireEvent.click(screen.getByRole('button', { name: 'Continuer' }));
    await screen.findByText('Comment souhaitez-vous payer ?');
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

    describe('payment step', () => {
        it('reaches the payment step after filling in customer info, with submit disabled until a method is chosen', async () => {
            await reachPaymentStep();

            expect(screen.getByText('Comment souhaitez-vous payer ?')).toBeInTheDocument();
            expect(screen.getByRole('button', { name: 'Compléter la Réservation' })).toBeDisabled();
        });

        it('enables submit immediately when "Paiement à la Livraison" is chosen', async () => {
            await reachPaymentStep();

            fireEvent.click(screen.getByText('Paiement à la Livraison'));

            expect(screen.getByRole('button', { name: 'Compléter la Réservation' })).toBeEnabled();
            expect(screen.queryByText('Choisissez votre moyen de paiement en ligne')).not.toBeInTheDocument();
        });

        it('offers no online payment while booking_payment is off', async () => {
            await reachPaymentStep();

            // No gateway is wired to anything, and the flag is off by default
            // for that reason, so cash at the agency is the only choice.
            expect(screen.queryByText('Paiement en Ligne')).not.toBeInTheDocument();
            expect(screen.getByText('Paiement à la Livraison')).toBeInTheDocument();
        });

        it('reveals CMI and keeps submit disabled until it is picked', async () => {
            withOnlinePayment();
            await reachPaymentStep();

            fireEvent.click(screen.getByText('Paiement en Ligne'));
            expect(screen.getByText('Choisissez votre moyen de paiement en ligne')).toBeInTheDocument();
            expect(screen.getByRole('button', { name: 'Compléter la Réservation' })).toBeDisabled();

            fireEvent.click(screen.getByText('CMI'));
            expect(screen.getByRole('button', { name: 'Compléter la Réservation' })).toBeEnabled();
        });

        it('submits the chosen payment_preference to booking.store_request', async () => {
            withOnlinePayment();
            await reachPaymentStep();

            fireEvent.click(screen.getByText('Paiement en Ligne'));
            fireEvent.click(screen.getByText('CMI'));
            fireEvent.click(screen.getByRole('button', { name: 'Compléter la Réservation' }));

            // handleSubmit's own zod validation is async too.
            await waitFor(() => expect(router.post).toHaveBeenCalledWith(
                '/booking/store_request',
                expect.objectContaining({ payment_preference: 'cmi' }),
                expect.anything(),
            ));
        });

        it('goes back to the customer step from the payment step', async () => {
            await reachPaymentStep();

            fireEvent.click(screen.getByRole('button', { name: 'Retour' }));

            expect(screen.getByLabelText('Nom Complet')).toBeInTheDocument();
        });
    });
});

describe('Booking wizard — step 1', () => {
    /**
     * vehicleSpecs.js exists because the storefront was printing the raw
     * column slugs. It was wired into the landing, the detail page, the
     * similar-car cards and the summary rail -- but not into this grid, which
     * is the largest list of cars on the storefront and step 1 of the flow.
     */
    it('translates the stored gearbox and fuel slugs', () => {
        render(<Booking vehicles={vehicles} places={places} />);

        expect(screen.getAllByText(/Manuelle/).length).toBeGreaterThan(0);
        expect(screen.getAllByText(/Diesel/).length).toBeGreaterThan(0);
        expect(screen.queryByText(/manual/)).not.toBeInTheDocument();
        expect(screen.queryByText(/petrol/)).not.toBeInTheDocument();
    });

    /**
     * Step 1 priced in MAD while the summary rail sitting beside step 2 said
     * Dh, so the two halves of one flow disagreed on the currency.
     */
    it('prices in the same currency as the rest of the flow', () => {
        render(<Booking vehicles={vehicles} places={places} />);

        expect(screen.queryByText(/MAD/)).not.toBeInTheDocument();
        expect(screen.getAllByText(/350 Dh/).length).toBeGreaterThan(0);
    });
});
