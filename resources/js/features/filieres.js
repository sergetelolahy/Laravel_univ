import { createResourceHooks } from '../lib/createResourceHooks';

export const { useList: useFilieres, useItem: useFiliere, useCreate: useCreateFiliere, useUpdate: useUpdateFiliere, useRemove: useRemoveFiliere } =
    createResourceHooks('filieres', '/filieres');
