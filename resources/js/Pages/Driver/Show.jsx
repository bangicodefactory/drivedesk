import { useState } from 'react';
import { Download, Ban, ShieldCheck } from 'lucide-react';
import { Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
} from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { useConfirm } from '@/components/ui/confirm-dialog';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';

// Port of resources/views/driver/show.blade.php.
// Props `driver` and `user` match the controller compact('driver', 'user').
function Detail({ label, value, className = '' }) {
    return (
        <div className={className}>
            <h6 className="text-sm font-semibold text-muted-foreground">{label}</h6>
            <p className="mb-4">{value || '-'}</p>
        </div>
    );
}

function FileDetail({ label, name, dir, className = '' }) {
    const t = useTranslation();
    if (!name) {
        return (
            <div className={className}>
                <h6 className="text-sm font-semibold text-muted-foreground">{label}</h6>
                <p className="mb-4 text-muted-foreground">-</p>
            </div>
        );
    }

    const url = `/storage/upload/${dir}/${name}`;
    const isImage = /\.(png|jpe?g|gif|webp|bmp)$/i.test(name);

    return (
        <div className={className}>
            <h6 className="text-sm font-semibold text-muted-foreground mb-1">{label}</h6>
            {isImage ? (
                <div className="mb-4 inline-flex items-center gap-2">
                    <a href={url} target="_blank" rel="noreferrer">
                        <img
                            src={url}
                            alt={label}
                            loading="lazy"
                            className="h-28 w-auto rounded border object-cover shadow-sm hover:opacity-80 transition-opacity"
                        />
                    </a>
                    <a
                        href={url}
                        download={name}
                        className="text-muted-foreground hover:text-primary transition-colors"
                        title={t('Download')}
                    >
                        <Download className="h-4 w-4" />
                    </a>
                </div>
            ) : (
                <div className="mb-4 inline-flex items-center gap-2">
                    <a href={url} target="_blank" rel="noreferrer" className="text-primary underline hover:opacity-80">
                        {name}
                    </a>
                    <a
                        href={url}
                        download={name}
                        className="text-muted-foreground hover:text-primary transition-colors"
                        title={t('Download')}
                    >
                        <Download className="h-4 w-4" />
                    </a>
                </div>
            )}
        </div>
    );
}

function DriverShow({ driver = {}, user = {}, is_blacklisted = false, blacklist_reason = null, blacklisted_at = null }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    const { auth } = usePage().props;
    const canBlacklist = auth.permissions.includes('manage driver blacklist');

    const [open, setOpen] = useState(false);
    const [reason, setReason] = useState('');

    function submitBlacklist() {
        if (!reason.trim()) return;
        router.post(route('driver.blacklist', user.id), { reason }, {
            preserveScroll: true,
            onSuccess: () => { setOpen(false); setReason(''); },
        });
    }

    async function liftBlacklist() {
        const ok = await confirmDialog({
            title: t('Remove from blacklist?'),
            description: user.name,
            confirmText: t('Remove from blacklist'),
            destructive: false,
        });
        if (ok) router.post(route('driver.unblacklist', user.id), {}, { preserveScroll: true });
    }

    return (
        <div className="space-y-6 p-6">
            {(is_blacklisted || canBlacklist) && (
                <div className="flex items-center justify-between gap-3 rounded-xl border bg-card px-4 py-3">
                    <div className="flex items-center gap-3">
                        {is_blacklisted ? (
                            <>
                                <Badge variant="destructive">{t('Blacklisted')}</Badge>
                                <span className="text-sm text-muted-foreground">
                                    {blacklist_reason}{blacklisted_at ? ` · ${blacklisted_at}` : ''}
                                </span>
                            </>
                        ) : (
                            <span className="text-sm text-muted-foreground">{t('Not blacklisted')}</span>
                        )}
                    </div>
                    {canBlacklist && (
                        is_blacklisted ? (
                            <Button variant="outline" size="sm" onClick={liftBlacklist}>
                                <ShieldCheck className="mr-2 h-4 w-4" /> {t('Remove from blacklist')}
                            </Button>
                        ) : (
                            <Button variant="destructive" size="sm" onClick={() => { setReason(''); setOpen(true); }}>
                                <Ban className="mr-2 h-4 w-4" /> {t('Blacklist')}
                            </Button>
                        )
                    )}
                </div>
            )}

            <Card>
                <CardHeader>
                    <CardTitle>{t('Details')}</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-1 gap-x-8 md:grid-cols-2">
                        <Detail label={t('ID')} value={driver.driver_id_display ?? '-'} />
                        <Detail label={t('First Name')} value={user.first_name} />
                        <Detail label={t('Last Name')} value={user.last_name} />
                        <Detail label={t('Email')} value={user.email} />
                        <Detail label={t('Phone Number')} value={user.phone_number} />
                        <Detail label={t('Gender')} value={driver.gender} />
                        <Detail label={t('Age')} value={driver.age && driver.age !== 0 ? driver.age : '-'} />
                        <Detail label={t('Address')} value={driver.address} />
                        <Detail label={t('Birth Date')} value={driver.birth_date_display} />
                        <Detail label={t('License Number')} value={driver.license_number} />
                        <Detail label={t('Issue Date')} value={driver.issue_date_display} />
                        <Detail label={t('Expiration Date')} value={driver.expiration_date_display} />
                        <FileDetail label={t('License 1:')} name={driver.license} dir="license" />
                        <FileDetail label={t('License 2:')} name={driver.license_1} dir="license" />
                        <Detail label={t('Reference')} value={driver.reference} className="md:col-span-2" />
                        <FileDetail label={t('ID file 1:')} name={driver.document} dir="document" />
                        <FileDetail label={t('ID file 2:')} name={driver.document_1} dir="document" />
                        <Detail label={t('notes')} value={driver.notes} />
                        <Detail label={t('ICE_company')} value={driver.ICE_company} className="md:col-span-2" />
                    </div>
                </CardContent>
            </Card>

            <Dialog open={open} onOpenChange={(o) => { setOpen(o); if (!o) setReason(''); }}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('Blacklist driver')}</DialogTitle>
                        <DialogDescription>{user.name}</DialogDescription>
                    </DialogHeader>
                    <div className="space-y-2">
                        <Label htmlFor="blacklist-reason">{t('Reason')}</Label>
                        <Textarea
                            id="blacklist-reason"
                            rows={4}
                            value={reason}
                            onChange={(e) => setReason(e.target.value)}
                            placeholder={t('Why is this driver being blacklisted?')}
                        />
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => { setOpen(false); setReason(''); }}>
                            {t('Cancel')}
                        </Button>
                        <Button variant="destructive" disabled={!reason.trim()} onClick={submitBlacklist}>
                            {t('Blacklist')}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    );
}

DriverShow.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Driver', href: route('driver.index') },
        { label: 'Details' },
    ]}>{page}</AdminLayout>
);
export default DriverShow;
