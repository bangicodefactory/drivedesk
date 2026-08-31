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
import FieldError from '@/components/FieldError';
import { fieldA11y } from '@/lib/fieldA11y';

// Port of resources/views/vehicle/create.blade.php.
// Field names match the Blade form 1:1 (type, name, model, engine_type,
// engine_no, license_plate, registration_expiry_date, daily_rate,
// year_of_ﬁrst_immatriculation [ﬁ ligature preserved verbatim], gearbox,
// fuel_type, number_of_seats, kilometers, option[], notes, document, picture).
// Posts to route('vehicle.store'). The zod schema mirrors the controller's
// `required` rules for UX only; Laravel validation stays authoritative and its
// errors surface via setError inside useZodForm.
const schema = z.object({
    type: z.string().min(1, 'The type field is required.'),
    name: z.string().min(1, 'The name field is required.'),
    model: z.string().min(1, 'The model field is required.'),
    engine_type: z.string().min(1, 'The engine type field is required.'),
    engine_no: z.string().min(1, 'The engine no field is required.'),
    license_plate: z.string().min(1, 'The license plate field is required.'),
    registration_expiry_date: z.string().min(1, 'The registration expiry date field is required.'),
    daily_rate: z.string().min(1, 'The daily rate field is required.'),
    'year_of_ﬁrst_immatriculation': z.string().min(1, 'The year of first immatriculation field is required.'),
    gearbox: z.string().min(1, 'The gearbox field is required.'),
    fuel_type: z.string().min(1, 'The fuel type field is required.'),
    number_of_seats: z.string().min(1, 'The number of seats field is required.'),
    kilometers: z.string().min(1, 'The kilometers field is required.'),
    // Non-validated fields — z.any()/optional() prevents zodResolver from stripping them
    option: z.array(z.string()).optional(),
    notes: z.string().optional(),
    picture: z.any().optional(),
    document: z.any().optional(),
});

function VehicleCreate({ types = {}, gearbox = {}, fuelType = {}, option = {} }) {
    const t = useTranslation();
    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            type: '',
            name: '',
            model: '',
            engine_type: '',
            engine_no: '',
            license_plate: '',
            registration_expiry_date: '',
            daily_rate: '',
            'year_of_ﬁrst_immatriculation': '',
            gearbox: '',
            fuel_type: '',
            number_of_seats: '',
            kilometers: '',
            option: [],
            notes: '',
            picture: null,
            document: null,
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
                    <CardTitle>{t('Create Vehicle')}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('vehicle.store'), { forceFormData: true })}>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div className="space-y-1.5">
                                <Label htmlFor="name">{t('Vehicle Name')}</Label>
                                <Input id="name" placeholder={t('Enter vehicle name')} {...register('name')} {...fieldA11y(errors, 'name')} />
                                <FieldError name="name" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="type">{t('Type')}</Label>
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
                                <Label htmlFor="model">{t('Model')}</Label>
                                <Input id="model" placeholder={t('Enter model')} {...register('model')} {...fieldA11y(errors, 'model')} />
                                <FieldError name="model" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="engine_type">{t('Engine Type')}</Label>
                                <Input id="engine_type" placeholder={t('Enter engine type')} {...register('engine_type')} {...fieldA11y(errors, 'engine_type')} />
                                <FieldError name="engine_type" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="engine_no">{t('Engine Number')}</Label>
                                <Input id="engine_no" placeholder={t('Enter engine number')} {...register('engine_no')} {...fieldA11y(errors, 'engine_no')} />
                                <FieldError name="engine_no" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="license_plate">{t('License Plate')}</Label>
                                <Input id="license_plate" placeholder={t('Enter license plate')} {...register('license_plate')} {...fieldA11y(errors, 'license_plate')} />
                                <FieldError name="license_plate" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="registration_expiry_date">{t('Registration Expiry Date')}</Label>
                                <Input id="registration_expiry_date" type="date" {...register('registration_expiry_date')} {...fieldA11y(errors, 'registration_expiry_date')} />
                                <FieldError name="registration_expiry_date" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="daily_rate">{t('Daily Rate')}</Label>
                                <Input id="daily_rate" type="number" step="any" placeholder={t('Enter daily rate')} {...register('daily_rate')} {...fieldA11y(errors, 'daily_rate')} />
                                <FieldError name="daily_rate" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="year_of_first_immatriculation">{t('Year of First Immatriculation')}</Label>
                                <Input
                                    id="year_of_first_immatriculation"
                                    type="number"
                                    placeholder={t('Enter Year of First Immatriculation')}
                                    {...register('year_of_ﬁrst_immatriculation')} {...fieldA11y(errors, 'year_of_ﬁrst_immatriculation')}
                                />
                                <FieldError name="year_of_ﬁrst_immatriculation" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="gearbox">{t('Gearbox')}</Label>
                                <Controller
                                    name="gearbox"
                                    control={control}
                                    render={({ field }) => (
                                        <Select value={field.value} onValueChange={field.onChange}>
                                            <SelectTrigger id="gearbox" {...fieldA11y(errors, 'gearbox')}><SelectValue placeholder={t('Gearbox')} /></SelectTrigger>
                                            <SelectContent>
                                                {Object.entries(gearbox).map(([k, label]) => (
                                                    <SelectItem key={k} value={String(k)}>{label}</SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    )}
                                />
                                <FieldError name="gearbox" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="fuel_type">{t('Fuel Type')}</Label>
                                <Controller
                                    name="fuel_type"
                                    control={control}
                                    render={({ field }) => (
                                        <Select value={field.value} onValueChange={field.onChange}>
                                            <SelectTrigger id="fuel_type" {...fieldA11y(errors, 'fuel_type')}><SelectValue placeholder={t('Fuel Type')} /></SelectTrigger>
                                            <SelectContent>
                                                {Object.entries(fuelType).map(([k, label]) => (
                                                    <SelectItem key={k} value={String(k)}>{label}</SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    )}
                                />
                                <FieldError name="fuel_type" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="number_of_seats">{t('Number of Seats')}</Label>
                                <Input id="number_of_seats" type="number" {...register('number_of_seats')} {...fieldA11y(errors, 'number_of_seats')} />
                                <FieldError name="number_of_seats" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="kilometers">{t('Kilometer')}</Label>
                                <Input id="kilometers" type="number" {...register('kilometers')} {...fieldA11y(errors, 'kilometers')} />
                                <FieldError name="kilometers" errors={errors} />
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
                            <Button type="submit" disabled={isSubmitting}>{t('Create')}</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

VehicleCreate.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Vehicles', href: route('vehicle.index') },
        { label: 'Create' },
    ]}>{page}</AdminLayout>
);
export default VehicleCreate;
