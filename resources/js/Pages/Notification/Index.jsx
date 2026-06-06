import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Pencil, Trash2, Plus, Mail, Search } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';

// Port of resources/views/notification/index.blade.php. Columns, badge states
// (Enable/Disable) and the destroy route are preserved 1:1.
function NotificationIndex({ notifications = [] }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);
    const showActions = can('edit notification') || can('delete notification');

    const [query, setQuery] = useState('');
    const q = query.trim().toLowerCase();
    const filtered = q
        ? notifications.filter((n) =>
            [n.name, n.subject].some((v) => String(v ?? '').toLowerCase().includes(q)))
        : notifications;

    async function remove(id) {
        if (await confirmDialog({ title: t('Delete this notification?') })) {
            router.delete(route('notification.destroy', id));
        }
    }

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
                    <Mail className="h-6 w-6" /> {t('Email Notification Template')}
                </h1>
                {can('create notification') && (
                    <Button size="sm" asChild>
                        <Link href={route('notification.create')}>
                            <Plus className="mr-2 h-4 w-4" /> {t('Add')}
                        </Link>
                    </Button>
                )}
            </div>

            <Card>
                <CardHeader className="flex flex-row items-center justify-between gap-4 space-y-0">
                    <CardTitle>{t('Email Notification Template')}</CardTitle>
                    <div className="relative w-full max-w-xs">
                        <Search className="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder={t('Search…')}
                            className="pl-8"
                        />
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Module')}</TableHead>
                                <TableHead>{t('Subject')}</TableHead>
                                <TableHead>{t('Email Enable')}</TableHead>
                                {showActions && <TableHead className="text-right">{t('Action')}</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {filtered.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={showActions ? 4 : 3} className="text-center text-muted-foreground py-8">
                                        {notifications.length === 0 ? t('No notifications yet') : t('No notifications match your search')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {filtered.map((n) => (
                                <TableRow key={n.id}>
                                    <TableCell className="font-medium">{n.name}</TableCell>
                                    <TableCell>{n.subject}</TableCell>
                                    <TableCell>
                                        <Badge variant={n.enabled_email === 1 ? 'default' : 'destructive'}>
                                            {n.enabled_email === 1 ? t('Enable') : t('Disable')}
                                        </Badge>
                                    </TableCell>
                                    {showActions && (
                                        <TableCell className="text-right space-x-1 whitespace-nowrap">
                                            {can('edit notification') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('notification.edit', n.id)} aria-label={t('Edit')}>
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('delete notification') && (
                                                <Button
                                                    variant="ghost" size="icon"
                                                    className="text-destructive hover:text-destructive"
                                                    onClick={() => remove(n.id)}
                                                    aria-label={t('Delete')}
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                            )}
                                        </TableCell>
                                    )}
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    );
}

NotificationIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Email Notification Template' }]}>{page}</AdminLayout>
);
export default NotificationIndex;
