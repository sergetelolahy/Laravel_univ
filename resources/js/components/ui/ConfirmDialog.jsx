import Modal from './Modal';
import Button from './Button';

export default function ConfirmDialog({
    open,
    title = 'Confirmer',
    message,
    confirmLabel = 'Confirmer',
    cancelLabel = 'Annuler',
    variant = 'danger',
    loading = false,
    onConfirm,
    onCancel,
}) {
    return (
        <Modal
            open={open}
            onClose={onCancel}
            title={title}
            size="sm"
            footer={
                <>
                    <Button variant="secondary" onClick={onCancel} disabled={loading}>
                        {cancelLabel}
                    </Button>
                    <Button variant={variant} onClick={onConfirm} loading={loading}>
                        {confirmLabel}
                    </Button>
                </>
            }
        >
            <p className="text-sm text-slate-600">{message}</p>
        </Modal>
    );
}
