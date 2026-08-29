import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/useTranslation';

// Fills Laravel-style `:name` placeholders in a translated string.
function fill(template, values) {
    return template.replace(/:(\w+)/g, (match, key) => (key in values ? String(values[key]) : match));
}

export default function Pagination({ paginator }) {
    const t = useTranslation();
    if (!paginator || paginator.last_page <= 1) return null;

    const previous = t('Previous');
    const next = t('Next');

    return (
        <nav
            aria-label={t('Pagination')}
            className="flex items-center justify-between mt-4 text-sm text-muted-foreground"
        >
            <span>
                {fill(t(':from–:to of :total'), { from: paginator.from, to: paginator.to, total: paginator.total })}
            </span>
            <div className="flex items-center gap-2">
                {paginator.prev_page_url ? (
                    <Button variant="outline" size="sm" asChild>
                        <Link href={paginator.prev_page_url} aria-label={previous}>
                            <ChevronLeft className="h-4 w-4 rtl:rotate-180" aria-hidden="true" /> {previous}
                        </Link>
                    </Button>
                ) : (
                    <Button variant="outline" size="sm" disabled aria-label={previous}>
                        <ChevronLeft className="h-4 w-4 rtl:rotate-180" aria-hidden="true" /> {previous}
                    </Button>
                )}
                <span className="px-2" aria-current="page">
                    {fill(t('Page :current of :last'), { current: paginator.current_page, last: paginator.last_page })}
                </span>
                {paginator.next_page_url ? (
                    <Button variant="outline" size="sm" asChild>
                        <Link href={paginator.next_page_url} aria-label={next}>
                            {next} <ChevronRight className="h-4 w-4 rtl:rotate-180" aria-hidden="true" />
                        </Link>
                    </Button>
                ) : (
                    <Button variant="outline" size="sm" disabled aria-label={next}>
                        {next} <ChevronRight className="h-4 w-4 rtl:rotate-180" aria-hidden="true" />
                    </Button>
                )}
            </div>
        </nav>
    );
}
