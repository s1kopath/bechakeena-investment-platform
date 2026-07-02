/**
 * Inline field validation error (Laravel useForm errors).
 */
export default function InputError({ message, className = '' }) {
    if (!message) {
        return null;
    }

    return <p className={`mt-1 text-sm text-red-600 ${className}`}>{message}</p>;
}
