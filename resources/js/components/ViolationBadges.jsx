import { Badge } from '@/components/ui/badge';
import { useTranslation } from '@/hooks/useTranslation';

// Single source of truth for how a violation's match confidence and recovery
// status are coloured, so the list and the detail page can't drift apart.
//
// Confidence is deliberately not a pass/fail: `probable` is amber rather than
// red because it is a real answer awaiting confirmation, not an error.
const CONFIDENCE_VARIANTS = {
    exact: 'success',
    probable: 'warning',
    none: 'secondary',
};

const CONFIDENCE_LABELS = {
    exact: 'Exact match',
    probable: 'Probable match',
    none: 'No match',
};

const STATUS_VARIANTS = {
    new: 'info',
    notified: 'warning',
    paid: 'success',
    contested: 'destructive',
    written_off: 'secondary',
};

export function ConfidenceBadge({ confidence, matchSource, confirmedAt }) {
    const t = useTranslation();

    // A human pinned this one; that outranks whatever the matcher thought.
    if (matchSource === 'manual') {
        return <Badge variant="success">{t('Assigned manually')}</Badge>;
    }

    const label = CONFIDENCE_LABELS[confidence] ?? CONFIDENCE_LABELS.none;

    return (
        <Badge variant={CONFIDENCE_VARIANTS[confidence] ?? 'secondary'}>
            {t(label)}
            {confidence === 'exact' && confirmedAt ? ` · ${t('Confirmed')}` : ''}
        </Badge>
    );
}

export function StatusBadge({ status, statuses = {} }) {
    const t = useTranslation();

    return (
        <Badge variant={STATUS_VARIANTS[status] ?? 'secondary'}>
            {t(statuses[status] ?? status)}
        </Badge>
    );
}
