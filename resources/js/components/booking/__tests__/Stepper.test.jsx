import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import Stepper from '@/components/booking/Stepper';

const labels = ['Voiture', 'Dates', 'Vos infos', 'Paiement'];

function stepFor(label) {
    return screen.getByText(label).closest('li');
}

describe('Stepper', () => {
    it('lists every step', () => {
        render(<Stepper current={1} labels={labels} />);

        expect(screen.getAllByRole('listitem')).toHaveLength(4);
        labels.forEach((label) => expect(screen.getByText(label)).toBeInTheDocument());
    });

    /**
     * The whole point of the component. An earlier version of this rewrite
     * kept only `n <= current`, so on the last step all four rules were filled
     * and all four labels bold — done and current were indistinguishable and
     * the stepper stopped saying where you were.
     */
    it('distinguishes the current step from the completed ones', () => {
        render(<Stepper current={4} labels={labels} />);

        expect(stepFor('Paiement')).toHaveAttribute('aria-current', 'step');
        expect(stepFor('Voiture')).not.toHaveAttribute('aria-current');
        expect(stepFor('Dates')).not.toHaveAttribute('aria-current');
        expect(stepFor('Vos infos')).not.toHaveAttribute('aria-current');
    });

    it('marks exactly one step as current on every step', () => {
        for (let current = 1; current <= labels.length; current++) {
            const { unmount } = render(<Stepper current={current} labels={labels} />);

            expect(screen.getAllByRole('listitem').filter((li) => li.getAttribute('aria-current') === 'step'))
                .toHaveLength(1);
            expect(stepFor(labels[current - 1])).toHaveAttribute('aria-current', 'step');

            unmount();
        }
    });

    it('checks off completed steps and numbers the rest', () => {
        const { container } = render(<Stepper current={3} labels={labels} />);

        // Two steps done -> two checks; the current and upcoming ones keep numbers.
        expect(container.querySelectorAll('svg')).toHaveLength(2);
        expect(screen.queryByText('01')).not.toBeInTheDocument();
        expect(screen.queryByText('02')).not.toBeInTheDocument();
        expect(screen.getByText('03')).toBeInTheDocument();
        expect(screen.getByText('04')).toBeInTheDocument();
    });

    it('numbers every step while on the first one', () => {
        const { container } = render(<Stepper current={1} labels={labels} />);

        expect(container.querySelectorAll('svg')).toHaveLength(0);
        expect(screen.getByText('01')).toBeInTheDocument();
        expect(screen.getByText('04')).toBeInTheDocument();
    });
});
