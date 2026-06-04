import { z } from 'zod';
import { Link } from '@inertiajs/react';
import { useZodForm } from '@/hooks/useZodForm';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';

const schema = z.object({
    title: z.string().min(1, 'The title field is required.'),
});

function ExpenseTypeCreate() {
    const t = useTranslation();
    const { form, submit } = useZodForm(schema, {
        defaultValues: { title: '' },
    });
    const { register, formState: { errors, isSubmitting } } = form;

    return (
        <div className="space-y-6 p-6">
            <Card>
                <CardHeader>
                    <CardTitle>{t('Create Expense Type')}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('expense-type.store'))}>
                        <div className="grid grid-cols-1 gap-4">
                            <div className="space-y-1.5">
                                <Label htmlFor="title">{t('Title')}</Label>
                                <Input id="title" placeholder={t('Enter title')} {...register('title')} />
                                {errors.title && <p className="text-sm text-destructive">{errors.title.message}</p>}
                            </div>
                        </div>

                        <div className="flex justify-end gap-2 mt-4">
                            <Button variant="ghost" type="button" asChild>
                                <Link href={route('expense-type.index')}>{t('Close')}</Link>
                            </Button>
                            <Button type="submit" disabled={isSubmitting}>{t('Create')}</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

ExpenseTypeCreate.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Expense Type', href: route('expense-type.index') },
        { label: 'Create' },
    ]}>{page}</AdminLayout>
);
export default ExpenseTypeCreate;
