import { z } from 'zod';
import { useRef, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import ReCAPTCHA from 'react-google-recaptcha';
import { Lock, Eye, EyeOff, ShieldCheck } from 'lucide-react';
import { useZodForm } from '@/hooks/useZodForm';
import { Button }   from '@/components/ui/button';
import { Input }    from '@/components/ui/input';
import { Label }    from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { useTranslation } from '@/hooks/useTranslation';

const loginSchema = z.object({
    email:                  z.string().email('Enter a valid work email address'),
    password:               z.string().min(1, 'Password is required'),
    remember:               z.boolean().optional(),
    'g-recaptcha-response': z.string().optional(),
});

function Login({ status }) {
    const t = useTranslation();
    const { recaptcha, branding } = usePage().props;
    const captchaRef = useRef(null);
    const [showPassword, setShowPassword] = useState(false);

    const { form, submit } = useZodForm(loginSchema, {
        defaultValues: { email: '', password: '', remember: false, 'g-recaptcha-response': '' },
    });
    const { register, setValue, watch, formState: { errors, isSubmitting } } = form;

    const appName = branding?.appName ?? 'RentCar';
    const year    = new Date().getFullYear();

    return (
        <div className="relative min-h-screen flex flex-col">
            {/* ── Hero background ─────────────────────────────────────────── */}
            <div
                className="absolute inset-0 bg-cover bg-center bg-no-repeat"
                style={{ backgroundImage: 'url(/images/hero-login.jpg)' }}
                aria-hidden="true"
            />
            <div className="absolute inset-0 bg-gray-950/70" aria-hidden="true" />

            {/* ── Top brand bar ────────────────────────────────────────────── */}
            <header className="relative z-10 flex items-center gap-2.5 px-8 py-5">
                <Lock className="h-5 w-5 text-indigo-400" aria-hidden="true" />
                <span className="text-lg font-semibold tracking-tight text-white">
                    {appName}
                </span>
            </header>

            {/* ── Centered sign-in card ─────────────────────────────────────── */}
            <main className="relative z-10 flex flex-1 items-center justify-center px-4 py-12">
                <div className="w-full max-w-md rounded-2xl border border-white/10 bg-slate-900 p-8 shadow-2xl">

                    {/* Card header */}
                    <div className="mb-6 text-center">
                        <h1 className="text-2xl font-bold text-white">{t('Agency operations portal')}</h1>
                        <p className="mt-1.5 flex items-center justify-center gap-1.5 text-sm text-white/70">
                            <ShieldCheck className="h-3.5 w-3.5 text-emerald-400" aria-hidden="true" />
                            {t('Secure encrypted connection')}
                        </p>
                    </div>

                    {/* Status flash (e.g. password reset success) */}
                    {status && (
                        <div className="mb-4 rounded-lg bg-emerald-500/20 px-4 py-3 text-sm text-emerald-300 border border-emerald-500/30">
                            {status}
                        </div>
                    )}

                    <form
                        onSubmit={submit('post', route('login'), { onError: () => captchaRef.current?.reset() })}
                        className="space-y-5"
                    >
                        {/* Work email */}
                        <div className="space-y-1.5">
                            <Label htmlFor="email" className="text-sm font-medium text-white/90">
                                {t('Work email')}
                            </Label>
                            <Input
                                id="email"
                                type="email"
                                autoComplete="email"
                                autoFocus
                                placeholder={t('you@agency.com')}
                                className="border-white/20 bg-white/10 text-white placeholder:text-white/40 focus:border-indigo-400 focus:ring-indigo-400"
                                {...register('email')}
                            />
                            {errors.email && (
                                <p className="text-xs text-red-400">{errors.email.message}</p>
                            )}
                        </div>

                        {/* Password with show/hide toggle */}
                        <div className="space-y-1.5">
                            <div className="flex items-center justify-between">
                                <Label htmlFor="password" className="text-sm font-medium text-white/90">
                                    {t('Password')}
                                </Label>
                                <Link
                                    href={route('password.request')}
                                    className="text-xs text-indigo-300 hover:text-indigo-200"
                                >
                                    {t('Forgot password?')}
                                </Link>
                            </div>
                            <div className="relative">
                                <Input
                                    id="password"
                                    type={showPassword ? 'text' : 'password'}
                                    autoComplete="current-password"
                                    className="border-white/20 bg-white/10 pr-10 text-white placeholder:text-white/40 focus:border-indigo-400 focus:ring-indigo-400"
                                    {...register('password')}
                                />
                                <button
                                    type="button"
                                    aria-label={showPassword ? t('Hide password') : t('Show password')}
                                    onClick={() => setShowPassword((v) => !v)}
                                    className="absolute right-3 top-1/2 -translate-y-1/2 text-white/50 hover:text-white/80"
                                >
                                    {showPassword
                                        ? <EyeOff className="h-4 w-4" aria-hidden="true" />
                                        : <Eye    className="h-4 w-4" aria-hidden="true" />
                                    }
                                </button>
                            </div>
                            {errors.password && (
                                <p className="text-xs text-red-400">{errors.password.message}</p>
                            )}
                        </div>

                        {/* Keep me signed in */}
                        <div className="flex items-center gap-2.5">
                            <Checkbox
                                id="remember"
                                checked={watch('remember')}
                                onCheckedChange={(v) => setValue('remember', !!v)}
                                className="border-white/30 data-[state=checked]:border-indigo-400 data-[state=checked]:bg-indigo-500"
                            />
                            <Label htmlFor="remember" className="cursor-pointer text-sm font-normal text-white/80">
                                {t('Keep me signed in')}
                            </Label>
                        </div>

                        {/* reCAPTCHA — only when enabled */}
                        {recaptcha?.enabled && (
                            <div>
                                <ReCAPTCHA
                                    ref={captchaRef}
                                    sitekey={recaptcha.siteKey}
                                    onChange={(token) => setValue('g-recaptcha-response', token ?? '')}
                                    onExpired={() => setValue('g-recaptcha-response', '')}
                                    theme="dark"
                                />
                                {errors['g-recaptcha-response'] && (
                                    <p className="mt-1 text-xs text-red-400">
                                        {errors['g-recaptcha-response'].message}
                                    </p>
                                )}
                            </div>
                        )}

                        {/* CTA */}
                        <Button
                            type="submit"
                            className="w-full bg-indigo-600 text-white hover:bg-indigo-500 focus-visible:ring-indigo-400"
                            disabled={isSubmitting}
                        >
                            {isSubmitting ? t('Signing in…') : t('Sign in to portal')}
                        </Button>
                    </form>

                    {/* Audit notice */}
                    <p className="mt-5 text-center text-xs text-white/40">
                        {t('Authorized personnel only. All activity is logged.')}
                    </p>
                </div>
            </main>

            {/* ── Footer ────────────────────────────────────────────────────── */}
            <footer className="relative z-10 flex flex-col items-center gap-1.5 px-8 py-4 text-xs text-white/40">
                <div className="flex flex-wrap items-center justify-center gap-x-6 gap-y-1">
                    <span>© {year} {appName}</span>
                    <Link href="#" className="hover:text-white/70">{t('Security policy')}</Link>
                    <Link href="#" className="hover:text-white/70">{t('Terms')}</Link>
                    <Link href="#" className="hover:text-white/70">{t('Support')}</Link>
                </div>
                <div>
                    {t('Developed by')}{' '}
                    <a
                        href="https://bangicode.ma/"
                        target="_blank"
                        rel="noopener noreferrer"
                        className="font-semibold text-white/70 underline-offset-2 transition-colors hover:text-white hover:underline"
                    >
                        Bangicode
                    </a>
                </div>
            </footer>
        </div>
    );
}

// No layout wrapper — the component is its own full-page shell
export default Login;
