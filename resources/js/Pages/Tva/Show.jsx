import { Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Eye, Pencil } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

function DetailRow({ label, value }) {
    return (
        <div className="space-y-1">
            <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wide">{label}</p>
            <p className="text-sm font-medium">{value ?? '—'}</p>
        </div>
    );
}

function TvaShow({ tva }) {
    const fmt = (n) => n !== null && n !== undefined ? Number(n).toFixed(2) : '—';

    return (
        <div className="p-6 max-w-3xl mx-auto">
            <div className="flex items-center justify-between mb-6">
                <h1 className="text-2xl font-semibold flex items-center gap-2">
                    <Eye className="h-6 w-6" /> TVA Details
                </h1>
                <Button variant="outline" size="sm" asChild>
                    <Link href={route('tva.edit', tva.id)}>
                        <Pencil className="mr-2 h-4 w-4" /> Edit
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle className="text-base">Invoice #{tva.facture_number}</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-2 gap-6">
                        <DetailRow label="Facture Number" value={tva.facture_number} />
                        <DetailRow label="Facture Date"   value={tva.facture_date} />
                        <DetailRow label="Client Name"    value={tva.client_name} />
                        <DetailRow label="Duration (Quantity)" value={tva.quantity} />
                        <DetailRow label="Unit Price HT"  value={fmt(tva.unit_price_ht)} />
                        <DetailRow label="Total HT"       value={fmt(tva.total_ht)} />
                        <DetailRow label="TVA (Tax)"      value={fmt(tva.tva)} />
                        <DetailRow label="Montant TTC"    value={fmt(tva.montant_ttc)} />
                        <DetailRow label="Vehicle"        value={tva.designation} />
                        <DetailRow label="ICE"            value={tva.payment_method} />
                    </div>
                </CardContent>
            </Card>

            <div className="mt-4">
                <Button variant="outline" asChild>
                    <Link href={route('tva.index')}>Back to TVA List</Link>
                </Button>
            </div>
        </div>
    );
}

TvaShow.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'TVA', href: route('tva.index') },
        { label: 'Details' },
    ]}>{page}</AdminLayout>
);
export default TvaShow;
