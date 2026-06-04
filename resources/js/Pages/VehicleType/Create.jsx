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

// Port of resources/views/vehicle_type/create.blade.php (modal fragment ->
// full Inertia page). Field names match the Blade form 1:1 (type, notes).
// Posts to route('vehicle-type.store'). The zod schema mirrors the controller's
// `type => required` rule for UX only; Laravel validation stays authoritative
// and its errors surface via setError inside useZodForm.
const schema = z.object({
    type: z.string().min(1, 'The type field is required.'),
    notes: z.string().optional(),
});

function VehicleTypeCreate() {
    const t = useTranslation();
    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            type: '',
            notes: '',
        },
    });
    const { register, formState: { errors, isSubmitting } } = form;

    return (
        <div className="space-y-6 p-6">
            <Card>
                <CardHeader>
                    <CardTitle>{t('Create Type')}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('vehicle-type.store'))}>
                        <div className="grid grid-cols-1 gap-4">
                            <div className="space-y-1.5">
                                <Label htmlFor="type">{t('Type')}</Label>
                                <Input id="type" placeholder={t('Enter type')} {...register('type')} />
                                {errors.type && <p className="text-sm text-destructive">{errors.type.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="notes">{t('Notes')}</Label>
                                <Textarea id="notes" placeholder={t('Enter notes')} rows={2} {...register('notes')} />
                                {errors.notes && <p className="text-sm text-destructive">{errors.notes.message}</p>}
                            </div>
                        </div>

                        <div className="flex justify-end gap-2 mt-4">
                            <Button variant="ghost" type="button" asChild>
                                <Link href={route('vehicle-type.index')}>{t('Close')}</Link>
                            </Button>
                            <Button type="submit" disabled={isSubmitting}>{t('Create')}</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

VehicleTypeCreate.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Vehicle Type', href: route('vehicle-type.index') },
        { label: 'Create' },
    ]}>{page}</AdminLayout>
);
export default VehicleTypeCreate;
