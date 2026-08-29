import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Pencil, Trash2, Plus, ListChecks, Search } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';

// Port of resources/views/inspection_type/index.blade.php.
// Action buttons are gated by the shared auth.permissions slugs, mirroring the
// Blade @can('edit inspection type') / @can('delete inspection type') /
// Gate::check('manage inspection type') guards. Prop name `types` matches the
// controller compact('types').
function InspectionTypeIndex({ types = [] }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    async function remove(id) {
        if (await confirmDialog({ title: t('Are you sure?') })) {
            router.delete(route('inspection-type.destroy', id));
        }
    }

    const showActions = can('edit inspection type') || can('delete inspection type');

    const [query, setQuery] = useState('');
    const q = query.trim().toLowerCase();
    const filtered = q
        ? types.filter((item) =>
            [item.type].some((v) => String(v ?? '').toLowerCase().includes(q)))
        : types;

    return (
        <div className="space-y-6 p-6">
            <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
                <ListChecks className="h-6 w-6" /> {t('Inspection Type')}
            </h1>

            {/* Search sits under the title on the left; actions face it on the
                same row, kept on the right. */}
            <div className="flex items-center justify-between gap-2">
                <div className="relative w-full max-w-xs">
                        <Search className="pointer-events-none absolute start-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder={t('Search types…')}
                            className="ps-8"
                        />
                    </div>
                {can('manage inspection type') && (
                    <Button size="sm" asChild>
                        <Link href={route('inspection-type.create')}>
                            <Plus className="me-2 h-4 w-4" /> {t('Create Type')}
                        </Link>
                    </Button>
                )}
            </div>

            <div className="rounded-xl border bg-card overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Type')}</TableHead>
                                {showActions && <TableHead className="text-end">{t('Action')}</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {filtered.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={showActions ? 2 : 1} className="text-center text-muted-foreground py-8">
                                        {types.length === 0 ? t('No inspection types yet') : t('No types match your search')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {filtered.map((type) => (
                                <TableRow key={type.id}>
                                    <TableCell>{type.type}</TableCell>
                                    {showActions && (
                                        <TableCell className="text-end space-x-1">
                                            {can('edit inspection type') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('inspection-type.edit', type.id)} aria-label={t('Edit')}>
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('delete inspection type') && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="text-destructive hover:text-destructive"
                                                    onClick={() => remove(type.id)}
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

InspectionTypeIndex.layout = (page) => {
    return (
        <AdminLayout breadcrumbs={[{ label: 'Inspection Type' }]}>{page}</AdminLayout>
    );
};
export default InspectionTypeIndex;
