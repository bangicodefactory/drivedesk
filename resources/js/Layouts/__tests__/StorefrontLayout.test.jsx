import { describe, it, expect, vi, afterEach } from 'vitest';
import { render, screen } from '@testing-library/react';
import { usePage } from '@inertiajs/react';
import StorefrontLayout from '@/Layouts/StorefrontLayout';

// Stand-in tenant. Deliberately not a real agency's details: this file is
// committed, and the layout is core product -- nothing here should read as one
// particular customer's contact card.
const mockProps = {
    branding: { appName: 'DriveDesk', logoUrl: null },
    contact: {
        phone: '+212 500-000000',
        whatsapp: '212500000000',
        email: 'contact@example.com',
        address: '1 Example Street, Casablanca 20000, Morocco',
        hoursWeekday: '8:00 AM - 8:00 PM',
        hoursSaturday: '9:00 AM - 6:00 PM',
        hoursSunday: '10:00 AM - 4:00 PM',
        facebookUrl: 'https://www.facebook.com/example/',
        instagramUrl: 'https://www.instagram.com/example/',
    },
    translations: {},
    flash: {},
    locale: 'fr',
};

vi.mock('@inertiajs/react', () => ({
    usePage: vi.fn(() => ({ props: mockProps, url: '/' })),
    Link: ({ href, children, ...rest }) => <a href={href} {...rest}>{children}</a>,
    router: { on: vi.fn(() => () => {}) },
}));

globalThis.route = (name, param) => {
    if (name === 'contact') return '/contact';
    if (name === 'language.change') return `/language/${param}`;
    return '/' + String(name).replace(/\./g, '/');
};

describe('StorefrontLayout', () => {
    it('renders the app name and children', () => {
        render(<StorefrontLayout><p>Page content</p></StorefrontLayout>);
        expect(screen.getAllByText('DriveDesk').length).toBeGreaterThan(0);
        expect(screen.getByText('Page content')).toBeInTheDocument();
    });

    it('shows the business contact info in the footer', () => {
        render(<StorefrontLayout><p>Page content</p></StorefrontLayout>);
        expect(screen.getByText(mockProps.contact.phone)).toBeInTheDocument();
        expect(screen.getByText(mockProps.contact.email)).toBeInTheDocument();
        expect(screen.getByText(mockProps.contact.address)).toBeInTheDocument();
    });

    it('builds a wa.me link from the whatsapp number for the floating button', () => {
        render(<StorefrontLayout><p>Page content</p></StorefrontLayout>);
        expect(screen.getByLabelText('WhatsApp')).toHaveAttribute(
            'href',
            `https://wa.me/${mockProps.contact.whatsapp}`,
        );
    });

    it('does not render the WhatsApp float when no number is configured', () => {
        vi.mocked(usePage).mockReturnValueOnce({
            props: { ...mockProps, contact: { ...mockProps.contact, whatsapp: null } },
            url: '/',
        });
        render(<StorefrontLayout><p>Page content</p></StorefrontLayout>);
        expect(screen.queryByLabelText('WhatsApp')).toBeNull();
    });

    it('links Accueil to / and Contact to the existing contact route', () => {
        render(<StorefrontLayout><p>Page content</p></StorefrontLayout>);
        const homeLinks = screen.getAllByRole('link', { name: 'Accueil' });
        expect(homeLinks[0]).toHaveAttribute('href', '/');
        const contactLinks = screen.getAllByRole('link', { name: 'Contact' });
        expect(contactLinks[0]).toHaveAttribute('href', '/contact');
    });

    it('links staff back to the login page', () => {
        render(<StorefrontLayout><p>Page content</p></StorefrontLayout>);
        const loginLinks = screen.getAllByRole('link', { name: /Connexion/i });
        expect(loginLinks.length).toBeGreaterThan(0);
        loginLinks.forEach((link) => expect(link).toHaveAttribute('href', '/login'));
    });
});

describe('StorefrontLayout footer — the contact column (BAN-341)', () => {
    // StorefrontLayout, Header and Footer each call usePage() — three calls per
    // render. mockReturnValueOnce would override only the first, so these tests
    // would pass by the accident of the default export happening to be the
    // caller that reads `contact`; the day Footer read it from usePage() instead
    // of props they would silently fall back to the fully-populated fixture and
    // go green with the bug present. mockReturnValue covers every call, and the
    // afterEach puts the shared default back for the suite above.
    afterEach(() => {
        vi.mocked(usePage).mockImplementation(() => ({ props: mockProps, url: '/' }));
    });

    /**
     * Shaped like HandleInertiaRequests::buildContact(), which always returns
     * every key and nulls the ones the tenant has not set. Omitting the keys
     * would pass for the wrong reason, since `undefined` is falsy too.
     */
    function withContact(contact) {
        vi.mocked(usePage).mockReturnValue({
            props: {
                ...mockProps,
                contact: {
                    phone: null,
                    whatsapp: null,
                    email: null,
                    address: null,
                    hoursWeekday: null,
                    hoursSaturday: null,
                    hoursSunday: null,
                    facebookUrl: null,
                    instagramUrl: null,
                    ...contact,
                },
            },
            url: '/',
        });
    }

    it('hides the "Contact Us" heading when the tenant has set no contact details', () => {
        // drivedesk's own demo was exactly this case: every row conditional, the
        // heading not, so the live storefront published a labelled empty column.
        withContact({});

        render(<StorefrontLayout><p>Page content</p></StorefrontLayout>);

        expect(screen.queryByText('Contact Us')).toBeNull();
    });

    it('shows the column as soon as there is one detail to put in it', () => {
        withContact({ email: 'contact@example.com' });

        render(<StorefrontLayout><p>Page content</p></StorefrontLayout>);

        expect(screen.getByText('Contact Us')).toBeInTheDocument();
        expect(screen.getByText('contact@example.com')).toBeInTheDocument();
    });

    it('shows the column for opening hours alone, with no address, phone or email', () => {
        // The predicate has to cover every field the block can render, not just
        // the obvious three.
        withContact({ hoursWeekday: '09:00 - 19:00' });

        render(<StorefrontLayout><p>Page content</p></StorefrontLayout>);

        expect(screen.getByText('Contact Us')).toBeInTheDocument();
        expect(screen.getByText(/09:00 - 19:00/)).toBeInTheDocument();
    });
});
