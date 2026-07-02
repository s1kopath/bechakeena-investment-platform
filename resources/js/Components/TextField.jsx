import { forwardRef } from 'react';
import InputError from '@/Components/InputError';

/**
 * Labelled text input with inline error. Forwards a ref for autofocus.
 */
const TextField = forwardRef(function TextField(
    { label, name, type = 'text', error, className = '', ...props },
    ref,
) {
    return (
        <div className={className}>
            {label && (
                <label htmlFor={name} className="mb-1 block text-sm font-medium text-gray-700">
                    {label}
                </label>
            )}
            <input
                {...props}
                ref={ref}
                id={name}
                name={name}
                type={type}
                className="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
            />
            <InputError message={error} />
        </div>
    );
});

export default TextField;
