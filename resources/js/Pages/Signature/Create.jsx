import { useEffect, useRef, useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import SignatureCanvas from 'react-signature-canvas';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { SearchableSelect } from '@/components/ui/searchable-select';
import { PenLine, Eraser, Save } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';

function SignatureCreate({ drivers }) {
    const t = useTranslation();
    const { errors: serverErrors } = usePage().props;
    const sigCanvasRef = useRef(null);
    const [userId, setUserId] = useState('');
    const [isEmpty, setIsEmpty] = useState(true);
    const [submitting, setSubmitting] = useState(false);

    // The canvas is sized by CSS (responsive), but its drawing buffer must match
    // the rendered size × devicePixelRatio or strokes blur and the pen drifts.
    // Sync the buffer on mount and whenever the box resizes (window / sidebar).
    useEffect(() => {
        const canvas = sigCanvasRef.current?.getCanvas();
        if (!canvas) return;

        const sync = () => {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const w = Math.round(canvas.offsetWidth * ratio);
            const h = Math.round(canvas.offsetHeight * ratio);
            if (!w || !h || (canvas.width === w && canvas.height === h)) return;
            canvas.width = w;
            canvas.height = h;
            canvas.getContext('2d').scale(ratio, ratio); // setting width/height resets the transform
            sigCanvasRef.current?.clear();
            setIsEmpty(true);
        };

        sync();
        const ro = new ResizeObserver(sync);
        ro.observe(canvas);
        return () => ro.disconnect();
    }, []);

    function clear() {
        sigCanvasRef.current?.clear();
        setIsEmpty(true);
    }

    function handleEnd() {
        setIsEmpty(sigCanvasRef.current?.isEmpty() ?? true);
    }

    function submit(e) {
        e.preventDefault();
        if (!userId) return;
        if (sigCanvasRef.current?.isEmpty()) {
            alert(t('Please provide a signature before submitting.'));
            return;
        }
        setSubmitting(true);
        const signatureData = sigCanvasRef.current.toDataURL('image/png');
        router.post(route('signature.store'), { user_id: userId, signature: signatureData }, {
            onFinish: () => setSubmitting(false),
        });
    }

    return (
        <div className="p-6 max-w-3xl mx-auto space-y-6">
            <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
                <PenLine className="h-6 w-6" /> {t('Create Signature')}
            </h1>

            <Card>
                <CardHeader>
                    <CardTitle>{t('Signature Pad')}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={submit} className="space-y-5">

                        <div className="space-y-1">
                            <Label>{t('Select Client')}</Label>
                            <SearchableSelect
                                options={drivers.map((d) => ({ value: String(d.id), label: d.name }))}
                                value={userId}
                                onChange={setUserId}
                                placeholder={t('Select a driver/client')}
                                searchPlaceholder={t('Search driver…')}
                                ariaLabel={t('Select Client')}
                            />
                            {serverErrors?.user_id && (
                                <p className="text-sm text-destructive">{serverErrors.user_id}</p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <div className="flex items-center justify-between">
                                <Label>{t('Signature')}</Label>
                                <span className="text-xs text-muted-foreground">{t('Draw inside the box below')}</span>
                            </div>
                            <div className="rounded-lg border-2 border-dashed bg-white touch-none overflow-hidden">
                                <SignatureCanvas
                                    ref={sigCanvasRef}
                                    penColor="black"
                                    backgroundColor="white"
                                    canvasProps={{
                                        className: 'block w-full h-72 sm:h-96 cursor-crosshair',
                                    }}
                                    onEnd={handleEnd}
                                />
                            </div>
                            {serverErrors?.signature && (
                                <p className="text-sm text-destructive">{serverErrors.signature}</p>
                            )}
                        </div>

                        <div className="flex gap-2">
                            <Button type="button" variant="outline" onClick={clear}>
                                <Eraser className="mr-2 h-4 w-4" /> {t('Clear')}
                            </Button>
                            <Button type="submit" disabled={submitting || isEmpty || !userId}>
                                <Save className="mr-2 h-4 w-4" /> {t('Save Signature')}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}

SignatureCreate.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Signatures', href: route('signature.index') }, { label: 'Create' }]}>{page}</AdminLayout>
);
export default SignatureCreate;
