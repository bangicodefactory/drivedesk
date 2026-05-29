import {
    BarChart, Bar, XAxis, YAxis, CartesianGrid,
    Tooltip, ResponsiveContainer,
} from 'recharts';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

/**
 * Simple monthly bar chart — used for organizationByMonth + paymentByMonth on
 * the super-admin dashboard. Replaces the two ApexCharts widgets in
 * resources/views/dashboard/super_admin.blade.php.
 *
 * @param {Object} props
 * @param {string} props.title
 * @param {{ label: string[], data: number[] } | null} props.data
 * @param {string} [props.dataKeyName='Value']
 */
export default function MonthlyBarChart({ title, data, dataKeyName = 'Value' }) {
    if (!data?.label?.length) return null;

    const rows = data.label.map((month, i) => ({
        month,
        value: data.data?.[i] ?? 0,
    }));

    return (
        <Card>
            <CardHeader>
                <CardTitle>{title}</CardTitle>
            </CardHeader>
            <CardContent>
                <div className="h-72 w-full">
                    <ResponsiveContainer width="100%" height="100%">
                        <BarChart data={rows} margin={{ top: 10, right: 16, left: 0, bottom: 0 }}>
                            <CartesianGrid strokeDasharray="3 3" className="stroke-border" />
                            <XAxis dataKey="month" className="text-xs" />
                            <YAxis className="text-xs" />
                            <Tooltip
                                contentStyle={{
                                    backgroundColor: 'hsl(var(--popover))',
                                    border: '1px solid hsl(var(--border))',
                                    borderRadius: 'var(--radius)',
                                    color: 'hsl(var(--popover-foreground))',
                                }}
                            />
                            <Bar dataKey="value" name={dataKeyName} fill="hsl(var(--primary))" radius={[4, 4, 0, 0]} />
                        </BarChart>
                    </ResponsiveContainer>
                </div>
            </CardContent>
        </Card>
    );
}
