import React from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslations } from '@/hooks/useTranslations';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

// Port of resources/views/vehicle/index.blade.php.
// Permission gating (create/show/edit/delete vehicle) is surfaced via the
// shared `permissions` prop so the visible action buttons match the Blade
// @can() guards exactly.
export default function VehicleIndex() {
    const { props } = usePage();
    const t = useTranslations();
    const vehicles = props.vehicles ?? [];
    const can = props.auth?.permissions ?? props.permissions ?? {};

    const hasPermission = (name) =>
        Array.isArray(can) ? can.includes(name) : Boolean(can[name]);

    const destroy = (id) => {
        if (confirm(t('Are you sure?'))) {
            router.delete(route('vehicle.destroy', id));
        }
    };

    return (
        <AdminLayout>
            <Head title={t('Manage Vehicle')} />
            <div className="row">
                <div className="col-sm-12">
                    <div className="card">
                        <div className="card-header card-body table-border-style">
                            <div className="d-flex justify-content-between align-items-center">
                                <h5>{t('Vehicle List')}</h5>
                                {hasPermission('create vehicle') && (
                                    <Link href={route('vehicle.create')} className="btn btn-primary">
                                        {t('Create Vehicle')}
                                    </Link>
                                )}
                            </div>
                        </div>
                        <div className="card-body table-border-style">
                            <div className="table-responsive">
                                <Table className="table">
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>{t('Vehicle ID')}</TableHead>
                                            <TableHead>{t('Name')}</TableHead>
                                            <TableHead>{t('Model')}</TableHead>
                                            <TableHead>{t('License Plate')}</TableHead>
                                            <TableHead>{t('Daily Rate')}</TableHead>
                                            <TableHead>{t('Action')}</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {vehicles.map((vehicle) => (
                                            <TableRow key={vehicle.id}>
                                                <TableCell>{vehicle.vehicle_id}</TableCell>
                                                <TableCell>{vehicle.name}</TableCell>
                                                <TableCell>{vehicle.model}</TableCell>
                                                <TableCell>{vehicle.license_plate}</TableCell>
                                                <TableCell>{vehicle.daily_rate_formatted ?? vehicle.daily_rate}</TableCell>
                                                <TableCell>
                                                    <div className="d-flex">
                                                        {hasPermission('show vehicle') && (
                                                            <Link
                                                                href={route('vehicle.show', vehicle.id)}
                                                                className="btn btn-sm btn-info me-1"
                                                            >
                                                                <i className="ti ti-eye"></i>
                                                            </Link>
                                                        )}
                                                        {hasPermission('edit vehicle') && (
                                                            <Link
                                                                href={route('vehicle.edit', vehicle.id)}
                                                                className="btn btn-sm btn-primary me-1"
                                                            >
                                                                <i className="ti ti-pencil"></i>
                                                            </Link>
                                                        )}
                                                        {hasPermission('delete vehicle') && (
                                                            <button
                                                                type="button"
                                                                onClick={() => destroy(vehicle.id)}
                                                                className="btn btn-sm btn-danger"
                                                            >
                                                                <i className="ti ti-trash"></i>
                                                            </button>
                                                        )}
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
