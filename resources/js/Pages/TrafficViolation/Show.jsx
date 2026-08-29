import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { SearchableSelect } from '@/components/ui/searchable-select';
import { Check, Link2Off, Pencil, RefreshCw, TriangleAlert, UserRound } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { ConfidenceBadge, StatusBadge } from '@/components/ViolationBadges';
import { useTranslation } from '@/hooks/useTranslation';

// Why the matcher proposed a given booking, in plain words. The owner is being
// asked to confirm a guess, so the reasoning has to be visible.
const REASONS = {
    within_window: 'The violation falls inside this rental period.',
    before_start: 'This rental started shortly after the violation.',
    after_end: 'This rental ended shortly before the violation.',
};

function formatGap(seconds, t) {
    if (!seconds) return null;
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.round((seconds % 3600) / 60);
    if (hours === 0) return `${minutes} ${t('min')}`;
    return `${hours} ${t('h')} ${minutes ? `${minutes} ${t('min')}` : ''}`.trim();
}

function Field({ label, children }) {
    return (
        <div className="space-y-1">
            <div className="text-xs uppercase tracking-wide text-muted-foreground">{label}</div>
            <div className="text-sm">{children ?? '—'}</div>
        </div>
    );
}

function TrafficViolationShow({
    violation,
    candidates = [],
    statuses = {},
    liableParties = {},
    assignableBookings = [],
    matchIsStale = false,
}) {
    const t = useTranslation();
    const { auth } = usePage().props;
    const can = (p) => auth.permissions.includes(p);
    const canEdit = can('edit traffic violation');

    const occurredAt = String(violation.occurred_at ?? '').replace('T', ' ').slice(0, 16);
    const matched = candidates.find((c) => c.is_current) ?? null;

    const [assignTo, setAssignTo] = useState(
        violation.booking_id ? String(violation.booking_id) : '',
    );
    const [status, setStatus] = useState(violation.status ?? 'new');
    const [liableParty, setLiableParty] = useState(violation.liable_party ?? 'unknown');
    const [recovered, setRecovered] = useState(
        violation.amount_recovered != null ? String(violation.amount_recovered) : '0',
    );

    const post = (name, data = {}) => router.post(route(name, violation.id), data, {
        preserveScroll: true,
    });

    return (
        <div className="space-y-6 p-6">
            <div className="flex flex-wrap items-center justify-between gap-2">
                <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
                    <TriangleAlert className="h-6 w-6" />
                    {violation.reference || t('Traffic Violation')}
                </h1>
                <div className="flex items-center gap-2">
                    <StatusBadge status={violation.status} statuses={statuses} />
                    {can('edit traffic violation') && (
                        <Button size="sm" variant="outline" asChild>
                            <Link href={route('traffic-violation.edit', violation.id)}>
                                <Pencil className="me-2 h-4 w-4" /> {t('Edit')}
                            </Link>
                        </Button>
                    )}
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>{t('The notice')}</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <Field label={t('Reference')}>{violation.reference}</Field>
                        <Field label={t('Authority')}>{violation.authority}</Field>
                        <Field label={t('Date')}>{occurredAt}</Field>
                        <Field label={t('License Plate')}>{violation.license_plate}</Field>
                        <Field label={t('Vehicle')}>{violation.vehicle?.name}</Field>
                        <Field label={t('Location')}>{violation.location}</Field>
                        <Field label={t('Amount')}>{violation.amount}</Field>
                        <Field label={t('Liable Party')}>
                            {t(liableParties[violation.liable_party] ?? violation.liable_party)}
                        </Field>
                        <Field label={t('Amount Recovered')}>{violation.amount_recovered}</Field>
                        <Field label={t('Notice Date')}>
                            {violation.notice_date ? String(violation.notice_date).slice(0, 10) : null}
                        </Field>
                        <Field label={t('Document')}>
                            {violation.document ? (
                                <a
                                    href={`/storage/upload/violation/${violation.document}`}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="text-primary underline"
                                >
                                    {t('View')}
                                </a>
                            ) : null}
                        </Field>
                        <div className="sm:col-span-2 lg:col-span-3">
                            <Field label={t('Description')}>{violation.description}</Field>
                        </div>
                        {violation.notes && (
                            <div className="sm:col-span-2 lg:col-span-3">
                                <Field label={t('Notes')}>{violation.notes}</Field>
                            </div>
                        )}
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        <UserRound className="h-5 w-5" /> {t('Who was renting')}
                    </CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                    <div className="flex flex-wrap items-center gap-2">
                        <ConfidenceBadge
                            confidence={violation.match_confidence}
                            matchSource={violation.match_source}
                            confirmedAt={violation.confirmed_at}
                        />
                        {violation.match_confidence === 'probable' && (
                            <span className="text-sm text-muted-foreground">
                                {t('More than one rental could fit — confirm before acting on this.')}
                            </span>
                        )}
                        {/* Tonal badge, not amber text: coloured text on a tint
                            fails WCAG AA — see components/ui/badge.jsx. */}
                        {matchIsStale && (
                            <Badge variant="warning">
                                {t('The rentals have changed since this was matched — re-run the match.')}
                            </Badge>
                        )}

                        {canEdit && (
                            <div className="ms-auto flex flex-wrap gap-2">
                                <Button
                                    size="sm"
                                    variant="outline"
                                    onClick={() => post('traffic-violation.rematch')}
                                >
                                    <RefreshCw className="me-2 h-4 w-4" /> {t('Re-run match')}
                                </Button>
                                {violation.booking_id && !violation.confirmed_at && (
                                    <Button
                                        size="sm"
                                        onClick={() => post('traffic-violation.assign', { booking_id: violation.booking_id })}
                                    >
                                        <Check className="me-2 h-4 w-4" /> {t('Confirm this renter')}
                                    </Button>
                                )}
                                {violation.booking_id && (
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        onClick={() => post('traffic-violation.assign', { booking_id: null })}
                                    >
                                        <Link2Off className="me-2 h-4 w-4" /> {t('Unlink')}
                                    </Button>
                                )}
                            </div>
                        )}
                    </div>

                    {violation.driver ? (
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <Field label={t('Renter')}>{violation.driver.name}</Field>
                            <Field label={t('Email')}>{violation.driver.email}</Field>
                            <Field label={t('Phone Number')}>{violation.driver.phone_number}</Field>
                            <Field label={t('Booking')}>
                                {violation.booking_id ? (
                                    <Link
                                        href={route('booking.show', violation.booking_id)}
                                        className="text-primary underline"
                                    >
                                        #{violation.booking?.booking_id ?? violation.booking_id}
                                    </Link>
                                ) : null}
                            </Field>
                            {matched?.second_driver && (
                                <Field label={t('Second Driver')}>
                                    {matched.second_driver}
                                    <div className="text-xs text-muted-foreground">
                                        {t('Also authorised to drive under the rental agreement.')}
                                    </div>
                                </Field>
                            )}
                        </div>
                    ) : (
                        <p className="text-sm text-muted-foreground">
                            {t('No rental covers this moment. The vehicle may have been at the agency, or the booking dates may be incomplete.')}
                        </p>
                    )}
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>{t('Candidate rentals')}</CardTitle>
                </CardHeader>
                <CardContent className="space-y-3">
                    {candidates.length === 0 && (
                        <p className="text-sm text-muted-foreground">
                            {t('No rental of this vehicle is close enough in time to be a candidate.')}
                        </p>
                    )}
                    {candidates.map((candidate) => {
                        const gap = formatGap(candidate.distance_seconds, t);
                        return (
                            <div
                                key={candidate.booking_id}
                                className={`rounded-lg border p-4 ${candidate.is_current ? 'border-primary bg-primary/5' : ''}`}
                            >
                                <div className="flex flex-wrap items-center justify-between gap-2">
                                    <div className="font-medium">
                                        {candidate.driver_name ?? t('Unknown renter')}
                                        <span className="ms-2 text-sm text-muted-foreground">
                                            #{candidate.booking_number ?? candidate.booking_id}
                                        </span>
                                    </div>
                                    {candidate.is_current && <Badge variant="info">{t('Selected')}</Badge>}
                                </div>
                                <div className="mt-1 text-sm text-muted-foreground">
                                    {candidate.start} → {candidate.end}
                                </div>
                                <div className="mt-1 text-sm">
                                    {t(REASONS[candidate.reason] ?? '')}
                                    {gap && <span className="text-muted-foreground"> ({gap})</span>}
                                </div>
                                {candidate.driver_email && (
                                    <div className="mt-1 text-xs text-muted-foreground">
                                        {candidate.driver_email}
                                        {candidate.driver_phone ? ` · ${candidate.driver_phone}` : ''}
                                    </div>
                                )}
                            </div>
                        );
                    })}
                </CardContent>
            </Card>

            {canEdit && (
                <Card>
                    <CardHeader>
                        <CardTitle>{t('Assign a rental')}</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-3">
                        {assignableBookings.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                {t('The plate does not match any vehicle in the fleet, so there is no rental to choose from. Correct the plate first.')}
                            </p>
                        ) : (
                            <>
                                <p className="text-sm text-muted-foreground">
                                    {t('Picking a rental here overrides the automatic match and keeps it through later edits.')}
                                </p>
                                <div className="flex flex-wrap items-end gap-2">
                                    <div className="min-w-[280px] flex-1 space-y-1.5">
                                        <Label>{t('Booking')}</Label>
                                        <SearchableSelect
                                            options={assignableBookings.map((b) => ({
                                                value: String(b.id),
                                                label: b.label,
                                            }))}
                                            value={assignTo}
                                            onChange={setAssignTo}
                                            placeholder={t('Select a rental')}
                                            searchPlaceholder={t('Search rentals…')}
                                            ariaLabel={t('Booking')}
                                        />
                                    </div>
                                    <Button
                                        disabled={!assignTo}
                                        onClick={() => post('traffic-violation.assign', { booking_id: assignTo })}
                                    >
                                        {t('Assign')}
                                    </Button>
                                </div>
                            </>
                        )}
                    </CardContent>
                </Card>
            )}

            {canEdit && (
                <Card>
                    <CardHeader>
                        <CardTitle>{t('Recovery')}</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-3">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div className="space-y-1.5">
                                <Label>{t('Status')}</Label>
                                <Select value={status} onValueChange={setStatus}>
                                    <SelectTrigger aria-label={t('Status')}>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {Object.entries(statuses).map(([key, label]) => (
                                            <SelectItem key={key} value={key}>{t(label)}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-1.5">
                                <Label>{t('Liable Party')}</Label>
                                <Select value={liableParty} onValueChange={setLiableParty}>
                                    <SelectTrigger aria-label={t('Liable Party')}>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {Object.entries(liableParties).map(([key, label]) => (
                                            <SelectItem key={key} value={key}>{t(label)}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="amount_recovered">{t('Amount Recovered')}</Label>
                                <Input
                                    id="amount_recovered"
                                    type="number"
                                    step="0.01"
                                    value={recovered}
                                    onChange={(e) => setRecovered(e.target.value)}
                                />
                            </div>
                        </div>

                        <div className="flex justify-end">
                            <Button
                                onClick={() => post('traffic-violation.status', {
                                    status,
                                    liable_party: liableParty,
                                    amount_recovered: recovered,
                                })}
                            >
                                {t('Save')}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            )}

            <div className="flex justify-end">
                <Button variant="ghost" asChild>
                    <Link href={route('traffic-violation.index')}>{t('Close')}</Link>
                </Button>
            </div>
        </div>
    );
}

TrafficViolationShow.layout = (page) => {
    return (
        <AdminLayout breadcrumbs={[
            { label: 'Traffic Violations', href: route('traffic-violation.index') },
            { label: 'Details' },
        ]}>{page}</AdminLayout>
    );
};
export default TrafficViolationShow;
