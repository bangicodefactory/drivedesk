import { useState } from 'react';
import { Head, usePage, Link } from '@inertiajs/react';
import { z } from 'zod';
import { useZodForm } from '@/hooks/useZodForm';
import { useTranslation } from '@/hooks/useTranslation';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription,
} from '@/components/ui/dialog';
import {
    LayoutDashboard, Car, CalendarRange, FileSignature, ReceiptText, Languages,
    Check, ArrowRight, Loader2,
} from 'lucide-react';

// DriveDesk brand palette (dark marketing theme).
const C = {
    bg: '#0E0F11', panel: '#17191C', line: 'rgba(255,255,255,0.09)',
    ink: '#FFFFFF', muted: '#828A96', orange: '#E5601E',
    grad: 'linear-gradient(100deg,#F7A21E 0%,#E5601E 48%,#D2400F 100%)',
};
// Latin brand display face (Saira Condensed italic). Used by the wordmark, which
// stays Latin in every locale, and by the headings when the locale is Latin.
const latinDisplay = { fontFamily: "'Saira Condensed', system-ui, sans-serif", fontStyle: 'italic', fontWeight: 800, textTransform: 'uppercase', letterSpacing: '0.005em' };

// "Gauge" mark (reversed — white ink on dark), recreated from the handoff geometry.
function Gauge({ size = 40 }) {
    const ticks = [
        [38.79, 85.28, 43.29, 79.92], [28.12, 68.54, 34.89, 66.73], [28.99, 48.71, 35.57, 51.11],
        [41.07, 32.97, 45.09, 38.7], [60, 27, 60, 34], [78.93, 32.97, 74.91, 38.7],
        [91.01, 48.71, 84.43, 51.11], [91.88, 68.54, 85.11, 66.73], [81.21, 85.28, 76.71, 79.92],
    ];
    return (
        <svg viewBox="0 0 120 120" width={size} height={size}>
            <defs>
                <linearGradient id="ddg" x1="0" y1="1" x2="1" y2="0">
                    <stop offset="0" stopColor="#D2400F" /><stop offset="0.48" stopColor="#E5601E" /><stop offset="1" stopColor="#F7A21E" />
                </linearGradient>
            </defs>
            <circle cx="60" cy="60" r="44" fill="none" stroke="#fff" strokeWidth="6" opacity="0.16" />
            <path fill="none" stroke="url(#ddg)" strokeWidth="8" strokeLinecap="round" d="M28.7 91.3 A 44 44 0 1 1 99.6 76" />
            {ticks.map((t, i) => <line key={i} x1={t[0]} y1={t[1]} x2={t[2]} y2={t[3]} stroke="#fff" strokeWidth="3" strokeLinecap="round" />)}
            <path fill="#fff" strokeLinejoin="round" d="M86 38 L62.3 62.7 L51.5 68.5 L57.7 57.3 Z" />
            <circle cx="60" cy="60" r="7" fill="url(#ddg)" /><circle cx="60" cy="60" r="2.6" fill="#fff" />
        </svg>
    );
}

// Brand wordmark — always Latin ("DriveDesk" is the brand, not translated).
const Wordmark = ({ size = 26 }) => (
    <span style={{ ...latinDisplay, fontSize: size, color: C.ink, lineHeight: 1 }} dir="ltr">
        Drive<span style={{ color: C.orange }}>Desk</span>
    </span>
);

// Locale-aware heading face. Arabic (incl. Moroccan Darija 'ary') is cursive:
// no italic, no letter-spacing (would disjoint the script), and a display face
// that actually has Arabic glyphs (Cairo) instead of the Latin-only Saira.
function useDisplay() {
    const { locale } = usePage().props;
    const rtl = (locale || '').startsWith('ar');
    const display = rtl
        ? { fontFamily: "'Cairo', system-ui, sans-serif", fontStyle: 'normal', fontWeight: 800, textTransform: 'none', letterSpacing: '0' }
        : latinDisplay;
    const bodyFont = rtl ? "'Cairo', system-ui, sans-serif" : "'Hanken Grotesk', system-ui, sans-serif";
    // Wide tracking on eyebrows looks great in Latin but breaks Arabic — drop it.
    const ls = (v) => (rtl ? 'normal' : v);
    return { rtl, display, bodyFont, ls };
}

function buildSchema(t) {
    return z.object({
        name: z.string().min(1, t('dg_val_name', 'Your name is required.')),
        company: z.string().min(1, t('dg_val_company', 'Your agency name is required.')),
        email: z.string().min(1, t('dg_val_email_required', 'Email is required.')).email(t('dg_val_email_invalid', 'Enter a valid email.')),
        phone: z.string().optional(),
        message: z.string().optional(),
    });
}

function DemoModal({ open, onOpenChange }) {
    const t = useTranslation();
    const { flash } = usePage().props;
    const { display, bodyFont } = useDisplay();
    const [sent, setSent] = useState(false);
    const { form, submit } = useZodForm(buildSchema(t), {
        defaultValues: { name: '', company: '', email: '', phone: '', message: '' },
    });
    const { register, formState: { errors, isSubmitting } } = form;

    const field = { width: '100%', background: '#0E0F11', border: `1px solid ${C.line}`, borderRadius: 10, padding: '11px 13px', color: C.ink, fontSize: 15, outline: 'none' };
    const label = { fontSize: 13, fontWeight: 700, color: C.muted, marginBottom: 6, display: 'block' };
    const err = { color: '#FCA5A5', fontSize: 13, marginTop: 4 };

    return (
        <Dialog open={open} onOpenChange={(v) => { onOpenChange(v); if (!v) setSent(false); }}>
            <DialogContent style={{ background: C.panel, border: `1px solid ${C.line}`, color: C.ink, maxWidth: 520, fontFamily: bodyFont }}>
                {sent ? (
                    <div style={{ textAlign: 'center', padding: '20px 8px' }}>
                        <div style={{ width: 64, height: 64, borderRadius: 999, background: C.grad, display: 'inline-flex', alignItems: 'center', justifyContent: 'center', marginBottom: 16 }}>
                            <Check size={32} color="#fff" strokeWidth={3} />
                        </div>
                        <h3 style={{ ...display, fontSize: 30 }}>{t('dg_modal_success_title', "You're on the list.")}</h3>
                        <p style={{ color: C.muted, marginTop: 8, fontSize: 15 }}>
                            {flash?.success ?? t('dg_modal_success_desc', "Thanks! We'll be in touch shortly to schedule your demo.")}
                        </p>
                        <div style={{ marginTop: 20, paddingTop: 18, borderTop: `1px solid ${C.line}` }}>
                            <p style={{ color: C.muted, fontSize: 14, lineHeight: 1.55 }}>
                                {t('dg_modal_success_note', "Once your demo is confirmed, we'll email you a username and password for your DriveDesk workspace. Already have your credentials?")}
                            </p>
                            <Link href={route('login')} style={{ marginTop: 14, background: C.grad, color: '#fff', textDecoration: 'none', borderRadius: 999, padding: '12px 22px', fontSize: 15, fontWeight: 800, display: 'inline-flex', alignItems: 'center', gap: 8 }}>
                                {t('dg_modal_login', 'Go to login')} <ArrowRight size={17} />
                            </Link>
                        </div>
                    </div>
                ) : (
                    <>
                        <DialogHeader>
                            <DialogTitle style={{ ...display, fontSize: 30, color: C.ink }}>{t('dg_modal_title', 'Book a demo')}</DialogTitle>
                            <DialogDescription style={{ color: C.muted }}>
                                {t('dg_modal_desc', 'See DriveDesk on your own fleet. A 20-minute call, no commitment.')}
                            </DialogDescription>
                        </DialogHeader>
                        <form onSubmit={submit('post', route('demo.request'), { preserveScroll: true, onSuccess: () => { setSent(true); form.reset(); } })} style={{ display: 'grid', gap: 14, marginTop: 6 }}>
                            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
                                <div><label style={label}>{t('dg_modal_name', 'Name')}</label><input style={field} {...register('name')} />{errors.name && <p style={err}>{errors.name.message}</p>}</div>
                                <div><label style={label}>{t('dg_modal_agency', 'Agency')}</label><input style={field} {...register('company')} />{errors.company && <p style={err}>{errors.company.message}</p>}</div>
                            </div>
                            <div><label style={label}>{t('dg_modal_email', 'Email')}</label><input style={field} type="email" {...register('email')} />{errors.email && <p style={err}>{errors.email.message}</p>}</div>
                            <div><label style={label}>{t('dg_modal_phone', 'Phone')} <span style={{ fontWeight: 400 }}>{t('dg_modal_optional', '(optional)')}</span></label><input style={field} {...register('phone')} /></div>
                            <div><label style={label}>{t('dg_modal_message', 'Anything we should know?')} <span style={{ fontWeight: 400 }}>{t('dg_modal_optional', '(optional)')}</span></label><textarea rows={3} style={{ ...field, resize: 'vertical' }} {...register('message')} /></div>
                            <button type="submit" disabled={isSubmitting} style={{ marginTop: 4, background: C.grad, color: '#fff', border: 0, borderRadius: 999, padding: '14px 22px', fontSize: 16, fontWeight: 800, cursor: 'pointer', display: 'inline-flex', alignItems: 'center', justifyContent: 'center', gap: 8, opacity: isSubmitting ? 0.7 : 1 }}>
                                {isSubmitting ? <><Loader2 size={18} className="animate-spin" /> {t('dg_modal_sending', 'Sending…')}</> : <>{t('dg_modal_send', 'Send request')} <ArrowRight size={18} /></>}
                            </button>
                        </form>
                    </>
                )}
            </DialogContent>
        </Dialog>
    );
}

export default function DemoGateway() {
    const t = useTranslation();
    const { rtl, display, bodyFont, ls } = useDisplay();
    const [open, setOpen] = useState(false);
    const book = () => setOpen(true);
    const pillBtn = { background: C.grad, color: '#fff', border: 0, borderRadius: 999, padding: '14px 28px', fontSize: 16, fontWeight: 800, cursor: 'pointer', boxShadow: '0 16px 36px -12px rgba(229,96,30,.6)' };
    const ghostBtn = { background: 'transparent', color: C.ink, border: `1px solid ${C.line}`, borderRadius: 999, padding: '14px 26px', fontSize: 16, fontWeight: 800, cursor: 'pointer' };

    const FEATURES = [
        { Icon: LayoutDashboard, t: t('dg_feat_dashboard_t', 'Live dashboard'),    s: t('dg_feat_dashboard_s', 'Revenue, returns and alerts at a glance.') },
        { Icon: Car,             t: t('dg_feat_fleet_t', 'Fleet management'),      s: t('dg_feat_fleet_s', 'Availability, documents and maintenance.') },
        { Icon: CalendarRange,   t: t('dg_feat_planning_t', 'Visual planning'),    s: t('dg_feat_planning_s', 'Every vehicle and booking on one screen.') },
        { Icon: FileSignature,   t: t('dg_feat_contracts_t', 'Contracts & e-sign'), s: t('dg_feat_contracts_s', 'Branded PDF agreements, signed in-app.') },
        { Icon: ReceiptText,     t: t('dg_feat_invoicing_t', 'Invoicing & VAT'),   s: t('dg_feat_invoicing_s', 'TVA, coupons, credits and exports.') },
        { Icon: Languages,       t: t('dg_feat_languages_t', '14 languages'),      s: t('dg_feat_languages_s', 'Multi-tenant, multi-currency ready.') },
    ];

    const STATS = [
        ['1', t('dg_stat_1_l', 'Desk to run it all')],
        ['14', t('dg_stat_2_l', 'Languages out of the box')],
        ['100%', t('dg_stat_3_l', 'Your brand & domain')],
    ];

    const gradText = { background: C.grad, WebkitBackgroundClip: 'text', backgroundClip: 'text', color: 'transparent' };

    return (
        <div style={{ fontFamily: bodyFont, background: C.bg, color: C.ink, minHeight: '100vh' }}>
            <Head title="DriveDesk — Car Rental Management, simplified">
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&family=Saira+Condensed:ital,wght@1,800&family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
            </Head>

            {/* Nav */}
            <nav style={{ position: 'sticky', top: 0, zIndex: 30, backdropFilter: 'blur(14px)', background: 'rgba(14,15,17,.72)', borderBottom: `1px solid ${C.line}` }}>
                <div style={{ maxWidth: 1180, margin: '0 auto', padding: '0 28px', height: 72, display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}><Gauge size={36} /><Wordmark /></div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 20 }}>
                        <Link href={route('login')} style={{ color: C.muted, textDecoration: 'none', fontSize: 15, fontWeight: 700 }}>{t('dg_nav_login', 'Log in')}</Link>
                        <button onClick={book} style={{ ...pillBtn, padding: '11px 22px', fontSize: 15 }}>{t('dg_book', 'Book a demo')}</button>
                    </div>
                </div>
            </nav>

            {/* Hero */}
            <header style={{ position: 'relative', textAlign: 'center', padding: '96px 28px 80px', overflow: 'hidden' }}>
                <div style={{ position: 'absolute', inset: 0, zIndex: 0, background: 'radial-gradient(circle at 24% 8%, rgba(229,96,30,.22), transparent 40%), radial-gradient(circle at 82% 32%, rgba(247,162,30,.12), transparent 42%)' }} />
                <div style={{ position: 'relative', zIndex: 1, maxWidth: 980, margin: '0 auto' }}>
                    <div style={{ fontWeight: 800, fontSize: 13, letterSpacing: ls('0.22em'), textTransform: 'uppercase', color: C.orange }}>{t('dg_hero_eyebrow', 'The operating system for car rental')}</div>
                    <h1 style={{ ...display, fontSize: 80, lineHeight: rtl ? 1.08 : 0.98, margin: '18px 0 0' }}>
                        {t('dg_hero_line1', 'Run your entire rental agency')}<br />{t('dg_hero_line2_pre', 'from ')}<span style={gradText}>{t('dg_hero_highlight', 'one desk.')}</span>
                    </h1>
                    <p style={{ fontSize: 21, color: C.muted, maxWidth: 660, margin: '24px auto 0', lineHeight: 1.55, fontWeight: 500 }}>
                        {t('dg_hero_sub', 'Fleet, bookings, contracts, e-signature, invoicing and planning — unified in one fast, multilingual platform built for rental agencies.')}
                    </p>
                    <div style={{ display: 'flex', gap: 14, justifyContent: 'center', marginTop: 34, flexWrap: 'wrap' }}>
                        <button onClick={book} style={pillBtn}>{t('dg_book', 'Book a demo')} →</button>
                        <a href="#features" style={{ ...ghostBtn, textDecoration: 'none', display: 'inline-flex', alignItems: 'center' }}>{t('dg_hero_explore', 'Explore features')}</a>
                    </div>
                    <div style={{ marginTop: 18, fontSize: 14, color: '#5A616C', fontWeight: 600 }}>{t('dg_hero_note', 'No setup fees · 14 languages · Your brand, your domain')}</div>
                </div>
            </header>

            {/* Features */}
            <section id="features" style={{ padding: '92px 28px', borderTop: `1px solid ${C.line}` }}>
                <div style={{ maxWidth: 1180, margin: '0 auto' }}>
                    <div style={{ textAlign: 'center', marginBottom: 54 }}>
                        <div style={{ fontWeight: 800, fontSize: 13, letterSpacing: ls('0.2em'), textTransform: 'uppercase', color: C.orange }}>{t('dg_features_eyebrow', 'Everything your agency runs on')}</div>
                        <h2 style={{ ...display, fontSize: 52, marginTop: 12 }}>{t('dg_features_title_1', 'One platform.')}<br />{t('dg_features_title_2', 'Zero spreadsheets.')}</h2>
                    </div>
                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 20 }}>
                        {FEATURES.map(({ Icon, t: ft, s }) => (
                            <div key={ft} style={{ background: C.panel, border: `1px solid ${C.line}`, borderRadius: 18, padding: 28 }}>
                                <div style={{ width: 56, height: 56, borderRadius: 14, background: C.grad, display: 'flex', alignItems: 'center', justifyContent: 'center', marginBottom: 16 }}>
                                    <Icon size={28} color="#fff" strokeWidth={2.2} />
                                </div>
                                <h3 style={{ fontSize: 21, fontWeight: 800 }}>{ft}</h3>
                                <p style={{ color: C.muted, fontSize: 15, marginTop: 7, lineHeight: 1.5, fontWeight: 500 }}>{s}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Stats band */}
            <section style={{ background: C.grad, color: '#fff', textAlign: 'center', padding: '64px 28px' }}>
                <div style={{ maxWidth: 980, margin: '0 auto', display: 'flex', justifyContent: 'center', gap: 90, flexWrap: 'wrap' }}>
                    {STATS.map(([n, l]) => (
                        <div key={l}>
                            <div style={{ ...display, fontSize: 74, lineHeight: 1 }} dir="ltr">{n}</div>
                            <div style={{ fontWeight: 700, fontSize: 16, opacity: 0.92, marginTop: 6 }}>{l}</div>
                        </div>
                    ))}
                </div>
            </section>

            {/* Final CTA */}
            <section style={{ textAlign: 'center', padding: '104px 28px' }}>
                <div style={{ fontWeight: 800, fontSize: 13, letterSpacing: ls('0.2em'), textTransform: 'uppercase', color: C.orange }}>{t('dg_cta_eyebrow', 'Ready when you are')}</div>
                <h2 style={{ ...display, fontSize: 62, marginTop: 12 }}>{t('dg_cta_line1', 'Put your agency')}<br />{t('dg_cta_line2_pre', 'on ')}<span style={gradText}>{t('dg_cta_highlight', 'autopilot.')}</span></h2>
                <p style={{ color: C.muted, fontSize: 19, marginTop: 16, fontWeight: 500 }}>{t('dg_cta_sub', 'See DriveDesk on your own fleet. A 20-minute demo, no commitment.')}</p>
                <div style={{ marginTop: 34 }}><button onClick={book} style={{ ...pillBtn, fontSize: 18, padding: '18px 38px' }}>{t('dg_book', 'Book a demo')} →</button></div>
            </section>

            {/* Footer */}
            <footer style={{ borderTop: `1px solid ${C.line}`, padding: '36px 28px', color: C.muted }}>
                <div style={{ maxWidth: 1180, margin: '0 auto', display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 20, flexWrap: 'wrap' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}><Gauge size={28} /><Wordmark size={20} /></div>
                    <div style={{ fontSize: 14 }} dir="ltr">© 2026 DriveDesk · {t('dg_footer_tagline', 'Car-rental management, simplified.')}</div>
                </div>
            </footer>

            <DemoModal open={open} onOpenChange={setOpen} />
        </div>
    );
}
