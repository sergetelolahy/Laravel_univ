import { createResourceHooks } from '../lib/createResourceHooks';

export const {
    useList: useSeances,
    useItem: useSeance,
    useCreate: useCreateSeance,
    useUpdate: useUpdateSeance,
    useRemove: useRemoveSeance,
} = createResourceHooks('seances', '/seances');
