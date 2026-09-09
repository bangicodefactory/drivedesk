/**
 * Accessibility attributes for a hand-rolled form field.
 *
 * Most pages build fields as `<Label htmlFor> + <Input id> + error <p>`
 * rather than through `components/ui/form.jsx`, so nothing tells assistive
 * technology that a field is invalid or which text explains why. Spread the
 * result of `fieldA11y(errors, name)` onto the input and render
 * `<FieldError name errors />` right after it; the two agree on the id.
 *
 *   <Input id="type" {...register('type')} {...fieldA11y(errors, 'type')} />
 *   <FieldError name="type" errors={errors} />
 *
 * `errors` accepts either shape used in this codebase: react-hook-form's
 * `formState.errors` (`{ [name]: { message } }`) or Inertia's own `useForm`
 * errors (`{ [name]: string }`).
 *
 * @param {Record<string, {message?: string}|string>|undefined} errors
 * @param {string} name  Field name as registered
 * @returns {{'aria-invalid'?: true, 'aria-describedby'?: string}}
 */
export function fieldA11y(errors, name) {
    // Gate on the message, not the error object: FieldError renders nothing
    // without a message, and aria-describedby must not point at a missing id.
    if (!fieldErrorMessage(errors, name)) return {};
    return {
        'aria-invalid': true,
        'aria-describedby': fieldErrorId(name),
    };
}

/** The id `FieldError` renders for `name`, and `fieldA11y` points at. */
export function fieldErrorId(name) {
    return `${name}-error`;
}

/** Extracts a field's message from either error-object shape, or undefined. */
export function fieldErrorMessage(errors, name) {
    const error = errors?.[name];
    if (!error) return undefined;
    return typeof error === 'string' ? error || undefined : error.message;
}
