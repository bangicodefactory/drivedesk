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

// Port of resources/views/driver/create.blade.php.
// Field names match the Blade form 1:1 (first_name, last_name, email,
// phone_number, gender, age, birth_date, address, license_number, issue_date,
// expiration_date, license, license1, reference, document, document1, notes,
// ICE_company). Posts multipart to route('driver.store') (url('driver')).
// The zod schema mirrors the controller store() `required` rules for UX only;
// Laravel validation stays authoritative and its errors surface via setError
// inside useZodForm. Note: when email is empty the controller skips the email
// rule and auto-generates one, so email is optional client-side.
const schema = z.object({
    first_name: z.string().min(1, 'The first name field is required.'),
    last_name: z.string().min(1, 'The last name field is required.'),
    email: z.string().optional(),
    phone_number: z.string().min(1, 'The phone number field is required.'),
    gender: z.string().min(1, 'The gender field is required.'),
    age: z.any().optional(),
    birth_date: z.string().min(1, 'The birth date field is required.'),
    address: z.string().min(1, 'The address field is required.'),
    license_number: z.string().min(1, 'The license number field is required.'),
    issue_date: z.string().min(1, 'The issue date field is required.'),
    expiration_date: z.string().min(1, 'The expiration date field is required.'),
    // File fields — z.any() prevents zodResolver from stripping them before submission
    license: z.any().optional(),
    license1: z.any().optional(),
    reference: z.string().optional(),
    document: z.any().optional(),
    document1: z.any().optional(),
    notes: z.string().optional(),
    ICE_company: z.string().optional(),
});

function DriverCreate({ gender = {} }) {
    const t = useTranslation();
    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            first_name: '',
            last_name: '',
            email: '',
            phone_number: '',
            gender: '',
            age: '',
            birth_date: '',
            address: '',
            license_number: '',
            issue_date: '',
            expiration_date: '',
            license: null,
            license1: null,
            reference: '',
            document: null,
            document1: null,
            notes: '',
            ICE_company: '',
        },
    });
    const { register, control, setValue, formState: { errors, isSubmitting } } = form;

    return (
        <div className="space-y-6 p-6">
            <Card>
                <CardHeader>
                    <CardTitle>{t('Create Driver')}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('driver.store'), { forceFormData: true })}>
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
                                <Input id="phone_number" placeholder={t('Enter Phone Number')} {...register('phone_number')} {...fieldA11y(errors, 'phone_number')} />
                                <FieldError name="phone_number" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="gender">{t('Gender')}</Label>
                                <Controller
                                    name="gender"
                                    control={control}
                                    render={({ field }) => (
                                        <Select value={field.value} onValueChange={field.onChange}>
                                            <SelectTrigger id="gender" {...fieldA11y(errors, 'gender')}><SelectValue placeholder={t('Gender')} /></SelectTrigger>
                                            <SelectContent>
                                                {Object.entries(gender).map(([k, label]) => (
                                                    <SelectItem key={k} value={String(k)}>{label}</SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    )}
                                />
                                <FieldError name="gender" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="age">{t('age')}</Label>
                                <Input id="age" type="number" placeholder={t('Enter age')} {...register('age')} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="birth_date">{t('Birth date')}</Label>
                                <Input id="birth_date" type="date" {...register('birth_date')} {...fieldA11y(errors, 'birth_date')} />
                                <FieldError name="birth_date" errors={errors} />
                            </div>

                            <div className="space-y-1.5 md:col-span-2">
                                <Label htmlFor="address">{t('Address')}</Label>
                                <Textarea id="address" placeholder={t('Enter address')} rows={1} {...register('address')} {...fieldA11y(errors, 'address')} />
                                <FieldError name="address" errors={errors} />
                            </div>

                            <div className="space-y-1.5 md:col-span-2">
                                <Label htmlFor="license_number">{t('License Number')}</Label>
                                <Input id="license_number" placeholder={t('Enter license number')} {...register('license_number')} {...fieldA11y(errors, 'license_number')} />
                                <FieldError name="license_number" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="issue_date">{t('Issue Date')}</Label>
                                <Input id="issue_date" type="date" {...register('issue_date')} {...fieldA11y(errors, 'issue_date')} />
                                <FieldError name="issue_date" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="expiration_date">{t('Expiration Date')}</Label>
                                <Input id="expiration_date" type="date" {...register('expiration_date')} {...fieldA11y(errors, 'expiration_date')} />
                                <FieldError name="expiration_date" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="license">{t('License 1:')}</Label>
                                <Input id="license" type="file" onChange={(e) => setValue('license', e.target.files?.[0] ?? null)} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="license1">{t('License 2:')}</Label>
                                <Input id="license1" type="file" onChange={(e) => setValue('license1', e.target.files?.[0] ?? null)} />
                            </div>

                            <div className="space-y-1.5 md:col-span-2">
                                <Label htmlFor="reference">{t('Reference')}</Label>
                                <Input id="reference" placeholder={t('Enter reference')} {...register('reference')} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="document">{t('ID file 1:')}</Label>
                                <Input id="document" type="file" onChange={(e) => setValue('document', e.target.files?.[0] ?? null)} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="document1">{t('ID file 2:')}</Label>
                                <Input id="document1" type="file" onChange={(e) => setValue('document1', e.target.files?.[0] ?? null)} />
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
                            <Button type="submit" disabled={isSubmitting}>{t('Create')}</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

DriverCreate.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Driver', href: route('driver.index') },
        { label: 'Create' },
    ]}>{page}</AdminLayout>
);
export default DriverCreate;
