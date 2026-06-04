import { useRef } from 'react';
import { useForm, Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';

// Port of resources/views/notification/create.blade.php. Field names
// (module, subject, message, enabled_email) and the notification.store route
// are preserved. The message is an HTML email template, edited as raw HTML
// (the legacy CKEditor is not reintroduced); shortcodes insert at the caret.
function NotificationCreate({ modules = [] }) {
    const t = useTranslation();
    const messageRef = useRef(null);
    const first = modules[0] ?? { key: '', name: '', subject: '', template: '', short_code: [] };

    const { data, setData, post, processing, transform } = useForm({
        module: first.key,
        subject: first.subject,
        message: first.template,
        enabled_email: true,
    });

    const current = modules.find((m) => m.key === data.module) ?? first;

    function changeModule(key) {
        const m = modules.find((x) => x.key === key);
        setData((d) => ({ ...d, module: key, subject: m?.subject ?? '', message: m?.template ?? '' }));
    }

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
        // Mirror the controller's isset() check: only send enabled_email when on.
        transform((d) => {
            const payload = { module: d.module, subject: d.subject, message: d.message };
            if (d.enabled_email) payload.enabled_email = 1;
            return payload;
        });
        post(route('notification.store'));
    }

    return (
        <div className="p-6 max-w-3xl">
            <Card>
                <CardHeader><CardTitle>{t('Create Notification')}</CardTitle></CardHeader>
                <CardContent>
                    <form onSubmit={submit} className="space-y-4">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="space-y-1.5">
                                <Label>{t('Module')}</Label>
                                <Select value={data.module} onValueChange={changeModule}>
                                    <SelectTrigger><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        {modules.map((m) => (
                                            <SelectItem key={m.key} value={m.key}>{m.name}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
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
                                {(current.short_code ?? []).map((code) => (
                                    <button type="button" key={code} onClick={() => insertShortcode(code)}>
                                        <Badge variant="secondary" className="cursor-pointer hover:bg-secondary/70">{code}</Badge>
                                    </button>
                                ))}
                                {(current.short_code ?? []).length === 0 && (
                                    <p className="text-sm text-muted-foreground">{t('No shortcodes available for this notification.')}</p>
                                )}
                            </div>
                        </div>

                        <div className="flex gap-2 pt-2">
                            <Button type="submit" disabled={processing}>{t('Create')}</Button>
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

NotificationCreate.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Email Notification Template', href: route('notification.index') },
        { label: 'Create' },
    ]}>{page}</AdminLayout>
);
export default NotificationCreate;
