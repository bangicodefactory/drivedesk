import { router } from '@inertiajs/react';
import {
    LineChart, Line, BarChart, Bar, PieChart, Pie, Cell,
    XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer,
} from 'recharts';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { Label } from '@/components/ui/label';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { FileText, Percent, Coins, TrendingUp, Car, Calendar, BarChart2, Trophy } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from '@/hooks/useTranslation';

// Chart series colors come from the theme's chart tokens so they follow the
// active palette and dark mode (recharts needs concrete color values, so we
// pass the CSS variables through).
const CHART_COLORS = [
    'hsl(var(--chart-1))',
    'hsl(var(--chart-2))',
    'hsl(var(--chart-3))',
    'hsl(var(--chart-4))',
    'hsl(var(--chart-5))',
];

const STAT_ICON_COLOR = {
    primary: 'text-primary',
    success: 'text-success',
    info:    'text-info',
    warning: 'text-warning',
    danger:  'text-destructive',
};

const fmt = (n, decimals = 2) =>
    n !== null && n !== undefined ? Number(n).toLocaleString(undefined, { minimumFractionDigits: decimals, maximumFractionDigits: decimals }) : '0.00';

function StatCard({ icon: Icon, color, label, value }) {
    return (
        <Card>
            <CardContent className="pt-4">
                <div className="flex items-center justify-between">
                    <div>
                        <p className="text-xs font-bold uppercase text-muted-foreground tracking-wide mb-1">{label}</p>
                        <p className="text-xl font-bold">{value}</p>
                    </div>
                    <Icon className={`h-8 w-8 ${STAT_ICON_COLOR[color] ?? 'text-muted-foreground'}`} />
                </div>
            </CardContent>
        </Card>
    );
}

function TvaReport({
    monthlyStats,
    yearlyStats,
    topClients,
    chartData,
    selectedYear,
    availableYears,
    topRentedCars,
    topProfitableCars,
    carPerformanceStats,
}) {
    const t = useTranslation();
    function changeYear(year) {
        router.get(route('tva.report'), { year }, { preserveScroll: true });
    }

    // Build recharts-compatible data arrays
    const lineData = (chartData.months ?? []).map((month, i) => ({
        name: month,
        'TVA Amount': chartData.tva_amounts?.[i] ?? 0,
        'Total TTC':  chartData.ttc_amounts?.[i]  ?? 0,
    }));

    const topClientsData = (topClients ?? []).map((c) => ({
        name:      c.client_name,
        total_tva: c.total_tva,
    }));

    const topRentedData = (topRentedCars ?? []).map((c) => ({
        name:  c.car_name?.length > 20 ? c.car_name.slice(0, 20) + '…' : c.car_name,
        count: c.rental_count,
    }));

    const topProfitableData = (topProfitableCars ?? []).map((c) => ({
        name:    c.car_name?.length > 20 ? c.car_name.slice(0, 20) + '…' : c.car_name,
        revenue: c.total_revenue_ht,
    }));

    return (
        <div className="p-6 space-y-6">
            <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
                <FileText className="h-6 w-6" /> {t('TVA Report')}
            </h1>

            {/* Year filter */}
            <Card>
                <CardContent className="pt-4">
                    <div className="w-48 space-y-1">
                        <Label>{t('Select Year')}</Label>
                        <Select value={String(selectedYear)} onValueChange={changeYear}>
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                {(availableYears ?? []).map((y) => (
                                    <SelectItem key={y} value={String(y)}>{y}</SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                </CardContent>
            </Card>

            {/* Yearly stats cards */}
            <div className="grid grid-cols-2 gap-4 xl:grid-cols-4">
                <StatCard icon={FileText}   color="primary" label={t('Total Invoices')}           value={fmt(yearlyStats.total_invoices, 0)} />
                <StatCard icon={Percent}    color="success" label={t('Total TVA')}                value={`${fmt(yearlyStats.total_tva_amount)} MAD`} />
                <StatCard icon={Coins}      color="info"    label={t('Total Revenue (TTC)')}      value={`${fmt(yearlyStats.total_ttc_amount)} MAD`} />
                <StatCard icon={TrendingUp} color="warning" label={t('Avg Invoice Value (HT)')}   value={`${fmt(yearlyStats.average_invoice_value)} MAD`} />
            </div>

            {/* Charts row 1 — monthly TVA line + top clients doughnut */}
            <div className="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <Card className="xl:col-span-2">
                    <CardHeader>
                        <CardTitle className="text-sm">{t('Monthly TVA Overview')} — {selectedYear}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ResponsiveContainer width="100%" height={320}>
                            <LineChart data={lineData} margin={{ top: 5, right: 20, left: 10, bottom: 5 }}>
                                <CartesianGrid strokeDasharray="3 3" className="stroke-border" />
                                <XAxis dataKey="name" tick={{ fontSize: 11 }} />
                                <YAxis tickFormatter={(v) => v.toLocaleString()} tick={{ fontSize: 11 }} />
                                <Tooltip formatter={(v) => `${v.toLocaleString()} MAD`} />
                                <Legend />
                                <Line type="monotone" dataKey="TVA Amount" stroke="hsl(var(--chart-1))" strokeWidth={2} dot={false} />
                                <Line type="monotone" dataKey="Total TTC"  stroke="hsl(var(--chart-2))" strokeWidth={2} dot={false} />
                            </LineChart>
                        </ResponsiveContainer>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-sm">{t('Top 5 Clients by TVA')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ResponsiveContainer width="100%" height={260}>
                            <PieChart>
                                <Pie
                                    data={topClientsData}
                                    dataKey="total_tva"
                                    nameKey="name"
                                    innerRadius={55}
                                    outerRadius={95}
                                >
                                    {topClientsData.map((_, i) => (
                                        <Cell key={i} fill={CHART_COLORS[i % 5]} />
                                    ))}
                                </Pie>
                                <Tooltip formatter={(v) => `${v.toLocaleString()} MAD`} />
                            </PieChart>
                        </ResponsiveContainer>
                        <div className="flex flex-wrap gap-2 justify-center mt-2">
                            {topClientsData.map((c, i) => (
                                <span key={i} className="text-xs flex items-center gap-1">
                                    <span className="inline-block h-2 w-2 rounded-full" style={{ background: CHART_COLORS[i % 5] }} />
                                    {c.name?.length > 15 ? c.name.slice(0, 15) + '…' : c.name}
                                </span>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Car stats cards */}
            <div className="grid grid-cols-2 gap-4 xl:grid-cols-4">
                <StatCard icon={Car}       color="success" label={t('Total Unique Cars')}     value={fmt(carPerformanceStats.total_unique_cars, 0)} />
                <StatCard icon={Calendar}  color="info"    label={t('Total Rental Days')}     value={`${fmt(carPerformanceStats.total_rental_days, 0)} ${t('Days')}`} />
                <StatCard icon={BarChart2} color="warning" label={t('Avg Rentals per Car')}   value={fmt(carPerformanceStats.average_rentals_per_car, 1)} />
                <StatCard icon={Trophy}    color="danger"  label={t('Most Rented Car')}
                    value={
                        carPerformanceStats.most_rented_car?.car_name
                            ? carPerformanceStats.most_rented_car.car_name.slice(0, 20)
                            : 'N/A'
                    }
                />
            </div>

            {/* Charts row 2 — top rented (bar) + top profitable (doughnut) */}
            <div className="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle className="text-sm">{t('Top 5 Most Rented Cars')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ResponsiveContainer width="100%" height={280}>
                            <BarChart data={topRentedData} margin={{ top: 5, right: 10, left: 10, bottom: 40 }}>
                                <CartesianGrid strokeDasharray="3 3" className="stroke-border" />
                                <XAxis dataKey="name" tick={{ fontSize: 10 }} angle={-30} textAnchor="end" />
                                <YAxis allowDecimals={false} tick={{ fontSize: 11 }} />
                                <Tooltip />
                                <Bar dataKey="count" name="Rental Count" radius={[4, 4, 0, 0]}>
                                    {topRentedData.map((_, i) => (
                                        <Cell key={i} fill={CHART_COLORS[i % 5]} />
                                    ))}
                                </Bar>
                            </BarChart>
                        </ResponsiveContainer>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-sm">{t('Top 5 Most Profitable Cars (HT)')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ResponsiveContainer width="100%" height={240}>
                            <PieChart>
                                <Pie
                                    data={topProfitableData}
                                    dataKey="revenue"
                                    nameKey="name"
                                    innerRadius={55}
                                    outerRadius={95}
                                >
                                    {topProfitableData.map((_, i) => (
                                        <Cell key={i} fill={CHART_COLORS[i % 5]} />
                                    ))}
                                </Pie>
                                <Tooltip formatter={(v) => `${v.toLocaleString()} MAD (HT)`} />
                            </PieChart>
                        </ResponsiveContainer>
                        <div className="flex flex-wrap gap-2 justify-center mt-2">
                            {topProfitableData.map((c, i) => (
                                <span key={i} className="text-xs flex items-center gap-1">
                                    <span className="inline-block h-2 w-2 rounded-full" style={{ background: CHART_COLORS[i % 5] }} />
                                    {c.name}
                                </span>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Monthly stats table */}
            <Card>
                <CardHeader>
                    <CardTitle className="text-sm">{t('Monthly Statistics')} — {selectedYear}</CardTitle>
                </CardHeader>
                <CardContent className="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Month')}</TableHead>
                                <TableHead className="text-end">{t('Invoices')}</TableHead>
                                <TableHead className="text-end">{t('Total HT')}</TableHead>
                                <TableHead className="text-end">{t('Total TVA')}</TableHead>
                                <TableHead className="text-end">{t('Total TTC')}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {(monthlyStats ?? []).map((row) => (
                                <TableRow key={row.month_name}>
                                    <TableCell>{row.month_name}</TableCell>
                                    <TableCell className="text-end">{row.count}</TableCell>
                                    <TableCell className="text-end">{fmt(row.ht_amount)} MAD</TableCell>
                                    <TableCell className="text-end">{fmt(row.tva_amount)} MAD</TableCell>
                                    <TableCell className="text-end">{fmt(row.ttc_amount)} MAD</TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                        <tfoot>
                            <tr className="border-t-2 font-bold bg-muted/50">
                                <td className="px-4 py-2">{t('Total')}</td>
                                <td className="px-4 py-2 text-end">{fmt(yearlyStats.total_invoices, 0)}</td>
                                <td className="px-4 py-2 text-end">{fmt(yearlyStats.total_ht_amount)} MAD</td>
                                <td className="px-4 py-2 text-end">{fmt(yearlyStats.total_tva_amount)} MAD</td>
                                <td className="px-4 py-2 text-end">{fmt(yearlyStats.total_ttc_amount)} MAD</td>
                            </tr>
                        </tfoot>
                    </Table>
                </CardContent>
            </Card>

            {/* Top clients table */}
            <Card>
                <CardHeader>
                    <CardTitle className="text-sm">{t('Top Clients by TVA Amount')} — {selectedYear}</CardTitle>
                </CardHeader>
                <CardContent className="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Rank')}</TableHead>
                                <TableHead>{t('Client Name')}</TableHead>
                                <TableHead className="text-end">{t('Invoices')}</TableHead>
                                <TableHead className="text-end">{t('Total TVA')}</TableHead>
                                <TableHead className="text-end">{t('Total TTC')}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {(topClients ?? []).map((c, i) => (
                                <TableRow key={i}>
                                    <TableCell>{i + 1}</TableCell>
                                    <TableCell>{c.client_name}</TableCell>
                                    <TableCell className="text-end">{c.count}</TableCell>
                                    <TableCell className="text-end">{fmt(c.total_tva)} MAD</TableCell>
                                    <TableCell className="text-end">{fmt(c.total_ttc)} MAD</TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            {/* Car tables row */}
            <div className="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle className="text-sm">{t('Top 5 Most Rented Cars')} — {selectedYear}</CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('Rank')}</TableHead>
                                    <TableHead>{t('Car')}</TableHead>
                                    <TableHead className="text-end">{t('Rentals')}</TableHead>
                                    <TableHead className="text-end">{t('Total Days')}</TableHead>
                                    <TableHead className="text-end">{t('Avg Value (HT)')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {(topRentedCars ?? []).map((c, i) => (
                                    <TableRow key={i}>
                                        <TableCell>{i + 1}</TableCell>
                                        <TableCell title={c.car_name}>{c.car_name?.length > 25 ? c.car_name.slice(0, 25) + '…' : c.car_name}</TableCell>
                                        <TableCell className="text-end">{c.rental_count}</TableCell>
                                        <TableCell className="text-end">{fmt(c.total_rental_days, 0)}</TableCell>
                                        <TableCell className="text-end">{fmt(c.average_rental_value_ht)} MAD</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-sm">{t('Top 5 Most Profitable Cars (HT)')} — {selectedYear}</CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('Rank')}</TableHead>
                                    <TableHead>{t('Car')}</TableHead>
                                    <TableHead className="text-end">{t('Revenue (HT)')}</TableHead>
                                    <TableHead className="text-end">{t('TVA')}</TableHead>
                                    <TableHead className="text-end">{t('Revenue (TTC)')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {(topProfitableCars ?? []).map((c, i) => (
                                    <TableRow key={i}>
                                        <TableCell>{i + 1}</TableCell>
                                        <TableCell title={c.car_name}>{c.car_name?.length > 25 ? c.car_name.slice(0, 25) + '…' : c.car_name}</TableCell>
                                        <TableCell className="text-end">{fmt(c.total_revenue_ht)} MAD</TableCell>
                                        <TableCell className="text-end">{fmt(c.total_tva)} MAD</TableCell>
                                        <TableCell className="text-end">{fmt(c.total_revenue_ttc)} MAD</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </div>
    );
}

TvaReport.layout = (page) => (
    <AdminLayout breadcrumbs={[
        { label: 'TVA', href: route('tva.index') },
        { label: 'Report' },
    ]}>{page}</AdminLayout>
);
export default TvaReport;
