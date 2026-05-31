import { Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Pencil, Trash2, Plus, MapPin } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

// Port of resources/views/place/index.blade.php.
// Action buttons are gated by the shared auth.permissions slugs, mirroring the
// Blade @can('edit place') / @can('delete place') / Gate::check('manage place')
// guards. Each place row carries a `price_formatted` prop produced by
// priceFormat() on the server (same as the Blade priceFormat($place->price)).
function PlaceIndex({ places = [] }) {
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    function remove(id) {
        if (window.confirm('Are you sure?')) {
            router.delete(route('place.destroy', id));
        }
    }

    const showActions = can('edit place') || can('delete place');

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-semibold flex items-center gap-2">
                    <MapPin className="h-6 w-6" /> Place
                </h1>
                {can('manage place') && (
                    <Button size="sm" asChild>
                        <Link href={route('place.create')}>
                            <Plus className="mr-2 h-4 w-4" /> Create Place
                        </Link>
                    </Button>
                )}
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>All Places</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>City</TableHead>
                                <TableHead>Island</TableHead>
                                <TableHead>Price</TableHead>
                                <TableHead>Depo name</TableHead>
                                <TableHead>Depo address</TableHead>
                                {showActions && <TableHead className="text-right">Action</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {places.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={7} className="text-center text-muted-foreground py-8">
                                        No places yet
                                    </TableCell>
                                </TableRow>
                            )}
                            {places.map((p) => (
                                <TableRow key={p.id}>
                                    <TableCell>{p.name}</TableCell>
                                    <TableCell>{p.city}</TableCell>
                                    <TableCell>{p.island}</TableCell>
                                    <TableCell>{p.price_formatted ?? p.price}</TableCell>
                                    <TableCell>{p.depo_name ? p.depo_name : '-'}</TableCell>
                                    <TableCell>{p.depo_address ? p.depo_address : '-'}</TableCell>
                                    {showActions && (
                                        <TableCell className="text-right space-x-1">
                                            {can('edit place') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('place.edit', p.id)} aria-label="Edit">
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('delete place') && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="text-destructive hover:text-destructive"
                                                    onClick={() => remove(p.id)}
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

PlaceIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Place' }]}>{page}</AdminLayout>
);
export default PlaceIndex;
