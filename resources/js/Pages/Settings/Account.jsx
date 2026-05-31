import { z } from 'zod';
import { useZodForm } from '@/hooks/useZodForm';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import AdminLayout from '@/Layouts/AdminLayout';

const schema = z.object({
    name:    z.string().min(1, 'Name is required').max(255),
    email:   z.string().email('Enter a valid email address'),
    profile: z.any().optional(),
});

function Account({ loginUser }) {
    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            name:  loginUser?.name  ?? '',
            email: loginUser?.email ?? '',
        },
    });
    const { register, formState: { errors, isSubmitting } } = form;

    return (
        <div className="space-y-6 p-6">
            <div>
                <h1 className="text-2xl font-semibold">Account Settings</h1>
                <p className="text-sm text-muted-foreground">Update your profile information.</p>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Profile</CardTitle>
                    <CardDescription>Your name, email address and profile picture.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form
                        onSubmit={submit('post', route('setting.account'), { forceFormData: true })}
                        encType="multipart/form-data"
                        className="space-y-4"
                    >
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div className="space-y-1.5">
                                <Label htmlFor="name">Name</Label>
                                <Input id="name" placeholder="Enter your name" autoComplete="name" {...register('name')} />
                                {errors.name && <p className="text-sm text-destructive">{errors.name.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="email">Email Address</Label>
                                <Input id="email" type="email" placeholder="Enter your email" autoComplete="email" {...register('email')} />
                                {errors.email && <p className="text-sm text-destructive">{errors.email.message}</p>}
                            </div>
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="profile">Profile</Label>
                            <Input id="profile" type="file" accept="image/*" {...register('profile')} />
                            {errors.profile && <p className="text-sm text-destructive">{errors.profile.message}</p>}
                        </div>

                        <div className="flex justify-end">
                            <Button type="submit" disabled={isSubmitting}>
                                {isSubmitting ? 'Saving…' : 'Save'}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

Account.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Settings' }, { label: 'Account Settings' }]}>
        {page}
    </AdminLayout>
);
export default Account;
