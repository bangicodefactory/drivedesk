import { useEffect, useRef, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Eye, Pencil, Trash2, Plus, Car, Search } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import Pagination from '@/components/Pagination';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';

// Port of resources/views/vehicle/index.blade.php.
// Action buttons are gated by the shared auth.permissions slugs, mirroring the
// Blade @can('show|edit|delete vehicle') / Gate::check('manage vehicle') guards.
function VehicleIndex({ vehicles = { data: [] }, filters = {} }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    // Server-side search (the list is paginated, so filtering must happen on the
    // server to cover vehicles beyond the current page). Debounced reload.
    const [search, setSearch] = useState(filters.search ?? '');
    const isFirst = useRef(true);
    useEffect(() => {
        if (isFirst.current) {
            isFirst.current = false;
            return;
        }
        const timer = setTimeout(() => {
            router.get(
                route('vehicle.index'),
                search ? { search } : {},
                { preserveState: true, preserveScroll: true, replace: true },
            );
        }, 300);
        return () => clearTimeout(timer);
    }, [search]);

    async function remove(id) {
        if (await confirmDialog({ title: t('Are you sure?') })) {
            router.delete(route('vehicle.destroy', id));
        }
    }

    const showActions = can('show vehicle') || can('edit vehicle') || can('delete vehicle');

    return (
        <div className="space-y-6 p-6">
            <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
                <Car className="h-6 w-6" /> {t('Vehicle')}
            </h1>

            {/* Search sits under the title on the left; actions face it on the
                same row, kept on the right. */}
            <div className="flex items-center justify-between gap-2">
                <div className="relative w-full max-w-xs">
                        <Search className="pointer-events-none absolute start-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder={t('Search vehicles…')}
                            className="ps-8"
                        />
                    </div>
                {can('manage vehicle') && (
                    <Button size="sm" asChild>
                        <Link href={route('vehicle.create')}>
                            <Plus className="me-2 h-4 w-4" /> {t('Create Vehicle')}
                        </Link>
                    </Button>
                )}
            </div>

            <div className="rounded-xl border bg-card overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('ID')}</TableHead>
                                <TableHead>{t('Name')}</TableHead>
                                <TableHead>{t('Type')}</TableHead>
                                <TableHead>{t('Model')}</TableHead>
                                <TableHead>{t('License Plate')}</TableHead>
                                <TableHead>{t('Registration Expiration Date')}</TableHead>
                                <TableHead>{t('Engine Type')}</TableHead>
                                {showActions && <TableHead className="text-end">{t('Action')}</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {vehicles.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={8} className="text-center text-muted-foreground py-8">
                                        {search ? t('No vehicles match your search') : t('No vehicles yet')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {vehicles.data.map((v) => (
                                <TableRow key={v.id}>
                                    <TableCell className="font-mono text-sm">{v.vehicle_id_display ?? v.vehicle_id}</TableCell>
                                    <TableCell>{v.name}</TableCell>
                                    <TableCell>{v.type_label ?? '-'}</TableCell>
                                    <TableCell>{v.model}</TableCell>
                                    <TableCell>{v.license_plate}</TableCell>
                                    <TableCell>{v.registration_expiry_date_display ?? '-'}</TableCell>
                                    <TableCell>{v.engine_type}</TableCell>
                                    {showActions && (
                                        <TableCell className="text-end space-x-1 rtl:space-x-reverse">
                                            {can('show vehicle') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('vehicle.show', v.id)} aria-label={t('Details')}>
                                                        <Eye className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('edit vehicle') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('vehicle.edit', v.id)} aria-label={t('Edit')}>
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('delete vehicle') && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="text-destructive hover:text-destructive"
                                                    onClick={() => remove(v.id)}
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
                    <Pagination paginator={vehicles} />
                </div>
        </div>
    );
}

VehicleIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Vehicles' }]}>{page}</AdminLayout>
);
export default VehicleIndex;
