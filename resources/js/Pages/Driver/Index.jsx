import { useEffect, useRef, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Eye, Pencil, Trash2, Plus, Users, Search } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import Pagination from '@/components/Pagination';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';

// Port of resources/views/driver/index.blade.php.
// Action buttons are gated by the shared auth.permissions slugs, mirroring the
// Blade @can('show|edit|delete driver') / Gate::check('manage|create driver')
// guards. Prop name `drivers` matches the controller compact('drivers').
function DriverIndex({ drivers = { data: [] }, filters = {} }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    async function remove(id) {
        if (await confirmDialog({ title: t('Are you sure?') })) {
            router.delete(route('driver.destroy', id));
        }
    }

    const showActions = can('show driver') || can('edit driver') || can('delete driver');

    // Server-side search (the list is paginated, so filter on the server to cover
    // all pages). Debounced reload.
    const [search, setSearch] = useState(filters.search ?? '');
    const isFirst = useRef(true);
    useEffect(() => {
        if (isFirst.current) {
            isFirst.current = false;
            return;
        }
        const timer = setTimeout(() => {
            router.get(
                route('driver.index'),
                search ? { search } : {},
                { preserveState: true, preserveScroll: true, replace: true },
            );
        }, 300);
        return () => clearTimeout(timer);
    }, [search]);

    return (
        <div className="space-y-6 p-6">
            <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
                <Users className="h-6 w-6" /> {t('Driver')}
            </h1>

            {/* Search sits under the title on the left; Create Driver faces it on
                the same row, kept on the right. */}
            <div className="flex items-center justify-between gap-2">
                <div className="relative w-full max-w-xs">
                    <Search className="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder={t('Search drivers…')}
                        className="pl-8"
                    />
                </div>
                {can('manage driver') && (
                    <Button size="sm" asChild>
                        <Link href={route('driver.create')}>
                            <Plus className="mr-2 h-4 w-4" /> {t('Create Driver')}
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
                                <TableHead>{t('Email')}</TableHead>
                                <TableHead>{t('Phone Number')}</TableHead>
                                <TableHead>{t('License Number')}</TableHead>
                                <TableHead>{t('Issue Date')}</TableHead>
                                <TableHead>{t('Expiration Date')}</TableHead>
                                {showActions && <TableHead className="text-right">{t('Action')}</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {drivers.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={8} className="text-center text-muted-foreground py-8">
                                        {drivers.total === 0 ? t('No drivers yet') : t('No drivers match your search')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {drivers.data.map((d) => (
                                <TableRow key={d.id}>
                                    <TableCell className="font-mono text-sm">{d.driver_id_display ?? '-'}</TableCell>
                                    <TableCell>{d.name}</TableCell>
                                    <TableCell>{d.email || '-'}</TableCell>
                                    <TableCell>{d.phone_number || '-'}</TableCell>
                                    <TableCell>{d.license_number || '-'}</TableCell>
                                    <TableCell>{d.issue_date_display ?? '-'}</TableCell>
                                    <TableCell>{d.expiration_date_display ?? '-'}</TableCell>
                                    {showActions && (
                                        <TableCell className="text-right space-x-1 whitespace-nowrap">
                                            {can('show driver') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('driver.show', d.id)} aria-label={t('Details')}>
                                                        <Eye className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('edit driver') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('driver.edit', d.id)} aria-label={t('Edit')}>
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('delete driver') && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="text-destructive hover:text-destructive"
                                                    onClick={() => remove(d.id)}
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
                        <Pagination paginator={drivers} />
                    </div>
            </div>
        </div>
    );
}

DriverIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Driver' }]}>{page}</AdminLayout>
);
export default DriverIndex;
