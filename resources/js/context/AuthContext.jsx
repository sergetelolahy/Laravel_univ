import { createContext, useContext, useEffect, useMemo, useState, useCallback } from 'react';
import api, { setAuthToken, onUnauthorized } from '../lib/api';
import { ADMIN, SCOLARITE, ENSEIGNANT, ETUDIANT } from '../lib/roles';

const STORAGE_KEY = 'univ_token';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
    const [token, setToken] = useState(() => localStorage.getItem(STORAGE_KEY));
    const [user, setUser] = useState(null);
    const [ready, setReady] = useState(false);

    const clear = useCallback(() => {
        setToken(null);
        setUser(null);
        localStorage.removeItem(STORAGE_KEY);
        setAuthToken(null);
    }, []);

    useEffect(() => {
        onUnauthorized(() => clear());
    }, [clear]);

    useEffect(() => {
        async function bootstrap() {
            const storedToken = localStorage.getItem(STORAGE_KEY);

            if (storedToken) {
                setAuthToken(storedToken);
                try {
                    const { data } = await api.get('/auth/me');
                    setUser(data.utilisateur);
                    setToken(storedToken);
                } catch (error) {
                    clear();
                }
            }

            setReady(true);
        }

        bootstrap();
    }, [clear]);

    const login = useCallback(async (email, password) => {
        const { data } = await api.post('/auth/login', { email, password });
        localStorage.setItem(STORAGE_KEY, data.token);
        setAuthToken(data.token);
        setToken(data.token);
        setUser(data.utilisateur);
        return data.utilisateur;
    }, []);

    const logout = useCallback(async () => {
        try {
            await api.post('/auth/logout');
        } catch (error) {
            // la session locale est nettoyée même si l'appel réseau échoue
        }
        clear();
    }, [clear]);

    const role = user?.role ?? null;

    const value = useMemo(
        () => ({
            token,
            user,
            ready,
            isAuthenticated: !!token && !!user,
            role,
            isAdmin: role === ADMIN,
            isScolarite: role === SCOLARITE,
            isEnseignant: role === ENSEIGNANT,
            isEtudiant: role === ETUDIANT,
            login,
            logout,
        }),
        [token, user, ready, role, login, logout]
    );

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
    const context = useContext(AuthContext);

    if (!context) {
        throw new Error('useAuth doit être utilisé à l\'intérieur de <AuthProvider>');
    }

    return context;
}
