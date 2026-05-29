import './bootstrap';
import { createInertiaApp, router } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { ThemeProvider } from 'next-themes';

function applyBranding(branding) {
    if (!branding) return;

    if (branding.cssVars) {
        const root = document.documentElement;
        Object.entries(branding.cssVars).forEach(([key, value]) => {
            root.style.setProperty(key, value);
        });
    }

    if (branding.layoutDirection) {
        document.documentElement.dir =
            branding.layoutDirection === 'rtlmode' ? 'rtl' : 'ltr';
    }
}

createInertiaApp({
    title: (title) => `${title} - ${import.meta.env.VITE_APP_NAME ?? 'RentCar'}`,
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.jsx');
        return pages[`./Pages/${name}.jsx`]();
    },
    setup({ el, App, props }) {
        const branding = props.initialPage.props.branding;

        // Apply before first paint so there's no theme flash
        applyBranding(branding);

        // Keep in sync on SPA navigations (e.g. admin changes theme mid-session)
        router.on('navigate', (event) => {
            applyBranding(event.detail.page.props.branding);
        });

        const initialTheme = branding?.layoutMode === 'darkmode' ? 'dark' : 'light';

        createRoot(el).render(
            <ThemeProvider
                attribute="class"
                defaultTheme={initialTheme}
                enableSystem={false}
            >
                <App {...props} />
            </ThemeProvider>
        );
    },
});
