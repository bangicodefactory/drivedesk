import { useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';

function CreditEdit({ credit, drivers = [], statuses = {} }) {
    const t = useTranslation();
    const { data, setData, put, processing, errors } = useForm({
        driver_id: String(credit.driver_id),
        amount: credit.amount,
        status: credit.status,
        credit_date: credit.credit_date ?? '',
    });

    function submit(e) {
        e.preventDefault();
        put(route('credit.update', credit.id));
    }

    return (
        <div className="p-6 max-w-lg">
            <Card>
                <CardHeader><CardTitle>{t('Edit Credit')}</CardTitle></CardHeader>
                <CardContent>
                    <form onSubmit={submit} className="space-y-4">
                        <div className="space-y-1">
                            <Label>{t('Driver')}</Label>
                            <Select value={data.driver_id} onValueChange={(v) => setData('driver_id', v)}>
                                <SelectTrigger><SelectValue placeholder={t('Select driver…')} /></SelectTrigger>
                                <SelectContent>
                                    {drivers.map((d) => (
                                        <SelectItem key={d.id} value={String(d.id)}>{d.name}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.driver_id && <p className="text-sm text-destructive">{errors.driver_id}</p>}
                        </div>

                        <div className="space-y-1">
                            <Label>{t('Amount (Dh)')}</Label>
                            <Input type="number" step="0.01" min="0" value={data.amount} onChange={(e) => setData('amount', e.target.value)} />
                            {errors.amount && <p className="text-sm text-destructive">{errors.amount}</p>}
                        </div>

                        <div className="space-y-1">
                            <Label>{t('Status')}</Label>
                            <Select value={data.status} onValueChange={(v) => setData('status', v)}>
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    {Object.entries(statuses).map(([val, label]) => (
                                        <SelectItem key={val} value={val}>{label}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.status && <p className="text-sm text-destructive">{errors.status}</p>}
                        </div>

                        <div className="space-y-1">
                            <Label>{t('Date')}</Label>
                            <Input type="date" value={data.credit_date} onChange={(e) => setData('credit_date', e.target.value)} />
                            {errors.credit_date && <p className="text-sm text-destructive">{errors.credit_date}</p>}
                        </div>

                        <div className="flex gap-2 pt-2">
                            <Button type="submit" disabled={processing}>{t('Update')}</Button>
                            <Button type="button" variant="outline" onClick={() => window.history.back()}>{t('Cancel')}</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

CreditEdit.layout = (page) => {
    return (
        <AdminLayout breadcrumbs={[{ label: 'Credits', href: route('credit.index') }, { label: 'Edit' }]}>{page}</AdminLayout>
    );
};
export default CreditEdit;
