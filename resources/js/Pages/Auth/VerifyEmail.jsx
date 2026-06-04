import { router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import PublicLayout from '@/Layouts/PublicLayout';
import { useTranslation } from '@/hooks/useTranslation';

function VerifyEmail({ status }) {
    const t = useTranslation();
    function resend() {
        router.post(route('verification.send'));
    }

    function logout() {
        router.post(route('logout'));
    }

    return (
        <div className="flex min-h-[80vh] items-center justify-center px-4">
            <Card className="w-full max-w-md">
                <CardHeader className="text-center">
                    <CardTitle className="text-2xl">{t('Verify your email')}</CardTitle>
                    <CardDescription>
                        {t("Thanks for signing up! Please verify your email address by clicking the link we sent you. Didn't receive it?")}
                    </CardDescription>
                </CardHeader>

                <CardContent className="space-y-4">
                    {status === 'verification-link-sent' && (
                        <div className="rounded-md bg-green-50 p-3 text-sm text-green-700">
                            {t('A new verification link has been sent to your email.')}
                        </div>
                    )}

                    <Button onClick={resend} className="w-full">
                        {t('Resend verification email')}
                    </Button>

                    <Button variant="ghost" onClick={logout} className="w-full">
                        {t('Log out')}
                    </Button>
                </CardContent>
            </Card>
        </div>
    );
}

VerifyEmail.layout = (page) => <PublicLayout>{page}</PublicLayout>;
export default VerifyEmail;
