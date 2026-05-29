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

const schema = z.object({
    title: z.string().min(1, 'Role name is required'),
    user_permission: z.array(z.number()).min(1, 'Pick at least one permission'),
});

function RolesEdit({ role, permissions = [], assignedPermissions = [] }) {
    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            title: role?.name ?? '',
            user_permission: assignedPermissions ?? [],
        },
    });
    const { register, control, formState: { errors, isSubmitting } } = form;

    function toggle(field, id) {
        const set = new Set(field.value ?? []);
        set.has(id) ? set.delete(id) : set.add(id);
        field.onChange([...set]);
    }

    return (
        <div className="max-w-3xl space-y-6 p-6">
            <h1 className="text-2xl font-semibold">Edit role</h1>

            <Card>
                <CardHeader><CardTitle>{role?.name}</CardTitle></CardHeader>
                <CardContent>
                    <form onSubmit={submit('put', route('role.update', role.id))} className="space-y-6">
                        <div className="space-y-1.5">
                            <Label htmlFor="title">Role name</Label>
                            <Input id="title" {...register('title')} />
                            {errors.title && <p className="text-sm text-destructive">{errors.title.message}</p>}
                        </div>

                        <div className="space-y-2">
                            <Label>Permissions</Label>
                            <Controller
                                name="user_permission"
                                control={control}
                                render={({ field }) => (
                                    <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
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
                            {errors.user_permission && (
                                <p className="text-sm text-destructive">{errors.user_permission.message}</p>
                            )}
                        </div>

                        <div className="flex gap-2">
                            <Button type="submit" disabled={isSubmitting}>
                                {isSubmitting ? 'Saving…' : 'Save changes'}
                            </Button>
                            <Button variant="ghost" type="button" asChild>
                                <Link href={route('role.index')}>Cancel</Link>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

RolesEdit.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Roles', href: route('role.index') },
        { label: 'Edit' },
    ]}>{page}</AdminLayout>
);
export default RolesEdit;
