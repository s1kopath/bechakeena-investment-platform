import { Head } from '@inertiajs/react';
import DashboardLayout from '@/Layouts/DashboardLayout';

/**
 * Investor dashboard home (Phase 0 smoke test).
 * Portfolio summary, history, and payouts are built in Phase 5.
 */
export default function Index({ appName }) {
    return (
        <DashboardLayout
            header={<h1 className="text-2xl font-semibold text-gray-900">Dashboard</h1>}
        >
            <Head title="Dashboard" />

            <div className="rounded-lg border border-gray-200 bg-white p-8">
                <p className="text-sm font-semibold uppercase tracking-wide text-brand-600">
                    {appName}
                </p>
                <h2 className="mt-2 text-xl font-semibold text-gray-900">
                    React + Inertia is wired up.
                </h2>
                <p className="mt-2 max-w-2xl text-gray-600">
                    This page is server-routed through Laravel and rendered by React via Inertia.
                    The investor portfolio, investment history, and payout tracker are built in
                    Phase 5.
                </p>
                <p className="mt-6 text-sm text-gray-400">
                    Phase 0 foundation — Inertia dashboard page rendering successfully.
                </p>
            </div>
        </DashboardLayout>
    );
}
