import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/useTranslation';

// Fills Laravel-style `:name` placeholders in a translated string. Missing
// values render empty (LengthAwarePaginator returns from/to = null past the
// last page), unknown placeholders are left untouched.
function fill(template, values) {
    return template.replace(/:(\w+)/g, (match, key) => (key in values ? String(values[key] ?? '') : match));
}

// One prev/next control: a Link inside the Button when there is a page to go
// to, otherwise the same Button disabled. Chevrons flip under dir="rtl".
function PageControl({ href, label, Icon, iconAfter }) {
    const content = iconAfter ? (
        <>{label} <Icon className="h-4 w-4 rtl:rotate-180" aria-hidden="true" /></>
    ) : (
        <><Icon className="h-4 w-4 rtl:rotate-180" aria-hidden="true" /> {label}</>
    );

    if (!href) {
        return <Button variant="outline" size="sm" disabled>{content}</Button>;
    }
    return (
        <Button variant="outline" size="sm" asChild>
            <Link href={href}>{content}</Link>
        </Button>
    );
}

export default function Pagination({ paginator }) {
    const t = useTranslation();
    if (!paginator || paginator.last_page <= 1) return null;

    return (
        <nav
            aria-label={t('Pagination')}
            className="flex items-center justify-between mt-4 text-sm text-muted-foreground"
        >
            <span>
                {fill(t(':from–:to of :total'), { from: paginator.from, to: paginator.to, total: paginator.total })}
            </span>
            <div className="flex items-center gap-2">
                <PageControl href={paginator.prev_page_url} label={t('Previous')} Icon={ChevronLeft} />
                <span className="px-2">
                    {fill(t('Page :current of :last'), { current: paginator.current_page, last: paginator.last_page })}
                </span>
                <PageControl href={paginator.next_page_url} label={t('Next')} Icon={ChevronRight} iconAfter />
            </div>
        </nav>
    );
}
