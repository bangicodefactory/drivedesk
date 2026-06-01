import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Pencil, Trash2, Plus, Eye, CreditCard } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

const STATUS_VARIANT = {
    'payé': 'outline',
    'non payé': 'destructive',
};

function CreditIndex({ credits = [], drivers = [] }) {
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    const params = new URLSearchParams(window.location.search);
    const [driverFilter, setDriverFilter] = useState(params.get('driver_id') ?? '');

    function remove(id) {
        if (window.confirm('Delete this credit?')) {
            router.delete(route('credit.destroy', id));
        }
    }

    function filter(driverId) {
        setDriverFilter(driverId);
        router.get(route('credit.index'), driverId ? { driver_id: driverId } : {}, { preserveState: true, replace: true });
    }

    const showActions = can('manage driver');

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-semibold flex items-center gap-2">
                    <CreditCard className="h-6 w-6" /> Credits
                </h1>
                {can('manage driver') && (
                    <Button size="sm" asChild>
                        <Link href={route('credit.create')}>
                            <Plus className="mr-2 h-4 w-4" /> Add Credit
                        </Link>
                    </Button>
                )}
            </div>

            <div className="flex gap-3 items-center">
                <Select value={driverFilter} onValueChange={filter}>
                    <SelectTrigger className="w-56">
                        <SelectValue placeholder="Filter by driver…" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">All drivers</SelectItem>
                        {drivers.map((d) => (
                            <SelectItem key={d.id} value={String(d.id)}>{d.name}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {driverFilter && (
                    <Button variant="ghost" size="sm" onClick={() => filter('')}>Clear</Button>
                )}
            </div>

            <Card>
                <CardHeader><CardTitle>All Credits</CardTitle></CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Driver</TableHead>
                                <TableHead>Amount</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Date</TableHead>
                                {showActions && <TableHead className="text-right">Action</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {credits.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={showActions ? 5 : 4} className="text-center text-muted-foreground py-8">
                                        No credits yet
                                    </TableCell>
                                </TableRow>
                            )}
                            {credits.map((credit) => (
                                <TableRow key={credit.id}>
                                    <TableCell className="font-medium">{credit.driver_name ?? '—'}</TableCell>
                                    <TableCell>{Number(credit.amount).toFixed(2)} Dh</TableCell>
                                    <TableCell>
                                        <Badge variant={STATUS_VARIANT[credit.status] ?? 'secondary'}>
                                            {credit.status}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{credit.credit_date ?? '—'}</TableCell>
                                    {showActions && (
                                        <TableCell className="text-right space-x-1">
                                            <Button variant="ghost" size="icon" asChild>
                                                <Link href={route('credit.show', credit.id)} aria-label="View">
                                                    <Eye className="h-4 w-4" />
                                                </Link>
                                            </Button>
                                            <Button variant="ghost" size="icon" asChild>
                                                <Link href={route('credit.edit', credit.id)} aria-label="Edit">
                                                    <Pencil className="h-4 w-4" />
                                                </Link>
                                            </Button>
                                            <Button
                                                variant="ghost" size="icon"
                                                className="text-destructive hover:text-destructive"
                                                onClick={() => remove(credit.id)}
                                                aria-label="Delete"
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
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

CreditIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Credits' }]}>{page}</AdminLayout>
);
export default CreditIndex;
