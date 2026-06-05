import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Eye, Pencil, Trash2, Plus, FileText, Search } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';

const STATUS_VARIANT = {
    draft: 'secondary',
    pending: 'default',
    confirmed: 'outline',
    active: 'outline',
    completed: 'secondary',
    cancelled: 'destructive',
};

function RentalAgreementIndex({ agreements, statuses }) {
    const t = useTranslation();
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    const statusLabel = (s) => statuses?.find((x) => x.value === s)?.label ?? s;

    function remove(id) {
        if (window.confirm(t('Delete this rental agreement?'))) {
            router.delete(route('rental-agreement.destroy', id));
        }
    }

    const [query, setQuery] = useState('');
    const q = query.trim().toLowerCase();
    const filtered = q
        ? agreements.filter((a) =>
            [a.agreement_id, a.driver_name, a.vehicle_label, statusLabel(a.status)]
                .some((v) => String(v ?? '').toLowerCase().includes(q)))
        : agreements;

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-semibold">{t('Rental Agreements')}</h1>
                {can('manage rental agreement') && (
                    <Button size="sm" asChild>
                        <Link href={route('rental-agreement.create')}>
                            <Plus className="mr-2 h-4 w-4" /> {t('Create Agreement')}
                        </Link>
                    </Button>
                )}
            </div>

            <Card>
                <CardHeader className="flex flex-row items-center justify-between gap-4 space-y-0">
                    <CardTitle className="flex items-center gap-2">
                        <FileText className="h-5 w-5" /> {t('All Agreements')}
                    </CardTitle>
                    <div className="relative w-full max-w-xs">
                        <Search className="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder={t('Search agreements…')}
                            className="pl-8"
                        />
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('ID')}</TableHead>
                                <TableHead>{t('Driver')}</TableHead>
                                <TableHead>{t('Vehicle')}</TableHead>
                                <TableHead>{t('Date')}</TableHead>
                                <TableHead>{t('Start')}</TableHead>
                                <TableHead>{t('End')}</TableHead>
                                <TableHead>{t('Duration')}</TableHead>
                                <TableHead>{t('Status')}</TableHead>
                                {(can('edit rental agreement') || can('delete rental agreement') || can('show rental agreement')) && (
                                    <TableHead className="text-right">{t('Action')}</TableHead>
                                )}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {filtered.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={9} className="text-center text-muted-foreground py-8">
                                        {agreements.length === 0 ? t('No rental agreements yet') : t('No rental agreements match your search')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {filtered.map((a) => (
                                <TableRow key={a.id}>
                                    <TableCell className="font-mono text-sm">{a.agreement_id}</TableCell>
                                    <TableCell>{a.driver_name}</TableCell>
                                    <TableCell>{a.vehicle_label}</TableCell>
                                    <TableCell>{a.date}</TableCell>
                                    <TableCell>{a.rental_start_date}</TableCell>
                                    <TableCell>{a.rental_end_date}</TableCell>
                                    <TableCell>{a.rental_duration} {t('Days')}</TableCell>
                                    <TableCell>
                                        <Badge variant={STATUS_VARIANT[a.status] ?? 'secondary'}>
                                            {statusLabel(a.status)}
                                        </Badge>
                                    </TableCell>
                                    {(can('edit rental agreement') || can('delete rental agreement') || can('show rental agreement')) && (
                                        <TableCell className="text-right space-x-1 whitespace-nowrap">
                                            {can('show rental agreement') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('rental-agreement.show', a.encrypted_id)} aria-label={t('View')}>
                                                        <Eye className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('edit rental agreement') && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <Link href={route('rental-agreement.edit', a.id)} aria-label={t('Edit')}>
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can('delete rental agreement') && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => remove(a.id)}
                                                    className="text-destructive hover:text-destructive"
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
                </CardContent>
            </Card>
        </div>
    );
}

RentalAgreementIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Rental Agreements' }]}>{page}</AdminLayout>
);
export default RentalAgreementIndex;
