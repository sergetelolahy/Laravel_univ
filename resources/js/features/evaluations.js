import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import api from '../lib/api';

export function useEvaluations(params = {}, options = {}) {
    return useQuery({
        queryKey: ['evaluations', 'list', params],
        queryFn: async () => {
            const { data } = await api.get('/evaluations', { params });
            return data;
        },
        ...options,
    });
}

export function useCreateEvaluation(options = {}) {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async (payload) => {
            const { data } = await api.post('/evaluations', payload);
            return data;
        },
        onSuccess: (...args) => {
            queryClient.invalidateQueries({ queryKey: ['evaluations'] });
            options.onSuccess?.(...args);
        },
        ...options,
    });
}

export function useSaisirNotes(evaluationId, options = {}) {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async (notes) => {
            const { data } = await api.post(`/evaluations/${evaluationId}/notes`, { notes });
            return data;
        },
        onSuccess: (...args) => {
            queryClient.invalidateQueries({ queryKey: ['evaluations'] });
            options.onSuccess?.(...args);
        },
        ...options,
    });
}
