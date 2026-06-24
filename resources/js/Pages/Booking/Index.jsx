import { useEffect, useRef, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Eye, Pencil, Trash2, Plus, Upload, Download, Truck, Search, CheckCircle2 } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import Pagination from '@/components/Pagination';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';

// Colours mapped to the design-handoff semantic palette (info/warning/success/
// danger) so each state is distinct: scheduled=blue, active=amber, done=green,
// cancelled=red; paid=green, partial=amber, unpaid=red.
const STATUS_VARIANT = {
    yet_to_start: 'info',
    on_going: 'warning',
    completed: 'success',
    cancelled: 'destructive',
};

const PAYMENT_VARIANT = {
    paye: 'success',
    impaye: 'destructive',
    partiellement_paye: 'warning',
};

function BookingIndex({ bookings, statuses, paymentStatuses, filters = {} }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    const { auth, flash } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    // Rows the server skipped on a partial Excel import (parity with the old
    // Blade modal). Flashed by BookingController@importExcel, shared through
    // HandleInertiaRequests; present only on the render right after an import.
    const importSkipped = flash?.import_skipped ?? null;

    // Server-side search (paginated list — filter on the server to cover all pages).
    const [search, setSearch] = useState(filters.search ?? '');
    const isFirst = useRef(true);
    useEffect(() => {
        if (isFirst.current) {
            isFirst.current = false;
            return;
        }
        const timer = setTimeout(() => {
            router.get(
                route('booking.index'),
                search ? { search } : {},
                { preserveState: true, preserveScroll: true, replace: true },
            );
        }, 300);
        return () => clearTimeout(timer);
    }, [search]);

    const [selected, setSelected] = useState([]);
    const [importOpen, setImportOpen] = useState(false);
    const [importFile, setImportFile] = useState(null);

    function toggleAll(e) {
        setSelected(e.target.checked ? bookings.data.map((b) => b.id) : []);
    }

    function toggleOne(id) {
        setSelected((prev) =>
            prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id],
        );
    }

    async function remove(id) {
        if (await confirmDialog({ title: t('Delete this booking?') })) {
            router.delete(route('booking.destroy', id));
        }
    }

    async function bulkDelete() {
        if (!selected.length) return;
        if (!await confirmDialog({ title: `${t('Delete')} ${selected.length} ${t('selected booking(s)?')}` })) return;
        router.post(route('booking.bulk-destroy'), { ids: selected });
    }

    function bulkMarkPaid() {
        if (!selected.length) return;
        router.post(route('booking.bulk-mark-paid'), { ids: selected }, {
            onSuccess: () => setSelected([]),
        });
    }

    function submitImport(e) {
        e.preventDefault();
        if (!importFile) return;
        router.post(route('booking.import'), { file: importFile }, {
            // Close only on a clean import. When the server skipped rows, the
            // redirect carries flash.import_skipped and the effect below reopens
            // the dialog so the user sees exactly which rows failed and why.
            onSuccess: (page) => {
                setImportFile(null);
                if (!page.props.flash?.import_skipped?.length) setImportOpen(false);
            },
        });
    }

    // Reopen the import dialog whenever the server reports skipped rows, so the
    // per-row error report is shown (matches the old Blade reopen_import_modal).
    useEffect(() => {
        if (importSkipped?.length) setImportOpen(true);
    }, [importSkipped]);

    const statusLabel = (s) => statuses?.find((x) => x.value === s)?.label ?? s;
    const payLabel = (s) => paymentStatuses?.find((x) => x.value === s)?.label ?? s;

    return (
        <div className="space-y-6 p-6">
            <h1 className="text-3xl font-bold tracking-tight">{t('Bookings')}</h1>

            {/* Search sits under the title on the left; actions face it on the
                same row, kept on the right. */}
            <div className="flex items-center justify-between gap-2">
                <div className="relative w-full max-w-xs min-w-0">
                        <Search className="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder={t('Search bookings…')}
                            className="pl-8"
                        />
                    </div>
                {/* All actions stay on the search bar's row (no wrap); the search
                    shrinks to make room. */}
                <div className="flex items-center gap-2 shrink-0">
                    {selected.length > 0 && can('edit booking') && (
                        <Button variant="success" size="sm" onClick={bulkMarkPaid}>
                            <CheckCircle2 className="mr-2 h-4 w-4" />
                            {t('Mark as Paid')} ({selected.length})
                        </Button>
                    )}
                    {selected.length > 0 && can('delete booking') && (
                        <Button variant="destructive" size="sm" onClick={bulkDelete}>
                            <Trash2 className="mr-2 h-4 w-4" />
                            {t('Delete Selected')} ({selected.length})
                        </Button>
                    )}
                    {can('create booking') && (
                        <>
                            <Button variant="outline" size="sm" asChild>
                                <a href={route('booking.template')} target="_blank">
                                    <Download className="mr-2 h-4 w-4" /> {t('Template')}
                                </a>
                            </Button>
                            <Dialog open={importOpen} onOpenChange={setImportOpen}>
                                <DialogTrigger asChild>
                                    <Button variant="outline" size="sm">
                                        <Upload className="mr-2 h-4 w-4" /> {t('Import Excel')}
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogHeader>
                                        <DialogTitle>{t('Import Bookings from Excel')}</DialogTitle>
                                    </DialogHeader>
                                    <form onSubmit={submitImport} className="space-y-4">
                                        {importSkipped?.length > 0 && (
                                            <div className="rounded border border-amber-300 bg-amber-50 p-3 text-amber-900">
                                                <strong className="text-sm">
                                                    {importSkipped.length} {t('ligne(s) non importée(s):')}
                                                </strong>
                                                <div className="mt-2 max-h-60 overflow-auto">
                                                    <table className="w-full border-collapse text-xs">
                                                        <thead>
                                                            <tr className="text-left">
                                                                <th className="border px-1 py-0.5">#{t('Ligne')}</th>
                                                                <th className="border px-1 py-0.5">NOM &amp; PRENOM</th>
                                                                <th className="border px-1 py-0.5">IMMATRICULATION</th>
                                                                <th className="border px-1 py-0.5">DATE DEBUT</th>
                                                                <th className="border px-1 py-0.5">DATE RETOUR</th>
                                                                <th className="border px-1 py-0.5">{t('Erreur(s)')}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            {importSkipped.map((s, i) => (
                                                                <tr key={i}>
                                                                    <td className="border px-1 py-0.5">{s.row}</td>
                                                                    <td className="border px-1 py-0.5">{s.nom}</td>
                                                                    <td className="border px-1 py-0.5">{s.plaque}</td>
                                                                    <td className="border px-1 py-0.5">{s.debut}</td>
                                                                    <td className="border px-1 py-0.5">{s.retour}</td>
                                                                    <td className="border px-1 py-0.5 text-red-600">
                                                                        {(s.errors ?? []).join(' | ')}
                                                                    </td>
                                                                </tr>
                                                            ))}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        )}
                                        <p className="text-sm text-muted-foreground">
                                            {t('Upload an .xlsx or .xls file. Download the')}{' '}
                                            <a href={route('booking.template')} target="_blank" className="underline">
                                                {t('template')}
                                            </a>{' '}
                                            {t('to see the required format.')}
                                        </p>
                                        <div className="space-y-1">
                                            <Label htmlFor="importFile">{t('Excel File')}</Label>
                                            <Input
                                                id="importFile"
                                                type="file"
                                                accept=".xlsx,.xls,.csv"
                                                required
                                                onChange={(e) => setImportFile(e.target.files?.[0] ?? null)}
                                            />
                                        </div>
                                        <div className="text-xs text-muted-foreground bg-muted p-3 rounded">
                                            <strong>{t('Format attendu (10 colonnes):')}</strong><br />
                                            NOM &amp; PRENOM | DATE DEBUT | HEURE | LA MARQUE | IMMATRICULATION | DATE RETOUR | HEURE RETOUR | PERIODE | PRIX | METHOD
                                        </div>
                                        <div className="flex justify-end gap-2">
                                            <Button type="button" variant="outline" onClick={() => setImportOpen(false)}>
                                                {t('Cancel')}
                                            </Button>
                                            <Button type="submit">
                                                <Upload className="mr-2 h-4 w-4" /> {t('Import')}
                                            </Button>
                                        </div>
                                    </form>
                                </DialogContent>
                            </Dialog>
                        </>
                    )}
                    {can('manage vehicle') && (
                        <Button size="sm" asChild>
                            <Link href={route('booking.create')}>
                                <Plus className="mr-2 h-4 w-4" /> {t('Create Booking')}
                            </Link>
                        </Button>
                    )}
                </div>
            </div>

            <div className="rounded-xl border bg-card overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                {can('delete booking') && (
                                    <TableHead style={{ width: 32 }}>
                                        <Checkbox
                                            aria-label={t('Select all bookings')}
                                            onCheckedChange={(v) => toggleAll({ target: { checked: v === true } })}
                                            checked={selected.length === bookings.data.length && bookings.data.length > 0}
                                        />
                                    </TableHead>
                                )}
                                <TableHead>{t('ID')}</TableHead>
                                <TableHead>{t('Driver')}</TableHead>
                                <TableHead>{t('Vehicle')}</TableHead>
                                <TableHead>{t('Duration')}</TableHead>
                                <TableHead>{t('Status')}</TableHead>
                                <TableHead>{t('Payment')}</TableHead>
                                {(can('edit booking') || can('delete booking') || can('show booking')) && (
                                    <TableHead className="text-right">{t('Action')}</TableHead>
                                )}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {bookings.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={8} className="text-center text-muted-foreground py-8">
                                        {search ? t('No bookings match your search') : t('No bookings yet')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {bookings.data.map((b) => (
                                <TableRow key={b.id}>
                                    {can('delete booking') && (
                                        <TableCell>
                                            <Checkbox
                                                aria-label={`${t('Select booking')} ${b.booking_id}`}
                                                checked={selected.includes(b.id)}
                                                onCheckedChange={() => toggleOne(b.id)}
                                            />
                                        </TableCell>
                                    )}
                                    <TableCell className="font-mono text-sm">{b.booking_id}</TableCell>
                                    <TableCell>{b.driver_name}</TableCell>
                                    <TableCell>{b.vehicle_label}</TableCell>
                                    <TableCell className="text-sm">
                                        <div>{b.start_date} {b.start_time}</div>
                                        <div className="text-muted-foreground">{b.end_date} {b.end_time}</div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant={STATUS_VARIANT[b.status] ?? 'secondary'}>
                                            {t(statusLabel(b.status))}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant={PAYMENT_VARIANT[b.payment_status] ?? 'secondary'} className="capitalize">
                                            {t(payLabel(b.payment_status))}
                                        </Badge>
                                    </TableCell>
                                    {(can('edit booking') || can('delete booking') || can('show booking')) && (
                                        <TableCell className="text-right space-x-1">
                                            {can('show booking') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('booking.show', b.encrypted_id)} aria-label={t('View')}>
                                                        <Eye className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('edit booking') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('booking.edit', b.encrypted_id)} aria-label={t('Edit')}>
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('delete booking') && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => remove(b.id)}
                                                    aria-label={t('Delete')}
                                                    className="text-destructive hover:text-destructive"
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
                    <Pagination paginator={bookings} />
                </div>
        </div>
    );
}

BookingIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Bookings' }]}>{page}</AdminLayout>
);
export default BookingIndex;
