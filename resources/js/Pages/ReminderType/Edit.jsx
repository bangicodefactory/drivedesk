import { z } from 'zod';
import { Link } from '@inertiajs/react';
import { useZodForm } from '@/hooks/useZodForm';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import FieldError from '@/components/FieldError';
import { fieldA11y } from '@/lib/fieldA11y';

const schema = z.object({
    _method: z.string().optional(),
    type: z.string().min(1, 'The type field is required.'),
});

function ReminderTypeEdit({ reminderType = {} }) {
    const t = useTranslation();
    const { form, submit } = useZodForm(schema, {
        defaultValues: { type: reminderType.type ?? '', _method: 'PUT' },
    });
    const { register, formState: { errors, isSubmitting } } = form;

    return (
        <div className="space-y-6 p-6">
            <Card>
                <CardHeader>
                    <CardTitle>{t('Edit Reminder Type')}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('reminder-type.update', reminderType.id))}>
                        <div className="grid grid-cols-1 gap-4">
                            <div className="space-y-1.5">
                                <Label htmlFor="type">{t('Type')}</Label>
                                <Input id="type" placeholder={t('Enter type')} {...register('type')} {...fieldA11y(errors, 'type')} />
                                <FieldError name="type" errors={errors} />
                            </div>
                        </div>

                        <div className="flex justify-end gap-2 mt-4">
                            <Button variant="ghost" type="button" asChild>
                                <Link href={route('reminder-type.index')}>{t('Close')}</Link>
                            </Button>
                            <Button type="submit" disabled={isSubmitting}>{t('Update')}</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

ReminderTypeEdit.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Reminder Type', href: route('reminder-type.index') },
        { label: 'Edit' },
    ]}>{page}</AdminLayout>
);
export default ReminderTypeEdit;
