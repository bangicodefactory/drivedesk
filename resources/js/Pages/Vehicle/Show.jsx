import { Download } from 'lucide-react';
import { Link, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/Layouts/AdminLayout';

// Port of resources/views/vehicle/show.blade.php.
function Detail({ label, value }) {
    return (
        <div>
            <h6 className="text-sm font-semibold text-muted-foreground">{label}</h6>
            <p className="mb-4">{value || '-'}</p>
        </div>
    );
}

function VehicleShow({ vehicle = {} }) {
    return (
        <div className="space-y-6 p-6">
            <Card>
                <CardHeader>
                    <CardTitle>Vehicle Details</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-1 gap-x-8 md:grid-cols-2">
                        <Detail label="Vehicle ID" value={vehicle.vehicle_id_display ?? vehicle.vehicle_id} />
                        <Detail label="Vehicle Type" value={vehicle.type_label} />
                        <Detail label="Vehicle Name" value={vehicle.name} />
                        <Detail label="Vehicle Model" value={vehicle.model} />
                        <Detail label="Engine Type" value={vehicle.engine_type} />
                        <Detail label="Engine Number" value={vehicle.engine_no} />
                        <Detail label="License Plate" value={vehicle.license_plate} />
                        <Detail label="Registration Expiry Date" value={vehicle.registration_expiry_date_display} />
                        <Detail label="Daily Rate" value={vehicle.daily_rate_formatted ?? vehicle.daily_rate} />
                        <Detail label="Year of First Immatriculation" value={vehicle.year_of_first_immatriculation_display} />
                        <Detail label="Gearbox" value={vehicle.gearbox_label} />
                        <Detail label="Fuel Type" value={vehicle.fuel_type_label} />
                        <Detail label="Number of Seats" value={vehicle.number_of_seats} />
                        <Detail label="Kilometer" value={vehicle.kilometers} />
                        <Detail
                            label="Options"
                            value={
                                vehicle.option_names && vehicle.option_names.length
                                    ? vehicle.option_names.join(', ')
                                    : '-'
                            }
                        />
                        <div>
                            <h6 className="text-sm font-semibold text-muted-foreground">Document</h6>
                            <p className="mb-4">
                                {vehicle.document
                                    ? (
                                        <a
                                            href={`/storage/upload/document/${vehicle.document}`}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="underline"
                                        >
                                            {vehicle.document}
                                        </a>
                                    )
                                    : '-'}
                            </p>
                        </div>
                        <div>
                            <h6 className="text-sm font-semibold text-muted-foreground mb-1">Photo de voiture</h6>
                            {vehicle.picture
                                ? (
                                    <div className="mb-4 inline-flex items-center gap-2">
                                        <a href={`/storage/upload/picture/${vehicle.picture}`} target="_blank" rel="noreferrer">
                                            <img
                                                src={`/storage/upload/picture/${vehicle.picture}`}
                                                alt="Vehicle"
                                                loading="lazy"
                                                className="h-28 w-auto rounded border object-cover shadow-sm hover:opacity-80 transition-opacity"
                                            />
                                        </a>
                                        <a
                                            href={`/storage/upload/picture/${vehicle.picture}`}
                                            download={vehicle.picture}
                                            className="text-muted-foreground hover:text-primary transition-colors"
                                            title="Download"
                                        >
                                            <Download className="h-4 w-4" />
                                        </a>
                                    </div>
                                )
                                : <p className="mb-4 text-muted-foreground">-</p>}
                        </div>
                        <div className="md:col-span-2">
                            <h6 className="text-sm font-semibold text-muted-foreground">Notes</h6>
                            <p className="mb-4">{vehicle.notes || '-'}</p>
                        </div>
                    </div>
                    <Button variant="outline" asChild>
                        <Link href={route('vehicle.index')}>Back</Link>
                    </Button>
                </CardContent>
            </Card>
        </div>
    );
}

VehicleShow.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Vehicles', href: route('vehicle.index') },
        { label: 'Details' },
    ]}>{page}</AdminLayout>
);
export default VehicleShow;
