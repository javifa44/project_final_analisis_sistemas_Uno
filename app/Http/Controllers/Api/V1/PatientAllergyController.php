<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientAllergy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatientAllergyController extends Controller
{
    public function index(Patient $patient): JsonResponse
    {
        $allergies = $patient->allergies()
            ->orderByDesc('active')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'message' => 'Patient allergies retrieved successfully.',
            'data' => $allergies,
            'has_active_allergies' => $allergies->where('active', true)->isNotEmpty(),
        ]);
    }

    public function store(Request $request, Patient $patient): JsonResponse
    {
        $validated = $request->validate([
            'allergy_name' => ['required', 'string', 'max:150'],
            'allergy_type' => ['required', Rule::in(['medication', 'food', 'environmental', 'other'])],
            'severity' => ['required', Rule::in(['mild', 'moderate', 'severe'])],
            'reaction' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'active' => ['sometimes', 'boolean'],
        ]);

        $allergy = $patient->allergies()->create($validated);

        return response()->json([
            'message' => 'Patient allergy created successfully.',
            'data' => $allergy,
        ], 201);
    }

    public function show(PatientAllergy $allergy): JsonResponse
    {
        return response()->json([
            'message' => 'Patient allergy retrieved successfully.',
            'data' => $allergy->load('patient'),
        ]);
    }

    public function update(Request $request, PatientAllergy $allergy): JsonResponse
    {
        $validated = $request->validate([
            'allergy_name' => ['sometimes', 'required', 'string', 'max:150'],
            'allergy_type' => ['sometimes', 'required', Rule::in(['medication', 'food', 'environmental', 'other'])],
            'severity' => ['sometimes', 'required', Rule::in(['mild', 'moderate', 'severe'])],
            'reaction' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'active' => ['sometimes', 'boolean'],
        ]);

        $allergy->update($validated);

        return response()->json([
            'message' => 'Patient allergy updated successfully.',
            'data' => $allergy,
        ]);
    }

    public function destroy(PatientAllergy $allergy): JsonResponse
    {
        $allergy->update([
            'active' => false,
        ]);

        return response()->json([
            'message' => 'Patient allergy deactivated successfully.',
            'data' => $allergy,
        ]);
    }
}
