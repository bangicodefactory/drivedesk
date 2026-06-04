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

function statusVariant(status) {
    if (status === 'overdue') return 'destructive';
    if (status === 'urgent') return 'warning';
    if (status === 'upcoming') return 'default';
    if (status === 'completed') return 'secondary';
    return 'outline';
}

function ReminderIndex({ reminders = [], stats = {} }) {
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    function remove(id) {
        if (window.confirm('Are you sure?')) {
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
                <h1 className="text-2xl font-semibold flex items-center gap-2">
                    <Bell className="h-6 w-6" /> Reminders
                </h1>
                {can('create reminder') && (
                    <Button size="sm" asChild>
                        <Link href={route('reminder.create')}>
                            <Plus className="mr-2 h-4 w-4" /> Create Reminder
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
                                <p className="text-sm text-muted-foreground">Overdue</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="pt-6">
                        <div className="flex items-center gap-3">
                            <AlertTriangle className="h-8 w-8 text-orange-500" />
                            <div>
                                <p className="text-2xl font-bold">{stats.urgent ?? 0}</p>
                                <p className="text-sm text-muted-foreground">Urgent</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="pt-6">
                        <div className="flex items-center gap-3">
                            <Clock className="h-8 w-8 text-blue-500" />
                            <div>
                                <p className="text-2xl font-bold">{stats.upcoming ?? 0}</p>
                                <p className="text-sm text-muted-foreground">Upcoming</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="pt-6">
                        <div className="flex items-center gap-3">
                            <CheckCircle className="h-8 w-8 text-green-500" />
                            <div>
                                <p className="text-2xl font-bold">{stats.completed ?? 0}</p>
                                <p className="text-sm text-muted-foreground">Completed</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader className="flex flex-row items-center justify-between gap-4 space-y-0">
                    <CardTitle>All Reminders</CardTitle>
                    <div className="relative w-full max-w-xs">
                        <Search className="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder="Search reminders…"
                            className="pl-8"
                        />
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Vehicle</TableHead>
                                <TableHead>Reminder Date</TableHead>
                                <TableHead>Days</TableHead>
                                <TableHead>Status</TableHead>
                                {showActions && <TableHead className="text-right">Action</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {filtered.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={showActions ? 7 : 6} className="text-center text-muted-foreground py-8">
                                        {reminders.length === 0 ? 'No reminders yet' : 'No reminders match your search'}
                                    </TableCell>
                                </TableRow>
                            )}
                            {filtered.map((reminder) => (
                                <TableRow key={reminder.id}>
                                    <TableCell className="font-medium">{reminder.name}</TableCell>
                                    <TableCell>{reminder.reminder_type?.type ?? '—'}</TableCell>
                                    <TableCell>{reminder.vehicles?.name ?? '—'}</TableCell>
                                    <TableCell>{reminder.reminder_date}</TableCell>
                                    <TableCell>
                                        {reminder.days_remaining !== undefined
                                            ? Math.round(reminder.days_remaining)
                                            : '—'}
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant={statusVariant(reminder.status)} className="capitalize">
                                            {reminder.status}
                                        </Badge>
                                    </TableCell>
                                    {showActions && (
                                        <TableCell className="text-right space-x-1">
                                            {can('edit reminder') && reminder.status !== 'completed' && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => markComplete(reminder.id)}
                                                    aria-label="Mark complete"
                                                    title="Mark as completed"
                                                >
                                                    <CheckCircle className="h-4 w-4 text-green-600" />
                                                </Button>
                                            )}
                                            {can('edit reminder') && reminder.status !== 'completed' && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => snooze(reminder.id)}
                                                    aria-label="Snooze"
                                                    title="Snooze"
                                                >
                                                    <Clock className="h-4 w-4 text-blue-600" />
                                                </Button>
                                            )}
                                            {can('edit reminder') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('reminder.edit', reminder.id)} aria-label="Edit">
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
                                                    aria-label="Delete"
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

ReminderIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Reminders' }]}>{page}</AdminLayout>
);
export default ReminderIndex;
