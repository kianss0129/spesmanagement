<?php


namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;


class EmployerRegisterController extends Controller
{
    // Show registration page
    public function create()
    {
        return Inertia::render('Auth/RegisterEmployer');
    }


    // Handle registration form submission
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email'         => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'      => ['required', 'confirmed', 'min:8'],
        ]);

        // Derive temporary name from email prefix
        $tempName = ucfirst(explode('@', $validated['email'])[0]);
        $user = null;

        DB::transaction(function () use ($validated, &$user, $tempName) {

            // 1. Create user
            $user = User::create([
                'name'     => $tempName,
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // 2. Assign Employer role
            $role = Role::firstOrCreate(['name' => 'Employer']);
            $user->assignRole($role);

            // 3. Create employer profile with pending company name
            $user->employer()->create([
                'company_name'            => 'Pending Company Profile',
                'phone'                   => null,
                'address'                 => null,
                'onboarding_completed_at' => null,
                'approved'                => false,
            ]);

            // 4. Send Laravel verification email
            event(new Registered($user));
        });

        try {
            activity()
                ->causedBy($user)
                ->performedOn($user)
                ->withProperties([
                    'module' => 'Employer',
                    'user_id' => $user->id,
                    'status' => 'registered',
                ])
                ->log('Employer registered account');
        } catch (\Throwable $e) {
            report($e);
        }

        // 5. Keep the user authenticated so the verification notice is accessible
        Auth::login($user);

        // 6. Send the user to the verification notice and let them confirm their Gmail address
        return redirect()->route('verification.notice');
    }


    private function normalizeText(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value));
    }


    private function normalizeName(string $value): string
    {
        return mb_convert_case($this->normalizeText($value), MB_CASE_TITLE, 'UTF-8');
    }
}

