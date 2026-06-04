import { z } from 'zod';
import { useZodForm } from '@/hooks/useZodForm';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';

const schema = z.object({
    meta_seo_title:       z.string().min(1, 'Required'),
    meta_seo_keyword:     z.string().min(1, 'Required'),
    meta_seo_description: z.string().min(1, 'Required'),
    meta_seo_image:       z.any().optional(),
});

function SiteSeo({ settings }) {
    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            meta_seo_title:       settings?.meta_seo_title       ?? '',
            meta_seo_keyword:     settings?.meta_seo_keyword     ?? '',
            meta_seo_description: settings?.meta_seo_description ?? '',
        },
    });
    const { register, setValue, formState: { errors, isSubmitting } } = form;
    const t = useTranslation();

    return (
        <div className="space-y-6 p-6">
            <div>
                <h1 className="text-2xl font-semibold">{t('Site SEO Settings')}</h1>
                <p className="text-sm text-muted-foreground">{t('Meta tags for search engine optimisation.')}</p>
            </div>

            <form
                onSubmit={submit('post', route('setting.site.seo'), { forceFormData: true })}
                encType="multipart/form-data"
            >
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_2fr]">

                    {/* Left: meta image */}
                    <Card>
                        <CardHeader><CardTitle>{t('Meta Image')}</CardTitle></CardHeader>
                        <CardContent className="space-y-3">
                            <div className="space-y-1.5">
                                <Label htmlFor="meta_seo_image">{t('Meta Image')}</Label>
                                <Input id="meta_seo_image" type="file" accept="image/*" onChange={(e) => setValue('meta_seo_image', e.target.files?.[0] ?? null)} />
                            </div>
                            {settings?.meta_seo_image && (
                                <img
                                    src={`/storage/upload/seo/${settings.meta_seo_image}`}
                                    alt="Current SEO meta image"
                                    className="max-h-24 rounded border"
                                />
                            )}
                        </CardContent>
                    </Card>

                    {/* Right: meta text fields */}
                    <Card>
                        <CardHeader><CardTitle>{t('SEO Meta Tags')}</CardTitle></CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-1.5">
                                <Label htmlFor="meta_seo_title">{t('Meta Title')}</Label>
                                <Input id="meta_seo_title" placeholder={t('Enter meta SEO title')} {...register('meta_seo_title')} />
                                {errors.meta_seo_title && <p className="text-sm text-destructive">{errors.meta_seo_title.message}</p>}
                            </div>
                            <div className="space-y-1.5">
                                <Label htmlFor="meta_seo_keyword">{t('Meta Keyword')}</Label>
                                <Input id="meta_seo_keyword" placeholder={t('Enter meta SEO keyword')} {...register('meta_seo_keyword')} />
                                {errors.meta_seo_keyword && <p className="text-sm text-destructive">{errors.meta_seo_keyword.message}</p>}
                            </div>
                            <div className="space-y-1.5">
                                <Label htmlFor="meta_seo_description">{t('Meta Description')}</Label>
                                <Textarea id="meta_seo_description" rows={3} placeholder={t('Enter meta SEO description')} {...register('meta_seo_description')} />
                                {errors.meta_seo_description && <p className="text-sm text-destructive">{errors.meta_seo_description.message}</p>}
                            </div>
                            <div className="flex justify-end">
                                <Button type="submit" disabled={isSubmitting}>
                                    {isSubmitting ? t('Saving…') : t('Save')}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                </div>
            </form>
        </div>
    );
}

SiteSeo.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Settings' }, { label: 'Site SEO Settings' }]}>{page}</AdminLayout>
);
export default SiteSeo;
