# Inertia Shared Props & Form Pattern

This document is the authoritative reference for:
1. **Shared props** — what every React page can read from `usePage().props`
2. **Form pattern** — how all SPA forms are built with `useZodForm`

---

## 1. Shared Props Contract

> **Status:** partially implemented (BAN-55 / BAN-56).
> Fields marked ✅ are live; fields marked 🔲 are planned for BAN-56.

Every Inertia response includes the following top-level props, shared via
`HandleInertiaRequests::share()`:

| Prop | Type | Status | Description |
|------|------|--------|-------------|
| `branding.cssVars` | `Record<string, string>` | ✅ BAN-54 | CSS custom property overrides (`--primary`, `--ring`, …) |
| `branding.layoutMode` | `'lightmode' \| 'darkmode'` | ✅ BAN-54 | Drives ThemeProvider initial theme |
| `branding.layoutDirection` | `'ltrmode' \| 'rtlmode'` | ✅ BAN-54 | Drives `<html dir>` |
| `auth.user` | `User \| null` | 🔲 BAN-56 | Authenticated user |
| `auth.permissions` | `string[]` | 🔲 BAN-56 | Spatie permission slugs |
| `client.name` | `string` | 🔲 BAN-56 | Active APP_CLIENT value |
| `client.features` | `Record<string, boolean>` | 🔲 BAN-56 | Feature flags |
| `translations` | `Record<string, string>` | 🔲 BAN-56 | Current locale strings |
| `flash.success` | `string \| null` | 🔲 BAN-56 | One-time success message |
| `flash.error` | `string \| null` | 🔲 BAN-56 | One-time error message |
| `ziggy` | `ZiggyConfig` | ✅ BAN-51 | Route definitions for `route()` helper |

TypeScript types for the full contract will live in
`resources/js/types/inertia.d.ts` (added in BAN-56).

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
| `schema` | `ZodSchema` | Zod schema for client-side validation |
| `rhfOptions` | `UseFormProps` | Passed directly to `useForm()` (e.g. `defaultValues`) |
| **Returns** `form` | `UseFormReturn` | Full react-hook-form instance |
| **Returns** `submit` | `(method, url, options?) => FormEventHandler` | Returns an `onSubmit` handler |

### How it works

1. `useForm({ resolver: zodResolver(schema) })` — RHF validates on submit.
2. If the data passes client-side validation, `router[method](url, data, …)` sends it via Inertia (preserves session, flash, redirects).
3. On a 422 response, Laravel's field errors are mapped to `form.setError(field, { type: 'server', message })`.
4. The handler returns a `Promise` resolved in Inertia's `onFinish`, so `form.formState.isSubmitting` correctly tracks the in-flight request.

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
// Preserve scroll position, handle success explicitly
<form onSubmit={submit('patch', route('profile.update'), {
    preserveScroll: true,
    onSuccess: () => toast.success('Profile saved'),
})}>
```

### What the hook does NOT do

- It does not manage a separate loading/error state — use `form.formState`.
- It does not reset the form on success — add `form.reset()` in `onSuccess` if needed.
- The zod schema is client-side UX only; **always** keep the matching Laravel
  validation rule in the controller. If the two diverge, Laravel wins.

---

## 3. Adding new shared props

1. Add the value to `HandleInertiaRequests::share()` in
   `app/Http/Middleware/HandleInertiaRequests.php`.
2. Add the TypeScript type to `resources/js/types/inertia.d.ts`
   (or JSDoc if still pre-BAN-56).
3. Update the table in §1 of this document.
4. If the prop drives React behaviour, document it in the relevant section.
