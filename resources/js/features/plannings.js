import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import api from '../lib/api';

export function useDisponibilites(semaineDebut, options = {}) {
    return useQuery({
        queryKey: ['edt', 'disponibilites', semaineDebut],
        queryFn: async () => {
            const { data } = await api.get('/edt/disponibilites', { params: { semaine_debut: semaineDebut } });
            return data;
        },
        enabled: !!semaineDebut,
        ...options,
    });
}

export function useEnregistrerDisponibilites(options = {}) {
    const queryClient = useQueryClient();
    const { onSuccess, ...mutationOptions } = options;

    return useMutation({
        mutationFn: async (payload) => {
            const { data } = await api.post('/edt/disponibilites', payload);
            return data;
        },
        onSuccess: (...args) => {
            queryClient.invalidateQueries({ queryKey: ['edt', 'disponibilites'] });
            onSuccess?.(...args);
        },
        ...mutationOptions,
    });
}

export function usePlannings(params, options = {}) {
    return useQuery({
        queryKey: ['edt', 'hebdomadaires', params],
        queryFn: async () => {
            const { data } = await api.get('/edt/hebdomadaires', { params });
            return data;
        },
        enabled: !!params?.semaine_debut,
        ...options,
    });
}

export function usePlanning(id, options = {}) {
    return useQuery({
        queryKey: ['edt', 'hebdomadaire', id],
        queryFn: async () => {
            const { data } = await api.get(`/edt/hebdomadaires/${id}`);
            return data;
        },
        enabled: !!id,
        ...options,
    });
}

function usePlanningMutation(action, options = {}) {
    const queryClient = useQueryClient();
    const { onSuccess, ...mutationOptions } = options;

    return useMutation({
        mutationFn: async ({ id, payload = {} }) => {
            const { data } = await api[action.method](`/edt/hebdomadaires/${id}${action.path}`, payload);
            return data;
        },
        onSuccess: (data, variables, ...args) => {
            queryClient.invalidateQueries({ queryKey: ['edt'] });
            onSuccess?.(data, variables, ...args);
        },
        ...mutationOptions,
    });
}

export function useCreerPlanning(options = {}) {
    const queryClient = useQueryClient();
    const { onSuccess, ...mutationOptions } = options;

    return useMutation({
        mutationFn: async (payload) => {
            const { data } = await api.post('/edt/hebdomadaires', payload);
            return data;
        },
        onSuccess: (...args) => {
            queryClient.invalidateQueries({ queryKey: ['edt'] });
            onSuccess?.(...args);
        },
        ...mutationOptions,
    });
}

export function useAjouterSeancePlanning(options = {}) {
    return usePlanningMutation({ method: 'post', path: '/seances' }, options);
}

export function useSupprimerSeancePlanning(options = {}) {
    const queryClient = useQueryClient();
    const { onSuccess, ...mutationOptions } = options;

    return useMutation({
        mutationFn: async ({ id, seanceId }) => {
            const { data } = await api.delete(`/edt/hebdomadaires/${id}/seances/${seanceId}`);
            return data;
        },
        onSuccess: (...args) => {
            queryClient.invalidateQueries({ queryKey: ['edt'] });
            onSuccess?.(...args);
        },
        ...mutationOptions,
    });
}

export function useSoumettrePlanning(options = {}) {
    return usePlanningMutation({ method: 'post', path: '/soumettre' }, options);
}

export function useValiderPlanning(options = {}) {
    return usePlanningMutation({ method: 'post', path: '/valider' }, options);
}

export function usePublierPlanning(options = {}) {
    return usePlanningMutation({ method: 'post', path: '/publier' }, options);
}
