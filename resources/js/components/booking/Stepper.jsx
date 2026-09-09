import { Fragment } from 'react';
import { Check } from 'lucide-react';

/**
 * Progress indicator for the /reserve wizard. `current` is 1-indexed.
 * Colors come from the theme's --primary token, not a hardcoded brand color.
 */
export default function Stepper({ current, labels }) {
    return (
        <div className="flex justify-center mb-12 md:mb-16">
            <div className="flex items-center w-full max-w-3xl">
                {labels.map((label, i) => {
                    const n = i + 1;
                    const done = n < current;
                    const active = n <= current;
                    return (
                        <Fragment key={n}>
                            <div className="flex flex-col items-center shrink-0">
                                <div
                                    className={`w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold border-2 transition-colors ${
                                        active ? 'bg-primary text-primary-foreground border-primary' : 'bg-background text-muted-foreground border-border'
                                    }`}
                                >
                                    {done ? <Check className="h-4 w-4" /> : n}
                                </div>
                                <span className={`mt-2.5 text-[11px] sm:text-xs uppercase tracking-wide text-center max-w-[6rem] sm:max-w-none ${
                                    active ? 'text-foreground font-medium' : 'text-muted-foreground'
                                }`}>
                                    {label}
                                </span>
                            </div>
                            {n < labels.length && (
                                <div className={`flex-1 h-px mx-2 sm:mx-3 mb-5 transition-colors ${done ? 'bg-primary' : 'bg-border'}`} />
                            )}
                        </Fragment>
                    );
                })}
            </div>
        </div>
    );
}
