<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Crew;
use App\Models\HealthRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CrewHealthRecordController extends Controller
{
    public function store(Request $request, Crew $crew)
    {
        $validated = $request->validate([
            'checkup_date' => ['required', 'date'],
            'medical_examiner' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'in:fit,unfit,restricted'],
            'notes' => ['nullable', 'string'],
            'next_checkup_date' => ['nullable', 'date', 'after_or_equal:checkup_date'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('health_records', 'public');
        }

        try {
            $crew->healthRecords()->create([
                'checkup_date' => $validated['checkup_date'],
                'medical_examiner' => $validated['medical_examiner'],
                'status' => $validated['status'] ?? 'fit',
                'notes' => $validated['notes'] ?? null,
                'next_checkup_date' => $validated['next_checkup_date'] ?? null,
                'attachment_path' => $attachmentPath,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            if ($attachmentPath) {
                Storage::disk('public')->delete($attachmentPath);
            }

            return back()->withInput()->with('error', 'Gagal menambahkan rekam medis.');
        }

        return back()->with('success', 'Rekam medis berhasil ditambahkan.');
    }

    public function update(Request $request, Crew $crew, HealthRecord $healthRecord)
    {
        $this->ensureHealthRecordBelongsToCrew($crew, $healthRecord);

        $validated = $request->validate([
            'checkup_date' => ['required', 'date'],
            'medical_examiner' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'in:fit,unfit,restricted'],
            'notes' => ['nullable', 'string'],
            'next_checkup_date' => ['nullable', 'date', 'after_or_equal:checkup_date'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $newAttachmentPath = null;
        if ($request->hasFile('attachment')) {
            $newAttachmentPath = $request->file('attachment')->store('health_records', 'public');
        }

        $oldAttachment = $healthRecord->attachment_path;

        try {
            $payload = [
                'checkup_date' => $validated['checkup_date'],
                'medical_examiner' => $validated['medical_examiner'],
                'status' => $validated['status'] ?? $healthRecord->status,
                'notes' => $validated['notes'] ?? null,
                'next_checkup_date' => $validated['next_checkup_date'] ?? null,
                'updated_by' => Auth::id(),
            ];

            if ($newAttachmentPath) {
                $payload['attachment_path'] = $newAttachmentPath;
            }

            $healthRecord->update($payload);

            if ($newAttachmentPath && !empty($oldAttachment)) {
                Storage::disk('public')->delete($oldAttachment);
            }
        } catch (\Throwable $e) {
            if ($newAttachmentPath) {
                Storage::disk('public')->delete($newAttachmentPath);
            }

            return back()->withInput()->with('error', 'Gagal memperbarui rekam medis.');
        }

        return back()->with('success', 'Rekam medis berhasil diperbarui.');
    }

    public function destroy(Crew $crew, HealthRecord $healthRecord)
    {
        $this->ensureHealthRecordBelongsToCrew($crew, $healthRecord);

        $healthRecord->delete();

        return back()->with('success', 'Rekam medis berhasil dihapus.');
    }

    protected function ensureHealthRecordBelongsToCrew(Crew $crew, HealthRecord $healthRecord): void
    {
        if ($healthRecord->crew_id !== $crew->id) {
            abort(404);
        }
    }
}
