import { Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Pencil, Trash2, Plus, Receipt } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

function ExpenseIndex({ expenses = [] }) {
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    function remove(id) {
        if (window.confirm('Are you sure?')) {
            router.delete(route('expense.destroy', id));
        }
    }

    const showActions = can('edit expense') || can('delete expense');

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-semibold flex items-center gap-2">
                    <Receipt className="h-6 w-6" /> Expenses
                </h1>
                {can('create expense') && (
                    <Button size="sm" asChild>
                        <Link href={route('expense.create')}>
                            <Plus className="mr-2 h-4 w-4" /> Create Expense
                        </Link>
                    </Button>
                )}
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>All Expenses</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Title</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Vehicle</TableHead>
                                <TableHead>Date</TableHead>
                                <TableHead>Amount</TableHead>
                                <TableHead>Receipt</TableHead>
                                {showActions && <TableHead className="text-right">Action</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {expenses.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={showActions ? 7 : 6} className="text-center text-muted-foreground py-8">
                                        No expenses yet
                                    </TableCell>
                                </TableRow>
                            )}
                            {expenses.map((expense) => (
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
                                                View
                                            </a>
                                        ) : '—'}
                                    </TableCell>
                                    {showActions && (
                                        <TableCell className="text-right space-x-1">
                                            {can('edit expense') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('expense.edit', expense.id)} aria-label="Edit">
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
                                                    aria-label="Delete"
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

ExpenseIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Expenses' }]}>{page}</AdminLayout>
);
export default ExpenseIndex;
