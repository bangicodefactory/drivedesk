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
import FieldError from '@/components/FieldError';
import { fieldA11y } from '@/lib/fieldA11y';

const schema = z.object({
    _method: z.string().optional(),
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

// occurred_at arrives as an ISO-ish string; the form edits it as a date and a
// time field, which is what the controller recombines on the way back in.
function splitOccurredAt(value) {
    const raw = String(value ?? '').replace('T', ' ');
    return { date: raw.slice(0, 10), time: raw.slice(11, 16) };
}

function TrafficViolationEdit({ violation = {} }) {
    const t = useTranslation();
    const occurred = splitOccurredAt(violation.occurred_at);

    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            _method: 'PUT',
            license_plate: violation.license_plate ?? '',
            occurred_date: occurred.date,
            occurred_time: occurred.time,
            reference: violation.reference ?? '',
            authority: violation.authority ?? '',
            notice_date: violation.notice_date ? String(violation.notice_date).slice(0, 10) : '',
            location: violation.location ?? '',
            description: violation.description ?? '',
            amount: violation.amount != null ? String(violation.amount) : '',
            notes: violation.notes ?? '',
            document: null,
        },
    });
    const { register, setValue, formState: { errors, isSubmitting } } = form;

    return (
        <div className="space-y-6 p-6">
            <form
                onSubmit={submit('post', route('traffic-violation.update', violation.id), { forceFormData: true })}
                className="space-y-6"
            >
                <Card>
                    <CardHeader>
                        <CardTitle>{t('Edit Traffic Violation')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="mb-4 text-sm text-muted-foreground">
                            {t('Changing the plate or the time re-runs the match, unless the rental was assigned by hand.')}
                        </p>

                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div className="space-y-1.5">
                                <Label htmlFor="license_plate">{t('License Plate')}</Label>
                                <Input id="license_plate" placeholder={t('Enter license plate')} {...register('license_plate')} {...fieldA11y(errors, 'license_plate')} />
                                <FieldError name="license_plate" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="reference">{t('Reference')}</Label>
                                <Input id="reference" placeholder={t('Enter notice number')} {...register('reference')} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="occurred_date">{t('Violation Date')}</Label>
                                <Input id="occurred_date" type="date" {...register('occurred_date')} {...fieldA11y(errors, 'occurred_date')} />
                                <FieldError name="occurred_date" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="occurred_time">{t('Violation Time')}</Label>
                                <Input id="occurred_time" type="time" {...register('occurred_time')} {...fieldA11y(errors, 'occurred_time')} />
                                <FieldError name="occurred_time" errors={errors} />
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
                                <Input id="amount" type="number" step="0.01" placeholder={t('Enter amount')} {...register('amount')} {...fieldA11y(errors, 'amount')} />
                                <FieldError name="amount" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="document">{t('Document')}</Label>
                                <Input
                                    id="document"
                                    type="file"
                                    onChange={(e) => setValue('document', e.target.files?.[0] ?? null)}
                                />
                                {violation.document && (
                                    <a
                                        href={`/storage/upload/violation/${violation.document}`}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="text-sm text-primary underline"
                                    >
                                        {t('View')}
                                    </a>
                                )}
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
                        <Link href={route('traffic-violation.show', violation.id)}>{t('Close')}</Link>
                    </Button>
                    <Button type="submit" disabled={isSubmitting}>{t('Update')}</Button>
                </div>
            </form>
        </div>
    );
}

TrafficViolationEdit.layout = (page) => {
    return (
        <AdminLayout breadcrumbs={[
            { label: 'Traffic Violations', href: route('traffic-violation.index') },
            { label: 'Edit' },
        ]}>{page}</AdminLayout>
    );
};
export default TrafficViolationEdit;
