import { createResourceHooks } from '../lib/createResourceHooks';

export const { useList: useSemestres, useItem: useSemestre, useCreate: useCreateSemestre, useUpdate: useUpdateSemestre, useRemove: useRemoveSemestre } =
    createResourceHooks('semestres', '/semestres');
