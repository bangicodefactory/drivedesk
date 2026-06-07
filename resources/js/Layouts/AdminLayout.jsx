import { useEffect } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { toast } from 'sonner';
import { Toaster } from '@/components/ui/sonner';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { ConfirmProvider } from '@/components/ui/confirm-dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarInset, SidebarProvider, SidebarTrigger,
} from '@/components/ui/sidebar';
import AppSidebar from '@/components/AppSidebar';
import { cn } from '@/lib/utils';
import { useTranslation } from '@/hooks/useTranslation';
import { initials } from '@/lib/nav';
import { LogOut, Languages, Check } from 'lucide-react';

// ─────────────────────────────────────────────────────────────────────────────
// Language switcher
// ─────────────────────────────────────────────────────────────────────────────

// Locales accepted by the SetLocale middleware ($supportedLanguages).
const LOCALES = [
    { code: 'en', label: 'English' },
    { code: 'fr', label: 'Français' },
    { code: 'ar', label: 'العربية' },
];

function LanguageSwitcher() {
    const { auth } = usePage().props;
    const current = auth?.user?.lang || 'fr';

    function change(code) {
        if (code === current) return;
        // GET /language/{lang} persists user.lang + session and redirects back.
        router.get(route('language.change', code), {}, { preserveScroll: true });
    }

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="icon" aria-label="Change language">
                    <Languages className="h-5 w-5" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-40">
                {LOCALES.map((l) => (
                    <DropdownMenuItem
                        key={l.code}
                        onClick={() => change(l.code)}
                        className={cn('cursor-pointer', current === l.code && 'font-semibold')}
                    >
                        <span className="flex-1">{l.label}</span>
                        {current === l.code && <Check className="ml-2 h-4 w-4" />}
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

// ─────────────────────────────────────────────────────────────────────────────
// UserMenu
// ─────────────────────────────────────────────────────────────────────────────

function UserMenu() {
    const { auth } = usePage().props;
    const t = useTranslation();
    const user = auth.user;
    const canManageSettings = auth.user?.type === 'super admin' || [
        'manage general settings', 'manage account settings', 'manage password settings',
        'manage company settings', 'manage email settings', 'manage payment settings',
        'manage seo settings', 'manage google recaptcha settings',
    ].some((p) => auth.permissions.includes(p));
    const profileSrc = user?.profile
        ? `/storage/upload/profile/${user.profile}`
        : null;

    function logout() {
        router.post(route('logout'));
    }

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" className="relative h-8 w-8 rounded-full">
                    <Avatar className="h-8 w-8">
                        <AvatarImage src={profileSrc} alt={user?.name} />
                        <AvatarFallback>{initials(user?.name)}</AvatarFallback>
                    </Avatar>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent className="w-56" align="end" forceMount>
                <DropdownMenuLabel className="font-normal">
                    <div className="flex flex-col space-y-1">
                        <p className="text-sm font-medium">{user?.name}</p>
                        <p className="text-xs text-muted-foreground">{user?.email}</p>
                    </div>
                </DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                    <Link href={route('setting.account')}>{t('Profile')}</Link>
                </DropdownMenuItem>
                {canManageSettings && (
                    <DropdownMenuItem asChild>
                        <Link href={route('setting.general')}>{t('Settings')}</Link>
                    </DropdownMenuItem>
                )}
                <DropdownMenuSeparator />
                <DropdownMenuItem onClick={logout} className="text-destructive focus:text-destructive">
                    <LogOut className="mr-2 h-4 w-4" />
                    {t('Log out')}
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

// ─────────────────────────────────────────────────────────────────────────────
// Breadcrumbs
// ─────────────────────────────────────────────────────────────────────────────

function Breadcrumbs({ items }) {
    const t = useTranslation();
    if (!items?.length) return null;

    // Labels are passed in English; translate here (a real component, so the
    // hook is valid — never call useTranslation() inside a page's static
    // `.layout` function, which Inertia invokes outside React's render tree).
    return (
        <nav aria-label="breadcrumb" className="flex items-center gap-1 text-sm text-muted-foreground">
            {items.map((crumb, i) => (
                <span key={i} className="flex items-center gap-1">
                    {i > 0 && <span className="select-none">/</span>}
                    {crumb.href
                        ? <Link href={crumb.href} className="hover:text-foreground transition-colors">{t(crumb.label)}</Link>
                        : <span className="text-foreground font-medium">{t(crumb.label)}</span>
                    }
                </span>
            ))}
        </nav>
    );
}

// ─────────────────────────────────────────────────────────────────────────────
// FlashToaster — reads flash shared prop and fires Sonner toasts
// ─────────────────────────────────────────────────────────────────────────────

function FlashToaster() {
    const { flash } = usePage().props;

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
        if (flash?.error)   toast.error(flash.error);
    }, [flash?.success, flash?.error]);

    return null;
}

// ─────────────────────────────────────────────────────────────────────────────
// AdminLayout — shadcn sidebar shell (collapsible-to-icon, sidebar-07 style)
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Persistent admin shell for all ported admin pages.
 *
 *   SomePage.layout = (page) => <AdminLayout breadcrumbs={[{label:'Cars',href:route('vehicle.index')},{label:'Edit'}]}>{page}</AdminLayout>;
 *
 * The sidebar collapses to icons via the trigger (or the rail / Ctrl+B), and the
 * collapsed state persists across navigations (SidebarProvider cookie).
 */
export default function AdminLayout({ children, breadcrumbs }) {
    const t = useTranslation();
    // Seed the open/collapsed state from the cookie SidebarProvider writes, so a
    // collapsed rail survives a full page reload (no SSR to read it for us).
    const defaultOpen = typeof document === 'undefined'
        || !/(?:^|;\s*)sidebar_state=false(?:;|$)/.test(document.cookie);

    return (
        <SidebarProvider defaultOpen={defaultOpen}>
            <AppSidebar />
            <SidebarInset>
                {/* TopBar */}
                <header className="flex h-14 shrink-0 items-center gap-2 border-b bg-card px-4 print:hidden">
                    <SidebarTrigger className="-ml-1" />
                    <Separator orientation="vertical" className="mr-1 h-5" />
                    <div className="flex-1">
                        <Breadcrumbs items={breadcrumbs} />
                    </div>
                    <LanguageSwitcher />
                    <UserMenu />
                </header>

                {/* Page content */}
                <div className="flex flex-1 flex-col overflow-y-auto print:overflow-visible">
                    <ConfirmProvider>
                        <div className="flex min-h-full flex-col">
                            <div className="flex-1">{children}</div>
                            <footer className="print:hidden border-t px-6 py-3 text-center text-xs text-muted-foreground">
                                {t('Developed by')}{' '}
                                <a
                                    href="https://bangicode.ma/"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="font-semibold text-foreground/80 underline-offset-2 transition-colors hover:text-primary hover:underline"
                                >
                                    Bangicode
                                </a>
                            </footer>
                        </div>
                    </ConfirmProvider>
                </div>
            </SidebarInset>

            {/* Flash toasts + Sonner container */}
            <FlashToaster />
            <Toaster richColors position="top-right" />
        </SidebarProvider>
    );
}
