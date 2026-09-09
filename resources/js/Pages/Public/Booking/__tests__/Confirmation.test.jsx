import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen } from '@testing-library/react';
import { usePage } from '@inertiajs/react';
import Confirmation from '@/Pages/Public/Booking/Confirmation';

vi.mock('@inertiajs/react', () => ({
    usePage: vi.fn(),
    Head: () => null,
    Link: ({ href, children, ...rest }) => <a href={href} {...rest}>{children}</a>,
}));

globalThis.route = (name) => '/' + String(name).replace(/\./g, '/');

const sharedProps = {
    translations: {},
    branding: { appName: 'DriveDesk' },
    contact: { whatsapp: '212600000000', hoursWeekday: '09:00 – 19:00' },
};

beforeEach(() => {
    vi.mocked(usePage).mockReturnValue({ props: sharedProps });
});

const props = {
    reference: 'BR-00001',
    car: { name: 'Renault Clio', model: '2023', picture: 'renault-clio.jpg' },
    pickupPlace: 'Casablanca Airport',
    dropOffPlace: 'Marrakech Centre',
    startDate: '2026-10-05',
    startTime: '09:00',
    endDate: '2026-10-09',
    endTime: '18:00',
    days: 4,
    amount: 720,
    paymentPreference: 'cash',
};

function renderConfirmation(overrides = {}) {
    return render(<Confirmation {...props} {...overrides} />);
}

describe('Confirmation', () => {
    it('leads with the reference, not with a banner', () => {
        renderConfirmation();

        expect(screen.getByText('BR-00001')).toBeInTheDocument();
        expect(screen.getByRole('heading', { level: 1, name: /demande envoyée/i })).toBeInTheDocument();
    });

    /**
     * The single most important sentence on the page. A visitor who has just
     * typed a phone number into a rental site needs to know the money side has
     * not started — booking_payment is off and nothing here can charge a card.
     */
    it('says plainly that nothing has been charged', () => {
        renderConfirmation();

        expect(screen.getByText(/rien n'a été prélevé/i)).toBeInTheDocument();
    });

    it('summarises what was requested', () => {
        renderConfirmation();

        expect(screen.getByText('Renault Clio 2023')).toBeInTheDocument();
        expect(screen.getByText('Casablanca Airport — 2026-10-05 09:00')).toBeInTheDocument();
        expect(screen.getByText('Marrakech Centre — 2026-10-09 18:00')).toBeInTheDocument();
        expect(screen.getByText('4 jours')).toBeInTheDocument();
        expect(screen.getByText('720 Dh')).toBeInTheDocument();
    });

    /**
     * Drawn from the rental agreement the client prints on the contract
     * (terms.rental_agreement, articles 2 and 4) — a pick-up fails without
     * these, and this is the last screen before it.
     */
    it('says what to bring at pick-up', () => {
        renderConfirmation();

        expect(screen.getByRole('heading', { name: /à apporter au retrait/i })).toBeInTheDocument();
        expect(screen.getByText(/permis de conduire original/i)).toBeInTheDocument();
        expect(screen.getByText(/CIN ou passeport/i)).toBeInTheDocument();
    });

    it('carries the reference into the WhatsApp message', () => {
        renderConfirmation();

        const link = screen.getByRole('link', { name: /whatsapp/i });
        expect(link.getAttribute('href')).toContain('wa.me/212600000000');
        expect(decodeURIComponent(link.getAttribute('href'))).toContain('BR-00001');
    });

    it('offers no WhatsApp button when the tenant has no number', () => {
        vi.mocked(usePage).mockReturnValue({
            props: { ...sharedProps, contact: { hoursWeekday: '09:00 – 19:00' } },
        });
        renderConfirmation();

        expect(screen.queryByRole('link', { name: /whatsapp/i })).not.toBeInTheDocument();
    });

    /** Hours are a Setting; a tenant that never filled them in shows none. */
    it('shows only the opening hours that are set', () => {
        renderConfirmation();

        expect(screen.getByText('Lun – Ven')).toBeInTheDocument();
        expect(screen.queryByText('Dim')).not.toBeInTheDocument();
    });

    /**
     * Online payment is not integrated: there is no gateway call anywhere, so
     * the copy must promise a follow-up rather than imply the payment is done.
     */
    it('does not imply an online payment has gone through', () => {
        renderConfirmation({ paymentPreference: 'cmi' });

        expect(screen.getByText(/finaliser votre paiement en ligne/i)).toBeInTheDocument();
        expect(screen.getByText(/rien n'a été prélevé/i)).toBeInTheDocument();
    });
});
