import { Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge }  from '@/components/ui/badge';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Pencil, Trash2, Plus } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

function UsersIndex({ users }) {
    const { auth } = usePage().props;
    const canCreate = auth.permissions.includes('create user');
    const canEdit   = auth.permissions.includes('edit user');
    const canDelete = auth.permissions.includes('delete user');

    function remove(id) {
        if (window.confirm('Delete this user?')) {
            router.delete(route('users.destroy', id));
        }
    }

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-semibold">Users</h1>
                {canCreate && (
                    <Button asChild>
                        <Link href={route('users.create')}>
                            <Plus className="mr-2 h-4 w-4" /> New user
                        </Link>
                    </Button>
                )}
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>All users</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Company</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {users.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={6} className="text-center text-muted-foreground py-8">
                                        No users yet
                                    </TableCell>
                                </TableRow>
                            )}
                            {users.map((u) => (
                                <TableRow key={u.id}>
                                    <TableCell className="font-medium">{u.name}</TableCell>
                                    <TableCell>{u.email}</TableCell>
                                    <TableCell><Badge variant="outline">{u.type}</Badge></TableCell>
                                    <TableCell>{u.company_name ?? '—'}</TableCell>
                                    <TableCell>
                                        <Badge variant={u.is_active ? 'default' : 'secondary'}>
                                            {u.is_active ? 'Active' : 'Inactive'}
                                        </Badge>
                                    </TableCell>
                                    <TableCell className="text-right space-x-1">
                                        {canEdit && (
                                            <Button variant="ghost" size="icon" asChild>
                                                <Link href={route('users.edit', u.id)} aria-label="Edit">
                                                    <Pencil className="h-4 w-4" />
                                                </Link>
                                            </Button>
                                        )}
                                        {canDelete && (
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                onClick={() => remove(u.id)}
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

UsersIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Users' }]}>{page}</AdminLayout>
);
export default UsersIndex;
