import { z } from 'zod';
import { useRef } from 'react';
import { Link, usePage } from '@inertiajs/react';
import ReCAPTCHA from 'react-google-recaptcha';
import { useZodForm } from '@/hooks/useZodForm';
import { Button }   from '@/components/ui/button';
import { Input }    from '@/components/ui/input';
import { Label }    from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import PublicLayout from '@/Layouts/PublicLayout';

const loginSchema = z.object({
    email:                  z.string().email('Enter a valid email address'),
    password:               z.string().min(1, 'Password is required'),
    remember:               z.boolean().optional(),
    'g-recaptcha-response': z.string().optional(),
});

function Login({ status }) {
    const { recaptcha } = usePage().props;
    const captchaRef = useRef(null);

    const { form, submit } = useZodForm(loginSchema, {
        defaultValues: { email: '', password: '', remember: false, 'g-recaptcha-response': '' },
    });
    const { register, setValue, watch, formState: { errors, isSubmitting } } = form;

    return (
        <div className="flex min-h-[80vh] items-center justify-center px-4">
            <Card className="w-full max-w-md">
                <CardHeader className="text-center">
                    <CardTitle className="text-2xl">Sign in</CardTitle>
                    <CardDescription>Enter your credentials to access the dashboard</CardDescription>
                </CardHeader>

                <CardContent>
                    {status && (
                        <div className="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
                            {status}
                        </div>
                    )}

                    <form onSubmit={submit('post', route('login'), { onError: () => captchaRef.current?.reset() })} className="space-y-4">
                        <div className="space-y-1.5">
                            <Label htmlFor="email">Email</Label>
                            <Input
                                id="email"
                                type="email"
                                autoComplete="email"
                                autoFocus
                                {...register('email')}
                            />
                            {errors.email && <p className="text-sm text-destructive">{errors.email.message}</p>}
                        </div>

                        <div className="space-y-1.5">
                            <div className="flex items-center justify-between">
                                <Label htmlFor="password">Password</Label>
                                <Link
                                    href={route('password.request')}
                                    className="text-xs text-muted-foreground hover:text-foreground"
                                >
                                    Forgot password?
                                </Link>
                            </div>
                            <Input
                                id="password"
                                type="password"
                                autoComplete="current-password"
                                {...register('password')}
                            />
                            {errors.password && <p className="text-sm text-destructive">{errors.password.message}</p>}
                        </div>

                        <div className="flex items-center gap-2">
                            <Checkbox
                                id="remember"
                                checked={watch('remember')}
                                onCheckedChange={(v) => setValue('remember', !!v)}
                            />
                            <Label htmlFor="remember" className="text-sm font-normal cursor-pointer">
                                Remember me
                            </Label>
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
                            {isSubmitting ? 'Signing in…' : 'Sign in'}
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

Login.layout = (page) => <PublicLayout>{page}</PublicLayout>;
export default Login;
