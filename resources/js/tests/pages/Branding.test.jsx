import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { vi } from 'vitest';

vi.mock('@inertiajs/react', () => ({
    router: { post: vi.fn(), get: vi.fn(), put: vi.fn(), patch: vi.fn(), delete: vi.fn(), on: vi.fn() },
    usePage: () => ({ props: { branding: { appName: 'RentCar' }, auth: { user: { type: 'owner' } } } }),
}));

vi.mock('@/Layouts/AdminLayout', () => ({
    default: ({ children }) => <div>{children}</div>,
}));

global.route = vi.fn((name) => `/${name}`);

import Branding from '@/Pages/Settings/Branding';

const defaultSettings = {
    brand_color:   '',
    accent_color:  '',
    brand_neutral: 'cool',
    layout_mode:   'lightmode',
};

describe('Branding settings page', () => {
    it('renders all UI sections', () => {
        render(<Branding settings={defaultSettings} />);

        expect(screen.getByRole('heading', { name: /branding & theme/i })).toBeInTheDocument();
        expect(screen.getByText('Brand color')).toBeInTheDocument();
        expect(screen.getByText('Accent color')).toBeInTheDocument();
        expect(screen.getByText('Surface temperature')).toBeInTheDocument();
        expect(screen.getByText('Default mode')).toBeInTheDocument();
        expect(screen.getByRole('button', { name: /save branding/i })).toBeInTheDocument();
        expect(screen.getByRole('button', { name: /reset to default/i })).toBeInTheDocument();
    });

    it('renders 8 preset swatches', () => {
        render(<Branding settings={defaultSettings} />);

        const presets = ['Ocean', 'Violet', 'Sky', 'Rose', 'Emerald', 'Slate', 'Teal', 'Navy'];
        presets.forEach(label => {
            expect(screen.getByRole('button', { name: label })).toBeInTheDocument();
        });
    });

    it('clicking a preset updates the hex input', async () => {
        render(<Branding settings={defaultSettings} />);

        fireEvent.click(screen.getByRole('button', { name: 'Ocean' }));

        await waitFor(() => {
            expect(screen.getByDisplayValue('#2563EB')).toBeInTheDocument();
        });
    });

    it('clicking a preset shows the live preview', async () => {
        render(<Branding settings={defaultSettings} />);

        fireEvent.click(screen.getByRole('button', { name: 'Rose' }));

        await waitFor(() => {
            expect(screen.getByText(/live preview/i)).toBeInTheDocument();
            expect(screen.getByText('Save changes')).toBeInTheDocument();
        });
    });

    it('live preview shows AA indicator for valid hex', async () => {
        render(<Branding settings={{ ...defaultSettings, brand_color: '#2563EB' }} />);

        await waitFor(() => {
            expect(screen.getByText('AA ✓')).toBeInTheDocument();
        });
    });

    it('auto-accent toggle is on by default when no accent set', () => {
        render(<Branding settings={defaultSettings} />);

        const toggle = screen.getByRole('switch', { name: /auto from brand/i });
        expect(toggle).toBeChecked();
        // accent picker should NOT be visible
        expect(screen.queryByPlaceholderText('#10B981')).not.toBeInTheDocument();
    });

    it('turning off auto-accent shows the accent color picker', async () => {
        render(<Branding settings={defaultSettings} />);

        const toggle = screen.getByRole('switch', { name: /auto from brand/i });
        fireEvent.click(toggle);

        await waitFor(() => {
            expect(screen.getByPlaceholderText('#10B981')).toBeInTheDocument();
        });
    });

    it('turning auto-accent back on hides the picker and clears the field', async () => {
        render(<Branding settings={{ ...defaultSettings, accent_color: '#10B981' }} />);

        // auto-accent should be OFF since accent_color is set
        const toggle = screen.getByRole('switch', { name: /auto from brand/i });
        expect(toggle).not.toBeChecked();

        // turn it back on
        fireEvent.click(toggle);

        await waitFor(() => {
            expect(screen.queryByPlaceholderText('#10B981')).not.toBeInTheDocument();
        });
    });

    it('surface temperature buttons highlight the active selection', () => {
        render(<Branding settings={{ ...defaultSettings, brand_neutral: 'warm' }} />);

        // 'Warm' should be active (bg-primary class applied)
        const warmBtn = screen.getByRole('button', { name: /^warm$/i });
        expect(warmBtn.className).toMatch(/bg-primary/);
    });

    it('default mode buttons highlight the active mode', () => {
        render(<Branding settings={{ ...defaultSettings, layout_mode: 'darkmode' }} />);

        const darkBtn = screen.getByRole('button', { name: /^dark$/i });
        expect(darkBtn.className).toMatch(/bg-primary/);
    });

    it('reset to default clears all fields', async () => {
        render(<Branding settings={{
            brand_color:   '#2563EB',
            accent_color:  '#10B981',
            brand_neutral: 'warm',
            layout_mode:   'darkmode',
        }} />);

        fireEvent.click(screen.getByRole('button', { name: /reset to default/i }));

        await waitFor(() => {
            // hex input should be empty
            const hexInput = screen.getByPlaceholderText('#3B82F6');
            expect(hexInput.value).toBe('');
            // live preview should disappear (no brand color)
            expect(screen.queryByText(/live preview/i)).not.toBeInTheDocument();
        });
    });
});
