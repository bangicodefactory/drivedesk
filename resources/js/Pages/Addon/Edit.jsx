import { z } from 'zod';
import { Link } from '@inertiajs/react';
import { useZodForm } from '@/hooks/useZodForm';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';

// Port of resources/views/addon/edit.blade.php.
// Submits PUT to route('addon.update') via a spoofed _method=PUT (matches the
// Blade @method('PUT')). Field names match the Blade form 1:1 (name, price,
// billing_type). The zod schema mirrors the controller update() `required`
// rules. Laravel validation stays authoritative. Props `addon` and
// `billingType` match the controller compact('addon','billingType').
const schema = z.object({
    _method: z.string().optional(),
    name: z.string().min(1, 'The name field is required.'),
    price: z.string().min(1, 'The price field is required.'),
    billing_type: z.string().min(1, 'The billing type field is required.'),
});

function AddonEdit({ addon = {}, billingType = {} }) {
    const t = useTranslation();
    const entries = Object.entries(billingType);

    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            name: addon.name ?? '',
            price: addon.price != null ? String(addon.price) : '',
            billing_type: addon.billing_type ?? (entries.length ? entries[0][0] : ''),
            _method: 'PUT',
        },
    });
    const { register, setValue, watch, formState: { errors, isSubmitting } } = form;
    const selectedBilling = watch('billing_type');

    return (
        <div className="space-y-6 p-6">
            <Card>
                <CardHeader>
                    <CardTitle>{t('Edit Addon')}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('addon.update', addon.id))}>
                        <input type="hidden" {...register('_method')} value="PUT" />
                        <div className="space-y-4">
                            <div className="space-y-1.5">
                                <Label htmlFor="name">{t('Addon')}</Label>
                                <Input id="name" placeholder={t('Enter addon name')} {...register('name')} />
                                {errors.name && <p className="text-sm text-destructive">{errors.name.message}</p>}
                            </div>
                            <div className="space-y-1.5">
                                <Label htmlFor="price">{t('Price')}</Label>
                                <Input id="price" type="number" placeholder={t('Enter price')} {...register('price')} />
                                {errors.price && <p className="text-sm text-destructive">{errors.price.message}</p>}
                            </div>
                            <div className="space-y-1.5">
                                <Label htmlFor="billing_type">{t('Billing Type')}</Label>
                                <Select value={selectedBilling} onValueChange={(v) => setValue('billing_type', v)}>
                                    <SelectTrigger id="billing_type">
                                        <SelectValue placeholder={t('Select billing type')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {entries.map(([value, label]) => (
                                            <SelectItem key={value} value={value}>{label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.billing_type && <p className="text-sm text-destructive">{errors.billing_type.message}</p>}
                            </div>
                        </div>

                        <div className="flex justify-end gap-2 mt-4">
                            <Button variant="ghost" type="button" asChild>
                                <Link href={route('addon.index')}>{t('Close')}</Link>
                            </Button>
                            <Button type="submit" disabled={isSubmitting}>{t('Update')}</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

AddonEdit.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Addon', href: route('addon.index') },
        { label: 'Edit' },
    ]}>{page}</AdminLayout>
);
export default AddonEdit;
