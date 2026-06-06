import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Pencil, Trash2, Plus, Tag, Search } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';

function ExpenseTypeIndex({ types = [] }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    async function remove(id) {
        if (await confirmDialog({ title: 'Are you sure?' })) {
            router.delete(route('expense-type.destroy', id));
        }
    }

    const showActions = can('edit expense type') || can('delete expense type');

    const [query, setQuery] = useState('');
    const q = query.trim().toLowerCase();
    const filtered = q
        ? types.filter((item) =>
            [item.title].some((v) => String(v ?? '').toLowerCase().includes(q)))
        : types;

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
                    <Tag className="h-6 w-6" /> {t('Expense Type')}
                </h1>
                {can('manage expense type') && (
                    <Button size="sm" asChild>
                        <Link href={route('expense-type.create')}>
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
                                <TableHead>{t('Title')}</TableHead>
                                {showActions && <TableHead className="text-right">{t('Action')}</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {filtered.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={showActions ? 2 : 1} className="text-center text-muted-foreground py-8">
                                        {types.length === 0 ? t('No expense types yet') : t('No types match your search')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {filtered.map((type) => (
                                <TableRow key={type.id}>
                                    <TableCell>{type.title}</TableCell>
                                    {showActions && (
                                        <TableCell className="text-right space-x-1">
                                            {can('edit expense type') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('expense-type.edit', type.id)} aria-label={t('Edit')}>
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('delete expense type') && (
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

ExpenseTypeIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Expense Type' }]}>{page}</AdminLayout>
);
export default ExpenseTypeIndex;
