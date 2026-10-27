import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import api from '../lib/api';
import { createResourceHooks } from '../lib/createResourceHooks';

export const { useList: useGroupes, useItem: useGroupe, useCreate: useCreateGroupe, useUpdate: useUpdateGroupe, useRemove: useRemoveGroupe } =
    createResourceHooks('groupes', '/groupes');

export function useEtudiantsDuGroupe(groupeId, options = {}) {
    return useQuery({
        queryKey: ['groupes', groupeId, 'etudiants'],
        queryFn: async () => {
            const { data } = await api.get(`/groupes/${groupeId}/etudiants`);
            return data;
        },
        enabled: !!groupeId,
        ...options,
    });
}

export function useAjouterEtudiantsAuGroupe(groupeId, options = {}) {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async (etudiant_ids) => {
            const { data } = await api.post(`/groupes/${groupeId}/etudiants`, { etudiant_ids });
            return data;
        },
        onSuccess: (...args) => {
            queryClient.invalidateQueries({ queryKey: ['groupes', groupeId, 'etudiants'] });
            queryClient.invalidateQueries({ queryKey: ['groupes', 'list'] });
            options.onSuccess?.(...args);
        },
        ...options,
    });
}

export function useRetirerEtudiantDuGroupe(groupeId, options = {}) {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async (etudiantId) => {
            const { data } = await api.delete(`/groupes/${groupeId}/etudiants/${etudiantId}`);
            return data;
        },
        onSuccess: (...args) => {
            queryClient.invalidateQueries({ queryKey: ['groupes', groupeId, 'etudiants'] });
            queryClient.invalidateQueries({ queryKey: ['groupes', 'list'] });
            options.onSuccess?.(...args);
        },
        ...options,
    });
}
