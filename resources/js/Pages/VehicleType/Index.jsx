import { Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Pencil, Trash2, Plus, Car } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

// Port of resources/views/vehicle_type/index.blade.php.
// Action buttons are gated by the shared auth.permissions slugs, mirroring the
// Blade Gate::check('manage vehicle type') / @can('edit|delete vehicle type')
// guards. Prop name `types` matches the controller compact('types').
function VehicleTypeIndex({ types = [] }) {
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    function remove(id) {
        if (window.confirm('Are you sure?')) {
            router.delete(route('vehicle-type.destroy', id));
        }
    }

    const showActions = can('edit vehicle type') || can('delete vehicle type');

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-semibold flex items-center gap-2">
                    <Car className="h-6 w-6" /> Vehicle Type
                </h1>
                {can('manage vehicle type') && (
                    <Button size="sm" asChild>
                        <Link href={route('vehicle-type.create')}>
                            <Plus className="mr-2 h-4 w-4" /> Create Type
                        </Link>
                    </Button>
                )}
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Vehicle Type</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Type</TableHead>
                                <TableHead>Notes</TableHead>
                                {showActions && <TableHead className="text-right">Action</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {types.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={3} className="text-center text-muted-foreground py-8">
                                        No vehicle types yet
                                    </TableCell>
                                </TableRow>
                            )}
                            {types.map((type) => (
                                <TableRow key={type.id}>
                                    <TableCell>{type.type}</TableCell>
                                    <TableCell>{type.notes}</TableCell>
                                    {showActions && (
                                        <TableCell className="text-right space-x-1">
                                            {can('edit vehicle type') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('vehicle-type.edit', type.id)} aria-label="Edit">
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('delete vehicle type') && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="text-destructive hover:text-destructive"
                                                    onClick={() => remove(type.id)}
                                                    aria-label="Delete"
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                            )}
                                        </TableCell>
                                    )}
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    );
}

VehicleTypeIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Vehicle Type' }]}>{page}</AdminLayout>
);
export default VehicleTypeIndex;
