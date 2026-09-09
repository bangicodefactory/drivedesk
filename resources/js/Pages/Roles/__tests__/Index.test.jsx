import { describe, it, expect, beforeEach, vi } from 'vitest';
import { render, screen } from '@testing-library/react';

vi.mock('@/components/ui/confirm-dialog', () => ({
    useConfirm: () => () => Promise.resolve(true),
    ConfirmProvider: ({ children }) => children,
}));

beforeEach(() => {
    globalThis.route = (name, param) => `/${name}${param != null ? `/${param}` : ''}`;
});

const permissionsRef = { current: [] };
vi.mock('@inertiajs/react', () => ({
    Link: ({ href, children, ...rest }) => <a href={href} {...rest}>{children}</a>,
    router: { delete: vi.fn() },
    usePage: () => ({ props: { auth: { permissions: permissionsRef.current } } }),
}));

vi.mock('@/Layouts/AdminLayout', () => ({
    default: ({ children }) => <div>{children}</div>,
}));

import RolesIndex from '../Index.jsx';

const roles = [{ id: 3, name: 'Manager', permissions_count: 4 }];

describe('Roles/Index', () => {
    beforeEach(() => {
        permissionsRef.current = [];
    });

    // BAN-306: role.create now requires `create role`. Edit and Delete were
    // already gated; New role was not, so a user without the permission saw the
    // button and was redirected back with "Permission Denied." on click.
    it('hides New role without the create role permission', () => {
        render(<RolesIndex roles={roles} />);

        expect(screen.queryByText('New role')).toBeNull();
    });

    it('shows New role with the create role permission', () => {
        permissionsRef.current = ['create role'];
        render(<RolesIndex roles={roles} />);

        expect(screen.getByText('New role')).toBeTruthy();
    });

    it('gates Edit and Delete on their own permissions', () => {
        permissionsRef.current = ['edit role'];
        render(<RolesIndex roles={roles} />);

        expect(screen.getByLabelText('Edit')).toBeTruthy();
        expect(screen.queryByLabelText('Delete')).toBeNull();
    });
});
