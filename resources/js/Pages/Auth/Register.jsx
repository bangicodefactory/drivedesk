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

const registerSchema = z.object({
    name:                   z.string().min(1, 'Name is required').max(255),
    email:                  z.string().email('Enter a valid email address'),
    company_name:           z.string().min(1, 'Company name is required').max(255),
    city:                   z.string().min(1, 'City is required').max(255),
    password:               z.string().min(8, 'At least 8 characters'),
    password_confirmation:  z.string().min(1, 'Please confirm your password'),
    'g-recaptcha-response': z.string().optional(),
}).refine((d) => d.password === d.password_confirmation, {
    message: "Passwords don't match",
    path: ['password_confirmation'],
});

function Register() {
    const t = useTranslation();
    const { recaptcha } = usePage().props;
    const captchaRef = useRef(null);

    const { form, submit } = useZodForm(registerSchema, {
        defaultValues: {
            name: '', email: '', company_name: '', city: '',
            password: '', password_confirmation: '', 'g-recaptcha-response': '',
        },
    });
    const { register, setValue, formState: { errors, isSubmitting } } = form;

    return (
        <div className="flex min-h-[80vh] items-center justify-center px-4 py-8">
            <Card className="w-full max-w-md">
                <CardHeader className="text-center">
                    <CardTitle className="text-2xl">{t('Create account')}</CardTitle>
                    <CardDescription>{t('Fill in the details below to get started')}</CardDescription>
                </CardHeader>

                <CardContent>
                    <form onSubmit={submit('post', route('register'), { onError: () => captchaRef.current?.reset() })} className="space-y-4">
                        <div className="space-y-1.5">
                            <Label htmlFor="name">{t('Full name')}</Label>
                            <Input id="name" autoComplete="name" autoFocus {...register('name')} {...fieldA11y(errors, 'name')} />
                            <FieldError name="name" errors={errors} />
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="email">{t('Email')}</Label>
                            <Input id="email" type="email" autoComplete="email" {...register('email')} {...fieldA11y(errors, 'email')} />
                            <FieldError name="email" errors={errors} />
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="company_name">{t('Company name')}</Label>
                            <Input id="company_name" {...register('company_name')} {...fieldA11y(errors, 'company_name')} />
                            <FieldError name="company_name" errors={errors} />
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="city">{t('City')}</Label>
                            <Input id="city" {...register('city')} {...fieldA11y(errors, 'city')} />
                            <FieldError name="city" errors={errors} />
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="password">{t('Password')}</Label>
                            <Input id="password" type="password" autoComplete="new-password" {...register('password')} {...fieldA11y(errors, 'password')} />
                            <FieldError name="password" errors={errors} />
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="password_confirmation">{t('Confirm password')}</Label>
                            <Input id="password_confirmation" type="password" autoComplete="new-password" {...register('password_confirmation')} {...fieldA11y(errors, 'password_confirmation')} />
                            <FieldError name="password_confirmation" errors={errors} />
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
                            {isSubmitting ? t('Creating account…') : t('Create account')}
                        </Button>

                        <p className="text-center text-sm text-muted-foreground">
                            {t('Already have an account?')}{' '}
                            <Link href={route('login')} className="underline hover:text-foreground">
                                {t('Sign in')}
                            </Link>
                        </p>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

Register.layout = (page) => <PublicLayout>{page}</PublicLayout>;
export default Register;
