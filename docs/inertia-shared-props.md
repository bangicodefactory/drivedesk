# Inertia Shared Props & Form Pattern

This document is the authoritative reference for:
1. **Shared props** — what every React page can read from `usePage().props`
2. **Form pattern** — how all SPA forms are built with `useZodForm`

---

## 1. Shared Props Contract

Every Inertia response includes the following top-level props, shared via
`HandleInertiaRequests::share()` (`app/Http/Middleware/HandleInertiaRequests.php`).

JSDoc type definitions live in `resources/js/types/inertia.js`.

| Prop | Type | Description |
|------|------|-------------|
| `auth.user` | `AuthUser \| null` | Authenticated user (id, name, email, type, lang, profile, company_name) |
| `auth.permissions` | `string[]` | Spatie permission slugs (e.g. `['manage-cars', 'view-bookings']`) |
| `branding.appName` | `string` | App name from the `Setting` model |
| `branding.logoUrl` | `string` | Logo filename from the `Setting` model |
| `branding.faviconUrl` | `string` | Favicon filename from the `Setting` model |
| `branding.cssVars` | `Record<string, string>` | CSS custom property overrides applied to `:root` |
| `branding.layoutMode` | `'lightmode' \| 'darkmode'` | Drives `ThemeProvider` initial theme |
| `branding.layoutDirection` | `'ltrmode' \| 'rtlmode'` | Drives `<html dir>` |
| `client.name` | `string` | Active `APP_CLIENT` value (e.g. `'directonderweg'`) |
| `client.default_locale` | `string` | Default locale code (e.g. `'en'`) |
| `client.supported_locales` | `string[]` | All locale codes with `resources/lang/<code>/` directories |
| `client.features` | `ClientFeatures` | Feature flags resolved by `ClientServiceProvider` |
| `translations` | `Record<string, string>` | Current locale's `resources/lang/<locale>.json` key→value pairs |
| `flash.success` | `string \| null` | One-time success message (from `redirect()->with('success', …)`) |
| `flash.error` | `string \| null` | One-time error message (from `redirect()->with('error', …)`) |
| `ziggy` | `ZiggyConfig` | Route definitions for the `route()` helper (added by Ziggy in BAN-51) |

### Feature flags (`client.features`)

All flags default to the values in `config/clients/_default.php`. Per-client
overrides live in `config/clients/<client>.php`.

| Flag | Default | Description |
|------|---------|-------------|
| `paypal` | `true` | PayPal checkout |
| `stripe` | `true` | Stripe checkout |
| `subscriptions` | `true` | Subscription billing |
| `booking_payment` | `true` | Payment step in booking flow |
| `excel_import` | `true` | Excel bulk-import |
| `multi_branch` | `false` | Multi-branch fleet management |
| `tva_renumber` | `true` | TVA invoice renumbering |
| `signatures` | `true` | Digital signature pad |

### Reading props in a page component

```jsx
import { usePage } from '@inertiajs/react';

export default function SomePage() {
    const { auth, branding, client, flash, translations } = usePage().props;

    // Guard a feature-gated section
    if (!client.features.paypal) return null;

    // Show a flash message
    if (flash.success) return <p>{flash.success}</p>;

    return <h1>Hello, {auth.user?.name}</h1>;
}
```

### Checking permissions

```jsx
const { auth } = usePage().props;

const canManageCars = auth.permissions.includes('manage-cars');
```

### Reading translations

```jsx
const { translations: t } = usePage().props;

<p>{t['Coupon successfully created.']}</p>
```

> Translations are the current-locale JSON strings only. Blade-side PHP
> translations use `__()` / `trans()` as before. Do not duplicate keys into JS.

---

## 2. Form Pattern — `useZodForm`

### Rules (from CLAUDE.md §5)

- **All SPA forms** use `react-hook-form` + `zod` via `useZodForm`.
- Client-side validation is **UX-only** — Laravel's server validation is authoritative.
- Zod schemas are **co-located** with the form component; do not share schemas across
  unrelated pages.
- Server errors (422) flow back into RHF via `setError`; there is no separate
  client-side error state.

### Hook signature

```js
// resources/js/hooks/useZodForm.js
const { form, submit } = useZodForm(schema, rhfOptions);
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `schema` | `ZodSchema` | Zod schema — must be a **stable module-level constant** (RHF captures the resolver once at mount) |
| `rhfOptions` | `UseFormProps` | Passed to `useForm()` (e.g. `defaultValues`). A `resolver` key is ignored — zod always wins. |
| **Returns** `form` | `UseFormReturn` | Full react-hook-form instance |
| **Returns** `submit` | `(method, url, options?) => FormEventHandler` | Returns an `onSubmit` handler |

### How it works

1. `useForm({ ...options, resolver: zodResolver(schema) })` — RHF validates on submit.
2. If validation passes, `router[method](url, data, …)` sends it via Inertia.
3. On a 422 response, Laravel's field errors are mapped to `form.setError(field, { type: 'server', message })`.
4. The handler wraps the router call in a Promise resolved in `onFinish`, so
   `form.formState.isSubmitting` correctly tracks the in-flight request.
5. `onBefore: () => false` to cancel resolves the Promise immediately (no frozen `isSubmitting`).
6. `router.delete` is special-cased: data is passed inside `options.data`.

### Worked example — login form

```jsx
// resources/js/Pages/Auth/Login.jsx
import { z } from 'zod';
import { useZodForm } from '@/hooks/useZodForm';
import { Button } from '@/components/ui/button';
import { Input }  from '@/components/ui/input';
import { Label }  from '@/components/ui/label';

const loginSchema = z.object({
    email:    z.string().email('Enter a valid email'),
    password: z.string().min(8, 'At least 8 characters'),
});

export default function Login() {
    const { form, submit } = useZodForm(loginSchema, {
        defaultValues: { email: '', password: '' },
    });

    const { register, formState: { errors, isSubmitting } } = form;

    return (
        <form onSubmit={submit('post', route('login'))}>
            <div>
                <Label htmlFor="email">Email</Label>
                <Input id="email" type="email" {...register('email')} />
                {errors.email && <p>{errors.email.message}</p>}
            </div>

            <div>
                <Label htmlFor="password">Password</Label>
                <Input id="password" type="password" {...register('password')} />
                {errors.password && <p>{errors.password.message}</p>}
            </div>

            <Button type="submit" disabled={isSubmitting}>
                {isSubmitting ? 'Signing in…' : 'Sign in'}
            </Button>
        </form>
    );
}
```

### Passing extra Inertia options

```js
<form onSubmit={submit('patch', route('profile.update'), {
    preserveScroll: true,
    onSuccess: () => toast.success('Profile saved'),
})}>
```

### What the hook does NOT do

- Does not reset the form on success — add `form.reset()` in `onSuccess` if needed.
- Does not manage a separate loading/error state — use `form.formState`.
- The zod schema is client-side UX only; **always** keep the matching Laravel
  validation rule in the controller.

---

## 3. Adding new shared props

1. Add the value to `HandleInertiaRequests::share()`.
2. Add a `@typedef` to `resources/js/types/inertia.js` and update `SharedProps`.
3. Update the table in §1 of this document.
4. If the prop drives React behaviour, document it in the relevant section.
