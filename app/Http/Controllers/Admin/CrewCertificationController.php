<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certification;
use App\Models\Crew;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CrewCertificationController extends Controller
{
    public function store(Request $request, Crew $crew)
    {
        $validated = $request->validate([
            'certificate_name' => ['required', 'string', 'max:255'],
            'certificate_number' => ['required', 'string', 'max:255'],
            'issue_date' => ['required', 'date'],
            'expiry_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'status' => ['nullable', 'in:valid,expired,revoked'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('certifications', 'public');
        }

        try {
            $crew->certifications()->create([
                'certificate_name' => $validated['certificate_name'],
                'certificate_number' => $validated['certificate_number'],
                'issue_date' => $validated['issue_date'],
                'expiry_date' => $validated['expiry_date'],
                'status' => $validated['status'] ?? 'valid',
                'attachment_path' => $attachmentPath,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            if ($attachmentPath) {
                Storage::disk('public')->delete($attachmentPath);
            }

            return back()->withInput()->with('error', 'Gagal menambahkan sertifikasi.');
        }

        return back()->with('success', 'Sertifikasi berhasil ditambahkan.');
    }

    public function update(Request $request, Crew $crew, Certification $certification)
    {
        $this->ensureCertificationBelongsToCrew($crew, $certification);

        $validated = $request->validate([
            'certificate_name' => ['required', 'string', 'max:255'],
            'certificate_number' => ['required', 'string', 'max:255'],
            'issue_date' => ['required', 'date'],
            'expiry_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'status' => ['nullable', 'in:valid,expired,revoked'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $newAttachmentPath = null;
        if ($request->hasFile('attachment')) {
            $newAttachmentPath = $request->file('attachment')->store('certifications', 'public');
        }

        $oldAttachment = $certification->attachment_path;

        try {
            $payload = [
                'certificate_name' => $validated['certificate_name'],
                'certificate_number' => $validated['certificate_number'],
                'issue_date' => $validated['issue_date'],
                'expiry_date' => $validated['expiry_date'],
                'status' => $validated['status'] ?? $certification->status,
                'updated_by' => Auth::id(),
            ];

            if ($newAttachmentPath) {
                $payload['attachment_path'] = $newAttachmentPath;
            }

            $certification->update($payload);

            if ($newAttachmentPath && !empty($oldAttachment)) {
                Storage::disk('public')->delete($oldAttachment);
            }
        } catch (\Throwable $e) {
            if ($newAttachmentPath) {
                Storage::disk('public')->delete($newAttachmentPath);
            }

            return back()->withInput()->with('error', 'Gagal memperbarui sertifikasi.');
        }

        return back()->with('success', 'Sertifikasi berhasil diperbarui.');
    }

    public function destroy(Crew $crew, Certification $certification)
    {
        $this->ensureCertificationBelongsToCrew($crew, $certification);

        $certification->delete();

        return back()->with('success', 'Sertifikasi berhasil dihapus.');
    }

    protected function ensureCertificationBelongsToCrew(Crew $crew, Certification $certification): void
    {
        if ($certification->crew_id !== $crew->id) {
            abort(404);
        }
    }
}
