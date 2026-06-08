import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import { Breadcrumbs } from '@/Layouts/AdminLayout';

// Inertia: usePage feeds translations (t falls back to the key); Link → <a>.
vi.mock('@inertiajs/react', () => ({
    usePage: vi.fn(() => ({ props: { translations: {} } })),
    Link: ({ href, children }) => <a href={href}>{children}</a>,
    router: {},
}));

// Some modules pulled in via AdminLayout reference the route() global at runtime.
globalThis.route = (name) => '/' + String(name).replace(/\./g, '/');

describe('Breadcrumbs', () => {
    it('renders nothing when there are no items', () => {
        const { container } = render(<Breadcrumbs items={[]} />);
        expect(container.firstChild).toBeNull();
    });

    it('renders every crumb label', () => {
        render(<Breadcrumbs items={[
            { label: 'Credits', href: '/credit' },
            { label: 'Edit' },
        ]} />);
        expect(screen.getByText('Credits')).toBeInTheDocument();
        expect(screen.getByText('Edit')).toBeInTheDocument();
    });

    it('links an interior crumb that carries an href', () => {
        render(<Breadcrumbs items={[
            { label: 'Credits', href: '/credit' },
            { label: 'Edit' },
        ]} />);
        expect(screen.getByRole('link', { name: 'Credits' }))
            .toHaveAttribute('href', '/credit');
    });

    it('marks exactly the trailing crumb as the current page', () => {
        // Settings group: first crumb has no href, so it must NOT be aria-current.
        render(<Breadcrumbs items={[
            { label: 'Settings' },
            { label: 'General' },
        ]} />);
        const current = document.querySelectorAll('[aria-current="page"]');
        expect(current).toHaveLength(1);
        expect(current[0]).toHaveTextContent('General');
    });

    it('does not link an interior crumb without an href', () => {
        render(<Breadcrumbs items={[
            { label: 'Settings' },
            { label: 'General' },
        ]} />);
        expect(screen.queryByRole('link', { name: 'Settings' })).toBeNull();
        expect(screen.getByText('Settings')).toBeInTheDocument();
    });
});
