import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';

vi.mock('@/hooks/useTranslation', () => ({ useTranslation: () => (k) => k }));

import { BlacklistNotice } from '../BlacklistNotice';

const drivers = [
    { id: 1, name: 'Clean Carla', blacklisted: false },
    { id: 2, name: 'Risky Rachid', blacklisted: true, blacklist_reason: 'No-show' },
];

describe('BlacklistNotice', () => {
    it('renders nothing when the selected driver is not blacklisted', () => {
        const { container } = render(<BlacklistNotice drivers={drivers} selectedIds={[1]} />);
        expect(container).toBeEmptyDOMElement();
    });

    it('renders nothing when no driver / "none" is selected', () => {
        const { container } = render(<BlacklistNotice drivers={drivers} selectedIds={['', 'none']} />);
        expect(container).toBeEmptyDOMElement();
    });

    it('warns immediately with the reason when a blacklisted driver is selected', () => {
        render(<BlacklistNotice drivers={drivers} selectedIds={[2]} />);
        const alert = screen.getByRole('alert');
        expect(alert).toHaveTextContent('Driver is blacklisted');
        expect(alert).toHaveTextContent('Risky Rachid');
        expect(alert).toHaveTextContent('No-show');
    });
});
