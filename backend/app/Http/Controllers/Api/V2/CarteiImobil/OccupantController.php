<?php

namespace App\Http\Controllers\Api\V2\CarteiImobil;

use App\Http\Controllers\Api\V2\ApiController;
use App\Http\Requests\CarteiImobil\UpdateOccupantRequest;
use App\Http\Resources\CarteiImobil\OccupantResource;
use App\Models\Occupant;
use App\Services\CarteiImobil\OccupantAuditService;
use App\Support\CarteiImobil\CarteiImobilAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OccupantController extends ApiController
{
    public function update(UpdateOccupantRequest $request, Occupant $occupant, OccupantAuditService $audit)
    {
        $this->authorize('update', $occupant);

        $user = $request->user();

        $updated = DB::transaction(function () use ($request, $occupant, $user, $audit) {
            $before = $occupant->toArray();

            $occupant->fill($request->validated());

            // Dacă e locatar și era respins, la prima editare revine în draft
            if (!CarteiImobilAccess::isApprover($user) && $occupant->status === 'rejected') {
                $occupant->status = 'draft';
                $occupant->reject_reason = null;
                $occupant->submitted_at = null;
                $occupant->approved_at = null;
                $occupant->approved_by = null;
            }

            $occupant->updated_by = $user->id;
            $occupant->save();

            $audit->log($occupant, 'updated', $request, $before, $occupant->fresh()->toArray());

            return $occupant;
        });

        return $this->success([
            'occupant' => OccupantResource::toArray($updated->fresh(), $request->user()),
        ]);
    }

    public function destroy(Request $request, Occupant $occupant, OccupantAuditService $audit)
    {
        $this->authorize('delete', $occupant);

        DB::transaction(function () use ($request, $occupant, $audit) {
            $before = $occupant->toArray();
            $occupant->delete(); // soft delete
            // păstrăm un log "updated" cu flag de ștergere (enum-ul nu include delete)
            $after = $before;
            $after['deleted_at'] = now()->format('Y-m-d H:i:s');
            $audit->log($occupant, 'updated', $request, $before, $after);
        });

        return $this->success(['message' => 'Persoana a fost ștearsă.']);
    }
}

