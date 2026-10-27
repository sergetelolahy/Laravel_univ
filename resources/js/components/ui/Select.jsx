import { inputClass } from './inputStyles';

export default function Select({ error, className = '', children, ...rest }) {
    return (
        <select className={`${inputClass(!!error)} ${className}`} {...rest}>
            {children}
        </select>
    );
}
