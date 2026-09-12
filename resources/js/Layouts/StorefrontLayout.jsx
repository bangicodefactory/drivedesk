import '@/../css/storefront.css';
import { useEffect, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sheet, SheetContent, SheetTrigger,
} from '@/components/ui/sheet';
import { Toaster } from '@/components/ui/sonner';
import { toast } from 'sonner';
import {
    Menu, Phone, Mail, MapPin, Clock, ExternalLink, MessageCircle, ChevronDown, LogIn,
} from 'lucide-react';
import { useTranslations } from '@/hooks/useTranslations';
import StorefrontLoader from '@/components/StorefrontLoader';

const LOCALES = [
    { code: 'fr', label: 'Français' },
    { code: 'en', label: 'English' },
    { code: 'ar', label: 'العربية' },
];

/**
 * Nav items are added here as their route lands (CLAUDE.md §5 — port one
 * route at a time). Fleet/Booking/Services/About/Travel Guide are not wired
 * up yet; adding a route() call for a route that doesn't exist would throw a
 * Ziggy error client-side, not just 404.
 */
function useNavItems(t) {
    return [
        { key: 'home', label: t('nav_home', 'Accueil'), href: '/' },
        { key: 'reserve', label: t('nav_book_now', 'Réserver'), href: route('reserve.create') },
        { key: 'contact', label: t('nav_contact', 'Contact'), href: route('contact') },
    ];
}

function Header({ contact }) {
    const t = useTranslations();
    const { url, props } = usePage();
    const branding = props.branding ?? {};
    const navItems = useNavItems(t);
    const [mobileOpen, setMobileOpen] = useState(false);

    return (
        <header className="sticky top-0 z-40 border-b border-border/60 bg-background/90 backdrop-blur-md">
            <div className="container mx-auto px-4 h-20 flex items-center justify-between gap-4">
                <Link href="/" className="flex items-center gap-2.5 shrink-0">
                    {branding?.logoUrl && (
                        <img src={branding.logoUrl} alt={branding?.appName ?? 'Logo'} className="h-8 w-auto object-contain" />
                    )}
                    <span className="font-display text-2xl tracking-tight">{branding?.appName}</span>
                </Link>

                <nav className="hidden lg:flex items-center gap-8">
                    {navItems.map(item => (
                        <Link
                            key={item.key}
                            href={item.href}
                            className={`relative py-2 text-sm font-medium transition-colors ${
                                url === item.href ? 'text-foreground' : 'text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            {item.label}
                            {url === item.href && <span className="absolute inset-x-0 -bottom-[1px] h-0.5 bg-primary" />}
                        </Link>
                    ))}
                </nav>

                <div className="hidden lg:flex items-center gap-3">
                    {contact?.phone && (
                        <a href={`tel:${contact.phone}`} className="p-2.5 rounded-full border border-border/60 text-muted-foreground hover:text-foreground hover:border-foreground/30 transition-colors" aria-label={contact.phone}>
                            <Phone className="h-4 w-4" />
                        </a>
                    )}
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <button className="flex items-center gap-1 px-3 py-2.5 rounded-full border border-border/60 text-sm text-muted-foreground hover:text-foreground hover:border-foreground/30 transition-colors">
                                {LOCALES.find(l => l.code === props.locale)?.label ?? 'Français'}
                                <ChevronDown className="h-3.5 w-3.5" />
                            </button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            {LOCALES.map(l => (
                                <DropdownMenuItem key={l.code} asChild>
                                    <Link href={route('language.change', l.code)}>{l.label}</Link>
                                </DropdownMenuItem>
                            ))}
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <Link href={route('login')} className="p-2.5 rounded-full border border-border/60 text-muted-foreground hover:text-foreground hover:border-foreground/30 transition-colors" aria-label={t('nav_login', 'Connexion')}>
                        <LogIn className="h-4 w-4" />
                    </Link>
                    {contact?.whatsapp && (
                        <a href={`https://wa.me/${contact.whatsapp}`} target="_blank" rel="noopener noreferrer">
                            <Button size="sm" className="rounded-full px-5">
                                <MessageCircle className="h-4 w-4" /> {t('nav_whatsapp', 'WhatsApp')}
                            </Button>
                        </a>
                    )}
                </div>

                <Sheet open={mobileOpen} onOpenChange={setMobileOpen}>
                    <SheetTrigger asChild>
                        <button className="lg:hidden p-2 rounded-full border border-border/60" aria-label="Menu">
                            <Menu className="h-5 w-5" />
                        </button>
                    </SheetTrigger>
                    <SheetContent side="right" className="w-72">
                        <nav className="flex flex-col gap-1 mt-8">
                            {navItems.map(item => (
                                <Link
                                    key={item.key}
                                    href={item.href}
                                    onClick={() => setMobileOpen(false)}
                                    className={`px-4 py-3 rounded-lg text-sm font-medium ${
                                        url === item.href ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'
                                    }`}
                                >
                                    {item.label}
                                </Link>
                            ))}
                        </nav>
                        <div className="mt-6 flex flex-col gap-2">
                            {contact?.phone && (
                                <a href={`tel:${contact.phone}`} className="flex items-center gap-2 px-4 py-2 text-sm">
                                    <Phone className="h-4 w-4" /> {contact.phone}
                                </a>
                            )}
                            <div className="flex gap-2 px-4">
                                {LOCALES.map(l => (
                                    <Link key={l.code} href={route('language.change', l.code)} className="text-sm px-2 py-1 rounded border">
                                        {l.code.toUpperCase()}
                                    </Link>
                                ))}
                            </div>
                            {contact?.whatsapp && (
                                <a href={`https://wa.me/${contact.whatsapp}`} target="_blank" rel="noopener noreferrer" className="px-4">
                                    <Button className="w-full bg-green-600 hover:bg-green-700">
                                        <MessageCircle className="h-4 w-4" /> {t('nav_whatsapp', 'WhatsApp')}
                                    </Button>
                                </a>
                            )}
                            <Link href={route('login')} onClick={() => setMobileOpen(false)} className="flex items-center gap-2 px-4 py-2 text-sm text-muted-foreground hover:text-foreground">
                                <LogIn className="h-4 w-4" /> {t('nav_login', 'Connexion')}
                            </Link>
                        </div>
                    </SheetContent>
                </Sheet>
            </div>
        </header>
    );
}

function Footer({ contact }) {
    // Exactly the fields the block below can render -- kept in step with them so
    // adding a row here cannot leave the heading hiding when there is content.
    const hasContactDetails = Boolean(
        contact?.address
        || contact?.phone
        || contact?.email
        || contact?.hoursWeekday
        || contact?.hoursSaturday
        || contact?.hoursSunday,
    );

    const t = useTranslations();
    const { branding } = usePage().props;
    const navItems = useNavItems(t);
    const year = new Date().getFullYear();

    return (
        <footer className="bg-foreground text-background pt-20 pb-8">
            {/* Column count follows the number of columns actually rendered:
                with the contact block gated, a fixed lg:grid-cols-4 left the
                footer's right quarter blank on exactly the tenant that fix
                targets. */}
            <div className={`container mx-auto px-4 grid grid-cols-1 gap-10 md:grid-cols-2 ${hasContactDetails ? 'lg:grid-cols-4' : 'lg:grid-cols-3'}`}>
                <div className="space-y-4">
                    <div className="flex items-center gap-2.5">
                        {branding?.logoUrl && <img src={branding.logoUrl} alt={branding?.appName} className="h-8 w-auto object-contain brightness-0 invert" />}
                        <span className="font-display text-2xl tracking-tight">{branding?.appName}</span>
                    </div>
                    <p className="text-sm text-background/60 leading-relaxed max-w-xs">
                        {t('footer_about_text', 'A professional car rental agency providing quality vehicles and excellent service.')}
                    </p>
                    <div className="flex gap-4 pt-1">
                        {contact?.facebookUrl && (
                            <a href={contact.facebookUrl} target="_blank" rel="noopener noreferrer" aria-label="Facebook" className="flex items-center gap-1.5 text-background/60 hover:text-background text-sm transition-colors">
                                <ExternalLink className="h-4 w-4" /> Facebook
                            </a>
                        )}
                        {contact?.instagramUrl && (
                            <a href={contact.instagramUrl} target="_blank" rel="noopener noreferrer" aria-label="Instagram" className="flex items-center gap-1.5 text-background/60 hover:text-background text-sm transition-colors">
                                <ExternalLink className="h-4 w-4" /> Instagram
                            </a>
                        )}
                    </div>
                </div>

                <div className="space-y-4">
                    <h3 className="eyebrow text-xs font-semibold text-background/50">{t('footer_quicklinks_title', 'Quick Links')}</h3>
                    <ul className="space-y-2.5 text-sm">
                        {navItems.map(item => (
                            <li key={item.key}>
                                <Link href={item.href} className="text-background/70 hover:text-background transition-colors">
                                    {item.label}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </div>

                {/* The heading used to render unconditionally while every row below
                    it was conditional, so a tenant with empty company settings --
                    which includes drivedesk's own demo -- published a labelled
                    empty column. Gate the whole block on there being something to
                    put in it. */}
                {hasContactDetails && (
                <div className="space-y-4">
                    <h3 className="eyebrow text-xs font-semibold text-background/50">{t('footer_contact_title', 'Contact Us')}</h3>
                    <ul className="space-y-2.5 text-sm text-background/70">
                        {contact?.address && (
                            <li className="flex items-start gap-2"><MapPin className="h-4 w-4 mt-0.5 shrink-0 text-primary" /> {contact.address}</li>
                        )}
                        {contact?.phone && (
                            <li className="flex items-center gap-2"><Phone className="h-4 w-4 shrink-0 text-primary" /> {contact.phone}</li>
                        )}
                        {contact?.email && (
                            <li className="flex items-center gap-2"><Mail className="h-4 w-4 shrink-0 text-primary" /> {contact.email}</li>
                        )}
                        {(contact?.hoursWeekday || contact?.hoursSaturday || contact?.hoursSunday) && (
                            <li className="flex items-start gap-2">
                                <Clock className="h-4 w-4 mt-0.5 shrink-0 text-primary" />
                                <span>
                                    {contact.hoursWeekday && <>{t('hours_weekday_label', 'Mon–Fri')}: {contact.hoursWeekday}<br /></>}
                                    {contact.hoursSaturday && <>{t('hours_saturday_label', 'Sat')}: {contact.hoursSaturday}<br /></>}
                                    {contact.hoursSunday && <>{t('hours_sunday_label', 'Sun')}: {contact.hoursSunday}</>}
                                </span>
                            </li>
                        )}
                    </ul>
                </div>
                )}

                <div className="space-y-4">
                    <h3 className="eyebrow text-xs font-semibold text-background/50">{t('footer_languages_title', 'Languages')}</h3>
                    <ul className="space-y-2.5 text-sm text-background/70">
                        {LOCALES.map(l => (
                            <li key={l.code}>
                                <Link href={route('language.change', l.code)} className="hover:text-background transition-colors">
                                    {l.label}
                                </Link>
                            </li>
                        ))}
                    </ul>
                    {contact?.whatsapp && (
                        <a href={`https://wa.me/${contact.whatsapp}`} target="_blank" rel="noopener noreferrer">
                            <Button className="w-full bg-green-600 hover:bg-green-700 mt-1">
                                <MessageCircle className="h-4 w-4" /> {t('footer_whatsapp_button', 'Chat on WhatsApp')}
                            </Button>
                        </a>
                    )}
                </div>
            </div>

            <div className="container mx-auto px-4 mt-14 pt-8 border-t border-background/10 flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-4 text-center text-sm text-background/50">
                <span>© {year} {branding?.appName}. {t('footer_rights', 'All rights reserved.')}</span>
                <Link href={route('login')} className="inline-flex items-center gap-1 hover:text-background transition-colors">
                    <LogIn className="h-3.5 w-3.5" /> {t('nav_login', 'Connexion')}
                </Link>
            </div>
        </footer>
    );
}

function WhatsAppFloat({ whatsapp }) {
    if (!whatsapp) return null;
    return (
        <a
            href={`https://wa.me/${whatsapp}`}
            target="_blank"
            rel="noopener noreferrer"
            className="fixed bottom-6 end-6 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-green-600 text-white shadow-lg hover:bg-green-700 transition-colors"
            aria-label="WhatsApp"
        >
            <MessageCircle className="h-7 w-7" />
        </a>
    );
}

/**
 * Chrome for the public B2C rental storefront (Home, Fleet, Booking, Contact,
 * Services, About, Travel Guide). Deliberately separate from PublicLayout,
 * which is also used by the Auth pages (login/register/etc.) — a full
 * storefront nav/footer has no business there.
 */
export default function StorefrontLayout({ children }) {
    const { contact, flash } = usePage().props;
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
        if (flash?.error) toast.error(flash.error);
    }, [flash?.success, flash?.error]);

    // Every storefront navigation (Landing → car → booking → confirmation) is
    // a real Inertia visit, not a client-side cache hit — show feedback while
    // one is in flight instead of leaving the page looking frozen.
    useEffect(() => {
        const removeStart = router.on('start', () => setLoading(true));
        const removeFinish = router.on('finish', () => setLoading(false));
        return () => { removeStart(); removeFinish(); };
    }, []);

    return (
        <div className="storefront min-h-screen bg-background flex flex-col">
            {loading && <StorefrontLoader />}
            <Header contact={contact} />
            <main className="flex-1">{children}</main>
            <Footer contact={contact} />
            <WhatsAppFloat whatsapp={contact?.whatsapp} />
            <Toaster richColors position="top-right" />
        </div>
    );
}
