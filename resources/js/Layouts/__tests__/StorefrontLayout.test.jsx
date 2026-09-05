import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import { usePage } from '@inertiajs/react';
import StorefrontLayout from '@/Layouts/StorefrontLayout';

const mockProps = {
    branding: { appName: 'MarrueCar', logoUrl: null },
    contact: {
        phone: '+212602-793425',
        whatsapp: '212602793425',
        email: 'marruecarsarl@gmail.com',
        address: 'Numero 16, Lot Mounia, Av Tizi Ouasli, Tétouan 93000, Morocco',
        hoursWeekday: '8:00 AM - 8:00 PM',
        hoursSaturday: '9:00 AM - 6:00 PM',
        hoursSunday: '10:00 AM - 4:00 PM',
        facebookUrl: 'https://www.facebook.com/MarrueCar/',
        instagramUrl: 'https://www.instagram.com/marruecar_rent_car/',
    },
    translations: {},
    flash: {},
    locale: 'fr',
};

vi.mock('@inertiajs/react', () => ({
    usePage: vi.fn(() => ({ props: mockProps, url: '/' })),
    Link: ({ href, children, ...rest }) => <a href={href} {...rest}>{children}</a>,
    router: {},
}));

globalThis.route = (name, param) => {
    if (name === 'contact') return '/contact';
    if (name === 'language.change') return `/language/${param}`;
    return '/' + String(name).replace(/\./g, '/');
};

describe('StorefrontLayout', () => {
    it('renders the app name and children', () => {
        render(<StorefrontLayout><p>Page content</p></StorefrontLayout>);
        expect(screen.getAllByText('MarrueCar').length).toBeGreaterThan(0);
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
