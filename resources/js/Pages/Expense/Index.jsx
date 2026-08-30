import { useEffect, useRef, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Pencil, Trash2, Plus, Receipt, Search } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import Pagination from '@/components/Pagination';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';

function ExpenseIndex({ expenses = { data: [] }, filters = {} }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    // Server-side search (paginated list — filter on the server to cover all pages).
    const [search, setSearch] = useState(filters.search ?? '');
    const isFirst = useRef(true);
    useEffect(() => {
        if (isFirst.current) {
            isFirst.current = false;
            return;
        }
        const t = setTimeout(() => {
            router.get(
                route('expense.index'),
                search ? { search } : {},
                { preserveState: true, preserveScroll: true, replace: true },
            );
        }, 300);
        return () => clearTimeout(t);
    }, [search]);

    async function remove(id) {
        if (await confirmDialog({ title: t('Are you sure?') })) {
            router.delete(route('expense.destroy', id));
        }
    }

    const showActions = can('edit expense') || can('delete expense');

    return (
        <div className="space-y-6 p-6">
            <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
                <Receipt className="h-6 w-6" /> {t('Expenses')}
            </h1>

            {/* Search sits under the title on the left; actions face it on the
                same row, kept on the right. */}
            <div className="flex items-center justify-between gap-2">
                <div className="relative w-full max-w-xs">
                        <Search className="pointer-events-none absolute start-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder={t('Search expenses…')}
                            className="ps-8"
                        />
                    </div>
                {can('create expense') && (
                    <Button size="sm" asChild>
                        <Link href={route('expense.create')}>
                            <Plus className="me-2 h-4 w-4" /> {t('Create Expense')}
                        </Link>
                    </Button>
                )}
            </div>

            <div className="rounded-xl border bg-card overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Title')}</TableHead>
                                <TableHead>{t('Type')}</TableHead>
                                <TableHead>{t('Vehicle')}</TableHead>
                                <TableHead>{t('Date')}</TableHead>
                                <TableHead>{t('Amount')}</TableHead>
                                <TableHead>{t('Receipt')}</TableHead>
                                {showActions && <TableHead className="text-end">{t('Action')}</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {expenses.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={showActions ? 7 : 6} className="text-center text-muted-foreground py-8">
                                        {search ? t('No expenses match your search') : t('No expenses yet')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {expenses.data.map((expense) => (
                                <TableRow key={expense.id}>
                                    <TableCell className="font-medium">{expense.title}</TableCell>
                                    <TableCell>{expense.types?.title ?? '—'}</TableCell>
                                    <TableCell>{expense.vehicles?.name ?? '—'}</TableCell>
                                    <TableCell>{expense.date}</TableCell>
                                    <TableCell>{expense.amount}</TableCell>
                                    <TableCell>
                                        {expense.receipt ? (
                                            <a
                                                href={`/storage/upload/expense/${expense.receipt}`}
                                                target="_blank"
                                                rel="noreferrer"
                                                className="text-primary underline text-sm"
                                            >
                                                {t('View')}
                                            </a>
                                        ) : '—'}
                                    </TableCell>
                                    {showActions && (
                                        <TableCell className="text-end space-x-1 rtl:space-x-reverse">
                                            {can('edit expense') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('expense.edit', expense.id)} aria-label={t('Edit')}>
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('delete expense') && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="text-destructive hover:text-destructive"
                                                    onClick={() => remove(expense.id)}
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
                    <Pagination paginator={expenses} />
                </div>
        </div>
    );
}

ExpenseIndex.layout = (page) => {
    return (
        <AdminLayout breadcrumbs={[{ label: 'Expenses' }]}>{page}</AdminLayout>
    );
};
export default ExpenseIndex;
