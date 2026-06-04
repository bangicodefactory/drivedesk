import * as React from 'react';
import { Check, ChevronsUpDown } from 'lucide-react';
import { cn } from '@/lib/utils';
import { Input } from '@/components/ui/input';

// Lightweight searchable single-select (combobox) built on existing primitives —
// no cmdk/popover dependency. Options are { value, label }. Value is compared as
// a string so it works with numeric ids coming from the server.
export function SearchableSelect({
    options = [],
    value,
    onChange,
    placeholder = 'Select…',
    searchPlaceholder = 'Search…',
    emptyText = 'No results',
    ariaLabel,
    className,
}) {
    const [open, setOpen] = React.useState(false);
    const [query, setQuery] = React.useState('');
    const ref = React.useRef(null);

    React.useEffect(() => {
        function onDocClick(e) {
            if (ref.current && !ref.current.contains(e.target)) setOpen(false);
        }
        document.addEventListener('mousedown', onDocClick);
        return () => document.removeEventListener('mousedown', onDocClick);
    }, []);

    const selected = options.find((o) => String(o.value) === String(value));
    const q = query.trim().toLowerCase();
    const filtered = q
        ? options.filter((o) => String(o.label).toLowerCase().includes(q))
        : options;

    function select(v) {
        onChange?.(String(v));
        setOpen(false);
        setQuery('');
    }

    return (
        <div className="relative" ref={ref}>
            <button
                type="button"
                aria-label={ariaLabel}
                aria-haspopup="listbox"
                aria-expanded={open}
                onClick={() => setOpen((o) => !o)}
                className={cn(
                    'flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
                    className
                )}
            >
                <span className={cn('truncate', !selected && 'text-muted-foreground')}>
                    {selected ? selected.label : placeholder}
                </span>
                <ChevronsUpDown className="h-4 w-4 shrink-0 opacity-50" />
            </button>

            {open && (
                <div className="absolute z-50 mt-1 w-full rounded-md border bg-popover text-popover-foreground shadow-md">
                    <div className="p-2">
                        <Input
                            autoFocus
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder={searchPlaceholder}
                            className="h-8"
                        />
                    </div>
                    <ul className="max-h-60 overflow-auto p-1">
                        {filtered.length === 0 && (
                            <li className="px-2 py-1.5 text-sm text-muted-foreground">{emptyText}</li>
                        )}
                        {filtered.map((o) => (
                            <li key={o.value}>
                                <button
                                    type="button"
                                    onClick={() => select(o.value)}
                                    className={cn(
                                        'flex w-full items-center justify-between rounded-sm px-2 py-1.5 text-left text-sm hover:bg-accent hover:text-accent-foreground',
                                        String(o.value) === String(value) && 'bg-accent'
                                    )}
                                >
                                    <span className="truncate">{o.label}</span>
                                    {String(o.value) === String(value) && <Check className="h-4 w-4 shrink-0" />}
                                </button>
                            </li>
                        ))}
                    </ul>
                </div>
            )}
        </div>
    );
}
