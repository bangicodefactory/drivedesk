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

// Port of resources/views/vehicle_type/edit.blade.php (modal fragment ->
// full Inertia page). Submits PUT to route('vehicle-type.update') via a spoofed
// _method=PUT (matches the Blade @method('PUT')). Field names match 1:1
// (type, notes). The zod schema mirrors the controller's update() `type =>
// required` rule. Laravel validation stays authoritative. Prop name
// `vehicleType` matches the controller compact('vehicleType').
const schema = z.object({
    _method: z.string().optional(),
    type: z.string().min(1, 'The type field is required.'),
    notes: z.string().optional(),
});

function VehicleTypeEdit({ vehicleType = {} }) {
    const t = useTranslation();
    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            type: vehicleType.type ?? '',
            notes: vehicleType.notes ?? '',
            _method: 'PUT',
        },
    });
    const { register, formState: { errors, isSubmitting } } = form;

    return (
        <div className="space-y-6 p-6">
            <Card>
                <CardHeader>
                    <CardTitle>{t('Edit Vehicle Type')}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('vehicle-type.update', vehicleType.id))}>
                        <div className="grid grid-cols-1 gap-4">
                            <div className="space-y-1.5">
                                <Label htmlFor="type">{t('Type')}</Label>
                                <Input id="type" placeholder={t('Enter type')} {...register('type')} {...fieldA11y(errors, 'type')} />
                                <FieldError name="type" errors={errors} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="notes">{t('Notes')}</Label>
                                <Textarea id="notes" placeholder={t('Enter notes')} rows={2} {...register('notes')} {...fieldA11y(errors, 'notes')} />
                                <FieldError name="notes" errors={errors} />
                            </div>
                        </div>

                        <div className="flex justify-end gap-2 mt-4">
                            <Button variant="ghost" type="button" asChild>
                                <Link href={route('vehicle-type.index')}>{t('Close')}</Link>
                            </Button>
                            <Button type="submit" disabled={isSubmitting}>{t('Update')}</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

VehicleTypeEdit.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Vehicle Type', href: route('vehicle-type.index') },
        { label: 'Edit' },
    ]}>{page}</AdminLayout>
);
export default VehicleTypeEdit;
