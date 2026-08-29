import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Eye, Pencil, Trash2, Plus, ClipboardCheck, Search } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';

// Port of resources/views/inspection/index.blade.php.
// Status / repair-status labels mirror Inspection::$status and
// Inspection::$repairStatus (English values from the model). Action buttons are
// gated by the shared auth.permissions slugs, mirroring the Blade
// @can('show|edit|delete inspection') guards. NOTE: the Blade gates the "Create
// Inspection" button on Gate::check('manage vehicle') (not manage inspection) —
// preserved verbatim to keep behaviour identical. Prop `inspections` matches the
// controller compact('inspections'); each row carries `id_encrypted` (the
// Crypt::encrypt(id) used by the Blade show/edit links).
const STATUS_LABELS = {
    pending: 'Pending',
    completed: 'Completed',
    in_progress: 'In Progress',
    reject: 'Reject',
    conditional_pass: 'Conditional Pass',
    on_hold: 'On Hold',
};

const REPAIR_STATUS_LABELS = {
    needs_repair: 'Needs Repair',
    pending: 'Pending',
    completed: 'Completed',
    in_progress: 'In Progress',
    on_hold: 'On Hold',
};

// Colours from the design-handoff semantic palette so each state is distinct:
// completed/pass=success (green), in_progress=warning (amber), pending/on_hold=
// secondary (grey), reject/needs_repair=destructive (red). Previously completed
// and in_progress were both 'default' (orange) and hard to tell apart.
function statusVariant(status) {
    if (status === 'pending' || status === 'on_hold') return 'secondary';
    if (status === 'completed' || status === 'conditional_pass') return 'success';
    if (status === 'in_progress') return 'warning';
    if (status === 'reject') return 'destructive';
    return 'outline';
}

function repairStatusVariant(status) {
    if (status === 'pending' || status === 'on_hold') return 'secondary';
    if (status === 'completed') return 'success';
    if (status === 'in_progress') return 'warning';
    if (status === 'needs_repair') return 'destructive';
    return 'outline';
}

function InspectionIndex({ inspections = [] }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    async function remove(id) {
        if (await confirmDialog({ title: t('Are you sure?') })) {
            router.delete(route('inspection.destroy', id));
        }
    }

    const showActions = can('show inspection') || can('edit inspection') || can('delete inspection');

    const [query, setQuery] = useState('');
    const q = query.trim().toLowerCase();
    const filtered = q
        ? inspections.filter((inspection) =>
            [inspection.vehicles?.name, inspection.inspector,
                STATUS_LABELS[inspection.status], REPAIR_STATUS_LABELS[inspection.repair_status]]
                .some((v) => String(v ?? '').toLowerCase().includes(q)))
        : inspections;

    return (
        <div className="space-y-6 p-6">
            <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
                <ClipboardCheck className="h-6 w-6" /> {t('Inspection')}
            </h1>

            {/* Search sits under the title on the left; actions face it on the
                same row, kept on the right. */}
            <div className="flex items-center justify-between gap-2">
                <div className="relative w-full max-w-xs">
                        <Search className="pointer-events-none absolute start-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder={t('Search inspections…')}
                            className="ps-8"
                        />
                    </div>
                {can('manage vehicle') && (
                    <Button size="sm" asChild>
                        <Link href={route('inspection.create')}>
                            <Plus className="me-2 h-4 w-4" /> {t('Create Inspection')}
                        </Link>
                    </Button>
                )}
            </div>

            <div className="rounded-xl border bg-card overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Vehicle')}</TableHead>
                                <TableHead>{t('Inspection Date')}</TableHead>
                                <TableHead>{t('Inspection By')}</TableHead>
                                <TableHead>{t('Inspection Status')}</TableHead>
                                <TableHead>{t('Repair Status')}</TableHead>
                                {showActions && <TableHead className="text-end">{t('Action')}</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {filtered.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={showActions ? 6 : 5} className="text-center text-muted-foreground py-8">
                                        {inspections.length === 0 ? t('No inspections yet') : t('No inspections match your search')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {filtered.map((inspection) => (
                                <TableRow key={inspection.id}>
                                    <TableCell>{inspection.vehicles?.name ?? '-'}</TableCell>
                                    <TableCell>{inspection.inspection_date_display ?? inspection.inspection_date ?? '-'}</TableCell>
                                    <TableCell>{inspection.inspector}</TableCell>
                                    <TableCell>
                                        {STATUS_LABELS[inspection.status] && (
                                            <Badge variant={statusVariant(inspection.status)}>
                                                {t(STATUS_LABELS[inspection.status])}
                                            </Badge>
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        {REPAIR_STATUS_LABELS[inspection.repair_status] && (
                                            <Badge variant={repairStatusVariant(inspection.repair_status)}>
                                                {t(REPAIR_STATUS_LABELS[inspection.repair_status])}
                                            </Badge>
                                        )}
                                    </TableCell>
                                    {showActions && (
                                        <TableCell className="text-end space-x-1">
                                            {can('show inspection') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('inspection.show', inspection.id_encrypted)} aria-label={t('Details')}>
                                                        <Eye className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('edit inspection') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('inspection.edit', inspection.id_encrypted)} aria-label={t('Edit')}>
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('delete inspection') && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="text-destructive hover:text-destructive"
                                                    onClick={() => remove(inspection.id)}
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
                </div>
        </div>
    );
}

InspectionIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Inspection' }]}>{page}</AdminLayout>
);
export default InspectionIndex;
