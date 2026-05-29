import { render, screen } from '@testing-library/react';
import { vi } from 'vitest';

vi.mock('@inertiajs/react', () => ({
    Link:    ({ children, href, ...rest }) => <a href={href} {...rest}>{children}</a>,
    usePage: () => ({ props: { auth: { permissions: ['create user', 'edit user', 'delete user'] } } }),
    router:  { delete: vi.fn(), post: vi.fn(), get: vi.fn(), put: vi.fn(), patch: vi.fn(), on: vi.fn() },
}));

global.route = vi.fn((name, id) => id ? `/${name}/${id}` : `/${name}`);

import UsersIndex from '@/Pages/Users/Index';

const users = [
    { id: 1, name: 'Alice', email: 'a@x', type: 'owner',    is_active: true,  company_name: 'Acme', created_at: '2026-01-01' },
    { id: 2, name: 'Bob',   email: 'b@x', type: 'employee', is_active: false, company_name: null,   created_at: '2026-01-02' },
];

describe('UsersIndex', () => {
    it('renders a row per user with the right cells', () => {
        render(<UsersIndex users={users} />);
        expect(screen.getByText('Alice')).toBeInTheDocument();
        expect(screen.getByText('Bob')).toBeInTheDocument();
        expect(screen.getByText('Active')).toBeInTheDocument();
        expect(screen.getByText('Inactive')).toBeInTheDocument();
    });

    it('shows the empty state when users is empty', () => {
        render(<UsersIndex users={[]} />);
        expect(screen.getByText(/no users yet/i)).toBeInTheDocument();
    });

    it('renders the New user button (with create permission)', () => {
        render(<UsersIndex users={users} />);
        expect(screen.getByRole('link', { name: /new user/i })).toBeInTheDocument();
    });
});
