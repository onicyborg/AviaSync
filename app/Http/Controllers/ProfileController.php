<?php

namespace App\Http\Controllers;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        if (!$user instanceof User) {
            abort(403);
        }

        return view('auth.profile', [
            'user' => $user,
            'crew' => $user?->crew,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user instanceof User) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'telepon' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->telepon = $validated['telepon'] ?? null;

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/profile', $filename);

            if (!empty($user->photo)) {
                Storage::disk('public')->delete('profile/' . $user->photo);
            }

            $user->photo = $filename;
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('profile')->with('success', 'Profil berhasil diperbarui.');
    }

    public function downloadBiodata(): Response
    {
        $user = Auth::user();

        if (!$user instanceof User) {
            abort(403);
        }

        $pdf = Pdf::loadView('auth.profile.biodata-pdf', compact('user'));

        return $pdf->stream('Biodata_' . str_replace(' ', '_', $user->name) . '.pdf');
    }
}
