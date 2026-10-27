import { useMesMoyennes } from '../../features/etudiantConsultation';
import { extractErrorMessage } from '../../lib/api';
import { formatNumber } from '../../lib/format';
import { Alert, Badge, Card, DataTable, PageHeader, Spinner } from '../../components/ui';

export default function MesMoyennesPage() {
    const { data, isLoading, error } = useMesMoyennes();

    return (
        <div>
            <PageHeader title="Mes moyennes" description="Moyennes par cours et par semestre." />

            {isLoading && <Spinner label="Chargement des moyennes…" />}
            {error && (
                <Alert variant="error">
                    {extractErrorMessage(error, "Impossible de charger les moyennes pour le moment.")}
                </Alert>
            )}

            {data && (
                <div className="space-y-6">
                    <Card title="Par semestre">
                        <DataTable
                            rows={data.moyennes_semestres ?? []}
                            rowKey="semestre_id"
                            emptyTitle="Aucune moyenne de semestre disponible"
                            columns={[
                                { key: 'semestre', header: 'Semestre', render: (r) => `Semestre ${r.semestre}` },
                                { key: 'moyenne', header: 'Moyenne', render: (r) => `${formatNumber(r.moyenne)} / 20` },
                                {
                                    key: 'valide',
                                    header: 'Statut',
                                    render: (r) => (r.valide ? <Badge variant="green">Validé</Badge> : <Badge variant="red">Non validé</Badge>),
                                },
                            ]}
                        />
                    </Card>

                    <Card title="Par cours">
                        <DataTable
                            rows={data.moyennes_cours ?? []}
                            rowKey="cours_id"
                            emptyTitle="Aucune moyenne de cours disponible"
                            columns={[
                                { key: 'code_cours', header: 'Code' },
                                { key: 'cours', header: 'Cours' },
                                { key: 'moyenne', header: 'Moyenne', render: (r) => `${formatNumber(r.moyenne)} / 20` },
                                {
                                    key: 'valide',
                                    header: 'Statut',
                                    render: (r) => (r.valide ? <Badge variant="green">Validé</Badge> : <Badge variant="red">Non validé</Badge>),
                                },
                            ]}
                        />
                    </Card>
                </div>
            )}
        </div>
    );
}
