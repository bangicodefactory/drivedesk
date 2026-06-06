import { usePage } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { useTranslation } from '@/hooks/useTranslation';
import AdminLayout from '@/Layouts/AdminLayout';
import StatCard           from '@/components/dashboard/StatCard';
import IncomeExpenseChart from '@/components/dashboard/IncomeExpenseChart';
import MonthlyBarChart    from '@/components/dashboard/MonthlyBarChart';
import ImmediateActions   from '@/components/dashboard/ImmediateActions';
import FleetAvailability  from '@/components/dashboard/FleetAvailability';
import {
    Car, RotateCcw, Wrench, TrendingUp, Building2,
} from 'lucide-react';

// Locale-aware grouping (fr uses spaces, en commas) but always Latin digits,
// matching how amounts are shown elsewhere in the app.
const fmtMoney = (v, locale) =>
    `${new Intl.NumberFormat(locale || 'en', { maximumFractionDigits: 0, numberingSystem: 'latn' }).format(Number(v || 0))} Dh`;

/**
 * Dashboard — BAN-66
 *
 * Branches on auth.user.type to render the owner or super-admin layout.
 * All widgets receive props sourced from HomeController::index.
 */
function Dashboard({ stats, incomeExpenseByMonth, organizationByMonth, operational, immediateActions, fleetAvailability }) {
    const t = useTranslation();
    const { auth } = usePage().props;
    const isSuperAdmin = auth.user?.type === 'super admin';

    return (
        <div className="p-6 space-y-6">
            <div className="flex items-center gap-3">
                <h1 className="text-2xl font-semibold">{t('Dashboard')}</h1>
                <Badge variant={isSuperAdmin ? 'default' : 'secondary'}>
                    {isSuperAdmin ? t('Super Admin') : t('Owner')}
                </Badge>
            </div>

            {isSuperAdmin
                ? <SuperAdminDashboard
                    stats={stats}
                    organizationByMonth={organizationByMonth}
                  />
                : <OwnerDashboard
                    operational={operational}
                    immediateActions={immediateActions}
                    fleetAvailability={fleetAvailability}
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

function OwnerDashboard({ operational, immediateActions, fleetAvailability, incomeExpenseByMonth }) {
    const t = useTranslation();
    const { locale } = usePage().props;
    const op = operational ?? {};

    return (
        <div className="space-y-6">
            {/* Operational metric cards — all derived from existing data */}
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    title={t('Cars Out Today')}
                    value={op.carsOut}
                    icon={Car}
                    subtitle={`/ ${op.totalVehicles ?? 0} ${t('Vehicles')}`}
                />
                <StatCard
                    title={t('Returns Due Today')}
                    value={op.returnsDueToday}
                    icon={RotateCcw}
                    subtitle={`${op.overdue ?? 0} ${t('overdue')}`}
                />
                <StatCard
                    title={t('Maintenance Due')}
                    value={op.maintenanceDue}
                    icon={Wrench}
                    subtitle={t('Reminders')}
                />
                <StatCard
                    title={t("Today's Revenue")}
                    value={op.revenueToday != null ? fmtMoney(op.revenueToday, locale) : null}
                    icon={TrendingUp}
                    subtitle={`${t('This month')}: ${fmtMoney(op.revenueMonth, locale)}`}
                />
            </div>

            {/* Immediate actions + income/expense */}
            <div className="grid gap-6 lg:grid-cols-3">
                <div className="lg:col-span-1">
                    <ImmediateActions actions={immediateActions ?? []} />
                </div>
                <div className="lg:col-span-2">
                    <IncomeExpenseChart data={incomeExpenseByMonth} />
                </div>
            </div>

            {/* Fleet availability timeline (next 7 days) */}
            <FleetAvailability data={fleetAvailability} />
        </div>
    );
}

function SuperAdminDashboard({ stats, organizationByMonth }) {
    const t = useTranslation();
    return (
        <div className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <StatCard title={t('Organisations')} value={stats?.totalOrganization} icon={Building2} />
            </div>

            <div className="grid gap-4 lg:grid-cols-2">
                <MonthlyBarChart
                    title={t('Organisations per month')}
                    data={organizationByMonth}
                    dataKeyName={t('Organisations')}
                />
            </div>
        </div>
    );
}
