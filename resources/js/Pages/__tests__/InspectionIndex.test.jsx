import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import Index from '@/Pages/Inspection/Index';

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ children }) => <a>{children}</a>,
    router: { delete: vi.fn() },
    usePage: () => ({ props: { translations: {} } }),
}));

vi.mock('@/Layouts/AdminLayout', () => ({
    default: ({ children }) => <div>{children}</div>,
}));

global.route = (name, params) => `/${name}/${params ?? ''}`;

describe('Inspection Index', () => {
    it('renders inspection rows with status badges', () => {
        const inspections = [
            {
                id: 1,
                id_encrypted: 'enc1',
                inspector: 'John',
                inspection_date: '2024-01-01',
                status: 'completed',
                repair_status: 'needs_repair',
                vehicles: { name: 'Car A' },
            },
        ];
        render(<Index inspections={inspections} />);
        expect(screen.getByText('Car A')).toBeInTheDocument();
        expect(screen.getByText('John')).toBeInTheDocument();
        expect(screen.getByText('Completed')).toBeInTheDocument();
        expect(screen.getByText('Needs Repair')).toBeInTheDocument();
    });

    it('falls back to dash when vehicle is missing', () => {
        const inspections = [
            {
                id: 2,
                id_encrypted: 'enc2',
                inspector: 'Jane',
                inspection_date: '2024-02-01',
                status: 'pending',
                repair_status: 'pending',
                vehicles: null,
            },
        ];
        render(<Index inspections={inspections} />);
        expect(screen.getByText('-')).toBeInTheDocument();
        expect(screen.getByText('Jane')).toBeInTheDocument();
    });

    it('shows empty state when no inspections', () => {
        render(<Index inspections={[]} />);
        expect(screen.getByText('No data available')).toBeInTheDocument();
    });
});
