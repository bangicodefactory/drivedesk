import { z } from 'zod';
import { Controller } from 'react-hook-form';
import { Link } from '@inertiajs/react';
import { useZodForm } from '@/hooks/useZodForm';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { Star, Car, Fuel, Settings2, Wrench, Tag, Users, Calendar, MapPin } from 'lucide-react';
import PublicLayout from '@/Layouts/PublicLayout';

const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

const schema = z.object({
    name:             z.string().min(1, 'Name is required.'),
    email:            z.string().email('Enter a valid email.'),
    phone_number:     z.string().min(1, 'Phone number is required.'),
    company_name:     z.string().optional(),
    city:             z.string().optional(),
    pickup_address:   z.string().min(1, 'Pick-up location is required.'),
    drop_off_address: z.string().min(1, 'Drop-off location is required.'),
    start_date:       z.string().min(1, 'Pick-up date is required.'),
    start_time:       z.string().min(1, 'Pick-up time is required.'),
    end_date:         z.string().min(1, 'Drop-off date is required.'),
    end_time:         z.string().min(1, 'Drop-off time is required.'),
    driver:           z.boolean().optional(),
    notes:            z.string().optional(),
    vehicle_id:       z.string().min(1),
});

function Stars() {
    return (
        <div className="flex items-center gap-1">
            {Array.from({ length: 5 }).map((_, i) => (
                <Star key={i} className="h-4 w-4 fill-yellow-400 text-yellow-400" />
            ))}
            <span className="text-sm text-muted-foreground ms-1">2 Reviews</span>
        </div>
    );
}

function CarDetails({ car, similarCars = [], places = [] }) {
    const today = new Date().toISOString().slice(0, 10);

    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            vehicle_id: String(car.id),
            name: '', email: '', phone_number: '', company_name: '',
            city: '', pickup_address: '', drop_off_address: '',
            start_date: '', start_time: '', end_date: '', end_time: '',
            driver: false, notes: '',
        },
    });
    const { register, control, formState: { errors, isSubmitting } } = form;

    return (
        <div className="space-y-12 py-8">

            <div className="border-b pb-4">
                <nav className="text-sm text-muted-foreground flex items-center gap-2">
                    <Link href={route('home')} className="hover:text-foreground">Home</Link>
                    <span>/</span>
                    <span>Cars</span>
                    <span>/</span>
                    <span className="text-foreground font-medium">{car.name}</span>
                </nav>
            </div>

            <div className="grid grid-cols-1 gap-8 lg:grid-cols-[1fr_340px]">

                <div className="space-y-10">

                    <div className="space-y-4">
                        {car.picture && (
                            <div className="overflow-hidden rounded-xl border">
                                <img
                                    src={`/storage/${car.picture}`}
                                    alt={car.name}
                                    className="w-full max-h-96 object-cover"
                                />
                            </div>
                        )}
                        <Stars />
                        <div>
                            <h1 className="text-3xl font-bold">{car.name}</h1>
                            <p className="text-2xl font-semibold text-primary mt-1">
                                MAD{Number(car.daily_rate ?? 0).toFixed(2)}
                                <span className="text-base font-normal text-muted-foreground"> / Day</span>
                            </p>
                        </div>
                        {car.notes && <p className="text-muted-foreground">{car.notes}</p>}
                    </div>

                    <Card>
                        <CardHeader><CardTitle>Key Features</CardTitle></CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                {[
                                    { icon: Car,      label: 'Type',         value: car.types?.type || car.type || 'N/A' },
                                    { icon: Calendar, label: 'Year',         value: car.year_of_first_immatriculation ?? 'N/A' },
                                    { icon: Settings2,label: 'Transmission', value: car.gearbox ?? 'N/A' },
                                    { icon: Fuel,     label: 'Fuel',         value: car.fuel_type ?? 'N/A' },
                                    { icon: Users,    label: 'Passengers',   value: car.number_of_seats ? `${car.number_of_seats} Seats` : 'N/A' },
                                    { icon: MapPin,   label: 'Mileage',      value: car.kilometers ? `${Number(car.kilometers).toLocaleString()} km` : 'N/A' },
                                    { icon: Wrench,   label: 'Engine',       value: car.engine_type ?? 'N/A' },
                                    { icon: Tag,      label: 'Model',        value: car.model ?? 'N/A' },
                                ].map(({ icon: Icon, label, value }) => (
                                    <div key={label} className="flex items-start gap-3 p-3 rounded-lg bg-muted/50">
                                        <Icon className="h-5 w-5 text-primary mt-0.5 shrink-0" />
                                        <div>
                                            <p className="text-xs text-muted-foreground">{label}</p>
                                            <p className="text-sm font-medium">{value}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>
                                Price Table <span className="text-sm font-normal text-muted-foreground">(by day of the week)</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="p-0">
                            {DAYS.map((day, i) => (
                                <div
                                    key={day}
                                    className={`flex justify-between px-6 py-3 text-sm ${i % 2 === 0 ? 'bg-muted/40' : ''}`}
                                >
                                    <span>{day}</span>
                                    <span className="font-medium">MAD{Number(car.daily_rate ?? 0).toFixed(2)}</span>
                                </div>
                            ))}
                        </CardContent>
                    </Card>

                    <Card id="booking-form">
                        <CardHeader>
                            <CardTitle>Request for Booking</CardTitle>
                            <p className="text-sm text-muted-foreground">
                                Send your requirement to us. We will check email and contact you soon.
                            </p>
                        </CardHeader>
                        <CardContent>
                            <form
                                onSubmit={submit('post', route('booking.store_request'))}
                                className="space-y-6"
                            >
                                <input type="hidden" {...register('vehicle_id')} />

                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div className="space-y-1.5">
                                        <Label htmlFor="name">Your Name *</Label>
                                        <Input id="name" placeholder="Enter your name" {...register('name')} />
                                        {errors.name && <p className="text-sm text-destructive">{errors.name.message}</p>}
                                    </div>
                                    <div className="space-y-1.5">
                                        <Label htmlFor="email">Email *</Label>
                                        <Input id="email" type="email" placeholder="Enter your email" {...register('email')} />
                                        {errors.email && <p className="text-sm text-destructive">{errors.email.message}</p>}
                                    </div>
                                    <div className="space-y-1.5">
                                        <Label htmlFor="phone_number">Phone Number *</Label>
                                        <Input id="phone_number" placeholder="+212 6XX XXX XXX" {...register('phone_number')} />
                                        {errors.phone_number && <p className="text-sm text-destructive">{errors.phone_number.message}</p>}
                                    </div>
                                    <div className="space-y-1.5">
                                        <Label htmlFor="company_name">Company Name (Optional)</Label>
                                        <Input id="company_name" placeholder="Your company name" {...register('company_name')} />
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                    <div className="space-y-1.5">
                                        <Label htmlFor="city">City</Label>
                                        <Controller
                                            name="city"
                                            control={control}
                                            render={({ field }) => (
                                                <Select value={field.value} onValueChange={field.onChange}>
                                                    <SelectTrigger id="city"><SelectValue placeholder="Select City" /></SelectTrigger>
                                                    <SelectContent>
                                                        {[...new Set(places.map(p => p.city).filter(Boolean))].map(city => (
                                                            <SelectItem key={city} value={city}>{city}</SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            )}
                                        />
                                    </div>
                                    <div className="space-y-1.5">
                                        <Label htmlFor="pickup_address">Pick-up Location *</Label>
                                        <Controller
                                            name="pickup_address"
                                            control={control}
                                            render={({ field }) => (
                                                <Select value={field.value} onValueChange={field.onChange}>
                                                    <SelectTrigger id="pickup_address"><SelectValue placeholder="Select Location" /></SelectTrigger>
                                                    <SelectContent>
                                                        {places.map(p => (
                                                            <SelectItem key={p.id} value={String(p.id)}>{p.name}</SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            )}
                                        />
                                        {errors.pickup_address && <p className="text-sm text-destructive">{errors.pickup_address.message}</p>}
                                    </div>
                                    <div className="space-y-1.5">
                                        <Label htmlFor="drop_off_address">Drop-off Location *</Label>
                                        <Controller
                                            name="drop_off_address"
                                            control={control}
                                            render={({ field }) => (
                                                <Select value={field.value} onValueChange={field.onChange}>
                                                    <SelectTrigger id="drop_off_address"><SelectValue placeholder="Select Location" /></SelectTrigger>
                                                    <SelectContent>
                                                        {places.map(p => (
                                                            <SelectItem key={p.id} value={String(p.id)}>{p.name}</SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            )}
                                        />
                                        {errors.drop_off_address && <p className="text-sm text-destructive">{errors.drop_off_address.message}</p>}
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div className="space-y-1.5">
                                        <Label htmlFor="start_date">Pick-up Date *</Label>
                                        <Input id="start_date" type="date" min={today} {...register('start_date')} />
                                        {errors.start_date && <p className="text-sm text-destructive">{errors.start_date.message}</p>}
                                    </div>
                                    <div className="space-y-1.5">
                                        <Label htmlFor="start_time">Pick-up Time *</Label>
                                        <Input id="start_time" type="time" {...register('start_time')} />
                                        {errors.start_time && <p className="text-sm text-destructive">{errors.start_time.message}</p>}
                                    </div>
                                    <div className="space-y-1.5">
                                        <Label htmlFor="end_date">Drop-off Date *</Label>
                                        <Input id="end_date" type="date" min={today} {...register('end_date')} />
                                        {errors.end_date && <p className="text-sm text-destructive">{errors.end_date.message}</p>}
                                    </div>
                                    <div className="space-y-1.5">
                                        <Label htmlFor="end_time">Drop-off Time *</Label>
                                        <Input id="end_time" type="time" {...register('end_time')} />
                                        {errors.end_time && <p className="text-sm text-destructive">{errors.end_time.message}</p>}
                                    </div>
                                </div>

                                <div className="space-y-1.5">
                                    <div className="flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            id="driver"
                                            {...register('driver')}
                                            className="h-4 w-4 accent-primary"
                                        />
                                        <Label htmlFor="driver" className="cursor-pointer font-normal">
                                            Yes, I need a driver
                                        </Label>
                                    </div>
                                </div>

                                <div className="space-y-1.5">
                                    <Label htmlFor="notes">Special Requests (Optional)</Label>
                                    <Textarea id="notes" placeholder="Any special requirements..." rows={3} {...register('notes')} />
                                </div>

                                <Button type="submit" size="lg" disabled={isSubmitting} className="w-full sm:w-auto">
                                    {isSubmitting ? 'Sending…' : 'Send Request'}
                                </Button>
                            </form>
                        </CardContent>
                    </Card>

                    <div className="space-y-6">
                        <h3 className="text-xl font-semibold">2 Reviews</h3>
                        {[
                            { name: 'Khalid bensdik', text: 'It has survived not only five centuries, but also the into electronic typesetting simply fee text aunchanged. It was popularised in the sheets containing lorem ipsum is simply free text.' },
                            { name: 'Sarah Albert',   text: 'It has survived not only five centuries, but also the into electronic typesetting simply fee text aunchanged. It was popularised in the sheets containing lorem ipsum is simply free text.' },
                        ].map((review, i) => (
                            <div key={i} className="flex gap-4 pb-6 border-b last:border-0">
                                <div className="h-14 w-14 rounded-full bg-muted flex items-center justify-center shrink-0 text-lg font-semibold">
                                    {review.name[0]}
                                </div>
                                <div className="space-y-2">
                                    <div className="flex items-center justify-between gap-4">
                                        <h4 className="font-semibold">{review.name}</h4>
                                        <Stars />
                                    </div>
                                    <p className="text-sm text-muted-foreground">{review.text}</p>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                <div className="space-y-6">
                    <Card className="sticky top-20">
                        <CardHeader>
                            <CardTitle className="text-lg">Book This Car</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="text-2xl font-bold text-primary">
                                MAD{Number(car.daily_rate ?? 0).toFixed(2)}
                                <span className="text-sm font-normal text-muted-foreground"> / day</span>
                            </div>

                            <Separator />

                            <div className="space-y-2 text-sm">
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Type</span>
                                    <span className="font-medium">{car.types?.type || car.type || '—'}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Transmission</span>
                                    <span className="font-medium">{car.gearbox ?? '—'}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Fuel</span>
                                    <span className="font-medium">{car.fuel_type ?? '—'}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Seats</span>
                                    <span className="font-medium">{car.number_of_seats ?? '—'}</span>
                                </div>
                            </div>

                            <a href="#booking-form">
                                <Button className="w-full mt-2">Book Now</Button>
                            </a>

                            <Separator />

                            <div className="space-y-1 text-sm text-muted-foreground">
                                <p className="font-medium text-foreground">Need Help?</p>
                                <p>Our team is here to help you with your booking.</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            {similarCars.length > 0 && (
                <div className="space-y-6">
                    <div className="text-center space-y-1">
                        <p className="text-sm text-primary font-medium uppercase tracking-wider">Checkout our new cars</p>
                        <h2 className="text-2xl font-bold">Similar Cars Available</h2>
                    </div>
                    <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {similarCars.map((c) => (
                            <Card key={c.id} className="overflow-hidden hover:shadow-md transition-shadow">
                                {c.picture && (
                                    <img
                                        src={`/storage/${c.picture}`}
                                        alt={c.name}
                                        loading="lazy"
                                        className="w-full h-48 object-cover"
                                    />
                                )}
                                <CardContent className="p-4 space-y-3">
                                    <div className="flex items-center justify-between">
                                        <Badge variant="secondary">{c.year_of_first_immatriculation ?? 'N/A'} Model</Badge>
                                        <Stars />
                                    </div>
                                    <h4 className="font-semibold text-lg">
                                        <Link href={route('client.details', c.id)} className="hover:text-primary transition-colors">
                                            {c.name}
                                        </Link>
                                    </h4>
                                    <p className="font-semibold text-primary">
                                        MAD{Number(c.daily_rate ?? 0).toFixed(2)}
                                        <span className="text-sm font-normal text-muted-foreground"> / day</span>
                                    </p>
                                    <div className="flex items-center gap-4 text-sm text-muted-foreground">
                                        <span className="flex items-center gap-1">
                                            <Users className="h-3.5 w-3.5" /> {c.number_of_seats ?? '—'} Seats
                                        </span>
                                        <span className="flex items-center gap-1">
                                            <Settings2 className="h-3.5 w-3.5" /> {c.gearbox ?? '—'}
                                        </span>
                                        <span className="flex items-center gap-1">
                                            <Fuel className="h-3.5 w-3.5" /> {c.fuel_type ?? '—'}
                                        </span>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}

CarDetails.layout = (page) => <PublicLayout>{page}</PublicLayout>;
export default CarDetails;
