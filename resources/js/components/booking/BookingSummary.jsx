import { Banknote } from 'lucide-react';
import { specLabels } from '@/lib/vehicleSpecs';

/**
 * The wizard's persistent summary (BAN-333).
 *
 * Steps 2, 3 and 4 each used to re-print their own little "car + dates +
 * total" header, so what you were booking scrolled away the moment you
 * started typing, and the price only reappeared between steps. This shows the
 * same thing continuously beside the form and replaces all three headers.
 *
 * It renders whatever is known so far — a row with nothing behind it yet is
 * left out rather than shown empty, so it never looks like data went missing.
 */
export default function BookingSummary({
    vehicle, places = [], pickupId, dropOffId,
    startDate, startTime, endDate, endTime,
    days = 0, total = 0, t,
}) {
    if (!vehicle) return null;

    const { gearbox, fuel } = specLabels(vehicle, t);
    const rate = Number(vehicle.daily_rate ?? 0);
    const placeName = (id) => places.find((p) => String(p.id) === String(id))?.name ?? null;

    const subtitle = [vehicle.model, vehicle.gearbox ? gearbox : null, vehicle.fuel_type ? fuel : null]
        .filter(Boolean)
        .join(' · ');

    const rows = [
        { key: 'pickup', label: t('pickup_location', 'Lieu de Prise en Charge'), value: placeName(pickupId) },
        { key: 'dropoff', label: t('return_location', 'Lieu de Retour'), value: placeName(dropOffId) },
        { key: 'from', label: t('summary_from', 'Du'), value: startDate ? [startDate, startTime].filter(Boolean).join(' · ') : null },
        { key: 'to', label: t('summary_to', 'Au'), value: endDate ? [endDate, endTime].filter(Boolean).join(' · ') : null },
    ].filter((r) => r.value);

    return (
        <aside
            aria-label={t('summary_title', 'Récapitulatif')}
            className="overflow-hidden rounded-lg border border-border bg-card lg:sticky lg:top-24"
        >
            <img
                src={vehicle.picture ? `/storage/upload/picture/${vehicle.picture}` : '/assets/images/client/default-car.jpg'}
                alt={vehicle.name}
                className="h-36 w-full object-cover"
                onError={(e) => { e.target.src = '/assets/images/client/default-car.jpg'; }}
            />

            <div className="p-4">
                <h2 className="font-display text-xl uppercase">{vehicle.name}</h2>
                {subtitle && <p className="mt-0.5 text-[13px] font-semibold text-muted-foreground">{subtitle}</p>}

                {rows.length > 0 && (
                    <dl className="mt-4 space-y-2.5 text-sm">
                        {rows.map(({ key, label, value }) => (
                            <div key={key} className="flex justify-between gap-3">
                                <dt className="text-muted-foreground">{label}</dt>
                                <dd className="text-end font-semibold">{value}</dd>
                            </div>
                        ))}
                    </dl>
                )}

                <div className="mt-4 border-t border-border pt-3">
                    {days > 0 ? (
                        <>
                            <div className="flex items-baseline justify-between">
                                <span className="text-sm font-bold">{t('detail_total_estimated', 'Total estimé')}</span>
                                <span className="font-display text-2xl text-primary">{total.toFixed(0)} Dh</span>
                            </div>
                            <p className="mt-1 text-xs leading-relaxed text-muted-foreground">
                                {days} {days > 1 ? t('detail_days', 'jours') : t('detail_day', 'jour')} × {rate.toFixed(0)} Dh.{' '}
                                {t('summary_amount_note', "Montant confirmé par l'agence avant le retrait.")}
                            </p>
                        </>
                    ) : (
                        <p className="text-sm text-muted-foreground">
                            {t('detail_pick_dates', 'Choisissez vos dates pour voir le total.')}
                        </p>
                    )}
                </div>
            </div>

            {/* The single most reassuring thing this flow can say, and the one a
                visitor is most likely to be looking for at the moment they are
                asked for a phone number. */}
            <p className="flex items-start gap-2 border-t border-border bg-accent px-4 py-3 text-xs font-semibold leading-relaxed text-accent-foreground">
                <Banknote className="mt-0.5 h-4 w-4 shrink-0" strokeWidth={1.9} />
                {t('summary_cash_note', "Aucun paiement en ligne. Vous réglez à l'agence.")}
            </p>
        </aside>
    );
}
