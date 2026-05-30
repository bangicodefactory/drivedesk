import React from 'react';
import { Head, usePage } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslations } from '@/hooks/useTranslations';
import { useZodForm } from '@/hooks/useZodForm';
import { z } from 'zod';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

// Port of resources/views/vehicle/create.blade.php.
// Field names match the Blade form 1:1 (type, name, model, engine_type,
// engine_no, license_plate, registration_expiry_date, picture, daily_rate,
// year_of_ﬁrst_immatriculation, gearbox, fuel_type, number_of_seats,
// kilometers, option[], notes, document). Posts to route('vehicle.store').
// Server-side Laravel validation stays authoritative; the zod schema here is
// duplicative UX only and mirrors the controller's `required` rules.
export default function VehicleCreate() {
    const { props } = usePage();
    const t = useTranslations();

    // `types`, `gearbox`, `fuelType`, `option` are key => label maps mirroring
    // the Blade pluck()/static-array selects.
    const types = props.types ?? {};
    const gearbox = props.gearbox ?? {};
    const fuelType = props.fuelType ?? {};
    const option = props.option ?? {};

    const schema = z.object({
        type: z.string().min(1, t('The type field is required.')),
        name: z.string().min(1, t('The name field is required.')),
        model: z.string().min(1, t('The model field is required.')),
        engine_type: z.string().min(1, t('The engine type field is required.')),
        engine_no: z.string().min(1, t('The engine no field is required.')),
        license_plate: z.string().min(1, t('The license plate field is required.')),
        registration_expiry_date: z.string().min(1, t('The registration expiry date field is required.')),
        daily_rate: z.string().min(1, t('The daily rate field is required.')),
        'year_of_ﬁrst_immatriculation': z.string().min(1, t('The year of first immatriculation field is required.')),
        gearbox: z.string().min(1, t('The gearbox field is required.')),
        fuel_type: z.string().min(1, t('The fuel type field is required.')),
        number_of_seats: z.string().min(1, t('The number of seats field is required.')),
        kilometers: z.string().min(1, t('The kilometers field is required.')),
    });

    const { data, setData, post, processing, errors, handleSubmit } = useZodForm({
        schema,
        defaults: {
            type: '',
            name: '',
            model: '',
            engine_type: '',
            engine_no: '',
            license_plate: '',
            registration_expiry_date: '',
            picture: null,
            daily_rate: '',
            'year_of_ﬁrst_immatriculation': '',
            gearbox: '',
            fuel_type: '',
            number_of_seats: '',
            kilometers: '',
            option: [],
            notes: '',
            document: null,
        },
        onValid: () => post(route('vehicle.store'), { forceFormData: true }),
    });

    const toggleOption = (key) => {
        const set = new Set(data.option.map(String));
        if (set.has(String(key))) {
            set.delete(String(key));
        } else {
            set.add(String(key));
        }
        setData('option', Array.from(set));
    };

    return (
        <AdminLayout>
            <Head title={t('Create Vehicle')} />
            <div className="row">
                <div className="col-sm-12">
                    <div className="card">
                        <div className="card-header">
                            <h5>{t('Create Vehicle')}</h5>
                        </div>
                        <div className="card-body">
                            <form onSubmit={handleSubmit} encType="multipart/form-data">
                                <div className="row">
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <Label className="form-label">{t('Type')}</Label>
                                            <select
                                                name="type"
                                                className="form-control"
                                                value={data.type}
                                                onChange={(e) => setData('type', e.target.value)}
                                            >
                                                {Object.entries(types).map(([key, label]) => (
                                                    <option key={key} value={key}>{label}</option>
                                                ))}
                                            </select>
                                            {errors.type && <span className="text-danger">{errors.type}</span>}
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <Label className="form-label">{t('Name')}</Label>
                                            <Input
                                                type="text"
                                                name="name"
                                                placeholder={t('Name')}
                                                value={data.name}
                                                onChange={(e) => setData('name', e.target.value)}
                                            />
                                            {errors.name && <span className="text-danger">{errors.name}</span>}
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <Label className="form-label">{t('Model')}</Label>
                                            <Input
                                                type="text"
                                                name="model"
                                                placeholder={t('Model')}
                                                value={data.model}
                                                onChange={(e) => setData('model', e.target.value)}
                                            />
                                            {errors.model && <span className="text-danger">{errors.model}</span>}
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <Label className="form-label">{t('Engine Type')}</Label>
                                            <Input
                                                type="text"
                                                name="engine_type"
                                                placeholder={t('Engine Type')}
                                                value={data.engine_type}
                                                onChange={(e) => setData('engine_type', e.target.value)}
                                            />
                                            {errors.engine_type && <span className="text-danger">{errors.engine_type}</span>}
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <Label className="form-label">{t('Engine No')}</Label>
                                            <Input
                                                type="text"
                                                name="engine_no"
                                                placeholder={t('Engine No')}
                                                value={data.engine_no}
                                                onChange={(e) => setData('engine_no', e.target.value)}
                                            />
                                            {errors.engine_no && <span className="text-danger">{errors.engine_no}</span>}
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <Label className="form-label">{t('License Plate')}</Label>
                                            <Input
                                                type="text"
                                                name="license_plate"
                                                placeholder={t('License Plate')}
                                                value={data.license_plate}
                                                onChange={(e) => setData('license_plate', e.target.value)}
                                            />
                                            {errors.license_plate && <span className="text-danger">{errors.license_plate}</span>}
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <Label className="form-label">{t('Registration Expiry Date')}</Label>
                                            <Input
                                                type="date"
                                                name="registration_expiry_date"
                                                value={data.registration_expiry_date}
                                                onChange={(e) => setData('registration_expiry_date', e.target.value)}
                                            />
                                            {errors.registration_expiry_date && <span className="text-danger">{errors.registration_expiry_date}</span>}
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <Label className="form-label">{t('Picture')}</Label>
                                            <Input
                                                type="file"
                                                name="picture"
                                                onChange={(e) => setData('picture', e.target.files[0] ?? null)}
                                            />
                                            {errors.picture && <span className="text-danger">{errors.picture}</span>}
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <Label className="form-label">{t('Daily Rate')}</Label>
                                            <Input
                                                type="number"
                                                step="any"
                                                name="daily_rate"
                                                placeholder={t('Daily Rate')}
                                                value={data.daily_rate}
                                                onChange={(e) => setData('daily_rate', e.target.value)}
                                            />
                                            {errors.daily_rate && <span className="text-danger">{errors.daily_rate}</span>}
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <Label className="form-label">{t('Year Of First Immatriculation')}</Label>
                                            <Input
                                                type="number"
                                                name="year_of_ﬁrst_immatriculation"
                                                placeholder={t('Year Of First Immatriculation')}
                                                value={data['year_of_ﬁrst_immatriculation']}
                                                onChange={(e) => setData('year_of_ﬁrst_immatriculation', e.target.value)}
                                            />
                                            {errors['year_of_ﬁrst_immatriculation'] && (
                                                <span className="text-danger">{errors['year_of_ﬁrst_immatriculation']}</span>
                                            )}
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <Label className="form-label">{t('Gearbox')}</Label>
                                            <select
                                                name="gearbox"
                                                className="form-control"
                                                value={data.gearbox}
                                                onChange={(e) => setData('gearbox', e.target.value)}
                                            >
                                                {Object.entries(gearbox).map(([key, label]) => (
                                                    <option key={key} value={key}>{label}</option>
                                                ))}
                                            </select>
                                            {errors.gearbox && <span className="text-danger">{errors.gearbox}</span>}
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <Label className="form-label">{t('Fuel Type')}</Label>
                                            <select
                                                name="fuel_type"
                                                className="form-control"
                                                value={data.fuel_type}
                                                onChange={(e) => setData('fuel_type', e.target.value)}
                                            >
                                                {Object.entries(fuelType).map(([key, label]) => (
                                                    <option key={key} value={key}>{label}</option>
                                                ))}
                                            </select>
                                            {errors.fuel_type && <span className="text-danger">{errors.fuel_type}</span>}
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <Label className="form-label">{t('Number Of Seats')}</Label>
                                            <Input
                                                type="number"
                                                name="number_of_seats"
                                                placeholder={t('Number Of Seats')}
                                                value={data.number_of_seats}
                                                onChange={(e) => setData('number_of_seats', e.target.value)}
                                            />
                                            {errors.number_of_seats && <span className="text-danger">{errors.number_of_seats}</span>}
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <Label className="form-label">{t('Kilometers')}</Label>
                                            <Input
                                                type="number"
                                                name="kilometers"
                                                placeholder={t('Kilometers')}
                                                value={data.kilometers}
                                                onChange={(e) => setData('kilometers', e.target.value)}
                                            />
                                            {errors.kilometers && <span className="text-danger">{errors.kilometers}</span>}
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <Label className="form-label">{t('Option')}</Label>
                                            <select
                                                multiple
                                                name="option[]"
                                                className="form-control"
                                                value={data.option.map(String)}
                                                onChange={(e) =>
                                                    setData(
                                                        'option',
                                                        Array.from(e.target.selectedOptions, (o) => o.value)
                                                    )
                                                }
                                            >
                                                {Object.entries(option).map(([key, label]) => (
                                                    <option key={key} value={key}>{label}</option>
                                                ))}
                                            </select>
                                            {errors.option && <span className="text-danger">{errors.option}</span>}
                                        </div>
                                    </div>
                                    <div className="col-md-12">
                                        <div className="form-group">
                                            <Label className="form-label">{t('Notes')}</Label>
                                            <Textarea
                                                name="notes"
                                                placeholder={t('Notes')}
                                                value={data.notes}
                                                onChange={(e) => setData('notes', e.target.value)}
                                            />
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <Label className="form-label">{t('Document')}</Label>
                                            <Input
                                                type="file"
                                                name="document"
                                                onChange={(e) => setData('document', e.target.files[0] ?? null)}
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div className="text-end">
                                    <Button type="submit" disabled={processing}>{t('Create')}</Button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
