import { useRef, useCallback } from 'react';
import ReactSignatureCanvas from 'react-signature-canvas';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

/**
 * React replacement for jq-signature (BAN-58).
 *
 * Produces a `data:image/png;base64,...` string — the exact format
 * SignatureController::store already accepts and validates.
 *
 * Use with react-hook-form Controller:
 *
 *   <Controller
 *     name="signature"
 *     control={form.control}
 *     render={({ field, fieldState }) => (
 *       <SignaturePad
 *         onChange={field.onChange}
 *         error={fieldState.error?.message}
 *       />
 *     )}
 *   />
 */
export default function SignaturePad({ onChange, error, disabled = false, className }) {
    const padRef = useRef(null);

    const handleEnd = useCallback(() => {
        if (padRef.current && !padRef.current.isEmpty()) {
            onChange?.(padRef.current.toDataURL('image/png'));
        }
    }, [onChange]);

    const handleClear = useCallback(() => {
        padRef.current?.clear();
        onChange?.(null);
    }, [onChange]);

    return (
        <div className={cn('space-y-2', className)}>
            <div
                className={cn(
                    'relative border rounded-md overflow-hidden bg-white',
                    error && 'border-destructive',
                    disabled && 'opacity-50 pointer-events-none',
                )}
                aria-label="Signature pad"
            >
                <ReactSignatureCanvas
                    ref={padRef}
                    penColor="black"
                    backgroundColor="white"
                    canvasProps={{
                        className: 'w-full h-40 touch-none',
                        'data-testid': 'signature-canvas',
                    }}
                    onEnd={handleEnd}
                />
            </div>

            <Button
                type="button"
                variant="outline"
                size="sm"
                onClick={handleClear}
                disabled={disabled}
            >
                Clear
            </Button>

            {error && (
                <p className="text-sm text-destructive" role="alert">
                    {error}
                </p>
            )}
        </div>
    );
}
