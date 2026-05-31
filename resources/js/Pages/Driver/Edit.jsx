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
});

const str = (v) => (v != null ? String(v) : '');

function DriverEdit({ driver = {}, user = {}, gender = {} }) {
    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            first_name: user.first_name ?? '',
            last_name: user.last_name ?? '',
            email: user.email ?? '',
            phone_number: user.phone_number ?? '',
            gender: str(user.gender),
            age: str(driver.age),
            birth_date: driver.birth_date ?? '',
            address: driver.address ?? '',
            license_number: driver.license_number ?? '',
            issue_date: driver.issue_date ?? '',
            expiration_date: driver.expiration_date ?? '',
            document: null,
            license: null,
            reference: driver.reference ?? '',
            notes: driver.notes ?? '',
            _method: 'PUT',
        },
    });
    const { register, control, setValue, formState: { errors, isSubmitting } } = form;

    return (
        <div className="space-y-6 p-6">
            <Card>
                <CardHeader>
                    <CardTitle>Edit Driver</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('driver.update', user.id), { forceFormData: true })}>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div className="space-y-1.5">
                                <Label htmlFor="first_name">First Name</Label>
                                <Input id="first_name" placeholder="Enter First Name" {...register('first_name')} />
                                {errors.first_name && <p className="text-sm text-destructive">{errors.first_name.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="last_name">Last Name</Label>
                                <Input id="last_name" placeholder="Enter First Name" {...register('last_name')} />
                                {errors.last_name && <p className="text-sm text-destructive">{errors.last_name.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="email">Email</Label>
                                <Input id="email" placeholder="Enter Email" {...register('email')} />
                                {errors.email && <p className="text-sm text-destructive">{errors.email.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="phone_number">Phone Number</Label>
                                <Input id="phone_number" placeholder="Enter Phone Number" {...register('phone_number')} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="gender">Gender</Label>
                                <Controller
                                    name="gender"
                                    control={control}
                                    render={({ field }) => (
                                        <Select value={field.value} onValueChange={field.onChange}>
                                            <SelectTrigger id="gender"><SelectValue placeholder="Gender" /></SelectTrigger>
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
                                <Label htmlFor="age">age</Label>
                                <Input id="age" type="number" placeholder="Enter age" {...register('age')} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="birth_date">Birth date</Label>
                                <Input id="birth_date" type="date" {...register('birth_date')} />
                            </div>

                            <div className="space-y-1.5 md:col-span-2">
                                <Label htmlFor="address">Address</Label>
                                <Textarea id="address" placeholder="Enter address" rows={1} {...register('address')} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="license_number">License Number</Label>
                                <Input id="license_number" placeholder="Enter license number" {...register('license_number')} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="issue_date">Issue Date</Label>
                                <Input id="issue_date" type="date" {...register('issue_date')} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="expiration_date">Expiration Date</Label>
                                <Input id="expiration_date" type="date" {...register('expiration_date')} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="document">Document</Label>
                                <Input id="document" type="file" onChange={(e) => setValue('document', e.target.files?.[0] ?? null)} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="license">License</Label>
                                <Input id="license" type="file" onChange={(e) => setValue('license', e.target.files?.[0] ?? null)} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="reference">Reference</Label>
                                <Input id="reference" placeholder="Enter reference" {...register('reference')} />
                            </div>

                            <div className="space-y-1.5 md:col-span-2">
                                <Label htmlFor="notes">Notes</Label>
                                <Textarea id="notes" placeholder="Enter notes" rows={1} {...register('notes')} />
                            </div>
                        </div>

                        <div className="flex justify-end gap-2 mt-4">
                            <Button variant="ghost" type="button" asChild>
                                <Link href={route('driver.index')}>Close</Link>
                            </Button>
                            <Button type="submit" disabled={isSubmitting}>Update</Button>
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
