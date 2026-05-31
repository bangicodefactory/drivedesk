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
                    <CardTitle className="text-2xl">Create account</CardTitle>
                    <CardDescription>Fill in the details below to get started</CardDescription>
                </CardHeader>

                <CardContent>
                    <form onSubmit={submit('post', route('register'))} className="space-y-4">
                        <div className="space-y-1.5">
                            <Label htmlFor="name">Full name</Label>
                            <Input id="name" autoComplete="name" autoFocus {...register('name')} />
                            {errors.name && <p className="text-sm text-destructive">{errors.name.message}</p>}
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="email">Email</Label>
                            <Input id="email" type="email" autoComplete="email" {...register('email')} />
                            {errors.email && <p className="text-sm text-destructive">{errors.email.message}</p>}
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="company_name">Company name</Label>
                            <Input id="company_name" {...register('company_name')} />
                            {errors.company_name && <p className="text-sm text-destructive">{errors.company_name.message}</p>}
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="city">City</Label>
                            <Input id="city" {...register('city')} />
                            {errors.city && <p className="text-sm text-destructive">{errors.city.message}</p>}
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="password">Password</Label>
                            <Input id="password" type="password" autoComplete="new-password" {...register('password')} />
                            {errors.password && <p className="text-sm text-destructive">{errors.password.message}</p>}
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="password_confirmation">Confirm password</Label>
                            <Input id="password_confirmation" type="password" autoComplete="new-password" {...register('password_confirmation')} />
                            {errors.password_confirmation && <p className="text-sm text-destructive">{errors.password_confirmation.message}</p>}
                        </div>

                        {recaptcha?.enabled && (
                            <div>
                                <ReCAPTCHA
                                    ref={captchaRef}
                                    sitekey={recaptcha.siteKey}
                                    onChange={(token) => setValue('g-recaptcha-response', token ?? '')}
                                    onExpired={() => setValue('g-recaptcha-response', '')}
                                />
                                {errors['g-recaptcha-response'] && (
                                    <p className="text-sm text-destructive mt-1">{errors['g-recaptcha-response'].message}</p>
                                )}
                            </div>
                        )}

                        <Button type="submit" className="w-full" disabled={isSubmitting}>
                            {isSubmitting ? 'Creating account…' : 'Create account'}
                        </Button>

                        <p className="text-center text-sm text-muted-foreground">
                            Already have an account?{' '}
                            <Link href={route('login')} className="underline hover:text-foreground">
                                Sign in
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
