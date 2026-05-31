import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import InspectionIndex from '@/Pages/Inspection/Index';

// Inertia's Link/router/usePage are mocked so the page renders in isolation.
const mockDelete = vi.fn();
vi.mock('@inertiajs/react', () => ({
    Link: ({ children, href }) => <a href={href}>{children}</a>,
    router: { delete: (...args) => mockDelete(...args) },
    usePage: () => ({
        props: {
            auth: {
                permissions: [
                    'manage vehicle',
                    'show inspection',
                    'edit inspection',
                    'delete inspection',
                ],
            },
        },
    }),
}));

global.route = (name, params) => `/${name}/${params ?? ''}`;

vi.mock('@/Layouts/AdminLayout', () => ({
    default: ({ children }) => <div>{children}</div>,
}));

describe('Inspection/Index', () => {
    it('renders inspection rows with translated status labels', () => {
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
        render(<InspectionIndex inspections={inspections} />);
        expect(screen.getByText('Car A')).toBeInTheDocument();
        expect(screen.getByText('John')).toBeInTheDocument();
        expect(screen.getByText('Completed')).toBeInTheDocument();
        expect(screen.getByText('Needs Repair')).toBeInTheDocument();
    });

    it('falls back to a dash when the vehicle relation is missing', () => {
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
        render(<InspectionIndex inspections={inspections} />);
        expect(screen.getByText('-')).toBeInTheDocument();
        expect(screen.getByText('Jane')).toBeInTheDocument();
    });

    it('shows an empty state when there are no inspections', () => {
        render(<InspectionIndex inspections={[]} />);
        expect(screen.getByText('No inspections yet')).toBeInTheDocument();
    });
});
