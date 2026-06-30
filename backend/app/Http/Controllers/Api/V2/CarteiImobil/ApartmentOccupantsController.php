<?php

namespace App\Http\Controllers\Api\V2\CarteiImobil;

use App\Http\Controllers\Api\V2\ApiController;
use App\Http\Requests\CarteiImobil\RejectOccupantsRequest;
use App\Http\Requests\CarteiImobil\StoreOccupantRequest;
use App\Http\Resources\CarteiImobil\OccupantResource;
use App\Models\Apartment;
use App\Models\Occupant;
use App\Services\CarteiImobil\OccupantAuditService;
use App\Support\CarteiImobil\CarteiImobilAccess;
use App\Support\Pdf\Fpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApartmentOccupantsController extends ApiController
{
    public function index(Request $request, Apartment $apartment)
    {
        $this->authorize('view', $apartment);

        $occupants = Occupant::query()
            ->where('apartment_id', $apartment->id)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $data = [
            'apartment' => [
                'id' => $apartment->id,
                'tenant_id' => $apartment->tenant_id,
                'number' => $apartment->number,
                'staircase' => $apartment->staircase,
                'floor' => $apartment->floor,
            ],
            'occupants' => $occupants->map(fn(Occupant $o) => OccupantResource::toArray($o, $request->user()))->values(),
        ];

        return $this->success($data);
    }

    public function store(StoreOccupantRequest $request, Apartment $apartment, OccupantAuditService $audit)
    {
        $this->authorize('create', [Occupant::class, $apartment]);

        // Înghețare editare dacă există cerere în lucru/aprobată
        $hasLocked = Occupant::query()
            ->where('apartment_id', $apartment->id)
            ->whereIn('status', ['submitted', 'approved'])
            ->exists();

        if ($hasLocked && !CarteiImobilAccess::isApprover($request->user())) {
            return $this->error('Cererea este deja trimisă sau aprobată. Nu mai poți modifica lista până la o decizie.', 409);
        }

        $user = $request->user();
        // Comitetul (CEX/admin) finalizează direct, fără pasul de validare.
        $isManager = CarteiImobilAccess::isTenantManager($user, $apartment);

        $occupant = DB::transaction(function () use ($request, $apartment, $user, $audit, $isManager) {
            $occupant = new Occupant();
            $occupant->fill($request->validated());
            $occupant->apartment_id = $apartment->id;
            $occupant->created_by = $user->id;
            $occupant->updated_by = $user->id;
            $occupant->reject_reason = null;

            if ($isManager) {
                $occupant->status = 'approved';
                $occupant->submitted_at = now();
                $occupant->approved_at = now();
                $occupant->approved_by = $user->id;
            } else {
                $occupant->status = 'draft';
                $occupant->submitted_at = null;
                $occupant->approved_at = null;
                $occupant->approved_by = null;
            }

            $occupant->save();

            $audit->log(
                $occupant,
                'created',
                $request,
                null,
                $occupant->fresh()->toArray()
            );

            return $occupant;
        });

        return $this->success([
            'occupant' => OccupantResource::toArray($occupant->fresh(), $request->user()),
        ], 201);
    }

    public function submit(Request $request, Apartment $apartment, OccupantAuditService $audit)
    {
        $this->authorize('submit', [Occupant::class, $apartment]);

        $user = $request->user();

        $occupants = Occupant::query()
            ->where('apartment_id', $apartment->id)
            ->get();

        if ($occupants->isEmpty()) {
            return $this->error('Nu poți trimite spre validare fără cel puțin o persoană.', 422);
        }

        if ($occupants->contains(fn(Occupant $o) => in_array($o->status, ['submitted', 'approved'], true))) {
            return $this->error('Există deja o cerere în lucru sau aprobată pentru acest apartament.', 409);
        }

        DB::transaction(function () use ($occupants, $user, $request, $audit) {
            foreach ($occupants as $o) {
                $before = $o->toArray();
                $o->status = 'submitted';
                $o->submitted_at = now();
                $o->reject_reason = null;
                $o->approved_at = null;
                $o->approved_by = null;
                $o->updated_by = $user->id;
                $o->save();
                $audit->log($o, 'submitted', $request, $before, $o->fresh()->toArray());
            }
        });

        return $this->success(['message' => 'Cererea a fost trimisă spre validare.']);
    }

    public function approve(Request $request, Apartment $apartment, OccupantAuditService $audit)
    {
        $this->authorize('approve', [Occupant::class, $apartment]);

        $user = $request->user();

        $occupants = Occupant::query()
            ->where('apartment_id', $apartment->id)
            ->get();

        if ($occupants->isEmpty()) {
            return $this->error('Nu există persoane în acest apartament.', 404);
        }

        // Aprobă tot ce nu e deja aprobat (draft/submitted/rejected -> approved).
        // Comitetul finalizează lista dintr-un singur pas.
        $toApprove = $occupants->filter(fn(Occupant $o) => $o->status !== 'approved');
        if ($toApprove->isEmpty()) {
            return $this->error('Toate persoanele sunt deja aprobate.', 409);
        }

        DB::transaction(function () use ($toApprove, $user, $request, $audit) {
            foreach ($toApprove as $o) {
                $before = $o->toArray();
                $o->status = 'approved';
                $o->approved_at = now();
                $o->approved_by = $user->id;
                $o->updated_by = $user->id;
                $o->reject_reason = null;
                $o->save();
                $audit->log($o, 'approved', $request, $before, $o->fresh()->toArray());
            }
        });

        return $this->success(['message' => 'Persoanele au fost aprobate.']);
    }

    public function reject(RejectOccupantsRequest $request, Apartment $apartment, OccupantAuditService $audit)
    {
        $this->authorize('approve', [Occupant::class, $apartment]);

        $user = $request->user();
        $reason = $request->validated()['reason'];

        $submitted = Occupant::query()
            ->where('apartment_id', $apartment->id)
            ->where('status', 'submitted')
            ->get();

        if ($submitted->isEmpty()) {
            return $this->error('Nu există o cerere submitted pentru acest apartament.', 409);
        }

        DB::transaction(function () use ($submitted, $user, $reason, $request, $audit) {
            foreach ($submitted as $o) {
                $before = $o->toArray();
                $o->status = 'rejected';
                $o->reject_reason = $reason;
                $o->approved_at = null;
                $o->approved_by = null;
                $o->updated_by = $user->id;
                $o->save();
                $audit->log($o, 'rejected', $request, $before, $o->fresh()->toArray());
            }
        });

        return $this->success(['message' => 'Cererea a fost respinsă.']);
    }

    public function exportPdf(Request $request, Apartment $apartment, OccupantAuditService $audit)
    {
        $this->authorize('export', [Occupant::class, $apartment]);

        $user = $request->user();

        $occupants = Occupant::query()
            ->where('apartment_id', $apartment->id)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        if ($occupants->isEmpty()) {
            return $this->error('Nu există persoane pentru export.', 404);
        }

        // Locatar: doar dacă TOTUL e aprobat
        if (!CarteiImobilAccess::isApprover($user) && !CarteiImobilAccess::isGlobalAdmin($user)) {
            $allApproved = $occupants->every(fn(Occupant $o) => $o->status === 'approved');
            if (!$allApproved) {
                return $this->error('Exportul PDF este disponibil doar după aprobare.', 403);
            }
        }

        $apartment->loadMissing('tenant');
        $tenantName = $apartment->tenant?->name ?? 'Asociație';
        $tenantAddress = $apartment->tenant?->address ?? '';

        $pdf = new Fpdf('P', 'mm', 'A4');
        $pdf->SetCompression(true);
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();
        $pdf->SetFont('Helvetica', 'B', 14);
        $pdf->Cell(190, 8, 'Carte de imobil - evidența persoanelor', 0, 1, 'C');
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->Cell(190, 6, $tenantName, 0, 1, 'L');
        if ($tenantAddress !== '') {
            $pdf->Cell(190, 6, $tenantAddress, 0, 1, 'L');
        }
        $pdf->Ln(2);
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->Cell(190, 7, 'Apartament: ' . $apartment->number . ($apartment->staircase ? (' | Scara: ' . $apartment->staircase) : '') . ($apartment->floor ? (' | Etaj: ' . $apartment->floor) : ''), 0, 1, 'L');
        $pdf->Ln(2);

        // Header tabel
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->Cell(40, 7, 'Nume', 1, 0, 'L');
        $pdf->Cell(22, 7, 'CNP', 1, 0, 'L');
        $pdf->Cell(28, 7, 'CI', 1, 0, 'L');
        $pdf->Cell(35, 7, 'Calitate', 1, 0, 'L');
        $pdf->Cell(22, 7, 'Sosire', 1, 0, 'L');
        $pdf->Cell(22, 7, 'Plec.', 1, 0, 'L');
        $pdf->Cell(21, 7, 'Status', 1, 1, 'L');

        $pdf->SetFont('Helvetica', '', 8);
        foreach ($occupants as $o) {
            $name = trim($o->last_name . ' ' . $o->first_name);
            $cnp = (string) ($o->cnp ?? '');
            $ci = trim((string) ($o->id_series ?? '') . ' ' . (string) ($o->id_number ?? ''));
            $role = $o->role_in_unit === 'other' ? ('altul: ' . (string) $o->other_role_text) : $o->role_in_unit;
            $in = optional($o->move_in_date)->format('Y-m-d') ?? '';
            $out = optional($o->move_out_date)->format('Y-m-d') ?? '';

            $pdf->Cell(40, 6, $name, 1, 0, 'L');
            $pdf->Cell(22, 6, $cnp, 1, 0, 'L');
            $pdf->Cell(28, 6, $ci, 1, 0, 'L');
            $pdf->Cell(35, 6, $role, 1, 0, 'L');
            $pdf->Cell(22, 6, $in, 1, 0, 'L');
            $pdf->Cell(22, 6, $out, 1, 0, 'L');
            $pdf->Cell(21, 6, $o->status, 1, 1, 'L');
        }

        $pdf->Ln(4);
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->MultiCell(190, 5, "Declarație: Subsemnatul declar pe propria răspundere că datele de mai sus sunt reale și complete.\nData generării: " . now()->format('Y-m-d H:i:s'), 0);

        // Audit exported (per persoană)
        DB::transaction(function () use ($occupants, $request, $audit) {
            foreach ($occupants as $o) {
                $audit->log($o, 'exported', $request, null, null);
            }
        });

        $content = $pdf->Output('S');
        $filename = 'carte-imobil-ap-' . $apartment->number . '-' . now()->format('Ymd_His') . '.pdf';

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}

