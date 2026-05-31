import { z } from 'zod';
import { Link } from '@inertiajs/react';
import { useZodForm } from '@/hooks/useZodForm';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/Layouts/AdminLayout';

const schema = z.object({
    type: z.string().min(1, 'The type field is required.'),
});

function ReminderTypeCreate() {
    const { form, submit } = useZodForm(schema, {
        defaultValues: { type: '' },
    });
    const { register, formState: { errors, isSubmitting } } = form;

    return (
        <div className="space-y-6 p-6">
            <Card>
                <CardHeader>
                    <CardTitle>Create Reminder Type</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('reminder-type.store'))}>
                        <div className="grid grid-cols-1 gap-4">
                            <div className="space-y-1.5">
                                <Label htmlFor="type">Type</Label>
                                <Input id="type" placeholder="Enter type" {...register('type')} />
                                {errors.type && <p className="text-sm text-destructive">{errors.type.message}</p>}
                            </div>
                        </div>

                        <div className="flex justify-end gap-2 mt-4">
                            <Button variant="ghost" type="button" asChild>
                                <Link href={route('reminder-type.index')}>Close</Link>
                            </Button>
                            <Button type="submit" disabled={isSubmitting}>Create</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

ReminderTypeCreate.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Reminder Type', href: route('reminder-type.index') },
        { label: 'Create' },
    ]}>{page}</AdminLayout>
);
export default ReminderTypeCreate;
