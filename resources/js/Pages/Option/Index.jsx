import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Pencil, Trash2, Plus, ListChecks, Search } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

// Port of resources/views/option/index.blade.php.
// Action buttons are gated by the shared auth.permissions slugs, mirroring the
// Blade @can('edit|delete options') / Gate::check('manage options') guards.
// Prop name `options` matches the controller compact('options').
function OptionIndex({ options = [] }) {
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    function remove(id) {
        if (window.confirm('Are you sure?')) {
            router.delete(route('option.destroy', id));
        }
    }

    const showActions = can('edit options') || can('delete options');

    const [query, setQuery] = useState('');
    const q = query.trim().toLowerCase();
    const filtered = q
        ? options.filter((option) =>
            [option.name]
                .some((v) => String(v ?? '').toLowerCase().includes(q)))
        : options;

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-semibold flex items-center gap-2">
                    <ListChecks className="h-6 w-6" /> Option
                </h1>
                {can('manage options') && (
                    <Button size="sm" asChild>
                        <Link href={route('option.create')}>
                            <Plus className="mr-2 h-4 w-4" /> Create Option
                        </Link>
                    </Button>
                )}
            </div>

            <Card>
                <CardHeader className="flex flex-row items-center justify-between gap-4 space-y-0">
                    <CardTitle>All Options</CardTitle>
                    <div className="relative w-full max-w-xs">
                        <Search className="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder="Search options…"
                            className="pl-8"
                        />
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Option</TableHead>
                                {showActions && <TableHead className="text-right">Action</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {filtered.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={showActions ? 2 : 1} className="text-center text-muted-foreground py-8">
                                        {options.length === 0 ? 'No options yet' : 'No options match your search'}
                                    </TableCell>
                                </TableRow>
                            )}
                            {filtered.map((option) => (
                                <TableRow key={option.id}>
                                    <TableCell>{option.name}</TableCell>
                                    {showActions && (
                                        <TableCell className="text-right space-x-1">
                                            {can('edit options') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('option.edit', option.id)} aria-label="Edit">
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('delete options') && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="text-destructive hover:text-destructive"
                                                    onClick={() => remove(option.id)}
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

OptionIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Option' }]}>{page}</AdminLayout>
);
export default OptionIndex;
