import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Pencil, Trash2, Plus, Eye, CreditCard, Search } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';

const STATUS_VARIANT = {
    'payé': 'success',
    'non payé': 'destructive',
};

function CreditIndex({ credits = [], drivers = [] }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    const params = new URLSearchParams(window.location.search);
    const [driverFilter, setDriverFilter] = useState(params.get('driver_id') ?? '');

    async function remove(id) {
        if (await confirmDialog({ title: t('Delete this credit?') })) {
            router.delete(route('credit.destroy', id));
        }
    }

    function filter(driverId) {
        // Radix Select forbids an empty-string item value, so the "All drivers"
        // option uses the 'all' sentinel; normalize it back to no filter here.
        const real = driverId === 'all' ? '' : driverId;
        setDriverFilter(real);
        router.get(route('credit.index'), real ? { driver_id: real } : {}, { preserveState: true, replace: true });
    }

    const showActions = can('manage driver');

    const [query, setQuery] = useState('');
    const q = query.trim().toLowerCase();
    const filtered = q
        ? credits.filter((credit) =>
            [credit.driver_name, credit.amount, credit.status]
                .some((v) => String(v ?? '').toLowerCase().includes(q)))
        : credits;

    return (
        <div className="space-y-6 p-6">
            <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
                <CreditCard className="h-6 w-6" /> {t('Credits')}
            </h1>

            <div className="flex gap-3 items-center">
                <Select value={driverFilter || 'all'} onValueChange={filter}>
                    <SelectTrigger className="w-56">
                        <SelectValue placeholder={t('Filter by driver…')} />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">{t('All drivers')}</SelectItem>
                        {drivers.map((d) => (
                            <SelectItem key={d.id} value={String(d.id)}>{d.name}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {driverFilter && (
                    <Button variant="ghost" size="sm" onClick={() => filter('')}>{t('Clear')}</Button>
                )}
            </div>

            {/* Search sits under the title on the left; actions face it on the
                same row, kept on the right. */}
            <div className="flex items-center justify-between gap-2">
                <div className="relative w-full max-w-xs">
                        <Search className="pointer-events-none absolute start-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder={t('Search credits…')}
                            className="ps-8"
                        />
                    </div>
                {can('manage driver') && (
                    <Button size="sm" asChild>
                        <Link href={route('credit.create')}>
                            <Plus className="me-2 h-4 w-4" /> {t('Add Credit')}
                        </Link>
                    </Button>
                )}
            </div>

            <div className="rounded-xl border bg-card overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Driver')}</TableHead>
                                <TableHead>{t('Amount')}</TableHead>
                                <TableHead>{t('Status')}</TableHead>
                                <TableHead>{t('Date')}</TableHead>
                                {showActions && <TableHead className="text-end">{t('Action')}</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {filtered.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={showActions ? 5 : 4} className="text-center text-muted-foreground py-8">
                                        {credits.length === 0 ? t('No credits yet') : t('No credits match your search')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {filtered.map((credit) => (
                                <TableRow key={credit.id}>
                                    <TableCell className="font-medium">{credit.driver_name ?? '—'}</TableCell>
                                    <TableCell>{Number(credit.amount).toFixed(2)} Dh</TableCell>
                                    <TableCell>
                                        <Badge variant={STATUS_VARIANT[credit.status] ?? 'secondary'}>
                                            {t(credit.status)}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{credit.credit_date ?? '—'}</TableCell>
                                    {showActions && (
                                        <TableCell className="text-end space-x-1 rtl:space-x-reverse">
                                            <Button variant="ghost" size="icon" asChild>
                                                <Link href={route('credit.show', credit.id)} aria-label={t('View')}>
                                                    <Eye className="h-4 w-4" />
                                                </Link>
                                            </Button>
                                            <Button variant="ghost" size="icon" asChild>
                                                <Link href={route('credit.edit', credit.id)} aria-label={t('Edit')}>
                                                    <Pencil className="h-4 w-4" />
                                                </Link>
                                            </Button>
                                            <Button
                                                variant="ghost" size="icon"
                                                className="text-destructive hover:text-destructive"
                                                onClick={() => remove(credit.id)}
                                                aria-label={t('Delete')}
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
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

CreditIndex.layout = (page) => {
    return (
        <AdminLayout breadcrumbs={[{ label: 'Credits' }]}>{page}</AdminLayout>
    );
};
export default CreditIndex;
