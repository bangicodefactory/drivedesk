import { z } from 'zod';
import { Link } from '@inertiajs/react';
import { useZodForm } from '@/hooks/useZodForm';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/Layouts/AdminLayout';

// Port of resources/views/option/edit.blade.php.
// Submits PUT to route('option.update') via a spoofed _method=PUT (matches the
// Blade @method('PUT')). Field name matches the Blade form 1:1 (name). The zod
// schema mirrors the controller update() `required` rule. Laravel validation
// stays authoritative. Prop `option` matches the controller compact('option').
const schema = z.object({
    _method: z.string().optional(),
    name: z.string().min(1, 'The name field is required.'),
});

function OptionEdit({ option = {} }) {
    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            name: option.name ?? '',
            _method: 'PUT',
        },
    });
    const { register, formState: { errors, isSubmitting } } = form;

    return (
        <div className="space-y-6 p-6">
            <Card>
                <CardHeader>
                    <CardTitle>Edit Option</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('option.update', option.id))}>
                        <div className="space-y-1.5">
                            <Label htmlFor="name">Option</Label>
                            <Input id="name" placeholder="Enter option" {...register('name')} />
                            {errors.name && <p className="text-sm text-destructive">{errors.name.message}</p>}
                        </div>

                        <div className="flex justify-end gap-2 mt-4">
                            <Button variant="ghost" type="button" asChild>
                                <Link href={route('option.index')}>Close</Link>
                            </Button>
                            <Button type="submit" disabled={isSubmitting}>Update</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

OptionEdit.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Option', href: route('option.index') },
        { label: 'Edit' },
    ]}>{page}</AdminLayout>
);
export default OptionEdit;
