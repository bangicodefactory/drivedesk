import { z } from 'zod';
import { useZodForm } from '@/hooks/useZodForm';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import FieldError from '@/components/FieldError';
import { fieldA11y } from '@/lib/fieldA11y';

const schema = z.object({
    google_recaptcha: z.string().optional(),
    recaptcha_key:    z.string().min(1, 'Required'),
    recaptcha_secret: z.string().min(1, 'Required'),
});

function Recaptcha({ settings }) {
    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            recaptcha_key:    settings?.recaptcha_key    ?? '',
            recaptcha_secret: settings?.recaptcha_secret ?? '',
        },
    });
    const { register, watch, setValue, formState: { errors, isSubmitting } } = form;
    const t = useTranslation();

    const enabled = watch('google_recaptcha') === 'on'
        ? true
        : settings?.google_recaptcha === 'on';

    return (
        <div className="max-w-2xl space-y-6 p-6">
            <div>
                <h1 className="text-3xl font-bold tracking-tight">{t('Google reCAPTCHA')}</h1>
                <p className="text-sm text-muted-foreground">{t('Protect public forms from spam bots.')}</p>
            </div>

            <Card>
                <CardHeader><CardTitle>{t('reCAPTCHA Settings')}</CardTitle></CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('setting.google.recaptcha'))} className="space-y-4">
                        <div className="flex items-center gap-3">
                            <Switch
                                defaultChecked={settings?.google_recaptcha === 'on'}
                                onCheckedChange={(v) => setValue('google_recaptcha', v ? 'on' : 'off')}
                            />
                            <Label className="cursor-pointer">{t('Enable Google reCAPTCHA')}</Label>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-1">
                                <Label htmlFor="recaptcha_key">{t('Recaptcha Key')}</Label>
                                <Input id="recaptcha_key" autoComplete="off" {...register('recaptcha_key')} {...fieldA11y(errors, 'recaptcha_key')} />
                                <FieldError name="recaptcha_key" errors={errors} />
                            </div>
                            <div className="space-y-1">
                                <Label htmlFor="recaptcha_secret">{t('Recaptcha Secret')}</Label>
                                <Input id="recaptcha_secret" autoComplete="off" {...register('recaptcha_secret')} {...fieldA11y(errors, 'recaptcha_secret')} />
                                <FieldError name="recaptcha_secret" errors={errors} />
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

Recaptcha.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Settings' }, { label: 'reCAPTCHA' }]}>{page}</AdminLayout>
);
export default Recaptcha;
