import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import api from './api';

/**
 * Fabrique des hooks React Query standards (liste, détail, create, update, delete)
 * pour une ressource REST qui suit le schéma classique du backend
 * (GET /path, GET /path/:id, POST /path, PUT /path/:id, DELETE /path/:id).
 */
export function createResourceHooks(resourceKey, basePath) {
    function useList(params = {}, options = {}) {
        return useQuery({
            queryKey: [resourceKey, 'list', params],
            queryFn: async () => {
                const { data } = await api.get(basePath, { params });
                return data;
            },
            ...options,
        });
    }

    function useItem(id, options = {}) {
        return useQuery({
            queryKey: [resourceKey, 'item', id],
            queryFn: async () => {
                const { data } = await api.get(`${basePath}/${id}`);
                return data;
            },
            enabled: options.enabled ?? !!id,
            ...options,
        });
    }

    function useCreate(options = {}) {
        const queryClient = useQueryClient();

        return useMutation({
            mutationFn: async (payload) => {
                const { data } = await api.post(basePath, payload);
                return data;
            },
            onSuccess: (...args) => {
                queryClient.invalidateQueries({ queryKey: [resourceKey, 'list'] });
                options.onSuccess?.(...args);
            },
            ...options,
        });
    }

    function useUpdate(options = {}) {
        const queryClient = useQueryClient();

        return useMutation({
            mutationFn: async ({ id, payload }) => {
                const { data } = await api.put(`${basePath}/${id}`, payload);
                return data;
            },
            onSuccess: (...args) => {
                queryClient.invalidateQueries({ queryKey: [resourceKey] });
                options.onSuccess?.(...args);
            },
            ...options,
        });
    }

    function useRemove(options = {}) {
        const queryClient = useQueryClient();

        return useMutation({
            mutationFn: async (id) => {
                const { data } = await api.delete(`${basePath}/${id}`);
                return data;
            },
            onSuccess: (...args) => {
                queryClient.invalidateQueries({ queryKey: [resourceKey, 'list'] });
                options.onSuccess?.(...args);
            },
            ...options,
        });
    }

    return { useList, useItem, useCreate, useUpdate, useRemove };
}
