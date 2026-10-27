import { useState } from 'react';
import { useEnseignants } from '../features/enseignants';
import { extractErrorMessage } from '../lib/api';
import { Button, Card, DataTable, PageHeader } from '../components/ui';

export default function EnseignantsPage() {
    const [page, setPage] = useState(1);
    const { data, isLoading, error } = useEnseignants({ page });
    const rows = data?.data ?? [];
    const meta = data?.meta;

    return (
        <div>
            <PageHeader title="Enseignants" description="Liste des comptes enseignants." />

            <Card>
                <DataTable
                    loading={isLoading}
                    error={error ? extractErrorMessage(error) : null}
                    rows={rows}
                    columns={[
                        { key: 'nom', header: 'Nom', render: (r) => `${r.nom} ${r.prenom}` },
                        { key: 'specialite', header: 'Spécialité' },
                        { key: 'email', header: 'E-mail' },
                    ]}
                />

                {meta && meta.last_page > 1 && (
                    <div className="mt-4 flex items-center justify-between text-sm text-slate-600">
                        <Button variant="secondary" disabled={page <= 1} onClick={() => setPage((p) => p - 1)}>
                            Précédent
                        </Button>
                        <span>
                            Page {meta.current_page} / {meta.last_page}
                        </span>
                        <Button variant="secondary" disabled={page >= meta.last_page} onClick={() => setPage((p) => p + 1)}>
                            Suivant
                        </Button>
                    </div>
                )}
            </Card>
        </div>
    );
}
