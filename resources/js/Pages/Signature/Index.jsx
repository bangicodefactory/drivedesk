import { Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Plus, Trash2, PenLine, Eye } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

function SignatureIndex({ signatures = [] }) {
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);

    function remove(id) {
        if (window.confirm('Delete this signature?')) {
            router.delete(route('signature.destroy', id));
        }
    }

    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-semibold flex items-center gap-2">
                    <PenLine className="h-6 w-6" /> Signatures
                </h1>
                {can('manage driver') && (
                    <Button size="sm" asChild>
                        <Link href={route('signature.create')}>
                            <Plus className="mr-2 h-4 w-4" /> Add Signature
                        </Link>
                    </Button>
                )}
            </div>

            <Card>
                <CardHeader><CardTitle>All Signatures</CardTitle></CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Driver</TableHead>
                                <TableHead>Preview</TableHead>
                                <TableHead>Date</TableHead>
                                {can('manage driver') && <TableHead className="text-right">Action</TableHead>}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {signatures.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={4} className="text-center text-muted-foreground py-8">
                                        No signatures yet
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
                                        <TableCell className="text-right space-x-1">
                                            {sig.signature_url && (
                                                <Button variant="ghost" size="icon" asChild>
                                                    <a
                                                        href={sig.signature_url}
                                                        target="_blank"
                                                        rel="noreferrer"
                                                        aria-label="View signature"
                                                    >
                                                        <Eye className="h-4 w-4" />
                                                    </a>
                                                </Button>
                                            )}
                                            <Button
                                                variant="ghost" size="icon"
                                                className="text-destructive hover:text-destructive"
                                                onClick={() => remove(sig.id)}
                                                aria-label="Delete"
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
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

SignatureIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Signatures' }]}>{page}</AdminLayout>
);
export default SignatureIndex;
