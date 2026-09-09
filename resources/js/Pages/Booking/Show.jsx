import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger,
} from '@/components/ui/dialog';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { Pencil, Printer, CreditCard, Trash2 } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';

const STATUS_VARIANT = {
    yet_to_start: 'default',
    on_going: 'secondary',
    completed: 'outline',
    cancelled: 'destructive',
};

const PAYMENT_VARIANT = {
    paye: 'outline',
    impaye: 'destructive',
    partiellement_paye: 'secondary',
};

function PaymentDialog({ bookingId, dueAmount, defaultQuantity, paymentMethods }) {
    const t = useTranslation();
    const { client } = usePage().props;
    const cashMax = client?.cash_max ?? 5000;
    const cashSplitEnabled = !!client?.features?.cash_split;

    const [open, setOpen] = useState(false);
    const [form, setForm] = useState({
        date: new Date().toISOString().slice(0, 10),
        amount: dueAmount,
        quantity: defaultQuantity,
        payment_method: paymentMethods?.[0]?.value ?? '',
        notes: '',
    });
    const [error, setError] = useState('');
    const [preview, setPreview] = useState(null); // pending split awaiting confirmation
    const [busy, setBusy] = useState(false);

    function set(key, value) {
        setForm((prev) => ({ ...prev, [key]: value }));
        setError('');
        setPreview(null); // any edit invalidates a shown split preview
    }

    function reset() {
        setError('');
        setPreview(null);
        setBusy(false);
    }

    const normalizeMethod = (m) => (m || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');

    function post() {
        setBusy(true);
        router.post(route('booking.payment.store', bookingId), form, {
            onSuccess: () => { setOpen(false); setPreview(null); },
            onError: (errs) => setError(errs.amount || errs.date || errs.payment_method || Object.values(errs)[0] || 'Error'),
            onFinish: () => setBusy(false),
        });
    }

    function submit(e) {
        e.preventDefault();
        setError('');
        const amount = parseFloat(form.amount) || 0;
        const overCap = amount > cashMax && normalizeMethod(form.payment_method) === 'espece';

        // Cash over the legal ceiling: split when enabled, else refuse (as before).
        if (overCap && !cashSplitEnabled) {
            setError(t('Cash payments over 5000 are not allowed. Please choose another method.'));
            return;
        }
        // First submit of an over-cap cash payment → fetch + show the split preview.
        if (overCap && cashSplitEnabled && !preview) {
            setBusy(true);
            window.axios.post(route('booking.payment.split-preview', bookingId), {
                amount: form.amount,
                payment_method: form.payment_method,
                quantity: form.quantity,
            })
                .then(({ data }) => { data?.split ? setPreview(data) : post(); })
                .catch(() => setError(t('Cash payments over 5000 are not allowed. Please choose another method.')))
                .finally(() => setBusy(false));
            return;
        }
        // Normal payment, or confirming the already-previewed split.
        post();
    }

    return (
        <Dialog open={open} onOpenChange={(o) => { setOpen(o); if (!o) reset(); }}>
            <DialogTrigger asChild>
                <Button size="sm" variant="outline">
                    <CreditCard className="me-2 h-4 w-4" /> {t('Payment')}
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{t('Create Payment')}</DialogTitle>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    {error && <p className="text-sm text-destructive">{error}</p>}
                    <div className="space-y-1">
                        <Label htmlFor="pay-date">{t('Date')}</Label>
                        <Input id="pay-date" type="date" value={form.date} onChange={(e) => set('date', e.target.value)} required />
                    </div>
                    <div className="space-y-1">
                        <Label htmlFor="pay-amount">{t('Amount')}</Label>
                        <Input id="pay-amount" type="number" step="any" value={form.amount} onChange={(e) => set('amount', e.target.value)} required />
                    </div>
                    <div className="space-y-1">
                        <Label htmlFor="pay-quantity">{t('Quantity (Days)')}</Label>
                        <Input id="pay-quantity" type="number" min="1" step="1" value={form.quantity} onChange={(e) => set('quantity', e.target.value)} required />
                    </div>
                    <div className="space-y-1">
                        <Label>{t('Method')}</Label>
                        <Select defaultValue={form.payment_method} onValueChange={(v) => set('payment_method', v)}>
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                {paymentMethods?.map((m) => (
                                    <SelectItem key={m.value} value={m.value}>{m.label}</SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="space-y-1">
                        <Label htmlFor="pay-notes">{t('Notes')}</Label>
                        <Textarea id="pay-notes" rows={2} value={form.notes} onChange={(e) => set('notes', e.target.value)} />
                    </div>
                    {preview && (
                        <div className="space-y-2 rounded-md border p-3 text-sm">
                            <p className="text-muted-foreground">
                                {t('This cash payment exceeds :max MAD and will be split into :count receipts.')
                                    .replace(':max', cashMax).replace(':count', preview.count)}
                            </p>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('Receipt')}</TableHead>
                                        <TableHead>{t('Date')}</TableHead>
                                        <TableHead>{t('Quantity (Days)')}</TableHead>
                                        <TableHead className="text-end">{t('Amount')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {preview.receipts.map((r, i) => (
                                        <TableRow key={i}>
                                            <TableCell>{i + 1}</TableCell>
                                            <TableCell>{r.date}</TableCell>
                                            <TableCell>{r.days}</TableCell>
                                            <TableCell className="text-end">{r.amount}</TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    )}
                    <div className="flex justify-end gap-2">
                        <Button type="button" variant="outline" onClick={() => { setOpen(false); reset(); }}>{t('Close')}</Button>
                        <Button type="submit" disabled={busy}>{preview ? t('Confirm split') : t('Create')}</Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function BookingShow({ booking, settings, paymentMethods, defaultQuantity }) {
    const t = useTranslation();
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);
    const confirmDialog = useConfirm();

    async function deletePayment(pid) {
        if (await confirmDialog({ title: t('Delete this payment?') })) {
            router.delete(route('booking.payment.destroy', [booking.id, pid]));
        }
    }

    function print() {
        window.print();
    }

    return (
        <div className="space-y-6 p-6 print:p-[14mm]" id="invoice-print">

            {/* Actions */}
            <div className="flex flex-wrap gap-2 justify-end print:hidden">
                {can('create booking payment') && booking.payment_status !== 'paye' && (
                    <PaymentDialog
                        bookingId={booking.id}
                        dueAmount={booking.due_amount}
                        defaultQuantity={defaultQuantity}
                        paymentMethods={paymentMethods}
                    />
                )}
                {can('edit booking') && (
                    <Button size="sm" asChild>
                        <Link href={route('booking.edit', booking.encrypted_id)}>
                            <Pencil className="me-2 h-4 w-4" /> {t('Edit')}
                        </Link>
                    </Button>
                )}
                <Button size="sm" variant="outline" onClick={print}>
                    <Printer className="me-2 h-4 w-4" /> {t('Print')}
                </Button>
            </div>

            {/* Invoice Card */}
            <Card className="print:border-0 print:shadow-none">
                <CardContent className="pt-6">
                    {/* Header */}
                    <div className="flex flex-col md:flex-row md:justify-between gap-6 mb-6">
                        <div className="flex items-start gap-4">
                            {settings?.company_logo && (
                                <img
                                    src={`/storage/upload/logo/${settings.company_logo}`}
                                    alt="logo"
                                    className="h-16 w-auto object-contain"
                                />
                            )}
                            <ul className="space-y-1 text-sm">
                                <li>{settings?.company_name}</li>
                                <li>{settings?.company_phone}</li>
                                <li>{settings?.company_email}</li>
                            </ul>
                        </div>
                        <ul className="space-y-1 text-sm text-end">
                            <li>IF: {settings?.if}</li>
                            <li>RC: {settings?.rc}</li>
                            <li>Patente: {settings?.patente}</li>
                            <li>ICE: {settings?.ice}</li>
                        </ul>
                    </div>

                    {/* Billing info */}
                    <div className="flex flex-col md:flex-row gap-6 mb-6">
                        <div className="flex-1">
                            <h5 className="font-semibold mb-2">{t('Receipt To:')}</h5>
                            <ul className="space-y-1 text-sm">
                                <li>{booking.driver_name}</li>
                                <li>{booking.driver_phone}</li>
                                <li>{booking.driver_email}</li>
                                {booking.driver_ice && <li>ICE: {booking.driver_ice}</li>}
                            </ul>
                        </div>
                        <div className="flex-1 text-sm space-y-1">
                            <p><span className="text-muted-foreground">{t('Booking Date:')}</span> {booking.created_at}</p>
                            <p><span className="text-muted-foreground">{t('Booking ID:')}</span> <span className="font-mono">{booking.booking_id}</span></p>
                            <p><span className="text-muted-foreground">{t('Start:')}</span> {booking.start_date} — {booking.start_time}</p>
                            <p><span className="text-muted-foreground">{t('End:')}</span> {booking.end_date} — {booking.end_time}</p>
                        </div>
                    </div>

                    {/* Details table */}
                    <Table className="mb-6">
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Vehicle')}</TableHead>
                                <TableHead>{booking.vehicle_name}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow>
                                <TableCell>{t('Duration')}</TableCell>
                                <TableCell>{booking.duration}</TableCell>
                            </TableRow>
                            {booking.addons?.map((a) => (
                                <TableRow key={a.id}>
                                    <TableCell>{a.name}</TableCell>
                                    <TableCell>{a.price} Dh</TableCell>
                                </TableRow>
                            ))}
                            <TableRow>
                                <TableCell>{t('Pickup Address')}</TableCell>
                                <TableCell>
                                    {booking.pickup_address
                                        ? `${booking.pickup_address.name}${booking.pickup_address.price ? ` (${booking.pickup_address.price} Dh)` : ''}`
                                        : '—'}
                                </TableCell>
                            </TableRow>
                            <TableRow>
                                <TableCell>{t('Drop Off Address')}</TableCell>
                                <TableCell>
                                    {booking.drop_off_address
                                        ? `${booking.drop_off_address.name}${booking.drop_off_address.price ? ` (${booking.drop_off_address.price} Dh)` : ''}`
                                        : '—'}
                                </TableCell>
                            </TableRow>
                            <TableRow>
                                <TableCell>{t('Payment Status')}</TableCell>
                                <TableCell>
                                    <Badge variant={PAYMENT_VARIANT[booking.payment_status] ?? 'secondary'}>
                                        {booking.payment_status_label}
                                    </Badge>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    {/* Totals */}
                    <div className="ms-auto w-full max-w-xs">
                        <Table>
                            <TableBody>
                                <TableRow>
                                    <TableCell>{t('Total Amount (HT)')}</TableCell>
                                    <TableCell className="text-end">{booking.total_ht} Dh</TableCell>
                                </TableRow>
                                <TableRow>
                                    <TableCell>{t('TVA (20%)')}</TableCell>
                                    <TableCell className="text-end">{booking.tva_amount} Dh</TableCell>
                                </TableRow>
                                <TableRow>
                                    <TableCell>{t('Rest')}</TableCell>
                                    <TableCell className="text-end">{Number(booking.due_amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} Dh</TableCell>
                                </TableRow>
                                <TableRow>
                                    <TableCell className="font-semibold">{t('Due Amount (TTC)')}</TableCell>
                                    <TableCell className="text-end font-semibold">{booking.total_amount} Dh</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

            {/* Payment History */}
            <Card className="print:border-0 print:shadow-none">
                <CardHeader>
                    <CardTitle>{t('Payment History')}</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Date')}</TableHead>
                                <TableHead>{t('Method')}</TableHead>
                                <TableHead>{t('Notes')}</TableHead>
                                <TableHead>{t('Amount')}</TableHead>
                                {can('delete booking payment') && (
                                    <TableHead className="text-end">{t('Action')}</TableHead>
                                )}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {booking.payments?.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={5} className="text-center text-muted-foreground py-6">
                                        {t('No payments yet')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {booking.payments?.map((p) => (
                                <TableRow key={p.id}>
                                    <TableCell>{p.date}</TableCell>
                                    <TableCell>{p.payment_method}</TableCell>
                                    <TableCell>{p.notes}</TableCell>
                                    <TableCell>{p.amount} Dh</TableCell>
                                    {can('delete booking payment') && (
                                        <TableCell className="text-end">
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                className="text-destructive hover:text-destructive"
                                                onClick={() => deletePayment(p.id)}
                                                aria-label={t('Delete payment')}
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
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

BookingShow.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Bookings', href: route('booking.index') },
        { label: 'Details' },
    ]}>{page}</AdminLayout>
);
export default BookingShow;
