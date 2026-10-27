export function inputClass(hasError) {
    return `block w-full rounded-md border-0 px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset disabled:bg-slate-50 disabled:text-slate-400 ${
        hasError ? 'ring-rose-400 focus:ring-rose-500' : 'ring-slate-300 focus:ring-indigo-600'
    }`;
}
