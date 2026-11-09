import { useMesMoyennes } from '../../features/etudiantConsultation';
import { extractErrorMessage } from '../../lib/api';
import { formatNumber } from '../../lib/format';
import { Alert, Badge, Card, DataTable, PageHeader, Spinner } from '../../components/ui';

export default function MesMoyennesPage() {
    const { data, isLoading, error } = useMesMoyennes();
    const moyennesSemestres = data?.moyennes_semestres ?? [];
    const moyennesCours = data?.moyennes_cours ?? [];
    const nombreCoursValides = data?.statistiques?.nombre_cours_valides ?? moyennesCours.filter((cours) => cours.valide).length;

    return (
        <div>
            <PageHeader
                title="Mes moyennes"
                description="Suis tes résultats par cours et par semestre."
                actions={<Badge variant="green">{nombreCoursValides} cours validé{nombreCoursValides > 1 ? 's' : ''}</Badge>}
            />

            {isLoading && <Spinner label="Chargement des moyennes…" />}
            {error && (
                <Alert variant="error">
                    {extractErrorMessage(error, "Impossible de charger les moyennes pour le moment.")}
                </Alert>
            )}

            {data && (
                <div className="space-y-6">
                    <Card title="Résultats par semestre">
                        <DataTable
                            rows={moyennesSemestres}
                            rowKey="semestre_id"
                            emptyTitle="Aucune moyenne de semestre disponible"
                            columns={[
                                { key: 'semestre', header: 'Semestre', render: (r) => <span className="font-semibold text-slate-800">Semestre {r.semestre}</span> },
                                {
                                    key: 'moyenne',
                                    header: 'Moyenne',
                                    render: (r) => (
                                        <span className={`font-bold ${r.valide ? 'text-emerald-700' : 'text-rose-700'}`}>
                                            {formatNumber(r.moyenne)} <span className="font-medium text-slate-400">/20</span>
                                        </span>
                                    ),
                                },
                                {
                                    key: 'valide',
                                    header: 'Statut',
                                    render: (r) => (r.valide ? <Badge variant="green">Validé</Badge> : <Badge variant="red">À améliorer</Badge>),
                                },
                            ]}
                        />
                    </Card>

                    <Card title="Résultats par cours">
                        <DataTable
                            rows={moyennesCours}
                            rowKey="cours_id"
                            emptyTitle="Aucune moyenne de cours disponible"
                            columns={[
                                { key: 'code_cours', header: 'Code' },
                                { key: 'cours', header: 'Cours' },
                                {
                                    key: 'moyenne',
                                    header: 'Moyenne',
                                    render: (r) => (
                                        <span className={`font-bold ${r.valide ? 'text-emerald-700' : 'text-rose-700'}`}>
                                            {formatNumber(r.moyenne)} <span className="font-medium text-slate-400">/20</span>
                                        </span>
                                    ),
                                },
                                {
                                    key: 'valide',
                                    header: 'Statut',
                                    render: (r) => (r.valide ? <Badge variant="green">Validé</Badge> : <Badge variant="red">À améliorer</Badge>),
                                },
                            ]}
                        />
                    </Card>
                </div>
            )}
        </div>
    );
}
