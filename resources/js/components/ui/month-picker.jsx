import * as React from 'react';
import { Calendar } from 'lucide-react';
import { cn } from '@/lib/utils';

// Month field that mirrors DatePicker: the value is kept in ISO month form
// (yyyy-mm) for filters/forms and the server, while the visible text renders in
// the app's typography as mm/yyyy. The browser's NATIVE month picker provides
// the actual calendar (accessibility, keyboard, mobile) via a transparent
// <input type="month"> overlay, so a click anywhere opens the real picker but
// none of the native chrome (spinner segments, dashed placeholder, duplicate
// picker glyph) shows. No extra dependencies. Matches ./date-picker.jsx.
function formatMy(month) {
    if (!month) return '';
    const m = /^(\d{4})-(\d{2})$/.exec(month);
    return m ? `${m[2]}/${m[1]}` : month;
}

const MonthPicker = React.forwardRef(function MonthPicker(
    { value = '', onChange, placeholder = 'mm/yyyy', id, className, disabled, ...props },
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
            // showPicker throws if already open / not allowed — ignore.
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
            <Calendar aria-hidden="true" className="pointer-events-none me-2 h-4 w-4 shrink-0 text-muted-foreground" />
            <span
                aria-hidden="true"
                className={cn('pointer-events-none flex-1 truncate tabular-nums', !value && 'text-muted-foreground')}
            >
                {value ? formatMy(value) : placeholder}
            </span>
            <input
                ref={setRef}
                id={id}
                type="month"
                value={value}
                disabled={disabled}
                onChange={(e) => onChange?.(e.target.value)}
                onClick={openPicker}
                // Overlays the whole field, fully transparent: carries the value
                // and provides the native picker, while the mm/yyyy text above is
                // what the user sees.
                className="absolute inset-0 h-full w-full cursor-pointer opacity-0 disabled:cursor-not-allowed"
                {...props}
            />
        </div>
    );
});

export { MonthPicker, formatMy };
