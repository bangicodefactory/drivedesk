import { Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Pencil, Trash2, Plus, ListChecks } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

// Port of resources/views/inspection_type/index.blade.php.
// Action buttons are gated by the shared auth.permissions slugs, mirroring the
// Blade @can('edit inspection type') / @can('delete inspection type') /
// Gate::check('manage inspection type') guards. Prop name `types` matches the
// controller compact('types').
function InspectionTypeIndex({ types = [] }) {
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    function remove(id) {
        if (window.confirm('Are you sure?')) {
            router.delete(route('inspection-type.destroy', id));
        }
    }

    const showActions = can('edit inspection type') || can('delete inspection type');

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-semibold flex items-center gap-2">
                    <ListChecks className="h-6 w-6" /> Inspection Type
                </h1>
                {can('manage inspection type') && (
                    <Button size="sm" asChild>
                        <Link href={route('inspection-type.create')}>
                            <Plus className="mr-2 h-4 w-4" /> Create Type
                        </Link>
                    </Button>
                )}
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>All Inspection Types</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Type</TableHead>
                                {showActions && <TableHead className="text-right">Action</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {types.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={showActions ? 2 : 1} className="text-center text-muted-foreground py-8">
                                        No inspection types yet
                                    </TableCell>
                                </TableRow>
                            )}
                            {types.map((type) => (
                                <TableRow key={type.id}>
                                    <TableCell>{type.type}</TableCell>
                                    {showActions && (
                                        <TableCell className="text-right space-x-1">
                                            {can('edit inspection type') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('inspection-type.edit', type.id)} aria-label="Edit">
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('delete inspection type') && (
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

InspectionTypeIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Inspection Type' }]}>{page}</AdminLayout>
);
export default InspectionTypeIndex;
