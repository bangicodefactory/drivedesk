import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { usePage, router } from '@inertiajs/react';
import BookingShow from '@/Pages/Booking/Show';

// BAN-260: the Payment History delete button must prompt for confirmation and
// only delete after the user confirms. The handler referenced an out-of-scope
// `confirmDialog` (useConfirm was only called inside the sibling PaymentDialog),
// so clicking the trash icon threw a ReferenceError — no prompt, no delete.

const confirmMock = vi.fn(() => Promise.resolve(true));

vi.mock('@/components/ui/confirm-dialog', () => ({
    useConfirm: () => confirmMock,
    ConfirmProvider: ({ children }) => children,
}));

vi.mock('@inertiajs/react', () => ({
    usePage: vi.fn(),
    Link: ({ href, children, ...rest }) => <a href={href} {...rest}>{children}</a>,
    router: { delete: vi.fn(), post: vi.fn() },
}));

globalThis.route = (name, param) =>
    `/${name}/${Array.isArray(param) ? param.join('/') : (param ?? '')}`;

const booking = {
    id: 1,
    booking_id: 'BOK-0001',
    encrypted_id: 'enc',
    payment_status: 'partiellement_paye',
    payment_status_label: 'Partial',
    total_ht: '100.00',
    tva_amount: '20.00',
    paid_amount: '50.00',
    due_amount: 70,
    total_amount: '120.00',
    payments: [
        { id: 10, date: '2026-06-20', payment_method: 'cash', notes: 'x', amount: '50.00' },
    ],
};

function renderShow() {
    usePage.mockReturnValue({ props: { auth: { permissions: ['delete booking payment'] } } });
    return render(<BookingShow booking={booking} settings={{}} paymentMethods={[]} defaultQuantity={1} />);
}

describe('Booking/Show — payment history delete confirmation', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('prompts for confirmation and deletes only after the user confirms', async () => {
        confirmMock.mockResolvedValueOnce(true);
        renderShow();

        fireEvent.click(screen.getByRole('button', { name: 'Delete payment' }));

        await waitFor(() => expect(confirmMock).toHaveBeenCalledWith({ title: 'Delete this payment?' }));
        await waitFor(() => expect(router.delete).toHaveBeenCalledTimes(1));
        // Targets the right booking + payment id (route('booking.payment.destroy', [1, 10])).
        expect(router.delete).toHaveBeenCalledWith('/booking.payment.destroy/1/10');
    });

    it('does not delete when the user cancels the confirmation', async () => {
        confirmMock.mockResolvedValueOnce(false);
        renderShow();

        fireEvent.click(screen.getByRole('button', { name: 'Delete payment' }));

        await waitFor(() => expect(confirmMock).toHaveBeenCalled());
        expect(router.delete).not.toHaveBeenCalled();
    });
});
