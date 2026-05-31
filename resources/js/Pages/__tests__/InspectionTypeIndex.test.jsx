import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import Index from '@/Pages/InspectionType/Index';

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

describe('InspectionType Index', () => {
    it('renders type rows', () => {
        const types = [
            { id: 1, type: 'Brakes' },
            { id: 2, type: 'Lights' },
        ];
        render(<Index types={types} />);
        expect(screen.getByText('Brakes')).toBeInTheDocument();
        expect(screen.getByText('Lights')).toBeInTheDocument();
    });

    it('shows empty state when no types', () => {
        render(<Index types={[]} />);
        expect(screen.getByText('No data available')).toBeInTheDocument();
    });
});
