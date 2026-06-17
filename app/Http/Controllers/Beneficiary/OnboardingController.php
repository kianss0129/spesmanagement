<?php


namespace App\Http\Controllers\Beneficiary;


use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Beneficiary;
use App\Models\Employer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;


class OnboardingController extends Controller
{
    private function normalizeFamilyIncome(string $value): string
    {
        $normalized = trim($value);
        $normalized = preg_replace('/\s+/u', ' ', $normalized);
        $normalized = str_replace(["\u{00a0}", "\u{2007}", "\u{202f}"], ' ', $normalized);
        $lower = mb_strtolower($normalized, 'UTF-8');


        $belowGroup = str_contains($lower, 'below')
            || str_contains($lower, 'under')
            || str_contains($lower, 'less than')
            || str_contains($lower, '5000 and below')
            || (str_contains($lower, '5000') && ! str_contains($lower, '5001') && ! str_contains($lower, '10001'));


        if ($belowGroup) {
            return 'Below ₱5,000';
        }


        if (str_contains($lower, '5001') || str_contains($lower, '5 001') || str_contains($lower, '5,001')) {
            return '₱5,001–₱10,000';
        }


        if (str_contains($lower, '10001') || str_contains($lower, '10 001') || str_contains($lower, '10,001') || str_contains($lower, '10000')) {
            return '₱10,001–₱15,000';
        }


        if (str_contains($lower, 'above') || str_contains($lower, 'over') || str_contains($lower, 'above 15000') || str_contains($lower, '15000')) {
            return 'Above ₱15,000';
        }


        return $normalized;
    }


    private function normalizeText(string $value): string
    {
        $normalized = trim(preg_replace('/\s+/u', ' ', $value));
        return mb_convert_case($normalized, MB_CASE_TITLE, 'UTF-8');
    }


    private function normalizeName(?string $value): ?string
    {
        return $value === null ? null : $this->normalizeText($value);
    }


    private function filterBeneficiaryColumns(array $data): array
    {
        $columns = array_flip(Schema::getColumnListing('beneficiaries'));


        return array_intersect_key($data, $columns);
    }


    private function buildDraftPayload(Request $request, ?int $schoolId = null): array
    {
        return $this->filterBeneficiaryColumns([
            'first_name' => $this->normalizeName($request->input('first_name')),
            'middle_name' => $this->normalizeName($request->input('middle_name')),
            'last_name' => $this->normalizeName($request->input('last_name')),
            'suffix' => $this->normalizeName($request->input('suffix')),
            'email' => $request->input('email', auth()->user()->email),
            'phone' => $request->input('contact_number'),
            'contact_number' => $request->input('contact_number'),
            'birthdate' => $request->input('birth_date'),
            'birth_date' => $request->input('birth_date'),
            'age' => (int) $request->input('age', 0),
            'sex' => $request->input('sex'),
            'gender' => $request->input('sex'),
            'civil_status' => $request->input('civil_status'),
            'place_of_birth' => $request->input('place_of_birth'),
            'citizenship' => $request->input('citizenship'),
            'facebook_account' => $request->input('facebook_account'),
            'category' => $request->input('category'),
            'present_address' => $request->input('present_address'),
            'barangay' => $request->input('barangay'),
            'city' => $request->input('city'),
            'province' => $request->input('province'),
            'father_name' => $request->input('father_name'),
            'father_contact' => $request->input('father_contact'),
            'father_occupation' => $request->input('father_occupation'),
            'mother_name' => $request->input('mother_name'),
            'mother_contact' => $request->input('mother_contact'),
            'mother_occupation' => $request->input('mother_occupation'),
            'family_income' => $this->normalizeFamilyIncome((string) $request->input('family_income', '')),
            'school_id' => $schoolId,
            'school_name' => $request->input('school_name'),
            'school_address' => $request->input('school_address'),
            'education_level' => $request->input('education_level'),
            'school_year' => $request->input('school_year'),
            'year_level' => $request->input('year_level'),
            'course' => $request->input('course'),
            'last_school_attended' => $request->input('last_school_attended'),
            'highest_attainment' => $request->input('highest_attainment'),
            'year_last_attended' => $request->input('year_last_attended'),
            'parent_guardian_name' => $request->input('parent_guardian_name'),
            'relationship' => $request->input('relationship'),
            'displacement_reason' => $request->input('displacement_reason'),
            'former_employer' => $request->input('former_employer'),
            'displacement_date' => $request->input('displacement_date'),
            'previous_spes' => filter_var($request->input('previous_spes', false), FILTER_VALIDATE_BOOLEAN),
            'spes_count' => $request->input('spes_count'),
            'status' => 'draft',
            'draft_status' => 'saved',
            'approval_status' => $request->input('approval_status', 'pending'),
            'onboarding_step' => (int) $request->input('onboarding_step', 1),
            'completion_percentage' => (int) $request->input('completion_percentage', 0),
            'completed_steps' => $request->input('completed_steps', []),
        ]);
    }


    public function index(Request $request)
    {
        $user = Auth::user();
        $beneficiary = $user->beneficiary;


        if (
            $user->hasRole('Beneficiary') &&
            optional($beneficiary)->approval_status === 'approved' &&
            ! request()->routeIs('onboarding.pending')
        ) {
            return redirect()->route('dashboard');
        }


        if ($user->hasRole('Employer')) {
            $employer = $user->employer;


            if (optional($employer)->approval_status === 'approved') {
                return redirect()->route('employer.dashboard');
            }


            return inertia('Onboarding/Employer', [
                'user' => $user,
                'employer' => $employer,
                'category' => 'employer',
            ]);
        }


        return inertia('Beneficiary/SpesOnboarding', [
            'user' => $user,
            'beneficiary' => $beneficiary,
            'category' => $request->query('category', $user->beneficiary_type ?? $beneficiary?->category ?? 'student'),
        ]);
    }


    public function step1(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);


        $user->update($validated);


        $beneficiary = $user->beneficiary ?? Beneficiary::create($this->filterBeneficiaryColumns([
            'user_id' => $user->id,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => 'draft',
        ]));


        $beneficiary->update($this->filterBeneficiaryColumns([
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? $beneficiary->phone,
        ]));


        return response()->json(['message' => 'Step 1 saved']);
    }


    public function step2(Request $request)
    {
        $beneficiary = Auth::user()->beneficiary;
        abort_if(! $beneficiary, 404);


        $category = $request->input('category');
        $rules = match ($category) {
            'student' => ['school' => 'required|string|max:255'],
            'osy' => ['skills' => 'required|string|max:255'],
            'dependent' => ['parentName' => 'required|string|max:255'],
            default => [],
        };


        $validated = $request->validate($rules);


        if ($category === 'student' && isset($validated['school'])) {
            $schoolName = trim($validated['school']);
            $school = \App\Models\School::firstOrCreate(['name' => $schoolName]);
            $beneficiary->update($this->filterBeneficiaryColumns(['school_id' => $school->id]));
        } else {
            $beneficiary->update($this->filterBeneficiaryColumns($validated));
        }


        return response()->json(['message' => 'Step 2 saved']);
    }


    public function upload(Request $request)
    {
        $user = Auth::user();
        $requirementFields = [
            'valid_id',
            'school_enrollment',
            'barangay_certificate',
            'birth_certificate',
            'school_record',
            'income_proof',
            'osy_certificate',
            'displacement_certificate',
            'displacement_proof',
            'termination_notice',
            'parent_valid_id',
        ];

        $rules = [
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:1024000',
            'files.*' => 'file|mimes:pdf,jpg,jpeg,png|max:1024000',
        ];

        foreach ($requirementFields as $field) {
            $rules[$field] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:1024000';
        }

        $request->validate($rules);


        $incomingFiles = [];
        $namedFiles = [];


        if ($request->hasFile('documents')) {
            $incomingFiles = array_merge($incomingFiles, (array) $request->file('documents'));
        }


        if ($request->hasFile('files')) {
            $incomingFiles = array_merge($incomingFiles, (array) $request->file('files'));
        }


        foreach ($requirementFields as $field) {
            if ($request->hasFile($field)) {
                $namedFiles[$field] = $request->file($field);
            }
        }


        if (count($incomingFiles) === 0 && count($namedFiles) === 0) {
            return response()->json([
                'message' => 'Please choose at least one document to upload.',
            ], 422);
        }


        $uploadedDocs = [];
        foreach ($incomingFiles as $file) {
            if (! $file) {
                continue;
            }


            $fileExtension = strtolower($file->getClientOriginalExtension());
            $videoExtensions = ['mp4', 'avi', 'mov', 'mkv', 'flv', 'wmv', 'webm', 'm4v'];


            if (in_array($fileExtension, $videoExtensions, true)) {
                return response()->json([
                    'error' => "❌ Video files ({$file->getClientOriginalName()}) are not allowed. Please upload only PDF, JPG, JPEG, or PNG files.",
                    'success' => false,
                ], 422);
            }


            $storedPath = $file->store('documents/users/' . $user->id, 'public');
            if ($storedPath) {
                $uploadedDocs[] = [
                    'path' => $storedPath,
                    'name' => $file->getClientOriginalName(),
                    'uploaded_at' => now()->toIso8601String(),
                ];
            }
        }


        $beneficiary = $user->beneficiary;
        abort_if(! $beneficiary, 404);


        $existingDocs = is_array($beneficiary->documents)
            ? $beneficiary->documents
            : ($beneficiary->documents ? [$beneficiary->documents] : []);


        foreach ($namedFiles as $field => $file) {
            if (! $file) {
                continue;
            }

            $storedPath = $file->store('documents/users/' . $user->id, 'public');
            if ($storedPath) {
                $isReplacement = isset($existingDocs[$field]) && is_array($existingDocs[$field]) && !empty($existingDocs[$field]['path']);

                $existingDocs[$field] = [
                    'path' => $storedPath,
                    'name' => $file->getClientOriginalName(),
                    'status' => 'under_review',
                    'uploaded_at' => now()->toIso8601String(),
                    'remarks' => null,
                    'resubmitted' => $isReplacement,
                    'resubmitted_at' => $isReplacement ? now()->toIso8601String() : null,
                ];
            }
        }


        $beneficiary->documents = array_merge($existingDocs, $uploadedDocs);
        $beneficiary->save();

        try {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($beneficiary)
                ->withProperties([
                    'module' => 'Requirements',
                    'user_id' => $user->id,
                    'status' => 'uploaded',
                ])
                ->log('Beneficiary uploaded requirement');
        } catch (\Throwable $e) {
            report($e);
        }


        return response()->json([
            'message' => 'Documents uploaded',
            'documents' => $beneficiary->documents,
        ]);
    }


    public function saveDraft(Request $request)
    {
        $user = Auth::user();


        $schoolId = null;
        if (($request->input('category') === 'student') && ! empty($request->input('school_name'))) {
            $school = \App\Models\School::firstOrCreate([
                'name' => trim($request->input('school_name')),
            ], [
                'name' => trim($request->input('school_name')),
            ]);
            $schoolId = $school->id;
        }


        $firstName = $this->normalizeName($request->input('first_name'));
        $middleName = $this->normalizeName($request->input('middle_name'));
        $lastName = $this->normalizeName($request->input('last_name'));
        $suffix = $this->normalizeName($request->input('suffix'));


        $fullName = trim(implode(' ', array_filter([
            $firstName,
            $middleName,
            $lastName,
            $suffix,
        ])));


        $user->update([
            'name' => $fullName,
            'email' => $request->input('email', $user->email),
            'beneficiary_type' => $request->input('category', $user->beneficiary_type),
        ]);


        $beneficiary = $user->beneficiary ?? Beneficiary::create($this->filterBeneficiaryColumns([
            'user_id' => $user->id,
            'email' => $request->input('email', $user->email),
            'status' => 'draft',
            'approval_status' => 'pending',
        ]));


        $payload = $this->buildDraftPayload($request, $schoolId);


        if ($beneficiary->draft_status === 'submitted') {
            unset(
                $payload['completion_percentage'],
                $payload['completed_steps'],
                $payload['onboarding_step']
            );
        }


        $beneficiary->update($payload);


        return response()->json([
            'message' => 'Draft saved',
            'onboarding_step' => (int) $request->input('onboarding_step', 1),
            'completion_percentage' => (int) $request->input('completion_percentage', 0),
        ]);
    }


    public function submit(Request $request)
    {
        $user = Auth::user();
        $isEmployerOnboarding = $user->hasRole('Employer') || $request->input('category') === 'employer';


        if ($isEmployerOnboarding) {
            $validated = $request->validate([
                'company_name' => 'required|string|max:255',
                'business_trade_name' => 'nullable|string|max:255',
                'nature_of_business' => 'required|string|max:255',
                'industry_type' => 'required|string|max:255',
                'sector' => 'required|string|max:255',
                'company_website' => 'nullable|url|max:255',
                'facebook_page' => 'nullable|url|max:255',
                'number_of_employees' => 'nullable|integer|min:1',
                'available_spes_slots' => 'required|integer|min:1',
                'house_number' => 'required|string|max:255',
                'street' => 'required|string|max:255',
                'barangay' => 'required|string|in:Alasas,Baliti,Bulaon,Calulut,Dela Paz Norte,Dela Paz Sur,Del Carmen,Del Pilar,Del Rosario,Dolores,Juliana,Lara,Lourdes,Magliman,Maimpis,Malino,Malpitic,Pandaras,Panipuan,Pulung Bulu,Quebiawan,Saguin,San Agustin,San Felipe,San Isidro,San Jose,San Juan,San Nicolas,San Pedro,Santa Lucia,Santa Teresita,Santo Niño,Santo Rosario,Sindalan,Telabastagan',
                'city' => 'required|string|in:San Fernando',
                'province' => 'required|string|in:Pampanga',
                'zip_code' => 'nullable|string|max:20',
                'auth_first_name' => 'required|string|max:255',
                'auth_middle_name' => 'nullable|string|max:255',
                'auth_last_name' => 'required|string|max:255',
                'auth_suffix' => 'nullable|string|max:50',
                'auth_position' => 'required|string|max:255',
                'auth_mobile' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
                'auth_email' => 'required|email|max:255',
                'contact_first_name' => 'required|string|max:255',
                'contact_middle_name' => 'nullable|string|max:255',
                'contact_last_name' => 'required|string|max:255',
                'contact_suffix' => 'nullable|string|max:50',
                'contact_position' => 'required|string|max:255',
                'contact_mobile' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
                'contact_email' => 'required|email|max:255',
                'finance_first_name' => 'nullable|string|max:255',
                'finance_middle_name' => 'nullable|string|max:255',
                'finance_last_name' => 'nullable|string|max:255',
                'finance_suffix' => 'nullable|string|max:50',
                'finance_position' => 'nullable|string|max:255',
                'finance_mobile' => ['nullable', 'string', 'regex:/^[0-9]{10}$/'],
                'finance_email' => 'nullable|email|max:255',
                'company_phone' => 'nullable|string|max:20',
                'company_mobile' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
                'company_email_official' => 'required|email|max:255',
                'alternative_email' => 'nullable|email|max:255',
                'previous_participation' => 'required|in:Yes,No',
                'years_participated' => 'nullable|integer|min:0',
                'preferred_beneficiaries' => 'required|integer|min:1',
                'preferred_department' => 'nullable|string|max:255',
                'employment_period' => 'nullable|string|max:255',
                'work_schedules' => 'nullable|string|max:1000',
                'work_assignments' => 'nullable|string|max:1000',
                'position_title' => 'required|string|max:255',
                'number_of_vacancies' => 'required|integer|min:1',
                'minimum_qualification' => 'nullable|string|max:255',
                'assigned_department' => 'nullable|string|max:255',
                'work_schedule' => 'nullable|string|max:255',
                'expected_duration' => 'nullable|string|max:255',
                'business_permit' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048000',
                'mayors_permit' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048000',
                'registration_certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048000',
                'bir_certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048000',
                'letter_of_intent' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048000',
                'supporting_documents' => 'nullable|array',
                'supporting_documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048000',
                'declaration' => 'required|accepted',
            ]);


            $existingDocs = [];
            $employer = $user->employer;


            if ($employer) {
                $existingDocs = is_array($employer->documents) ? $employer->documents : ($employer->documents ? [$employer->documents] : []);
            }


            $uploadedDocs = [];
            foreach (['business_permit', 'mayors_permit', 'registration_certificate', 'bir_certificate', 'letter_of_intent'] as $field) {
                $file = $request->file($field);
                if (! $file) {
                    continue;
                }


                $storedPath = $file->store('documents/employers/' . $user->id, 'public');
                if ($storedPath) {
                    $uploadedDocs[] = [
                        'type' => $field,
                        'path' => $storedPath,
                        'name' => $file->getClientOriginalName(),
                        'uploaded_at' => now()->toIso8601String(),
                    ];
                }
            }


            foreach ((array) $request->file('supporting_documents') as $file) {
                if (! $file) {
                    continue;
                }


                $storedPath = $file->store('documents/employers/' . $user->id, 'public');
                if ($storedPath) {
                    $uploadedDocs[] = [
                        'type' => 'supporting_document',
                        'path' => $storedPath,
                        'name' => $file->getClientOriginalName(),
                        'uploaded_at' => now()->toIso8601String(),
                    ];
                }
            }


            $details = [
                'business_trade_name' => $validated['business_trade_name'] ?? null,
                'nature_of_business' => $validated['nature_of_business'],
                'industry_type' => $validated['industry_type'],
                'sector' => $validated['sector'],
                'company_website' => $validated['company_website'] ?? null,
                'facebook_page' => $validated['facebook_page'] ?? null,
                'number_of_employees' => $validated['number_of_employees'] ?? null,
                'available_spes_slots' => $validated['available_spes_slots'],
                'house_number' => $validated['house_number'],
                'street' => $validated['street'],
                'barangay' => $validated['barangay'],
                'city' => $validated['city'],
                'province' => $validated['province'],
                'zip_code' => $validated['zip_code'] ?? null,
                'authorized_representative' => [
                    'first_name' => $validated['auth_first_name'],
                    'middle_name' => $validated['auth_middle_name'] ?? null,
                    'last_name' => $validated['auth_last_name'],
                    'suffix' => $validated['auth_suffix'] ?? null,
                    'position' => $validated['auth_position'],
                    'mobile' => $validated['auth_mobile'],
                    'email' => $validated['auth_email'],
                ],
                'contact_person' => [
                    'first_name' => $validated['contact_first_name'],
                    'middle_name' => $validated['contact_middle_name'] ?? null,
                    'last_name' => $validated['contact_last_name'],
                    'suffix' => $validated['contact_suffix'] ?? null,
                    'position' => $validated['contact_position'],
                    'mobile' => $validated['contact_mobile'],
                    'email' => $validated['contact_email'],
                ],
                'finance_officer' => [
                    'first_name' => $validated['finance_first_name'] ?? null,
                    'middle_name' => $validated['finance_middle_name'] ?? null,
                    'last_name' => $validated['finance_last_name'] ?? null,
                    'suffix' => $validated['finance_suffix'] ?? null,
                    'position' => $validated['finance_position'] ?? null,
                    'mobile' => $validated['finance_mobile'] ?? null,
                    'email' => $validated['finance_email'] ?? null,
                ],
                'company_contact' => [
                    'telephone_number' => $validated['company_phone'] ?? null,
                    'mobile_number' => $validated['company_mobile'],
                    'official_company_email' => $validated['company_email_official'],
                    'alternative_email' => $validated['alternative_email'] ?? null,
                ],
                'spes_participation' => [
                    'previous_participation' => $validated['previous_participation'],
                    'years_participated' => $validated['years_participated'] ?? null,
                    'preferred_beneficiaries' => $validated['preferred_beneficiaries'],
                    'preferred_department' => $validated['preferred_department'] ?? null,
                    'employment_period' => $validated['employment_period'] ?? null,
                    'work_schedules' => $validated['work_schedules'] ?? null,
                    'work_assignments' => $validated['work_assignments'] ?? null,
                ],
                'employment_opportunities' => [
                    'position_title' => $validated['position_title'],
                    'number_of_vacancies' => $validated['number_of_vacancies'],
                    'minimum_qualification' => $validated['minimum_qualification'] ?? null,
                    'assigned_department' => $validated['assigned_department'] ?? null,
                    'work_schedule' => $validated['work_schedule'] ?? null,
                    'expected_duration' => $validated['expected_duration'] ?? null,
                ],
            ];


            $addressParts = array_filter([
                $validated['house_number'],
                $validated['street'],
                $validated['barangay'],
                $validated['city'],
                $validated['province'],
                $validated['zip_code'] ?? null,
            ]);


            $contactPerson = trim(implode(' ', array_filter([
                $validated['contact_first_name'],
                $validated['contact_middle_name'] ?? null,
                $validated['contact_last_name'],
                $validated['contact_suffix'] ?? null,
            ])));


            $employerData = [
                'company_name' => $validated['company_name'],
                'email' => $validated['company_email_official'],
                'phone' => $validated['company_phone'] ?? $validated['company_mobile'],
                'contact_person' => $contactPerson,
                'address' => implode(', ', $addressParts),
                'documents' => array_merge($existingDocs, $uploadedDocs),
                'approved' => false,
                'approval_status' => 'pending',
                'status' => 'pending',
                'onboarding_completed_at' => now(),
            ];


            if (Schema::hasColumn('employers', 'details')) {
                $employerData['details'] = $details;
            }


            $employer = Employer::updateOrCreate(
                ['user_id' => $user->id],
                $employerData
            );


            return redirect()->route('employer.dashboard')->with('success', 'Your employer onboarding application has been submitted and is awaiting approval.');
        }


        $allowedFamilyIncomes = ['Below ₱5,000', '₱5,001–₱10,000', '₱10,001–₱15,000', 'Above ₱15,000'];


        $request->merge([
            'family_income' => $this->normalizeFamilyIncome((string) $request->input('family_income', '')),
        ]);

        // Check if beneficiary already has uploaded documents (for re-submission / update flow)
        $beneficiary = $user->beneficiary;
        $existingDocs = [];
        if ($beneficiary && is_array($beneficiary->documents)) {
            foreach ($beneficiary->documents as $doc) {
                if (isset($doc['type'])) {
                    $existingDocs[$doc['type']] = true;
                }
            }
        }

        // Documents are only required if they haven't been uploaded previously
        // valid_id is NOT required for 'dependent' category
        $category = $request->input('category', 'student');

        $validIdRule = (!empty($existingDocs['valid_id']))
            ? 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048000'
            : ($category === 'dependent'
                ? 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048000'
                : 'required|file|mimes:pdf,jpg,jpeg,png|max:2048000');

        $schoolEnrollmentRule = (!empty($existingDocs['school_enrollment']))
            ? 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048000'
            : 'required_if:category,student|nullable|file|mimes:pdf,jpg,jpeg,png|max:2048000';

        $barangayCertRule = (!empty($existingDocs['barangay_certificate']))
            ? 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048000'
            : ($category === 'dependent'
                ? 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048000'
                : 'required|file|mimes:pdf,jpg,jpeg,png|max:2048000');

        $rules = [
            'valid_id' => $validIdRule,
            'school_enrollment' => $schoolEnrollmentRule,
            'barangay_certificate' => $barangayCertRule,
            'birth_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048000',
            'osy_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048000',
            'income_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048000',
            'displacement_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048000',
            'parent_valid_id' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048000',
            'supporting_documents' => 'nullable|array',
            'supporting_documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048000',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'birth_date' => 'required|date',
            'age' => 'required|integer|min:1',
            'sex' => 'required|in:Male,Female',
            'civil_status' => 'required|in:Single,Married,Widowed,Separated',
            'place_of_birth' => 'required|string|max:255',
            'citizenship' => 'required|string|max:100',
            'contact_number' => 'required|string|min:10|max:20',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'facebook_account' => 'nullable|string|max:255',
            'category' => 'required|in:student,osy,dependent',
            'present_address' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'father_contact' => 'nullable|string|max:50',
            'father_occupation' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'mother_contact' => 'nullable|string|max:50',
            'mother_occupation' => 'nullable|string|max:255',
            'family_income' => 'required|string',
            'skills' => 'nullable|array',
            'skills.*' => 'integer|exists:skills,id',
           
            //Student
            'school_name' => 'required_if:category,student|string|max:255',
            'school_address' => 'required_if:category,student|string|max:255',
            'education_level' => 'required_if:category,student|in:Junior High School,Senior High School,College,Vocational,Technical/Vocational',
            'school_year' => 'required_if:category,student|string|max:50',
            'year_level' => 'required_if:category,student|string|max:50',
            'course' => 'nullable|string|max:255',


            //OSY
            'last_school_attended' => 'nullable|required_if:category,osy|string|max:255',
            'highest_attainment' => 'nullable|required_if:category,osy|string|max:255',
            'year_last_attended' => 'nullable|required_if:category,osy|string|max:50',


            //Displace
            'parent_guardian_name' => 'nullable|required_if:category,dependent|string|max:255',
            'relationship' => 'nullable|required_if:category,dependent|string|max:255',
            'displacement_reason' => 'nullable|required_if:category,dependent|string|max:255',


            //Employer
            'former_employer' => 'nullable|string|max:255',
            'displacement_date' => 'nullable|date',
            'previous_spes' => 'required|in:Yes,No',
            'spes_count' => 'nullable|integer|min:1',
            'declaration' => 'required|accepted',
        ];


        $validated = $request->validate($rules);


        if (! in_array($validated['family_income'], $allowedFamilyIncomes, true)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'family_income' => ['The selected family income is invalid.'],
            ]);
        }


        $validated['family_income'] = $this->normalizeFamilyIncome((string) $validated['family_income']);


        $uploadedDocs = [];
        
        // Process individual document uploads
        foreach (['valid_id', 'school_enrollment', 'barangay_certificate', 'birth_certificate', 'osy_certificate', 'income_proof', 'displacement_proof', 'parent_valid_id'] as $field) {
            $file = $request->file($field);
            if ($file) {
                $storedPath = $file->store('documents/beneficiaries/' . $user->id, 'public');
                if ($storedPath) {
                    $uploadedDocs[] = [
                        'type' => $field,
                        'path' => $storedPath,
                        'name' => $file->getClientOriginalName(),
                        'uploaded_at' => now()->toIso8601String(),
                    ];
                }
            }
        }
        
        // Process generic supporting documents if provided
        foreach ((array) $request->file('documents') as $file) {
            if (! $file) {
                continue;
            }

            $storedPath = $file->store('documents/beneficiaries/' . $user->id, 'public');
            if ($storedPath) {
                $uploadedDocs[] = [
                    'path' => $storedPath,
                    'name' => $file->getClientOriginalName(),
                    'uploaded_at' => now()->toIso8601String(),
                ];
            }
        }


        $firstName = $this->normalizeName($validated['first_name']);
        $middleName = $this->normalizeName($validated['middle_name'] ?? '');
        $lastName = $this->normalizeName($validated['last_name']);
        $suffix = $this->normalizeName($validated['suffix'] ?? '');


        $fullName = trim(implode(' ', array_filter([
            $firstName,
            $middleName,
            $lastName,
            $suffix,
        ])));


        $user->update([
            'name' => $fullName,
            'email' => $validated['email'],
            'beneficiary_type' => $validated['category'],
        ]);


        $beneficiary = $user->beneficiary ?? Beneficiary::create(
        $this->filterBeneficiaryColumns([
            'user_id' => $user->id,
            'first_name' => $firstName ?? '',
            'middle_name' => $middleName ?? '',
            'last_name' => $lastName ?? '',
            'suffix' => $suffix ?? '',
            'email' => $validated['email'],
            'status' => 'draft',
        ])
    );


        $schoolId = null;
        if ($validated['category'] === 'student' && ! empty($validated['school_name'])) {
            $school = \App\Models\School::firstOrCreate(['name' => trim($validated['school_name'])], ['name' => trim($validated['school_name'])]);
            $schoolId = $school->id;
        }


        $existingDocs = is_array($beneficiary->documents)
            ? $beneficiary->documents
            : ($beneficiary->documents ? [$beneficiary->documents] : []);


        $beneficiary->update($this->filterBeneficiaryColumns([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'suffix' => $validated['suffix'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['contact_number'],
            'contact_number' => $validated['contact_number'],
            'birth_date' => $validated['birth_date'],
            'age' => (int) $validated['age'],
            'sex' => $validated['sex'],
            'civil_status' => $validated['civil_status'],
            'place_of_birth' => $validated['place_of_birth'],
            'citizenship' => $validated['citizenship'],
            'facebook_account' => $validated['facebook_account'] ?? null,
            'category' => $validated['category'],
            'present_address' => $validated['present_address'],
            'barangay' => $validated['barangay'],
            'city' => $validated['city'],
            'province' => $validated['province'],
            'father_name' => $validated['father_name'] ?? null,
            'father_contact' => $validated['father_contact'] ?? null,
            'father_occupation' => $validated['father_occupation'] ?? null,
            'mother_name' => $validated['mother_name'] ?? null,
            'mother_contact' => $validated['mother_contact'] ?? null,
            'mother_occupation' => $validated['mother_occupation'] ?? null,
            'family_income' => $validated['family_income'],
            'school_id' => $schoolId,
            'school_name' => $validated['school_name'] ?? null,
            'school_address' => $validated['school_address'] ?? null,
            'education_level' => $validated['education_level'] ?? null,
            'school_year' => $validated['school_year'] ?? null,
            'year_level' => $validated['year_level'] ?? null,
            'course' => $validated['course'] ?? null,
            'last_school_attended' => $validated['last_school_attended'] ?? null,
            'highest_attainment' => $validated['highest_attainment'] ?? null,
            'year_last_attended' => $validated['year_last_attended'] ?? null,
            'parent_guardian_name' => $validated['parent_guardian_name'] ?? null,
            'relationship' => $validated['relationship'] ?? null,
            'displacement_reason' => $validated['displacement_reason'] ?? null,
            'former_employer' => $validated['former_employer'] ?? null,
            'displacement_date' => $validated['displacement_date'] ?? null,
            'previous_spes' => $validated['previous_spes'] === 'Yes',
            'spes_count' => $validated['spes_count'] ?? null,
            'documents' => array_merge($existingDocs, $uploadedDocs),
            'status' => 'pending',
            'draft_status' => 'submitted',
            'approved' => false,
            'approval_status' => 'pending',
            'rejection_reason' => null,
            'submitted_at' => now(),
            'onboarding_step' => 5,
            'completion_percentage' => 100,
            'completed_steps' => [1, 2, 3, 4, 5],
            'onboarding_completed_at' => now(),
        ]));

        // Step 3: Save beneficiary skills using the pivot table
        $skillIds = $request->input('skills', []);
        if (! empty($skillIds)) {
            $beneficiary->skills()->sync($skillIds);
        } else {
            // Clear any existing skills if none provided
            $beneficiary->skills()->sync([]);
        }

        // Step 4: Create or update Application record — reset status to 'applied' on resubmission
        $application = Application::firstOrCreate(
            ['beneficiary_id' => $beneficiary->id],
            ['job_listing_id' => null, 'status' => 'applied']
        );

        $wasResubmission = ! $application->wasRecentlyCreated
            && in_array($application->status, ['needs_correction', 'rejected'], true);

        // If the application already existed (e.g., was 'needs_correction'), reset it to 'applied'
        if ($wasResubmission) {
            $application->update(['status' => 'applied']);
        }

        try {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($application)
                ->withProperties([
                    'module' => 'Beneficiary',
                    'user_id' => $user->id,
                    'status' => $application->fresh()?->status ?? $application->status,
                ])
                ->log('Beneficiary submitted SPES application');
        } catch (\Throwable $e) {
            report($e);
        }

        if ($wasResubmission) {
            try {
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($application)
                    ->withProperties([
                        'module' => 'Requirements',
                        'user_id' => $user->id,
                        'status' => 'resubmitted',
                    ])
                    ->log('Beneficiary resubmitted application requirements');
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('dashboard')->with('success', 'Your SPES application has been submitted successfully.');
    }
}
