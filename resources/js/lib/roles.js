export const ADMIN = 'ADMIN';
export const SCOLARITE = 'SCOLARITE';
export const ENSEIGNANT = 'ENSEIGNANT';
export const ETUDIANT = 'ETUDIANT';

export const ALL_ROLES = [ADMIN, SCOLARITE, ENSEIGNANT, ETUDIANT];

export const ROLE_LABELS = {
    [ADMIN]: 'Administrateur',
    [SCOLARITE]: 'Scolarité',
    [ENSEIGNANT]: 'Enseignant',
    [ETUDIANT]: 'Étudiant',
};

export function homePathForRole(role) {
    switch (role) {
        case ADMIN:
            return '/admin';
        case SCOLARITE:
            return '/scolarite';
        case ENSEIGNANT:
            return '/enseignant';
        case ETUDIANT:
            return '/etudiant';
        default:
            return '/login';
    }
}
