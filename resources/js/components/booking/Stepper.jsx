/**
 * Progress indicator for the /reserve wizard. `current` is 1-indexed.
 *
 * A row of rules rather than circles joined by lines (BAN-333): at four steps
 * the circles-and-connectors version had to shrink its labels to 11px and clip
 * them to 6rem to fit a phone, so "Sélectionner une Voiture" arrived as
 * "Sélectionn…". Rules stack into a 2×2 grid instead and give each label its
 * own full-width line.
 *
 * Colors come from theme tokens, never a hardcoded brand color.
 */
export default function Stepper({ current, labels }) {
    return (
        <ol
            className="mb-8 grid grid-cols-2 gap-x-3 gap-y-4 sm:grid-cols-4"
            aria-label={`${current} / ${labels.length}`}
        >
            {labels.map((label, i) => {
                const n = i + 1;
                const active = n <= current;

                return (
                    <li key={label} className="flex flex-col gap-2">
                        <span className={`h-1 rounded-full ${active ? 'bg-primary' : 'bg-muted'}`} />
                        <span className="flex items-baseline gap-2">
                            <span className={`font-display text-sm ${active ? 'text-primary' : 'text-muted-foreground'}`}>
                                {String(n).padStart(2, '0')}
                            </span>
                            <span className={`text-[13px] ${active ? 'font-bold text-foreground' : 'font-semibold text-muted-foreground'}`}>
                                {label}
                            </span>
                        </span>
                    </li>
                );
            })}
        </ol>
    );
}
