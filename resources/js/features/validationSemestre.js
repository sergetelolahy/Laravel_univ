import { useMutation } from '@tanstack/react-query';
import api from '../lib/api';

export function useValiderSemestre(semestreId, options = {}) {
    return useMutation({
        mutationFn: async ({ etudiant_id, annee_id }) => {
            const { data } = await api.post(`/semestres/${semestreId}/validation`, { etudiant_id, annee_id });
            return data;
        },
        ...options,
    });
}
