import { Link } from 'react-router-dom';

export default function ForbiddenPage() {
    return (
        <div className="flex min-h-screen flex-col items-center justify-center gap-3 bg-slate-50 px-4 text-center">
            <p className="text-5xl font-bold text-slate-300">403</p>
            <h1 className="text-lg font-semibold text-slate-900">Accès refusé</h1>
            <p className="max-w-sm text-sm text-slate-500">
                Votre rôle ne permet pas d'accéder à cette page.
            </p>
            <Link to="/dashboard" className="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                Retour au tableau de bord
            </Link>
        </div>
    );
}
