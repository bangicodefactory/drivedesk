import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';

/**
 * KPI tile used on the dashboard.
 *
 * @param {Object}                props
 * @param {string}                props.title    — heading text
 * @param {number|string|null}    props.value    — number or pre-formatted string; null shows skeleton
 * @param {React.ComponentType}   [props.icon]   — optional lucide icon component
 * @param {string}                [props.subtitle]
 */
export default function StatCard({ title, value, icon: Icon, subtitle }) {
    return (
        <Card>
            <CardHeader className="flex flex-row items-center justify-between pb-2 space-y-0">
                <CardTitle className="text-sm font-medium text-muted-foreground">
                    {title}
                </CardTitle>
                {Icon && <Icon className="h-4 w-4 text-muted-foreground" />}
            </CardHeader>
            <CardContent>
                {value == null
                    ? <Skeleton className="h-8 w-24" />
                    : <p className="text-2xl font-bold">{value}</p>
                }
                {subtitle && (
                    <p className="text-xs text-muted-foreground mt-1">{subtitle}</p>
                )}
            </CardContent>
        </Card>
    );
}
