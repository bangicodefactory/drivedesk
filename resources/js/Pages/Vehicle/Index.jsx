import { Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Eye, Pencil, Trash2, Plus, Car } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import Pagination from '@/components/Pagination';

// Port of resources/views/vehicle/index.blade.php.
// Action buttons are gated by the shared auth.permissions slugs, mirroring the
// Blade @can('show|edit|delete vehicle') / Gate::check('manage vehicle') guards.
function VehicleIndex({ vehicles = [] }) {
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    function remove(id) {
        if (window.confirm('Are you sure?')) {
            router.delete(route('vehicle.destroy', id));
        }
    }

    const showActions = can('show vehicle') || can('edit vehicle') || can('delete vehicle');

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-semibold flex items-center gap-2">
                    <Car className="h-6 w-6" /> Vehicle
                </h1>
                {can('manage vehicle') && (
                    <Button size="sm" asChild>
                        <Link href={route('vehicle.create')}>
                            <Plus className="mr-2 h-4 w-4" /> Create Vehicle
                        </Link>
                    </Button>
                )}
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>All Vehicles</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>ID</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Model</TableHead>
                                <TableHead>License Plate</TableHead>
                                <TableHead>Registration Expiration Date</TableHead>
                                <TableHead>Engine Type</TableHead>
                                {showActions && <TableHead className="text-right">Action</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {vehicles.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={8} className="text-center text-muted-foreground py-8">
                                        No vehicles yet
                                    </TableCell>
                                </TableRow>
                            )}
                            {vehicles.data.map((v) => (
                                <TableRow key={v.id}>
                                    <TableCell className="font-mono text-sm">{v.vehicle_id_display ?? v.vehicle_id}</TableCell>
                                    <TableCell>{v.name}</TableCell>
                                    <TableCell>{v.type_label ?? '-'}</TableCell>
                                    <TableCell>{v.model}</TableCell>
                                    <TableCell>{v.license_plate}</TableCell>
                                    <TableCell>{v.registration_expiry_date_display ?? '-'}</TableCell>
                                    <TableCell>{v.engine_type}</TableCell>
                                    {showActions && (
                                        <TableCell className="text-right space-x-1">
                                            {can('show vehicle') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('vehicle.show', v.id)} aria-label="Details">
                                                        <Eye className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('edit vehicle') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('vehicle.edit', v.id)} aria-label="Edit">
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('delete vehicle') && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="text-destructive hover:text-destructive"
                                                    onClick={() => remove(v.id)}
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
                    <Pagination paginator={vehicles} />
                </CardContent>
            </Card>
        </div>
    );
}

VehicleIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Vehicles' }]}>{page}</AdminLayout>
);
export default VehicleIndex;
