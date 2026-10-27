import { useState } from 'react';
import { Navigate, useLocation, useNavigate } from 'react-router-dom';
import AuthLayout from '../../layouts/AuthLayout';
import { useAuth } from '../../context/AuthContext';
import { extractErrorMessage } from '../../lib/api';
import { Alert, Button, Field, Input } from '../../components/ui';

export default function LoginPage() {
    const { login, isAuthenticated, ready } = useAuth();
    const navigate = useNavigate();
    const location = useLocation();
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);

    if (ready && isAuthenticated) {
        const redirectTo = location.state?.from?.pathname ?? '/dashboard';
        return <Navigate to={redirectTo} replace />;
    }

    async function handleSubmit(event) {
        event.preventDefault();
        setError('');
        setLoading(true);
        try {
            await login(email, password);
            navigate(location.state?.from?.pathname ?? '/dashboard', { replace: true });
        } catch (err) {
            setError(extractErrorMessage(err, 'Connexion impossible.'));
        } finally {
            setLoading(false);
        }
    }

    return (
        <AuthLayout>
            <form className="space-y-4" onSubmit={handleSubmit}>
                <Alert variant="error">{error}</Alert>
                <Field label="Adresse e-mail" required>
                    <Input
                        type="email"
                        autoComplete="username"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        required
                    />
                </Field>
                <Field label="Mot de passe" required>
                    <Input
                        type="password"
                        autoComplete="current-password"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        required
                    />
                </Field>
                <Button type="submit" className="w-full" loading={loading}>
                    Se connecter
                </Button>
            </form>
        </AuthLayout>
    );
}
