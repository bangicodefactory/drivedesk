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
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { AlertTriangle, Eye, Pencil, Plus, Search, TriangleAlert, Trash2, Upload } from 'lucide-react';
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
    const { auth, flash } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    const [importOpen, setImportOpen] = useState(false);
    const [importFile, setImportFile] = useState(null);
    const importSkipped = flash?.import_skipped;

    // The import redirects back here; if any row was rejected, reopen the
    // dialog so the reasons are seen rather than lost behind a toast.
    useEffect(() => {
        if (importSkipped?.length > 0) {
            setImportOpen(true);
        }
    }, [importSkipped]);

    function submitImport(e) {
        e.preventDefault();
        if (!importFile) return;
        router.post(
            route('traffic-violation.import'),
            { file: importFile },
            {
                forceFormData: true,
                onSuccess: () => setImportFile(null),
            },
        );
    }

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
                    className="w-full rounded-xl border border-warning/40 bg-warning/10 px-4 py-3 text-start text-sm hover:bg-warning/20"
                >
                    <span className="font-semibold">{unmatchedCount}</span>{' '}
                    {t('violations are not linked to a rental yet. Review them.')}
                </button>
            )}

            <div className="flex flex-wrap items-center justify-between gap-2">
                <div className="flex flex-wrap items-center gap-2">
                    <div className="relative w-full max-w-xs">
                        <Search className="pointer-events-none absolute start-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder={t('Search violations…')}
                            className="ps-8"
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
                    <div className="flex items-center gap-2">
                        <Dialog open={importOpen} onOpenChange={setImportOpen}>
                            <DialogTrigger asChild>
                                <Button variant="outline" size="sm">
                                    <Upload className="me-2 h-4 w-4" /> {t('Import')}
                                </Button>
                            </DialogTrigger>
                            <DialogContent className="flex max-h-[90vh] flex-col gap-0 p-0 sm:max-w-2xl">
                                <DialogHeader className="border-b px-6 py-4 text-start">
                                    <DialogTitle>{t('Import Traffic Violations')}</DialogTitle>
                                </DialogHeader>
                                <form onSubmit={submitImport} className="flex min-h-0 flex-1 flex-col">
                                    <div className="flex min-h-0 flex-1 flex-col gap-4 overflow-y-auto px-6 py-4">
                                        <div className="space-y-1.5">
                                            <Label htmlFor="importFile">{t('File')}</Label>
                                            <Input
                                                id="importFile"
                                                type="file"
                                                accept=".xlsx,.xls,.csv"
                                                required
                                                onChange={(e) => setImportFile(e.target.files?.[0] ?? null)}
                                            />
                                            <p className="text-sm text-muted-foreground">
                                                {t('Upload an .xlsx or .xls file. Download the')}{' '}
                                                <a
                                                    href={route('traffic-violation.template')}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className="font-medium text-primary underline underline-offset-2"
                                                >
                                                    {t('template')}
                                                </a>{' '}
                                                {t('to see the required format.')}
                                            </p>
                                        </div>

                                        <div className="rounded-md border bg-muted/50 px-3 py-2 text-xs text-muted-foreground">
                                            <strong className="font-semibold text-foreground">
                                                {t('Expected format (7 columns):')}
                                            </strong>
                                            <p className="mt-1 font-mono leading-relaxed">
                                                REFERENCE | IMMATRICULATION | DATE | HEURE | LIEU | INFRACTION | MONTANT
                                            </p>
                                            <p className="mt-2">
                                                {t('Dates are read day-first (03/06/2026 is 3 June). Rows already imported are skipped.')}
                                            </p>
                                        </div>

                                        {importSkipped?.length > 0 && (
                                            <div className="rounded-md border border-warning/40 bg-warning/10">
                                                <div className="flex items-center gap-2 border-b border-warning/30 px-3 py-2">
                                                    <AlertTriangle className="h-4 w-4 shrink-0" />
                                                    <strong className="text-sm font-semibold">
                                                        {importSkipped.length} {t('row(s) not imported:')}
                                                    </strong>
                                                </div>
                                                <ul className="max-h-56 space-y-1 overflow-auto px-4 py-2 text-xs">
                                                    {importSkipped.map((reason, i) => (
                                                        <li key={i}>{reason}</li>
                                                    ))}
                                                </ul>
                                            </div>
                                        )}
                                    </div>
                                    <div className="flex justify-end gap-2 border-t px-6 py-4">
                                        <Button type="button" variant="outline" onClick={() => setImportOpen(false)}>
                                            {t('Cancel')}
                                        </Button>
                                        <Button type="submit">
                                            <Upload className="me-2 h-4 w-4" /> {t('Import')}
                                        </Button>
                                    </div>
                                </form>
                            </DialogContent>
                        </Dialog>

                        <Button size="sm" asChild>
                            <Link href={route('traffic-violation.create')}>
                                <Plus className="me-2 h-4 w-4" /> {t('Create Traffic Violation')}
                            </Link>
                        </Button>
                    </div>
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
                            {showActions && <TableHead className="text-end">{t('Action')}</TableHead>}
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
                                    <TableCell className="text-end space-x-1 rtl:space-x-reverse">
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
