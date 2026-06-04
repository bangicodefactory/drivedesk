import { z } from 'zod';
import { Link } from '@inertiajs/react';
import { useZodForm } from '@/hooks/useZodForm';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/Layouts/AdminLayout';

// Port of resources/views/place/edit.blade.php.
// Submits PUT to route('place.update') via a spoofed _method=PUT (matches the
// Blade @method('PUT')). Field names match the Blade form 1:1 (name, city,
// island, price, depo_name, depo_address). The zod schema mirrors the
// controller's update() `required` rules (name|city|island|price) for UX only;
// Laravel validation stays authoritative and its errors surface via setError.
const schema = z.object({
    _method: z.string().optional(),
    name: z.string().min(1, 'The name field is required.'),
    city: z.string().min(1, 'The city field is required.'),
    island: z.string().min(1, 'The island field is required.'),
    price: z.string().min(1, 'The price field is required.'),
    depo_name: z.string().optional(),
    depo_address: z.string().optional(),
});

const str = (v) => (v != null ? String(v) : '');

function PlaceEdit({ place = {} }) {
    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            name: place.name ?? '',
            city: place.city ?? '',
            island: place.island ?? '',
            price: str(place.price),
            depo_name: place.depo_name ?? '',
            depo_address: place.depo_address ?? '',
            _method: 'PUT',
        },
    });
    const { register, formState: { errors, isSubmitting } } = form;

    return (
        <div className="space-y-6 p-6">
            <Card>
                <CardHeader>
                    <CardTitle>Edit Place</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('place.update', place.id))}>
                        <div className="grid grid-cols-1 gap-4">
                            <div className="space-y-1.5">
                                <Label htmlFor="name">Name</Label>
                                <Input id="name" placeholder="Enter place name" {...register('name')} />
                                {errors.name && <p className="text-sm text-destructive">{errors.name.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="city">City</Label>
                                <Input id="city" placeholder="Enter city" {...register('city')} />
                                {errors.city && <p className="text-sm text-destructive">{errors.city.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="island">Island</Label>
                                <Input id="island" placeholder="Enter island" {...register('island')} />
                                {errors.island && <p className="text-sm text-destructive">{errors.island.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="price">Price</Label>
                                <Input id="price" type="number" placeholder="Enter price" {...register('price')} />
                                {errors.price && <p className="text-sm text-destructive">{errors.price.message}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="depo_name">Depo name</Label>
                                <Input id="depo_name" placeholder="Enter depo name" {...register('depo_name')} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="depo_address">Depo address</Label>
                                <Input id="depo_address" placeholder="Enter depo address" {...register('depo_address')} />
                            </div>
                        </div>

                        <div className="flex justify-end gap-2 mt-4">
                            <Button variant="ghost" type="button" asChild>
                                <Link href={route('place.index')}>Close</Link>
                            </Button>
                            <Button type="submit" disabled={isSubmitting}>Update</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

PlaceEdit.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'Place', href: route('place.index') },
        { label: 'Edit' },
    ]}>{page}</AdminLayout>
);
export default PlaceEdit;
