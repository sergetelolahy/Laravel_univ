import { inputClass } from './inputStyles';

export default function Textarea({ error, className = '', ...rest }) {
    return <textarea className={`${inputClass(!!error)} ${className}`} {...rest} />;
}
