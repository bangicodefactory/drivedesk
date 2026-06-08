import { Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { AlertTriangle, RotateCcw, Wrench, CheckCircle2, ChevronRight } from 'lucide-react';
import { useTranslation } from '@/hooks/useTranslation';

// Items needing attention: overdue returns + urgent/overdue maintenance
// reminders. All sourced from HomeController::ownerDashboardExtras.
// Colours mirror the Reminders page status badges so a state reads the same
// everywhere: overdue=danger, urgent=warning, upcoming=info, completed=success.
const STATUS_VARIANT = {
    overdue:  'destructive',
    urgent:   'warning',
    upcoming: 'info',
    completed: 'success',
};

const TYPE_ICON = { return: RotateCcw, maintenance: Wrench };

export default function ImmediateActions({ actions = [] }) {
    const t = useTranslation();

    return (
        <Card className="h-full">
            <CardHeader>
                <CardTitle className="flex items-center gap-2 text-base">
                    <AlertTriangle className="h-4 w-4 text-muted-foreground" />
                    {t('Immediate Actions')}
                </CardTitle>
            </CardHeader>
            <CardContent className="space-y-2">
                {actions.length === 0 && (
                    <div className="flex flex-col items-center justify-center py-10 text-center text-sm text-muted-foreground">
                        <CheckCircle2 className="mb-2 h-8 w-8 text-emerald-500" />
                        {t('All clear — nothing needs attention')}
                    </div>
                )}
                {actions.map((a, i) => {
                    const Icon = TYPE_ICON[a.type] ?? AlertTriangle;
                    return (
                        <Link
                            key={i}
                            href={a.href}
                            className="flex items-center gap-3 rounded-lg border p-2.5 transition-colors hover:bg-accent"
                        >
                            <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-muted">
                                <Icon className="h-4 w-4 text-muted-foreground" />
                            </span>
                            <div className="min-w-0 flex-1">
                                <p className="truncate text-sm font-medium">{a.title}</p>
                                {a.subtitle && (
                                    <p className="truncate text-xs text-muted-foreground">{a.subtitle}</p>
                                )}
                            </div>
                            <Badge variant={STATUS_VARIANT[a.status] ?? 'secondary'}>{t(a.status)}</Badge>
                            <ChevronRight className="h-4 w-4 shrink-0 text-muted-foreground rtl:rotate-180" />
                        </Link>
                    );
                })}
            </CardContent>
        </Card>
    );
}
