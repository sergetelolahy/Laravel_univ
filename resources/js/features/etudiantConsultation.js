import { useQuery } from '@tanstack/react-query';
import api from '../lib/api';

export function useMesNotes(options = {}) {
    return useQuery({
        queryKey: ['etudiant', 'mes-notes'],
        queryFn: async () => {
            const { data } = await api.get('/etudiant/mes-notes');
            return data;
        },
        ...options,
    });
}

export function useMesMoyennes(options = {}) {
    return useQuery({
        queryKey: ['etudiant', 'mes-moyennes'],
        queryFn: async () => {
            const { data } = await api.get('/etudiant/mes-moyennes');
            return data;
        },
        retry: false,
        ...options,
    });
}
