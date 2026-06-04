import { useRef } from 'react';
import { useForm, Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';

// Port of resources/views/notification/edit.blade.php. The module is read-only
// (the controller never changes it); subject/message/enabled_email post to the
// notification.update route. Message is edited as raw HTML; shortcodes insert
// at the caret.
function NotificationEdit({ notification, shortCodes = [] }) {
    const t = useTranslation();
    const messageRef = useRef(null);

    const { data, setData, put, processing } = useForm({
        subject: notification.subject ?? '',
        message: notification.message ?? '',
        enabled_email: notification.enabled_email === 1,
    });

    function insertShortcode(code) {
        const el = messageRef.current;
        const msg = data.message ?? '';
        if (!el) { setData('message', msg + code); return; }
        const start = el.selectionStart ?? msg.length;
        const end = el.selectionEnd ?? msg.length;
        setData('message', msg.slice(0, start) + code + msg.slice(end));
        requestAnimationFrame(() => {
            el.focus();
            el.selectionStart = el.selectionEnd = start + code.length;
        });
    }

    function submit(e) {
        e.preventDefault();
        put(route('notification.update', notification.id), {
            data: {
                subject: data.subject,
                message: data.message,
                enabled_email: data.enabled_email ? 1 : 0,
            },
        });
    }

    return (
        <div className="p-6 max-w-3xl">
            <Card>
                <CardHeader><CardTitle>{t('Edit Notification')}</CardTitle></CardHeader>
                <CardContent>
                    <form onSubmit={submit} className="space-y-4">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="space-y-1.5">
                                <Label htmlFor="name">{t('Module')}</Label>
                                <Input id="name" value={notification.name ?? ''} readOnly />
                            </div>
                            <div className="space-y-1.5">
                                <Label htmlFor="subject">{t('Subject')}</Label>
                                <Input
                                    id="subject"
                                    value={data.subject}
                                    onChange={(e) => setData('subject', e.target.value)}
                                    placeholder={t('Enter Subject')}
                                />
                            </div>
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="message">{t('User Message')}</Label>
                            <Textarea
                                id="message"
                                ref={messageRef}
                                value={data.message}
                                onChange={(e) => setData('message', e.target.value)}
                                rows={12}
                                className="font-mono text-xs"
                            />
                        </div>

                        <div className="flex items-center gap-2">
                            <Switch
                                id="enabled_email"
                                checked={data.enabled_email}
                                onCheckedChange={(v) => setData('enabled_email', v)}
                            />
                            <Label htmlFor="enabled_email" className="cursor-pointer">{t('Enabled Email Notification')}</Label>
                        </div>

                        <div className="space-y-2">
                            <h4 className="font-semibold">{t('Shortcodes')}</h4>
                            <p className="text-sm text-muted-foreground">{t('Click to add below shortcodes and insert in your Message')}</p>
                            <div className="flex flex-wrap gap-2">
                                {shortCodes.map((code) => (
                                    <button type="button" key={code} onClick={() => insertShortcode(code)}>
                                        <Badge variant="secondary" className="cursor-pointer hover:bg-secondary/70">{code}</Badge>
                                    </button>
                                ))}
                                {shortCodes.length === 0 && (
                                    <p className="text-sm text-muted-foreground">{t('No shortcodes available for this notification.')}</p>
                                )}
                            </div>
                        </div>

                        <div className="flex gap-2 pt-2">
                            <Button type="submit" disabled={processing}>{t('Update')}</Button>
                            <Button type="button" variant="outline" asChild>
                                <Link href={route('notification.index')}>{t('Cancel')}</Link>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

NotificationEdit.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Email Notification Template', href: route('notification.index') },
        { label: 'Edit' },
    ]}>{page}</AdminLayout>
);
export default NotificationEdit;
