import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import Spinner from './ui/Spinner';

export default function RequireAuth({ roles, children }) {
    const { ready, isAuthenticated, role } = useAuth();
    const location = useLocation();

    if (!ready) {
        return (
            <div className="flex min-h-screen items-center justify-center">
                <Spinner label="Vérification de la session…" />
            </div>
        );
    }

    if (!isAuthenticated) {
        return <Navigate to="/login" state={{ from: location }} replace />;
    }

    if (roles && roles.length > 0 && !roles.includes(role)) {
        return <Navigate to="/403" replace />;
    }

    return children;
}
