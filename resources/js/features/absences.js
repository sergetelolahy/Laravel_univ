import { useMutation, useQueryClient } from '@tanstack/react-query';
import api from '../lib/api';

export function useDeclarerAbsences(seanceId, options = {}) {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async (absences) => {
            const { data } = await api.post(`/seances/${seanceId}/absences`, { absences });
            return data;
        },
        onSuccess: (...args) => {
            queryClient.invalidateQueries({ queryKey: ['seances'] });
            options.onSuccess?.(...args);
        },
        ...options,
    });
}
