<?php

return [
    /**
     * Dacă e true, CNP devine obligatoriu la validare (FormRequest).
     */
    'require_cnp' => env('REQUIRE_CNP', false),

    /**
     * Dacă e true, pentru rol "locatar" mascăm CNP/CI în API/UI.
     * Admin/comitet vede integral.
     */
    'mask_cnp_for_resident' => env('MASK_CNP_FOR_RESIDENT', true),
];

