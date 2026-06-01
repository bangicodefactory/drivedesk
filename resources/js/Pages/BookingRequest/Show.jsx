import { Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, CheckCircle, XCircle } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

const STATUS_VARIANT = {
    pending:   'secondary',
    confirmed: 'outline',
    refused:   'destructive',
};

function BookingRequestShow({ booking, settings }) {
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    function confirm() {
        if (window.confirm('Confirm this booking request?')) {
            router.post(route('booking_requests.approve', booking.id));
        }
    }

    function refuse() {
        if (window.confirm('Refuse this booking request?')) {
            router.post(route('booking_requests.refuse', booking.id));
        }
    }

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center gap-3">
                <Button variant="ghost" size="icon" asChild>
                    <Link href={route('booking_requests.index')}><ArrowLeft className="h-4 w-4" /></Link>
                </Button>
                <h1 className="text-2xl font-semibold">Booking Request #{booking.id}</h1>
                <Badge variant={STATUS_VARIANT[booking.status] ?? 'secondary'}>{booking.status}</Badge>
            </div>

            {booking.status === 'pending' && (
                <div className="flex gap-2">
                    {can('create booking') && (
                        <Button onClick={confirm} className="gap-2">
                            <CheckCircle className="h-4 w-4" /> Confirm
                        </Button>
                    )}
                    {can('delete booking') && (
                        <Button variant="destructive" onClick={refuse} className="gap-2">
                            <XCircle className="h-4 w-4" /> Refuse
                        </Button>
                    )}
                </div>
            )}

            <div className="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader><CardTitle>Guest</CardTitle></CardHeader>
                    <CardContent className="space-y-2 text-sm">
                        <div className="flex justify-between"><span className="text-muted-foreground">Name</span><span>{booking.guest_name}</span></div>
                        <div className="flex justify-between"><span className="text-muted-foreground">Email</span><span>{booking.guest_email}</span></div>
                        <div className="flex justify-between"><span className="text-muted-foreground">Phone</span><span>{booking.guest_phone ?? '—'}</span></div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader><CardTitle>Vehicle</CardTitle></CardHeader>
                    <CardContent className="space-y-2 text-sm">
                        <div className="flex justify-between"><span className="text-muted-foreground">Car</span><span>{booking.car_name}</span></div>
                        <div className="flex justify-between"><span className="text-muted-foreground">Daily Rate</span><span>{booking.daily_rate} Dh</span></div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader><CardTitle>Rental Period</CardTitle></CardHeader>
                    <CardContent className="space-y-2 text-sm">
                        <div className="flex justify-between"><span className="text-muted-foreground">Start</span><span>{booking.start_date} {booking.start_time}</span></div>
                        <div className="flex justify-between"><span className="text-muted-foreground">End</span><span>{booking.end_date} {booking.end_time}</span></div>
                        <div className="flex justify-between"><span className="text-muted-foreground">Pickup</span><span>{booking.pickup_place ?? '—'}</span></div>
                        <div className="flex justify-between"><span className="text-muted-foreground">Drop-off</span><span>{booking.dropoff_place ?? '—'}</span></div>
                    </CardContent>
                </Card>

                {booking.notes && (
                    <Card>
                        <CardHeader><CardTitle>Notes</CardTitle></CardHeader>
                        <CardContent><p className="text-sm">{booking.notes}</p></CardContent>
                    </Card>
                )}
            </div>
        </div>
    );
}

BookingRequestShow.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Booking Requests', href: route('booking_requests.index') },
        { label: 'Details' },
    ]}>{page}</AdminLayout>
);
export default BookingRequestShow;
