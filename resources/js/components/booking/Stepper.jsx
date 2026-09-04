import { Fragment } from 'react';
import { Check } from 'lucide-react';

/**
 * Progress indicator for the /reserve wizard. `current` is 1-indexed.
 * Colors come from the theme's --primary token, not a hardcoded brand color.
 */
export default function Stepper({ current, labels }) {
    return (
        <div className="flex justify-center mb-10">
            <div className="flex items-center w-full max-w-3xl">
                {labels.map((label, i) => {
                    const n = i + 1;
                    const done = n < current;
                    const active = n <= current;
                    return (
                        <Fragment key={n}>
                            <div className="flex flex-col items-center shrink-0">
                                <div
                                    className={`w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold transition-colors ${
                                        active ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'
                                    }`}
                                >
                                    {done ? <Check className="h-5 w-5" /> : n}
                                </div>
                                <span className="mt-2 text-xs sm:text-sm text-muted-foreground text-center max-w-[6rem] sm:max-w-none">
                                    {label}
                                </span>
                            </div>
                            {n < labels.length && (
                                <div className={`flex-1 h-1 mx-2 rounded transition-colors ${done ? 'bg-primary' : 'bg-muted'}`} />
                            )}
                        </Fragment>
                    );
                })}
            </div>
        </div>
    );
}
