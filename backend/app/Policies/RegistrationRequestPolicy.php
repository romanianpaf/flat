<?php

namespace App\Policies;

use App\Models\RegistrationRequest;
use App\Models\User;
use App\Support\CarteiImobil\CarteiImobilAccess;
use Illuminate\Auth\Access\Response;

class RegistrationRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response|bool
    {
        if (CarteiImobilAccess::isGlobalAdmin($user)) {
            return true;
        }

        return $user->can('view registration requests');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RegistrationRequest $registrationRequest): Response|bool
    {
        if (CarteiImobilAccess::isGlobalAdmin($user)) {
            return true;
        }

        if (!$user->can('view registration requests')) {
            return false;
        }

        // Must be from same tenant
        return $user->tenant_id === $registrationRequest->tenant_id;
    }

    /**
     * Determine whether the user can approve the request.
     */
    public function approve(User $user, RegistrationRequest $registrationRequest): Response|bool
    {
        if (CarteiImobilAccess::isGlobalAdmin($user)) {
            return true;
        }

        if (!$user->can('approve registration requests')) {
            return false;
        }

        // Must be from same tenant
        return $user->tenant_id === $registrationRequest->tenant_id;
    }

    /**
     * Determine whether the user can reject the request.
     */
    public function reject(User $user, RegistrationRequest $registrationRequest): Response|bool
    {
        return $this->approve($user, $registrationRequest);
    }

    /**
     * Determine whether the user can download the document.
     */
    public function downloadDocument(User $user, RegistrationRequest $registrationRequest): Response|bool
    {
        return $this->view($user, $registrationRequest);
    }
}
