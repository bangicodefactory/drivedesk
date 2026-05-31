import { describe, it, expect, beforeEach, vi } from 'vitest';
import { render, screen } from '@testing-library/react';

// Mock Ziggy's global route() helper used inside the component.
beforeEach(() => {
    globalThis.route = (name, param) => `/${name}${param != null ? `/${param}` : ''}`;
});

// Stub Inertia so we can drive usePage().props.auth.permissions and avoid a
// real Inertia app context. Link renders a plain anchor; router.delete is a spy.
const permissionsRef = { current: [] };
vi.mock('@inertiajs/react', () => ({
    Link: ({ href, children, ...rest }) => <a href={href} {...rest}>{children}</a>,
    router: { delete: vi.fn() },
    usePage: () => ({ props: { auth: { permissions: permissionsRef.current } } }),
}));

// AdminLayout is only attached as a static `.layout`; stub it to be safe.
vi.mock('@/Layouts/AdminLayout', () => ({
    default: ({ children }) => <div>{children}</div>,
}));

import OptionIndex from '../Index.jsx';

const option = { id: 3, name: 'GPS' };

describe('Option/Index', () => {
    it('renders option rows', () => {
        permissionsRef.current = ['manage options'];
        render(<OptionIndex options={[option]} />);
        expect(screen.getByText('GPS')).toBeInTheDocument();
    });

    it('shows an empty state when there are no options', () => {
        permissionsRef.current = ['manage options'];
        render(<OptionIndex options={[]} />);
        expect(screen.getByText('No options yet')).toBeInTheDocument();
    });

    it('shows the Create button only with manage options permission', () => {
        permissionsRef.current = [];
        const { rerender } = render(<OptionIndex options={[]} />);
        expect(screen.queryByText('Create Option')).not.toBeInTheDocument();

        permissionsRef.current = ['manage options'];
        rerender(<OptionIndex options={[]} />);
        expect(screen.getByText('Create Option')).toBeInTheDocument();
    });

    it('hides the Action column when the user lacks edit/delete options', () => {
        permissionsRef.current = ['manage options'];
        render(<OptionIndex options={[option]} />);
        expect(screen.queryByText('Action')).not.toBeInTheDocument();
    });

    it('renders per-row action buttons gated by permission', () => {
        permissionsRef.current = ['edit options', 'delete options'];
        render(<OptionIndex options={[option]} />);
        expect(screen.getByLabelText('Edit')).toBeInTheDocument();
        expect(screen.getByLabelText('Delete')).toBeInTheDocument();
    });
});
