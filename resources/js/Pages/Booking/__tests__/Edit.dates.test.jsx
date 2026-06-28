import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { usePage } from '@inertiajs/react';
import axios from 'axios';
import BookingEdit from '@/Pages/Booking/Edit';

// Guards the edit-page parity fix: changing the dates must re-fetch the
// available-vehicle list (it previously never refreshed), sending the
// slash-formatted dates the backend expects plus booking_id so THIS booking
// isn't treated as a conflict with itself.

vi.mock('axios', () => ({
    default: { get: vi.fn(() => Promise.resolve({ data: {} })), post: vi.fn() },
}));

vi.mock('@inertiajs/react', () => ({
    usePage: vi.fn(),
    router: { put: vi.fn() },
}));

globalThis.route = (name) => `/${name}`;

const booking = {
    id: 42,
    vehicle: '',
    driver: '',
    start_date_time: '',
    end_date_time: '',
    pickup_address: '',
    drop_off_address: '',
    addon: '',
    discount: '',
    status: 'yet_to_start',
    notes: '',
    daily_price_final: '',
    amount: '',
    details: {},
};

beforeEach(() => {
    vi.clearAllMocks();
    usePage.mockReturnValue({ props: { translations: {}, errors: {} } });
});

describe('Booking/Edit — available vehicles refresh on date change', () => {
    it('re-fetches /available.vehicle with slash dates and booking_id when dates change', async () => {
        render(
            <BookingEdit
                booking={booking}
                vehicles={[]}
                drivers={[]}
                statuses={[]}
                places={[]}
                addons={[]}
            />,
        );

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
        expect(call[1].params.booking_id).toBe(42);
    });
});
