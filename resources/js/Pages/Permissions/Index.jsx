import { router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge }  from '@/components/ui/badge';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Trash2 } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import { useConfirm } from '@/components/ui/confirm-dialog';

function PermissionsIndex({ permissions }) {
    const t = useTranslation();
    const confirmDialog = useConfirm();
    const { auth } = usePage().props;
    const canDelete = auth.permissions.includes('delete permission');

    async function remove(id) {
        if (await confirmDialog({ title: t('Delete this permission?') })) {
            router.delete(route('permission.destroy', id));
        }
    }

    return (
        <div className="space-y-6 p-6">
            <h1 className="text-2xl font-semibold">{t('Permissions')}</h1>

            <Card>
                <CardHeader><CardTitle>{t('All permissions')}</CardTitle></CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Name')}</TableHead>
                                <TableHead>{t('Guard')}</TableHead>
                                <TableHead className="text-right">{t('Actions')}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {permissions.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={3} className="text-center text-muted-foreground py-8">
                                        {t('No permissions yet')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {permissions.map((p) => (
                                <TableRow key={p.id}>
                                    <TableCell className="font-medium">{p.name}</TableCell>
                                    <TableCell><Badge variant="outline">{p.guard_name}</Badge></TableCell>
                                    <TableCell className="text-right">
                                        {canDelete && (
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                onClick={() => remove(p.id)}
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
                </CardContent>
            </Card>
        </div>
    );
}

PermissionsIndex.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Permissions' }]}>{page}</AdminLayout>
);
export default PermissionsIndex;
