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
import FieldError from '@/components/FieldError';
import { fieldA11y } from '@/lib/fieldA11y';

// Port of resources/views/addon/create.blade.php.
// Field names match the Blade form 1:1 (name, price, billing_type). Posts to
// route('addon.store') (url('addon')). The zod schema mirrors the controller
// store() `required` rules for UX only; Laravel validation stays authoritative
// and its errors surface via setError inside useZodForm.
// Prop `billingType` matches the controller compact('billingType')
// (Addon::$billingType, e.g. { daily: 'Daily', total: 'Total' }).
const schema = z.object({
    name: z.string().min(1, 'The name field is required.'),
    price: z.string().min(1, 'The price field is required.'),
    billing_type: z.string().min(1, 'The billing type field is required.'),
});

function AddonCreate({ billingType = {} }) {
    const t = useTranslation();
    const entries = Object.entries(billingType);
    const firstKey = entries.length ? entries[0][0] : '';

    const { form, submit } = useZodForm(schema, {
        defaultValues: { name: '', price: '', billing_type: firstKey },
    });
    const { register, setValue, watch, formState: { errors, isSubmitting } } = form;
    const selectedBilling = watch('billing_type');

    return (
        <div className="space-y-6 p-6">
            <Card>
                <CardHeader>
                    <CardTitle>{t('Create Addon')}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('addon.store'))}>
                        <div className="space-y-4">
                            <div className="space-y-1.5">
                                <Label htmlFor="name">{t('Addon')}</Label>
                                <Input id="name" placeholder={t('Enter addon name')} {...register('name')} {...fieldA11y(errors, 'name')} />
                                <FieldError name="name" errors={errors} />
                            </div>
                            <div className="space-y-1.5">
                                <Label htmlFor="price">{t('Price')}</Label>
                                <Input id="price" type="number" placeholder={t('Enter price')} {...register('price')} {...fieldA11y(errors, 'price')} />
                                <FieldError name="price" errors={errors} />
                            </div>
                            <div className="space-y-1.5">
                                <Label htmlFor="billing_type">{t('Billing Type')}</Label>
                                <Select value={selectedBilling} onValueChange={(v) => setValue('billing_type', v)}>
                                    <SelectTrigger id="billing_type" {...fieldA11y(errors, 'billing_type')}>
                                        <SelectValue placeholder={t('Select billing type')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {entries.map(([value, label]) => (
                                            <SelectItem key={value} value={value}>{label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <FieldError name="billing_type" errors={errors} />
                            </div>
                        </div>

                        <div className="flex justify-end gap-2 mt-4">
                            <Button variant="ghost" type="button" asChild>
                                <Link href={route('addon.index')}>{t('Close')}</Link>
                            </Button>
                            <Button type="submit" disabled={isSubmitting}>{t('Create')}</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

AddonCreate.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Addon', href: route('addon.index') },
        { label: 'Create' },
    ]}>{page}</AdminLayout>
);
export default AddonCreate;
