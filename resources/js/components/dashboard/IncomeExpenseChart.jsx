import {
    AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer,
} from 'recharts';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useTranslation } from '@/hooks/useTranslation';

// Compact for axis ticks (12k), grouped full numbers for the tooltip.
const fmtCompact = (n) =>
    new Intl.NumberFormat(undefined, { notation: 'compact', maximumFractionDigits: 1 }).format(n ?? 0);
const fmtFull = (n) =>
    new Intl.NumberFormat(undefined, { maximumFractionDigits: 0 }).format(n ?? 0);

function ChartTooltip({ active, payload, label, t }) {
    if (!active || !payload?.length) return null;
    return (
        <div className="rounded-lg border bg-popover px-3 py-2 text-popover-foreground shadow-md">
            <p className="mb-1.5 text-xs font-medium text-muted-foreground">{label}</p>
            <div className="space-y-1">
                {payload.map((p) => (
                    <div key={p.dataKey} className="flex items-center gap-2 text-sm">
                        <span className="h-2 w-2 shrink-0 rounded-full" style={{ backgroundColor: p.color }} />
                        <span className="flex-1 text-muted-foreground">{t(p.name)}</span>
                        <span className="font-semibold tabular-nums">{fmtFull(p.value)}</span>
                    </div>
                ))}
            </div>
        </div>
    );
}

/**
 * Income vs Expense — twin gradient area chart (income = success/green,
 * expense = danger/red). Replaces the ApexCharts widget at #incomeExpense in
 * resources/views/dashboard/index.blade.php.
 *
 * @param {{ label: string[], income: number[], expense: number[] } | null} props.data
 */
export default function IncomeExpenseChart({ data }) {
    const t = useTranslation();
    if (!data?.label?.length) return null;

    // Recharts wants an array of objects, not parallel arrays.
    const rows = data.label.map((month, i) => ({
        month,
        income:  data.income?.[i]  ?? 0,
        expense: data.expense?.[i] ?? 0,
    }));

    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-base">{t('Income vs Expense')}</CardTitle>
            </CardHeader>
            <CardContent>
                <div className="h-72 w-full">
                    <ResponsiveContainer width="100%" height="100%">
                        <AreaChart data={rows} margin={{ top: 8, right: 12, left: -8, bottom: 0 }}>
                            <defs>
                                <linearGradient id="fillIncome" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="5%" stopColor="hsl(var(--success))" stopOpacity={0.35} />
                                    <stop offset="95%" stopColor="hsl(var(--success))" stopOpacity={0} />
                                </linearGradient>
                                <linearGradient id="fillExpense" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="5%" stopColor="hsl(var(--destructive))" stopOpacity={0.28} />
                                    <stop offset="95%" stopColor="hsl(var(--destructive))" stopOpacity={0} />
                                </linearGradient>
                            </defs>
                            <CartesianGrid vertical={false} strokeDasharray="3 3" className="stroke-border/60" />
                            <XAxis
                                dataKey="month"
                                tickLine={false}
                                axisLine={false}
                                tickMargin={8}
                                className="text-xs"
                            />
                            <YAxis
                                tickLine={false}
                                axisLine={false}
                                width={44}
                                tickMargin={4}
                                tickFormatter={fmtCompact}
                                className="text-xs"
                            />
                            <Tooltip
                                cursor={{ stroke: 'hsl(var(--border))', strokeWidth: 1 }}
                                content={(props) => <ChartTooltip {...props} t={t} />}
                            />
                            <Area
                                type="monotone" dataKey="income" name="Income"
                                stroke="hsl(var(--success))" strokeWidth={2}
                                fill="url(#fillIncome)" activeDot={{ r: 4, strokeWidth: 0 }}
                            />
                            <Area
                                type="monotone" dataKey="expense" name="Expense"
                                stroke="hsl(var(--destructive))" strokeWidth={2}
                                fill="url(#fillExpense)" activeDot={{ r: 4, strokeWidth: 0 }}
                            />
                        </AreaChart>
                    </ResponsiveContainer>
                </div>

                {/* Legend */}
                <div className="mt-3 flex items-center justify-center gap-6 text-sm">
                    <span className="flex items-center gap-2">
                        <span className="h-2.5 w-2.5 rounded-full bg-success" />
                        <span className="text-muted-foreground">{t('Income')}</span>
                    </span>
                    <span className="flex items-center gap-2">
                        <span className="h-2.5 w-2.5 rounded-full bg-destructive" />
                        <span className="text-muted-foreground">{t('Expense')}</span>
                    </span>
                </div>
            </CardContent>
        </Card>
    );
}
