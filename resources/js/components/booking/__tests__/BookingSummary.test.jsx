import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen } from '@testing-library/react';
import { usePage } from '@inertiajs/react';
import BookingSummary from '@/components/booking/BookingSummary';

// The rail prints a price, and the currency symbol is a per-tenant setting
// shared as branding.currencySymbol (useCurrency).
vi.mock('@inertiajs/react', () => ({
    usePage: vi.fn(),
}));

beforeEach(() => {
    vi.mocked(usePage).mockReturnValue({ props: { branding: { currencySymbol: 'Dh' } } });
});

const t = (key, fallback = key) => fallback;

const vehicle = {
    id: 4,
    name: 'Renault Clio',
    model: '2023',
    daily_rate: '180.00',
    gearbox: 'manual',
    fuel_type: 'petrol',
    number_of_seats: 5,
    picture: 'renault-clio.jpg',
};
const places = [
    { id: 3, name: 'Casablanca Airport' },
    { id: 4, name: 'Marrakech Centre' },
];

function renderSummary(props = {}) {
    return render(
        <BookingSummary
            vehicle={vehicle}
            places={places}
            pickupId="3"
            dropOffId="4"
            startDate="2026-10-05"
            startTime="09:00"
            endDate="2026-10-09"
            endTime="18:00"
            days={4}
            total={720}
            t={t}
            {...props}
        />,
    );
}

describe('BookingSummary', () => {
    it('renders nothing until a car is chosen', () => {
        const { container } = renderSummary({ vehicle: null });

        expect(container).toBeEmptyDOMElement();
    });

    it('names the car and its specs, translating the stored slugs', () => {
        renderSummary();

        expect(screen.getByText('Renault Clio')).toBeInTheDocument();
        expect(screen.getByText('2023 · Manuelle · Essence')).toBeInTheDocument();
    });

    it('resolves place ids to their names', () => {
        renderSummary();

        expect(screen.getByText('Casablanca Airport')).toBeInTheDocument();
        expect(screen.getByText('Marrakech Centre')).toBeInTheDocument();
    });

    /**
     * A row with nothing behind it is left out rather than rendered empty —
     * otherwise the rail reads as though data went missing between steps.
     */
    it('omits rows it has no value for', () => {
        renderSummary({ dropOffId: '', endDate: '', endTime: '' });

        expect(screen.getByText('Lieu de Prise en Charge')).toBeInTheDocument();
        expect(screen.queryByText('Lieu de Retour')).not.toBeInTheDocument();
        expect(screen.queryByText('Au')).not.toBeInTheDocument();
    });

    it('shows the total and how it was reached', () => {
        renderSummary();

        expect(screen.getByText('720 Dh')).toBeInTheDocument();
        expect(screen.getByText(/4 jours × 180 Dh/)).toBeInTheDocument();
    });

    it('asks for dates instead of showing a total of zero', () => {
        renderSummary({ days: 0, total: 0, startDate: '', endDate: '' });

        expect(screen.getByText('Choisissez vos dates pour voir le total.')).toBeInTheDocument();
        expect(screen.queryByText('Total estimé')).not.toBeInTheDocument();
    });

    /** Singular, so it does not read "1 jours". */
    it('uses the singular for a one-day rental', () => {
        renderSummary({ days: 1, total: 180 });

        expect(screen.getByText(/1 jour × 180 Dh/)).toBeInTheDocument();
    });

    /**
     * CURRENCY_SYMBOL is a per-tenant Setting. The rail hardcoded "Dh", which
     * is right for this deployment and wrong for the next one (§10.2 rule 1).
     */
    it("prints the tenant's own currency symbol", () => {
        vi.mocked(usePage).mockReturnValue({ props: { branding: { currencySymbol: '€' } } });
        renderSummary();

        expect(screen.getByText('720 €')).toBeInTheDocument();
        expect(screen.queryByText('720 Dh')).not.toBeInTheDocument();
    });

    /**
     * The reassurance a visitor most wants at the moment they are asked for a
     * phone number, so it is present on every step the rail appears on.
     */
    it('always states that nothing is paid online', () => {
        renderSummary();

        expect(screen.getByText(/aucun paiement en ligne/i)).toBeInTheDocument();
    });
});

describe('BookingSummary — payment note follows the flag', () => {
    it('says cash-only while card payment is off', () => {
        vi.mocked(usePage).mockReturnValue({
            props: { branding: { currencySymbol: 'Dh' }, client: { features: { booking_payment: false } } },
        });
        renderSummary();

        expect(screen.getByText(/aucun paiement en ligne/i)).toBeInTheDocument();
    });

    /**
     * The rail sits beside step 4, where the card tile is. Denying that the
     * option exists, right next to the option, is worse than saying nothing.
     */
    it('stops denying online payment once the card tile exists', () => {
        vi.mocked(usePage).mockReturnValue({
            props: { branding: { currencySymbol: 'Dh' }, client: { features: { booking_payment: true } } },
        });
        renderSummary();

        expect(screen.queryByText(/aucun paiement en ligne/i)).not.toBeInTheDocument();
        expect(screen.getByText(/rien n'est prélevé maintenant/i)).toBeInTheDocument();
    });
});
