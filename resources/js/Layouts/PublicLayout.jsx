import { useState, useEffect } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Toaster } from '@/components/ui/sonner';
import { Button } from '@/components/ui/button';
import { toast } from 'sonner';
import { Phone, Globe, ChevronDown, Menu, X } from 'lucide-react';

const LOCALE_LABELS = {
    ar: 'العربية', fr: 'Français', en: 'English', nl: 'Nederlands',
    da: 'Dansk', de: 'Deutsch', es: 'Español', it: 'Italiano',
    ja: '日本語', pl: 'Polski', pt: 'Português', ru: 'Русский',
};

export default function PublicLayout({ children }) {
    const { branding, flash, translations, locale, client } = usePage().props;
    const supportedLocales = client?.supported_locales ?? [];
    const t = (key, fallback = key) => translations?.[key] ?? fallback;

    const [mobileOpen, setMobileOpen] = useState(false);
    const [langOpen, setLangOpen] = useState(false);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
        if (flash?.error)   toast.error(flash.error);
    }, [flash?.success, flash?.error]);

    const NAV = [
        { label: t('menu_home',    'Home'),    href: route('client.home') },
        { label: t('menu_cars',    'Cars'),    href: `${route('client.home')}#search` },
        { label: t('menu_about',   'About'),   href: `${route('client.home')}#about` },
        { label: t('menu_contact', 'Contact'), href: route('contact') },
    ];

    const otherLocales = supportedLocales.filter(l => l !== locale);

    return (
        <div className="min-h-screen bg-background">
            <header className="sticky top-0 z-50 border-b bg-card/95 backdrop-blur supports-[backdrop-filter]:bg-card/80">
                <div className="mx-auto max-w-7xl px-4">
                    <div className="flex h-16 items-center justify-between gap-4">

                        {/* Logo */}
                        <Link href={route('client.home')} className="flex items-center gap-2 shrink-0">
                            {branding?.logoUrl && (
                                <img
                                    src={branding.logoUrl}
                                    alt={branding?.appName ?? 'Logo'}
                                    className="h-10 w-auto object-contain"
                                    onError={e => { e.target.style.display = 'none'; }}
                                />
                            )}
                            {branding?.appName && (
                                <span className="font-semibold text-sm">{branding.appName}</span>
                            )}
                        </Link>

                        {/* Desktop nav */}
                        <nav className="hidden lg:flex items-center gap-7">
                            {NAV.map(({ label, href }) => (
                                <a
                                    key={href}
                                    href={href}
                                    className="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors"
                                >
                                    {label}
                                </a>
                            ))}
                        </nav>

                        {/* Desktop right side */}
                        <div className="hidden lg:flex items-center gap-4">
                            {/* Phone */}
                            {branding?.phone && (
                                <a
                                    href={`tel:${branding.phone}`}
                                    className="flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors"
                                >
                                    <Phone className="h-3.5 w-3.5" />
                                    {branding.phone}
                                </a>
                            )}

                            {/* Language switcher */}
                            {otherLocales.length > 0 && (
                                <div className="relative">
                                    <button
                                        onClick={() => setLangOpen(o => !o)}
                                        onBlur={() => setTimeout(() => setLangOpen(false), 150)}
                                        className="flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground transition-colors"
                                    >
                                        <Globe className="h-3.5 w-3.5" />
                                        <span>{LOCALE_LABELS[locale] ?? locale.toUpperCase()}</span>
                                        <ChevronDown className="h-3 w-3" />
                                    </button>
                                    {langOpen && (
                                        <div className="absolute right-0 top-full mt-1 bg-card border rounded-md shadow-md py-1 min-w-[140px] z-50">
                                            {otherLocales.map(l => (
                                                <a
                                                    key={l}
                                                    href={`/language/${l}`}
                                                    className="block px-3 py-1.5 text-sm hover:bg-muted transition-colors"
                                                >
                                                    {LOCALE_LABELS[l] ?? l.toUpperCase()}
                                                </a>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            )}

                            {/* Find a Car CTA */}
                            <a href={`${route('client.home')}#search`}>
                                <Button size="sm">{t('header_find_a_car', 'Find a Car')}</Button>
                            </a>
                        </div>

                        {/* Mobile hamburger */}
                        <button
                            className="lg:hidden p-2 -mr-2 rounded-md text-muted-foreground hover:text-foreground"
                            onClick={() => setMobileOpen(o => !o)}
                            aria-label="Toggle menu"
                        >
                            {mobileOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
                        </button>
                    </div>

                    {/* Mobile menu */}
                    {mobileOpen && (
                        <div className="lg:hidden border-t py-4 space-y-1">
                            {NAV.map(({ label, href }) => (
                                <a
                                    key={href}
                                    href={href}
                                    className="block px-2 py-2 text-sm text-muted-foreground hover:text-foreground transition-colors"
                                    onClick={() => setMobileOpen(false)}
                                >
                                    {label}
                                </a>
                            ))}
                            {otherLocales.length > 0 && (
                                <div className="px-2 pt-2 flex flex-wrap gap-2">
                                    {otherLocales.map(l => (
                                        <a key={l} href={`/language/${l}`} className="text-xs text-muted-foreground hover:text-foreground border rounded px-2 py-1">
                                            {LOCALE_LABELS[l] ?? l.toUpperCase()}
                                        </a>
                                    ))}
                                </div>
                            )}
                            <div className="px-2 pt-2">
                                <a href={`${route('client.home')}#search`}>
                                    <Button size="sm" className="w-full" onClick={() => setMobileOpen(false)}>
                                        {t('header_find_a_car', 'Find a Car')}
                                    </Button>
                                </a>
                            </div>
                        </div>
                    )}
                </div>
            </header>

            <main>
                {children}
            </main>

            <Toaster richColors position="top-right" />
        </div>
    );
}
