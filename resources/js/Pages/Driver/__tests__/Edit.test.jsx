import { describe, it, expect, beforeEach, vi } from 'vitest';
import { render, waitFor } from '@testing-library/react';
import { fireEvent } from '@testing-library/react';

// Mock Ziggy's global route() helper used inside the component.
beforeEach(() => {
    globalThis.route = (name, param) => `/${name}${param != null ? `/${param}` : ''}`;
    routerPost.mockClear();
});

// Stub Inertia: Link renders a plain anchor; router.post is a spy so we can
// assert the exact payload the form submits.
const routerPost = vi.fn();
vi.mock('@inertiajs/react', () => ({
    Link: ({ href, children, ...rest }) => <a href={href} {...rest}>{children}</a>,
    router: { post: (...args) => routerPost(...args) },
    usePage: () => ({ props: { auth: { permissions: [] } } }),
}));

// AdminLayout is only attached as a static `.layout`; stub it to be safe.
vi.mock('@/Layouts/AdminLayout', () => ({
    default: ({ children }) => <div>{children}</div>,
}));

import DriverEdit from '../Edit.jsx';

const genderOptions = { Male: 'Male', Female: 'Female' };
const user = {
    id: 42,
    first_name: 'Jane',
    last_name: 'Doe',
    email: 'jane@example.com',
    phone_number: '0600000000',
};

describe('Driver/Edit', () => {
    // Regression: gender lives on the drivers table, not users. The form used
    // to initialise from user.gender (always undefined), so every save NULLed
    // the stored gender server-side.
    it('submits the gender stored on the driver profile', async () => {
        const { container } = render(
            <DriverEdit
                driver={{ gender: 'Male', license_number: 'L-1' }}
                user={user}
                gender={genderOptions}
            />,
        );

        fireEvent.submit(container.querySelector('form'));

        await waitFor(() => expect(routerPost).toHaveBeenCalledTimes(1));
        const [url, payload] = routerPost.mock.calls[0];
        expect(url).toBe('/driver.update/42');
        expect(payload.gender).toBe('Male');
    });

    it('submits an empty gender (not a crash) when the driver profile is missing', async () => {
        const { container } = render(
            <DriverEdit driver={null} user={user} gender={genderOptions} />,
        );

        fireEvent.submit(container.querySelector('form'));

        await waitFor(() => expect(routerPost).toHaveBeenCalledTimes(1));
        const [, payload] = routerPost.mock.calls[0];
        expect(payload.gender).toBe('');
    });
});
