import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { router, usePage } from '@inertiajs/react';
import RentalAgreementCreate from '@/Pages/RentalAgreement/Create';

// BAN-259: picking an end date before the start date must pop a modal and block
// the submit (the server's after_or_equal rule is the authoritative backstop).

const confirmMock = vi.fn(() => Promise.resolve(true));

vi.mock('@inertiajs/react', () => ({
    router: { post: vi.fn() },
    usePage: vi.fn(),
}));

vi.mock('@/components/ui/confirm-dialog', () => ({
    useConfirm: () => confirmMock,
    ConfirmProvider: ({ children }) => children,
}));

globalThis.route = (name, param) => `/${name}/${param ?? ''}`;

beforeEach(() => {
    vi.clearAllMocks();
    usePage.mockReturnValue({ props: { translations: {}, errors: {} } });
});

describe('RentalAgreement/Create — end-before-start guard', () => {
    it('shows a modal and does not POST when end date precedes start date', async () => {
        const { container } = render(
            <RentalAgreementCreate
                vehicles={[]}
                drivers={[]}
                statuses={[{ value: 'draft', label: 'Draft' }]}
                defaultTerms=""
            />,
        );

        const dates = container.querySelectorAll('input[type="date"]'); // [0]=start, [1]=end
        const times = container.querySelectorAll('input[type="time"]');
        const duration = container.querySelector('input[type="number"]');

        fireEvent.change(dates[0], { target: { value: '2026-07-10' } });
        fireEvent.change(times[0], { target: { value: '09:00' } });
        fireEvent.change(dates[1], { target: { value: '2026-07-01' } }); // before start
        fireEvent.change(times[1], { target: { value: '18:00' } });
        fireEvent.change(duration, { target: { value: '2' } });

        fireEvent.click(screen.getByRole('button', { name: 'Create' }));

        await waitFor(() => expect(confirmMock).toHaveBeenCalled());
        expect(router.post).not.toHaveBeenCalled();
    });
});
