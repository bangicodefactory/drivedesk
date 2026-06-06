import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Pencil, Trash2, Plus, MapPin, Search } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';

// Port of resources/views/place/index.blade.php.
// Action buttons are gated by the shared auth.permissions slugs, mirroring the
// Blade @can('edit place') / @can('delete place') / Gate::check('manage place')
// guards. Each place row carries a `price_formatted` prop produced by
// priceFormat() on the server (same as the Blade priceFormat($place->price)).
function PlaceIndex({ places = [] }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    async function remove(id) {
        if (await confirmDialog({ title: 'Are you sure?' })) {
            router.delete(route('place.destroy', id));
        }
    }

    const showActions = can('edit place') || can('delete place');

    const [query, setQuery] = useState('');
    const q = query.trim().toLowerCase();
    const filtered = q
        ? places.filter((p) =>
            [p.name, p.city, p.island, p.price_formatted ?? p.price, p.depo_name, p.depo_address]
                .some((v) => String(v ?? '').toLowerCase().includes(q)))
        : places;

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
                    <MapPin className="h-6 w-6" /> {t('Place')}
                </h1>
                {can('manage place') && (
                    <Button size="sm" asChild>
                        <Link href={route('place.create')}>
                            <Plus className="mr-2 h-4 w-4" /> {t('Create Place')}
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
                            placeholder={t('Search places…')}
                            className="pl-8"
                        />
                    </div>
            </div>

            <div className="rounded-xl border bg-card overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Name')}</TableHead>
                                <TableHead>{t('City')}</TableHead>
                                <TableHead>{t('Island')}</TableHead>
                                <TableHead>{t('Price')}</TableHead>
                                <TableHead>{t('Depo name')}</TableHead>
                                <TableHead>{t('Depo address')}</TableHead>
                                {showActions && <TableHead className="text-right">{t('Action')}</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {filtered.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={7} className="text-center text-muted-foreground py-8">
                                        {places.length === 0 ? t('No places yet') : t('No places match your search')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {filtered.map((p) => (
                                <TableRow key={p.id}>
                                    <TableCell>{p.name}</TableCell>
                                    <TableCell>{p.city}</TableCell>
                                    <TableCell>{p.island}</TableCell>
                                    <TableCell>{p.price_formatted ?? p.price}</TableCell>
                                    <TableCell>{p.depo_name ? p.depo_name : '-'}</TableCell>
                                    <TableCell>{p.depo_address ? p.depo_address : '-'}</TableCell>
                                    {showActions && (
                                        <TableCell className="text-right space-x-1">
                                            {can('edit place') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('place.edit', p.id)} aria-label={t('Edit')}>
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('delete place') && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="text-destructive hover:text-destructive"
                                                    onClick={() => remove(p.id)}
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

PlaceIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Place' }]}>{page}</AdminLayout>
);
export default PlaceIndex;
