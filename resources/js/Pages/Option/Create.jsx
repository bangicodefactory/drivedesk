import { z } from 'zod';
import { Link } from '@inertiajs/react';
import { useZodForm } from '@/hooks/useZodForm';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/Layouts/AdminLayout';

// Port of resources/views/option/create.blade.php.
// Field name matches the Blade form 1:1 (name). Posts to route('option.store')
// (url('option')). The zod schema mirrors the controller store() `required`
// rule for UX only; Laravel validation stays authoritative and its errors
// surface via setError inside useZodForm.
const schema = z.object({
    name: z.string().min(1, 'The name field is required.'),
});

function OptionCreate() {
    const { form, submit } = useZodForm(schema, {
        defaultValues: { name: '' },
    });
    const { register, formState: { errors, isSubmitting } } = form;

    return (
        <div className="space-y-6 p-6">
            <Card>
                <CardHeader>
                    <CardTitle>Create Option</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('option.store'))}>
                        <div className="space-y-1.5">
                            <Label htmlFor="name">Option</Label>
                            <Input id="name" placeholder="Enter option" {...register('name')} />
                            {errors.name && <p className="text-sm text-destructive">{errors.name.message}</p>}
                        </div>

                        <div className="flex justify-end gap-2 mt-4">
                            <Button variant="ghost" type="button" asChild>
                                <Link href={route('option.index')}>Close</Link>
                            </Button>
                            <Button type="submit" disabled={isSubmitting}>Create</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

OptionCreate.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Option', href: route('option.index') },
        { label: 'Create' },
    ]}>{page}</AdminLayout>
);
export default OptionCreate;
