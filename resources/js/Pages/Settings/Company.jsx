import { z } from 'zod';
import { useZodForm } from '@/hooks/useZodForm';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import FieldError from '@/components/FieldError';
import { fieldA11y } from '@/lib/fieldA11y';

const schema = z.object({
    company_name:                    z.string().min(1, 'Required'),
    company_email:                   z.string().email('Enter a valid email'),
    company_phone:                   z.string().min(1, 'Required'),
    company_address:                 z.string().min(1, 'Required'),
    patente:                         z.string().optional(),
    rc:                              z.string().optional(),
    if:                              z.string().optional(),
    ice:                             z.string().optional(),
    client_number_prefix:            z.string().optional(),
    driver_number_prefix:            z.string().optional(),
    vehicle_number_prefix:           z.string().optional(),
    booking_number_prefix:           z.string().optional(),
    rental_agreement_number_prefix:  z.string().optional(),
    CURRENCY_SYMBOL:                 z.string().min(1, 'Required'),
    CURRENCY:                        z.string().min(1, 'Required'),
    company_date_format:             z.string().optional(),
    company_time_format:             z.string().optional(),
    timezone:                        z.string().optional(),
});

const DATE_FORMATS = [
    { value: 'M j, Y',  label: new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) },
    { value: 'y-m-d',   label: new Date().toISOString().slice(2, 10) },
    { value: 'd-m-y',   label: (() => { const d = new Date(); return `${String(d.getDate()).padStart(2,'0')}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getFullYear()).slice(2)}`; })() },
    { value: 'm-d-y',   label: (() => { const d = new Date(); return `${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}-${String(d.getFullYear()).slice(2)}`; })() },
];

const TIME_FORMATS = [
    { value: 'H:i',   label: new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', hour12: false }) },
    { value: 'g:i A', label: new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }).toUpperCase() },
    { value: 'g:i a', label: new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }).toLowerCase() },
];

function Company({ settings, timezones }) {
    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            company_name:                   settings?.company_name                   ?? '',
            company_email:                  settings?.company_email                  ?? '',
            company_phone:                  settings?.company_phone                  ?? '',
            company_address:                settings?.company_address                ?? '',
            patente:                        settings?.patente                        ?? '',
            rc:                             settings?.rc                             ?? '',
            if:                             settings?.if                             ?? '',
            ice:                            settings?.ice                            ?? '',
            client_number_prefix:           settings?.client_number_prefix           ?? '',
            driver_number_prefix:           settings?.driver_number_prefix           ?? '',
            vehicle_number_prefix:          settings?.vehicle_number_prefix          ?? '',
            booking_number_prefix:          settings?.booking_number_prefix          ?? '',
            rental_agreement_number_prefix: settings?.rental_agreement_number_prefix ?? '',
            CURRENCY_SYMBOL:                settings?.CURRENCY_SYMBOL                ?? '',
            CURRENCY:                       settings?.CURRENCY                       ?? '',
            company_date_format:            settings?.company_date_format            ?? 'M j, Y',
            company_time_format:            settings?.company_time_format            ?? 'H:i',
            timezone:                       settings?.timezone                       ?? '',
        },
    });
    const { register, setValue, watch, formState: { errors, isSubmitting } } = form;
    const t = useTranslation();

    const tzList = timezones ? Object.entries(timezones) : [];

    return (
        <div className="space-y-6 p-6">
            <div>
                <h1 className="text-3xl font-bold tracking-tight">{t('Company Settings')}</h1>
                <p className="text-sm text-muted-foreground">{t('Company details, legal identifiers and system preferences.')}</p>
            </div>

            <Card>
                <CardHeader><CardTitle>{t('Company Info')}</CardTitle></CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('setting.company'))} className="space-y-6">

                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div className="space-y-1.5">
                                <Label htmlFor="company_name">{t('Name')}</Label>
                                <Input id="company_name" placeholder={t('Enter company name')} {...register('company_name')} {...fieldA11y(errors, 'company_name')} />
                                <FieldError name="company_name" errors={errors} />
                            </div>
                            <div className="space-y-1.5">
                                <Label htmlFor="company_email">{t('Email')}</Label>
                                <Input id="company_email" type="email" placeholder={t('Enter company email')} {...register('company_email')} {...fieldA11y(errors, 'company_email')} />
                                <FieldError name="company_email" errors={errors} />
                            </div>
                            <div className="space-y-1.5">
                                <Label htmlFor="company_phone">{t('Phone Number')}</Label>
                                <Input id="company_phone" placeholder={t('Enter company phone')} {...register('company_phone')} {...fieldA11y(errors, 'company_phone')} />
                                <FieldError name="company_phone" errors={errors} />
                            </div>
                            <div className="space-y-1.5">
                                <Label htmlFor="company_address">{t('Address')}</Label>
                                <Textarea id="company_address" rows={2} placeholder={t('Enter company address')} {...register('company_address')} {...fieldA11y(errors, 'company_address')} />
                                <FieldError name="company_address" errors={errors} />
                            </div>
                        </div>

                        <div>
                            <p className="text-sm font-medium text-muted-foreground mb-3">{t('Legal Identifiers')}</p>
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                {[
                                    ['patente', 'Patente', 'Enter patente'],
                                    ['rc',      'RC',      'Enter RC'],
                                    ['if',      'IF',      'Enter IF'],
                                    ['ice',     'ICE',     'Enter ICE'],
                                ].map(([key, label, placeholder]) => (
                                    <div key={key} className="space-y-1.5">
                                        <Label htmlFor={key}>{t(label)}</Label>
                                        <Input id={key} placeholder={t(placeholder)} {...register(key)} />
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div>
                            <p className="text-sm font-medium text-muted-foreground mb-3">{t('Number Prefixes')}</p>
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                {[
                                    ['client_number_prefix',           'Client Number Prefix',            'e.g. CLT-'],
                                    ['driver_number_prefix',           'Driver Number Prefix',            'e.g. DRV-'],
                                    ['vehicle_number_prefix',          'Vehicle Number Prefix',           'e.g. VEH-'],
                                    ['booking_number_prefix',          'Booking Number Prefix',           'e.g. BKG-'],
                                    ['rental_agreement_number_prefix', 'Rental Agreement Number Prefix',  'e.g. RA-'],
                                ].map(([key, label, placeholder]) => (
                                    <div key={key} className="space-y-1.5">
                                        <Label htmlFor={key}>{t(label)}</Label>
                                        <Input id={key} placeholder={t(placeholder)} {...register(key)} />
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div>
                            <p className="text-sm font-medium text-muted-foreground mb-3">{t('Currency')}</p>
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div className="space-y-1.5">
                                    <Label htmlFor="CURRENCY_SYMBOL">{t('Currency Icon')}</Label>
                                    <Input id="CURRENCY_SYMBOL" placeholder={t('e.g. €')} {...register('CURRENCY_SYMBOL')} {...fieldA11y(errors, 'CURRENCY_SYMBOL')} />
                                    <FieldError name="CURRENCY_SYMBOL" errors={errors} />
                                </div>
                                <div className="space-y-1.5">
                                    <Label htmlFor="CURRENCY">{t('Currency Code')}</Label>
                                    <Input id="CURRENCY" placeholder={t('e.g. EUR')} {...register('CURRENCY')} {...fieldA11y(errors, 'CURRENCY')} />
                                    <FieldError name="CURRENCY" errors={errors} />
                                </div>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div className="space-y-2">
                                <Label>{t('System Date Format')}</Label>
                                <RadioGroup
                                    value={watch('company_date_format')}
                                    onValueChange={(v) => setValue('company_date_format', v, { shouldDirty: true })}
                                    className="space-y-1.5"
                                >
                                    {DATE_FORMATS.map(({ value, label }) => {
                                        const id = `dfmt-${value.replace(/[^a-zA-Z0-9]/g, '')}`;
                                        return (
                                            <div key={value} className="flex items-center gap-2">
                                                <RadioGroupItem id={id} value={value} />
                                                <Label htmlFor={id} className="cursor-pointer text-sm font-normal">{label}</Label>
                                            </div>
                                        );
                                    })}
                                </RadioGroup>
                            </div>

                            <div className="space-y-2">
                                <Label>{t('System Time Format')}</Label>
                                <RadioGroup
                                    value={watch('company_time_format')}
                                    onValueChange={(v) => setValue('company_time_format', v, { shouldDirty: true })}
                                    className="space-y-1.5"
                                >
                                    {TIME_FORMATS.map(({ value, label }) => {
                                        const id = `tfmt-${value.replace(/[^a-zA-Z0-9]/g, '')}`;
                                        return (
                                            <div key={value} className="flex items-center gap-2">
                                                <RadioGroupItem id={id} value={value} />
                                                <Label htmlFor={id} className="cursor-pointer text-sm font-normal">{label}</Label>
                                            </div>
                                        );
                                    })}
                                </RadioGroup>
                            </div>
                        </div>

                        {tzList.length > 0 && (
                            <div className="space-y-1.5">
                                <Label>{t('Timezone')}</Label>
                                <Select
                                    defaultValue={settings?.timezone ?? ''}
                                    onValueChange={(v) => setValue('timezone', v)}
                                >
                                    <SelectTrigger><SelectValue placeholder={t('Select timezone')} /></SelectTrigger>
                                    <SelectContent>
                                        {tzList.map(([value, label]) => (
                                            <SelectItem key={value} value={value}>{label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        )}

                        <div className="flex justify-end">
                            <Button type="submit" disabled={isSubmitting}>
                                {isSubmitting ? t('Saving…') : t('Save')}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

Company.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Settings' }, { label: 'Company Settings' }]}>{page}</AdminLayout>
);
export default Company;
