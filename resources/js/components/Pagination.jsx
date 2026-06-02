import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';

export default function Pagination({ paginator }) {
    if (!paginator || paginator.last_page <= 1) return null;

    return (
        <div className="flex items-center justify-between mt-4 text-sm text-muted-foreground">
            <span>
                {paginator.from}–{paginator.to} of {paginator.total}
            </span>
            <div className="flex items-center gap-2">
                {paginator.prev_page_url ? (
                    <Button variant="outline" size="sm" asChild>
                        <Link href={paginator.prev_page_url}>
                            <ChevronLeft className="h-4 w-4" /> Prev
                        </Link>
                    </Button>
                ) : (
                    <Button variant="outline" size="sm" disabled>
                        <ChevronLeft className="h-4 w-4" /> Prev
                    </Button>
                )}
                <span className="px-2">
                    {paginator.current_page} / {paginator.last_page}
                </span>
                {paginator.next_page_url ? (
                    <Button variant="outline" size="sm" asChild>
                        <Link href={paginator.next_page_url}>
                            Next <ChevronRight className="h-4 w-4" />
                        </Link>
                    </Button>
                ) : (
                    <Button variant="outline" size="sm" disabled>
                        Next <ChevronRight className="h-4 w-4" />
                    </Button>
                )}
            </div>
        </div>
    );
}
