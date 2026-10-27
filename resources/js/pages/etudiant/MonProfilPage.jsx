import { useEtudiantMe } from '../../features/etudiants';
import { useAuth } from '../../context/AuthContext';
import { extractErrorMessage } from '../../lib/api';
import { Alert, Card, PageHeader, Spinner } from '../../components/ui';

export default function MonProfilPage() {
    const { user } = useAuth();
    const { data, isLoading, error } = useEtudiantMe();
    const etudiant = data?.data;

    return (
        <div>
            <PageHeader title="Mon profil" description="Informations de mon dossier étudiant." />

            {isLoading && <Spinner />}
            {error && <Alert variant="error">{extractErrorMessage(error, 'Impossible de charger le profil.')}</Alert>}

            {etudiant && (
                <Card className="max-w-lg">
                    <dl className="divide-y divide-slate-100 text-sm">
                        <Row label="Matricule" value={etudiant.matricule} />
                        <Row label="Nom" value={etudiant.nom} />
                        <Row label="Prénom" value={etudiant.prenom} />
                        <Row label="Filière" value={etudiant.filiere?.nom ?? '—'} />
                        <Row label="E-mail" value={user?.email} />
                    </dl>
                </Card>
            )}
        </div>
    );
}

function Row({ label, value }) {
    return (
        <div className="flex justify-between py-2">
            <dt className="text-slate-500">{label}</dt>
            <dd className="font-medium text-slate-900">{value}</dd>
        </div>
    );
}
