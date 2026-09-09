import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, within } from '@testing-library/react';
import { router, usePage } from '@inertiajs/react';
import CarDetails from '@/Pages/Public/CarDetails';

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

// `first_registration_year` is appended by the controller, because the column
// behind it is spelled with a U+FB01 ligature.
const car = {
    id: 2,
    name: 'Toyota RAV4',
    model: '2023',
    daily_rate: '350.00',
    gearbox: 'automatic',
    fuel_type: 'hybrid',
    number_of_seats: 5,
    kilometers: 42000,
    engine_type: '2.5',
    picture: 'toyota-rav4.jpg',
    notes: null,
    types: { id: 2, type: 'SUV' },
    first_registration_year: '2023',
};
const similarCars = [
    { id: 4, name: 'Renault Clio', daily_rate: '180.00', gearbox: 'manual', fuel_type: 'petrol', number_of_seats: 5, picture: 'renault-clio.jpg' },
];
const places = [
    { id: 3, name: 'Casablanca Airport', city: 'Casablanca' },
    { id: 4, name: 'Marrakech Centre', city: 'Marrakech' },
];

function renderDetails(props = {}) {
    return render(<CarDetails car={car} similarCars={similarCars} places={places} {...props} />);
}

describe('CarDetails — the image path', () => {
    /**
     * The page built `/storage/${picture}`; every other caller in the app uses
     * `/storage/upload/picture/`, which is where the files are. The hero and
     * every similar-car thumbnail were 404s on the live storefront.
     */
    it('points at the directory the pictures are actually stored in', () => {
        renderDetails();

        const hero = screen.getByAltText('Toyota RAV4');
        expect(hero.getAttribute('src')).toBe('/storage/upload/picture/toyota-rav4.jpg');

        const similar = screen.getByAltText('Renault Clio');
        expect(similar.getAttribute('src')).toBe('/storage/upload/picture/renault-clio.jpg');
    });

    it('falls back to the placeholder when a car has no picture', () => {
        renderDetails({ car: { ...car, picture: null } });

        expect(screen.getByAltText('Toyota RAV4').getAttribute('src'))
            .toBe('/assets/images/client/default-car.jpg');
    });
});

describe('CarDetails — specifications', () => {
    it('shows the registration year rather than N/A', () => {
        renderDetails();

        expect(screen.getAllByText('2023').length).toBeGreaterThan(0);
        expect(screen.queryByText('N/A')).not.toBeInTheDocument();
    });

    it('translates the stored gearbox and fuel slugs', () => {
        renderDetails();

        expect(screen.getByText('Automatique')).toBeInTheDocument();
        expect(screen.getByText('Hybride')).toBeInTheDocument();
        expect(screen.queryByText('automatic')).not.toBeInTheDocument();
    });

    /** A spec the row does not carry is left out, not printed as "N/A". */
    it('omits a spec the vehicle has no value for', () => {
        renderDetails({ car: { ...car, engine_type: null, kilometers: null } });

        expect(screen.queryByText('Moteur')).not.toBeInTheDocument();
        expect(screen.queryByText('Kilométrage')).not.toBeInTheDocument();
        expect(screen.getByText('Places')).toBeInTheDocument();
    });
});

describe('CarDetails — the claims it no longer makes', () => {
    /**
     * The page carried two reviews with invented names and lorem-ipsum bodies,
     * plus a five-star rating and "2 Reviews" on the car and on every similar
     * car. No review feature exists. This URL is in sitemap.xml.
     */
    it('carries no reviews and no invented ratings', () => {
        const { container } = renderDetails();

        expect(screen.queryByText(/reviews/i)).not.toBeInTheDocument();
        expect(screen.queryByText(/Khalid bensdik/i)).not.toBeInTheDocument();
        expect(screen.queryByText(/lorem ipsum/i)).not.toBeInTheDocument();
        expect(container.querySelectorAll('.fill-yellow-400')).toHaveLength(0);
    });

    /**
     * "Price Table (by day of the week)" printed the same daily_rate seven
     * times. There is no per-weekday pricing in the schema or anywhere else.
     */
    it('carries no per-weekday price table', () => {
        renderDetails();

        expect(screen.queryByText(/price table/i)).not.toBeInTheDocument();
        expect(screen.queryByText('Monday')).not.toBeInTheDocument();
        expect(screen.queryByText('Lundi')).not.toBeInTheDocument();
    });
});

describe('CarDetails — booking card', () => {
    it('prices the range and hands the car, location and dates to the wizard', () => {
        renderDetails();

        fireEvent.click(screen.getByLabelText('Lieu de prise en charge', { selector: 'button' }));
        fireEvent.click(screen.getByRole('option', { name: 'Casablanca Airport' }));
        fireEvent.change(screen.getByLabelText('Date de prise en charge'), { target: { value: '2026-10-05' } });
        fireEvent.change(screen.getByLabelText('Date de restitution'), { target: { value: '2026-10-09' } });

        // 4 days x 350 Dh
        const card = screen.getByRole('form', { name: 'Réserver ce véhicule' });
        expect(within(card).getByText('4 jours × 350 Dh')).toBeInTheDocument();
        expect(within(card).getAllByText('1400 Dh').length).toBeGreaterThan(0);

        fireEvent.click(within(card).getByRole('button', { name: /réserver/i }));

        expect(router.get).toHaveBeenCalledWith('/reserve/create', {
            vehicle: 2,
            place: '3',
            start_date: '2026-10-05',
            end_date: '2026-10-09',
            start_time: '09:00',
            end_time: '18:00',
        });
    });

    it('asks for dates before it shows a total', () => {
        renderDetails();

        expect(screen.getByText('Choisissez vos dates pour voir le total.')).toBeInTheDocument();
        expect(screen.queryByText('Total estimé')).not.toBeInTheDocument();
    });

    /**
     * The car alone is still a complete handoff — the wizard opens on its date
     * step with the vehicle chosen, which is what the fleet cards do too.
     */
    it('can hand over the car alone', () => {
        renderDetails();

        const card = screen.getByRole('form', { name: 'Réserver ce véhicule' });
        fireEvent.click(within(card).getByRole('button', { name: /réserver/i }));

        expect(router.get).toHaveBeenCalledWith('/reserve/create', { vehicle: 2 });
    });

    /** Nothing on this page takes money, and it says so. */
    it('states that no payment is taken online', () => {
        renderDetails();

        expect(screen.getByText(/aucun paiement en ligne/i)).toBeInTheDocument();
    });
});
