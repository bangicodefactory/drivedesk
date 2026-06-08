import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Pencil, Trash2, Plus, Bell, CheckCircle, Clock, AlertTriangle, XCircle, Search } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';

// Badge colours mirror the summary cards above the table so a status reads
// the same in both places: overdue=danger, urgent=warning, upcoming=info,
// completed=success.
function statusVariant(status) {
    if (status === 'overdue') return 'destructive';
    if (status === 'urgent') return 'warning';
    if (status === 'upcoming') return 'info';
    if (status === 'completed') return 'success';
    return 'outline';
}

function ReminderIndex({ reminders = [], stats = {} }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    async function remove(id) {
        if (await confirmDialog({ title: t('Are you sure?') })) {
            router.delete(route('reminder.destroy', id));
        }
    }

    function markComplete(id) {
        router.post(route('reminder.complete', id));
    }

    function snooze(id) {
        const days = window.prompt('Snooze for how many days?', '7');
        if (days && parseInt(days) > 0) {
            router.post(route('reminder.snooze', id), { days: parseInt(days) });
        }
    }

    const showActions = can('edit reminder') || can('delete reminder');

    const [query, setQuery] = useState('');
    const q = query.trim().toLowerCase();
    const filtered = q
        ? reminders.filter((reminder) =>
            [reminder.name, reminder.reminder_type?.type, reminder.vehicles?.name, reminder.status]
                .some((v) => String(v ?? '').toLowerCase().includes(q)))
        : reminders;

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
                    <Bell className="h-6 w-6" /> {t('Reminders')}
                </h1>
                {can('create reminder') && (
                    <Button size="sm" asChild>
                        <Link href={route('reminder.create')}>
                            <Plus className="mr-2 h-4 w-4" /> {t('Create Reminder')}
                        </Link>
                    </Button>
                )}
            </div>

            <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
                <Card>
                    <CardContent className="pt-6">
                        <div className="flex items-center gap-3">
                            <XCircle className="h-8 w-8 text-destructive" />
                            <div>
                                <p className="text-2xl font-bold">{stats.overdue ?? 0}</p>
                                <p className="text-sm text-muted-foreground">{t('Overdue')}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="pt-6">
                        <div className="flex items-center gap-3">
                            <AlertTriangle className="h-8 w-8 text-warning" />
                            <div>
                                <p className="text-2xl font-bold">{stats.urgent ?? 0}</p>
                                <p className="text-sm text-muted-foreground">{t('Urgent')}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="pt-6">
                        <div className="flex items-center gap-3">
                            <Clock className="h-8 w-8 text-info" />
                            <div>
                                <p className="text-2xl font-bold">{stats.upcoming ?? 0}</p>
                                <p className="text-sm text-muted-foreground">{t('Upcoming')}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="pt-6">
                        <div className="flex items-center gap-3">
                            <CheckCircle className="h-8 w-8 text-success" />
                            <div>
                                <p className="text-2xl font-bold">{stats.completed ?? 0}</p>
                                <p className="text-sm text-muted-foreground">{t('Completed')}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div className="flex items-center justify-end">
                <div className="relative w-full max-w-xs">
                    <Search className="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                        placeholder={t('Search reminders…')}
                        className="pl-8"
                    />
                </div>
            </div>

            <div className="rounded-xl border bg-card overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Name')}</TableHead>
                                <TableHead>{t('Type')}</TableHead>
                                <TableHead>{t('Vehicle')}</TableHead>
                                <TableHead>{t('Reminder Date')}</TableHead>
                                <TableHead>{t('Days')}</TableHead>
                                <TableHead>{t('Status')}</TableHead>
                                {showActions && <TableHead className="text-right">{t('Action')}</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {filtered.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={showActions ? 7 : 6} className="text-center text-muted-foreground py-8">
                                        {reminders.length === 0 ? t('No reminders yet') : t('No reminders match your search')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {filtered.map((reminder) => (
                                <TableRow key={reminder.id}>
                                    <TableCell className="font-medium">{reminder.name}</TableCell>
                                    <TableCell>{reminder.reminder_type?.type ?? '—'}</TableCell>
                                    <TableCell>{reminder.vehicles?.name ?? '—'}</TableCell>
                                    <TableCell>{reminder.reminder_date ? reminder.reminder_date.slice(0, 10) : '—'}</TableCell>
                                    <TableCell>
                                        {reminder.days_remaining !== undefined
                                            ? Math.round(reminder.days_remaining)
                                            : '—'}
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant={statusVariant(reminder.status)} className="capitalize">
                                            {t(reminder.status)}
                                        </Badge>
                                    </TableCell>
                                    {showActions && (
                                        <TableCell className="text-right space-x-1">
                                            {can('edit reminder') && reminder.status !== 'completed' && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => markComplete(reminder.id)}
                                                    aria-label={t('Mark complete')}
                                                    title={t('Mark as completed')}
                                                >
                                                    <CheckCircle className="h-4 w-4 text-success" />
                                                </Button>
                                            )}
                                            {can('edit reminder') && reminder.status !== 'completed' && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => snooze(reminder.id)}
                                                    aria-label={t('Snooze')}
                                                    title={t('Snooze')}
                                                >
                                                    <Clock className="h-4 w-4 text-info" />
                                                </Button>
                                            )}
                                            {can('edit reminder') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('reminder.edit', reminder.id)} aria-label={t('Edit')}>
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('delete reminder') && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="text-destructive hover:text-destructive"
                                                    onClick={() => remove(reminder.id)}
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
            </div>
        </div>
    );
}

ReminderIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Reminders' }]}>{page}</AdminLayout>
);
export default ReminderIndex;
