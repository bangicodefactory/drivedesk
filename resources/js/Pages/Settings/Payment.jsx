import { z } from 'zod';
import { useZodForm } from '@/hooks/useZodForm';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Separator } from '@/components/ui/separator';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import FieldError from '@/components/FieldError';
import { fieldA11y } from '@/lib/fieldA11y';

const schema = z.object({
    CURRENCY_SYMBOL:        z.string().min(1, 'Required'),
    CURRENCY:               z.string().min(1, 'Required'),
    stripe_payment:         z.string().optional(),
    stripe_key:             z.string().optional(),
    stripe_secret:          z.string().optional(),
    paypal_payment:         z.string().optional(),
    paypal_mode:            z.enum(['sandbox', 'live']).optional(),
    paypal_client_id:       z.string().optional(),
    paypal_secret_key:      z.string().optional(),
    bank_transfer_payment:  z.string().optional(),
    bank_name:              z.string().optional(),
    bank_holder_name:       z.string().optional(),
    bank_account_number:    z.string().optional(),
    bank_ifsc_code:         z.string().optional(),
    bank_other_details:     z.string().optional(),
    flutterwave_payment:    z.string().optional(),
    flutterwave_public_key: z.string().optional(),
    flutterwave_secret_key: z.string().optional(),
});

function ToggleRow({ label, checked, onChange }) {
    return (
        <div className="flex items-center gap-3 py-2">
            <Switch checked={checked} onCheckedChange={onChange} />
            <Label className="cursor-pointer">{label}</Label>
        </div>
    );
}

function Payment({ settings }) {
    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            CURRENCY_SYMBOL:        settings?.CURRENCY_SYMBOL        ?? '',
            CURRENCY:               settings?.CURRENCY               ?? '',
            stripe_key:             settings?.STRIPE_KEY             ?? '',
            stripe_secret:          settings?.STRIPE_SECRET          ?? '',
            paypal_mode:            settings?.paypal_mode            ?? 'sandbox',
            paypal_client_id:       settings?.paypal_client_id       ?? '',
            paypal_secret_key:      settings?.paypal_secret_key      ?? '',
            bank_name:              settings?.bank_name              ?? '',
            bank_holder_name:       settings?.bank_holder_name       ?? '',
            bank_account_number:    settings?.bank_account_number    ?? '',
            bank_ifsc_code:         settings?.bank_ifsc_code         ?? '',
            bank_other_details:     settings?.bank_other_details     ?? '',
            flutterwave_public_key: settings?.flutterwave_public_key ?? '',
            flutterwave_secret_key: settings?.flutterwave_secret_key ?? '',
        },
    });
    const { register, watch, setValue, formState: { errors, isSubmitting } } = form;
    const t = useTranslation();

    const stripeOn      = watch('stripe_payment') === 'on';
    const paypalOn      = watch('paypal_payment') === 'on';
    const bankOn        = watch('bank_transfer_payment') === 'on';
    const flutterwaveOn = watch('flutterwave_payment') === 'on';

    function initToggle(key, settingsKey) {
        const initial = settings?.[settingsKey] === 'on';
        if (initial && watch(key) === undefined) setValue(key, 'on');
        return initial;
    }

    return (
        <div className="max-w-2xl space-y-6 p-6">
            <div>
                <h1 className="text-3xl font-bold tracking-tight">{t('Payment Settings')}</h1>
                <p className="text-sm text-muted-foreground">{t('Currency and payment gateway configuration.')}</p>
            </div>

            <Card>
                <CardContent className="pt-6">
                    <form onSubmit={submit('post', route('setting.payment'))} className="space-y-6">

                        {/* Currency */}
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-1">
                                <Label htmlFor="CURRENCY_SYMBOL">{t('Currency Icon')}</Label>
                                <Input id="CURRENCY_SYMBOL" {...register('CURRENCY_SYMBOL')} {...fieldA11y(errors, 'CURRENCY_SYMBOL')} />
                                <FieldError name="CURRENCY_SYMBOL" errors={errors} />
                            </div>
                            <div className="space-y-1">
                                <Label htmlFor="CURRENCY">{t('Currency Code')}</Label>
                                <Input id="CURRENCY" {...register('CURRENCY')} {...fieldA11y(errors, 'CURRENCY')} />
                                <FieldError name="CURRENCY" errors={errors} />
                            </div>
                        </div>

                        <Separator />

                        {/* Stripe */}
                        <div className="space-y-3">
                            <ToggleRow
                                label={t('Stripe Payment')}
                                checked={settings?.STRIPE_PAYMENT === 'on' ? !stripeOn : stripeOn}
                                onChange={(v) => setValue('stripe_payment', v ? 'on' : 'off')}
                            />
                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-1">
                                    <Label htmlFor="stripe_key">{t('Account Key')}</Label>
                                    <Input id="stripe_key" autoComplete="off" {...register('stripe_key')} />
                                </div>
                                <div className="space-y-1">
                                    <Label htmlFor="stripe_secret">{t('Account Secret Key')}</Label>
                                    <Input id="stripe_secret" autoComplete="off" {...register('stripe_secret')} />
                                </div>
                            </div>
                        </div>

                        <Separator />

                        {/* PayPal */}
                        <div className="space-y-3">
                            <ToggleRow
                                label={t('PayPal Payment')}
                                checked={settings?.paypal_payment === 'on' ? !paypalOn : paypalOn}
                                onChange={(v) => setValue('paypal_payment', v ? 'on' : 'off')}
                            />
                            <div className="flex gap-4 items-center">
                                <Label>{t('Account Mode')}</Label>
                                {['sandbox', 'live'].map((mode) => (
                                    <label key={mode} className="flex items-center gap-1.5 cursor-pointer">
                                        <input
                                            type="radio"
                                            value={mode}
                                            {...register('paypal_mode')}
                                            defaultChecked={settings?.paypal_mode === mode}
                                        />
                                        <span className="text-sm capitalize">{mode}</span>
                                    </label>
                                ))}
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-1">
                                    <Label htmlFor="paypal_client_id">{t('Client ID')}</Label>
                                    <Input id="paypal_client_id" autoComplete="off" {...register('paypal_client_id')} />
                                </div>
                                <div className="space-y-1">
                                    <Label htmlFor="paypal_secret_key">{t('Secret Key')}</Label>
                                    <Input id="paypal_secret_key" autoComplete="off" {...register('paypal_secret_key')} />
                                </div>
                            </div>
                        </div>

                        <Separator />

                        {/* Bank Transfer */}
                        <div className="space-y-3">
                            <ToggleRow
                                label={t('Bank Transfer Payment')}
                                checked={settings?.bank_transfer_payment === 'on' ? !bankOn : bankOn}
                                onChange={(v) => setValue('bank_transfer_payment', v ? 'on' : 'off')}
                            />
                            <div className="grid grid-cols-2 gap-4">
                                {[
                                    ['bank_name',           'Bank Name'],
                                    ['bank_holder_name',    'Account Holder'],
                                    ['bank_account_number', 'Account Number'],
                                    ['bank_ifsc_code',      'IFSC Code'],
                                ].map(([key, label]) => (
                                    <div key={key} className="space-y-1">
                                        <Label htmlFor={key}>{t(label)}</Label>
                                        <Input id={key} {...register(key)} />
                                    </div>
                                ))}
                                <div className="space-y-1 col-span-2">
                                    <Label htmlFor="bank_other_details">{t('Other Details')}</Label>
                                    <Textarea id="bank_other_details" rows={2} {...register('bank_other_details')} />
                                </div>
                            </div>
                        </div>

                        <Separator />

                        {/* Flutterwave */}
                        <div className="space-y-3">
                            <ToggleRow
                                label={t('Flutterwave Payment')}
                                checked={settings?.flutterwave_payment === 'on' ? !flutterwaveOn : flutterwaveOn}
                                onChange={(v) => setValue('flutterwave_payment', v ? 'on' : 'off')}
                            />
                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-1">
                                    <Label htmlFor="flutterwave_public_key">{t('Public Key')}</Label>
                                    <Input id="flutterwave_public_key" autoComplete="off" {...register('flutterwave_public_key')} />
                                </div>
                                <div className="space-y-1">
                                    <Label htmlFor="flutterwave_secret_key">{t('Secret Key')}</Label>
                                    <Input id="flutterwave_secret_key" autoComplete="off" {...register('flutterwave_secret_key')} />
                                </div>
                            </div>
                        </div>

                        <Button type="submit" disabled={isSubmitting}>
                            {isSubmitting ? t('Saving…') : t('Save')}
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

Payment.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Settings' }, { label: 'Payment' }]}>{page}</AdminLayout>
);
export default Payment;
