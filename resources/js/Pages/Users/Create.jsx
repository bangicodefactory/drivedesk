import { z } from 'zod';
import { Link } from '@inertiajs/react';
import { useZodForm } from '@/hooks/useZodForm';
import { Controller } from 'react-hook-form';
import { Button } from '@/components/ui/button';
import { Input }  from '@/components/ui/input';
import { Label }  from '@/components/ui/label';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/Layouts/AdminLayout';

const schema = z.object({
    name:     z.string().min(1, 'Name is required'),
    email:    z.string().email('Enter a valid email address'),
    password: z.string().min(6, 'At least 6 characters'),
    role:     z.string().min(1, 'Pick a role'),
});

function UsersCreate({ userRoles = [] }) {
    const { form, submit } = useZodForm(schema, {
        defaultValues: { name: '', email: '', password: '', role: '' },
    });
    const { register, control, formState: { errors, isSubmitting } } = form;

    return (
        <div className="max-w-2xl space-y-6 p-6">
            <h1 className="text-2xl font-semibold">New user</h1>

            <Card>
                <CardHeader><CardTitle>User details</CardTitle></CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('users.store'))} className="space-y-4">
                        <div className="space-y-1.5">
                            <Label htmlFor="name">Name</Label>
                            <Input id="name" autoFocus {...register('name')} />
                            {errors.name && <p className="text-sm text-destructive">{errors.name.message}</p>}
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="email">Email</Label>
                            <Input id="email" type="email" {...register('email')} />
                            {errors.email && <p className="text-sm text-destructive">{errors.email.message}</p>}
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="password">Password</Label>
                            <Input id="password" type="password" autoComplete="new-password" {...register('password')} />
                            {errors.password && <p className="text-sm text-destructive">{errors.password.message}</p>}
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="role">Role</Label>
                            <Controller
                                name="role"
                                control={control}
                                render={({ field }) => (
                                    <Select value={field.value} onValueChange={field.onChange}>
                                        <SelectTrigger id="role"><SelectValue placeholder="Select a role" /></SelectTrigger>
                                        <SelectContent>
                                            {userRoles.map((r) => (
                                                <SelectItem key={r.id} value={String(r.id)}>{r.name}</SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                )}
                            />
                            {errors.role && <p className="text-sm text-destructive">{errors.role.message}</p>}
                        </div>

                        <div className="flex gap-2">
                            <Button type="submit" disabled={isSubmitting}>
                                {isSubmitting ? 'Creating…' : 'Create user'}
                            </Button>
                            <Button variant="ghost" type="button" asChild>
                                <Link href={route('users.index')}>Cancel</Link>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

UsersCreate.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Users', href: route('users.index') },
        { label: 'New' },
    ]}>{page}</AdminLayout>
);
export default UsersCreate;
