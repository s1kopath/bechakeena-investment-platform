import { Link } from '@inertiajs/react';

/**
 * Admin panel shell (React + Inertia, /admin).
 * Role-aware navigation and admin auth are built in Phase 2.
 */
export default function AdminLayout({ header, children }) {
    return (
        <div className="min-h-screen bg-gray-100">
            <header className="bg-gray-900 text-gray-100">
                <div className="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                    <Link href="/admin" className="text-lg font-semibold">
                        Bechakeena Admin
                    </Link>
                    <nav className="flex items-center gap-6 text-sm font-medium text-gray-300">
                        <Link href="/admin" className="hover:text-white">
                            Overview
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
