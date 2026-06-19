import { AlertTriangle } from 'lucide-react';
import { Alert, AlertTitle, AlertDescription } from '@/components/ui/alert';
import { useTranslation } from '@/hooks/useTranslation';

// Immediate, inline blacklist warning for the booking/contract driver pickers
// (BAN-252). Renders the moment a blacklisted driver is selected — no need to
// fill the rest of the form or click Create. The server still enforces and
// records the override on submit; this is the instant heads-up.
//
// Props:
//   drivers     — the picker's driver list, each { id, name, blacklisted, blacklist_reason }
//   selectedIds — currently selected driver id(s) (strings/numbers; falsy/'none' ignored)
export function BlacklistNotice({ drivers = [], selectedIds = [] }) {
    const t = useTranslation();
    const ids = selectedIds.filter(Boolean).map(String);
    const flagged = drivers.filter((d) => d.blacklisted && ids.includes(String(d.id)));

    if (flagged.length === 0) return null;

    return (
        <Alert variant="destructive" className="mt-2">
            <AlertTriangle className="h-4 w-4" />
            <AlertTitle>{t('Driver is blacklisted')}</AlertTitle>
            <AlertDescription>
                {flagged.map((d) => (
                    <div key={d.id}>
                        <span className="font-medium">{d.name}</span>
                        {d.blacklist_reason ? `: ${d.blacklist_reason}` : ''}
                    </div>
                ))}
            </AlertDescription>
        </Alert>
    );
}
