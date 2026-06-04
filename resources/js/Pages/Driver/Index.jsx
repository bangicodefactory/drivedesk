import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Eye, Pencil, Trash2, Plus, Users, Search } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

// Port of resources/views/driver/index.blade.php.
// Action buttons are gated by the shared auth.permissions slugs, mirroring the
// Blade @can('show|edit|delete driver') / Gate::check('manage|create driver')
// guards. Prop name `drivers` matches the controller compact('drivers').
function DriverIndex({ drivers = [] }) {
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    function remove(id) {
        if (window.confirm('Are you sure?')) {
            router.delete(route('driver.destroy', id));
        }
    }

    const showActions = can('show driver') || can('edit driver') || can('delete driver');

    const [query, setQuery] = useState('');
    const q = query.trim().toLowerCase();
    const filtered = q
        ? drivers.filter((d) =>
            [d.name, d.email, d.phone_number, d.license_number, d.driver_id_display]
                .some((v) => String(v ?? '').toLowerCase().includes(q)))
        : drivers;

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-semibold flex items-center gap-2">
                    <Users className="h-6 w-6" /> Driver
                </h1>
                {can('manage driver') && (
                    <Button size="sm" asChild>
                        <Link href={route('driver.create')}>
                            <Plus className="mr-2 h-4 w-4" /> Create Driver
                        </Link>
                    </Button>
                )}
            </div>

            <Card>
                <CardHeader className="flex flex-row items-center justify-between gap-4 space-y-0">
                    <CardTitle>All Drivers</CardTitle>
                    <div className="relative w-full max-w-xs">
                        <Search className="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder="Search drivers…"
                            className="pl-8"
                        />
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>ID</TableHead>
                                <TableHead>Driver</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Phone Number</TableHead>
                                <TableHead>License Number</TableHead>
                                <TableHead>Issue Date</TableHead>
                                <TableHead>Expiration Date</TableHead>
                                {showActions && <TableHead className="text-right">Action</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {filtered.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={8} className="text-center text-muted-foreground py-8">
                                        {drivers.length === 0 ? 'No drivers yet' : 'No drivers match your search'}
                                    </TableCell>
                                </TableRow>
                            )}
                            {filtered.map((d) => (
                                <TableRow key={d.id}>
                                    <TableCell className="font-mono text-sm">{d.driver_id_display ?? '-'}</TableCell>
                                    <TableCell>{d.name}</TableCell>
                                    <TableCell>{d.email || '-'}</TableCell>
                                    <TableCell>{d.phone_number || '-'}</TableCell>
                                    <TableCell>{d.license_number || '-'}</TableCell>
                                    <TableCell>{d.issue_date_display ?? '-'}</TableCell>
                                    <TableCell>{d.expiration_date_display ?? '-'}</TableCell>
                                    {showActions && (
                                        <TableCell className="text-right space-x-1">
                                            {can('show driver') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('driver.show', d.id)} aria-label="Details">
                                                        <Eye className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('edit driver') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('driver.edit', d.id)} aria-label="Edit">
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('delete driver') && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="text-destructive hover:text-destructive"
                                                    onClick={() => remove(d.id)}
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

DriverIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Driver' }]}>{page}</AdminLayout>
);
export default DriverIndex;
