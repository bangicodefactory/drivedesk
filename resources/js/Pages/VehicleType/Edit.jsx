import { z } from 'zod';
import { Link } from '@inertiajs/react';
import { useZodForm } from '@/hooks/useZodForm';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/Layouts/AdminLayout';

// Port of resources/views/vehicle_type/edit.blade.php (modal fragment ->
// full Inertia page). Submits PUT to route('vehicle-type.update') via a spoofed
// _method=PUT (matches the Blade @method('PUT')). Field names match 1:1
// (type, notes). The zod schema mirrors the controller's update() `type =>
// required` rule. Laravel validation stays authoritative. Prop name
// `vehicleType` matches the controller compact('vehicleType').
const schema = z.object({
    type: z.string().min(1, 'The type field is required.'),
    notes: z.string().optional(),
});

function VehicleTypeEdit({ vehicleType = {} }) {
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
                    <CardTitle>Edit Vehicle Type</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('vehicle-type.update', vehicleType.id))}>
                        <div className="grid grid-cols-1 gap-4">
                            <div className="space-y-1.5">
                                <Label htmlFor="type">Type</Label>
                                <Input id="type" placeholder="Enter type" {...register('type')} />
                                {errors.type && <p className="text-sm text-destructive">{errors.type.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="notes">Notes</Label>
                                <Textarea id="notes" placeholder="Enter notes" rows={2} {...register('notes')} />
                                {errors.notes && <p className="text-sm text-destructive">{errors.notes.message}</p>}
                            </div>
                        </div>

                        <div className="flex justify-end gap-2 mt-4">
                            <Button variant="ghost" type="button" asChild>
                                <Link href={route('vehicle-type.index')}>Close</Link>
                            </Button>
                            <Button type="submit" disabled={isSubmitting}>Update</Button>
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
