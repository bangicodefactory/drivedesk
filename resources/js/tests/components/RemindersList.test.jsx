import { render, screen } from '@testing-library/react';
import { vi } from 'vitest';

// Inertia Link → plain <a>
vi.mock('@inertiajs/react', () => ({
    Link: ({ children, href, ...rest }) => <a href={href} {...rest}>{children}</a>,
}));

// Stub global route helper
global.route = vi.fn((name) => `/${name}`);

import RemindersList from '@/components/dashboard/RemindersList';

const sample = [
    {
        id: 1,
        reminder_date: '2026-06-01',
        note: 'Oil change due',
        status: 'urgent',
        vehicle: { name: 'BMW X5', license_plate: 'XYZ-123' },
    },
    {
        id: 2,
        reminder_date: '2026-06-15',
        note: null,
        status: 'pending',
        vehicle: null,
    },
];

describe('RemindersList', () => {
    it('renders an empty state when reminders is empty', () => {
        render(<RemindersList reminders={[]} />);
        expect(screen.getByText(/no notifications found/i)).toBeInTheDocument();
        // no "View all" footer when empty
        expect(screen.queryByText(/view all/i)).not.toBeInTheDocument();
    });

    it('renders vehicle, note, date, status and a View-all link when reminders exist', () => {
        render(<RemindersList reminders={sample} />);

        expect(screen.getByText('BMW X5')).toBeInTheDocument();
        expect(screen.getByText('XYZ-123')).toBeInTheDocument();
        expect(screen.getByText('Oil change due')).toBeInTheDocument();
        expect(screen.getByText('Urgent')).toBeInTheDocument();
        expect(screen.getByText('Pending')).toBeInTheDocument();
        expect(screen.getByText('2 New')).toBeInTheDocument();
        expect(screen.getByRole('link', { name: /view all/i })).toBeInTheDocument();
    });

    it("falls back to 'N/A' when the vehicle relation is null", () => {
        render(<RemindersList reminders={[sample[1]]} />);
        const nas = screen.getAllByText('N/A');
        // one for vehicle name, one for license plate
        expect(nas.length).toBeGreaterThanOrEqual(2);
    });

    it("falls back to 'No description' when note is null", () => {
        render(<RemindersList reminders={[sample[1]]} />);
        expect(screen.getByText(/no description/i)).toBeInTheDocument();
    });
});
