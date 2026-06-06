import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Badge }  from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Pencil, Trash2, Plus, Search } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';

function UsersIndex({ users }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    const { auth } = usePage().props;
    const canCreate = auth.permissions.includes('create user');
    const canEdit   = auth.permissions.includes('edit user');
    const canDelete = auth.permissions.includes('delete user');

    async function remove(id) {
        if (await confirmDialog({ title: t('Delete this user?') })) {
            router.delete(route('users.destroy', id));
        }
    }

    const [query, setQuery] = useState('');
    const q = query.trim().toLowerCase();
    const filtered = q
        ? users.filter((u) =>
            [u.name, u.email, u.type, u.company_name]
                .some((v) => String(v ?? '').toLowerCase().includes(q)))
        : users;

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <h1 className="text-3xl font-bold tracking-tight">{t('Users')}</h1>
                {canCreate && (
                    <Button asChild>
                        <Link href={route('users.create')}>
                            <Plus className="mr-2 h-4 w-4" /> {t('New user')}
                        </Link>
                    </Button>
                )}
            </div>

            <div className="flex items-center justify-end">
                <div className="relative w-full max-w-xs">
                        <Search className="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder={t('Search users…')}
                            className="pl-8"
                        />
                    </div>
            </div>

            <div className="rounded-xl border bg-card overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Name')}</TableHead>
                                <TableHead>{t('Email')}</TableHead>
                                <TableHead>{t('Type')}</TableHead>
                                <TableHead>{t('Company')}</TableHead>
                                <TableHead>{t('Status')}</TableHead>
                                <TableHead className="text-right">{t('Actions')}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {filtered.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={6} className="text-center text-muted-foreground py-8">
                                        {users.length === 0 ? t('No users yet') : t('No users match your search')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {filtered.map((u) => (
                                <TableRow key={u.id}>
                                    <TableCell className="font-medium">{u.name}</TableCell>
                                    <TableCell>{u.email}</TableCell>
                                    <TableCell><Badge variant="outline">{u.type}</Badge></TableCell>
                                    <TableCell>{u.company_name ?? '—'}</TableCell>
                                    <TableCell>
                                        <Badge variant={u.is_active ? 'default' : 'secondary'}>
                                            {u.is_active ? t('Active') : t('Inactive')}
                                        </Badge>
                                    </TableCell>
                                    <TableCell className="text-right space-x-1">
                                        {canEdit && (
                                            <Button variant="ghost" size="icon" asChild>
                                                <Link href={route('users.edit', u.id)} aria-label={t('Edit')}>
                                                    <Pencil className="h-4 w-4" />
                                                </Link>
                                            </Button>
                                        )}
                                        {canDelete && (
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                onClick={() => remove(u.id)}
                                                aria-label={t('Delete')}
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
                </div>
        </div>
    );
}

UsersIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Users' }]}>{page}</AdminLayout>
);
export default UsersIndex;
