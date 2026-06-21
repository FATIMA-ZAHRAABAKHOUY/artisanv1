<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Support;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

// ================================================================
//  ProfileController
// ================================================================
class ProfileController extends Controller
{
    // GET /profil
    public function show()
    {
        return view('profile');
    }

    // PUT /profil
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'nom'        => 'required|string|max:100',
            'prenom'     => 'required|string|max:100',
            'telephone'  => 'nullable|string|max:20',
            'adresse'    => 'nullable|string|max:300',
            'ville'      => 'nullable|string|max:100',
            'code_postal'=> 'nullable|string|max:10',
            'avatar'     => 'nullable|image|max:2048',
        ]);

        // Upload avatar
        if ($request->hasFile('avatar')) {
            // Supprimer ancien avatar
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')
                ->store("avatars", 'public');
        }

        $user->update($validated);

        return back()->with('success', 'Profil mis à jour avec succès !');
    }

    // PUT /profil/password
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (! Hash::check($validated['current_password'], $user->getAuthPassword())) {
            return back()->withErrors([
                'current_password' => 'Le mot de passe actuel est incorrect.',
            ]);
        }

        $user->update(['password' => $validated['password']]);

        return back()->with('success', 'Mot de passe modifié avec succès !');
    }
}

