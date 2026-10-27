import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import api from '../lib/api';

export function useUtilisateurs(params = {}, options = {}) {
    return useQuery({
        queryKey: ['utilisateurs', 'list', params],
        queryFn: async () => {
            const { data } = await api.get('/utilisateurs', { params });
            return data;
        },
        ...options,
    });
}

export function useCreateUtilisateur(options = {}) {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async (payload) => {
            const { data } = await api.post('/utilisateurs', payload);
            return data;
        },
        onSuccess: (...args) => {
            queryClient.invalidateQueries({ queryKey: ['utilisateurs'] });
            options.onSuccess?.(...args);
        },
        ...options,
    });
}

export function useDesactiverUtilisateur(options = {}) {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async (id) => {
            const { data } = await api.patch(`/utilisateurs/${id}/desactiver`);
            return data;
        },
        onSuccess: (...args) => {
            queryClient.invalidateQueries({ queryKey: ['utilisateurs'] });
            options.onSuccess?.(...args);
        },
        ...options,
    });
}
