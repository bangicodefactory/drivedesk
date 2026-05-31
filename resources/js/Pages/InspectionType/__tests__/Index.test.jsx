import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import InspectionTypeIndex from '@/Pages/InspectionType/Index';

const mockDelete = vi.fn();
vi.mock('@inertiajs/react', () => ({
    Link: ({ children, href }) => <a href={href}>{children}</a>,
    router: { delete: (...args) => mockDelete(...args) },
    usePage: () => ({
        props: {
            auth: {
                permissions: [
                    'manage inspection type',
                    'edit inspection type',
                    'delete inspection type',
                ],
            },
        },
    }),
}));

global.route = (name, params) => `/${name}/${params ?? ''}`;

vi.mock('@/Layouts/AdminLayout', () => ({
    default: ({ children }) => <div>{children}</div>,
}));

describe('InspectionType/Index', () => {
    it('renders inspection type rows', () => {
        const types = [
            { id: 1, type: 'Brakes' },
            { id: 2, type: 'Lights' },
        ];
        render(<InspectionTypeIndex types={types} />);
        expect(screen.getByText('Brakes')).toBeInTheDocument();
        expect(screen.getByText('Lights')).toBeInTheDocument();
    });

    it('shows an empty state when there are no types', () => {
        render(<InspectionTypeIndex types={[]} />);
        expect(screen.getByText('No inspection types yet')).toBeInTheDocument();
    });
});
