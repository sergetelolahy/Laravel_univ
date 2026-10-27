import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import api from '../lib/api';
import { createResourceHooks } from '../lib/createResourceHooks';

export const { useList: useCoursList, useItem: useCoursItem, useCreate: useCreateCours, useUpdate: useUpdateCours, useRemove: useRemoveCours } =
    createResourceHooks('cours', '/cours');

export function useEnseignantsDuCours(coursId, params = {}, options = {}) {
    return useQuery({
        queryKey: ['cours', coursId, 'enseignants', params],
        queryFn: async () => {
            const { data } = await api.get(`/cours/${coursId}/enseignants`, { params });
            return data;
        },
        enabled: !!coursId,
        ...options,
    });
}

export function useInscritsDuCours(coursId, params = {}, options = {}) {
    return useQuery({
        queryKey: ['cours', coursId, 'inscrits', params],
        queryFn: async () => {
            const { data } = await api.get(`/cours/${coursId}/inscrits`, { params });
            return data;
        },
        enabled: !!coursId,
        ...options,
    });
}

export function useAttachEnseignant(coursId, options = {}) {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async ({ enseignant_id, annee_id }) => {
            const { data } = await api.post(`/cours/${coursId}/enseignants`, { enseignant_id, annee_id });
            return data;
        },
        onSuccess: (...args) => {
            queryClient.invalidateQueries({ queryKey: ['cours', coursId, 'enseignants'] });
            options.onSuccess?.(...args);
        },
        ...options,
    });
}

export function useDetachEnseignant(coursId, options = {}) {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async ({ enseignantId, annee_id }) => {
            const { data } = await api.delete(`/cours/${coursId}/enseignants/${enseignantId}`, { data: { annee_id } });
            return data;
        },
        onSuccess: (...args) => {
            queryClient.invalidateQueries({ queryKey: ['cours', coursId, 'enseignants'] });
            options.onSuccess?.(...args);
        },
        ...options,
    });
}
