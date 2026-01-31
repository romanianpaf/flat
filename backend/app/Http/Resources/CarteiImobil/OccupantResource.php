<?php

namespace App\Http\Resources\CarteiImobil;

use App\Models\Occupant;
use App\Models\User;
use App\Support\CarteiImobil\CarteiImobilAccess;
use App\Support\CarteiImobil\SensitiveMasker;

class OccupantResource
{
    public static function toArray(Occupant $occupant, User $user): array
    {
        $maskForResident = (bool) config('carte_imobil.mask_cnp_for_resident', true);
        $isApprover = CarteiImobilAccess::isApprover($user) || CarteiImobilAccess::isGlobalAdmin($user);

        $includeSensitive = $isApprover || !$maskForResident;

        $base = [
            'id' => $occupant->id,
            'apartment_id' => $occupant->apartment_id,
            'first_name' => $occupant->first_name,
            'last_name' => $occupant->last_name,
            'domicile_address' => $occupant->domicile_address,
            'role_in_unit' => $occupant->role_in_unit,
            'other_role_text' => $occupant->other_role_text,
            'move_in_date' => optional($occupant->move_in_date)->format('Y-m-d'),
            'move_out_date' => optional($occupant->move_out_date)->format('Y-m-d'),
            'is_minor' => (bool) $occupant->is_minor,
            'legal_guardian_name' => $occupant->legal_guardian_name,
            'phone' => $occupant->phone,
            'email' => $occupant->email,
            'notes' => $occupant->notes,
            'status' => $occupant->status,
            'submitted_at' => optional($occupant->submitted_at)->format('Y-m-d H:i:s'),
            'approved_at' => optional($occupant->approved_at)->format('Y-m-d H:i:s'),
            'approved_by' => $occupant->approved_by,
            'reject_reason' => $occupant->reject_reason,
            'created_at' => optional($occupant->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($occupant->updated_at)->format('Y-m-d H:i:s'),
        ];

        if ($includeSensitive) {
            $base['cnp'] = $occupant->cnp;
            $base['id_series'] = $occupant->id_series;
            $base['id_number'] = $occupant->id_number;
        } else {
            $base['cnp_masked'] = SensitiveMasker::mask($occupant->cnp);
            $base['id_series_masked'] = SensitiveMasker::mask($occupant->id_series);
            $base['id_number_masked'] = SensitiveMasker::mask($occupant->id_number);
            $base['has_cnp'] = !empty((string) $occupant->cnp);
            $base['has_ci'] = !empty((string) $occupant->id_series) || !empty((string) $occupant->id_number);
        }

        return $base;
    }
}

