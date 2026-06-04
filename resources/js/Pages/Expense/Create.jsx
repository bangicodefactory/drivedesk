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
import { useTranslation } from '@/hooks/useTranslation';

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
    const t = useTranslation();
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
                        <CardTitle>{t('Create Expense')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div className="space-y-1.5">
                                <Label htmlFor="title">{t('Title')}</Label>
                                <Input id="title" placeholder={t('Enter title')} {...register('title')} />
                                {errors.title && <p className="text-sm text-destructive">{errors.title.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="type">{t('Expense Type')}</Label>
                                <Controller
                                    name="type"
                                    control={control}
                                    render={({ field }) => (
                                        <Select value={field.value} onValueChange={field.onChange}>
                                            <SelectTrigger id="type"><SelectValue placeholder={t('Select Type')} /></SelectTrigger>
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
                                <Label>{t('Vehicle')}</Label>
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
                                            placeholder={t('Select Vehicle')}
                                            searchPlaceholder={t('Search vehicle…')}
                                            ariaLabel={t('Vehicle')}
                                        />
                                    )}
                                />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="date">{t('Date')}</Label>
                                <Input id="date" type="date" {...register('date')} />
                                {errors.date && <p className="text-sm text-destructive">{errors.date.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="amount">{t('Amount')}</Label>
                                <Input id="amount" type="number" step="0.01" placeholder={t('Enter amount')} {...register('amount')} />
                                {errors.amount && <p className="text-sm text-destructive">{errors.amount.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="receipt">{t('Receipt')}</Label>
                                <Input id="receipt" type="file" onChange={(e) => setValue('receipt', e.target.files?.[0] ?? null)} />
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
                    <Button type="submit" disabled={isSubmitting}>{t('Create')}</Button>
                </div>
            </form>
        </div>
    );
}

ExpenseCreate.layout = (page) => {
    return (
        <AdminLayout breadcrumbs={[
            { label: 'Expenses', href: route('expense.index') },
            { label: 'Create' },
        ]}>{page}</AdminLayout>
    );
};
export default ExpenseCreate;
