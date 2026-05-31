import { useRef, useState } from 'react';
import { router } from '@inertiajs/react';
import SignatureCanvas from 'react-signature-canvas';
import { z } from 'zod';
import { useZodForm } from '@/hooks/useZodForm';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Eraser, Save } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

const schema = z.object({
    application_name: z.string().min(1, 'Application name is required'),
    logo:             z.any().optional(),
    favicon:          z.any().optional(),
    image_home_1:     z.any().optional(),
    image_home_2:     z.any().optional(),
    landing_logo:     z.any().optional(),
});

function General({ settings, loginUser }) {
    const isSuperAdmin = loginUser?.type === 'super admin';

    const { form, submit } = useZodForm(schema, {
        defaultValues: { application_name: settings?.app_name ?? '' },
    });
    const { register, setValue, formState: { errors, isSubmitting } } = form;

    // Admin signature pad
    const sigRef = useRef(null);
    const [sigEmpty, setSigEmpty] = useState(true);
    const [savingSig, setSavingSig] = useState(false);

    function saveSig() {
        if (sigRef.current?.isEmpty()) return;
        setSavingSig(true);
        sigRef.current.getCanvas().toBlob((blob) => {
            if (!blob) { setSavingSig(false); return; }
            // Convert Blob → File so Inertia sends it as a named multipart file.
            // The controller validates 'required|image|mimes:png' and uses storeAs().
            const file = new File([blob], 'signature.png', { type: 'image/png' });
            router.post(route('AdminSignature.store'), { signature: file }, {
                forceFormData: true,
                onFinish: () => setSavingSig(false),
            });
        }, 'image/png');
    }

    return (
        <div className="max-w-2xl space-y-6 p-6">
            <div>
                <h1 className="text-2xl font-semibold">General Settings</h1>
                <p className="text-sm text-muted-foreground">Branding, logos and home images.</p>
            </div>

            <Card>
                <CardHeader><CardTitle>Application</CardTitle></CardHeader>
                <CardContent>
                    <form onSubmit={submit('post', route('setting.general'), { forceFormData: true })} className="space-y-4">
                        <div className="space-y-1">
                            <Label htmlFor="application_name">Application Name</Label>
                            <Input id="application_name" {...register('application_name')} />
                            {errors.application_name && <p className="text-sm text-destructive">{errors.application_name.message}</p>}
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-1">
                                <Label htmlFor="logo">Logo (.png)</Label>
                                <Input id="logo" type="file" accept=".png" onChange={(e) => setValue('logo', e.target.files?.[0] ?? null)} />
                            </div>
                            <div className="space-y-1">
                                <Label htmlFor="favicon">Favicon (.png)</Label>
                                <Input id="favicon" type="file" accept=".png" onChange={(e) => setValue('favicon', e.target.files?.[0] ?? null)} />
                            </div>
                            <div className="space-y-1">
                                <Label htmlFor="image_home_1">Première image accueil</Label>
                                <Input id="image_home_1" type="file" accept=".png" onChange={(e) => setValue('image_home_1', e.target.files?.[0] ?? null)} />
                            </div>
                            <div className="space-y-1">
                                <Label htmlFor="image_home_2">Deuxième image accueil</Label>
                                <Input id="image_home_2" type="file" accept=".png" onChange={(e) => setValue('image_home_2', e.target.files?.[0] ?? null)} />
                            </div>
                            {isSuperAdmin && (
                                <div className="space-y-1 col-span-2">
                                    <Label htmlFor="landing_logo">Landing Page Logo (.png)</Label>
                                    <Input id="landing_logo" type="file" accept=".png" onChange={(e) => setValue('landing_logo', e.target.files?.[0] ?? null)} />
                                </div>
                            )}
                        </div>
                        <Button type="submit" disabled={isSubmitting}>
                            {isSubmitting ? 'Saving…' : 'Save'}
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Admin Signature</CardTitle>
                </CardHeader>
                <CardContent className="space-y-3">
                    {settings?.admin_signature && (
                        <div className="space-y-1">
                            <p className="text-sm text-muted-foreground">Current signature:</p>
                            <img
                                src={`/storage/${settings.admin_signature}`}
                                alt="Admin signature"
                                className="max-h-24 border rounded"
                            />
                        </div>
                    )}
                    <div className="border rounded overflow-hidden bg-white touch-none">
                        <SignatureCanvas
                            ref={sigRef}
                            penColor="black"
                            backgroundColor="white"
                            canvasProps={{ className: 'w-full', style: { width: '100%', height: 150 } }}
                            onEnd={() => setSigEmpty(sigRef.current?.isEmpty() ?? true)}
                        />
                    </div>
                    <div className="flex gap-2">
                        <Button type="button" variant="outline" size="sm" onClick={() => { sigRef.current?.clear(); setSigEmpty(true); }}>
                            <Eraser className="mr-2 h-4 w-4" /> Clear
                        </Button>
                        <Button type="button" size="sm" disabled={sigEmpty || savingSig} onClick={saveSig}>
                            <Save className="mr-2 h-4 w-4" /> {savingSig ? 'Saving…' : 'Save Signature'}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}

General.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Settings' }, { label: 'General' }]}>{page}</AdminLayout>
);
export default General;
