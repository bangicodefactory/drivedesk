import { Link, router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Pencil, Trash2, ArrowLeft } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';

const STATUS_VARIANT = { 'payé': 'success', 'non payé': 'destructive' };

function CreditShow({ credit, driver, credits = [], chartStatus, chartByMonth }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    async function remove(id) {
        if (await confirmDialog({ title: t('Delete this credit?') })) {
            router.delete(route('credit.destroy', id));
        }
    }

    const paidTotal = credits.filter((c) => c.status === 'payé').reduce((s, c) => s + Number(c.amount), 0);
    const unpaidTotal = credits.filter((c) => c.status === 'non payé').reduce((s, c) => s + Number(c.amount), 0);

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center gap-3">
                <Button variant="ghost" size="icon" asChild>
                    <Link href={route('credit.index')}><ArrowLeft className="h-4 w-4" /></Link>
                </Button>
                <h1 className="text-3xl font-bold tracking-tight">{t('Credits')} — {driver?.name}</h1>
            </div>

            <div className="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader><CardTitle>{t('Summary')}</CardTitle></CardHeader>
                    <CardContent className="space-y-2 text-sm">
                        <div className="flex justify-between"><span className="text-muted-foreground">{t('Driver')}</span><span className="font-medium">{driver?.name}</span></div>
                        <div className="flex justify-between"><span className="text-muted-foreground">{t('Total paid')}</span><span className="font-medium text-green-600">{paidTotal.toFixed(2)} Dh</span></div>
                        <div className="flex justify-between"><span className="text-muted-foreground">{t('Total unpaid')}</span><span className="font-medium text-destructive">{unpaidTotal.toFixed(2)} Dh</span></div>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader><CardTitle>{t('Credit History')}</CardTitle></CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Date')}</TableHead>
                                <TableHead>{t('Amount')}</TableHead>
                                <TableHead>{t('Status')}</TableHead>
                                <TableHead className="text-end">{t('Action')}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {credits.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={4} className="text-center text-muted-foreground py-8">{t('No credits')}</TableCell>
                                </TableRow>
                            )}
                            {credits.map((c) => (
                                <TableRow key={c.id} className={c.id === credit.id ? 'bg-muted/40' : ''}>
                                    <TableCell>{c.credit_date ?? '—'}</TableCell>
                                    <TableCell>{Number(c.amount).toFixed(2)} Dh</TableCell>
                                    <TableCell>
                                        <Badge variant={STATUS_VARIANT[c.status] ?? 'secondary'}>{t(c.status)}</Badge>
                                    </TableCell>
                                    <TableCell className="text-end space-x-1">
                                        <Button variant="ghost" size="icon" asChild>
                                            <Link href={route('credit.edit', c.id)} aria-label={t('Edit')}><Pencil className="h-4 w-4" /></Link>
                                        </Button>
                                        <Button
                                            variant="ghost" size="icon"
                                            className="text-destructive hover:text-destructive"
                                            onClick={() => remove(c.id)}
                                            aria-label={t('Delete')}
                                        >
                                            <Trash2 className="h-4 w-4" />
                                        </Button>
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

CreditShow.layout = (page) => {
    return (
        <AdminLayout breadcrumbs={[{ label: 'Credits', href: route('credit.index') }, { label: 'Details' }]}>{page}</AdminLayout>
    );
};
export default CreditShow;
