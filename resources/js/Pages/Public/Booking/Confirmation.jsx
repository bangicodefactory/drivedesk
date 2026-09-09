import { Head, Link, usePage } from '@inertiajs/react';
import { CheckCircle, MessageCircle } from 'lucide-react';
import { Button } from '@/components/ui/button';
import PageBanner from '@/components/PageBanner';
import StorefrontLayout from '@/Layouts/StorefrontLayout';
import { useTranslations } from '@/hooks/useTranslations';

function vehiclePictureUrl(car) {
    return car?.picture ? `/storage/upload/picture/${car.picture}` : '/assets/images/client/default-car.jpg';
}

function Confirmation({ reference, car, pickupPlace, dropOffPlace, startDate, startTime, endDate, endTime, days, amount, paymentPreference }) {
    const t = useTranslations();
    const { contact, branding } = usePage().props;

    // Suffix with the tenant's actual name (Settings → General, "Application
    // Name") instead of a literal client name — a hardcoded suffix here would
    // go stale the moment an owner renames their business.
    const pageTitle = branding?.appName
        ? `${t('booking_confirmation_title', 'Réservation Envoyée')} | ${branding.appName}`
        : t('booking_confirmation_title', 'Réservation Envoyée');

    const whatsappText = encodeURIComponent(
        `Bonjour, je confirme ma réservation ${reference} : ${car?.name ?? ''} du ${startDate} au ${endDate}.`,
    );
    const whatsappHref = contact?.whatsapp ? `https://wa.me/${contact.whatsapp}?text=${whatsappText}` : null;

    // No PayPal/CMI charge actually happens yet — staff follow up by phone/
    // WhatsApp to collect it, so the copy must not imply payment is done.
    const isOnlinePayment = paymentPreference === 'paypal' || paymentPreference === 'cmi';
    const confirmationBody = isOnlinePayment
        ? t('confirmation_body_online', 'Nous vous contacterons rapidement pour finaliser votre paiement en ligne et confirmer votre réservation.')
        : t('confirmation_body', 'Nous vous contacterons rapidement pour confirmer votre réservation.');

    return (
        <>
            <Head title={pageTitle} />
            <PageBanner title={t('confirmation_banner_title', 'Réservation Envoyée')} subtitle={t('confirmation_banner_subtitle', 'Merci pour votre confiance')} />

            <section className="py-12 md:py-20">
                <div className="container mx-auto px-4 max-w-2xl">
                    <div className="bg-card rounded-2xl border border-border/60 shadow-sm p-8 md:p-10 text-center">
                        <div className="mx-auto mb-5 h-16 w-16 rounded-full bg-primary/10 flex items-center justify-center">
                            <CheckCircle className="w-8 h-8 text-primary" strokeWidth={1.5} />
                        </div>
                        <h2 className="font-display text-3xl mb-2">{t('confirmation_heading', 'Votre demande de réservation a été reçue !')}</h2>
                        <p className="text-muted-foreground mb-8">
                            {t('confirmation_reference_prefix', 'Référence :')} <strong className="text-foreground">{reference}</strong>.{' '}
                            {confirmationBody}
                        </p>

                        <div className="bg-muted/50 border border-border/60 rounded-xl p-5 text-start grid grid-cols-2 gap-y-3 text-sm mb-8">
                            <span className="text-muted-foreground">{t('summary_car', 'Voiture')}</span>
                            <span className="font-medium text-end">{car?.name} {car?.model}</span>

                            <span className="text-muted-foreground">{t('summary_pickup', 'Prise en charge')}</span>
                            <span className="font-medium text-end">{pickupPlace} — {startDate} {startTime}</span>

                            <span className="text-muted-foreground">{t('summary_return', 'Retour')}</span>
                            <span className="font-medium text-end">{dropOffPlace} — {endDate} {endTime}</span>

                            <span className="text-muted-foreground">{t('summary_days', 'Durée')}</span>
                            <span className="font-medium text-end">{days} {t('days', 'jours')}</span>

                            <span className="text-muted-foreground pt-2 border-t border-border/60">{t('summary_total', 'Total estimé')}</span>
                            <span className="font-display text-end text-primary pt-2 border-t border-border/60">{Number(amount).toFixed(0)} MAD</span>

                            {paymentPreference && (
                                <>
                                    <span className="text-muted-foreground">{t('summary_payment', 'Paiement')}</span>
                                    <span className="font-medium text-end">
                                        {paymentPreference === 'cash' && t('payment_cash', 'Paiement à la Livraison')}
                                        {paymentPreference === 'paypal' && 'PayPal'}
                                        {paymentPreference === 'cmi' && 'CMI'}
                                    </span>
                                </>
                            )}
                        </div>

                        <div className="flex flex-col sm:flex-row gap-3 justify-center">
                            {whatsappHref && (
                                <a href={whatsappHref} target="_blank" rel="noopener noreferrer">
                                    <Button className="w-full sm:w-auto rounded-full bg-green-600 hover:bg-green-700">
                                        <MessageCircle className="h-4 w-4" /> {t('confirm_on_whatsapp', 'Confirmer sur WhatsApp')}
                                    </Button>
                                </a>
                            )}
                            <Link href="/">
                                <Button variant="outline" className="w-full sm:w-auto rounded-full">{t('back_to_home', "Retour à l'Accueil")}</Button>
                            </Link>
                        </div>
                    </div>
                </div>
            </section>
        </>
    );
}

Confirmation.layout = (page) => <StorefrontLayout>{page}</StorefrontLayout>;
export default Confirmation;
