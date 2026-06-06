import { Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Check, X } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';

// Port of resources/views/inspection/show.blade.php.
// Status / repair-status labels mirror Inspection::$status and
// Inspection::$repairStatus. Props inspection/details match the controller
// compact('inspection','details'); `details` is keyed by type id with
// type/status/note entries.
const STATUS_LABELS = {
    pending: 'Pending',
    completed: 'Completed',
    in_progress: 'In Progress',
    reject: 'Reject',
    conditional_pass: 'Conditional Pass',
    on_hold: 'On Hold',
};

const REPAIR_STATUS_LABELS = {
    needs_repair: 'Needs Repair',
    pending: 'Pending',
    completed: 'Completed',
    in_progress: 'In Progress',
    on_hold: 'On Hold',
};

function statusVariant(status) {
    if (status === 'pending' || status === 'on_hold') return 'secondary';
    if (status === 'completed' || status === 'conditional_pass') return 'default';
    if (status === 'in_progress') return 'default';
    if (status === 'reject') return 'destructive';
    return 'outline';
}

function repairStatusVariant(status) {
    if (status === 'pending' || status === 'on_hold') return 'secondary';
    if (status === 'completed') return 'default';
    if (status === 'in_progress') return 'default';
    if (status === 'needs_repair') return 'destructive';
    return 'outline';
}

function Detail({ label, children }) {
    return (
        <div>
            <h6 className="text-sm font-semibold text-muted-foreground">{label}</h6>
            <p className="mb-4">{children}</p>
        </div>
    );
}

function InspectionShow({ inspection = {}, details = {} }) {
    const t = useTranslation();
    const checklist = Object.values(details);

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <h1 className="text-3xl font-bold tracking-tight">{t('Details')}</h1>
                <Button variant="outline" asChild>
                    <Link href={route('inspection.index')}>{t('Back')}</Link>
                </Button>
            </div>

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <Card className="lg:col-span-1">
                    <CardHeader>
                        <CardTitle>{t('Inspection Details')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-2 gap-x-8">
                            <Detail label={t('Vehicle')}>{inspection.vehicles?.name ?? '-'}</Detail>
                            <Detail label={t('Inspection By')}>{inspection.inspector}</Detail>
                            <Detail label={t('Inspection Date')}>{inspection.inspection_date_display ?? inspection.inspection_date ?? '-'}</Detail>
                            <Detail label={t('Inspection Status')}>
                                {STATUS_LABELS[inspection.status] && (
                                    <Badge variant={statusVariant(inspection.status)}>
                                        {t(STATUS_LABELS[inspection.status])}
                                    </Badge>
                                )}
                            </Detail>
                            <Detail label={t('Repair Status')}>
                                {REPAIR_STATUS_LABELS[inspection.repair_status] && (
                                    <Badge variant={repairStatusVariant(inspection.repair_status)}>
                                        {t(REPAIR_STATUS_LABELS[inspection.repair_status])}
                                    </Badge>
                                )}
                            </Detail>
                            <Detail label={t('Amount')}>{inspection.amount_formatted ?? inspection.amount}</Detail>
                            {inspection.receipt && (
                                <Detail label={t('Receipt')}>
                                    <a
                                        className="text-primary underline"
                                        href={`/storage/upload/expense/${inspection.receipt}`}
                                        target="_blank"
                                        rel="noreferrer"
                                    >
                                        {t('Receipt')}
                                    </a>
                                </Detail>
                            )}
                        </div>
                    </CardContent>
                </Card>

                <div className="space-y-6 lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>{t('Incoming Details')}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-2 gap-x-8">
                                <Detail label={t('Date')}>{inspection.incoming_date_display ?? inspection.incoming_date ?? '-'}</Detail>
                                <Detail label={t('Meter Reading (Km)')}>
                                    {inspection.meter_reading_incoming ? inspection.meter_reading_incoming : '-'}
                                </Detail>
                            </div>
                        </CardContent>
                    </Card>
                    {inspection.notes && (
                        <Card>
                            <CardHeader>
                                <Detail label={t('Notes')}>{inspection.notes}</Detail>
                            </CardHeader>
                        </Card>
                    )}
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>{t('Inspections Checklist')}</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        {checklist.map((item, idx) => (
                            <div key={idx}>
                                <h6 className="flex items-center gap-2 text-sm font-semibold">
                                    {item.status === 'on'
                                        ? <Check className="h-4 w-4 text-green-600" />
                                        : <X className="h-4 w-4 text-destructive" />}
                                    {item.type}
                                </h6>
                                <p className="mb-4 text-muted-foreground">{item.note}</p>
                            </div>
                        ))}
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}

InspectionShow.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Inspection', href: route('inspection.index') },
        { label: 'Details' },
    ]}>{page}</AdminLayout>
);
export default InspectionShow;
