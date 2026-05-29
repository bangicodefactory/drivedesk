import {
    ComposedChart, Bar, Area, XAxis, YAxis, CartesianGrid,
    Tooltip, Legend, ResponsiveContainer,
} from 'recharts';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

/**
 * Income vs Expense composed chart (column + area).
 * Replaces the ApexCharts widget at #incomeExpense in resources/views/dashboard/index.blade.php.
 *
 * @param {Object} props
 * @param {{ label: string[], income: number[], expense: number[] } | null} props.data
 */
export default function IncomeExpenseChart({ data }) {
    if (!data?.label?.length) return null;

    // Recharts wants an array of objects, not parallel arrays
    const rows = data.label.map((month, i) => ({
        month,
        income:  data.income?.[i]  ?? 0,
        expense: data.expense?.[i] ?? 0,
    }));

    return (
        <Card>
            <CardHeader>
                <CardTitle>Income vs Expense</CardTitle>
            </CardHeader>
            <CardContent>
                <div className="h-80 w-full">
                    <ResponsiveContainer width="100%" height="100%">
                        <ComposedChart data={rows} margin={{ top: 10, right: 16, left: 0, bottom: 0 }}>
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
                            <Legend />
                            <Bar  dataKey="income"  name="Income"  fill="hsl(var(--primary))" radius={[4, 4, 0, 0]} />
                            <Area dataKey="expense" name="Expense" stroke="hsl(var(--destructive))" fill="hsl(var(--destructive))" fillOpacity={0.15} />
                        </ComposedChart>
                    </ResponsiveContainer>
                </div>
            </CardContent>
        </Card>
    );
}
