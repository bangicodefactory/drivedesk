import { Head, usePage } from '@inertiajs/react';
import { z } from 'zod';
import { useZodForm } from '@/hooks/useZodForm';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import FieldError from '@/components/FieldError';
import { fieldA11y } from '@/lib/fieldA11y';
import { MessageCircle, Mail, MapPin, Phone, Clock } from 'lucide-react';
import StorefrontLayout from '@/Layouts/StorefrontLayout';
import { useTranslations } from '@/hooks/useTranslations';

/**
 * /contact (BAN-333).
 *
 * Replaces a Blade view whose entire body was "This is a placeholder contact
 * page. Replace with real content." — linked from the storefront header, the
 * footer and the booking confirmation, so it was the answer a visitor got when
 * a rental went wrong. (SeoController keeps /contact out of sitemap.xml
 * precisely because it was scaffolding; worth revisiting now that it is not.)
 *
 * Every channel shown here is one the tenant actually filled in (Settings →
 * General, via the `contact` shared prop); a channel with no value is left out
 * rather than shown empty. The form appears only when the deployment has an
 * address to deliver to, so this page cannot repeat the newsletter endpoint's
 * trick of reporting success and discarding the message.
 *
 * There is deliberately no map. We have a postal address string and nothing
 * that geocodes it, and an iframe pointing at a guessed location is worse than
 * no map at all.
 */

const schema = z.object({
    name: z.string().min(1, 'Votre nom est requis.'),
    email: z.string().email('Adresse email invalide.'),
    phone: z.string().optional(),
    reference: z.string().optional(),
    message: z.string().min(1, 'Votre message est requis.'),
});

function ChannelCard({ icon: Icon, title, description, value, href, tone }) {
    const body = (
        <>
            <span
                className="flex h-10 w-10 items-center justify-center rounded-lg"
                style={{ background: tone.bg }}
            >
                <Icon className="h-5 w-5" strokeWidth={1.9} style={{ color: tone.fg }} />
            </span>
            <span className="block">
                <span className="font-display block text-xl uppercase">{title}</span>
                <span className="mt-1 block text-sm leading-relaxed text-muted-foreground">{description}</span>
                <span className="mt-2 block font-bold">{value}</span>
            </span>
        </>
    );

    const className = 'flex flex-col gap-3 rounded-lg border border-border bg-card p-5';

    return href
        ? <a href={href} target={href.startsWith('http') ? '_blank' : undefined} rel="noopener noreferrer" className={`${className} transition-colors hover:border-foreground/25`}>{body}</a>
        : <div className={className}>{body}</div>;
}

function Contact({ canSendMessage = false }) {
    const t = useTranslations();
    const { contact, branding } = usePage().props;

    const pageTitle = branding?.appName
        ? `${t('nav_contact', 'Contact')} | ${branding.appName}`
        : t('nav_contact', 'Contact');

    const { form, submit } = useZodForm(schema, {
        defaultValues: { name: '', email: '', phone: '', reference: '', message: '' },
    });
    const { register, reset, formState: { errors, isSubmitting } } = form;

    const channels = [
        contact?.whatsapp && {
            key: 'whatsapp',
            icon: MessageCircle,
            title: 'WhatsApp',
            description: t('contact_whatsapp_desc', "Le plus rapide pendant les horaires d'ouverture."),
            value: contact.whatsapp,
            href: `https://wa.me/${contact.whatsapp}`,
            tone: { bg: 'hsl(var(--success) / 0.12)', fg: 'hsl(var(--success))' },
        },
        contact?.phone && {
            key: 'phone',
            icon: Phone,
            title: t('contact_phone_title', 'Téléphone'),
            description: t('contact_phone_desc', "Pour joindre l'agence directement."),
            value: contact.phone,
            href: `tel:${contact.phone}`,
            tone: { bg: 'hsl(var(--accent))', fg: 'hsl(var(--primary))' },
        },
        contact?.email && {
            key: 'email',
            icon: Mail,
            title: t('contact_email_title', 'Email'),
            description: t('contact_email_desc', "Pour les devis, les factures et les demandes d'entreprise."),
            value: contact.email,
            href: `mailto:${contact.email}`,
            tone: { bg: 'hsl(var(--accent))', fg: 'hsl(var(--primary))' },
        },
        contact?.address && {
            key: 'address',
            icon: MapPin,
            title: t('contact_branch_title', 'Agence'),
            description: t('contact_branch_desc', 'Retrait et restitution des véhicules.'),
            value: contact.address,
            href: null,
            tone: { bg: 'hsl(var(--muted))', fg: 'hsl(var(--foreground))' },
        },
    ].filter(Boolean);

    const hours = [
        contact?.hoursWeekday && [t('hours_weekday_label', 'Lun – Ven'), contact.hoursWeekday],
        contact?.hoursSaturday && [t('hours_saturday_label', 'Sam'), contact.hoursSaturday],
        contact?.hoursSunday && [t('hours_sunday_label', 'Dim'), contact.hoursSunday],
    ].filter(Boolean);

    return (
        <>
            <Head title={pageTitle} />

            <section className="relative overflow-hidden bg-foreground">
                <div
                    className="absolute inset-0"
                    style={{ background: 'radial-gradient(620px 320px at 82% 20%, hsl(var(--primary) / 0.34), transparent 64%)' }}
                />
                <div className="relative container mx-auto px-4 py-12 md:py-14">
                    <div className="max-w-2xl space-y-3">
                        <p className="eyebrow text-xs font-bold" style={{ color: 'hsl(var(--chart-2))' }}>
                            {t('contact_eyebrow', 'Nous joindre')}
                        </p>
                        <h1 className="font-display text-4xl uppercase text-background md:text-5xl">
                            {t('contact_title', 'Une question ?')}
                        </h1>
                        <p className="leading-relaxed text-background/70">
                            {t('contact_subtitle', 'Pour une réservation en cours, indiquez votre référence — elle commence par BR et figure sur votre confirmation.')}
                        </p>
                    </div>
                </div>
                <div
                    className="absolute inset-x-0 bottom-0 h-[5px]"
                    style={{ background: 'linear-gradient(100deg, hsl(var(--chart-2)) 0%, hsl(var(--primary)) 48%, hsl(var(--primary)) 100%)' }}
                />
            </section>

            {channels.length > 0 && (
                <section className="container mx-auto grid grid-cols-1 gap-5 px-4 pt-8 sm:grid-cols-2 lg:grid-cols-4">
                    {channels.map(({ key, ...rest }) => <ChannelCard key={key} {...rest} />)}
                </section>
            )}

            <section className="container mx-auto grid grid-cols-1 items-start gap-5 px-4 py-8 lg:grid-cols-[1fr_340px]">
                {canSendMessage ? (
                    <form
                        // reset on success: `back()` re-renders this same
                        // component instance, so react-hook-form state survives
                        // the round trip. Without it the fields stay populated
                        // after the toast fades, which reads as "it did not
                        // send" and invites a second press -- straight into
                        // throttle:5,1.
                        onSubmit={submit('post', route('contact.send'), {
                            preserveScroll: true,
                            onSuccess: () => reset(),
                        })}
                        className="rounded-lg border border-border bg-card p-5 md:p-6"
                    >
                        <h2 className="font-display text-2xl uppercase">{t('contact_form_title', 'Écrivez-nous')}</h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {t('contact_form_subtitle', 'Nous répondons pendant les horaires d’ouverture.')}
                        </p>

                        <div className="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div className="space-y-1.5">
                                <Label htmlFor="contact-name">{t('contact_field_name', 'Nom')}</Label>
                                <Input id="contact-name" {...register('name')} {...fieldA11y(errors, 'name')} />
                                <FieldError name="name" errors={errors} />
                            </div>
                            <div className="space-y-1.5">
                                <Label htmlFor="contact-email">{t('email', 'Adresse Email')}</Label>
                                <Input id="contact-email" type="email" {...register('email')} {...fieldA11y(errors, 'email')} />
                                <FieldError name="email" errors={errors} />
                            </div>
                            <div className="space-y-1.5">
                                <Label htmlFor="contact-phone">{t('phone', 'Numéro de Téléphone')}</Label>
                                <Input id="contact-phone" type="tel" {...register('phone')} />
                            </div>
                            <div className="space-y-1.5">
                                <Label htmlFor="contact-reference">
                                    {t('confirmation_reference_label', 'Référence')}{' '}
                                    <span className="font-normal text-muted-foreground">
                                        ({t('optional', 'facultatif')})
                                    </span>
                                </Label>
                                <Input id="contact-reference" placeholder="BR-00000" {...register('reference')} />
                            </div>
                            <div className="space-y-1.5 sm:col-span-2">
                                <Label htmlFor="contact-message">{t('contact_field_message', 'Votre message')}</Label>
                                <Textarea id="contact-message" rows={5} {...register('message')} {...fieldA11y(errors, 'message')} />
                                <FieldError name="message" errors={errors} />
                            </div>
                        </div>

                        <div className="mt-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <p className="max-w-sm text-xs leading-relaxed text-muted-foreground">
                                {t('contact_privacy_note', 'Vos coordonnées servent uniquement à répondre à cette demande.')}
                            </p>
                            <Button type="submit" size="lg" disabled={isSubmitting} className="h-12 px-7 text-base">
                                {isSubmitting ? t('sending', 'Envoi…') : t('contact_send', 'Envoyer')}
                            </Button>
                        </div>
                    </form>
                ) : (
                    /* No address configured for this deployment, so there is
                       nowhere for a message to go. Say so and point at the
                       channels above rather than showing a form that would
                       swallow it. */
                    <div className="rounded-lg border border-dashed border-border p-8 text-center">
                        <p className="text-muted-foreground">
                            {t('contact_form_unavailable', 'Le formulaire est indisponible pour le moment. Contactez-nous par téléphone ou WhatsApp.')}
                        </p>
                    </div>
                )}

                {hours.length > 0 && (
                    <div className="rounded-lg border border-border bg-card p-5">
                        <h2 className="font-display flex items-center gap-2 text-2xl uppercase">
                            <Clock className="h-5 w-5 text-primary" strokeWidth={1.9} />
                            {t('contact_hours_title', 'Horaires')}
                        </h2>
                        <dl className="mt-4 space-y-3 text-sm">
                            {hours.map(([label, value]) => (
                                <div key={label} className="flex justify-between gap-3">
                                    <dt className="font-bold">{label}</dt>
                                    <dd className="text-muted-foreground">{value}</dd>
                                </div>
                            ))}
                        </dl>
                    </div>
                )}
            </section>
        </>
    );
}

Contact.layout = (page) => <StorefrontLayout>{page}</StorefrontLayout>;
export default Contact;
