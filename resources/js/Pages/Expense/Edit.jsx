import { z } from 'zod';
import { Controller } from 'react-hook-form';
import { Link } from '@inertiajs/react';
import { useZodForm } from '@/hooks/useZodForm';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import AdminLayout from '@/Layouts/AdminLayout';

const schema = z.object({
    title: z.string().min(1, 'The title field is required.'),
    type: z.string().min(1, 'The type field is required.'),
    date: z.string().min(1, 'The date field is required.'),
    amount: z.string().min(1, 'The amount field is required.'),
});

function ExpenseEdit({ expense = {}, vehicles = {}, types = {} }) {
    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            _method: 'PUT',
            title: expense.title ?? '',
            type: expense.type ? String(expense.type) : '',
            vehicle: expense.vehicle ? String(expense.vehicle) : '',
            date: expense.date ?? '',
            amount: expense.amount ? String(expense.amount) : '',
            notes: expense.notes ?? '',
            receipt: null,
        },
    });
    const { register, control, setValue, formState: { errors, isSubmitting } } = form;

    return (
        <div className="space-y-6 p-6">
            <form onSubmit={submit('post', route('expense.update', expense.id), { forceFormData: true })} className="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Edit Expense</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div className="space-y-1.5">
                                <Label htmlFor="title">Title</Label>
                                <Input id="title" placeholder="Enter title" {...register('title')} />
                                {errors.title && <p className="text-sm text-destructive">{errors.title.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="type">Expense Type</Label>
                                <Controller
                                    name="type"
                                    control={control}
                                    render={({ field }) => (
                                        <Select value={field.value} onValueChange={field.onChange}>
                                            <SelectTrigger id="type"><SelectValue placeholder="Select Type" /></SelectTrigger>
                                            <SelectContent>
                                                {Object.entries(types).filter(([k]) => k !== '').map(([k, label]) => (
                                                    <SelectItem key={k} value={String(k)}>{label}</SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    )}
                                />
                                {errors.type && <p className="text-sm text-destructive">{errors.type.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="vehicle">Vehicle</Label>
                                <Controller
                                    name="vehicle"
                                    control={control}
                                    render={({ field }) => (
                                        <Select value={field.value} onValueChange={field.onChange}>
                                            <SelectTrigger id="vehicle"><SelectValue placeholder="Select Vehicle" /></SelectTrigger>
                                            <SelectContent>
                                                {Object.entries(vehicles).filter(([k]) => k !== '').map(([k, label]) => (
                                                    <SelectItem key={k} value={String(k)}>{label}</SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    )}
                                />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="date">Date</Label>
                                <Input id="date" type="date" {...register('date')} />
                                {errors.date && <p className="text-sm text-destructive">{errors.date.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="amount">Amount</Label>
                                <Input id="amount" type="number" step="0.01" placeholder="Enter amount" {...register('amount')} />
                                {errors.amount && <p className="text-sm text-destructive">{errors.amount.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="receipt">Receipt {expense.receipt && <span className="text-muted-foreground text-xs">(leave empty to keep current)</span>}</Label>
                                <Input id="receipt" type="file" onChange={(e) => setValue('receipt', e.target.files?.[0] ?? null)} />
                                {expense.receipt && (
                                    <a
                                        href={`/storage/upload/expense/${expense.receipt}`}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="text-sm text-primary underline"
                                    >
                                        View current receipt
                                    </a>
                                )}
                            </div>

                            <div className="space-y-1.5 md:col-span-2">
                                <Label htmlFor="notes">Notes</Label>
                                <Textarea id="notes" placeholder="Enter notes" rows={3} {...register('notes')} />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div className="flex justify-end gap-2">
                    <Button variant="ghost" type="button" asChild>
                        <Link href={route('expense.index')}>Close</Link>
                    </Button>
                    <Button type="submit" disabled={isSubmitting}>Update</Button>
                </div>
            </form>
        </div>
    );
}

ExpenseEdit.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Expenses', href: route('expense.index') },
        { label: 'Edit' },
    ]}>{page}</AdminLayout>
);
export default ExpenseEdit;
