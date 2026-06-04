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
import { SearchableSelect } from '@/components/ui/searchable-select';
import AdminLayout from '@/Layouts/AdminLayout';

const schema = z.object({
    title: z.string().min(1, 'The title field is required.'),
    type: z.string().min(1, 'The type field is required.'),
    date: z.string().min(1, 'The date field is required.'),
    amount: z.string().min(1, 'The amount field is required.'),
    vehicle: z.string().optional(),
    notes: z.string().optional(),
    receipt: z.any().optional(),
});

function ExpenseCreate({ vehicles = {}, types = {} }) {
    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            title: '',
            type: '',
            vehicle: '',
            date: '',
            amount: '',
            notes: '',
            receipt: null,
        },
    });
    const { register, control, setValue, formState: { errors, isSubmitting } } = form;

    return (
        <div className="space-y-6 p-6">
            <form onSubmit={submit('post', route('expense.store'), { forceFormData: true })} className="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Create Expense</CardTitle>
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
                                <Label>Vehicle</Label>
                                <Controller
                                    name="vehicle"
                                    control={control}
                                    render={({ field }) => (
                                        <SearchableSelect
                                            options={Object.entries(vehicles)
                                                .filter(([k]) => k !== '')
                                                .map(([k, label]) => ({ value: k, label }))}
                                            value={field.value}
                                            onChange={field.onChange}
                                            placeholder="Select Vehicle"
                                            searchPlaceholder="Search vehicle…"
                                            ariaLabel="Vehicle"
                                        />
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
                                <Label htmlFor="receipt">Receipt</Label>
                                <Input id="receipt" type="file" onChange={(e) => setValue('receipt', e.target.files?.[0] ?? null)} />
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
                    <Button type="submit" disabled={isSubmitting}>Create</Button>
                </div>
            </form>
        </div>
    );
}

ExpenseCreate.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Expenses', href: route('expense.index') },
        { label: 'Create' },
    ]}>{page}</AdminLayout>
);
export default ExpenseCreate;
