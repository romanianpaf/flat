<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Impersonare reală ("login ca user") pentru operatorul de platformă (sysadmin).
 *
 * start() emite un access token Passport pentru userul țintă; frontend-ul comută
 * pe el (păstrând token-ul sysadminului pentru revenire). stop() revocă token-ul
 * de impersonare. Doar sysadmin poate porni o impersonare, și nu poate impersona
 * un alt sysadmin sau pe sine.
 */
class ImpersonationController extends ApiController
{
    /**
     * Lista userilor care pot fi impersonați (toți userii de tenant, fără sysadmini).
     */
    public function candidates(Request $request)
    {
        $actor = $request->user();
        if (!$actor->isSystemAdmin()) {
            return $this->error('Doar un administrator de platformă poate impersona.', 403);
        }

        $users = User::query()
            ->with(['tenant', 'roles'])
            ->whereNotNull('tenant_id')
            ->orderBy('tenant_id')
            ->orderBy('first_name')
            ->get()
            ->reject(fn(User $u) => $u->isSystemAdmin())
            ->map(fn(User $u) => [
                'id' => $u->id,
                'name' => $this->displayName($u),
                'email' => $u->email,
                'tenant_id' => $u->tenant_id,
                'tenant_name' => $u->tenant?->name,
                'roles' => $u->roles->pluck('name')->values(),
            ])
            ->values();

        return $this->success(['users' => $users]);
    }

    /**
     * Pornește impersonarea: emite un token pentru userul țintă.
     */
    public function start(Request $request, User $user)
    {
        $actor = $request->user();

        if (!$actor->isSystemAdmin()) {
            return $this->error('Doar un administrator de platformă poate impersona.', 403);
        }
        if ($user->id === $actor->id) {
            return $this->error('Nu te poți impersona pe tine însuți.', 422);
        }
        if ($user->isSystemAdmin()) {
            return $this->error('Nu poți impersona un alt administrator de platformă.', 422);
        }

        $tokenResult = $user->createToken('impersonation:by-' . $actor->id);
        $tokenResult->token->forceFill(['expires_at' => now()->addHours(8)])->save();

        Log::info('Impersonation started', ['by' => $actor->id, 'as' => $user->id]);

        return $this->success([
            'access_token' => $tokenResult->accessToken,
            'user' => [
                'id' => $user->id,
                'name' => $this->displayName($user),
            ],
        ]);
    }

    /**
     * Oprește impersonarea: revocă token-ul curent (de impersonare).
     * Apelabilă de userul impersonat — revocă doar propriul token.
     */
    public function stop(Request $request)
    {
        $token = $request->user()->token();
        if ($token) {
            $token->revoke();
            Log::info('Impersonation stopped', ['as' => $request->user()->id]);
        }

        return $this->success(['message' => 'Impersonarea a fost oprită.']);
    }

    private function displayName(User $u): string
    {
        $name = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? ''));
        return $name !== '' ? $name : $u->email;
    }
}
