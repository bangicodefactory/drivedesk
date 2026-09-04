/**
 * Dark banner used at the top of inner storefront pages (Booking, Fleet,
 * Contact, ...). Uses the brand primary token (Setting-driven via
 * ThemePalette — see branding.cssVars) rather than a hardcoded color, so it
 * always matches the active client's configured brand color.
 */
export default function PageBanner({ title, subtitle }) {
    return (
        <div className="bg-primary text-primary-foreground py-10 md:py-14">
            <div className="container mx-auto px-4">
                <h1 className="text-3xl sm:text-4xl font-bold mb-3">{title}</h1>
                {subtitle && <p className="text-lg text-primary-foreground/80">{subtitle}</p>}
            </div>
        </div>
    );
}
