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

// Port of resources/views/option/index.blade.php.
// Action buttons are gated by the shared auth.permissions slugs, mirroring the
// Blade @can('edit|delete options') / Gate::check('manage options') guards.
// Prop name `options` matches the controller compact('options').
function OptionIndex({ options = [] }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    async function remove(id) {
        if (await confirmDialog({ title: t('Are you sure?') })) {
            router.delete(route('option.destroy', id));
        }
    }

    const showActions = can('edit options') || can('delete options');

    const [query, setQuery] = useState('');
    const q = query.trim().toLowerCase();
    const filtered = q
        ? options.filter((option) =>
            [option.name]
                .some((v) => String(v ?? '').toLowerCase().includes(q)))
        : options;

    return (
        <div className="space-y-6 p-6">
            <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
                <ListChecks className="h-6 w-6" /> {t('Option')}
            </h1>

            {/* Search sits under the title on the left; actions face it on the
                same row, kept on the right. */}
            <div className="flex items-center justify-between gap-2">
                <div className="relative w-full max-w-xs">
                        <Search className="pointer-events-none absolute start-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder={t('Search options…')}
                            className="ps-8"
                        />
                    </div>
                {can('manage options') && (
                    <Button size="sm" asChild>
                        <Link href={route('option.create')}>
                            <Plus className="me-2 h-4 w-4" /> {t('Create Option')}
                        </Link>
                    </Button>
                )}
            </div>

            <div className="rounded-xl border bg-card overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Option')}</TableHead>
                                {showActions && <TableHead className="text-end">{t('Action')}</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {filtered.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={showActions ? 2 : 1} className="text-center text-muted-foreground py-8">
                                        {options.length === 0 ? t('No options yet') : t('No options match your search')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {filtered.map((option) => (
                                <TableRow key={option.id}>
                                    <TableCell>{option.name}</TableCell>
                                    {showActions && (
                                        <TableCell className="text-end space-x-1 rtl:space-x-reverse">
                                            {can('edit options') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('option.edit', option.id)} aria-label={t('Edit')}>
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('delete options') && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="text-destructive hover:text-destructive"
                                                    onClick={() => remove(option.id)}
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

OptionIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Option' }]}>{page}</AdminLayout>
);
export default OptionIndex;
