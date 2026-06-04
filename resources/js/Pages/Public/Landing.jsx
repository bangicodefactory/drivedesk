import { useState, useEffect, useCallback } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import {
    Users, Settings2, Fuel, DoorOpen, Star,
    ChevronLeft, ChevronRight, ArrowRight,
    Shield, Clock, MapPin, Award,
} from 'lucide-react';
import PublicLayout from '@/Layouts/PublicLayout';

function useTranslations() {
    const { translations } = usePage().props;
    return (key, fallback = key) => translations?.[key] ?? fallback;
}

function Hero({ heroImages }) {
    const t = useTranslations();
    const allSlides = [
        { image: heroImages[0], subtitle: t('subtitle_1', 'Your journey starts here'), title: t('title_1', 'Rent a Car You Love') },
        { image: heroImages[1], subtitle: t('subtitle_2', 'Explore with confidence'), title: t('title_2', 'Premium Fleet, Great Rates') },
    ];
    const slides = allSlides[0]?.image === allSlides[1]?.image ? allSlides.slice(0, 1) : allSlides;

    const [current, setCurrent] = useState(0);
    const next = useCallback(() => setCurrent(c => (c + 1) % slides.length), [slides.length]);
    const prev = () => setCurrent(c => (c - 1 + slides.length) % slides.length);

    useEffect(() => {
        const id = setInterval(next, 5000);
        return () => clearInterval(id);
    }, [next]);

    // Hero fallback uses theme tokens so it tracks the active palette.
    const defaultBg = 'linear-gradient(135deg, hsl(var(--chart-4)) 0%, hsl(var(--primary)) 100%)';

    return (
        <section className="relative h-[520px] md:h-[620px] overflow-hidden">
            {slides.map((slide, i) => (
                <div
                    key={i}
                    className={`absolute inset-0 transition-opacity duration-700 ${i === current ? 'opacity-100' : 'opacity-0'}`}
                    style={{
                        background: slide.image ? `url(${slide.image}) center/cover no-repeat` : defaultBg,
                    }}
                >
                    <div className="absolute inset-0 bg-black/55" />
                </div>
            ))}

            <div className="relative h-full flex items-center justify-center text-center px-4">
                <div className="space-y-6 max-w-3xl">
                    <p className="text-primary-foreground/80 text-lg font-medium tracking-wide uppercase">
                        {slides[current].subtitle}
                    </p>
                    <h1 className="text-4xl md:text-6xl font-bold text-white leading-tight">
                        {slides[current].title}
                    </h1>
                    <a href="#search">
                        <Button size="lg" className="mt-2 text-base px-8">
                            {t('find_car_button', 'Find a Car')} <ArrowRight className="ml-2 h-4 w-4" />
                        </Button>
                    </a>
                </div>
            </div>

            <button onClick={prev} className="absolute left-4 top-1/2 -translate-y-1/2 bg-white/20 hover:bg-white/40 text-white rounded-full p-3 transition-colors">
                <ChevronLeft className="h-5 w-5" />
            </button>
            <button onClick={next} className="absolute right-4 top-1/2 -translate-y-1/2 bg-white/20 hover:bg-white/40 text-white rounded-full p-3 transition-colors">
                <ChevronRight className="h-5 w-5" />
            </button>

            <div className="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
                {slides.map((_, i) => (
                    <button key={i} onClick={() => setCurrent(i)}
                        className={`h-2 rounded-full transition-all ${i === current ? 'w-6 bg-white' : 'w-2 bg-white/50'}`} />
                ))}
            </div>
        </section>
    );
}

function Pickup({ places, vehicleTypes }) {
    const t = useTranslations();
    return (
        <section id="search" className="bg-muted/60 py-10 border-b">
            <div className="container mx-auto px-4">
                <div className="bg-background rounded-2xl shadow-lg p-6">
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5 items-end">
                        <div className="space-y-1.5">
                            <label className="text-sm font-medium">{t('pickup_location_label', 'Pick-up Location')}</label>
                            <Select>
                                <SelectTrigger><SelectValue placeholder={t('pickup_location_select', 'Select location')} /></SelectTrigger>
                                <SelectContent>
                                    {places.map(p => <SelectItem key={p.id} value={String(p.id)}>{p.name}</SelectItem>)}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-1.5">
                            <label className="text-sm font-medium">{t('pickup_date_label', 'Pick-up Date')}</label>
                            <Input type="date" placeholder={t('check_in_placeholder', 'Check-in')} />
                        </div>
                        <div className="space-y-1.5">
                            <label className="text-sm font-medium">{t('dropoff_date_label', 'Drop-off Date')}</label>
                            <Input type="date" placeholder={t('check_out_placeholder', 'Check-out')} />
                        </div>
                        <div className="space-y-1.5">
                            <label className="text-sm font-medium">{t('car_type_label', 'Car Type')}</label>
                            <Select>
                                <SelectTrigger><SelectValue placeholder={t('select_car_placeholder', 'All types')} /></SelectTrigger>
                                <SelectContent>
                                    {vehicleTypes.map(vt => <SelectItem key={vt.id} value={String(vt.id)}>{vt.type}</SelectItem>)}
                                </SelectContent>
                            </Select>
                        </div>
                        <Button className="w-full" size="lg">{t('find_car_button', 'Find a Car')}</Button>
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
        <section className="py-16">
            <div className="container mx-auto px-4">
                <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
                    {features.map(({ icon: Icon, title, desc }) => (
                        <Card key={title} className="text-center">
                            <CardContent className="pt-8 pb-6 space-y-3">
                                <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-primary/10">
                                    <Icon className="h-7 w-7 text-primary" />
                                </div>
                                <h3 className="text-lg font-semibold">{title}</h3>
                                <p className="text-sm text-muted-foreground">{desc}</p>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </section>
    );
}

function About() {
    const t = useTranslations();
    return (
        <section className="py-16 bg-muted/40">
            <div className="container mx-auto px-4">
                <div className="grid grid-cols-1 gap-12 lg:grid-cols-2 items-center">
                    <div className="space-y-6">
                        <div className="space-y-2">
                            <p className="text-sm font-medium text-primary uppercase tracking-wider">{t('about_subtitle', 'About Us')}</p>
                            <h2 className="text-3xl font-bold leading-tight">{t('about_title', 'Trusted Car Rental Service for Over 7 Years')}</h2>
                        </div>
                        <p className="text-muted-foreground leading-relaxed">{t('about_desc_1', 'We are a leading car rental company with years of experience providing quality vehicles and exceptional service to our customers.')}</p>
                        <p className="text-muted-foreground leading-relaxed">{t('about_desc_2', 'Our fleet includes a wide range of vehicles to suit every need and budget, from economy cars to luxury SUVs.')}</p>
                        <div className="flex items-center gap-6">
                            <div className="text-center">
                                <p className="text-4xl font-bold text-primary">7+</p>
                                <p className="text-sm text-muted-foreground">{t('about_years', 'Years Experience')}</p>
                            </div>
                            <div className="text-center">
                                <p className="text-4xl font-bold text-primary">50+</p>
                                <p className="text-sm text-muted-foreground">{t('about_cars', 'Cars Available')}</p>
                            </div>
                            <div className="text-center">
                                <p className="text-4xl font-bold text-primary">800+</p>
                                <p className="text-sm text-muted-foreground">{t('about_clients', 'Happy Clients')}</p>
                            </div>
                        </div>
                    </div>
                    <div className="bg-muted rounded-2xl aspect-video flex items-center justify-center">
                        <MapPin className="h-16 w-16 text-muted-foreground/30" />
                    </div>
                </div>
            </div>
        </section>
    );
}

function CarRentals({ vehicles }) {
    const t = useTranslations();
    return (
        <section className="py-16">
            <div className="container mx-auto px-4">
                <div className="text-center mb-10 space-y-2">
                    <p className="text-sm font-medium text-primary uppercase tracking-wider">{t('car_rentals_subtitle', 'Our Fleet')}</p>
                    <h2 className="text-3xl font-bold">{t('car_rentals_title', 'Choose Your Perfect Ride')}</h2>
                </div>
                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {vehicles.map(v => (
                        <Card key={v.id} className="overflow-hidden hover:shadow-lg transition-shadow group">
                            <div className="relative h-48 bg-muted overflow-hidden">
                                <img
                                    src={v.picture ? `/storage/upload/picture/${v.picture}` : '/assets/images/client/default-car.jpg'}
                                    alt={v.name}
                                    loading="lazy"
                                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    onError={e => { e.target.src = '/assets/images/client/default-car.jpg'; }}
                                />
                                <Badge className="absolute top-3 right-3">{t('car_model', 'Model')} {v.model}</Badge>
                            </div>
                            <CardContent className="p-4 space-y-3">
                                <div className="flex items-center justify-between">
                                    <div className="flex gap-0.5">
                                        {Array.from({ length: 5 }).map((_, i) => (
                                            <Star key={i} className="h-3.5 w-3.5 fill-yellow-400 text-yellow-400" />
                                        ))}
                                    </div>
                                    <span className="text-xs text-muted-foreground">2 {t('car_reviews', 'Reviews')}</span>
                                </div>
                                <h4 className="font-semibold text-base">{v.name} {v.model}</h4>
                                <p className="text-xl font-bold text-primary">
                                    {Number(v.daily_rate).toFixed(2)} Dh
                                    <span className="text-sm font-normal text-muted-foreground"> / {t('car_per_day', 'day')}</span>
                                </p>
                                <div className="grid grid-cols-2 gap-2 text-sm text-muted-foreground">
                                    <span className="flex items-center gap-1"><Users className="h-3.5 w-3.5" /> {v.number_of_seats ?? 5} {t('car_seats', 'Seats')}</span>
                                    <span className="flex items-center gap-1"><Settings2 className="h-3.5 w-3.5" /> {v.gearbox ?? t('car_automatic', 'Auto')}</span>
                                    <span className="flex items-center gap-1"><DoorOpen className="h-3.5 w-3.5" /> 4 {t('car_doors', 'Doors')}</span>
                                    <span className="flex items-center gap-1"><Fuel className="h-3.5 w-3.5" /> {v.fuel_type ?? t('car_petrol', 'Petrol')}</span>
                                </div>
                                <Link href={route('client.details', v.id)}>
                                    <Button className="w-full mt-1">{t('car_book_now', 'Book Now')} <ArrowRight className="ml-2 h-4 w-4" /></Button>
                                </Link>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </section>
    );
}

function CarService() {
    const t = useTranslations();
    return (
        <section className="py-16 bg-primary text-primary-foreground">
            <div className="container mx-auto px-4 text-center space-y-4">
                <p className="text-sm font-medium uppercase tracking-wider opacity-80">{t('car_service_subtitle', 'Why Choose Us')}</p>
                <h2 className="text-3xl font-bold">{t('car_service_title', 'Premium Car Rental Service')}</h2>
                <p className="max-w-xl mx-auto opacity-80 leading-relaxed">{t('car_service_desc', 'Experience the difference with our premium fleet, professional drivers, and dedicated customer support available 24/7.')}</p>
                <a href="#search">
                    <Button variant="secondary" size="lg" className="mt-2">{t('car_service_btn', 'Get Started')}</Button>
                </a>
            </div>
        </section>
    );
}

function FunFact() {
    const t = useTranslations();
    const stats = [
        { value: '50+',  label: t('funfact_cars',    'Cars Available') },
        { value: '800+', label: t('funfact_clients',  'Happy Clients') },
        { value: '7+',   label: t('funfact_years',    'Years Experience') },
    ];
    return (
        <section className="py-16 bg-muted/40">
            <div className="container mx-auto px-4">
                <div className="grid grid-cols-1 gap-8 sm:grid-cols-3">
                    {stats.map(({ value, label }) => (
                        <div key={label} className="text-center space-y-2">
                            <p className="text-5xl font-bold text-primary">{value}</p>
                            <p className="text-muted-foreground font-medium">{label}</p>
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
        <section className="py-16">
            <div className="container mx-auto px-4">
                <div className="text-center mb-10 space-y-2">
                    <p className="text-sm font-medium text-primary uppercase tracking-wider">{t('popular_cars_subtitle', 'Car Categories')}</p>
                    <h2 className="text-3xl font-bold">{t('popular_cars_title', 'Browse by Category')}</h2>
                </div>
                <div className="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    {types.map(({ img, label }) => (
                        <div key={label} className="relative overflow-hidden rounded-2xl group cursor-pointer">
                            <img
                                src={img} alt={label}
                                className="w-full h-56 object-cover group-hover:scale-105 transition-transform duration-300"
                                onError={e => { e.target.parentElement.style.background = 'hsl(var(--muted))'; e.target.style.display = 'none'; }}
                            />
                            <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent" />
                            <p className="absolute bottom-4 left-4 text-white text-xl font-bold">{label}</p>
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
        <section className="py-16 bg-muted/40">
            <div className="container mx-auto px-4">
                <div className="text-center mb-10 space-y-2">
                    <p className="text-sm font-medium text-primary uppercase tracking-wider">{t('testimonials_subtitle', 'Testimonials')}</p>
                    <h2 className="text-3xl font-bold">{t('testimonials_title', 'What Our Customers Say')}</h2>
                </div>
                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    {reviews.map(({ name, text }) => (
                        <Card key={name}>
                            <CardContent className="pt-6 pb-4 space-y-3">
                                <div className="flex gap-0.5">
                                    {Array.from({ length: 5 }).map((_, i) => (
                                        <Star key={i} className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                    ))}
                                </div>
                                <p className="text-sm text-muted-foreground leading-relaxed">"{text}"</p>
                                <p className="font-semibold text-sm">{name}</p>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </section>
    );
}

function Landing({ vehicles = [], vehicleTypes = [], places = [], heroImages = [] }) {
    return (
        <>
            <Hero heroImages={heroImages} />
            <Pickup places={places} vehicleTypes={vehicleTypes} />
            <FeatureBenefit />
            <About />
            <CarRentals vehicles={vehicles} />
            <CarService />
            <FunFact />
            <PopularCars />
            <Testimonials />
        </>
    );
}

Landing.layout = (page) => <PublicLayout>{page}</PublicLayout>;
export default Landing;
