import { Check } from 'lucide-react';

/**
 * Progress indicator for the /reserve wizard. `current` is 1-indexed.
 *
 * A row of rules rather than circles joined by connectors (BAN-333): at four
 * steps the circles-and-connectors version had to shrink its labels to 11px
 * and clip them to 6rem to fit a phone, so "Sélectionner une Voiture" arrived
 * as "Sélectionn…". Rules stack into a 2×2 grid instead and give each label
 * its own full-width line.
 *
 * Three states, not two. An earlier version of this rewrite kept only
 * `n <= current`, which styled done and current identically — so on the last
 * step all four rules were filled and all four labels bold, and the stepper no
 * longer showed where you were, which is the one thing it exists for. A done
 * step is marked with a check and dimmed; the current one carries the accent.
 *
 * Colors come from theme tokens, never a hardcoded brand color.
 */
export default function Stepper({ current, labels }) {
    return (
        <ol className="mb-8 grid grid-cols-2 gap-x-3 gap-y-4 sm:grid-cols-4">
            {labels.map((label, i) => {
                const n = i + 1;
                const done = n < current;
                const isCurrent = n === current;

                return (
                    <li
                        key={label}
                        className="flex flex-col gap-2"
                        aria-current={isCurrent ? 'step' : undefined}
                    >
                        <span
                            className={`h-1 rounded-full ${
                                isCurrent ? 'bg-primary' : done ? 'bg-primary/40' : 'bg-muted'
                            }`}
                        />
                        <span className="flex items-baseline gap-2">
                            <span
                                className={`font-display flex items-center text-sm ${
                                    isCurrent ? 'text-primary' : done ? 'text-primary/70' : 'text-muted-foreground'
                                }`}
                            >
                                {done
                                    ? <Check className="h-3.5 w-3.5" strokeWidth={3} aria-hidden="true" />
                                    : String(n).padStart(2, '0')}
                            </span>
                            <span
                                className={`text-[13px] ${
                                    isCurrent
                                        ? 'font-bold text-foreground'
                                        : done
                                            ? 'font-semibold text-muted-foreground'
                                            : 'font-semibold text-muted-foreground/70'
                                }`}
                            >
                                {label}
                            </span>
                        </span>
                    </li>
                );
            })}
        </ol>
    );
}
