import { useState } from 'react';
import { router } from '@inertiajs/react';
import { z } from 'zod';
import { useZodForm } from '@/hooks/useZodForm';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';

const schema = z.object({
    sender_name:       z.string().min(1, 'Required'),
    sender_email:      z.string().email('Enter a valid email'),
    server_driver:     z.string().min(1, 'Required'),
    server_host:       z.string().min(1, 'Required'),
    server_port:       z.string().min(1, 'Required'),
    server_username:   z.string().min(1, 'Required'),
    server_password:   z.string().min(1, 'Required'),
    server_encryption: z.string().min(1, 'Required'),
});

function Smtp({ settings }) {
    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            sender_name:       settings?.FROM_NAME         ?? '',
            sender_email:      settings?.FROM_EMAIL        ?? '',
            server_driver:     settings?.SERVER_DRIVER     ?? '',
            server_host:       settings?.SERVER_HOST       ?? '',
            server_port:       settings?.SERVER_PORT       ?? '',
            server_username:   settings?.SERVER_USERNAME   ?? '',
            server_password:   settings?.SERVER_PASSWORD   ?? '',
            server_encryption: settings?.SERVER_ENCRYPTION ?? '',
        },
    });
    const { register, formState: { errors, isSubmitting } } = form;
    const t = useTranslation();

    const [testEmail, setTestEmail] = useState('');
    const [sendingTest, setSendingTest] = useState(false);

    function sendTestMail(e) {
        e.preventDefault();
        if (!testEmail) return;
        setSendingTest(true);
        router.post(route('setting.smtp.testing'), { email: testEmail }, {
            onFinish: () => setSendingTest(false),
        });
    }

    return (
        <div className="max-w-2xl space-y-6 p-6">
            <div>
                <h1 className="text-2xl font-semibold">{t('SMTP Settings')}</h1>
                <p className="text-sm text-muted-foreground">{t('Outbound email server configuration.')}</p>
            </div>

            <Card>
                <CardHeader><CardTitle>{t('Mail Server')}</CardTitle></CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('setting.smtp'))} className="space-y-4">
                        <div className="grid grid-cols-2 gap-4">
                            {[
                                ['sender_name',       'Sender Name',       'text'],
                                ['sender_email',      'Sender Email',      'email'],
                                ['server_driver',     'SMTP Driver',       'text'],
                                ['server_host',       'SMTP Host',         'text'],
                                ['server_username',   'SMTP Username',     'text'],
                                ['server_password',   'SMTP Password',     'password'],
                                ['server_encryption', 'SMTP Encryption',   'text'],
                                ['server_port',       'SMTP Port',         'text'],
                            ].map(([key, label, type]) => (
                                <div key={key} className="space-y-1">
                                    <Label htmlFor={key}>{t(label)}</Label>
                                    <Input id={key} type={type} autoComplete="off" {...register(key)} />
                                    {errors[key] && <p className="text-sm text-destructive">{errors[key].message}</p>}
                                </div>
                            ))}
                        </div>
                        <Button type="submit" disabled={isSubmitting}>
                            {isSubmitting ? t('Saving…') : t('Save')}
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <Card>
                <CardHeader><CardTitle>{t('Test Mail')}</CardTitle></CardHeader>
                <CardContent>
                    <form onSubmit={sendTestMail} className="flex gap-3 items-end">
                        <div className="flex-1 space-y-1">
                            <Label htmlFor="test_email">{t('Recipient email')}</Label>
                            <Input
                                id="test_email"
                                type="email"
                                placeholder="you@example.com"
                                value={testEmail}
                                onChange={(e) => setTestEmail(e.target.value)}
                                required
                            />
                        </div>
                        <Button type="submit" variant="outline" disabled={sendingTest}>
                            {sendingTest ? t('Sending…') : t('Send Test')}
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

Smtp.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Settings' }, { label: 'SMTP' }]}>{page}</AdminLayout>
);
export default Smtp;
