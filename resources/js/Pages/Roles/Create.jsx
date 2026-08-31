import { z } from 'zod';
import { Link } from '@inertiajs/react';
import { useZodForm } from '@/hooks/useZodForm';
import { Controller } from 'react-hook-form';
import { Button }   from '@/components/ui/button';
import { Input }    from '@/components/ui/input';
import { Label }    from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';
import FieldError from '@/components/FieldError';
import { fieldA11y } from '@/lib/fieldA11y';

const schema = z.object({
    title: z.string().min(1, 'Role name is required'),
    user_permission: z.array(z.number()).min(1, 'Pick at least one permission'),
});

function RolesCreate({ permissions = [] }) {
    const t = useTranslation();
    const { form, submit } = useZodForm(schema, {
        defaultValues: { title: '', user_permission: [] },
    });
    const { register, control, formState: { errors, isSubmitting } } = form;

    function toggle(field, id) {
        const set = new Set(field.value ?? []);
        set.has(id) ? set.delete(id) : set.add(id);
        field.onChange([...set]);
    }

    return (
        <div className="max-w-3xl space-y-6 p-6">
            <h1 className="text-3xl font-bold tracking-tight">{t('New role')}</h1>

            <Card>
                <CardHeader><CardTitle>{t('Role details')}</CardTitle></CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('role.store'))} className="space-y-6">
                        <div className="space-y-1.5">
                            <Label htmlFor="title">{t('Role name')}</Label>
                            <Input id="title" autoFocus {...register('title')} {...fieldA11y(errors, 'title')} />
                            <FieldError name="title" errors={errors} />
                        </div>

                        <div className="space-y-2">
                            <Label>{t('Permissions')}</Label>
                            <Controller
                                name="user_permission"
                                control={control}
                                render={({ field }) => (
                                    <div
                                        role="group"
                                        aria-label={t('Permissions')}
                                        className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3"
                                        {...fieldA11y(errors, 'user_permission')}
                                    >
                                        {permissions.map((p) => (
                                            <label
                                                key={p.id}
                                                className="flex items-center gap-2 rounded-md border px-3 py-2 hover:bg-accent cursor-pointer"
                                            >
                                                <Checkbox
                                                    checked={(field.value ?? []).includes(p.id)}
                                                    onCheckedChange={() => toggle(field, p.id)}
                                                />
                                                <span className="text-sm">{p.name}</span>
                                            </label>
                                        ))}
                                    </div>
                                )}
                            />
                            <FieldError name="user_permission" errors={errors} />
                        </div>

                        <div className="flex gap-2">
                            <Button type="submit" disabled={isSubmitting}>
                                {isSubmitting ? t('Creating…') : t('Create role')}
                            </Button>
                            <Button variant="ghost" type="button" asChild>
                                <Link href={route('role.index')}>{t('Cancel')}</Link>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

RolesCreate.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Roles', href: route('role.index') },
        { label: 'New' },
    ]}>{page}</AdminLayout>
);
export default RolesCreate;
