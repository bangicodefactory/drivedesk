import { Download } from 'lucide-react';
import { Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/Layouts/AdminLayout';

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
                <div className="mb-4 inline-flex items-start gap-2">
                    <a href={url} target="_blank" rel="noreferrer">
                        <img
                            src={url}
                            alt={label}
                            className="h-28 w-auto rounded border object-cover shadow-sm hover:opacity-80 transition-opacity"
                        />
                    </a>
                    <a
                        href={url}
                        download={name}
                        className="text-muted-foreground hover:text-primary transition-colors"
                        title="Download"
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
                        title="Download"
                    >
                        <Download className="h-4 w-4" />
                    </a>
                </div>
            )}
        </div>
    );
}

function DriverShow({ driver = {}, user = {} }) {
    return (
        <div className="space-y-6 p-6">
            <Card>
                <CardHeader>
                    <CardTitle>Details</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-1 gap-x-8 md:grid-cols-2">
                        <Detail label="ID" value={driver.driver_id_display ?? '-'} />
                        <Detail label="First Name" value={user.first_name} />
                        <Detail label="Last Name" value={user.last_name} />
                        <Detail label="Email" value={user.email} />
                        <Detail label="Phone Number" value={user.phone_number} />
                        <Detail label="Gender" value={driver.gender} />
                        <Detail label="Age" value={driver.age && driver.age !== 0 ? driver.age : '-'} />
                        <Detail label="Address" value={driver.address} />
                        <Detail label="Birth Date" value={driver.birth_date_display} />
                        <Detail label="License Number" value={driver.license_number} />
                        <Detail label="Issue Date" value={driver.issue_date_display} />
                        <Detail label="Expiration Date" value={driver.expiration_date_display} />
                        <FileDetail label="License 1:" name={driver.license} dir="license" />
                        <FileDetail label="License 2:" name={driver.license_1} dir="license" />
                        <Detail label="Reference" value={driver.reference} className="md:col-span-2" />
                        <FileDetail label="ID file 1:" name={driver.document} dir="document" />
                        <FileDetail label="ID file 2:" name={driver.document_1} dir="document" />
                        <Detail label="notes" value={driver.notes} />
                        <Detail label="ICE_company" value={driver.ICE_company} className="md:col-span-2" />
                    </div>
                </CardContent>
            </Card>
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
