import { z } from 'zod';
import { Link } from '@inertiajs/react';
import { useZodForm } from '@/hooks/useZodForm';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/Layouts/AdminLayout';

const schema = z.object({
    _method: z.string().optional(),
    title: z.string().min(1, 'The title field is required.'),
});

function ExpenseTypeEdit({ expenseType = {} }) {
    const { form, submit } = useZodForm(schema, {
        defaultValues: { title: expenseType.title ?? '', _method: 'PUT' },
    });
    const { register, formState: { errors, isSubmitting } } = form;

    return (
        <div className="space-y-6 p-6">
            <Card>
                <CardHeader>
                    <CardTitle>Edit Expense Type</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('expense-type.update', expenseType.id))}>
                        <div className="grid grid-cols-1 gap-4">
                            <div className="space-y-1.5">
                                <Label htmlFor="title">Title</Label>
                                <Input id="title" placeholder="Enter title" {...register('title')} />
                                {errors.title && <p className="text-sm text-destructive">{errors.title.message}</p>}
                            </div>
                        </div>

                        <div className="flex justify-end gap-2 mt-4">
                            <Button variant="ghost" type="button" asChild>
                                <Link href={route('expense-type.index')}>Close</Link>
                            </Button>
                            <Button type="submit" disabled={isSubmitting}>Update</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

ExpenseTypeEdit.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Expense Type', href: route('expense-type.index') },
        { label: 'Edit' },
    ]}>{page}</AdminLayout>
);
export default ExpenseTypeEdit;
