import { router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Inbox, Check, X } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';

// Super-admin review of pending demo requests (BAN-249). Each row is an inactive
// `manager` sub-user of the demo tenant; Approve emails a set-password link and
// activates the login, Decline removes it. Routes are super-admin + feature
// gated server-side (DemoApprovalController) — this page only renders for them.
function DemoRequestsIndex({ requests = [] }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();

    async function approve(req) {
        const ok = await confirmDialog({
            title: t('Approve demo request?'),
            description: t('A set-password link will be emailed to') + ' ' + req.email + '.',
            confirmText: t('Approve'),
            destructive: false,
        });
        if (ok) {
            router.post(route('demo-requests.approve', req.id), {}, { preserveScroll: true });
        }
    }

    async function decline(req) {
        const ok = await confirmDialog({
            title: t('Decline demo request?'),
            description: t('This permanently removes the pending request.'),
            confirmText: t('Decline'),
        });
        if (ok) {
            router.post(route('demo-requests.decline', req.id), {}, { preserveScroll: true });
        }
    }

    const fmt = (iso) => {
        if (!iso) return '-';
        const d = new Date(iso);
        return Number.isNaN(d.getTime()) ? '-' : d.toLocaleString();
    };

    return (
        <div className="space-y-6 p-6">
            <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
                <Inbox className="h-6 w-6" /> {t('Demo requests')}
            </h1>
            <p className="-mt-2 text-sm text-muted-foreground">
                {t('Prospects who booked a demo. Approve to email them a set-password link; decline to remove.')}
            </p>

            <div className="rounded-xl border bg-card overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>{t('Company')}</TableHead>
                            <TableHead>{t('Contact')}</TableHead>
                            <TableHead>{t('Email')}</TableHead>
                            <TableHead>{t('Phone')}</TableHead>
                            <TableHead>{t('Requested')}</TableHead>
                            <TableHead className="text-end">{t('Action')}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {requests.length === 0 && (
                            <TableRow>
                                <TableCell colSpan={6} className="py-8 text-center text-muted-foreground">
                                    {t('No pending demo requests')}
                                </TableCell>
                            </TableRow>
                        )}
                        {requests.map((r) => (
                            <TableRow key={r.id}>
                                <TableCell className="font-medium">{r.company || '-'}</TableCell>
                                <TableCell>{r.name}</TableCell>
                                <TableCell>{r.email}</TableCell>
                                <TableCell>{r.phone || '-'}</TableCell>
                                <TableCell>{fmt(r.created_at)}</TableCell>
                                <TableCell className="text-end space-x-1 rtl:space-x-reverse">
                                    <Button size="sm" onClick={() => approve(r)}>
                                        <Check className="me-1 h-4 w-4" /> {t('Approve')}
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        className="text-destructive hover:text-destructive"
                                        onClick={() => decline(r)}
                                    >
                                        <X className="me-1 h-4 w-4" /> {t('Decline')}
                                    </Button>
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </div>
        </div>
    );
}

DemoRequestsIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Demo requests' }]}>{page}</AdminLayout>
);
export default DemoRequestsIndex;
