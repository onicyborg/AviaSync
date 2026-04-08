<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Crew;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ManageCrewController extends Controller
{
    public function index()
    {
        $crews = Crew::query()
            ->with('user')
            ->latest()
            ->get();

        return view('admin.crew', compact('crews'));
    }

    public function show(string $id)
    {
        $crew = Crew::query()
            ->with([
                'user',
                'certifications' => fn ($query) => $query->latest(),
                'healthRecords' => fn ($query) => $query->latest(),
            ])
            ->findOrFail($id);

        return view('admin.crew.show', compact('crew'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'employee_id' => ['required', 'string', 'max:255', 'unique:crews,employee_id'],
            'position' => ['required', 'string', 'max:255'],
            'base_location' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,inactive'],
            'profile_picture' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ]);

        $path = null;
        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('crews', 'public');
        }

        try {
            DB::transaction(function () use ($validated, $path) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'role' => 'crew',
                    'password' => Hash::make('Qwerty123*'),
                ]);

                Crew::create([
                    'user_id' => $user->id,
                    'employee_id' => $validated['employee_id'],
                    'profile_picture' => $path,
                    'position' => $validated['position'],
                    'base_location' => $validated['base_location'],
                    'status' => $validated['status'] ?? 'active',
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            });
        } catch (\Throwable $e) {
            if (!empty($path)) {
                Storage::disk('public')->delete($path);
            }

            return back()->withInput()->with('error', 'Gagal menyimpan data crew.');
        }

        return redirect()->route('admin.crew.index')->with('success', 'Crew berhasil ditambahkan');
    }

    public function update(Request $request, string $id)
    {
        $crew = Crew::query()->with('user')->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $crew->user_id],
            'employee_id' => ['required', 'string', 'max:255', 'unique:crews,employee_id,' . $crew->id],
            'position' => ['required', 'string', 'max:255'],
            'base_location' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,inactive'],
            'profile_picture' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $newPath = null;
        if ($request->hasFile('profile_picture')) {
            $newPath = $request->file('profile_picture')->store('crews', 'public');
        }

        try {
            DB::transaction(function () use ($request, $crew, $validated, $newPath) {
                if ($crew->user) {
                    $userPayload = [
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                    ];

                    if (!empty($validated['password'])) {
                        $userPayload['password'] = Hash::make($validated['password']);
                    }

                    $crew->user->update($userPayload);
                }

                $payload = [
                    'employee_id' => $validated['employee_id'],
                    'position' => $validated['position'],
                    'base_location' => $validated['base_location'],
                    'status' => $validated['status'] ?? $crew->status,
                    'updated_by' => Auth::id(),
                ];

                if (!empty($newPath)) {
                    if (!empty($crew->profile_picture)) {
                        Storage::disk('public')->delete($crew->profile_picture);
                    }
                    $payload['profile_picture'] = $newPath;
                }

                $crew->update($payload);
            });
        } catch (\Throwable $e) {
            if (!empty($newPath)) {
                Storage::disk('public')->delete($newPath);
            }

            return back()->withInput()->with('error', 'Gagal memperbarui data crew.');
        }

        $redirectTarget = $request->input('redirect_to') === 'detail'
            ? route('admin.crew.show', $crew->id)
            : route('admin.crew.index');

        return redirect()->to($redirectTarget)->with('success', 'Crew berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        $crew = Crew::query()->findOrFail($id);
        $crew->delete();

        return redirect()->route('admin.crew.index')->with('success', 'Crew berhasil dihapus');
    }
}
