import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Pencil, Trash2, Plus, Car, Search } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';

// Port of resources/views/vehicle_type/index.blade.php.
// Action buttons are gated by the shared auth.permissions slugs, mirroring the
// Blade Gate::check('manage vehicle type') / @can('edit|delete vehicle type')
// guards. Prop name `types` matches the controller compact('types').
function VehicleTypeIndex({ types = [] }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    async function remove(id) {
        if (await confirmDialog({ title: 'Are you sure?' })) {
            router.delete(route('vehicle-type.destroy', id));
        }
    }

    const showActions = can('edit vehicle type') || can('delete vehicle type');

    const [query, setQuery] = useState('');
    const q = query.trim().toLowerCase();
    const filtered = q
        ? types.filter((type) =>
            [type.type, type.notes]
                .some((v) => String(v ?? '').toLowerCase().includes(q)))
        : types;

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
                    <Car className="h-6 w-6" /> {t('Vehicle Type')}
                </h1>
                {can('manage vehicle type') && (
                    <Button size="sm" asChild>
                        <Link href={route('vehicle-type.create')}>
                            <Plus className="mr-2 h-4 w-4" /> {t('Create Type')}
                        </Link>
                    </Button>
                )}
            </div>

            <div className="flex items-center justify-end">
                <div className="relative w-full max-w-xs">
                        <Search className="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder={t('Search types…')}
                            className="pl-8"
                        />
                    </div>
            </div>

            <div className="rounded-xl border bg-card overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Type')}</TableHead>
                                <TableHead>{t('Notes')}</TableHead>
                                {showActions && <TableHead className="text-right">{t('Action')}</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {filtered.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={3} className="text-center text-muted-foreground py-8">
                                        {types.length === 0 ? t('No vehicle types yet') : t('No vehicle types match your search')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {filtered.map((type) => (
                                <TableRow key={type.id}>
                                    <TableCell>{type.type}</TableCell>
                                    <TableCell>{type.notes}</TableCell>
                                    {showActions && (
                                        <TableCell className="text-right space-x-1">
                                            {can('edit vehicle type') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('vehicle-type.edit', type.id)} aria-label={t('Edit')}>
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('delete vehicle type') && (
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

VehicleTypeIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Vehicle Type' }]}>{page}</AdminLayout>
);
export default VehicleTypeIndex;
