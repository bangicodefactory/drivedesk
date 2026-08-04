import { useEffect, useRef, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Eye, Pencil, Plus, Search, TriangleAlert, Trash2 } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import Pagination from '@/components/Pagination';
import { ConfidenceBadge, StatusBadge } from '@/components/ViolationBadges';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';

const ALL = '__all__';

function TrafficViolationIndex({
    violations = { data: [] },
    filters = {},
    statuses = {},
    unmatchedCount = 0,
}) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? '');
    const [confidence, setConfidence] = useState(filters.confidence ?? '');

    // Server-side filtering — the list is paginated, so filtering client-side
    // would only ever cover the current page.
    const isFirst = useRef(true);
    useEffect(() => {
        if (isFirst.current) {
            isFirst.current = false;
            return;
        }
        const timer = setTimeout(() => {
            const query = {};
            if (search) query.search = search;
            if (status) query.status = status;
            if (confidence) query.confidence = confidence;

            router.get(route('traffic-violation.index'), query, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        }, 300);
        return () => clearTimeout(timer);
    }, [search, status, confidence]);

    async function remove(id) {
        if (await confirmDialog({ title: t('Are you sure?') })) {
            router.delete(route('traffic-violation.destroy', id));
        }
    }

    const showActions = can('edit traffic violation') || can('delete traffic violation');
    const columnCount = showActions ? 8 : 7;

    return (
        <div className="space-y-6 p-6">
            <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
                <TriangleAlert className="h-6 w-6" /> {t('Traffic Violations')}
            </h1>

            {/* The queue that actually needs work, surfaced before the table so
                it reads as a task rather than a statistic. */}
            {unmatchedCount > 0 && confidence !== 'unmatched' && (
                <button
                    type="button"
                    onClick={() => setConfidence('unmatched')}
                    className="w-full rounded-xl border border-warning/40 bg-warning/10 px-4 py-3 text-left text-sm hover:bg-warning/20"
                >
                    <span className="font-semibold">{unmatchedCount}</span>{' '}
                    {t('violations are not linked to a rental yet. Review them.')}
                </button>
            )}

            <div className="flex flex-wrap items-center justify-between gap-2">
                <div className="flex flex-wrap items-center gap-2">
                    <div className="relative w-full max-w-xs">
                        <Search className="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder={t('Search violations…')}
                            className="pl-8"
                        />
                    </div>

                    <Select
                        value={status || ALL}
                        onValueChange={(v) => setStatus(v === ALL ? '' : v)}
                    >
                        <SelectTrigger className="w-[170px]" aria-label={t('Status')}>
                            <SelectValue placeholder={t('All statuses')} />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={ALL}>{t('All statuses')}</SelectItem>
                            {Object.entries(statuses).map(([key, label]) => (
                                <SelectItem key={key} value={key}>{t(label)}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    <Select
                        value={confidence || ALL}
                        onValueChange={(v) => setConfidence(v === ALL ? '' : v)}
                    >
                        <SelectTrigger className="w-[190px]" aria-label={t('Match')}>
                            <SelectValue placeholder={t('All matches')} />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={ALL}>{t('All matches')}</SelectItem>
                            <SelectItem value="unmatched">{t('Needs review')}</SelectItem>
                            <SelectItem value="exact">{t('Exact match')}</SelectItem>
                            <SelectItem value="probable">{t('Probable match')}</SelectItem>
                            <SelectItem value="none">{t('No match')}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                {can('create traffic violation') && (
                    <Button size="sm" asChild>
                        <Link href={route('traffic-violation.create')}>
                            <Plus className="mr-2 h-4 w-4" /> {t('Create Traffic Violation')}
                        </Link>
                    </Button>
                )}
            </div>

            <div className="rounded-xl border bg-card overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>{t('Reference')}</TableHead>
                            <TableHead>{t('Date')}</TableHead>
                            <TableHead>{t('Vehicle')}</TableHead>
                            <TableHead>{t('Renter')}</TableHead>
                            <TableHead>{t('Match')}</TableHead>
                            <TableHead>{t('Amount')}</TableHead>
                            <TableHead>{t('Status')}</TableHead>
                            {showActions && <TableHead className="text-right">{t('Action')}</TableHead>}
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {violations.data.length === 0 && (
                            <TableRow>
                                <TableCell colSpan={columnCount} className="text-center text-muted-foreground py-8">
                                    {search || status || confidence
                                        ? t('No violations match your search')
                                        : t('No traffic violations yet')}
                                </TableCell>
                            </TableRow>
                        )}
                        {violations.data.map((violation) => (
                            <TableRow key={violation.id}>
                                <TableCell className="font-medium">{violation.reference ?? '—'}</TableCell>
                                <TableCell className="whitespace-nowrap">
                                    {String(violation.occurred_at ?? '').replace('T', ' ').slice(0, 16)}
                                </TableCell>
                                <TableCell>
                                    <div>{violation.vehicle?.name ?? '—'}</div>
                                    <div className="text-xs text-muted-foreground">{violation.license_plate}</div>
                                </TableCell>
                                <TableCell>{violation.driver?.name ?? '—'}</TableCell>
                                <TableCell>
                                    <ConfidenceBadge
                                        confidence={violation.match_confidence}
                                        matchSource={violation.match_source}
                                        confirmedAt={violation.confirmed_at}
                                    />
                                </TableCell>
                                <TableCell>{violation.amount}</TableCell>
                                <TableCell>
                                    <StatusBadge status={violation.status} statuses={statuses} />
                                </TableCell>
                                {showActions && (
                                    <TableCell className="text-right space-x-1">
                                        <Button variant="ghost" size="icon" asChild>
                                            <Link href={route('traffic-violation.show', violation.id)} aria-label={t('View')}>
                                                <Eye className="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        {can('edit traffic violation') && (
                                            <Button variant="ghost" size="icon" asChild>
                                                <Link href={route('traffic-violation.edit', violation.id)} aria-label={t('Edit')}>
                                                    <Pencil className="h-4 w-4" />
                                                </Link>
                                            </Button>
                                        )}
                                        {can('delete traffic violation') && (
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                className="text-destructive hover:text-destructive"
                                                onClick={() => remove(violation.id)}
                                                aria-label={t('Delete')}
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
                                        )}
                                    </TableCell>
                                )}
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
                <Pagination paginator={violations} />
            </div>
        </div>
    );
}

TrafficViolationIndex.layout = (page) => {
    return (
        <AdminLayout breadcrumbs={[{ label: 'Traffic Violations' }]}>{page}</AdminLayout>
    );
};
export default TrafficViolationIndex;
