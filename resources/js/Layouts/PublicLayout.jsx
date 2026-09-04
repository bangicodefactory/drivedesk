import { Link, usePage } from '@inertiajs/react';
import { Toaster } from '@/components/ui/sonner';
import { useEffect } from 'react';
import { toast } from 'sonner';

/**
 * Lightweight layout for the Auth pages (login, register, forgot/reset
 * password, verify email). No sidebar — just a centered content area with a
 * logo header. The public B2C storefront (Home, Fleet, Contact, etc.) uses
 * StorefrontLayout instead, which adds a full nav/footer that has no business
 * on an auth screen.
 *
 * Usage:
 *   LoginPage.layout = (page) => <PublicLayout>{page}</PublicLayout>;
 */
export default function PublicLayout({ children }) {
    const { branding, flash } = usePage().props;

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
        if (flash?.error)   toast.error(flash.error);
    }, [flash?.success, flash?.error]);

    return (
        <div className="min-h-screen bg-background">
            <header className="border-b bg-card">
                <div className="mx-auto max-w-7xl px-4 h-14 flex items-center">
                    <Link href="/" className="flex items-center gap-2">
                        {branding?.logoUrl && (
                            <img
                                src={branding.logoUrl}
                                alt={branding?.appName ?? 'Logo'}
                                className="h-8 w-auto object-contain"
                            />
                        )}
                        <span className="font-semibold text-sm">{branding?.appName}</span>
                    </Link>
                </div>
            </header>

            <main className="mx-auto max-w-7xl px-4 py-8">
                {children}
            </main>

            <Toaster richColors position="top-right" />
        </div>
    );
}
