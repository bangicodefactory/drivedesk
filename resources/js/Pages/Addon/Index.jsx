import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Pencil, Trash2, Plus, Package, Search } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

// Port of resources/views/addon/index.blade.php.
// Action buttons are gated by the shared auth.permissions slugs, mirroring the
// Blade @can('edit|delete addon') / Gate::check('manage addon') guards.
// Prop name `addons` matches the controller compact('addons'). Each addon row
// shows the formatted price (price_formatted is appended by the controller to
// reproduce the Blade priceFormat($addon->price) helper output).
function AddonIndex({ addons = [] }) {
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    function remove(id) {
        if (window.confirm('Are you sure?')) {
            router.delete(route('addon.destroy', id));
        }
    }

    const showActions = can('edit addon') || can('delete addon');

    const [query, setQuery] = useState('');
    const q = query.trim().toLowerCase();
    const filtered = q
        ? addons.filter((addon) =>
            [addon.name, addon.price_formatted ?? addon.price, addon.billing_type]
                .some((v) => String(v ?? '').toLowerCase().includes(q)))
        : addons;

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-semibold flex items-center gap-2">
                    <Package className="h-6 w-6" /> Addon
                </h1>
                {can('manage addon') && (
                    <Button size="sm" asChild>
                        <Link href={route('addon.create')}>
                            <Plus className="mr-2 h-4 w-4" /> Create Addon
                        </Link>
                    </Button>
                )}
            </div>

            <Card>
                <CardHeader className="flex flex-row items-center justify-between gap-4 space-y-0">
                    <CardTitle>All Addons</CardTitle>
                    <div className="relative w-full max-w-xs">
                        <Search className="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder="Search addons…"
                            className="pl-8"
                        />
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Addon</TableHead>
                                <TableHead>Price</TableHead>
                                <TableHead>Billing Type</TableHead>
                                {showActions && <TableHead className="text-right">Action</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {filtered.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={showActions ? 4 : 3} className="text-center text-muted-foreground py-8">
                                        {addons.length === 0 ? 'No addons yet' : 'No addons match your search'}
                                    </TableCell>
                                </TableRow>
                            )}
                            {filtered.map((addon) => (
                                <TableRow key={addon.id}>
                                    <TableCell>{addon.name}</TableCell>
                                    <TableCell>{addon.price_formatted ?? addon.price}</TableCell>
                                    <TableCell>{addon.billing_type}</TableCell>
                                    {showActions && (
                                        <TableCell className="text-right space-x-1">
                                            {can('edit addon') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('addon.edit', addon.id)} aria-label="Edit">
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('delete addon') && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="text-destructive hover:text-destructive"
                                                    onClick={() => remove(addon.id)}
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

AddonIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Addon' }]}>{page}</AdminLayout>
);
export default AddonIndex;
