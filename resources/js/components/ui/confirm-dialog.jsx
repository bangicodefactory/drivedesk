import * as React from 'react';
import * as DialogPrimitive from '@radix-ui/react-dialog';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/useTranslation';

// Promise-based confirm dialog that replaces window.confirm app-wide.
// Mount <ConfirmProvider> once (in AdminLayout); call useConfirm() in any page:
//   const confirm = useConfirm();
//   if (await confirm({ title: t('Delete this driver?') })) router.delete(...)
const ConfirmContext = React.createContext(() => Promise.resolve(false));

export function useConfirm() {
    return React.useContext(ConfirmContext);
}

export function ConfirmProvider({ children }) {
    const t = useTranslation();
    const [state, setState] = React.useState(null);
    const resolverRef = React.useRef(null);

    const confirm = React.useCallback((opts = {}) => {
        return new Promise((resolve) => {
            resolverRef.current = resolve;
            setState(opts);
        });
    }, []);

    const settle = (value) => {
        resolverRef.current?.(value);
        resolverRef.current = null;
        setState(null);
    };

    const open = state !== null;
    const destructive = state?.destructive ?? true; // deletes are the common case

    return (
        <ConfirmContext.Provider value={confirm}>
            {children}
            <DialogPrimitive.Root open={open} onOpenChange={(o) => { if (!o) settle(false); }}>
                <DialogPrimitive.Portal>
                    <DialogPrimitive.Overlay
                        className="fixed inset-0 z-50 bg-black/60 backdrop-blur-[1px] data-[state=open]:animate-in data-[state=open]:fade-in-0"
                    />
                    <DialogPrimitive.Content
                        onOpenAutoFocus={(e) => e.preventDefault()}
                        className={cn(
                            'fixed left-1/2 top-1/2 z-50 w-full max-w-md -translate-x-1/2 -translate-y-1/2',
                            'rounded-xl border bg-background p-6 shadow-lg',
                            'data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95',
                        )}
                    >
                        <DialogPrimitive.Title className="text-lg font-semibold tracking-tight">
                            {state?.title ?? t('Are you sure?')}
                        </DialogPrimitive.Title>
                        {state?.description && (
                            <DialogPrimitive.Description className="mt-1.5 text-sm text-muted-foreground">
                                {state.description}
                            </DialogPrimitive.Description>
                        )}
                        <div className="mt-6 flex justify-end gap-2">
                            <Button variant="outline" onClick={() => settle(false)}>
                                {state?.cancelText ?? t('Cancel')}
                            </Button>
                            <Button
                                variant={destructive ? 'destructive' : 'default'}
                                onClick={() => settle(true)}
                                autoFocus
                            >
                                {state?.confirmText ?? t('Confirm')}
                            </Button>
                        </div>
                    </DialogPrimitive.Content>
                </DialogPrimitive.Portal>
            </DialogPrimitive.Root>
        </ConfirmContext.Provider>
    );
}
