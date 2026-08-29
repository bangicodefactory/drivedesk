import { Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Plus, Trash2, PenLine, Eye } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';

function SignatureIndex({ signatures = [] }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    async function remove(id) {
        if (await confirmDialog({ title: t('Delete this signature?') })) {
            router.delete(route('signature.destroy', id));
        }
    }

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
                    <PenLine className="h-6 w-6" /> {t('Signatures')}
                </h1>
                {can('manage driver') && (
                    <Button size="sm" asChild>
                        <Link href={route('signature.create')}>
                            <Plus className="me-2 h-4 w-4" /> {t('Add Signature')}
                        </Link>
                    </Button>
                )}
            </div>

            <div className="rounded-xl border bg-card overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Driver')}</TableHead>
                                <TableHead>{t('Preview')}</TableHead>
                                <TableHead>{t('Date')}</TableHead>
                                {can('manage driver') && <TableHead className="text-end">{t('Action')}</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {signatures.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={4} className="text-center text-muted-foreground py-8">
                                        {t('No signatures yet')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {signatures.map((sig) => (
                                <TableRow key={sig.id}>
                                    <TableCell className="font-medium">{sig.driver_name ?? '—'}</TableCell>
                                    <TableCell>
                                        {sig.signature_url ? (
                                            <img src={sig.signature_url} alt="signature" loading="lazy" className="h-10 max-w-[120px] object-contain border rounded" />
                                        ) : '—'}
                                    </TableCell>
                                    <TableCell>{sig.created_at}</TableCell>
                                    {can('manage driver') && (
                                        <TableCell className="text-end space-x-1">
                                            {sig.signature_url && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <a
                                                        href={sig.signature_url}
                                                        target="_blank"
                                                        rel="noreferrer"
                                                        aria-label={t('View signature')}
                                                    >
                                                        <Eye className="h-4 w-4" />
                                                    </a>
                                                </Button>
                                            )}
                                            <Button
                                                variant="ghost" size="icon"
                                                className="text-destructive hover:text-destructive"
                                                onClick={() => remove(sig.id)}
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

SignatureIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Signatures' }]}>{page}</AdminLayout>
);
export default SignatureIndex;
