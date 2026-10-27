import { useQuery } from '@tanstack/react-query';
import api from '../lib/api';
import { createResourceHooks } from '../lib/createResourceHooks';

export const { useList: useSalles, useItem: useSalle, useCreate: useCreateSalle, useUpdate: useUpdateSalle, useRemove: useRemoveSalle } =
    createResourceHooks('salles', '/salles');

export function useSallesDisponibles(params, options = {}) {
    return useQuery({
        queryKey: ['salles', 'disponibles', params],
        queryFn: async () => {
            const { data } = await api.get('/salles/disponibles', { params });
            return data;
        },
        enabled: !!(params?.debut && params?.fin) && (options.enabled ?? true),
        ...options,
    });
}

export function useSalleOccupation(salleId, params = {}, options = {}) {
    return useQuery({
        queryKey: ['salles', salleId, 'occupation', params],
        queryFn: async () => {
            const { data } = await api.get(`/salles/${salleId}/occupation`, { params });
            return data;
        },
        enabled: !!salleId,
        ...options,
    });
}
