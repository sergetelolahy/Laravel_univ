import { inputClass } from './inputStyles';

export default function Input({ error, className = '', ...rest }) {
    return <input className={`${inputClass(!!error)} ${className}`} {...rest} />;
}
