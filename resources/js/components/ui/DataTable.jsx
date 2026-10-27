import Spinner from './Spinner';
import EmptyState from './EmptyState';
import Alert from './Alert';

/**
 * Table générique.
 * columns: [{ key, header, render?: (row) => node, className? }]
 * rows: tableau d'objets
 */
export default function DataTable({ columns, rows, loading, error, emptyTitle, rowKey = 'id', actions }) {
    if (loading) return <Spinner label="Chargement des données…" />;

    if (error) return <Alert variant="error">{error}</Alert>;

    if (!rows || rows.length === 0) {
        return <EmptyState title={emptyTitle ?? 'Aucun élément trouvé'} />;
    }

    return (
        <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-slate-200">
                <thead>
                    <tr>
                        {columns.map((col) => (
                            <th
                                key={col.key}
                                className="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                            >
                                {col.header}
                            </th>
                        ))}
                        {actions && (
                            <th className="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Actions
                            </th>
                        )}
                    </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                    {rows.map((row) => (
                        <tr key={row[rowKey]} className="hover:bg-slate-50">
                            {columns.map((col) => (
                                <td key={col.key} className={`px-3 py-2.5 text-sm text-slate-700 ${col.className ?? ''}`}>
                                    {col.render ? col.render(row) : (row[col.key] ?? '—')}
                                </td>
                            ))}
                            {actions && (
                                <td className="px-3 py-2.5 text-right text-sm">
                                    <div className="flex justify-end gap-2">{actions(row)}</div>
                                </td>
                            )}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
