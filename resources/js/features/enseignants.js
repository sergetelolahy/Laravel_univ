import { useQuery } from '@tanstack/react-query';
import api from '../lib/api';

export function useEnseignants(params = {}, options = {}) {
    return useQuery({
        queryKey: ['enseignants', 'list', params],
        queryFn: async () => {
            const { data } = await api.get('/enseignants', { params });
            return data;
        },
        ...options,
    });
}

export function useMonEmploiDuTempsEnseignant(params = {}, options = {}) {
    return useQuery({
        queryKey: ['enseignants', 'me', 'emploi-du-temps', params],
        queryFn: async () => {
            const { data } = await api.get('/enseignants/me/emploi-du-temps', { params });
            return data;
        },
        ...options,
    });
}
