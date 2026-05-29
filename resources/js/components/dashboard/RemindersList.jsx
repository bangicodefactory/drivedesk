import { Link } from '@inertiajs/react';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Hash, CalendarDays } from 'lucide-react';

/**
 * Notifications list — upcoming vehicle reminders.
 * Replaces the Notifications block in resources/views/dashboard/index.blade.php.
 *
 * @param {Object} props
 * @param {Array<{
 *   id: number,
 *   reminder_date: string|null,
 *   note: string|null,
 *   status: string|null,
 *   vehicle: { name: string, license_plate: string } | null,
 * }>} props.reminders
 */
export default function RemindersList({ reminders }) {
    const count = reminders?.length ?? 0;

    return (
        <Card>
            <CardHeader className="flex flex-row items-center justify-between">
                <CardTitle>Notifications</CardTitle>
                <Badge variant="secondary">{count} New</Badge>
            </CardHeader>

            <CardContent className="p-0">
                {count === 0 ? (
                    <p className="px-6 py-8 text-center text-sm text-muted-foreground">
                        No notifications found
                    </p>
                ) : (
                    <ul className="divide-y">
                        {reminders.map((r) => (
                            <li key={r.id} className="grid grid-cols-1 gap-2 px-6 py-4 md:grid-cols-12 md:items-center">
                                <div className="md:col-span-4">
                                    <p className="font-medium">{r.vehicle?.name ?? 'N/A'}</p>
                                    <p className="flex items-center gap-1 text-sm text-muted-foreground">
                                        <Hash className="h-3 w-3" />
                                        {r.vehicle?.license_plate ?? 'N/A'}
                                    </p>
                                </div>

                                <div className="md:col-span-5">
                                    <p className="text-sm text-muted-foreground">
                                        {r.note ?? 'No description'}
                                    </p>
                                    <p className="flex items-center gap-1 text-sm">
                                        <CalendarDays className="h-3 w-3" />
                                        {r.reminder_date ?? '—'}
                                    </p>
                                </div>

                                <div className="md:col-span-3 md:text-right">
                                    {r.status && (
                                        <Badge variant={r.status === 'urgent' ? 'destructive' : 'secondary'}>
                                            {r.status.charAt(0).toUpperCase() + r.status.slice(1)}
                                        </Badge>
                                    )}
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
            </CardContent>

            {count > 0 && (
                <CardFooter className="justify-center border-t py-3">
                    <Link
                        href={route('reminder.index')}
                        className="text-sm text-primary hover:underline"
                    >
                        View all
                    </Link>
                </CardFooter>
            )}
        </Card>
    );
}
