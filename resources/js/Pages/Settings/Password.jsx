import { z } from 'zod';
import { useZodForm } from '@/hooks/useZodForm';
import { Button } from '@/components/ui/button';
import { Input }  from '@/components/ui/input';
import { Label }  from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import FieldError from '@/components/FieldError';
import { fieldA11y } from '@/lib/fieldA11y';

const schema = z.object({
    current_password: z.string().min(1, 'Current password is required'),
    new_password:     z.string().min(6, 'At least 6 characters'),
    confirm_password: z.string().min(1, 'Please confirm your new password'),
}).refine((d) => d.new_password === d.confirm_password, {
    message: "Passwords don't match",
    path: ['confirm_password'],
});

function Password() {
    const { form, submit } = useZodForm(schema, {
        defaultValues: { current_password: '', new_password: '', confirm_password: '' },
    });
    const { register, reset, formState: { errors, isSubmitting } } = form;
    const t = useTranslation();

    return (
        <div className="max-w-2xl space-y-6 p-6">
            <div>
                <h1 className="text-3xl font-bold tracking-tight">{t('Password')}</h1>
                <p className="text-sm text-muted-foreground">{t('Update the password on your account.')}</p>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>{t('Change password')}</CardTitle>
                    <CardDescription>{t('You will be signed out of other sessions after saving.')}</CardDescription>
                </CardHeader>
                <CardContent>
                    <form
                        onSubmit={submit('post', route('setting.password'), {
                            onSuccess: () => reset(),
                        })}
                        className="space-y-4"
                    >
                        <div className="space-y-1.5">
                            <Label htmlFor="current_password">{t('Current password')}</Label>
                            <Input id="current_password" type="password" autoComplete="current-password" {...register('current_password')} {...fieldA11y(errors, 'current_password')} />
                            <FieldError name="current_password" errors={errors} />
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="new_password">{t('New password')}</Label>
                            <Input id="new_password" type="password" autoComplete="new-password" {...register('new_password')} {...fieldA11y(errors, 'new_password')} />
                            <FieldError name="new_password" errors={errors} />
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="confirm_password">{t('Confirm new password')}</Label>
                            <Input id="confirm_password" type="password" autoComplete="new-password" {...register('confirm_password')} {...fieldA11y(errors, 'confirm_password')} />
                            <FieldError name="confirm_password" errors={errors} />
                        </div>

                        <Button type="submit" disabled={isSubmitting}>
                            {isSubmitting ? t('Saving…') : t('Save password')}
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

Password.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Settings' }, { label: 'Password' }]}>
        {page}
    </AdminLayout>
);
export default Password;
