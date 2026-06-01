import { usePage } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import AdminLayout from '@/Layouts/AdminLayout';
import StatCard           from '@/components/dashboard/StatCard';
import IncomeExpenseChart from '@/components/dashboard/IncomeExpenseChart';
import MonthlyBarChart    from '@/components/dashboard/MonthlyBarChart';
import RemindersList      from '@/components/dashboard/RemindersList';
import {
    Users, UserCheck, CalendarCheck, DollarSign, ReceiptText,
    Building2, CreditCard, ArrowRightLeft,
} from 'lucide-react';

/**
 * Dashboard — BAN-66
 *
 * Branches on auth.user.type to render the owner or super-admin layout.
 * All widgets receive props sourced from HomeController::index.
 */
function Dashboard({ stats, reminders, incomeExpenseByMonth, organizationByMonth }) {
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
                ? <SuperAdminDashboard
                    stats={stats}
                    organizationByMonth={organizationByMonth}
                  />
                : <OwnerDashboard
                    stats={stats}
                    reminders={reminders}
                    incomeExpenseByMonth={incomeExpenseByMonth}
                  />
            }
        </div>
    );
}

Dashboard.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Dashboard' }]}>{page}</AdminLayout>
);

export default Dashboard;

// ─────────────────────────────────────────────────────────────────────────────

function OwnerDashboard({ stats, reminders, incomeExpenseByMonth }) {
    return (
        <div className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <StatCard title="Total Driver"  value={stats?.totalDriver}  icon={UserCheck} />
                <StatCard title="Total Booking" value={stats?.totalBooking} icon={CalendarCheck} />
                <StatCard title="Total Income"  value={stats?.totalIncome}  icon={DollarSign} />
                <StatCard title="Total Expense" value={stats?.totalExpense} icon={ReceiptText} />
            </div>

            <RemindersList reminders={reminders ?? []} />

            <IncomeExpenseChart data={incomeExpenseByMonth} />
        </div>
    );
}

function SuperAdminDashboard({ stats, organizationByMonth }) {
    return (
        <div className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <StatCard title="Organisations" value={stats?.totalOrganization} icon={Building2} />
            </div>

            <div className="grid gap-4 lg:grid-cols-2">
                <MonthlyBarChart
                    title="Organisations per month"
                    data={organizationByMonth}
                    dataKeyName="Organisations"
                />
            </div>
        </div>
    );
}
