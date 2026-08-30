import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import Register from '@/Pages/Auth/Register';

vi.mock('@inertiajs/react', () => ({
    usePage: () => ({ props: { recaptcha: { enabled: false } } }),
    Link: ({ href, children, ...rest }) => <a href={href} {...rest}>{children}</a>,
    router: { post: vi.fn(), on: vi.fn() },
}));

vi.mock('@/Layouts/PublicLayout', () => ({ default: ({ children }) => <div>{children}</div> }));

globalThis.route = (name) => `/${name}`;

describe('Register form accessibility', () => {
    it('wires every field error to its input', async () => {
        render(<Register />);
        fireEvent.click(screen.getByRole('button', { name: 'Create account' }));

        const alerts = await screen.findAllByRole('alert');
        // name, email, company_name, city, password, password_confirmation
        expect(alerts).toHaveLength(6);

        for (const [label, name] of [
            ['Full name', 'name'],
            ['Email', 'email'],
            ['Company name', 'company_name'],
            ['City', 'city'],
            ['Password', 'password'],
            ['Confirm password', 'password_confirmation'],
        ]) {
            const input = screen.getByLabelText(label);
            expect(input).toHaveAttribute('aria-invalid', 'true');
            expect(input).toHaveAttribute('aria-describedby', `${name}-error`);
            expect(document.getElementById(`${name}-error`)).toHaveAttribute('role', 'alert');
        }
    });
});
