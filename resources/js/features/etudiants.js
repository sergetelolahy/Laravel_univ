import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import api from '../lib/api';

export function useEtudiants(params = {}, options = {}) {
    return useQuery({
        queryKey: ['etudiants', 'list', params],
        queryFn: async () => {
            const { data } = await api.get('/etudiants', { params });
            return data;
        },
        ...options,
    });
}

export function useEtudiant(id, options = {}) {
    return useQuery({
        queryKey: ['etudiants', 'item', id],
        queryFn: async () => {
            const { data } = await api.get(`/etudiants/${id}`);
            return data;
        },
        enabled: !!id,
        ...options,
    });
}

export function useUpdateEtudiant(options = {}) {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async ({ id, payload }) => {
            const { data } = await api.put(`/etudiants/${id}`, payload);
            return data;
        },
        onSuccess: (...args) => {
            queryClient.invalidateQueries({ queryKey: ['etudiants'] });
            options.onSuccess?.(...args);
        },
        ...options,
    });
}

export function useEtudiantMe(options = {}) {
    return useQuery({
        queryKey: ['etudiants', 'me'],
        queryFn: async () => {
            const { data } = await api.get('/etudiants/me');
            return data;
        },
        ...options,
    });
}

export function useMesInscriptions(options = {}) {
    return useQuery({
        queryKey: ['etudiants', 'me', 'inscriptions'],
        queryFn: async () => {
            const { data } = await api.get('/etudiants/me/inscriptions');
            return data;
        },
        ...options,
    });
}

export function useMonEmploiDuTemps(params = {}, options = {}) {
    return useQuery({
        queryKey: ['etudiants', 'me', 'emploi-du-temps', params],
        queryFn: async () => {
            const { data } = await api.get('/etudiants/me/emploi-du-temps', { params });
            return data;
        },
        ...options,
    });
}
