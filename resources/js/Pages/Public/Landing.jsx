import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import {
    Users, Settings2, Fuel, DoorOpen, Star,
    ArrowRight, ArrowUpRight,
    Shield, Clock, MapPin, Award,
    Calendar, CalendarDays, CalendarRange, Plane, SlidersHorizontal, Headphones,
} from 'lucide-react';
import StorefrontLayout from '@/Layouts/StorefrontLayout';
import { useTranslations } from '@/hooks/useTranslations';

function Eyebrow({ children }) {
    return <p className="eyebrow text-xs sm:text-sm font-semibold text-primary">{children}</p>;
}

function Hero({ heroImage }) {
    const t = useTranslations();

    // Hero fallback uses theme tokens so it tracks the active palette.
    const defaultBg = 'linear-gradient(135deg, hsl(var(--chart-4)) 0%, hsl(var(--primary)) 100%)';

    return (
        <section className="relative h-[600px] md:h-[720px] overflow-hidden bg-foreground">
            {/* Separate desktop/mobile banners, picked by the browser via plain
                media-query CSS (no JS breakpoint check, so there's no flash of
                the wrong image before hydration). Each falls back to the theme
                gradient independently if only one variant was uploaded. */}
            <div
                className="hidden md:block absolute inset-0"
                style={{ background: heroImage?.desktop ? `url(${heroImage.desktop}) center/cover no-repeat` : defaultBg }}
            />
            <div
                className="block md:hidden absolute inset-0"
                style={{ background: heroImage?.mobile ? `url(${heroImage.mobile}) center/cover no-repeat` : defaultBg }}
            />
            <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/20" />

            <div className="relative h-full flex flex-col items-start justify-end pb-20 md:pb-28 px-6 md:px-16">
                <div className="max-w-2xl space-y-6">
                    <p className="eyebrow text-sm font-semibold text-white/70">
                        {t('subtitle_1', 'Your journey starts here')}
                    </p>
                    <h1 className="font-display text-white text-5xl sm:text-6xl md:text-7xl">
                        {t('title_1', 'Rent a Car You Love')}
                    </h1>
                    <div className="flex flex-wrap items-center gap-4 pt-2">
                        <a href="#search">
                            <Button size="lg" className="text-base px-8 h-12 rounded-full">
                                {t('find_car_button', 'Find a Car')} <ArrowRight className="ms-2 h-4 w-4" />
                            </Button>
                        </a>
                        <a href="#fleet" className="inline-flex items-center gap-1.5 text-sm font-medium text-white/80 hover:text-white transition-colors border-b border-white/30 hover:border-white pb-0.5">
                            {t('car_rentals_title', 'Choose Your Perfect Ride')} <ArrowUpRight className="h-4 w-4" />
                        </a>
                    </div>
                </div>
            </div>
        </section>
    );
}

function Pickup({ places, vehicleTypes }) {
    const t = useTranslations();
    return (
        <section id="search" className="relative -mt-12 md:-mt-16 z-10 px-4">
            <div className="container mx-auto">
                <div className="bg-card border border-border/60 rounded-2xl shadow-xl shadow-black/5 p-5 md:p-7">
                    <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5 lg:items-end">
                        <div className="space-y-2">
                            <label className="eyebrow text-xs font-semibold text-muted-foreground">{t('pickup_location_label', 'Pick-up Location')}</label>
                            <Select>
                                <SelectTrigger><SelectValue placeholder={t('pickup_location_select', 'Select location')} /></SelectTrigger>
                                <SelectContent>
                                    {places.map(p => <SelectItem key={p.id} value={String(p.id)}>{p.name}</SelectItem>)}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <label className="eyebrow text-xs font-semibold text-muted-foreground">{t('pickup_date_label', 'Pick-up Date')}</label>
                            <Input type="date" placeholder={t('check_in_placeholder', 'Check-in')} />
                        </div>
                        <div className="space-y-2">
                            <label className="eyebrow text-xs font-semibold text-muted-foreground">{t('dropoff_date_label', 'Drop-off Date')}</label>
                            <Input type="date" placeholder={t('check_out_placeholder', 'Check-out')} />
                        </div>
                        <div className="space-y-2">
                            <label className="eyebrow text-xs font-semibold text-muted-foreground">{t('car_type_label', 'Car Type')}</label>
                            <Select>
                                <SelectTrigger><SelectValue placeholder={t('select_car_placeholder', 'All types')} /></SelectTrigger>
                                <SelectContent>
                                    {vehicleTypes.map(vt => <SelectItem key={vt.id} value={String(vt.id)}>{vt.type}</SelectItem>)}
                                </SelectContent>
                            </Select>
                        </div>
                        <Button className="w-full rounded-full h-10" size="lg">{t('find_car_button', 'Find a Car')}</Button>
                    </div>
                </div>
            </div>
        </section>
    );
}

function FeatureBenefit() {
    const t = useTranslations();
    const features = [
        { icon: Shield, title: t('feature_benefit_title_1', 'Fully Insured'), desc: t('feature_benefit_desc_1', 'All our vehicles come with comprehensive insurance coverage.') },
        { icon: Clock,  title: t('feature_benefit_title_2', '24/7 Support'),  desc: t('feature_benefit_desc_2', 'Our support team is available around the clock to assist you.') },
        { icon: Award,  title: t('feature_benefit_title_3', 'Best Rates'),    desc: t('feature_benefit_desc_3', 'Competitive pricing with no hidden fees — transparent billing.') },
    ];
    return (
        <section className="py-24 md:py-28">
            <div className="container mx-auto px-4">
                <div className="grid grid-cols-1 gap-10 md:grid-cols-3 md:divide-x md:divide-border/60">
                    {features.map(({ icon: Icon, title, desc }, i) => (
                        <div key={title} className={`space-y-3 ${i > 0 ? 'md:ps-10' : ''}`}>
                            <Icon className="h-7 w-7 text-primary" strokeWidth={1.5} />
                            <h3 className="text-lg font-semibold">{title}</h3>
                            <p className="text-sm text-muted-foreground leading-relaxed">{desc}</p>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}

function About({ vehicles = [] }) {
    const t = useTranslations();
    const stats = [
        { value: `${vehicles.length}+`, label: t('about_cars', 'Cars Available') },
        { value: '2', label: t('about_airports', 'Airports Served') },
        { value: '7/7', label: t('about_availability', 'Days a Week') },
    ];
    return (
        <section className="py-24 md:py-28 bg-muted/30 border-y border-border/60">
            <div className="container mx-auto px-4">
                <div className="grid grid-cols-1 gap-14 lg:grid-cols-2 items-center">
                    <div className="space-y-6">
                        <div className="space-y-3">
                            <Eyebrow>{t('about_subtitle', 'About Us')}</Eyebrow>
                            <h2 className="font-display text-4xl md:text-5xl leading-[1.05]">{t('about_title', 'Trusted Car Rental Service for Over 7 Years')}</h2>
                        </div>
                        <p className="text-muted-foreground leading-relaxed max-w-lg">{t('about_desc_1', 'We are a leading car rental company with years of experience providing quality vehicles and exceptional service to our customers.')}</p>
                        <p className="text-muted-foreground leading-relaxed max-w-lg">{t('about_desc_2', 'Our fleet includes a wide range of vehicles to suit every need and budget, from economy cars to luxury SUVs.')}</p>
                        {/* Real, verifiable figures only — the live fleet count, not an
                            invented "years in business" / "happy clients" number. */}
                        <div className="flex items-stretch gap-8 pt-4 divide-x divide-border/60">
                            {stats.map(({ value, label }, i) => (
                                <div key={label} className={i > 0 ? 'ps-8' : ''}>
                                    <p className="font-display text-4xl text-primary">{value}</p>
                                    <p className="text-sm text-muted-foreground mt-1">{label}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                    <div className="relative aspect-[4/5] lg:aspect-square rounded-2xl overflow-hidden border border-border/60 bg-foreground/5 flex items-center justify-center">
                        <MapPin className="h-16 w-16 text-muted-foreground/20" strokeWidth={1} />
                    </div>
                </div>
            </div>
        </section>
    );
}

function Services() {
    const t = useTranslations();
    const services = [
        { icon: Calendar, title: t('services_daily_title', 'Daily Rentals'), desc: t('services_daily_desc', 'Flexible daily rental options for short trips.') },
        { icon: CalendarDays, title: t('services_weekly_title', 'Weekly Rentals'), desc: t('services_weekly_desc', 'Discounted rates for weekly rentals.') },
        { icon: CalendarRange, title: t('services_monthly_title', 'Monthly Rentals'), desc: t('services_monthly_desc', 'Best value for long-term stays.') },
        { icon: Plane, title: t('services_airport_title', 'Airport Pickup'), desc: t('services_airport_desc', 'Convenient service at both airports.') },
        { icon: SlidersHorizontal, title: t('services_flexible_title', 'Flexible Terms'), desc: t('services_flexible_desc', 'Rental options customized to your needs.') },
        { icon: Headphones, title: t('services_support_title', '24/7 Support'), desc: t('services_support_desc', 'Round-the-clock assistance for peace of mind.') },
    ];
    return (
        <section className="py-24 md:py-28">
            <div className="container mx-auto px-4">
                <div className="mb-14 space-y-3 max-w-xl">
                    <Eyebrow>{t('services_subtitle', 'Our Services')}</Eyebrow>
                    <h2 className="font-display text-4xl md:text-5xl">{t('services_title', 'Comprehensive Car Rental Services')}</h2>
                </div>
                <div className="grid grid-cols-1 gap-px sm:grid-cols-2 lg:grid-cols-3 bg-border/60 border border-border/60 rounded-2xl overflow-hidden">
                    {services.map(({ icon: Icon, title, desc }) => (
                        <div key={title} className="bg-card p-8 space-y-3 hover:bg-muted/40 transition-colors">
                            <Icon className="h-7 w-7 text-primary" strokeWidth={1.5} />
                            <h3 className="text-lg font-bold">{title}</h3>
                            <p className="text-sm text-muted-foreground leading-relaxed">{desc}</p>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}

function CarRentals({ vehicles }) {
    const t = useTranslations();
    return (
        <section id="fleet" className="py-24 md:py-28 bg-muted/30 border-y border-border/60">
            <div className="container mx-auto px-4">
                <div className="mb-14 space-y-3 max-w-xl">
                    <Eyebrow>{t('car_rentals_subtitle', 'Our Fleet')}</Eyebrow>
                    <h2 className="font-display text-4xl md:text-5xl">{t('car_rentals_title', 'Choose Your Perfect Ride')}</h2>
                </div>
                <div className="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    {vehicles.map(v => (
                        <div key={v.id} className="group bg-card rounded-xl overflow-hidden border border-border/60 hover:border-foreground/20 hover:shadow-xl hover:shadow-black/5 transition-all duration-300">
                            <div className="relative h-52 bg-muted overflow-hidden">
                                <img
                                    src={v.picture ? `/storage/upload/picture/${v.picture}` : '/assets/images/client/default-car.jpg'}
                                    alt={v.name}
                                    loading="lazy"
                                    className="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-500"
                                    onError={e => { e.target.src = '/assets/images/client/default-car.jpg'; }}
                                />
                                <Badge className="absolute top-3 end-3 rounded-full">{t('car_model', 'Model')} {v.model}</Badge>
                            </div>
                            <div className="p-5 space-y-3">
                                <div className="flex items-center justify-between">
                                    <div className="flex gap-0.5">
                                        {Array.from({ length: 5 }).map((_, i) => (
                                            <Star key={i} className="h-3.5 w-3.5 fill-yellow-400 text-yellow-400" />
                                        ))}
                                    </div>
                                    <span className="text-xs text-muted-foreground">2 {t('car_reviews', 'Reviews')}</span>
                                </div>
                                <h4 className="font-semibold text-base">{v.name} {v.model}</h4>
                                <p className="text-xl font-display text-primary">
                                    {Number(v.daily_rate).toFixed(2)} Dh
                                    <span className="text-sm font-sans font-normal text-muted-foreground"> / {t('car_per_day', 'day')}</span>
                                </p>
                                <div className="grid grid-cols-2 gap-2 text-sm text-muted-foreground border-t border-border/60 pt-3">
                                    <span className="flex items-center gap-1.5"><Users className="h-3.5 w-3.5" /> {v.number_of_seats ?? 5} {t('car_seats', 'Seats')}</span>
                                    <span className="flex items-center gap-1.5"><Settings2 className="h-3.5 w-3.5" /> {v.gearbox ?? t('car_automatic', 'Auto')}</span>
                                    <span className="flex items-center gap-1.5"><DoorOpen className="h-3.5 w-3.5" /> 4 {t('car_doors', 'Doors')}</span>
                                    <span className="flex items-center gap-1.5"><Fuel className="h-3.5 w-3.5" /> {v.fuel_type ?? t('car_petrol', 'Petrol')}</span>
                                </div>
                                <Link href={route('reserve.create', { vehicle: v.id })}>
                                    <Button className="w-full mt-1 rounded-full">{t('car_book_now', 'Book Now')} <ArrowRight className="ms-2 h-4 w-4" /></Button>
                                </Link>
                                <Link href={route('client.details', v.id)} className="block text-center text-sm text-muted-foreground hover:text-foreground mt-1 transition-colors">
                                    {t('car_view_details', 'Voir les détails')}
                                </Link>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}

function CarService() {
    const t = useTranslations();
    return (
        <section className="relative py-24 md:py-28 bg-foreground text-background overflow-hidden">
            <div className="absolute inset-0 bg-gradient-to-br from-transparent via-transparent to-primary/10" />
            <div className="relative container mx-auto px-4 text-center space-y-5">
                <p className="eyebrow text-sm font-semibold text-background/50">{t('car_service_subtitle', 'Why Choose Us')}</p>
                <h2 className="font-display text-4xl md:text-5xl">{t('car_service_title', 'Premium Car Rental Service')}</h2>
                <p className="max-w-xl mx-auto text-background/60 leading-relaxed">{t('car_service_desc', 'Experience the difference with our premium fleet, professional drivers, and dedicated customer support available 24/7.')}</p>
                <a href="#search" className="inline-block pt-2">
                    <Button size="lg" className="rounded-full px-8 h-12">{t('car_service_btn', 'Get Started')}</Button>
                </a>
            </div>
        </section>
    );
}

function FunFact({ vehicles = [] }) {
    const t = useTranslations();
    const stats = [
        { value: `${vehicles.length}+`, label: t('funfact_cars', 'Cars Available') },
        { value: '2',   label: t('funfact_airports', 'Airports Served') },
        { value: '7/7', label: t('funfact_availability', 'Days a Week') },
    ];
    return (
        <section className="py-20 border-b border-border/60">
            <div className="container mx-auto px-4">
                <div className="grid grid-cols-1 gap-10 sm:grid-cols-3 sm:divide-x sm:divide-border/60">
                    {stats.map(({ value, label }, i) => (
                        <div key={label} className={`text-center space-y-1.5 ${i > 0 ? 'sm:ps-4' : ''}`}>
                            <p className="font-display text-5xl md:text-6xl text-primary">{value}</p>
                            <p className="text-muted-foreground text-sm eyebrow font-medium">{label}</p>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}

function PopularCars() {
    const t = useTranslations();
    const types = [
        { img: '/assets/images/client/popular-car-1.jpg', label: t('popular_car_1', 'SUV') },
        { img: '/assets/images/client/popular-car-2.jpg', label: t('popular_car_2', 'Sports') },
        { img: '/assets/images/client/popular-car-3.jpg', label: t('popular_car_3', 'Hatchback') },
    ];
    return (
        <section className="py-24 md:py-28">
            <div className="container mx-auto px-4">
                <div className="mb-14 space-y-3 max-w-xl">
                    <Eyebrow>{t('popular_cars_subtitle', 'Car Categories')}</Eyebrow>
                    <h2 className="font-display text-4xl md:text-5xl">{t('popular_cars_title', 'Browse by Category')}</h2>
                </div>
                <div className="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    {types.map(({ img, label }) => (
                        <div key={label} className="relative overflow-hidden rounded-xl border border-border/60 group cursor-pointer">
                            <img
                                src={img} alt={label}
                                className="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-500"
                                onError={e => { e.target.parentElement.style.background = 'hsl(var(--muted))'; e.target.style.display = 'none'; }}
                            />
                            <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent" />
                            <p className="absolute bottom-5 start-5 text-white font-display text-2xl">{label}</p>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}

function Testimonials() {
    const t = useTranslations();
    const reviews = [
        { name: t('testimonial_name_1', 'Ahmed K.'),   text: t('testimonial_text_1', 'Excellent service and very clean cars. Highly recommended!') },
        { name: t('testimonial_name_2', 'Sarah M.'),   text: t('testimonial_text_2', 'Easy booking process and great customer support throughout.') },
        { name: t('testimonial_name_3', 'Omar B.'),    text: t('testimonial_text_3', 'Best car rental experience I have had. Will use again.') },
        { name: t('testimonial_name_4', 'Fatima A.'),  text: t('testimonial_text_4', 'Affordable prices and professional staff. 5 stars!') },
    ];
    return (
        <section className="py-24 md:py-28 bg-muted/30 border-t border-border/60">
            <div className="container mx-auto px-4">
                <div className="mb-14 space-y-3 max-w-xl">
                    <Eyebrow>{t('testimonials_subtitle', 'Testimonials')}</Eyebrow>
                    <h2 className="font-display text-4xl md:text-5xl">{t('testimonials_title', 'What Our Customers Say')}</h2>
                </div>
                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    {reviews.map(({ name, text }) => (
                        <div key={name} className="bg-card border border-border/60 rounded-xl p-6 space-y-4">
                            <div className="flex gap-0.5">
                                {Array.from({ length: 5 }).map((_, i) => (
                                    <Star key={i} className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                ))}
                            </div>
                            <p className="text-sm text-muted-foreground leading-relaxed">"{text}"</p>
                            <p className="font-semibold text-sm">{name}</p>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}

function Landing({ vehicles = [], vehicleTypes = [], places = [], heroImage = null }) {
    return (
        <>
            <Hero heroImage={heroImage} />
            <Pickup places={places} vehicleTypes={vehicleTypes} />
            <FeatureBenefit />
            <About vehicles={vehicles} />
            <CarRentals vehicles={vehicles} />
            <Services />
            <CarService />
            <FunFact vehicles={vehicles} />
            <PopularCars />
            <Testimonials />
        </>
    );
}

Landing.layout = (page) => <StorefrontLayout>{page}</StorefrontLayout>;
export default Landing;
