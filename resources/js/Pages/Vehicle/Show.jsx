import React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslations } from '@/hooks/useTranslations';

// Port of resources/views/vehicle/show.blade.php.
export default function VehicleShow() {
    const { props } = usePage();
    const t = useTranslations();
    const vehicle = props.vehicle ?? {};

    return (
        <AdminLayout>
            <Head title={t('Vehicle Detail')} />
            <div className="row">
                <div className="col-sm-12">
                    <div className="card">
                        <div className="card-header">
                            <h5>{t('Vehicle Detail')}</h5>
                        </div>
                        <div className="card-body">
                            <div className="row">
                                <div className="col-md-6 mb-3">
                                    <strong>{t('Vehicle ID')}:</strong> {vehicle.vehicle_id}
                                </div>
                                <div className="col-md-6 mb-3">
                                    <strong>{t('Name')}:</strong> {vehicle.name}
                                </div>
                                <div className="col-md-6 mb-3">
                                    <strong>{t('Model')}:</strong> {vehicle.model}
                                </div>
                                <div className="col-md-6 mb-3">
                                    <strong>{t('Engine Type')}:</strong> {vehicle.engine_type}
                                </div>
                                <div className="col-md-6 mb-3">
                                    <strong>{t('Engine No')}:</strong> {vehicle.engine_no}
                                </div>
                                <div className="col-md-6 mb-3">
                                    <strong>{t('License Plate')}:</strong> {vehicle.license_plate}
                                </div>
                                <div className="col-md-6 mb-3">
                                    <strong>{t('Registration Expiry Date')}:</strong>{' '}
                                    {vehicle.registration_expiry_date}
                                </div>
                                <div className="col-md-6 mb-3">
                                    <strong>{t('Daily Rate')}:</strong>{' '}
                                    {vehicle.daily_rate_formatted ?? vehicle.daily_rate}
                                </div>
                                <div className="col-md-6 mb-3">
                                    <strong>{t('Year Of First Immatriculation')}:</strong>{' '}
                                    {vehicle['year_of_ﬁrst_immatriculation']}
                                </div>
                                <div className="col-md-6 mb-3">
                                    <strong>{t('Gearbox')}:</strong> {vehicle.gearbox}
                                </div>
                                <div className="col-md-6 mb-3">
                                    <strong>{t('Fuel Type')}:</strong> {vehicle.fuel_type}
                                </div>
                                <div className="col-md-6 mb-3">
                                    <strong>{t('Number Of Seats')}:</strong> {vehicle.number_of_seats}
                                </div>
                                <div className="col-md-6 mb-3">
                                    <strong>{t('Kilometers')}:</strong> {vehicle.kilometers}
                                </div>
                                <div className="col-md-12 mb-3">
                                    <strong>{t('Notes')}:</strong> {vehicle.notes}
                                </div>
                            </div>
                            <Link href={route('vehicle.index')} className="btn btn-secondary">
                                {t('Back')}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
