import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Eye, Pencil, Trash2, Plus, ClipboardCheck, Search } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';

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

function statusVariant(status) {
    if (status === 'pending' || status === 'on_hold') return 'secondary';
    if (status === 'completed' || status === 'conditional_pass') return 'default';
    if (status === 'in_progress') return 'default';
    if (status === 'reject') return 'destructive';
    return 'outline';
}

function repairStatusVariant(status) {
    if (status === 'pending' || status === 'on_hold') return 'secondary';
    if (status === 'completed') return 'default';
    if (status === 'in_progress') return 'default';
    if (status === 'needs_repair') return 'destructive';
    return 'outline';
}

function InspectionIndex({ inspections = [] }) {
    const t = useTranslation();
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    function remove(id) {
        if (window.confirm(t('Are you sure?'))) {
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
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-semibold flex items-center gap-2">
                    <ClipboardCheck className="h-6 w-6" /> {t('Inspection')}
                </h1>
                {can('manage vehicle') && (
                    <Button size="sm" asChild>
                        <Link href={route('inspection.create')}>
                            <Plus className="mr-2 h-4 w-4" /> {t('Create Inspection')}
                        </Link>
                    </Button>
                )}
            </div>

            <Card>
                <CardHeader className="flex flex-row items-center justify-between gap-4 space-y-0">
                    <CardTitle>{t('All Inspections')}</CardTitle>
                    <div className="relative w-full max-w-xs">
                        <Search className="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder={t('Search inspections…')}
                            className="pl-8"
                        />
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Vehicle')}</TableHead>
                                <TableHead>{t('Inspection Date')}</TableHead>
                                <TableHead>{t('Inspection By')}</TableHead>
                                <TableHead>{t('Inspection Status')}</TableHead>
                                <TableHead>{t('Repair Status')}</TableHead>
                                {showActions && <TableHead className="text-right">{t('Action')}</TableHead>}
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
                                        <TableCell className="text-right space-x-1">
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
                </CardContent>
            </Card>
        </div>
    );
}

InspectionIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Inspection' }]}>{page}</AdminLayout>
);
export default InspectionIndex;
