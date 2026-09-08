/**
 * Dark banner used at the top of inner storefront pages (Booking, Fleet,
 * Contact, ...). Uses the brand primary token (Setting-driven via
 * ThemePalette — see branding.cssVars) rather than a hardcoded color, so it
 * always matches the active client's configured brand color.
 */
export default function PageBanner({ title, subtitle }) {
    return (
        <div className="relative bg-foreground text-background py-16 md:py-20 overflow-hidden">
            <div className="absolute inset-0 bg-gradient-to-br from-foreground via-foreground to-primary/20" />
            <div className="relative container mx-auto px-4">
                <h1 className="font-display text-4xl sm:text-5xl">{title}</h1>
                {subtitle && <p className="mt-3 text-base sm:text-lg text-background/60 max-w-xl">{subtitle}</p>}
            </div>
            <div className="absolute inset-x-0 bottom-0 h-1 bg-primary" />
        </div>
    );
}
