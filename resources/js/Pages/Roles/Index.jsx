import { Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge }  from '@/components/ui/badge';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Pencil, Trash2, Plus } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

function RolesIndex({ roles }) {
    const { auth } = usePage().props;
    const canEdit   = auth.permissions.includes('edit role');
    const canDelete = auth.permissions.includes('delete role');

    function remove(id) {
        if (window.confirm('Delete this role?')) {
            router.delete(route('role.destroy', id));
        }
    }

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-semibold">Roles</h1>
                <Button asChild>
                    <Link href={route('role.create')}>
                        <Plus className="mr-2 h-4 w-4" /> New role
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader><CardTitle>All roles</CardTitle></CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Permissions</TableHead>
                                <TableHead className="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {roles.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={3} className="text-center text-muted-foreground py-8">
                                        No roles yet
                                    </TableCell>
                                </TableRow>
                            )}
                            {roles.map((r) => (
                                <TableRow key={r.id}>
                                    <TableCell className="font-medium">{r.name}</TableCell>
                                    <TableCell>
                                        <Badge variant="secondary">{r.permissions_count}</Badge>
                                    </TableCell>
                                    <TableCell className="text-right space-x-1">
                                        {canEdit && (
                                            <Button variant="ghost" size="icon" asChild>
                                                <Link href={route('role.edit', r.id)} aria-label="Edit">
                                                    <Pencil className="h-4 w-4" />
                                                </Link>
                                            </Button>
                                        )}
                                        {canDelete && (
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                onClick={() => remove(r.id)}
                                                aria-label="Delete"
                                                className="text-destructive hover:text-destructive"
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
                                        )}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    );
}

RolesIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Roles' }]}>{page}</AdminLayout>
);
export default RolesIndex;
