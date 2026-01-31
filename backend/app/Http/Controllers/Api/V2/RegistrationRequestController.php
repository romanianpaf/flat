<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\StoreRegistrationRequest;
use App\Models\Apartment;
use App\Models\RegistrationRequest;
use App\Models\Tenant;
use App\Models\User;
use App\Support\CarteiImobil\CarteiImobilAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RegistrationRequestController extends ApiController
{
    /**
     * List tenants for public dropdown (no auth required)
     */
    public function publicTenants()
    {
        $tenants = Tenant::query()
            ->select('id', 'name', 'address', 'registration_instructions')
            ->orderBy('name')
            ->get();

        return $this->success(['tenants' => $tenants]);
    }

    /**
     * List apartments for a tenant (public, for registration form)
     */
    public function publicApartments(int $tenantId)
    {
        $apartments = Apartment::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('staircase')
            ->orderByRaw('CAST(number AS UNSIGNED) ASC')
            ->orderBy('number')
            ->get()
            ->map(fn(Apartment $a) => [
                'id' => $a->id,
                'number' => $a->number,
                'staircase' => $a->staircase,
                'floor' => $a->floor,
                'label' => $a->number . ($a->staircase ? ' (Scara ' . $a->staircase . ')' : ''),
            ]);

        return $this->success(['apartments' => $apartments]);
    }

    /**
     * Store a new registration request (public)
     */
    public function store(StoreRegistrationRequest $request)
    {
        $data = $request->validated();

        // Handle document upload
        $documentPath = null;
        $documentOriginalName = null;
        
        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $documentOriginalName = $file->getClientOriginalName();
            $documentPath = $file->store('registration-docs', 'private');
        }

        $registrationRequest = RegistrationRequest::create([
            'tenant_id' => $data['tenant_id'],
            'apartment_id' => $data['apartment_id'] ?? null,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'document_path' => $documentPath,
            'document_original_name' => $documentOriginalName,
            'notes' => $data['notes'] ?? null,
            'status' => RegistrationRequest::STATUS_PENDING,
        ]);

        return $this->success([
            'message' => 'Cererea a fost trimisă cu succes. Vei primi un email când va fi procesată.',
            'request_id' => $registrationRequest->id,
        ], 201);
    }

    /**
     * List registration requests for admin (protected)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Check permissions
        if (!$this->canManageRequests($user)) {
            return $this->error('Nu ai drepturi pentru a vedea cererile de înregistrare.', 403);
        }

        $query = RegistrationRequest::query()
            ->with(['tenant:id,name', 'apartment:id,number,staircase', 'reviewer:id,first_name,last_name']);

        // Filter by tenant for non-global admins
        if (!CarteiImobilAccess::isGlobalAdmin($user) && $user->tenant_id) {
            $query->where('tenant_id', $user->tenant_id);
        }

        // Filter by status
        if ($request->has('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        $requests = $query
            ->orderByRaw("FIELD(status, 'pending', 'rejected', 'approved')")
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return $this->success([
            'requests' => $requests->items(),
            'pagination' => [
                'total' => $requests->total(),
                'per_page' => $requests->perPage(),
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
            ],
        ]);
    }

    /**
     * Show a single registration request (protected)
     */
    public function show(Request $request, RegistrationRequest $registrationRequest)
    {
        $user = $request->user();

        if (!$this->canManageRequests($user, $registrationRequest)) {
            return $this->error('Nu ai drepturi pentru a vedea această cerere.', 403);
        }

        $registrationRequest->load(['tenant:id,name,address', 'apartment:id,number,staircase,floor', 'reviewer:id,first_name,last_name']);

        return $this->success([
            'request' => $registrationRequest,
            'has_document' => !empty($registrationRequest->document_path),
        ]);
    }

    /**
     * Download the document for a registration request
     */
    public function downloadDocument(Request $request, RegistrationRequest $registrationRequest)
    {
        $user = $request->user();

        if (!$this->canManageRequests($user, $registrationRequest)) {
            return $this->error('Nu ai drepturi pentru a descărca acest document.', 403);
        }

        if (empty($registrationRequest->document_path)) {
            return $this->error('Această cerere nu are document atașat.', 404);
        }

        if (!Storage::disk('private')->exists($registrationRequest->document_path)) {
            return $this->error('Documentul nu a fost găsit.', 404);
        }

        return Storage::disk('private')->download(
            $registrationRequest->document_path,
            $registrationRequest->document_original_name ?? 'document'
        );
    }

    /**
     * Approve a registration request
     */
    public function approve(Request $request, RegistrationRequest $registrationRequest)
    {
        $user = $request->user();

        if (!$this->canManageRequests($user, $registrationRequest)) {
            return $this->error('Nu ai drepturi pentru a aproba această cerere.', 403);
        }

        if (!$registrationRequest->isPending()) {
            return $this->error('Această cerere a fost deja procesată.', 400);
        }

        // Generate random password
        $password = Str::random(12);

        DB::beginTransaction();
        try {
            // Create user (password is hashed automatically by User model mutator)
            $newUser = User::create([
                'first_name' => $registrationRequest->first_name,
                'last_name' => $registrationRequest->last_name,
                'email' => $registrationRequest->email,
                'password' => $password,
                'tenant_id' => $registrationRequest->tenant_id,
                'phone' => $registrationRequest->phone,
                'profile_image' => 'https://i.pravatar.cc/150?u=' . urlencode($registrationRequest->email),
            ]);

            // Assign role
            $newUser->assignRole('locatar');

            // Assign apartment if selected
            if ($registrationRequest->apartment_id) {
                $newUser->apartments()->attach($registrationRequest->apartment_id);
                
                // Also set the apartment fields on user for legacy support
                $apartment = Apartment::find($registrationRequest->apartment_id);
                if ($apartment) {
                    $newUser->update([
                        'apartment' => $apartment->number,
                        'staircase' => $apartment->staircase,
                        'floor' => $apartment->floor,
                    ]);
                }
            }

            // Update request status
            $registrationRequest->update([
                'status' => RegistrationRequest::STATUS_APPROVED,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
            ]);

            DB::commit();

            // TODO: Send email with credentials
            // Mail::to($registrationRequest->email)->send(new WelcomeEmail($newUser, $password));

            return $this->success([
                'message' => 'Cererea a fost aprobată. Utilizatorul a fost creat.',
                'user_id' => $newUser->id,
                'temporary_password' => $password, // Remove in production, send via email instead
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('A apărut o eroare la crearea utilizatorului: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Reject a registration request
     */
    public function reject(Request $request, RegistrationRequest $registrationRequest)
    {
        $user = $request->user();

        if (!$this->canManageRequests($user, $registrationRequest)) {
            return $this->error('Nu ai drepturi pentru a respinge această cerere.', 403);
        }

        if (!$registrationRequest->isPending()) {
            return $this->error('Această cerere a fost deja procesată.', 400);
        }

        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $registrationRequest->update([
            'status' => RegistrationRequest::STATUS_REJECTED,
            'rejection_reason' => $request->input('reason'),
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]);

        // TODO: Send email notification
        // Mail::to($registrationRequest->email)->send(new RegistrationRejectedEmail($registrationRequest));

        return $this->success([
            'message' => 'Cererea a fost respinsă.',
        ]);
    }

    /**
     * Check if user can manage registration requests
     */
    private function canManageRequests(User $user, ?RegistrationRequest $request = null): bool
    {
        // Global admin can manage all
        if (CarteiImobilAccess::isGlobalAdmin($user)) {
            return true;
        }

        // Check permission
        if (!$user->can('view registration requests') && !$user->can('approve registration requests')) {
            return false;
        }

        // If checking specific request, verify tenant match
        if ($request && $user->tenant_id !== $request->tenant_id) {
            return false;
        }

        return true;
    }
}
