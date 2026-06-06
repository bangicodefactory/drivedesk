import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { CalendarRange } from 'lucide-react';
import { useTranslation } from '@/hooks/useTranslation';

// Gantt-style availability for the next 7 days, built from booking
// start/end dates (HomeController::ownerDashboardExtras). The grid is forced
// LTR — timelines read left-to-right regardless of UI direction.
const BAR_COLOR = {
    in_progress:  'bg-primary',
    approved:     'bg-blue-500',
    pending:      'bg-amber-500',
    yet_to_start: 'bg-violet-500',
    completed:    'bg-muted-foreground/40',
};

const DAY_MS = 86400000;

export default function FleetAvailability({ data }) {
    const t = useTranslation();
    const days = data?.days ?? [];
    const vehicles = data?.vehicles ?? [];
    const total = data?.total ?? vehicles.length;
    const n = days.length || 7;
    const startMs = days.length ? new Date(days[0]).getTime() : 0;

    const col = (dateStr) => Math.round((new Date(dateStr).getTime() - startMs) / DAY_MS);
    const clamp = (x) => Math.max(0, Math.min(n - 1, x));
    // Dates arrive as 'YYYY-MM-DD' (parsed as UTC midnight); format in UTC so
    // the day label can't shift in non-UTC timezones.
    const fmtDay = (d) =>
        new Date(d).toLocaleDateString(undefined, { weekday: 'short', day: 'numeric', timeZone: 'UTC' });

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2 text-base">
                    <CalendarRange className="h-4 w-4 text-muted-foreground" />
                    {t('Fleet Availability')}
                </CardTitle>
            </CardHeader>
            <CardContent className="overflow-x-auto">
                <div className="min-w-[640px]" dir="ltr">
                    {/* Header row */}
                    <div className="flex border-b pb-2 text-xs text-muted-foreground">
                        <div className="w-40 shrink-0">{t('Vehicle')}</div>
                        <div
                            className="grid flex-1"
                            style={{ gridTemplateColumns: `repeat(${n}, minmax(0,1fr))` }}
                        >
                            {days.map((d) => (
                                <div key={d} className="px-1 text-center">{fmtDay(d)}</div>
                            ))}
                        </div>
                    </div>

                    {vehicles.length === 0 && (
                        <p className="py-6 text-center text-sm text-muted-foreground">
                            {t('No vehicles yet')}
                        </p>
                    )}

                    {vehicles.map((v) => (
                        <div key={v.id} className="flex items-center border-b py-2 last:border-0">
                            <div className="w-40 shrink-0 pr-2">
                                <p className="truncate text-sm font-medium">{v.name}</p>
                                <p className="truncate text-xs text-muted-foreground">{v.license_plate}</p>
                            </div>
                            <div className="relative h-8 flex-1">
                                {/* day gridlines */}
                                <div
                                    className="absolute inset-0 grid"
                                    style={{ gridTemplateColumns: `repeat(${n}, minmax(0,1fr))` }}
                                >
                                    {days.map((d) => (
                                        <div key={d} className="border-l border-border/50 first:border-l-0" />
                                    ))}
                                </div>
                                {/* booking bars */}
                                {v.bookings.map((b, i) => {
                                    const s = clamp(col(b.start));
                                    const e = clamp(col(b.end));
                                    const left = (s / n) * 100;
                                    const width = ((e - s + 1) / n) * 100;
                                    return (
                                        <div
                                            key={i}
                                            className={`absolute top-1 flex h-6 items-center overflow-hidden rounded px-2 ${BAR_COLOR[b.status] ?? 'bg-primary'}`}
                                            style={{ left: `${left}%`, width: `${width}%` }}
                                            title={`${b.driver ?? ''} (${b.start} → ${b.end})`}
                                        >
                                            <span className="truncate text-[10px] font-medium text-white">
                                                {b.driver ?? b.booking_id}
                                            </span>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    ))}
                </div>

                {total > vehicles.length && (
                    <p className="pt-3 text-center text-xs text-muted-foreground">
                        {vehicles.length} / {total} {t('Vehicles')}
                    </p>
                )}
            </CardContent>
        </Card>
    );
}
