<?php


namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

use Inertia\Inertia;


class BeneficiaryRegisterController extends Controller
{
    // Show registration page
    public function create()
    {
        return Inertia::render('Auth/RegisterBeneficiary');
    }


    // Handle registration form submission
    public function store(Request $request)
    {
        $validated = $request->validate([
            
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'type' => ['required', 'in:student,osy,dependent'],
            'recaptcha' => ['required', 'string'],
        ]);

        // Verify reCAPTCHA token
        $recaptchaResponse = $this->verifyRecaptcha($validated['recaptcha']);
        if (!$recaptchaResponse['success']) {
            return redirect()->back()->withErrors(['recaptcha' => 'reCAPTCHA verification failed. Please try again.']);
        }

        
        
        $user = null;


        DB::transaction(function () use ($validated, &$user) {


            // 1. Create user
           $user = User::create([
    'name' => $validated['email'], // temporary value
    'email' => $validated['email'],
    'password' => Hash::make($validated['password']),
    'beneficiary_type' => $validated['type'],
]);


            // 2. Assign Beneficiary role
            $role = Role::firstOrCreate(['name' => 'Beneficiary']);
            $user->assignRole($role);


            // 3. Create beneficiary profile with parsed name pieces
        $user->beneficiary()->create([
    'first_name' => '',
    'middle_name' => '',
    'last_name' => '',
    'suffix' => null,
    'email' => $validated['email'],
    'approved' => false,
    'approval_status' => 'pending',
    'status' => 'pending',
]);

            // 4. Fire registered event (email verification if enabled)
            event(new Registered($user));
        });

        try {
            activity()
                ->causedBy($user)
                ->performedOn($user)
                ->withProperties([
                    'module' => 'Beneficiary',
                    'user_id' => $user->id,
                    'status' => 'registered',
                ])
                ->log('Beneficiary registered account');
        } catch (\Throwable $e) {
            report($e);
        }


        // 5. Keep the user authenticated so the verification notice is accessible
        Auth::login($user);


        // 6. Send the user to the verification notice and let them confirm their Gmail address
        return redirect()->route('verification.notice');
    }




   

    private function verifyRecaptcha(string $token): array
    {
        $secretKey = config('services.google.recaptcha_secret');
        
        if (!$secretKey) {
            return ['success' => true]; // Skip if secret not configured
        }

        try {
            $response = \Illuminate\Support\Facades\Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secretKey,
                'response' => $token,
            ]);

            $data = $response->json();
            
            // Consider verification successful if score is above 0.5 or it's just a checkbox
            return [
                'success' => $data['success'] ?? false,
                'score' => $data['score'] ?? 0,
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('reCAPTCHA verification error: ' . $e->getMessage());
            return ['success' => false];
        }
    }
}

