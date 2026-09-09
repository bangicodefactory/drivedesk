import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, within } from '@testing-library/react';
import { router, usePage } from '@inertiajs/react';
import Landing from '@/Pages/Public/Landing';

vi.mock('@inertiajs/react', () => ({
    usePage: vi.fn(() => ({ props: { translations: {} } })),
    Head: () => null,
    Link: ({ href, children, ...rest }) => <a href={href} {...rest}>{children}</a>,
    router: { get: vi.fn(), post: vi.fn() },
}));

globalThis.route = (name) => '/' + String(name).replace(/\./g, '/');

beforeEach(() => {
    vi.mocked(router.get).mockReset();
    vi.mocked(usePage).mockReturnValue({ props: { translations: {} } });
});

// Shaped like HomeController::landingProps() — `type` is the VehicleType id,
// and gearbox/fuel_type are the lowercase slugs the column actually stores
// (App\Models\Vehicle::$gearbox / ::$fuelType), not display text.
const vehicles = [
    { id: 2, type: '2', name: 'Toyota RAV4', model: '2023', daily_rate: '350.00', number_of_seats: 5, gearbox: 'automatic', fuel_type: 'hybrid', picture: 'toyota-rav4.jpg' },
    { id: 4, type: '4', name: 'Renault Clio', model: '2023', daily_rate: '180.00', number_of_seats: 5, gearbox: 'manual', fuel_type: 'petrol', picture: 'renault-clio.jpg' },
    { id: 8, type: '5', name: 'Ford Transit', model: '2021', daily_rate: '450.00', number_of_seats: 9, gearbox: 'manual', fuel_type: 'diesel', picture: null },
];
const vehicleTypes = [
    { id: 2, type: 'SUV' },
    { id: 4, type: 'Hatchback' },
    { id: 5, type: 'Minivan' },
    { id: 6, type: 'Cabriolet' },
];
const places = [
    { id: 3, name: 'Casablanca Airport' },
    { id: 4, name: 'Marrakech Centre' },
];

function renderLanding(props = {}) {
    return render(<Landing vehicles={vehicles} vehicleTypes={vehicleTypes} places={places} {...props} />);
}

describe('Landing — fleet', () => {
    it('renders every vehicle offered for rent', () => {
        renderLanding();

        expect(screen.getByText('Toyota RAV4')).toBeInTheDocument();
        expect(screen.getByText('Renault Clio')).toBeInTheDocument();
        expect(screen.getByText('Ford Transit')).toBeInTheDocument();
    });

    /**
     * The column stores 'automatic' / 'hybrid'; the admin screens translate
     * those through Vehicle::$gearbox server-side but the storefront printed
     * the raw slug, so a French visitor read "automatic • hybrid" in the
     * middle of otherwise French copy.
     */
    it('translates the stored gearbox and fuel slugs', () => {
        renderLanding();

        const rav4 = screen.getByText('Toyota RAV4').closest('article');
        expect(within(rav4).getByText('Automatique')).toBeInTheDocument();
        expect(within(rav4).getByText('Hybride')).toBeInTheDocument();
        expect(within(rav4).queryByText('automatic')).not.toBeInTheDocument();
        expect(within(rav4).queryByText('hybrid')).not.toBeInTheDocument();
    });

    it('offers a filter only for types the fleet actually contains', () => {
        renderLanding();

        expect(screen.getByRole('button', { name: 'SUV' })).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Minivan' })).toBeInTheDocument();
        // No car has type 6, so a Cabriolet chip could only ever empty the grid.
        expect(screen.queryByRole('button', { name: 'Cabriolet' })).not.toBeInTheDocument();
    });

    it('narrows the grid to the chosen type', () => {
        renderLanding();

        fireEvent.click(screen.getByRole('button', { name: 'SUV' }));

        expect(screen.getByText('Toyota RAV4')).toBeInTheDocument();
        expect(screen.queryByText('Renault Clio')).not.toBeInTheDocument();
        expect(screen.queryByText('Ford Transit')).not.toBeInTheDocument();

        fireEvent.click(screen.getByRole('button', { name: 'Tous' }));
        expect(screen.getByText('Renault Clio')).toBeInTheDocument();
    });

    /**
     * `available_for_rent` means "we rent this car", not "this car is free on
     * your dates" — nothing on this page has looked at a booking. The heading
     * counts the fleet and the note says who resolves availability; the word
     * "disponibles" belongs on /reserve, which runs the real overlap query.
     */
    it('counts the fleet without claiming the cars are free', () => {
        renderLanding();

        expect(screen.getByRole('heading', { name: /3 véhicules/i })).toBeInTheDocument();
        expect(screen.queryByText(/véhicules disponibles/i)).not.toBeInTheDocument();
    });
});

describe('Landing — the claims it no longer makes', () => {
    /**
     * The storefront is in sitemap.xml (BAN-262). Four testimonials with
     * invented names, a hardcoded five-star rating and "2 Reviews" on every
     * card are not placeholder copy on a crawlable page — they are published
     * claims about a real business, and no review feature exists to back them.
     */
    it('carries no testimonials and no invented ratings', () => {
        const { container } = renderLanding();

        expect(screen.queryByText(/témoignages/i)).not.toBeInTheDocument();
        expect(screen.queryByText(/testimonials/i)).not.toBeInTheDocument();
        expect(screen.queryByText(/reviews/i)).not.toBeInTheDocument();
        expect(screen.queryByText(/avis/i)).not.toBeInTheDocument();
        expect(container.querySelectorAll('.fill-yellow-400')).toHaveLength(0);
    });

    /** "2 Airports Served" / "7/7 Days a Week" counted nothing. */
    it('carries no unsourced statistics', () => {
        renderLanding();

        expect(screen.queryByText(/7\/7/)).not.toBeInTheDocument();
        expect(screen.queryByText(/airports served/i)).not.toBeInTheDocument();
    });
});

describe('Landing — search panel', () => {
    it('hands the dates and the location to the booking wizard', () => {
        renderLanding();

        fireEvent.click(screen.getByLabelText('Lieu de prise en charge', { selector: 'button' }));
        // getByRole('option'), not getByText: Radix also renders a visually
        // hidden native <select> for form/a11y purposes, so the place name
        // matches twice and getByText refuses to choose.
        fireEvent.click(screen.getByRole('option', { name: 'Casablanca Airport' }));
        fireEvent.change(screen.getByLabelText('Date de prise en charge'), { target: { value: '2026-10-05' } });
        fireEvent.change(screen.getByLabelText('Date de restitution'), { target: { value: '2026-10-09' } });

        fireEvent.click(screen.getByRole('button', { name: /voir les voitures/i }));

        expect(router.get).toHaveBeenCalledWith('/reserve/create', {
            place: '3',
            start_date: '2026-10-05',
            end_date: '2026-10-09',
            start_time: '09:00',
            end_time: '18:00',
        });
    });

    /**
     * An empty search is a reasonable "just show me the cars", so it goes to
     * the wizard's full fleet rather than refusing to move.
     */
    it('still goes to the wizard when nothing is filled in', () => {
        renderLanding();

        fireEvent.click(screen.getByRole('button', { name: /voir les voitures/i }));

        expect(router.get).toHaveBeenCalledWith('/reserve/create', {});
    });

    /** A return date already before the new pick-up date is cleared, not kept. */
    it('clears a return date that the new pick-up date invalidates', () => {
        renderLanding();

        const start = screen.getByLabelText('Date de prise en charge');
        const end = screen.getByLabelText('Date de restitution');

        fireEvent.change(start, { target: { value: '2026-10-05' } });
        fireEvent.change(end, { target: { value: '2026-10-09' } });
        fireEvent.change(start, { target: { value: '2026-10-20' } });

        expect(end.value).toBe('');
    });
});

describe('Landing — payment reassurance follows the flag', () => {
    /**
     * BAN-334. Turning booking_payment on made this card lie: it promised
     * "aucun prélèvement en ligne" while the wizard, two steps later, offered a
     * tile headed "Paiement en Ligne". Exactly the stale-copy failure that PR
     * fixed elsewhere, introduced by the same PR here.
     */
    it('promises cash-only while card payment is off', () => {
        vi.mocked(usePage).mockReturnValue({
            props: { translations: {}, client: { features: { booking_payment: false } } },
        });
        renderLanding();

        expect(screen.getByText(/aucun prélèvement en ligne/i)).toBeInTheDocument();
    });

    it('stops promising cash-only once card payment is offered', () => {
        vi.mocked(usePage).mockReturnValue({
            props: { translations: {}, client: { features: { booking_payment: true } } },
        });
        renderLanding();

        expect(screen.queryByText(/aucun prélèvement en ligne/i)).not.toBeInTheDocument();
        expect(screen.getByText(/paiement à l'agence ou par carte/i)).toBeInTheDocument();
        // Still true in both modes, and the part that actually reassures.
        expect(screen.getByText(/rien n'est prélevé au moment de la demande/i)).toBeInTheDocument();
    });

    /** Whatever the flag says, PayPal is not a method this app offers. */
    it('never names PayPal', () => {
        for (const booking_payment of [true, false]) {
            vi.mocked(usePage).mockReturnValue({
                props: { translations: {}, client: { features: { booking_payment } } },
            });
            const { container, unmount } = renderLanding();

            expect(container.textContent).not.toMatch(/paypal/i);
            unmount();
        }
    });
});
