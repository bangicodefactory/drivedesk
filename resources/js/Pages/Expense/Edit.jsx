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
import { useTranslation } from '@/hooks/useTranslation';
import FieldError from '@/components/FieldError';
import { fieldA11y } from '@/lib/fieldA11y';

const schema = z.object({
    _method: z.string().optional(),
    title: z.string().min(1, 'The title field is required.'),
    type: z.string().min(1, 'The type field is required.'),
    date: z.string().min(1, 'The date field is required.'),
    amount: z.string().min(1, 'The amount field is required.'),
    vehicle: z.string().optional(),
    notes: z.string().optional(),
    receipt: z.any().optional(),
});

function ExpenseEdit({ expense = {}, vehicles = {}, types = {} }) {
    const t = useTranslation();
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
                        <CardTitle>{t('Edit Expense')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div className="space-y-1.5">
                                <Label htmlFor="title">{t('Title')}</Label>
                                <Input id="title" placeholder={t('Enter title')} {...register('title')} {...fieldA11y(errors, 'title')} />
                                <FieldError name="title" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="type">{t('Expense Type')}</Label>
                                <Controller
                                    name="type"
                                    control={control}
                                    render={({ field }) => (
                                        <Select value={field.value} onValueChange={field.onChange}>
                                            <SelectTrigger id="type" {...fieldA11y(errors, 'type')}><SelectValue placeholder={t('Select Type')} /></SelectTrigger>
                                            <SelectContent>
                                                {Object.entries(types).filter(([k]) => k !== '').map(([k, label]) => (
                                                    <SelectItem key={k} value={String(k)}>{label}</SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    )}
                                />
                                <FieldError name="type" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="vehicle">{t('Vehicle')}</Label>
                                <Controller
                                    name="vehicle"
                                    control={control}
                                    render={({ field }) => (
                                        <Select value={field.value} onValueChange={field.onChange}>
                                            <SelectTrigger id="vehicle"><SelectValue placeholder={t('Select Vehicle')} /></SelectTrigger>
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
                                <Label htmlFor="date">{t('Date')}</Label>
                                <Input id="date" type="date" {...register('date')} {...fieldA11y(errors, 'date')} />
                                <FieldError name="date" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="amount">{t('Amount')}</Label>
                                <Input id="amount" type="number" step="0.01" placeholder={t('Enter amount')} {...register('amount')} {...fieldA11y(errors, 'amount')} />
                                <FieldError name="amount" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="receipt">{t('Receipt')} {expense.receipt && <span className="text-muted-foreground text-xs">{t('(leave empty to keep current)')}</span>}</Label>
                                <Input id="receipt" type="file" onChange={(e) => setValue('receipt', e.target.files?.[0] ?? null)} />
                                {expense.receipt && (
                                    <a
                                        href={`/storage/upload/expense/${expense.receipt}`}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="text-sm text-primary underline"
                                    >
                                        {t('View current receipt')}
                                    </a>
                                )}
                            </div>

                            <div className="space-y-1.5 md:col-span-2">
                                <Label htmlFor="notes">{t('Notes')}</Label>
                                <Textarea id="notes" placeholder={t('Enter notes')} rows={3} {...register('notes')} />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div className="flex justify-end gap-2">
                    <Button variant="ghost" type="button" asChild>
                        <Link href={route('expense.index')}>{t('Close')}</Link>
                    </Button>
                    <Button type="submit" disabled={isSubmitting}>{t('Update')}</Button>
                </div>
            </form>
        </div>
    );
}

ExpenseEdit.layout = (page) => {
    return (
        <AdminLayout breadcrumbs={[
            { label: 'Expenses', href: route('expense.index') },
            { label: 'Edit' },
        ]}>{page}</AdminLayout>
    );
};
export default ExpenseEdit;
