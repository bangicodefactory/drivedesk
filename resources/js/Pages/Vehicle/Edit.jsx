import { z } from 'zod';
import { Controller } from 'react-hook-form';
import { Link } from '@inertiajs/react';
import { useZodForm } from '@/hooks/useZodForm';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';

// Port of resources/views/vehicle/edit.blade.php.
// Submits PUT to route('vehicle.update') via a spoofed _method=PUT (matches the
// Blade @method('PUT') + multipart form so the optional document file uploads).
// The zod schema mirrors the controller's update() `required` rules — note that
// update does NOT require engine_no, registration_expiry_date, picture, or
// year_of_ﬁrst_immatriculation. Laravel validation stays authoritative.
const schema = z.object({
    type: z.string().min(1, 'The type field is required.'),
    name: z.string().min(1, 'The name field is required.'),
    model: z.string().min(1, 'The model field is required.'),
    engine_type: z.string().min(1, 'The engine type field is required.'),
    license_plate: z.string().min(1, 'The license plate field is required.'),
    daily_rate: z.string().min(1, 'The daily rate field is required.'),
    gearbox: z.string().min(1, 'The gearbox field is required.'),
    fuel_type: z.string().min(1, 'The fuel type field is required.'),
    number_of_seats: z.string().min(1, 'The number of seats field is required.'),
    kilometers: z.string().min(1, 'The kilometers field is required.'),
    // Non-validated fields — z.any()/optional() prevents zodResolver from stripping them
    engine_no: z.string().optional(),
    registration_expiry_date: z.string().optional(),
    'year_of_ﬁrst_immatriculation': z.string().optional(),
    option: z.array(z.string()).optional(),
    notes: z.string().optional(),
    picture: z.any().optional(),
    document: z.any().optional(),
    _method: z.string().optional(),
});

const str = (v) => (v != null ? String(v) : '');

function VehicleEdit({ vehicle = {}, types = {}, gearbox = {}, fuelType = {}, option = {} }) {
    const t = useTranslation();
    const selectedOptions = String(vehicle.option ?? '').split(',').filter((v) => v !== '');

    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            type: str(vehicle.type),
            name: vehicle.name ?? '',
            model: vehicle.model ?? '',
            engine_type: vehicle.engine_type ?? '',
            engine_no: vehicle.engine_no ?? '',
            license_plate: vehicle.license_plate ?? '',
            registration_expiry_date: vehicle.registration_expiry_date ?? '',
            daily_rate: str(vehicle.daily_rate),
            'year_of_ﬁrst_immatriculation': str(vehicle['year_of_ﬁrst_immatriculation']),
            gearbox: str(vehicle.gearbox),
            fuel_type: str(vehicle.fuel_type),
            number_of_seats: str(vehicle.number_of_seats),
            kilometers: str(vehicle.kilometers),
            option: selectedOptions,
            notes: vehicle.notes ?? '',
            document: null,
            picture: null,
            _method: 'PUT',
        },
    });
    const { register, control, setValue, formState: { errors, isSubmitting } } = form;

    function toggleOption(field, key) {
        const set = new Set((field.value ?? []).map(String));
        set.has(key) ? set.delete(key) : set.add(key);
        field.onChange([...set]);
    }

    return (
        <div className="space-y-6 p-6">
            <Card>
                <CardHeader>
                    <CardTitle>{t('Edit Vehicle')}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('vehicle.update', vehicle.id), { forceFormData: true })}>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div className="space-y-1.5">
                                <Label htmlFor="name">{t('Vehicle Name')}</Label>
                                <Input id="name" placeholder={t('Enter vehicle name')} {...register('name')} />
                                {errors.name && <p className="text-sm text-destructive">{errors.name.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="type">{t('Type')}</Label>
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
                                <Label htmlFor="model">{t('Model')}</Label>
                                <Input id="model" placeholder={t('Enter model')} {...register('model')} />
                                {errors.model && <p className="text-sm text-destructive">{errors.model.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="engine_type">{t('Engine Type')}</Label>
                                <Input id="engine_type" placeholder={t('Enter engine type')} {...register('engine_type')} />
                                {errors.engine_type && <p className="text-sm text-destructive">{errors.engine_type.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="engine_no">{t('Engine Number')}</Label>
                                <Input id="engine_no" placeholder={t('Enter engine number')} {...register('engine_no')} />
                                {errors.engine_no && <p className="text-sm text-destructive">{errors.engine_no.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="license_plate">{t('License Plate')}</Label>
                                <Input id="license_plate" placeholder={t('Enter license plate')} {...register('license_plate')} />
                                {errors.license_plate && <p className="text-sm text-destructive">{errors.license_plate.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="registration_expiry_date">{t('Registration Expiry Date')}</Label>
                                <Input id="registration_expiry_date" type="date" {...register('registration_expiry_date')} />
                                {errors.registration_expiry_date && <p className="text-sm text-destructive">{errors.registration_expiry_date.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="daily_rate">{t('Daily Rate')}</Label>
                                <Input id="daily_rate" type="number" step="any" placeholder={t('Enter daily rate')} {...register('daily_rate')} />
                                {errors.daily_rate && <p className="text-sm text-destructive">{errors.daily_rate.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="year_of_first_immatriculation">{t('Year of First Immatriculation')}</Label>
                                <Input
                                    id="year_of_first_immatriculation"
                                    type="number"
                                    placeholder={t('Enter Year of First Immatriculation')}
                                    {...register('year_of_ﬁrst_immatriculation')}
                                />
                                {errors['year_of_ﬁrst_immatriculation'] && (
                                    <p className="text-sm text-destructive">{errors['year_of_ﬁrst_immatriculation'].message}</p>
                                )}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="gearbox">{t('Gearbox')}</Label>
                                <Controller
                                    name="gearbox"
                                    control={control}
                                    render={({ field }) => (
                                        <Select value={field.value} onValueChange={field.onChange}>
                                            <SelectTrigger id="gearbox"><SelectValue placeholder={t('Gearbox')} /></SelectTrigger>
                                            <SelectContent>
                                                {Object.entries(gearbox).map(([k, label]) => (
                                                    <SelectItem key={k} value={String(k)}>{label}</SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    )}
                                />
                                {errors.gearbox && <p className="text-sm text-destructive">{errors.gearbox.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="fuel_type">{t('Fuel Type')}</Label>
                                <Controller
                                    name="fuel_type"
                                    control={control}
                                    render={({ field }) => (
                                        <Select value={field.value} onValueChange={field.onChange}>
                                            <SelectTrigger id="fuel_type"><SelectValue placeholder={t('Fuel Type')} /></SelectTrigger>
                                            <SelectContent>
                                                {Object.entries(fuelType).map(([k, label]) => (
                                                    <SelectItem key={k} value={String(k)}>{label}</SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    )}
                                />
                                {errors.fuel_type && <p className="text-sm text-destructive">{errors.fuel_type.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="number_of_seats">{t('Number of Seats')}</Label>
                                <Input id="number_of_seats" type="number" {...register('number_of_seats')} />
                                {errors.number_of_seats && <p className="text-sm text-destructive">{errors.number_of_seats.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="kilometers">{t('Kilometer')}</Label>
                                <Input id="kilometers" type="number" {...register('kilometers')} />
                                {errors.kilometers && <p className="text-sm text-destructive">{errors.kilometers.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label>{t('Options')}</Label>
                                <Controller
                                    name="option"
                                    control={control}
                                    render={({ field }) => (
                                        <div className="space-y-2">
                                            {Object.entries(option).map(([k, label]) => (
                                                <label
                                                    key={k}
                                                    className="flex items-center gap-2 cursor-pointer"
                                                >
                                                    <Checkbox
                                                        checked={(field.value ?? []).map(String).includes(String(k))}
                                                        onCheckedChange={() => toggleOption(field, String(k))}
                                                    />
                                                    <span className="text-sm">{label}</span>
                                                </label>
                                            ))}
                                        </div>
                                    )}
                                />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="document">{t('Document')}</Label>
                                <Input id="document" type="file" onChange={(e) => setValue('document', e.target.files?.[0] ?? null)} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="picture">{t('Photo de voiture')}</Label>
                                <Input id="picture" type="file" onChange={(e) => setValue('picture', e.target.files?.[0] ?? null)} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="notes">{t('Notes')}</Label>
                                <Textarea id="notes" placeholder={t('Enter notes')} rows={1} {...register('notes')} />
                            </div>
                        </div>

                        <div className="flex justify-end gap-2 mt-4">
                            <Button variant="ghost" type="button" asChild>
                                <Link href={route('vehicle.index')}>{t('Close')}</Link>
                            </Button>
                            <Button type="submit" disabled={isSubmitting}>{t('Update')}</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

VehicleEdit.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Vehicles', href: route('vehicle.index') },
        { label: 'Edit' },
    ]}>{page}</AdminLayout>
);
export default VehicleEdit;
