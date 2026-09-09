import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { router, usePage } from '@inertiajs/react';
import Contact from '@/Pages/Public/Contact';

vi.mock('@inertiajs/react', () => ({
    usePage: vi.fn(),
    Head: () => null,
    Link: ({ href, children, ...rest }) => <a href={href} {...rest}>{children}</a>,
    router: { get: vi.fn(), post: vi.fn(), put: vi.fn(), patch: vi.fn(), delete: vi.fn() },
}));

globalThis.route = (name) => '/' + String(name).replace(/\./g, '/');

const fullContact = {
    phone: '+212 5 22 00 00 00',
    whatsapp: '212600000000',
    email: 'agence@example.com',
    address: '12 rue Example, Casablanca',
    hoursWeekday: '09:00 – 19:00',
    hoursSaturday: '09:00 – 13:00',
};

function mockShared(contact = fullContact) {
    vi.mocked(usePage).mockReturnValue({
        props: { translations: {}, branding: { appName: 'DriveDesk' }, contact },
    });
}

beforeEach(() => {
    vi.mocked(router.post).mockReset();
    mockShared();
});

describe('Contact — channels', () => {
    it('is no longer the placeholder page', () => {
        render(<Contact canSendMessage />);

        expect(screen.queryByText(/placeholder contact page/i)).not.toBeInTheDocument();
        expect(screen.getByRole('heading', { level: 1, name: /une question/i })).toBeInTheDocument();
    });

    it('links each channel the way it is meant to be used', () => {
        render(<Contact canSendMessage />);

        expect(screen.getByRole('link', { name: /whatsapp/i }).getAttribute('href'))
            .toBe('https://wa.me/212600000000');
        expect(screen.getByRole('link', { name: /téléphone/i }).getAttribute('href'))
            .toBe('tel:+212 5 22 00 00 00');
        expect(screen.getByRole('link', { name: /email/i }).getAttribute('href'))
            .toBe('mailto:agence@example.com');
    });

    /**
     * Every channel is a Setting an owner fills in. One that was never filled
     * in is left out — not rendered as an empty card or a `tel:` link to
     * nothing.
     */
    it('leaves out channels the tenant never configured', () => {
        mockShared({ phone: '+212 5 22 00 00 00' });
        render(<Contact canSendMessage />);

        expect(screen.getByRole('link', { name: /téléphone/i })).toBeInTheDocument();
        expect(screen.queryByRole('link', { name: /whatsapp/i })).not.toBeInTheDocument();
        expect(screen.queryByRole('link', { name: /email/i })).not.toBeInTheDocument();
        // The address itself, not the word "agence" -- the phone card's own
        // description says "Pour joindre l'agence directement."
        expect(screen.queryByText('12 rue Example, Casablanca')).not.toBeInTheDocument();
    });

    it('shows only the opening hours that are set', () => {
        render(<Contact canSendMessage />);

        expect(screen.getByText('Lun – Ven')).toBeInTheDocument();
        expect(screen.getByText('Sam')).toBeInTheDocument();
        expect(screen.queryByText('Dim')).not.toBeInTheDocument();
    });

    it('omits the hours card entirely when none are set', () => {
        mockShared({ phone: '+212 5 22 00 00 00' });
        render(<Contact canSendMessage />);

        // The heading, not any mention of the word: the form's own subtitle
        // reads "Nous répondons pendant les horaires d'ouverture."
        expect(screen.queryByRole('heading', { name: /horaires/i })).not.toBeInTheDocument();
    });
});

describe('Contact — the form', () => {
    /**
     * The form exists only where the deployment has an address to deliver to.
     * Showing one otherwise is how POST /newsletter/subscribe ended up
     * thanking people for an address it discards.
     */
    it('is withheld, with an explanation, when there is nowhere to deliver', () => {
        render(<Contact canSendMessage={false} />);

        expect(screen.queryByRole('button', { name: /envoyer/i })).not.toBeInTheDocument();
        expect(screen.getByText(/formulaire est indisponible/i)).toBeInTheDocument();
        // The channels that do work are still offered.
        expect(screen.getByRole('link', { name: /whatsapp/i })).toBeInTheDocument();
    });

    it('posts a completed message to the contact endpoint', async () => {
        render(<Contact canSendMessage />);

        fireEvent.change(screen.getByLabelText('Nom'), { target: { value: 'Yassine Berrada' } });
        fireEvent.change(screen.getByLabelText('Adresse Email'), { target: { value: 'yassine@example.com' } });
        fireEvent.change(screen.getByLabelText('Votre message'), { target: { value: 'Bonjour, une question.' } });

        fireEvent.click(screen.getByRole('button', { name: /envoyer/i }));

        await waitFor(() => expect(router.post).toHaveBeenCalled());
        expect(router.post.mock.calls[0][0]).toBe('/contact/send');
        expect(router.post.mock.calls[0][1]).toMatchObject({
            name: 'Yassine Berrada',
            email: 'yassine@example.com',
            message: 'Bonjour, une question.',
        });
    });

    /** Client-side validation is UX only, but it should still stop an empty post. */
    it('does not post an incomplete message', async () => {
        render(<Contact canSendMessage />);

        fireEvent.click(screen.getByRole('button', { name: /envoyer/i }));

        await waitFor(() => expect(screen.getByText('Votre nom est requis.')).toBeInTheDocument());
        expect(router.post).not.toHaveBeenCalled();
    });

    it('offers the booking reference as an optional field', () => {
        render(<Contact canSendMessage />);

        expect(screen.getByLabelText(/référence/i)).toBeInTheDocument();
        expect(screen.getByText(/facultatif/i)).toBeInTheDocument();
    });
});
