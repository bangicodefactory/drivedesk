import './bootstrap';
import { createInertiaApp, router } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { ThemeProvider } from 'next-themes';

// Injects or updates a <style id="brand-dark"> element with .dark-scoped vars.
function injectDarkVars(darkMap) {
    const id = 'brand-dark';
    let el = document.getElementById(id);
    if (!el) {
        el = document.createElement('style');
        el.id = id;
        document.head.appendChild(el);
    }
    const rules = Object.entries(darkMap)
        .map(([k, v]) => `  ${k}: ${v};`)
        .join('\n');
    el.textContent = `.dark {\n${rules}\n}`;
}

function applyBranding(branding) {
    if (!branding) return;

    if (branding.cssVars) {
        const root = document.documentElement;

        if (branding.cssVars.light) {
            // BAN-243: full derived palette { light: {...}, dark: {...} }
            Object.entries(branding.cssVars.light).forEach(([k, v]) => root.style.setProperty(k, v));
            injectDarkVars(branding.cssVars.dark ?? {});
        } else {
            // Legacy: flat 3-var object (no brand_color set)
            Object.entries(branding.cssVars).forEach(([k, v]) => root.style.setProperty(k, v));
        }
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
