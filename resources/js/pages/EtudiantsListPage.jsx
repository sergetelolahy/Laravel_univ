import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useEtudiants } from '../features/etudiants';
import { useFilieres } from '../features/filieres';
import { extractErrorMessage } from '../lib/api';
import { Card, DataTable, PageHeader, Input, Select, Button } from '../components/ui';

export default function EtudiantsListPage() {
    const [search, setSearch] = useState('');
    const [filiereFiltre, setFiliereFiltre] = useState('');
    const [page, setPage] = useState(1);
    const { data: filieresData } = useFilieres();
    const filieres = filieresData?.data ?? [];

    const params = { page, per_page: 20, ...(search && { q: search }), ...(filiereFiltre && { filiere_id: filiereFiltre }) };
    const { data, isLoading, error } = useEtudiants(params);
    const rows = data?.data ?? [];

    return (
        <div>
            <PageHeader title="Étudiants" description="Dossiers des étudiants inscrits à l'université." />

            <Card
                actions={
                    <div className="flex gap-2">
                        <Input
                            placeholder="Rechercher…"
                            value={search}
                            onChange={(e) => {
                                setSearch(e.target.value);
                                setPage(1);
                            }}
                            className="w-56"
                        />
                        <Select
                            value={filiereFiltre}
                            onChange={(e) => {
                                setFiliereFiltre(e.target.value);
                                setPage(1);
                            }}
                            className="w-48"
                        >
                            <option value="">Toutes les filières</option>
                            {filieres.map((f) => (
                                <option key={f.id} value={f.id}>
                                    {f.code}
                                </option>
                            ))}
                        </Select>
                    </div>
                }
            >
                <DataTable
                    loading={isLoading}
                    error={error ? extractErrorMessage(error) : null}
                    rows={rows}
                    columns={[
                        { key: 'matricule', header: 'Matricule' },
                        {
                            key: 'nom',
                            header: 'Nom',
                            render: (r) => (
                                <Link to={`/etudiants/${r.id}`} className="font-medium text-indigo-600 hover:text-indigo-500">
                                    {r.nom} {r.prenom}
                                </Link>
                            ),
                        },
                        { key: 'filiere', header: 'Filière', render: (r) => r.filiere?.nom ?? '—' },
                    ]}
                    actions={(row) => (
                        <Link to={`/etudiants/${row.id}`} className="text-sm font-medium text-slate-600 hover:text-slate-900">
                            Détails
                        </Link>
                    )}
                />

                <div className="mt-4 flex items-center justify-between text-sm text-slate-600">
                    <Button variant="secondary" disabled={page <= 1} onClick={() => setPage((p) => p - 1)}>
                        Précédent
                    </Button>
                    <span>Page {page}</span>
                    <Button variant="secondary" disabled={rows.length === 0} onClick={() => setPage((p) => p + 1)}>
                        Suivant
                    </Button>
                </div>
            </Card>
        </div>
    );
}
