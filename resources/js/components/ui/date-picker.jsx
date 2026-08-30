import * as React from 'react';
import { Calendar } from 'lucide-react';
import { cn } from '@/lib/utils';

// Shared date field that DISPLAYS the value as day/month/year (dd/mm/yyyy) to
// follow the app's System Date Format, while keeping the value in ISO
// (yyyy-mm-dd) for filters/forms and the server — so callers and the backend
// are unchanged.
//
// It reuses the browser's NATIVE date picker for the actual calendar UI
// (accessibility, keyboard, mobile) via HTMLInputElement.showPicker(): a
// transparent native <input type="date"> overlays the field, so a click
// anywhere opens the real calendar while the visible text/icon render in
// dd/mm/yyyy. No extra dependencies.
//
// Props mirror the native input: `value` (ISO string), `onChange(iso)`,
// `placeholder`, `id`, `className`, plus any pass-through props (e.g. `min`).
function formatDmy(iso) {
    if (!iso) return '';
    const m = /^(\d{4})-(\d{2})-(\d{2})/.exec(iso);
    return m ? `${m[3]}/${m[2]}/${m[1]}` : iso;
}

const DatePicker = React.forwardRef(function DatePicker(
    { value = '', onChange, placeholder = 'dd/mm/yyyy', id, className, disabled, ...props },
    forwardedRef,
) {
    const innerRef = React.useRef(null);
    const setRef = (el) => {
        innerRef.current = el;
        if (typeof forwardedRef === 'function') forwardedRef(el);
        else if (forwardedRef) forwardedRef.current = el;
    };

    function openPicker() {
        const el = innerRef.current;
        if (el && typeof el.showPicker === 'function') {
            // showPicker throws if called when already open / not allowed — ignore.
            try { el.showPicker(); } catch (_) { /* noop */ }
        }
    }

    return (
        <div
            className={cn(
                'relative flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background md:text-sm',
                'focus-within:outline-none focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2',
                disabled && 'cursor-not-allowed opacity-50',
                className,
            )}
        >
            <span
                aria-hidden="true"
                className={cn('pointer-events-none flex-1 truncate', !value && 'text-muted-foreground')}
            >
                {value ? formatDmy(value) : placeholder}
            </span>
            <Calendar aria-hidden="true" className="pointer-events-none ms-2 h-4 w-4 shrink-0 text-muted-foreground" />
            <input
                ref={setRef}
                id={id}
                type="date"
                value={value}
                disabled={disabled}
                onChange={(e) => onChange?.(e.target.value)}
                onClick={openPicker}
                // Overlays the whole field, fully transparent: it carries the
                // value + provides the native calendar, but the dd/mm/yyyy text
                // above is what the user sees.
                className="absolute inset-0 h-full w-full cursor-pointer opacity-0 disabled:cursor-not-allowed"
                {...props}
            />
        </div>
    );
});

export { DatePicker, formatDmy };
