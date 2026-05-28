import { usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';

/**
 * Dashboard POC — BAN-57
 *
 * Renders stat cards using shadcn/ui primitives.
 * Branches on auth.user.type (shared prop) to show the correct layout.
 * Chart areas render a Skeleton placeholder until a charting library is added.
 */
export default function Dashboard({ stats, reminders, incomeExpenseByMonth, organizationByMonth }) {
    const { auth } = usePage().props;
    const isSuperAdmin = auth.user?.type === 'super admin';

    return (
        <div className="p-6 space-y-6">
            <div className="flex items-center gap-3">
                <h1 className="text-2xl font-semibold">Dashboard</h1>
                <Badge variant={isSuperAdmin ? 'default' : 'secondary'}>
                    {isSuperAdmin ? 'Super Admin' : 'Owner'}
                </Badge>
            </div>

            {isSuperAdmin
                ? <SuperAdminStats stats={stats} />
                : <OwnerStats stats={stats} reminders={reminders} />
            }

            {/* Chart placeholder — replaced by a real chart library in Phase 6 */}
            <div className="space-y-2">
                <p className="text-sm text-muted-foreground">Monthly chart</p>
                <Skeleton className="h-64 w-full rounded-lg" />
            </div>
        </div>
    );
}

function StatCard({ title, value }) {
    return (
        <Card>
            <CardHeader className="pb-2">
                <CardTitle className="text-sm font-medium text-muted-foreground">
                    {title}
                </CardTitle>
            </CardHeader>
            <CardContent>
                {value == null
                    ? <Skeleton className="h-8 w-24" />
                    : <p className="text-2xl font-bold">{value}</p>
                }
            </CardContent>
        </Card>
    );
}

function OwnerStats({ stats, reminders }) {
    return (
        <div className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <StatCard title="Users"    value={stats?.totalUser} />
                <StatCard title="Drivers"  value={stats?.totalDriver} />
                <StatCard title="Bookings" value={stats?.totalBooking} />
                <StatCard title="Income"   value={stats?.totalIncome} />
                <StatCard title="Expenses" value={stats?.totalExpense} />
            </div>

            {reminders?.length > 0 && (
                <div className="space-y-2">
                    <h2 className="text-lg font-semibold">Upcoming Reminders</h2>
                    <div className="space-y-2">
                        {reminders.map((r) => (
                            <Card key={r.id}>
                                <CardContent className="flex items-center justify-between py-3">
                                    <span className="text-sm">{r.description}</span>
                                    <Badge variant="outline">{r.reminder_date}</Badge>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}

function SuperAdminStats({ stats }) {
    return (
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <StatCard title="Organisations"  value={stats?.totalOrganization} />
            <StatCard title="Subscriptions"  value={stats?.totalSubscription} />
            <StatCard title="Transactions"   value={stats?.totalTransaction} />
            <StatCard title="Income"         value={stats?.totalIncome} />
        </div>
    );
}
