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

function RolesIndex({ roles }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    const { auth } = usePage().props;
    const canEdit   = auth.permissions.includes('edit role');
    const canDelete = auth.permissions.includes('delete role');

    async function remove(id) {
        if (await confirmDialog({ title: t('Delete this role?') })) {
            router.delete(route('role.destroy', id));
        }
    }

    const [query, setQuery] = useState('');
    const q = query.trim().toLowerCase();
    const filtered = q
        ? roles.filter((r) =>
            [r.name]
                .some((v) => String(v ?? '').toLowerCase().includes(q)))
        : roles;

    return (
        <div className="space-y-6 p-6">
            <h1 className="text-3xl font-bold tracking-tight">{t('Roles')}</h1>

            {/* Search sits under the title on the left; actions face it on the
                same row, kept on the right. */}
            <div className="flex items-center justify-between gap-2">
                <div className="relative w-full max-w-xs">
                        <Search className="pointer-events-none absolute start-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder={t('Search roles…')}
                            className="ps-8"
                        />
                    </div>
                <Button asChild>
                    <Link href={route('role.create')}>
                        <Plus className="me-2 h-4 w-4" /> {t('New role')}
                    </Link>
                </Button>
            </div>

            <div className="rounded-xl border bg-card overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Name')}</TableHead>
                                <TableHead>{t('Permissions')}</TableHead>
                                <TableHead className="text-end">{t('Actions')}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {filtered.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={3} className="text-center text-muted-foreground py-8">
                                        {roles.length === 0 ? t('No roles yet') : t('No roles match your search')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {filtered.map((r) => (
                                <TableRow key={r.id}>
                                    <TableCell className="font-medium">{r.name}</TableCell>
                                    <TableCell>
                                        <Badge variant="secondary">{r.permissions_count}</Badge>
                                    </TableCell>
                                    <TableCell className="text-end space-x-1">
                                        {canEdit && (
                                            <Button variant="ghost" size="icon" asChild>
                                                <Link href={route('role.edit', r.id)} aria-label={t('Edit')}>
                                                    <Pencil className="h-4 w-4" />
                                                </Link>
                                            </Button>
                                        )}
                                        {canDelete && (
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                onClick={() => remove(r.id)}
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

RolesIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Roles' }]}>{page}</AdminLayout>
);
export default RolesIndex;
