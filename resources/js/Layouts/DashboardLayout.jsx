import { Link } from '@inertiajs/react';

/**
 * Investor dashboard shell (React + Inertia).
 * Navigation and auth-aware header are fleshed out in Phase 5.
 */
export default function DashboardLayout({ header, children }) {
    return (
        <div className="min-h-screen bg-gray-50">
            <header className="border-b border-gray-200 bg-white">
                <div className="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                    <Link href="/" className="text-lg font-semibold text-brand-700">
                        Bechakeena
                    </Link>
                    <nav className="flex items-center gap-6 text-sm font-medium text-gray-600">
                        <Link href="/dashboard" className="hover:text-brand-700">
                            Dashboard
                        </Link>
                    </nav>
                </div>
            </header>

            {header && (
                <div className="border-b border-gray-200 bg-white">
                    <div className="mx-auto max-w-7xl px-6 py-6">{header}</div>
                </div>
            )}

            <main className="mx-auto max-w-7xl px-6 py-8">{children}</main>
        </div>
    );
}
