import { z } from 'zod';
import { Link } from '@inertiajs/react';
import { useZodForm } from '@/hooks/useZodForm';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';

// Module-level, per the useZodForm contract — an inline schema silently goes
// stale between renders. Client-side validation is UX only; the Laravel rules
// in TrafficViolationController remain the authoritative check.
const schema = z.object({
    license_plate: z.string().min(1, 'The license plate field is required.'),
    occurred_date: z.string().min(1, 'The date field is required.'),
    occurred_time: z.string().min(1, 'The time field is required.'),
    reference: z.string().optional(),
    authority: z.string().optional(),
    notice_date: z.string().optional(),
    location: z.string().optional(),
    description: z.string().optional(),
    amount: z.string().optional(),
    notes: z.string().optional(),
    document: z.any().optional(),
});

function TrafficViolationCreate() {
    const t = useTranslation();
    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            license_plate: '',
            occurred_date: '',
            occurred_time: '',
            reference: '',
            authority: '',
            notice_date: '',
            location: '',
            description: '',
            amount: '',
            notes: '',
            document: null,
        },
    });
    const { register, setValue, formState: { errors, isSubmitting } } = form;

    return (
        <div className="space-y-6 p-6">
            <form
                onSubmit={submit('post', route('traffic-violation.store'), { forceFormData: true })}
                className="space-y-6"
            >
                <Card>
                    <CardHeader>
                        <CardTitle>{t('Create Traffic Violation')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="mb-4 text-sm text-muted-foreground">
                            {t('The plate and the exact time are what identify the renter — enter them as printed on the notice.')}
                        </p>

                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div className="space-y-1.5">
                                <Label htmlFor="license_plate">{t('License Plate')}</Label>
                                <Input id="license_plate" placeholder={t('Enter license plate')} {...register('license_plate')} />
                                {errors.license_plate && <p className="text-sm text-destructive">{errors.license_plate.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="reference">{t('Reference')}</Label>
                                <Input id="reference" placeholder={t('Enter notice number')} {...register('reference')} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="occurred_date">{t('Violation Date')}</Label>
                                <Input id="occurred_date" type="date" {...register('occurred_date')} />
                                {errors.occurred_date && <p className="text-sm text-destructive">{errors.occurred_date.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="occurred_time">{t('Violation Time')}</Label>
                                <Input id="occurred_time" type="time" {...register('occurred_time')} />
                                {errors.occurred_time && <p className="text-sm text-destructive">{errors.occurred_time.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="authority">{t('Authority')}</Label>
                                <Input id="authority" placeholder={t('Enter issuing authority')} {...register('authority')} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="notice_date">{t('Notice Date')}</Label>
                                <Input id="notice_date" type="date" {...register('notice_date')} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="location">{t('Location')}</Label>
                                <Input id="location" placeholder={t('Enter location')} {...register('location')} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="amount">{t('Amount')}</Label>
                                <Input id="amount" type="number" step="0.01" placeholder={t('Enter amount')} {...register('amount')} />
                                {errors.amount && <p className="text-sm text-destructive">{errors.amount.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="document">{t('Document')}</Label>
                                <Input
                                    id="document"
                                    type="file"
                                    onChange={(e) => setValue('document', e.target.files?.[0] ?? null)}
                                />
                            </div>

                            <div className="space-y-1.5 md:col-span-2">
                                <Label htmlFor="description">{t('Description')}</Label>
                                <Input id="description" placeholder={t('Enter the offence')} {...register('description')} />
                            </div>

                            <div className="space-y-1.5 md:col-span-2">
                                <Label htmlFor="notes">{t('Notes')}</Label>
                                <Textarea id="notes" placeholder={t('Enter notes')} rows={3} {...register('notes')} />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div className="flex justify-end gap-2">
                    <Button variant="ghost" type="button" asChild>
                        <Link href={route('traffic-violation.index')}>{t('Close')}</Link>
                    </Button>
                    <Button type="submit" disabled={isSubmitting}>{t('Create')}</Button>
                </div>
            </form>
        </div>
    );
}

TrafficViolationCreate.layout = (page) => {
    return (
        <AdminLayout breadcrumbs={[
            { label: 'Traffic Violations', href: route('traffic-violation.index') },
            { label: 'Create' },
        ]}>{page}</AdminLayout>
    );
};
export default TrafficViolationCreate;
