import { render, screen, fireEvent } from '@testing-library/react';
import { vi } from 'vitest';

// Mock Inertia hooks/components before importing the page.
vi.mock('@inertiajs/react', () => ({
    Link: ({ children, href, ...rest }) => <a href={href} {...rest}>{children}</a>,
    usePage: () => ({ props: { branding: { appName: 'RentCar' } } }),
    router: { post: vi.fn(), get: vi.fn(), put: vi.fn(), patch: vi.fn(), delete: vi.fn(), on: vi.fn() },
}));

// PublicLayout is a Page-layout wrapper that uses usePage(); short-circuit it.
vi.mock('@/Layouts/PublicLayout', () => ({
    default: ({ children }) => <div>{children}</div>,
}));

// Stub Ziggy's global route() helper used by Inertia pages.
global.route = vi.fn((name) => `/${name}`);

import Login from '@/Pages/Auth/Login';

describe('Login page', () => {
    it('renders the form fields and submit button', () => {
        render(<Login />);
        expect(screen.getByLabelText('Email')).toBeInTheDocument();
        expect(screen.getByLabelText('Password')).toBeInTheDocument();
        expect(screen.getByLabelText(/remember me/i)).toBeInTheDocument();
        expect(screen.getByRole('button', { name: /sign in/i })).toBeInTheDocument();
    });

    it('shows a validation error when email is empty', async () => {
        render(<Login />);
        fireEvent.click(screen.getByRole('button', { name: /sign in/i }));

        // react-hook-form runs validation async on submit
        const error = await screen.findByText(/enter a valid email/i);
        expect(error).toBeInTheDocument();
    });

    it('shows a forgot-password link', () => {
        render(<Login />);
        expect(screen.getByRole('link', { name: /forgot password/i })).toBeInTheDocument();
    });
});
