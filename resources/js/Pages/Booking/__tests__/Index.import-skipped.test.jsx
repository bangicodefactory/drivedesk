import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import { usePage } from '@inertiajs/react';
import BookingIndex from '@/Pages/Booking/Index';

// useConfirm is consumed by row actions; mock so the import resolves.
vi.mock('@/components/ui/confirm-dialog', () => ({
    useConfirm: () => () => Promise.resolve(true),
    ConfirmProvider: ({ children }) => children,
}));

vi.mock('@inertiajs/react', () => ({
    usePage: vi.fn(),
    Link: ({ href, children, ...rest }) => <a href={href} {...rest}>{children}</a>,
    router: { get: vi.fn(), post: vi.fn(), delete: vi.fn() },
}));

globalThis.route = (name, param) => `/${name}/${param ?? ''}`;

// Shape mirrors BookingController@importExcel's skipped[] entries + the Blade
// session('import_skipped') payload it replaces.
const skipped = [{
    row: 2,
    nom: 'INVALID ROW',
    plaque: 'ZZ-0000-ZZ',
    debut: 'not-a-date',
    retour: 'also-bad',
    errors: ["date début invalide 'not-a-date'"],
}];

function renderIndex(flash = {}) {
    usePage.mockReturnValue({
        props: { auth: { permissions: ['create booking'] }, translations: {}, flash },
    });
    return render(
        <BookingIndex
            bookings={{ data: [], last_page: 1 }}
            statuses={[]}
            paymentStatuses={[]}
            filters={{}}
        />,
    );
}

describe('Booking/Index — Excel import skipped-rows feedback (parity with booking/index.blade.php)', () => {
    it('reopens the import dialog and lists the skipped rows when the server flashes import_skipped', async () => {
        renderIndex({ import_skipped: skipped });

        // The effect opens the (controlled) dialog; its portal content carries the report.
        expect(
            await screen.findByText(
                (_c, el) => el?.tagName === 'STRONG' && el.textContent.includes('ligne(s) non importée'),
            ),
        ).toBeInTheDocument();
        expect(screen.getByText('INVALID ROW')).toBeInTheDocument();
        expect(screen.getByText('ZZ-0000-ZZ')).toBeInTheDocument();
        expect(screen.getByText("date début invalide 'not-a-date'")).toBeInTheDocument();
    });

    it('shows no skipped-rows report (dialog stays closed) when there are none', () => {
        renderIndex({});

        expect(screen.queryByText(/ligne\(s\) non importée/)).toBeNull();
        expect(screen.queryByText("date début invalide 'not-a-date'")).toBeNull();
    });
});
