import { z } from 'zod';
import { Controller } from 'react-hook-form';
import { Link } from '@inertiajs/react';
import { useZodForm } from '@/hooks/useZodForm';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import FieldError from '@/components/FieldError';
import { fieldA11y } from '@/lib/fieldA11y';

const schema = z.object({
    name: z.string().min(1, 'The name field is required.'),
    type: z.string().min(1, 'The type field is required.'),
    vehicle: z.string().min(1, 'The vehicle field is required.'),
    reminder_date: z.string().min(1, 'The reminder date field is required.'),
    note: z.string().optional(),
});

function ReminderCreate({ vehicles = {}, types = {} }) {
    const t = useTranslation();
    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            name: '',
            type: '',
            vehicle: '',
            reminder_date: '',
            note: '',
        },
    });
    const { register, control, formState: { errors, isSubmitting } } = form;

    return (
        <div className="space-y-6 p-6">
            <form onSubmit={submit('post', route('reminder.store'))} className="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>{t('Create Reminder')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div className="space-y-1.5">
                                <Label htmlFor="name">{t('Name')}</Label>
                                <Input id="name" placeholder={t('Enter name')} {...register('name')} {...fieldA11y(errors, 'name')} />
                                <FieldError name="name" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="type">{t('Reminder Type')}</Label>
                                <Controller
                                    name="type"
                                    control={control}
                                    render={({ field }) => (
                                        <Select value={field.value} onValueChange={field.onChange}>
                                            <SelectTrigger id="type" {...fieldA11y(errors, 'type')}><SelectValue placeholder={t('Select Type')} /></SelectTrigger>
                                            <SelectContent>
                                                {Object.entries(types).filter(([k]) => k !== '').map(([k, label]) => (
                                                    <SelectItem key={k} value={String(k)}>{label}</SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    )}
                                />
                                <FieldError name="type" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="vehicle">{t('Vehicle')}</Label>
                                <Controller
                                    name="vehicle"
                                    control={control}
                                    render={({ field }) => (
                                        <Select value={field.value} onValueChange={field.onChange}>
                                            <SelectTrigger id="vehicle" {...fieldA11y(errors, 'vehicle')}><SelectValue placeholder={t('Select Vehicle')} /></SelectTrigger>
                                            <SelectContent>
                                                {Object.entries(vehicles).filter(([k]) => k !== '').map(([k, label]) => (
                                                    <SelectItem key={k} value={String(k)}>{label}</SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    )}
                                />
                                <FieldError name="vehicle" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="reminder_date">{t('Reminder Date')}</Label>
                                <Input id="reminder_date" type="date" {...register('reminder_date')} {...fieldA11y(errors, 'reminder_date')} />
                                <FieldError name="reminder_date" errors={errors} />
                            </div>

                            <div className="space-y-1.5 md:col-span-2">
                                <Label htmlFor="note">{t('Note')}</Label>
                                <Textarea id="note" placeholder={t('Enter note')} rows={3} {...register('note')} />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div className="flex justify-end gap-2">
                    <Button variant="ghost" type="button" asChild>
                        <Link href={route('reminder.index')}>{t('Close')}</Link>
                    </Button>
                    <Button type="submit" disabled={isSubmitting}>{t('Create')}</Button>
                </div>
            </form>
        </div>
    );
}

ReminderCreate.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Reminders', href: route('reminder.index') },
        { label: 'Create' },
    ]}>{page}</AdminLayout>
);
export default ReminderCreate;
