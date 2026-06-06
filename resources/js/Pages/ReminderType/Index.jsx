import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Pencil, Trash2, Plus, Bell, Search } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';

function ReminderTypeIndex({ types = [] }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    async function remove(id) {
        if (await confirmDialog({ title: t('Are you sure?') })) {
            router.delete(route('reminder-type.destroy', id));
        }
    }

    const showActions = can('edit reminder') || can('delete reminder');

    const [query, setQuery] = useState('');
    const q = query.trim().toLowerCase();
    const filtered = q
        ? types.filter((item) =>
            [item.type].some((v) => String(v ?? '').toLowerCase().includes(q)))
        : types;

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
                    <Bell className="h-6 w-6" /> {t('Reminder Type')}
                </h1>
                {can('manage reminder') && (
                    <Button size="sm" asChild>
                        <Link href={route('reminder-type.create')}>
                            <Plus className="mr-2 h-4 w-4" /> {t('Create Type')}
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
                            placeholder={t('Search types…')}
                            className="pl-8"
                        />
                    </div>
            </div>

            <div className="rounded-xl border bg-card overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Type')}</TableHead>
                                {showActions && <TableHead className="text-right">{t('Action')}</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {filtered.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={showActions ? 2 : 1} className="text-center text-muted-foreground py-8">
                                        {types.length === 0 ? t('No reminder types yet') : t('No types match your search')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {filtered.map((type) => (
                                <TableRow key={type.id}>
                                    <TableCell>{type.type}</TableCell>
                                    {showActions && (
                                        <TableCell className="text-right space-x-1">
                                            {can('edit reminder') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('reminder-type.edit', type.id)} aria-label={t('Edit')}>
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('delete reminder') && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="text-destructive hover:text-destructive"
                                                    onClick={() => remove(type.id)}
                                                    aria-label={t('Delete')}
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
                </div>
        </div>
    );
}

ReminderTypeIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Reminder Type' }]}>{page}</AdminLayout>
);
export default ReminderTypeIndex;
