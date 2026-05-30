import { z } from 'zod';
import { useZodForm } from '@/hooks/useZodForm';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import AdminLayout from '@/Layouts/AdminLayout';

const schema = z.object({
    company_name:    z.string().min(1, 'Required'),
    company_email:   z.string().email('Enter a valid email'),
    company_phone:   z.string().min(1, 'Required'),
    company_address: z.string().min(1, 'Required'),
    patente:         z.string().optional(),
    rc:              z.string().optional(),
    if:              z.string().optional(),
    ice:             z.string().optional(),
    timezone:        z.string().optional(),
});

function Company({ settings, timezones }) {
    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            company_name:    settings?.company_name    ?? '',
            company_email:   settings?.company_email   ?? '',
            company_phone:   settings?.company_phone   ?? '',
            company_address: settings?.company_address ?? '',
            patente:         settings?.patente         ?? '',
            rc:              settings?.rc              ?? '',
            if:              settings?.if              ?? '',
            ice:             settings?.ice             ?? '',
            timezone:        settings?.timezone        ?? '',
        },
    });
    const { register, setValue, formState: { errors, isSubmitting } } = form;

    const tzList = timezones ? Object.entries(timezones) : [];

    return (
        <div className="max-w-2xl space-y-6 p-6">
            <div>
                <h1 className="text-2xl font-semibold">Company Settings</h1>
                <p className="text-sm text-muted-foreground">Company details and legal identifiers.</p>
            </div>

            <Card>
                <CardHeader><CardTitle>Company Info</CardTitle></CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('setting.company'))} className="space-y-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-1">
                                <Label htmlFor="company_name">Name</Label>
                                <Input id="company_name" {...register('company_name')} />
                                {errors.company_name && <p className="text-sm text-destructive">{errors.company_name.message}</p>}
                            </div>
                            <div className="space-y-1">
                                <Label htmlFor="company_email">Email</Label>
                                <Input id="company_email" type="email" {...register('company_email')} />
                                {errors.company_email && <p className="text-sm text-destructive">{errors.company_email.message}</p>}
                            </div>
                            <div className="space-y-1">
                                <Label htmlFor="company_phone">Phone</Label>
                                <Input id="company_phone" {...register('company_phone')} />
                                {errors.company_phone && <p className="text-sm text-destructive">{errors.company_phone.message}</p>}
                            </div>
                            <div className="space-y-1">
                                <Label htmlFor="company_address">Address</Label>
                                <Textarea id="company_address" rows={2} {...register('company_address')} />
                                {errors.company_address && <p className="text-sm text-destructive">{errors.company_address.message}</p>}
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-4 pt-2">
                            {[['patente', 'Patente'], ['rc', 'RC'], ['if', 'IF'], ['ice', 'ICE']].map(([key, label]) => (
                                <div key={key} className="space-y-1">
                                    <Label htmlFor={key}>{label}</Label>
                                    <Input id={key} {...register(key)} />
                                </div>
                            ))}
                        </div>

                        {tzList.length > 0 && (
                            <div className="space-y-1">
                                <Label>Timezone</Label>
                                <Select
                                    defaultValue={settings?.timezone ?? ''}
                                    onValueChange={(v) => setValue('timezone', v)}
                                >
                                    <SelectTrigger><SelectValue placeholder="Select timezone" /></SelectTrigger>
                                    <SelectContent>
                                        {tzList.map(([value, label]) => (
                                            <SelectItem key={value} value={value}>{label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        )}

                        <Button type="submit" disabled={isSubmitting}>
                            {isSubmitting ? 'Saving…' : 'Save'}
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

Company.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Settings' }, { label: 'Company' }]}>{page}</AdminLayout>
);
export default Company;
