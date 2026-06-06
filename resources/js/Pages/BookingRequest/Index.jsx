import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Eye, CheckCircle, XCircle, ClipboardList, Search } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';

const STATUS_VARIANT = {
    pending:   'secondary',
    confirmed: 'outline',
    refused:   'destructive',
};

function BookingRequestIndex({ bookingRequests = [] }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    async function confirm(id) {
        if (await confirmDialog({ title: t('Confirm this booking request?') })) {
            router.post(route('booking_requests.approve', id));
        }
    }

    async function refuse(id) {
        if (await confirmDialog({ title: t('Refuse this booking request?') })) {
            router.post(route('booking_requests.refuse', id));
        }
    }

    const [query, setQuery] = useState('');
    const q = query.trim().toLowerCase();
    const filtered = q
        ? bookingRequests.filter((br) =>
            [br.guest_name, br.car_name, br.status]
                .some((v) => String(v ?? '').toLowerCase().includes(q)))
        : bookingRequests;

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
                    <ClipboardList className="h-6 w-6" /> {t('Booking Requests')}
                </h1>
            </div>

            <div className="flex items-center justify-end">
                <div className="relative w-full max-w-xs">
                        <Search className="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder={t('Search requests…')}
                            className="pl-8"
                        />
                    </div>
            </div>

            <div className="rounded-xl border bg-card overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Guest')}</TableHead>
                                <TableHead>{t('Vehicle')}</TableHead>
                                <TableHead>{t('Start')}</TableHead>
                                <TableHead>{t('End')}</TableHead>
                                <TableHead>{t('Status')}</TableHead>
                                <TableHead className="text-right">{t('Action')}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {filtered.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={6} className="text-center text-muted-foreground py-8">
                                        {bookingRequests.length === 0 ? t('No booking requests yet') : t('No booking requests match your search')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {filtered.map((br) => (
                                <TableRow key={br.id}>
                                    <TableCell className="font-medium">{br.guest_name ?? '—'}</TableCell>
                                    <TableCell>{br.car_name ?? '—'}</TableCell>
                                    <TableCell>{br.start_date}</TableCell>
                                    <TableCell>{br.end_date}</TableCell>
                                    <TableCell>
                                        <Badge variant={STATUS_VARIANT[br.status] ?? 'secondary'} className="capitalize">
                                            {t(br.status)}
                                        </Badge>
                                    </TableCell>
                                    <TableCell className="text-right space-x-1">
                                        <Button variant="ghost" size="icon" asChild>
                                            <Link href={route('booking_requests.show', br.encrypted_id)} aria-label={t('View')}>
                                                <Eye className="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        {can('create booking') && br.status === 'pending' && (
                                            <Button
                                                variant="ghost" size="icon"
                                                className="text-green-600 hover:text-green-600"
                                                onClick={() => confirm(br.id)}
                                                aria-label={t('Confirm')}
                                            >
                                                <CheckCircle className="h-4 w-4" />
                                            </Button>
                                        )}
                                        {can('delete booking') && br.status === 'pending' && (
                                            <Button
                                                variant="ghost" size="icon"
                                                className="text-destructive hover:text-destructive"
                                                onClick={() => refuse(br.id)}
                                                aria-label={t('Refuse')}
                                            >
                                                <XCircle className="h-4 w-4" />
                                            </Button>
                                        )}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>
        </div>
    );
}

BookingRequestIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Booking Requests' }]}>{page}</AdminLayout>
);
export default BookingRequestIndex;
