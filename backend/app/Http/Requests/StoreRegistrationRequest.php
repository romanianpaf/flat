<?php

namespace App\Http\Requests;

use App\Http\Requests\ApiFormRequest;

class StoreRegistrationRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    public function rules(): array
    {
        return [
            'tenant_id' => 'required|exists:tenants,id',
            'apartment_id' => 'nullable|exists:apartments,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
                // Only block if there's a PENDING request with this email
                function ($attribute, $value, $fail) {
                    $pendingExists = \App\Models\RegistrationRequest::where('email', $value)
                        ->where('status', 'pending')
                        ->exists();
                    if ($pendingExists) {
                        $fail('Există deja o cerere în așteptare pentru acest email.');
                    }
                },
            ],
            'phone' => 'nullable|string|max:50',
            // Dovada de proprietar (poza contractului) este obligatorie.
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'tenant_id.required' => 'Te rog selectează asociația.',
            'tenant_id.exists' => 'Asociația selectată nu există.',
            'apartment_id.exists' => 'Apartamentul selectat nu există.',
            'first_name.required' => 'Prenumele este obligatoriu.',
            'first_name.max' => 'Prenumele nu poate avea mai mult de 255 caractere.',
            'last_name.required' => 'Numele este obligatoriu.',
            'last_name.max' => 'Numele nu poate avea mai mult de 255 caractere.',
            'email.required' => 'Emailul este obligatoriu.',
            'email.email' => 'Te rog introdu un email valid.',
            'email.unique' => 'Acest email este deja utilizat sau există o cerere în așteptare.',
            'phone.max' => 'Telefonul nu poate avea mai mult de 50 caractere.',
            'document.required' => 'Te rog atașează poza contractului de vânzare-cumpărare.',
            'document.file' => 'Documentul trebuie să fie un fișier.',
            'document.mimes' => 'Documentul trebuie să fie PDF sau imagine (JPG, PNG).',
            'document.max' => 'Documentul nu poate depăși 5MB.',
            'notes.max' => 'Mesajul nu poate avea mai mult de 1000 caractere.',
        ];
    }
}
