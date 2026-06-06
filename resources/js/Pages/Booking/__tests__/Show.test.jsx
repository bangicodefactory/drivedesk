import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import { usePage } from '@inertiajs/react';
import BookingShow from '@/Pages/Booking/Show';

// useConfirm is only consumed by the inner PaymentDialog (not rendered without the
// 'create booking payment' permission); mock it so the import resolves.
vi.mock('@/components/ui/confirm-dialog', () => ({
    useConfirm: () => () => Promise.resolve(true),
    ConfirmProvider: ({ children }) => children,
}));

vi.mock('@inertiajs/react', () => ({
    usePage: vi.fn(),
    Link: ({ href, children, ...rest }) => <a href={href} {...rest}>{children}</a>,
    router: { delete: vi.fn() },
}));

globalThis.route = (name, param) => `/${name}/${param ?? ''}`;

// `due_amount` is the raw getTotalDueAmount() (the old Blade "Rest" value); the
// other totals arrive pre-formatted from the server. paid_amount must NOT appear
// in the totals — the row was wrongly ported to "Paid" (see fix/booking-rest-label).
const booking = {
    id: 1,
    booking_id: 'BOK-0001',
    encrypted_id: 'enc',
    payment_status: 'partiellement_paye',
    payment_status_label: 'Partiellement payé',
    total_ht: '4,160.00',
    tva_amount: '1,040.00',
    paid_amount: '1,000.00',
    due_amount: 4200,
    total_amount: '5,200.00',
    payments: [],
};

// Match a <td> whose full text equals the given string (values render as two text
// nodes — "4,200.00" + " Dh" — so an exact getByText on the combined string needs this).
const cell = (text) => (_content, el) => el?.tagName === 'TD' && el.textContent.trim() === text;

function renderShow() {
    usePage.mockReturnValue({ props: { auth: { permissions: [] } } });
    return render(<BookingShow booking={booking} settings={{}} paymentMethods={[]} defaultQuantity={1} />);
}

describe('Booking/Show totals — Rest row (parity with booking/show.blade.php)', () => {
    it("shows a 'Rest' row with the remaining balance (due_amount), formatted", () => {
        renderShow();
        expect(screen.getByText('Rest')).toBeInTheDocument();
        expect(screen.getByText(cell('4,200.00 Dh'))).toBeInTheDocument();
    });

    it("does not mislabel the row 'Paid' or render paid_amount in the totals (regression)", () => {
        renderShow();
        expect(screen.queryByText('Paid')).toBeNull();
        expect(screen.queryByText(cell('1,000.00 Dh'))).toBeNull();
    });
});
