import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { usePage } from '@inertiajs/react';
import axios from 'axios';
import BookingCreate from '@/Pages/Booking/Create';

// Integration guard for JAVASCRIPT-4: the helper unit test proves formatDt emits
// slashes; this proves the page actually routes its date inputs through it, so a
// future refactor that bypasses formatDt would be caught.

vi.mock('axios', () => ({
    default: { get: vi.fn(() => Promise.resolve({ data: {} })), post: vi.fn() },
}));

vi.mock('@inertiajs/react', () => ({
    usePage: vi.fn(),
    router: { post: vi.fn() },
}));

vi.mock('@/components/ui/confirm-dialog', () => ({
    useConfirm: () => () => Promise.resolve(true),
    ConfirmProvider: ({ children }) => children,
}));

globalThis.route = (name) => `/${name}`;

beforeEach(() => {
    vi.clearAllMocks();
    usePage.mockReturnValue({ props: { translations: {}, errors: {} } });
});

describe('Booking/Create — date format sent to the backend', () => {
    it('sends Y/m/d H:i (slashes) to /available.vehicle when both dates are picked', async () => {
        render(<BookingCreate vehicles={[]} drivers={[]} statuses={[]} places={[]} addons={[]} />);

        // datetime-local inputs yield dash-formatted values; the page must convert them.
        fireEvent.change(screen.getByLabelText('Start Date & Time'), {
            target: { value: '2026-06-20T09:00' },
        });
        fireEvent.change(screen.getByLabelText('End Date & Time'), {
            target: { value: '2026-06-23T18:00' },
        });

        await waitFor(() => {
            expect(axios.get.mock.calls.some((c) => c[0] === '/available.vehicle')).toBe(true);
        });

        const call = axios.get.mock.calls.find((c) => c[0] === '/available.vehicle');
        expect(call[1].params.start_date_time).toBe('2026/06/20 09:00');
        expect(call[1].params.end_date_time).toBe('2026/06/23 18:00');
    });
});
