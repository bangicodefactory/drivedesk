import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import { usePage } from '@inertiajs/react';
import Login from '@/Pages/Auth/Login';

vi.mock('@inertiajs/react', () => ({
    usePage: vi.fn(),
    Link: ({ href, children, ...rest }) => <a href={href} {...rest}>{children}</a>,
}));

vi.mock('react-google-recaptcha', () => ({
    default: ({ sitekey, onChange }) => (
        <div data-testid="recaptcha" data-sitekey={sitekey}>
            <button onClick={() => onChange('test-token')}>Verify</button>
        </div>
    ),
}));

globalThis.route = (name) => `/${name}`;

const basePage = {
    props: {
        branding: { appName: 'TestAgency' },
        recaptcha: { enabled: false, siteKey: '' },
    },
};

function renderLogin(overrides = {}) {
    usePage.mockReturnValue({ ...basePage, props: { ...basePage.props, ...overrides } });
    return render(<Login />);
}

describe('Login — branded portal', () => {
    beforeEach(() => {
        usePage.mockReturnValue(basePage);
    });

    it('renders the agency operations portal title', () => {
        renderLogin();
        expect(screen.getByText('Agency operations portal')).toBeInTheDocument();
    });

    it('renders the app name from branding in the top bar', () => {
        renderLogin();
        expect(screen.getByText('TestAgency')).toBeInTheDocument();
    });

    it('renders authorized-personnel notice', () => {
        renderLogin();
        expect(screen.getByText(/Authorized personnel only/i)).toBeInTheDocument();
    });

    it('password field is hidden by default', () => {
        renderLogin();
        const passwordInput = document.getElementById('password');
        expect(passwordInput).toHaveAttribute('type', 'password');
    });

    it('show/hide toggle reveals and re-hides the password', () => {
        renderLogin();
        const toggle = screen.getByRole('button', { name: /show password/i });
        const passwordInput = document.getElementById('password');

        fireEvent.click(toggle);
        expect(passwordInput).toHaveAttribute('type', 'text');

        const hideToggle = screen.getByRole('button', { name: /hide password/i });
        fireEvent.click(hideToggle);
        expect(passwordInput).toHaveAttribute('type', 'password');
    });

    it('does not render reCAPTCHA when disabled', () => {
        renderLogin({ recaptcha: { enabled: false, siteKey: '' } });
        expect(screen.queryByTestId('recaptcha')).toBeNull();
    });

    it('renders reCAPTCHA when enabled', () => {
        renderLogin({ recaptcha: { enabled: true, siteKey: 'test-key-123' } });
        const captcha = screen.getByTestId('recaptcha');
        expect(captcha).toBeInTheDocument();
        expect(captcha).toHaveAttribute('data-sitekey', 'test-key-123');
    });
});
