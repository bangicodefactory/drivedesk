import { useEffect, useRef, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Eye, Pencil, Trash2, Plus, FileText, Search } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import Pagination from '@/components/Pagination';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';

// Colours from the design-handoff semantic palette so the lifecycle states are
// distinct: draft=secondary (grey), pending=warning (amber), confirmed=info
// (blue), active=success (green, the live state), completed=secondary (grey,
// archived), cancelled=destructive (red). Previously active (outline) and
// completed (secondary) read as the same muted tone.
const STATUS_VARIANT = {
    draft: 'secondary',
    pending: 'warning',
    confirmed: 'info',
    active: 'success',
    completed: 'secondary',
    cancelled: 'destructive',
};

function RentalAgreementIndex({ agreements = { data: [] }, statuses, filters = {} }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    const statusLabel = (s) => statuses?.find((x) => x.value === s)?.label ?? s;

    async function remove(id) {
        if (await confirmDialog({ title: t('Delete this rental agreement?') })) {
            router.delete(route('rental-agreement.destroy', id));
        }
    }

    // Server-side search (the list is paginated, so filter on the server to
    // cover all pages). Debounced reload.
    const [search, setSearch] = useState(filters.search ?? '');
    const isFirst = useRef(true);
    useEffect(() => {
        if (isFirst.current) {
            isFirst.current = false;
            return;
        }
        const timer = setTimeout(() => {
            router.get(
                route('rental-agreement.index'),
                search ? { search } : {},
                { preserveState: true, preserveScroll: true, replace: true },
            );
        }, 300);
        return () => clearTimeout(timer);
    }, [search]);

    return (
        <div className="space-y-6 p-6">
            <h1 className="text-3xl font-bold tracking-tight">{t('Rental Agreements')}</h1>

            {/* Search sits under the title on the left; actions face it on the
                same row, kept on the right. */}
            <div className="flex items-center justify-between gap-2">
                <div className="relative w-full max-w-xs">
                        <Search className="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder={t('Search agreements…')}
                            className="pl-8"
                        />
                    </div>
                {can('manage rental agreement') && (
                    <Button size="sm" asChild>
                        <Link href={route('rental-agreement.create')}>
                            <Plus className="mr-2 h-4 w-4" /> {t('Create Agreement')}
                        </Link>
                    </Button>
                )}
            </div>

            <div className="rounded-xl border bg-card overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('ID')}</TableHead>
                                <TableHead>{t('Driver')}</TableHead>
                                <TableHead>{t('Vehicle')}</TableHead>
                                <TableHead>{t('Date')}</TableHead>
                                <TableHead>{t('Start')}</TableHead>
                                <TableHead>{t('End')}</TableHead>
                                <TableHead>{t('Duration')}</TableHead>
                                <TableHead>{t('Status')}</TableHead>
                                {(can('edit rental agreement') || can('delete rental agreement') || can('show rental agreement')) && (
                                    <TableHead className="text-right">{t('Action')}</TableHead>
                                )}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {agreements.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={9} className="text-center text-muted-foreground py-8">
                                        {agreements.total === 0 ? t('No rental agreements yet') : t('No rental agreements match your search')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {agreements.data.map((a) => (
                                <TableRow key={a.id}>
                                    <TableCell className="font-mono text-sm">{a.agreement_id}</TableCell>
                                    <TableCell>{a.driver_name}</TableCell>
                                    <TableCell>{a.vehicle_label}</TableCell>
                                    <TableCell>{a.date}</TableCell>
                                    <TableCell>{a.rental_start_date}</TableCell>
                                    <TableCell>{a.rental_end_date}</TableCell>
                                    <TableCell>{a.rental_duration} {t('Days')}</TableCell>
                                    <TableCell>
                                        <Badge variant={STATUS_VARIANT[a.status] ?? 'secondary'}>
                                            {t(statusLabel(a.status))}
                                        </Badge>
                                    </TableCell>
                                    {(can('edit rental agreement') || can('delete rental agreement') || can('show rental agreement')) && (
                                        <TableCell className="text-right space-x-1 whitespace-nowrap">
                                            {can('show rental agreement') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('rental-agreement.show', a.encrypted_id)} aria-label={t('View')}>
                                                        <Eye className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('edit rental agreement') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('rental-agreement.edit', a.id)} aria-label={t('Edit')}>
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('delete rental agreement') && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => remove(a.id)}
                                                    className="text-destructive hover:text-destructive"
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
                    <div className="px-4 pb-3">
                        <Pagination paginator={agreements} />
                    </div>
                </div>
        </div>
    );
}

RentalAgreementIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Rental Agreements' }]}>{page}</AdminLayout>
);
export default RentalAgreementIndex;
