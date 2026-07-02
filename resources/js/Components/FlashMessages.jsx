import { usePage } from '@inertiajs/react';
import Alert from '@/Components/Alert';

/**
 * Renders server-flashed messages exposed via the shared `flash` Inertia prop
 * (HandleInertiaRequests::share). Drop into layouts once.
 */
export default function FlashMessages() {
    const { flash } = usePage().props;

    const messages = [
        ['success', flash?.success],
        ['error', flash?.error],
        ['warning', flash?.warning],
        ['info', flash?.info],
    ].filter(([, message]) => Boolean(message));

    if (messages.length === 0) {
        return null;
    }

    return (
        <div className="space-y-3">
            {messages.map(([variant, message]) => (
                <Alert key={variant} variant={variant}>
                    {message}
                </Alert>
            ))}
        </div>
    );
}
