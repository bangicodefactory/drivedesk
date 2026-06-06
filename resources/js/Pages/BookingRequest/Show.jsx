import { Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, CheckCircle, XCircle } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';

const STATUS_VARIANT = {
    pending:   'secondary',
    confirmed: 'outline',
    refused:   'destructive',
};

function BookingRequestShow({ booking }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    async function confirm() {
        if (await confirmDialog({ title: t('Confirm this booking request?') })) {
            router.post(route('booking_requests.approve', booking.id));
        }
    }

    async function refuse() {
        if (await confirmDialog({ title: t('Refuse this booking request?') })) {
            router.post(route('booking_requests.refuse', booking.id));
        }
    }

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center gap-3">
                <Button variant="ghost" size="icon" asChild>
                    <Link href={route('booking_requests.index')}><ArrowLeft className="h-4 w-4" /></Link>
                </Button>
                <h1 className="text-3xl font-bold tracking-tight">{t('Booking Request')} #{booking.id}</h1>
                <Badge variant={STATUS_VARIANT[booking.status] ?? 'secondary'}>{booking.status}</Badge>
            </div>

            {booking.status === 'pending' && (
                <div className="flex gap-2">
                    {can('create booking') && (
                        <Button onClick={confirm} className="gap-2">
                            <CheckCircle className="h-4 w-4" /> {t('Confirm')}
                        </Button>
                    )}
                    {can('delete booking') && (
                        <Button variant="destructive" onClick={refuse} className="gap-2">
                            <XCircle className="h-4 w-4" /> {t('Refuse')}
                        </Button>
                    )}
                </div>
            )}

            <div className="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader><CardTitle>{t('Guest')}</CardTitle></CardHeader>
                    <CardContent className="space-y-2 text-sm">
                        <div className="flex justify-between"><span className="text-muted-foreground">{t('Name')}</span><span>{booking.guest_name}</span></div>
                        <div className="flex justify-between"><span className="text-muted-foreground">{t('Email')}</span><span>{booking.guest_email}</span></div>
                        <div className="flex justify-between"><span className="text-muted-foreground">{t('Phone')}</span><span>{booking.guest_phone ?? '—'}</span></div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader><CardTitle>{t('Vehicle')}</CardTitle></CardHeader>
                    <CardContent className="space-y-2 text-sm">
                        <div className="flex justify-between"><span className="text-muted-foreground">{t('Car')}</span><span>{booking.car_name}</span></div>
                        <div className="flex justify-between"><span className="text-muted-foreground">{t('Daily Rate')}</span><span>{booking.daily_rate} Dh</span></div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader><CardTitle>{t('Rental Period')}</CardTitle></CardHeader>
                    <CardContent className="space-y-2 text-sm">
                        <div className="flex justify-between"><span className="text-muted-foreground">{t('Start')}</span><span>{booking.start_date} {booking.start_time}</span></div>
                        <div className="flex justify-between"><span className="text-muted-foreground">{t('End')}</span><span>{booking.end_date} {booking.end_time}</span></div>
                        <div className="flex justify-between"><span className="text-muted-foreground">{t('Pickup')}</span><span>{booking.pickup_place ?? '—'}</span></div>
                        <div className="flex justify-between"><span className="text-muted-foreground">{t('Drop-off')}</span><span>{booking.dropoff_place ?? '—'}</span></div>
                    </CardContent>
                </Card>

                {booking.notes && (
                    <Card>
                        <CardHeader><CardTitle>{t('Notes')}</CardTitle></CardHeader>
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
