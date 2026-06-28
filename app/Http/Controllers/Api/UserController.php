<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AccountDeletionMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function updateUsername(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $request->user()->id,
        ]);

        $request->user()->update(['username' => $data['username']]);

        return response()->json(['user' => $request->user()->fresh()]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/',     
                'regex:/[0-9]/',     
                'regex:/[^A-Za-z0-9]/', 
            ],
        ]);

        if (!Hash::check($request->current_password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Mot de passe actuel incorrect.'],
            ]);
        }

        $request->user()->update(['password' => $request->password]);

        return response()->json(['message' => 'Mot de passe mis à jour.']);
    }

    public function updatePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,webp|max:2048',
        ]);

        $user = $request->user();

        if ($user->profile_photo) {
            \Storage::disk('public')->delete($user->profile_photo);
        }

        $path = $request->file('profile_photo')->store('profile_photos', 'public');
        $user->update(['profile_photo' => $path]);

        return response()->json(['user' => $user->fresh()]);
    }
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $user = $request->user();

        // Vérifie le mot de passe avant suppression
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['errors' => ['password' => ['Mot de passe incorrect.']]], 422);
        }

        $deletionDate = now()->addDays(30)->format('d/m/Y');

        $user->scheduleDeletion('self');

        Mail::to($user->email)->send(new AccountDeletionMail($user->username, $deletionDate, $user->email, 'self'));

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Compte supprimé. Vous avez 30 jours pour annuler.']);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = \App\Models\User::withTrashed()
            ->whereRaw('LOWER(email) = ?', [strtolower($request->email)])
            ->first();


        if (!$user || !$user->trashed()) {
            return response()->json(['message' => 'Aucun compte supprimé trouvé.'], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['errors' => ['password' => ['Mot de passe incorrect.']]], 422);
        }

        if ($user->deletion_initiator === 'admin') {
            return response()->json([
                'message' => 'Cette suppression a été effectuée par un administrateur et ne peut être annulée. Contactez le support.'
            ], 403);
        }

        if ($user->scheduled_deletion_at && now()->isAfter($user->scheduled_deletion_at)) {
            return response()->json(['message' => 'Délai de restauration dépassé.'], 403);
        }

        $user->restoreAccount();

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json(['message' => 'Compte restauré.', 'user' => $user->fresh()]);
    }
    public function acceptCgu(Request $request)
    {
        $request->user()->update([
            'cgu_accepted_at'      => now(),
            'cgu_version_accepted' => config('app.cgu_version'),
        ]);

        return response()->json(['user' => $request->user()->fresh()]);
    }
    public function exportData(Request $request)
{
    $user = $request->user();

    $export = [
        'export_metadata' => [
            'generated_at' => now()->toIso8601String(),
            'version'      => '1.0',
            'user_id'      => $user->id,
        ],
        'account' => [
            'id'                   => $user->id,
            'username'             => $user->username,
            'email'                => $user->email,
            'role'                 => $user->role,
            'email_verified_at'    => $user->email_verified_at,
            'cgu_accepted_at'      => $user->cgu_accepted_at,
            'cgu_version_accepted' => $user->cgu_version_accepted,
            'two_factor_enabled'   => $user->two_factor_enabled,
            'two_factor_method'    => $user->two_factor_method,
            'created_at'           => $user->created_at,
            'updated_at'           => $user->updated_at,
        ],
        'projects' => $user->projects()
            ->with(['type:id,name', 'template:id,name', 'objects', 'formats:id,name,slug'])
            ->get()
            ->map(fn ($p) => [
                'id'              => $p->id,
                'name'            => $p->name,
                'description'     => $p->description,
                'rules'           => $p->rules,
                'mode'            => $p->mode,
                'status'          => $p->status,
                'min_players'     => $p->min_players,
                'max_players'     => $p->max_players,
                'duration_min'    => $p->duration_min,
                'duration_max'    => $p->duration_max,
                'allow_duplication' => $p->allow_duplication,
                'type'            => $p->type?->name,
                'based_on_template' => $p->template?->name,
                'formats'         => $p->formats->pluck('name'),
                'objects'         => $p->objects->map(fn ($o) => [
                    'name'         => $o->name,
                    'description'  => $o->description,
                    'quantity'     => $o->quantity,
                    'custom_data'  => $o->custom_data,
                ]),
                'card_schema'     => $p->card_schema,
                'card_layout'     => $p->card_layout,
                'created_at'      => $p->created_at,
                'updated_at'      => $p->updated_at,
            ]),
        'comments' => \App\Models\Comment::where('user_id', $user->id)
            ->with(['project:id,name', 'template:id,name'])
            ->get()
            ->map(fn ($c) => [
                'id'         => $c->id,
                'content'    => $c->content,
                'target'     => $c->project_id
                    ? ['type' => 'project',  'name' => $c->project?->name]
                    : ['type' => 'template', 'name' => $c->template?->name],
                'created_at' => $c->created_at,
                'updated_at' => $c->updated_at,
            ]),
        'ratings' => \App\Models\Rating::where('user_id', $user->id)
            ->with(['project:id,name', 'template:id,name'])
            ->get()
            ->map(fn ($r) => [
                'score'      => $r->score,
                'target'     => $r->project_id
                    ? ['type' => 'project',  'name' => $r->project?->name]
                    : ['type' => 'template', 'name' => $r->template?->name],
                'created_at' => $r->created_at,
            ]),
        'reports_submitted' => \App\Models\Report::where('reporter_id', $user->id)
            ->get()
            ->map(fn ($rep) => [
                'reportable_type' => class_basename($rep->reportable_type),
                'reportable_id'   => $rep->reportable_id,
                'reason_code'     => $rep->reason_code,
                'reason_text'     => $rep->reason_text,
                'status'          => $rep->status,
                'created_at'      => $rep->created_at,
            ]),
    ];

    $filename = 'gamefabrick-export-' . $user->id . '-' . now()->format('Y-m-d-His') . '.json';

    return response()->json($export, 200, [
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
}