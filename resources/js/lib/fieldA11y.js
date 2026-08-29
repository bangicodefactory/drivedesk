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
 * @param {Record<string, {message?: string}>|undefined} errors  RHF formState.errors
 * @param {string} name  Field name as registered
 * @returns {{'aria-invalid'?: true, 'aria-describedby'?: string}}
 */
export function fieldA11y(errors, name) {
    if (!errors?.[name]) return {};
    return {
        'aria-invalid': true,
        'aria-describedby': fieldErrorId(name),
    };
}

/** The id `FieldError` renders for `name`, and `fieldA11y` points at. */
export function fieldErrorId(name) {
    return `${name}-error`;
}
