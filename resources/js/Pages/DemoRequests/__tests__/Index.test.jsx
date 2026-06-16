import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { router } from '@inertiajs/react';
import DemoRequestsIndex from '@/Pages/DemoRequests/Index';

vi.mock('@inertiajs/react', () => ({
    router: { post: vi.fn() },
}));

// t(key) → key; confirm() → always confirmed; layout is irrelevant to a direct render.
vi.mock('@/hooks/useTranslation', () => ({ useTranslation: () => (k) => k }));
vi.mock('@/components/ui/confirm-dialog', () => ({ useConfirm: () => () => Promise.resolve(true) }));
vi.mock('@/Layouts/AdminLayout', () => ({ default: ({ children }) => <div>{children}</div> }));

globalThis.route = (name, id) => `/${name}/${id}`;

const sample = [
    { id: 7, name: 'Sara Idrissi', company: 'Atlas Cars', email: 'sara@atlascars.ma', phone: '+212600', created_at: '2026-06-16T10:00:00+00:00' },
];

describe('DemoRequests/Index — super-admin review', () => {
    beforeEach(() => router.post.mockClear());

    it('renders a pending request row', () => {
        render(<DemoRequestsIndex requests={sample} />);
        expect(screen.getByText('Atlas Cars')).toBeInTheDocument();
        expect(screen.getByText('sara@atlascars.ma')).toBeInTheDocument();
    });

    it('shows an empty state when there are none', () => {
        render(<DemoRequestsIndex requests={[]} />);
        expect(screen.getByText('No pending demo requests')).toBeInTheDocument();
    });

    it('approve posts to the approve route (after confirm)', async () => {
        render(<DemoRequestsIndex requests={sample} />);
        fireEvent.click(screen.getByRole('button', { name: /approve/i }));
        await waitFor(() =>
            expect(router.post).toHaveBeenCalledWith('/demo-requests.approve/7', {}, expect.any(Object))
        );
    });

    it('decline posts to the decline route (after confirm)', async () => {
        render(<DemoRequestsIndex requests={sample} />);
        fireEvent.click(screen.getByRole('button', { name: /decline/i }));
        await waitFor(() =>
            expect(router.post).toHaveBeenCalledWith('/demo-requests.decline/7', {}, expect.any(Object))
        );
    });
});
