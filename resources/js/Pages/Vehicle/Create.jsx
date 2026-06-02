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
    const { register, control, setValue, watch, formState: { errors, isSubmitting } } = form;

    const selectedOptions = (watch('option') ?? []).map(String);

    return (
        <div className="space-y-6 p-6">
            <Card>
                <CardHeader>
                    <CardTitle>Create Vehicle</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('vehicle.store'), { forceFormData: true })}>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div className="space-y-1.5">
                                <Label htmlFor="name">Vehicle Name</Label>
                                <Input id="name" placeholder="Enter vehicle name" {...register('name')} />
                                {errors.name && <p className="text-sm text-destructive">{errors.name.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="type">Type</Label>
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
                                <Label htmlFor="model">Model</Label>
                                <Input id="model" placeholder="Enter model" {...register('model')} />
                                {errors.model && <p className="text-sm text-destructive">{errors.model.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="engine_type">Engine Type</Label>
                                <Input id="engine_type" placeholder="Enter engine type" {...register('engine_type')} />
                                {errors.engine_type && <p className="text-sm text-destructive">{errors.engine_type.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="engine_no">Engine Number</Label>
                                <Input id="engine_no" placeholder="Enter engine number" {...register('engine_no')} />
                                {errors.engine_no && <p className="text-sm text-destructive">{errors.engine_no.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="license_plate">License Plate</Label>
                                <Input id="license_plate" placeholder="Enter license plate" {...register('license_plate')} />
                                {errors.license_plate && <p className="text-sm text-destructive">{errors.license_plate.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="registration_expiry_date">Registration Expiry Date</Label>
                                <Input id="registration_expiry_date" type="date" {...register('registration_expiry_date')} />
                                {errors.registration_expiry_date && <p className="text-sm text-destructive">{errors.registration_expiry_date.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="daily_rate">Daily Rate</Label>
                                <Input id="daily_rate" type="number" step="any" placeholder="Enter daily rate" {...register('daily_rate')} />
                                {errors.daily_rate && <p className="text-sm text-destructive">{errors.daily_rate.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="year_of_first_immatriculation">Year of First Immatriculation</Label>
                                <Input
                                    id="year_of_first_immatriculation"
                                    type="number"
                                    placeholder="Enter Year of First Immatriculation"
                                    {...register('year_of_ﬁrst_immatriculation')}
                                />
                                {errors['year_of_ﬁrst_immatriculation'] && (
                                    <p className="text-sm text-destructive">{errors['year_of_ﬁrst_immatriculation'].message}</p>
                                )}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="gearbox">Gearbox</Label>
                                <Controller
                                    name="gearbox"
                                    control={control}
                                    render={({ field }) => (
                                        <Select value={field.value} onValueChange={field.onChange}>
                                            <SelectTrigger id="gearbox"><SelectValue placeholder="Gearbox" /></SelectTrigger>
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
                                <Label htmlFor="fuel_type">Fuel Type</Label>
                                <Controller
                                    name="fuel_type"
                                    control={control}
                                    render={({ field }) => (
                                        <Select value={field.value} onValueChange={field.onChange}>
                                            <SelectTrigger id="fuel_type"><SelectValue placeholder="Fuel Type" /></SelectTrigger>
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
                                <Label htmlFor="number_of_seats">Number of Seats</Label>
                                <Input id="number_of_seats" type="number" {...register('number_of_seats')} />
                                {errors.number_of_seats && <p className="text-sm text-destructive">{errors.number_of_seats.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="kilometers">Kilometer</Label>
                                <Input id="kilometers" type="number" {...register('kilometers')} />
                                {errors.kilometers && <p className="text-sm text-destructive">{errors.kilometers.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="option">Options</Label>
                                <select
                                    id="option"
                                    multiple
                                    className="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                    value={selectedOptions}
                                    onChange={(e) =>
                                        setValue('option', Array.from(e.target.selectedOptions, (o) => o.value))
                                    }
                                >
                                    {Object.entries(option).map(([k, label]) => (
                                        <option key={k} value={String(k)}>{label}</option>
                                    ))}
                                </select>
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="document">Document</Label>
                                <Input id="document" type="file" onChange={(e) => setValue('document', e.target.files?.[0] ?? null)} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="picture">Photo de voiture</Label>
                                <Input id="picture" type="file" onChange={(e) => setValue('picture', e.target.files?.[0] ?? null)} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="notes">Notes</Label>
                                <Textarea id="notes" placeholder="Enter notes" rows={1} {...register('notes')} />
                            </div>
                        </div>

                        <div className="flex justify-end gap-2 mt-4">
                            <Button variant="ghost" type="button" asChild>
                                <Link href={route('vehicle.index')}>Close</Link>
                            </Button>
                            <Button type="submit" disabled={isSubmitting}>Create</Button>
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
