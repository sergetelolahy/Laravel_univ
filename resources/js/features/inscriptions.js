import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import api from '../lib/api';

export function useInscriptions(params = {}, options = {}) {
    return useQuery({
        queryKey: ['inscriptions', 'list', params],
        queryFn: async () => {
            const { data } = await api.get('/inscriptions', { params });
            return data;
        },
        ...options,
    });
}

export function useCreateInscription(options = {}) {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async (payload) => {
            const { data } = await api.post('/inscriptions', payload);
            return data;
        },
        onSuccess: (...args) => {
            queryClient.invalidateQueries({ queryKey: ['inscriptions'] });
            options.onSuccess?.(...args);
        },
        ...options,
    });
}

export function useCreateInscriptionsLot(options = {}) {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async (inscriptions) => {
            const { data } = await api.post('/inscriptions/lot', { inscriptions });
            return data;
        },
        onSuccess: (...args) => {
            queryClient.invalidateQueries({ queryKey: ['inscriptions'] });
            options.onSuccess?.(...args);
        },
        ...options,
    });
}

export function useRemoveInscription(options = {}) {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async (id) => {
            const { data } = await api.delete(`/inscriptions/${id}`);
            return data;
        },
        onSuccess: (...args) => {
            queryClient.invalidateQueries({ queryKey: ['inscriptions'] });
            options.onSuccess?.(...args);
        },
        ...options,
    });
}
