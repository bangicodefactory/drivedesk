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

// Port of resources/views/inspection_type/edit.blade.php.
// Submits PUT to route('inspection-type.update') via a spoofed _method=PUT
// (matches the Blade @method('PUT')). Field name matches the Blade form 1:1
// (type). The zod schema mirrors the controller's update() `required` rule
// (type) for UX only; Laravel validation stays authoritative and its errors
// surface via setError inside useZodForm. Prop `inspectionType` matches the
// controller compact('inspectionType').
const schema = z.object({
    _method: z.string().optional(),
    type: z.string().min(1, 'The type field is required.'),
});

function InspectionTypeEdit({ inspectionType = {} }) {
    const t = useTranslation();
    const { form, submit } = useZodForm(schema, {
        defaultValues: { type: inspectionType.type ?? '', _method: 'PUT' },
    });
    const { register, formState: { errors, isSubmitting } } = form;

    return (
        <div className="space-y-6 p-6">
            <Card>
                <CardHeader>
                    <CardTitle>{t('Edit Type')}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('inspection-type.update', inspectionType.id))}>
                        <div className="grid grid-cols-1 gap-4">
                            <div className="space-y-1.5">
                                <Label htmlFor="type">{t('Type')}</Label>
                                <Input id="type" placeholder={t('Enter type')} {...register('type')} {...fieldA11y(errors, 'type')} />
                                <FieldError name="type" errors={errors} />
                            </div>
                        </div>

                        <div className="flex justify-end gap-2 mt-4">
                            <Button variant="ghost" type="button" asChild>
                                <Link href={route('inspection-type.index')}>{t('Close')}</Link>
                            </Button>
                            <Button type="submit" disabled={isSubmitting}>{t('Update')}</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

InspectionTypeEdit.layout = (page) => {
    return (
        <AdminLayout breadcrumbs={[
            { label: 'Inspection Type', href: route('inspection-type.index') },
            { label: 'Edit' },
        ]}>{page}</AdminLayout>
    );
};
export default InspectionTypeEdit;
