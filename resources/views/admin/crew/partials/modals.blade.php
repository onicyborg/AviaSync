<div class="modal fade" id="certificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST"
                action="{{ route('admin.crew.certifications.store', $crew->id) }}"
                enctype="multipart/form-data"
                data-store-action="{{ route('admin.crew.certifications.store', $crew->id) }}">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Certification</h5>
                    <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label">Certificate Name</label>
                        <input type="text" name="certificate_name" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Certificate Number</label>
                        <input type="text" name="certificate_number" class="form-control" required>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Issue Date</label>
                            <input type="date" name="issue_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-4 mt-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="valid">Valid</option>
                            <option value="expired">Expired</option>
                            <option value="revoked">Revoked</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Attachment (optional)</label>
                        <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp">
                        <div class="text-muted fs-8 mt-1">Format: pdf/jpg/png/webp. Maks 4MB.</div>
                        <div class="text-muted fs-8 edit-attachment-hint d-none">* Kosongkan field ini jika tidak ingin mengganti file yg tersimpan saat ini</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="healthRecordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST"
                action="{{ route('admin.crew.health-records.store', $crew->id) }}"
                enctype="multipart/form-data"
                data-store-action="{{ route('admin.crew.health-records.store', $crew->id) }}">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Health Record</h5>
                    <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Checkup Date</label>
                            <input type="date" name="checkup_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Medical Examiner</label>
                            <input type="text" name="medical_examiner" class="form-control" required>
                        </div>
                    </div>
                    <div class="row g-4 mt-1">
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="fit">Fit</option>
                                <option value="unfit">Unfit</option>
                                <option value="restricted">Restricted</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Next Checkup Date</label>
                            <input type="date" name="next_checkup_date" class="form-control">
                        </div>
                    </div>
                    <div class="mt-4 mb-4">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Attachment (optional)</label>
                        <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp">
                        <div class="text-muted fs-8 mt-1">Format: pdf/jpg/png/webp. Maks 4MB.</div>
                        <div class="text-muted fs-8 edit-attachment-hint d-none">Jika tidak ingin mengganti maka kosongkan saja.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
