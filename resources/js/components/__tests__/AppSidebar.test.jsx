import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import { usePage } from '@inertiajs/react';
import AppSidebar from '@/components/AppSidebar';

// Mock Inertia: usePage feeds the nav (auth/permissions/url/branding); Link → <a>.
vi.mock('@inertiajs/react', () => ({
    usePage: vi.fn(),
    Link: ({ href, children }) => <a href={href}>{children}</a>,
}));

// Stub the shadcn sidebar/collapsible primitives with lightweight passthroughs so
// AppSidebar can render without a SidebarProvider (useSidebar) / matchMedia. Active
// state is surfaced as data-active for assertions.
vi.mock('@/components/ui/sidebar', () => {
    const pass = ({ children }) => <div>{children}</div>;
    const btn = ({ children, isActive }) => <div data-active={isActive ? 'true' : 'false'}>{children}</div>;
    return {
        Sidebar: pass, SidebarHeader: pass, SidebarContent: pass, SidebarFooter: pass,
        SidebarGroup: pass, SidebarGroupLabel: pass, SidebarMenu: pass, SidebarMenuItem: pass,
        SidebarMenuSub: pass, SidebarMenuSubItem: pass, SidebarRail: () => null,
        SidebarMenuButton: btn, SidebarMenuSubButton: btn,
    };
});
vi.mock('@/components/ui/collapsible', () => ({
    Collapsible: ({ children }) => <div>{children}</div>,
    CollapsibleTrigger: ({ children }) => <div>{children}</div>,
    CollapsibleContent: ({ children }) => <div>{children}</div>,
}));

// route() global — return absolute URLs so `new URL(...).pathname` works in useIsActive.
const PATHS = {
    dashboard: '/dashboard',
    'vehicle.index': '/vehicle',
    'driver.index': '/driver',
    'booking.index': '/booking',
    'booking_requests.index': '/booking_requests',
    'tva.index': '/tva',
    'tva.report': '/tva-report',
};
globalThis.route = (name) => 'http://localhost' + (PATHS[name] ?? '/' + String(name).replace(/\./g, '/'));

function renderSidebar(url, permissions) {
    usePage.mockReturnValue({
        url,
        props: { auth: { permissions, user: { type: 'owner' } }, client: {}, branding: {}, translations: {} },
    });
    return render(<AppSidebar />);
}

// data-active for the menu row that contains the given label text.
const activeFor = (label) => screen.getByText(label).closest('[data-active]')?.getAttribute('data-active');

describe('AppSidebar', () => {
    it('renders only nav items the user has permission for', () => {
        renderSidebar('/vehicle', ['manage vehicle']);
        expect(screen.getByText('Vehicles')).toBeInTheDocument();
        expect(screen.queryByText('Drivers')).toBeNull();   // needs 'manage driver'
        expect(screen.queryByText('Bookings')).toBeNull();  // needs 'manage booking'
    });

    it('marks the active item by exact/sub-path, not raw prefix (Bookings vs Booking Requests)', () => {
        renderSidebar('/booking_requests', ['manage booking']);
        expect(activeFor('Booking Requests')).toBe('true');
        expect(activeFor('Bookings')).toBe('false'); // '/booking' must NOT match '/booking_requests'
    });

    it('keeps the parent route active on detail/edit sub-paths', () => {
        renderSidebar('/vehicle/1/edit', ['manage vehicle']);
        expect(activeFor('Vehicles')).toBe('true');
    });

    it('does not let /tva match /tva-report (TVA sub-items)', () => {
        renderSidebar('/tva-report', ['manage tva', 'manage tva report']);
        expect(activeFor('TVA Report')).toBe('true');
        expect(activeFor('TVA Management')).toBe('false');
    });
});
