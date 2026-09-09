import { router, usePage } from '@inertiajs/react';
import { useForm } from 'react-hook-form';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { SearchableSelect } from '@/components/ui/searchable-select';
import { Switch } from '@/components/ui/switch';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';
import { confirmBlacklist } from '@/lib/blacklist';
import { BlacklistNotice } from '@/components/BlacklistNotice';
import FieldError from '@/components/FieldError';
import { fieldA11y } from '@/lib/fieldA11y';

function RentalAgreementCreate({ vehicles, drivers, statuses, defaultTerms }) {
    const t = useTranslation();
    const { errors: serverErrors } = usePage().props;
    const confirm = useConfirm();
    const { register, handleSubmit, setValue, watch, formState: { isSubmitting } } = useForm({
        defaultValues: {
            driver: '',
            driver2: '',
            vehicle: '',
            rental_start_date: '',
            rental_start_time: '',
            rental_end_date: '',
            rental_end_time: '',
            rental_duration: '',
            status: statuses?.[0]?.value ?? '',
            terms_condition: defaultTerms ?? '',
            description: '',
            create_booking: false,
        },
    });

    async function onSubmit(data) {
        // Date-order guard (BAN-259): block end-before-start with a modal,
        // mirroring the server's after_or_equal rule (date-level; ISO date
        // strings compare chronologically).
        if (data.rental_start_date && data.rental_end_date
            && data.rental_end_date < data.rental_start_date) {
            await confirm({
                title: t('Invalid dates'),
                description: t('The rental end date cannot be before the start date.'),
                confirmText: t('OK'),
            });
            return;
        }

        const driver2 = data.driver2 === 'none' ? '' : data.driver2;

        // Blacklist warning (BAN-252): check both drivers; let the owner decide.
        const { proceed, acknowledge } = await confirmBlacklist(drivers, [data.driver, driver2], confirm, t);
        if (!proceed) return;

        router.post(route('rental-agreement.store'), {
            ...data,
            driver2,
            create_booking: data.create_booking ? 1 : 0,
            acknowledge_blacklist: acknowledge ? 1 : 0,
        });
    }

    return (
        <form onSubmit={handleSubmit(onSubmit)}>
            <div className="space-y-6 p-6">
                <Card>
                    <CardContent className="pt-6">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">

                            <div className="space-y-1">
                                <Label>{t('Driver')}</Label>
                                <SearchableSelect
                                    options={drivers.map((d) => ({ value: d.id, label: d.name }))}
                                    value={watch('driver')}
                                    onChange={(v) => setValue('driver', v)}
                                    placeholder={t('Select Driver')}
                                    searchPlaceholder={t('Search driver…')}
                                    ariaLabel={t('Driver')}
                                    {...fieldA11y(serverErrors, 'driver')}
                                />
                                <FieldError name="driver" errors={serverErrors} />
                                <BlacklistNotice drivers={drivers} selectedIds={[watch('driver')]} />
                            </div>

                            <div className="space-y-1">
                                <Label>{t('Driver 2 (optional)')}</Label>
                                <SearchableSelect
                                    options={[{ value: 'none', label: t('— None —') }, ...drivers.map((d) => ({ value: d.id, label: d.name }))]}
                                    value={watch('driver2') || 'none'}
                                    onChange={(v) => setValue('driver2', v)}
                                    placeholder={t('— None —')}
                                    searchPlaceholder={t('Search driver…')}
                                    ariaLabel={t('Driver 2 (optional)')}
                                />
                                <BlacklistNotice drivers={drivers} selectedIds={[watch('driver2')]} />
                            </div>

                            <div className="space-y-1">
                                <Label>{t('Vehicle')}</Label>
                                <SearchableSelect
                                    options={vehicles.map((v) => ({ value: v.id, label: v.label }))}
                                    value={watch('vehicle')}
                                    onChange={(v) => setValue('vehicle', v)}
                                    placeholder={t('Select Vehicle')}
                                    searchPlaceholder={t('Search vehicle…')}
                                    ariaLabel={t('Vehicle')}
                                    {...fieldA11y(serverErrors, 'vehicle')}
                                />
                                <FieldError name="vehicle" errors={serverErrors} />
                            </div>

                            <div className="space-y-1">
                                <Label>{t('Rental Start Date & Time')}</Label>
                                <div className="flex gap-2">
                                    <Input type="date" {...register('rental_start_date', { required: true })} />
                                    <Input type="time" {...register('rental_start_time', { required: true })} />
                                </div>
                            </div>

                            <div className="space-y-1">
                                <Label>{t('Rental End Date & Time')}</Label>
                                <div className="flex gap-2">
                                    <Input type="date" {...register('rental_end_date', { required: true })} />
                                    <Input type="time" {...register('rental_end_time', { required: true })} />
                                </div>
                            </div>

                            <div className="space-y-1">
                                <Label htmlFor="rental_duration">{t('Rental Duration (Days)')}</Label>
                                <Input id="rental_duration" type="number" placeholder={t('Enter rental duration')} {...register('rental_duration', { required: true })} />
                            </div>

                            <div className="space-y-1">
                                <Label>{t('Status')}</Label>
                                <Select defaultValue={statuses?.[0]?.value} onValueChange={(v) => setValue('status', v)}>
                                    <SelectTrigger><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        {statuses?.map((s) => (
                                            <SelectItem key={s.value} value={s.value}>{s.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="flex items-center gap-3 pt-4">
                                <Switch
                                    id="create_booking"
                                    onCheckedChange={(v) => setValue('create_booking', v)}
                                />
                                <Label htmlFor="create_booking" className="cursor-pointer">{t('Also create a Booking')}</Label>
                            </div>

                            <div className="space-y-1 md:col-span-2">
                                <Label htmlFor="terms_condition">{t('Terms & Conditions')}</Label>
                                <Textarea id="terms_condition" rows={6} {...register('terms_condition')} />
                            </div>

                            <div className="space-y-1 md:col-span-2">
                                <Label htmlFor="description">{t('Description')}</Label>
                                <Textarea id="description" rows={4} placeholder={t('Enter description')} {...register('description')} />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div className="flex justify-end">
                    <Button type="submit" disabled={isSubmitting}>{t('Create')}</Button>
                </div>
            </div>
        </form>
    );
}

RentalAgreementCreate.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Rental Agreements', href: route('rental-agreement.index') },
        { label: 'Create' },
    ]}>{page}</AdminLayout>
);
export default RentalAgreementCreate;
