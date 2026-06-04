import { z } from 'zod';
import { useZodForm } from '@/hooks/useZodForm';
import { Button } from '@/components/ui/button';
import { Input }  from '@/components/ui/input';
import { Label }  from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import PublicLayout from '@/Layouts/PublicLayout';
import { useTranslation } from '@/hooks/useTranslation';

const resetSchema = z.object({
    token:                 z.string(),
    email:                 z.string().email('Enter a valid email address'),
    password:              z.string().min(8, 'At least 8 characters'),
    password_confirmation: z.string().min(1, 'Please confirm your password'),
}).refine((d) => d.password === d.password_confirmation, {
    message: "Passwords don't match",
    path: ['password_confirmation'],
});

function ResetPassword({ token, email }) {
    const t = useTranslation();
    const { form, submit } = useZodForm(resetSchema, {
        defaultValues: { token: token ?? '', email: email ?? '', password: '', password_confirmation: '' },
    });
    const { register, formState: { errors, isSubmitting } } = form;

    return (
        <div className="flex min-h-[80vh] items-center justify-center px-4">
            <Card className="w-full max-w-md">
                <CardHeader className="text-center">
                    <CardTitle className="text-2xl">{t('Reset password')}</CardTitle>
                    <CardDescription>{t('Enter your new password below.')}</CardDescription>
                </CardHeader>

                <CardContent>
                    <form onSubmit={submit('post', route('password.update'))} className="space-y-4">
                        {/* Hidden field — server errors on token still surface here */}
                        <input type="hidden" {...register('token')} />
                        {errors.token && (
                            <p className="text-sm text-destructive" role="alert">
                                {errors.token.message}
                            </p>
                        )}

                        <div className="space-y-1.5">
                            <Label htmlFor="email">{t('Email')}</Label>
                            <Input
                                id="email"
                                type="email"
                                autoComplete="email"
                                {...register('email')}
                            />
                            {errors.email && <p className="text-sm text-destructive">{errors.email.message}</p>}
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="password">{t('New password')}</Label>
                            <Input
                                id="password"
                                type="password"
                                autoComplete="new-password"
                                autoFocus
                                {...register('password')}
                            />
                            {errors.password && <p className="text-sm text-destructive">{errors.password.message}</p>}
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="password_confirmation">{t('Confirm new password')}</Label>
                            <Input
                                id="password_confirmation"
                                type="password"
                                autoComplete="new-password"
                                {...register('password_confirmation')}
                            />
                            {errors.password_confirmation && <p className="text-sm text-destructive">{errors.password_confirmation.message}</p>}
                        </div>

                        <Button type="submit" className="w-full" disabled={isSubmitting}>
                            {isSubmitting ? t('Resetting…') : t('Reset password')}
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

ResetPassword.layout = (page) => <PublicLayout>{page}</PublicLayout>;
export default ResetPassword;
