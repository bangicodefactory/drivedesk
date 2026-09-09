import { useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { SearchableSelect } from '@/components/ui/searchable-select';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import FieldError from '@/components/FieldError';
import { fieldA11y } from '@/lib/fieldA11y';

function CreditCreate({ drivers = [], statuses = {} }) {
    const t = useTranslation();
    const { data, setData, post, processing, errors } = useForm({
        driver_id: '',
        amount: '',
        status: 'non payé',
        credit_date: new Date().toISOString().slice(0, 10),
    });

    function submit(e) {
        e.preventDefault();
        post(route('credit.store'));
    }

    return (
        <div className="p-6 max-w-lg">
            <Card>
                <CardHeader><CardTitle>{t('Add Credit')}</CardTitle></CardHeader>
                <CardContent>
                    <form onSubmit={submit} className="space-y-4">
                        <div className="space-y-1">
                            <Label>{t('Driver')}</Label>
                            <SearchableSelect
                                options={drivers.map((d) => ({ value: String(d.id), label: d.name }))}
                                value={data.driver_id}
                                onChange={(v) => setData('driver_id', v)}
                                placeholder={t('Select driver…')}
                                searchPlaceholder={t('Search driver…')}
                                ariaLabel={t('Driver')}
                                {...fieldA11y(errors, 'driver_id')}
                            />
                            <FieldError name="driver_id" errors={errors} />
                        </div>

                        <div className="space-y-1">
                            <Label>{t('Amount (Dh)')}</Label>
                            <Input type="number" step="0.01" min="0" value={data.amount} onChange={(e) => setData('amount', e.target.value)} {...fieldA11y(errors, 'amount')} />
                            <FieldError name="amount" errors={errors} />
                        </div>

                        <div className="space-y-1">
                            <Label>{t('Status')}</Label>
                            <Select value={data.status} onValueChange={(v) => setData('status', v)}>
                                <SelectTrigger {...fieldA11y(errors, 'status')}><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    {Object.entries(statuses).map(([val, label]) => (
                                        <SelectItem key={val} value={val}>{t(label)}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <FieldError name="status" errors={errors} />
                        </div>

                        <div className="space-y-1">
                            <Label>{t('Date')}</Label>
                            <Input type="date" value={data.credit_date} onChange={(e) => setData('credit_date', e.target.value)} {...fieldA11y(errors, 'credit_date')} />
                            <FieldError name="credit_date" errors={errors} />
                        </div>

                        <div className="flex gap-2 pt-2">
                            <Button type="submit" disabled={processing}>{t('Save')}</Button>
                            <Button type="button" variant="outline" onClick={() => window.history.back()}>{t('Cancel')}</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

CreditCreate.layout = (page) => {
    return (
        <AdminLayout breadcrumbs={[{ label: 'Credits', href: route('credit.index') }, { label: 'Add' }]}>{page}</AdminLayout>
    );
};
export default CreditCreate;
