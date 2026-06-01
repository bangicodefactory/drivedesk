import { Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Eye, CheckCircle, XCircle, ClipboardList } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

const STATUS_VARIANT = {
    pending:   'secondary',
    confirmed: 'outline',
    refused:   'destructive',
};

function BookingRequestIndex({ bookingRequests = [] }) {
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    function confirm(id) {
        if (window.confirm('Confirm this booking request?')) {
            router.post(route('booking_requests.approve', id));
        }
    }

    function refuse(id) {
        if (window.confirm('Refuse this booking request?')) {
            router.post(route('booking_requests.refuse', id));
        }
    }

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-semibold flex items-center gap-2">
                    <ClipboardList className="h-6 w-6" /> Booking Requests
                </h1>
            </div>

            <Card>
                <CardHeader><CardTitle>All Requests</CardTitle></CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Guest</TableHead>
                                <TableHead>Vehicle</TableHead>
                                <TableHead>Start</TableHead>
                                <TableHead>End</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="text-right">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {bookingRequests.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={6} className="text-center text-muted-foreground py-8">
                                        No booking requests yet
                                    </TableCell>
                                </TableRow>
                            )}
                            {bookingRequests.map((br) => (
                                <TableRow key={br.id}>
                                    <TableCell className="font-medium">{br.guest_name ?? '—'}</TableCell>
                                    <TableCell>{br.car_name ?? '—'}</TableCell>
                                    <TableCell>{br.start_date}</TableCell>
                                    <TableCell>{br.end_date}</TableCell>
                                    <TableCell>
                                        <Badge variant={STATUS_VARIANT[br.status] ?? 'secondary'}>
                                            {br.status}
                                        </Badge>
                                    </TableCell>
                                    <TableCell className="text-right space-x-1">
                                        <Button variant="ghost" size="icon" asChild>
                                            <Link href={route('booking_requests.show', br.encrypted_id)} aria-label="View">
                                                <Eye className="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        {can('create booking') && br.status === 'pending' && (
                                            <Button
                                                variant="ghost" size="icon"
                                                className="text-green-600 hover:text-green-600"
                                                onClick={() => confirm(br.id)}
                                                aria-label="Confirm"
                                            >
                                                <CheckCircle className="h-4 w-4" />
                                            </Button>
                                        )}
                                        {can('delete booking') && br.status === 'pending' && (
                                            <Button
                                                variant="ghost" size="icon"
                                                className="text-destructive hover:text-destructive"
                                                onClick={() => refuse(br.id)}
                                                aria-label="Refuse"
                                            >
                                                <XCircle className="h-4 w-4" />
                                            </Button>
                                        )}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    );
}

BookingRequestIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Booking Requests' }]}>{page}</AdminLayout>
);
export default BookingRequestIndex;
