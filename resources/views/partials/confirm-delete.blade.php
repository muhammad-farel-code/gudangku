<!-- Global Confirm Delete Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <div style="width: 56px; height: 56px; border-radius: 50%; background-color: var(--gk-danger-bg); color: var(--gk-danger); display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        <i class="bi bi-trash3-fill"></i>
                    </div>
                </div>
                <h5 class="fw-bold mb-2 text-dark" id="deleteConfirmModalLabel">Konfirmasi Hapus</h5>
                <p class="text-muted small mb-4" id="deleteModalMessage">
                    Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.
                </p>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-gk-outline px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger px-4" style="border-radius: var(--gk-radius-pill); font-weight: 600;">Hapus Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openDeleteModal(actionUrl, itemName = 'data ini') {
        const form = document.getElementById('deleteForm');
        form.action = actionUrl;
        const msg = document.getElementById('deleteModalMessage');
        msg.innerHTML = `Apakah Anda yakin ingin menghapus <strong>${itemName}</strong>? Tindakan ini permanen dan tidak dapat dibatalkan.`;
        const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        modal.show();
    }
</script>
