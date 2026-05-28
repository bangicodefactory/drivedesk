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
 * @param {import('zod').ZodSchema} schema
 * @param {import('react-hook-form').UseFormProps} [options]
 * @returns {{ form: import('react-hook-form').UseFormReturn, submit: Function }}
 */
export function useZodForm(schema, options = {}) {
    const form = useForm({
        resolver: zodResolver(schema),
        ...options,
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
     * @param {'post'|'put'|'patch'|'delete'} method
     * @param {string} url
     * @param {Object} [inertiaOptions]  Extra Inertia visit options (preserveScroll, onSuccess, …)
     */
    const submit = useCallback(
        (method, url, inertiaOptions = {}) =>
            handleSubmit(
                (data) =>
                    new Promise((resolve) => {
                        router[method](url, data, {
                            ...inertiaOptions,
                            onError(serverErrors) {
                                Object.entries(serverErrors).forEach(([field, message]) => {
                                    setError(field, { type: 'server', message });
                                });
                                inertiaOptions.onError?.(serverErrors);
                            },
                            onFinish() {
                                inertiaOptions.onFinish?.();
                                resolve();
                            },
                        });
                    })
            ),
        [handleSubmit, setError]
    );

    return { form, submit };
}
