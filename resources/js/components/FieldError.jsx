import { cn } from '@/lib/utils';
import { fieldErrorId, fieldErrorMessage } from '@/lib/fieldA11y';

/**
 * Validation message for one form field, announced to assistive technology.
 *
 * Pair with `fieldA11y(errors, name)` on the input (see lib/fieldA11y.js).
 * Renders nothing when the field has no error, so it can sit unconditionally
 * under every input.
 */
export default function FieldError({ name, errors, className }) {
    const message = fieldErrorMessage(errors, name);
    if (!message) return null;

    return (
        <p id={fieldErrorId(name)} role="alert" className={cn('text-sm text-destructive', className)}>
            {message}
        </p>
    );
}
