import { z } from 'zod';
import { useRef } from 'react';
import { Link, usePage } from '@inertiajs/react';
import ReCAPTCHA from 'react-google-recaptcha';
import { useZodForm } from '@/hooks/useZodForm';
import { Button } from '@/components/ui/button';
import { Input }  from '@/components/ui/input';
import { Label }  from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import PublicLayout from '@/Layouts/PublicLayout';
import { useTranslation } from '@/hooks/useTranslation';
import FieldError from '@/components/FieldError';
import { fieldA11y } from '@/lib/fieldA11y';

const forgotSchema = z.object({
    email:                  z.string().email('Enter a valid email address'),
    'g-recaptcha-response': z.string().optional(),
});

function ForgotPassword({ status }) {
    const t = useTranslation();
    const { recaptcha } = usePage().props;
    const captchaRef = useRef(null);

    const { form, submit } = useZodForm(forgotSchema, {
        defaultValues: { email: '', 'g-recaptcha-response': '' },
    });
    const { register, setValue, formState: { errors, isSubmitting } } = form;

    return (
        <div className="flex min-h-[80vh] items-center justify-center px-4">
            <Card className="w-full max-w-md">
                <CardHeader className="text-center">
                    <CardTitle className="text-2xl">{t('Forgot password?')}</CardTitle>
                    <CardDescription>
                        {t("Enter your email and we'll send a reset link.")}
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    {status && (
                        <div className="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
                            {status}
                        </div>
                    )}

                    <form onSubmit={submit('post', route('password.email'), { onError: () => captchaRef.current?.reset() })} className="space-y-4">
                        <div className="space-y-1.5">
                            <Label htmlFor="email">{t('Email')}</Label>
                            <Input
                                id="email"
                                type="email"
                                autoComplete="email"
                                autoFocus
                                {...register('email')}
                                {...fieldA11y(errors, 'email')}
                            />
                            <FieldError name="email" errors={errors} />
                        </div>

                        {recaptcha?.enabled && (
                            <div>
                                <ReCAPTCHA
                                    ref={captchaRef}
                                    sitekey={recaptcha.siteKey}
                                    onChange={(token) => setValue('g-recaptcha-response', token ?? '')}
                                    onExpired={() => setValue('g-recaptcha-response', '')}
                                />
                                <FieldError name="g-recaptcha-response" errors={errors} className="mt-1" />
                            </div>
                        )}

                        <Button type="submit" className="w-full" disabled={isSubmitting}>
                            {isSubmitting ? t('Sending…') : t('Send reset link')}
                        </Button>

                        <p className="text-center text-sm text-muted-foreground">
                            <Link href={route('login')} className="underline hover:text-foreground">
                                {t('Back to sign in')}
                            </Link>
                        </p>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

ForgotPassword.layout = (page) => <PublicLayout>{page}</PublicLayout>;
export default ForgotPassword;
