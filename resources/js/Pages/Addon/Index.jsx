import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Pencil, Trash2, Plus, Package, Search } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';

// Port of resources/views/addon/index.blade.php.
// Action buttons are gated by the shared auth.permissions slugs, mirroring the
// Blade @can('edit|delete addon') / Gate::check('manage addon') guards.
// Prop name `addons` matches the controller compact('addons'). Each addon row
// shows the formatted price (price_formatted is appended by the controller to
// reproduce the Blade priceFormat($addon->price) helper output).
function AddonIndex({ addons = [] }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    async function remove(id) {
        if (await confirmDialog({ title: t('Are you sure?') })) {
            router.delete(route('addon.destroy', id));
        }
    }

    const showActions = can('edit addon') || can('delete addon');

    const [query, setQuery] = useState('');
    const q = query.trim().toLowerCase();
    const filtered = q
        ? addons.filter((addon) =>
            [addon.name, addon.price_formatted ?? addon.price, addon.billing_type]
                .some((v) => String(v ?? '').toLowerCase().includes(q)))
        : addons;

    return (
        <div className="space-y-6 p-6">
            <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
                <Package className="h-6 w-6" /> {t('Addon')}
            </h1>

            {/* Search sits under the title on the left; actions face it on the
                same row, kept on the right. */}
            <div className="flex items-center justify-between gap-2">
                <div className="relative w-full max-w-xs">
                        <Search className="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder={t('Search addons…')}
                            className="pl-8"
                        />
                    </div>
                {can('manage addon') && (
                    <Button size="sm" asChild>
                        <Link href={route('addon.create')}>
                            <Plus className="mr-2 h-4 w-4" /> {t('Create Addon')}
                        </Link>
                    </Button>
                )}
            </div>

            <div className="rounded-xl border bg-card overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Addon')}</TableHead>
                                <TableHead>{t('Price')}</TableHead>
                                <TableHead>{t('Billing Type')}</TableHead>
                                {showActions && <TableHead className="text-right">{t('Action')}</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {filtered.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={showActions ? 4 : 3} className="text-center text-muted-foreground py-8">
                                        {addons.length === 0 ? t('No addons yet') : t('No addons match your search')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {filtered.map((addon) => (
                                <TableRow key={addon.id}>
                                    <TableCell>{addon.name}</TableCell>
                                    <TableCell>{addon.price_formatted ?? addon.price}</TableCell>
                                    <TableCell>{addon.billing_type}</TableCell>
                                    {showActions && (
                                        <TableCell className="text-right space-x-1">
                                            {can('edit addon') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('addon.edit', addon.id)} aria-label={t('Edit')}>
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('delete addon') && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="text-destructive hover:text-destructive"
                                                    onClick={() => remove(addon.id)}
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

AddonIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Addon' }]}>{page}</AdminLayout>
);
export default AddonIndex;
