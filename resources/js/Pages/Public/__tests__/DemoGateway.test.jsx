import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import DemoGateway from '@/Pages/Public/DemoGateway';

// Inertia: stub usePage/Head, and make router.post "succeed" so the booking
// flow reaches its onSuccess (which flips the modal to the success state).
const post = vi.fn((url, data, opts) => {
    opts?.onSuccess?.();
    opts?.onFinish?.();
});

vi.mock('@inertiajs/react', () => ({
    usePage: vi.fn(() => ({ props: { flash: {} } })),
    Head: ({ children }) => <>{children}</>,
    router: { post: (...args) => post(...args) },
}));

// Ziggy route() — mirror the helper the page calls (route('login') → /login).
globalThis.route = (name) => `/${name}`;

describe('DemoGateway — login affordances (#BAN-246)', () => {
    beforeEach(() => {
        post.mockClear();
    });

    it('nav exposes a Log in link pointing at the app login route', () => {
        render(<DemoGateway />);

        const login = screen.getByRole('link', { name: /log in/i });
        expect(login).toHaveAttribute('href', '/login');
    });

    it('after a successful demo request, the success state links to login', async () => {
        render(<DemoGateway />);

        // Open the modal from the nav CTA (exact name avoids the "Book a demo →" CTAs).
        fireEvent.click(screen.getByRole('button', { name: 'Book a demo' }));

        // Radix portals the dialog to document.body. Fill the required fields so
        // client-side zod validation passes.
        fireEvent.change(document.querySelector('input[name="name"]'), { target: { value: 'Nadia El Amrani' } });
        fireEvent.change(document.querySelector('input[name="company"]'), { target: { value: 'Atlas Mobility' } });
        fireEvent.change(document.querySelector('input[name="email"]'), { target: { value: 'nadia@atlasmobility.ma' } });

        fireEvent.click(screen.getByRole('button', { name: /send request/i }));

        // Success state appears and offers a route into the app.
        const heading = await screen.findByText(/you're on the list/i);
        expect(heading).toBeInTheDocument();
        expect(post).toHaveBeenCalledWith('/demo.request', expect.any(Object), expect.any(Object));

        const goToLogin = screen.getByRole('link', { name: /go to login/i });
        expect(goToLogin).toHaveAttribute('href', '/login');
    });
});
