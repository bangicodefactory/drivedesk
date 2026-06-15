import { useState } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { z } from 'zod';
import { useZodForm } from '@/hooks/useZodForm';
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
const display = { fontFamily: "'Saira Condensed', system-ui, sans-serif", fontStyle: 'italic', fontWeight: 800, textTransform: 'uppercase', letterSpacing: '0.005em' };

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

const Wordmark = ({ size = 26 }) => (
    <span style={{ ...display, fontSize: size, color: C.ink, lineHeight: 1 }}>
        Drive<span style={{ color: C.orange }}>Desk</span>
    </span>
);

const FEATURES = [
    { Icon: LayoutDashboard, t: 'Live dashboard', s: 'Revenue, returns and alerts at a glance.' },
    { Icon: Car, t: 'Fleet management', s: 'Availability, documents and maintenance.' },
    { Icon: CalendarRange, t: 'Visual planning', s: 'Every vehicle and booking on one screen.' },
    { Icon: FileSignature, t: 'Contracts & e-sign', s: 'Branded PDF agreements, signed in-app.' },
    { Icon: ReceiptText, t: 'Invoicing & VAT', s: 'TVA, coupons, credits and exports.' },
    { Icon: Languages, t: '14 languages', s: 'Multi-tenant, multi-currency ready.' },
];

const schema = z.object({
    name: z.string().min(1, 'Your name is required.'),
    company: z.string().min(1, 'Your agency name is required.'),
    email: z.string().min(1, 'Email is required.').email('Enter a valid email.'),
    phone: z.string().optional(),
    message: z.string().optional(),
});

function DemoModal({ open, onOpenChange }) {
    const { flash } = usePage().props;
    const [sent, setSent] = useState(false);
    const { form, submit } = useZodForm(schema, {
        defaultValues: { name: '', company: '', email: '', phone: '', message: '' },
    });
    const { register, formState: { errors, isSubmitting } } = form;

    const field = { width: '100%', background: '#0E0F11', border: `1px solid ${C.line}`, borderRadius: 10, padding: '11px 13px', color: C.ink, fontSize: 15, outline: 'none' };
    const label = { fontSize: 13, fontWeight: 700, color: C.muted, marginBottom: 6, display: 'block' };
    const err = { color: '#FCA5A5', fontSize: 13, marginTop: 4 };

    return (
        <Dialog open={open} onOpenChange={(v) => { onOpenChange(v); if (!v) setSent(false); }}>
            <DialogContent style={{ background: C.panel, border: `1px solid ${C.line}`, color: C.ink, maxWidth: 520 }}>
                {sent ? (
                    <div style={{ textAlign: 'center', padding: '20px 8px' }}>
                        <div style={{ width: 64, height: 64, borderRadius: 999, background: C.grad, display: 'inline-flex', alignItems: 'center', justifyContent: 'center', marginBottom: 16 }}>
                            <Check size={32} color="#fff" strokeWidth={3} />
                        </div>
                        <h3 style={{ ...display, fontSize: 30 }}>You're on the list.</h3>
                        <p style={{ color: C.muted, marginTop: 8, fontSize: 15 }}>
                            {flash?.success ?? 'Thanks! We\'ll be in touch shortly to schedule your demo.'}
                        </p>
                        <div style={{ marginTop: 20, paddingTop: 18, borderTop: `1px solid ${C.line}` }}>
                            <p style={{ color: C.muted, fontSize: 14, lineHeight: 1.55 }}>
                                Once your demo is confirmed, we'll email you a username and password
                                for your DriveDesk workspace. Already have your credentials?
                            </p>
                            <a href={route('login')} style={{ marginTop: 14, background: C.grad, color: '#fff', textDecoration: 'none', borderRadius: 999, padding: '12px 22px', fontSize: 15, fontWeight: 800, display: 'inline-flex', alignItems: 'center', gap: 8 }}>
                                Go to login <ArrowRight size={17} />
                            </a>
                        </div>
                    </div>
                ) : (
                    <>
                        <DialogHeader>
                            <DialogTitle style={{ ...display, fontSize: 30, color: C.ink }}>Book a demo</DialogTitle>
                            <DialogDescription style={{ color: C.muted }}>
                                See DriveDesk on your own fleet. A 20-minute call, no commitment.
                            </DialogDescription>
                        </DialogHeader>
                        <form onSubmit={submit('post', route('demo.request'), { preserveScroll: true, onSuccess: () => { setSent(true); form.reset(); } })} style={{ display: 'grid', gap: 14, marginTop: 6 }}>
                            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
                                <div><label style={label}>Name</label><input style={field} {...register('name')} />{errors.name && <p style={err}>{errors.name.message}</p>}</div>
                                <div><label style={label}>Agency</label><input style={field} {...register('company')} />{errors.company && <p style={err}>{errors.company.message}</p>}</div>
                            </div>
                            <div><label style={label}>Email</label><input style={field} type="email" {...register('email')} />{errors.email && <p style={err}>{errors.email.message}</p>}</div>
                            <div><label style={label}>Phone <span style={{ fontWeight: 400 }}>(optional)</span></label><input style={field} {...register('phone')} /></div>
                            <div><label style={label}>Anything we should know? <span style={{ fontWeight: 400 }}>(optional)</span></label><textarea rows={3} style={{ ...field, resize: 'vertical' }} {...register('message')} /></div>
                            <button type="submit" disabled={isSubmitting} style={{ marginTop: 4, background: C.grad, color: '#fff', border: 0, borderRadius: 999, padding: '14px 22px', fontSize: 16, fontWeight: 800, cursor: 'pointer', display: 'inline-flex', alignItems: 'center', justifyContent: 'center', gap: 8, opacity: isSubmitting ? 0.7 : 1 }}>
                                {isSubmitting ? <><Loader2 size={18} className="animate-spin" /> Sending…</> : <>Send request <ArrowRight size={18} /></>}
                            </button>
                        </form>
                    </>
                )}
            </DialogContent>
        </Dialog>
    );
}

export default function DemoGateway() {
    const [open, setOpen] = useState(false);
    const book = () => setOpen(true);
    const pillBtn = { background: C.grad, color: '#fff', border: 0, borderRadius: 999, padding: '14px 28px', fontSize: 16, fontWeight: 800, cursor: 'pointer', boxShadow: '0 16px 36px -12px rgba(229,96,30,.6)' };
    const ghostBtn = { background: 'transparent', color: C.ink, border: `1px solid ${C.line}`, borderRadius: 999, padding: '14px 26px', fontSize: 16, fontWeight: 800, cursor: 'pointer' };

    return (
        <div style={{ fontFamily: "'Hanken Grotesk', system-ui, sans-serif", background: C.bg, color: C.ink, minHeight: '100vh' }}>
            <Head title="DriveDesk — Car Rental Management, simplified">
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&family=Saira+Condensed:ital,wght@1,800&display=swap" rel="stylesheet" />
            </Head>

            {/* Nav */}
            <nav style={{ position: 'sticky', top: 0, zIndex: 30, backdropFilter: 'blur(14px)', background: 'rgba(14,15,17,.72)', borderBottom: `1px solid ${C.line}` }}>
                <div style={{ maxWidth: 1180, margin: '0 auto', padding: '0 28px', height: 72, display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}><Gauge size={36} /><Wordmark /></div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 20 }}>
                        <a href={route('login')} style={{ color: C.muted, textDecoration: 'none', fontSize: 15, fontWeight: 700 }}>Log in</a>
                        <button onClick={book} style={{ ...pillBtn, padding: '11px 22px', fontSize: 15 }}>Book a demo</button>
                    </div>
                </div>
            </nav>

            {/* Hero */}
            <header style={{ position: 'relative', textAlign: 'center', padding: '96px 28px 80px', overflow: 'hidden' }}>
                <div style={{ position: 'absolute', inset: 0, zIndex: 0, background: 'radial-gradient(circle at 24% 8%, rgba(229,96,30,.22), transparent 40%), radial-gradient(circle at 82% 32%, rgba(247,162,30,.12), transparent 42%)' }} />
                <div style={{ position: 'relative', zIndex: 1, maxWidth: 980, margin: '0 auto' }}>
                    <div style={{ fontWeight: 800, fontSize: 13, letterSpacing: '0.22em', textTransform: 'uppercase', color: C.orange }}>The operating system for car rental</div>
                    <h1 style={{ ...display, fontSize: 80, lineHeight: 0.98, margin: '18px 0 0' }}>
                        Run your entire rental agency<br />from <span style={{ background: C.grad, WebkitBackgroundClip: 'text', backgroundClip: 'text', color: 'transparent' }}>one desk.</span>
                    </h1>
                    <p style={{ fontSize: 21, color: C.muted, maxWidth: 660, margin: '24px auto 0', lineHeight: 1.55, fontWeight: 500 }}>
                        Fleet, bookings, contracts, e-signature, invoicing and planning — unified in one fast, multilingual platform built for rental agencies.
                    </p>
                    <div style={{ display: 'flex', gap: 14, justifyContent: 'center', marginTop: 34, flexWrap: 'wrap' }}>
                        <button onClick={book} style={pillBtn}>Book a demo →</button>
                        <a href="#features" style={{ ...ghostBtn, textDecoration: 'none', display: 'inline-flex', alignItems: 'center' }}>Explore features</a>
                    </div>
                    <div style={{ marginTop: 18, fontSize: 14, color: '#5A616C', fontWeight: 600 }}>No setup fees · 14 languages · Your brand, your domain</div>
                </div>
            </header>

            {/* Features */}
            <section id="features" style={{ padding: '92px 28px', borderTop: `1px solid ${C.line}` }}>
                <div style={{ maxWidth: 1180, margin: '0 auto' }}>
                    <div style={{ textAlign: 'center', marginBottom: 54 }}>
                        <div style={{ fontWeight: 800, fontSize: 13, letterSpacing: '0.2em', textTransform: 'uppercase', color: C.orange }}>Everything your agency runs on</div>
                        <h2 style={{ ...display, fontSize: 52, marginTop: 12 }}>One platform.<br />Zero spreadsheets.</h2>
                    </div>
                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 20 }}>
                        {FEATURES.map(({ Icon, t, s }) => (
                            <div key={t} style={{ background: C.panel, border: `1px solid ${C.line}`, borderRadius: 18, padding: 28 }}>
                                <div style={{ width: 56, height: 56, borderRadius: 14, background: C.grad, display: 'flex', alignItems: 'center', justifyContent: 'center', marginBottom: 16 }}>
                                    <Icon size={28} color="#fff" strokeWidth={2.2} />
                                </div>
                                <h3 style={{ fontSize: 21, fontWeight: 800 }}>{t}</h3>
                                <p style={{ color: C.muted, fontSize: 15, marginTop: 7, lineHeight: 1.5, fontWeight: 500 }}>{s}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Stats band */}
            <section style={{ background: C.grad, color: '#fff', textAlign: 'center', padding: '64px 28px' }}>
                <div style={{ maxWidth: 980, margin: '0 auto', display: 'flex', justifyContent: 'center', gap: 90, flexWrap: 'wrap' }}>
                    {[['1', 'Desk to run it all'], ['14', 'Languages out of the box'], ['100%', 'Your brand & domain']].map(([n, l]) => (
                        <div key={l}>
                            <div style={{ ...display, fontSize: 74, lineHeight: 1 }}>{n}</div>
                            <div style={{ fontWeight: 700, fontSize: 16, opacity: 0.92, marginTop: 6 }}>{l}</div>
                        </div>
                    ))}
                </div>
            </section>

            {/* Final CTA */}
            <section style={{ textAlign: 'center', padding: '104px 28px' }}>
                <div style={{ fontWeight: 800, fontSize: 13, letterSpacing: '0.2em', textTransform: 'uppercase', color: C.orange }}>Ready when you are</div>
                <h2 style={{ ...display, fontSize: 62, marginTop: 12 }}>Put your agency<br />on <span style={{ background: C.grad, WebkitBackgroundClip: 'text', backgroundClip: 'text', color: 'transparent' }}>autopilot.</span></h2>
                <p style={{ color: C.muted, fontSize: 19, marginTop: 16, fontWeight: 500 }}>See DriveDesk on your own fleet. A 20-minute demo, no commitment.</p>
                <div style={{ marginTop: 34 }}><button onClick={book} style={{ ...pillBtn, fontSize: 18, padding: '18px 38px' }}>Book a demo →</button></div>
            </section>

            {/* Footer */}
            <footer style={{ borderTop: `1px solid ${C.line}`, padding: '36px 28px', color: C.muted }}>
                <div style={{ maxWidth: 1180, margin: '0 auto', display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 20, flexWrap: 'wrap' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}><Gauge size={28} /><Wordmark size={20} /></div>
                    <div style={{ fontSize: 14 }}>© 2026 DriveDesk · Car-rental management, simplified.</div>
                </div>
            </footer>

            <DemoModal open={open} onOpenChange={setOpen} />
        </div>
    );
}
