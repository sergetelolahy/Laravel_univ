import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import api from '../lib/api';
import { createResourceHooks } from '../lib/createResourceHooks';

export const { useList: useAnnees, useItem: useAnnee, useCreate: useCreateAnnee, useUpdate: useUpdateAnnee, useRemove: useRemoveAnnee } =
    createResourceHooks('annees', '/annees');

export function useAnneeCourante(options = {}) {
    return useQuery({
        queryKey: ['annees', 'courante'],
        queryFn: async () => {
            const { data } = await api.get('/annees/courante');
            return data;
        },
        retry: false,
        ...options,
    });
}

export function useSetAnneeCourante(options = {}) {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async (id) => {
            const { data } = await api.patch(`/annees/${id}/courante`);
            return data;
        },
        onSuccess: (...args) => {
            queryClient.invalidateQueries({ queryKey: ['annees'] });
            options.onSuccess?.(...args);
        },
        ...options,
    });
}
