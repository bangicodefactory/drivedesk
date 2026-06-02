import { useCallback } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { router } from '@inertiajs/react';

/**
 * Bridges react-hook-form (client-side zod validation) with Inertia (server submission).
 *
 * Client-side validation is UX-only — Laravel validation remains authoritative.
 * Server errors from a 422 response are mapped back into RHF field errors via setError().
 *
 * @param {import('zod').ZodSchema} schema  Must be a stable (module-level) constant.
 *   Passing a schema constructed inline on every render will silently use a stale
 *   resolver after the first render — RHF captures the resolver once at mount.
 * @param {import('react-hook-form').UseFormProps} [options]
 * @returns {{ form: import('react-hook-form').UseFormReturn, submit: Function }}
 */
export function useZodForm(schema, options = {}) {
    // resolver is applied last so a caller-supplied resolver in `options` cannot
    // silently override the zod resolver and bypass client-side validation.
    const form = useForm({
        mode: 'onTouched',
        ...options,
        resolver: zodResolver(schema),
    });

    const { handleSubmit, setError } = form;

    /**
     * Returns an onSubmit event handler.
     *
     * RHF validates via the zod schema first. If the data is clean it is sent to
     * the server via Inertia. Any 422 validation errors returned by Laravel are
     * mapped to the matching RHF field so they surface in formState.errors.
     *
     * form.formState.isSubmitting stays true until the Inertia request finishes
     * (success or error) because the handler returns a Promise resolved in onFinish.
     *
     * NOTE: if you pass onBefore in inertiaOptions and it returns false to cancel
     * the request, the Promise resolves immediately so isSubmitting resets correctly.
     *
     * @param {'post'|'put'|'patch'|'delete'} method
     * @param {string} url
     * @param {Object} [inertiaOptions]  Extra Inertia visit options (preserveScroll, onSuccess, …)
     */
    const submit = useCallback(
        (method, url, inertiaOptions = {}) =>
            handleSubmit(
                (data) =>
                    new Promise((resolve) => {
                        // router.delete(url, options) has no data parameter — form data
                        // must be passed inside options.data to avoid being silently dropped.
                        const isDelete = method === 'delete';

                        const visitOptions = {
                            ...inertiaOptions,
                            // Wrap onBefore so that if it cancels the request (returns false),
                            // we still resolve the Promise and clear isSubmitting.
                            onBefore(visit) {
                                const result = inertiaOptions.onBefore?.(visit);
                                if (result === false) {
                                    resolve();
                                    return false;
                                }
                            },
                            onError(serverErrors) {
                                Object.entries(serverErrors).forEach(([field, message]) => {
                                    setError(field, { type: 'server', message });
                                });
                                inertiaOptions.onError?.(serverErrors);
                            },
                            onFinish(visit) {
                                inertiaOptions.onFinish?.(visit);
                                resolve();
                            },
                        };

                        if (isDelete) {
                            router.delete(url, { ...visitOptions, data });
                        } else {
                            router[method](url, data, visitOptions);
                        }
                    })
            ),
        [handleSubmit, setError]
    );

    return { form, submit };
}
