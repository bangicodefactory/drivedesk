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

// Port of resources/views/inspection/edit.blade.php.
// Submits multipart PUT to route('inspection.update') via a spoofed _method=PUT
// (matches the Blade @method('PUT')). Field names match the Blade form 1:1.
// The zod schema mirrors the controller's update() `required` rules for UX only;
// Laravel validation stays authoritative and its errors surface via setError.
// Props inspection/vehicles/status/repairStatus/types/details match the
// controller compact('inspection','vehicles','status','repairStatus','fuelLevel','types','details').
const schema = z.object({
    _method: z.string().optional(),
    vehicle: z.string().min(1, 'The vehicle field is required.'),
    inspector: z.string().min(1, 'The inspector field is required.'),
    inspection_date: z.string().min(1, 'The inspection date field is required.'),
    incoming_date: z.string().min(1, 'The incoming date field is required.'),
    meter_reading_incoming: z.string().min(1, 'The meter reading incoming field is required.'),
    status: z.string().min(1, 'The status field is required.'),
    repair_status: z.string().min(1, 'The repair status field is required.'),
    amount: z.string().min(1, 'The amount field is required.'),
    notes: z.string().optional(),
    receipt: z.any().optional(),
    types: z.any().optional(),
});

const str = (v) => (v != null ? String(v) : '');

function InspectionEdit({
    inspection = {}, vehicles = {}, status = {}, repairStatus = {}, types = [], details = {},
}) {
    // Rebuild the nested types[id] map from `details`. The Blade checkbox is
    // checked when details[id]['type'] is non-empty; carry the same flag as 'on'.
    const initialTypes = {};
    Object.entries(details).forEach(([id, detail]) => {
        initialTypes[id] = {
            type: detail.type ? 'on' : '',
            note: detail.note ?? '',
        };
    });

    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            vehicle: str(inspection.vehicle),
            inspector: inspection.inspector ?? '',
            inspection_date: inspection.inspection_date ?? '',
            status: str(inspection.status),
            repair_status: str(inspection.repair_status),
            notes: inspection.notes ?? '',
            amount: str(inspection.amount),
            receipt: null,
            incoming_date: inspection.incoming_date ?? '',
            meter_reading_incoming: str(inspection.meter_reading_incoming),
            types: initialTypes,
            _method: 'PUT',
        },
    });
    const { register, control, setValue, watch, formState: { errors, isSubmitting } } = form;

    const checklist = watch('types') ?? {};

    function setChecklist(id, key, value) {
        const current = watch('types') ?? {};
        setValue('types', {
            ...current,
            [id]: { ...(current[id] ?? {}), [key]: value },
        });
    }

    return (
        <div className="space-y-6 p-6">
            <form onSubmit={submit('post', route('inspection.update', inspection.id), { forceFormData: true })} className="space-y-6">
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Inspection Details</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div className="space-y-1.5">
                                    <Label htmlFor="vehicle">Vehicle</Label>
                                    <Controller
                                        name="vehicle"
                                        control={control}
                                        render={({ field }) => (
                                            <Select value={field.value} onValueChange={field.onChange}>
                                                <SelectTrigger id="vehicle"><SelectValue placeholder="Select Vehicle" /></SelectTrigger>
                                                <SelectContent>
                                                    {Object.entries(vehicles).filter(([k]) => k !== '').map(([k, label]) => (
                                                        <SelectItem key={k} value={String(k)}>{label}</SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        )}
                                    />
                                    {errors.vehicle && <p className="text-sm text-destructive">{errors.vehicle.message}</p>}
                                </div>

                                <div className="space-y-1.5">
                                    <Label htmlFor="inspector">Inspection By</Label>
                                    <Input id="inspector" {...register('inspector')} />
                                    {errors.inspector && <p className="text-sm text-destructive">{errors.inspector.message}</p>}
                                </div>

                                <div className="space-y-1.5">
                                    <Label htmlFor="inspection_date">Inspection Date</Label>
                                    <Input id="inspection_date" type="date" {...register('inspection_date')} />
                                    {errors.inspection_date && <p className="text-sm text-destructive">{errors.inspection_date.message}</p>}
                                </div>

                                <div className="space-y-1.5">
                                    <Label htmlFor="status">Inspection Status</Label>
                                    <Controller
                                        name="status"
                                        control={control}
                                        render={({ field }) => (
                                            <Select value={field.value} onValueChange={field.onChange}>
                                                <SelectTrigger id="status"><SelectValue placeholder="Inspection Status" /></SelectTrigger>
                                                <SelectContent>
                                                    {Object.entries(status).map(([k, label]) => (
                                                        <SelectItem key={k} value={String(k)}>{label}</SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        )}
                                    />
                                    {errors.status && <p className="text-sm text-destructive">{errors.status.message}</p>}
                                </div>

                                <div className="space-y-1.5">
                                    <Label htmlFor="repair_status">Repair Status</Label>
                                    <Controller
                                        name="repair_status"
                                        control={control}
                                        render={({ field }) => (
                                            <Select value={field.value} onValueChange={field.onChange}>
                                                <SelectTrigger id="repair_status"><SelectValue placeholder="Repair Status" /></SelectTrigger>
                                                <SelectContent>
                                                    {Object.entries(repairStatus).map(([k, label]) => (
                                                        <SelectItem key={k} value={String(k)}>{label}</SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        )}
                                    />
                                    {errors.repair_status && <p className="text-sm text-destructive">{errors.repair_status.message}</p>}
                                </div>

                                <div className="space-y-1.5">
                                    <Label htmlFor="notes">Notes</Label>
                                    <Textarea id="notes" placeholder="Enter notes" rows={2} {...register('notes')} />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <div className="space-y-6">
                        <Card>
                            <CardContent className="pt-6">
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div className="space-y-1.5">
                                        <Label htmlFor="amount">Amount</Label>
                                        <Input id="amount" type="number" placeholder="Enter amount" {...register('amount')} />
                                        {errors.amount && <p className="text-sm text-destructive">{errors.amount.message}</p>}
                                    </div>
                                    <div className="space-y-1.5">
                                        <Label htmlFor="receipt">Receipt</Label>
                                        <Input id="receipt" type="file" onChange={(e) => setValue('receipt', e.target.files?.[0] ?? null)} />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Incoming Details</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div className="space-y-1.5">
                                        <Label htmlFor="incoming_date">Date</Label>
                                        <Input id="incoming_date" type="date" {...register('incoming_date')} />
                                        {errors.incoming_date && <p className="text-sm text-destructive">{errors.incoming_date.message}</p>}
                                    </div>
                                    <div className="space-y-1.5">
                                        <Label htmlFor="meter_reading_incoming">Meter Reading (km)</Label>
                                        <Input
                                            id="meter_reading_incoming"
                                            type="number"
                                            placeholder="Enter meter reading incoming (km)"
                                            {...register('meter_reading_incoming')}
                                        />
                                        {errors.meter_reading_incoming && <p className="text-sm text-destructive">{errors.meter_reading_incoming.message}</p>}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Inspections Checklist</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            {types.map((type) => (
                                <div key={type.id} className="space-y-1.5">
                                    <h6 className="text-sm font-medium">{type.type}</h6>
                                    <div className="flex items-center gap-3">
                                        <input
                                            type="checkbox"
                                            checked={checklist[type.id]?.type === 'on'}
                                            onChange={(e) => setChecklist(type.id, 'type', e.target.checked ? 'on' : '')}
                                        />
                                        <Input
                                            type="text"
                                            placeholder="Enter notes"
                                            autoComplete="off"
                                            value={checklist[type.id]?.note ?? ''}
                                            onChange={(e) => setChecklist(type.id, 'note', e.target.value)}
                                        />
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                <div className="flex justify-end gap-2">
                    <Button variant="ghost" type="button" asChild>
                        <Link href={route('inspection.index')}>Close</Link>
                    </Button>
                    <Button type="submit" disabled={isSubmitting}>Update</Button>
                </div>
            </form>
        </div>
    );
}

InspectionEdit.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Inspection', href: route('inspection.index') },
        { label: 'Edit' },
    ]}>{page}</AdminLayout>
);
export default InspectionEdit;
