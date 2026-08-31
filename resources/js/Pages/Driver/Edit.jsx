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

// Port of resources/views/driver/edit.blade.php.
// Submits PUT to route('driver.update') via a spoofed _method=PUT (matches the
// Blade @method('PUT') + multipart form so the optional document/license files
// upload). The zod schema mirrors the controller update() `required` rules:
// update only requires first_name, last_name, email. Laravel validation stays
// authoritative. Props `driver` and `user` match the controller compact().
const schema = z.object({
    first_name: z.string().min(1, 'The first name field is required.'),
    last_name: z.string().min(1, 'The last name field is required.'),
    email: z.string().min(1, 'The email field is required.'),
    // Non-validated fields — z.any() prevents zodResolver from stripping them
    phone_number: z.string().optional(),
    gender: z.any().optional(),
    age: z.any().optional(),
    birth_date: z.string().optional(),
    address: z.string().optional(),
    license_number: z.string().optional(),
    issue_date: z.string().optional(),
    expiration_date: z.string().optional(),
    document: z.any().optional(),
    document1: z.any().optional(),
    license: z.any().optional(),
    license1: z.any().optional(),
    reference: z.string().optional(),
    notes: z.string().optional(),
    ICE_company: z.string().optional(),
    _method: z.string().optional(),
});

const str = (v) => (v != null ? String(v) : '');

function DriverEdit({ driver, user = {}, gender = {} }) {
    // The controller sends driver: null (not undefined) when the user has no
    // driver profile, so a default parameter alone doesn't protect the
    // driver.* reads below.
    driver = driver ?? {};
    const t = useTranslation();
    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            first_name: user.first_name ?? '',
            last_name: user.last_name ?? '',
            email: user.email ?? '',
            phone_number: user.phone_number ?? '',
            // Gender lives on the drivers table, not users — reading user.gender
            // (always undefined) left the select empty and every save NULLed the
            // stored value.
            gender: str(driver.gender),
            age: str(driver.age),
            birth_date: driver.birth_date ?? '',
            address: driver.address ?? '',
            license_number: driver.license_number ?? '',
            issue_date: driver.issue_date ?? '',
            expiration_date: driver.expiration_date ?? '',
            document: null,
            document1: null,
            license: null,
            license1: null,
            reference: driver.reference ?? '',
            notes: driver.notes ?? '',
            ICE_company: driver.ICE_company ?? '',
            _method: 'PUT',
        },
    });
    const { register, control, setValue, formState: { errors, isSubmitting } } = form;

    return (
        <div className="space-y-6 p-6">
            <Card>
                <CardHeader>
                    <CardTitle>{t('Edit Driver')}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('driver.update', user.id), { forceFormData: true })}>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div className="space-y-1.5">
                                <Label htmlFor="first_name">{t('First Name')}</Label>
                                <Input id="first_name" placeholder={t('Enter First Name')} {...register('first_name')} {...fieldA11y(errors, 'first_name')} />
                                <FieldError name="first_name" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="last_name">{t('Last Name')}</Label>
                                <Input id="last_name" placeholder={t('Enter First Name')} {...register('last_name')} {...fieldA11y(errors, 'last_name')} />
                                <FieldError name="last_name" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="email">{t('Email')}</Label>
                                <Input id="email" placeholder={t('Enter Email')} {...register('email')} {...fieldA11y(errors, 'email')} />
                                <FieldError name="email" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="phone_number">{t('Phone Number')}</Label>
                                <Input id="phone_number" placeholder={t('Enter Phone Number')} {...register('phone_number')} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="gender">{t('Gender')}</Label>
                                <Controller
                                    name="gender"
                                    control={control}
                                    render={({ field }) => (
                                        <Select value={field.value} onValueChange={field.onChange}>
                                            <SelectTrigger id="gender"><SelectValue placeholder={t('Gender')} /></SelectTrigger>
                                            <SelectContent>
                                                {Object.entries(gender).map(([k, label]) => (
                                                    <SelectItem key={k} value={String(k)}>{label}</SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    )}
                                />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="age">{t('age')}</Label>
                                <Input id="age" type="number" placeholder={t('Enter age')} {...register('age')} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="birth_date">{t('Birth date')}</Label>
                                <Input id="birth_date" type="date" {...register('birth_date')} />
                            </div>

                            <div className="space-y-1.5 md:col-span-2">
                                <Label htmlFor="address">{t('Address')}</Label>
                                <Textarea id="address" placeholder={t('Enter address')} rows={1} {...register('address')} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="license_number">{t('License Number')}</Label>
                                <Input id="license_number" placeholder={t('Enter license number')} {...register('license_number')} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="issue_date">{t('Issue Date')}</Label>
                                <Input id="issue_date" type="date" {...register('issue_date')} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="expiration_date">{t('Expiration Date')}</Label>
                                <Input id="expiration_date" type="date" {...register('expiration_date')} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="license">{t('License 1:')}</Label>
                                <Input id="license" type="file" onChange={(e) => setValue('license', e.target.files?.[0] ?? null)} />
                                {driver.license && <p className="text-xs text-muted-foreground">{t('Current:')} {driver.license}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="license1">{t('License 2:')}</Label>
                                <Input id="license1" type="file" onChange={(e) => setValue('license1', e.target.files?.[0] ?? null)} />
                                {driver.license_1 && <p className="text-xs text-muted-foreground">{t('Current:')} {driver.license_1}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="document">{t('ID file 1:')}</Label>
                                <Input id="document" type="file" onChange={(e) => setValue('document', e.target.files?.[0] ?? null)} />
                                {driver.document && <p className="text-xs text-muted-foreground">{t('Current:')} {driver.document}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="document1">{t('ID file 2:')}</Label>
                                <Input id="document1" type="file" onChange={(e) => setValue('document1', e.target.files?.[0] ?? null)} />
                                {driver.document_1 && <p className="text-xs text-muted-foreground">{t('Current:')} {driver.document_1}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="reference">{t('Reference')}</Label>
                                <Input id="reference" placeholder={t('Enter reference')} {...register('reference')} />
                            </div>

                            <div className="space-y-1.5 md:col-span-2">
                                <Label htmlFor="notes">{t('Notes')}</Label>
                                <Textarea id="notes" placeholder={t('Enter notes')} rows={1} {...register('notes')} />
                            </div>

                            <div className="space-y-1.5 md:col-span-2">
                                <Label htmlFor="ICE_company">{t('ICE_company')}</Label>
                                <Input id="ICE_company" placeholder={t('Enter ICE if client company')} {...register('ICE_company')} />
                            </div>
                        </div>

                        <div className="flex justify-end gap-2 mt-4">
                            <Button variant="ghost" type="button" asChild>
                                <Link href={route('driver.index')}>{t('Close')}</Link>
                            </Button>
                            <Button type="submit" disabled={isSubmitting}>{t('Update')}</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

DriverEdit.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Driver', href: route('driver.index') },
        { label: 'Edit' },
    ]}>{page}</AdminLayout>
);
export default DriverEdit;
