import { useState } from 'react';

const VARIANTS = {
    success: 'border-green-200 bg-green-50 text-green-800',
    error: 'border-red-200 bg-red-50 text-red-800',
    warning: 'border-amber-200 bg-amber-50 text-amber-800',
    info: 'border-blue-200 bg-blue-50 text-blue-800',
};

/**
 * Shared alert / flash message. See docs/05-conventions.md → Shared UI feedback.
 */
export default function Alert({ variant = 'info', dismissible = true, children }) {
    const [visible, setVisible] = useState(true);

    if (!visible || !children) {
        return null;
    }

    return (
        <div
            role="alert"
            className={`flex items-start justify-between gap-3 rounded-lg border px-4 py-3 text-sm ${
                VARIANTS[variant] ?? VARIANTS.info
            }`}
        >
            <span>{children}</span>
            {dismissible && (
                <button
                    type="button"
                    onClick={() => setVisible(false)}
                    className="text-current opacity-60 transition hover:opacity-100"
                    aria-label="Dismiss"
                >
                    ×
                </button>
            )}
        </div>
    );
}
