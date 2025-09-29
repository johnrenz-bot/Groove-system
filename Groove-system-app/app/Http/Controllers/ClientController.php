<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Coach;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use App\Notifications\VerifyEmail;   
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Models\Announcement;
use Carbon\Carbon;
use Illuminate\Validation\Rules\File;



use Illuminate\Support\Facades\Storage;



use App\Notifications\GrooveNotification;

class ClientController extends Controller
{

     public function Clientregister(Request $request)
{
    $request->session()->put('address_return', 'client');
    $selected = $request->session()->get('selected_address');
    $summary = $selected ? collect([
        $selected['street'] ?? null,
        $selected['barangay_name'] ?? null,
        $selected['city_name'] ?? null,
        $selected['province_name'] ?? null,
        $selected['region_name'] ?? null,
    ])->filter()->implode(', ') . (!empty($selected['postal_code']) ? ' — '.$selected['postal_code'] : '') : null;

    return view('Client.register', compact('selected','summary'));
}


    public function profile()
    {
        $client = auth('client')->user();
        $appointments = \App\Models\Appointment::where('client_id', $client->client_id)
            ->with('coach')->orderByDesc('date')->get();
        return view('client.profile', compact('client', 'appointments'));
    }

    public function profileedit()
    {
        $client = auth('client')->user();
        return view('client.profileEdit', compact('client'));
    }

 
     public function ClientStore(Request $request)
    {
        // Rehydrate address fields from session if present
        $selected = $request->session()->get('selected_address', []);
        $request->merge([
            'region_code'   => $request->input('region_code', data_get($selected, 'region_code')),
            'province_code' => $request->input('province_code', data_get($selected, 'province_code')),
            'city_code'     => $request->input('city_code', data_get($selected, 'city_code')),
            'barangay_code' => $request->input('barangay_code', data_get($selected, 'barangay_code')),
            'region_name'   => $request->input('region_name', data_get($selected, 'region_name')),
            'province_name' => $request->input('province_name', data_get($selected, 'province_name')),
            'city_name'     => $request->input('city_name', data_get($selected, 'city_name')),
            'barangay_name' => $request->input('barangay_name', data_get($selected, 'barangay_name')),
            'street'        => $request->input('street', data_get($selected, 'street')),
            'postal_code'   => $request->input('postal_code', data_get($selected, 'postal_code')),
        ]);

        $validated = $request->validate([
            'firstname'     => ['required','string','max:255','regex:/^[A-Za-zÀ-ÿ\s\'-]+$/u'],
            'middlename'    => ['nullable','string','max:255','regex:/^[A-Za-zÀ-ÿ\s\'-]+$/u'],
            'lastname'      => ['required','string','max:255','regex:/^[A-Za-zÀ-ÿ\s\'-]+$/u'],

            'birth_day'     => ['required','integer','min:1','max:31'],
            'birth_month'   => ['required','integer','min:1','max:12'],
            'birth_year'    => ['required','integer','min:1900','max:'.(now()->year - 13)],

            'region_code'   => ['required','string','max:9'],
            'province_code' => ['required','string','max:9'],
            'city_code'     => ['required','string','max:9'],
            'barangay_code' => ['required','string','max:9'],
            'region_name'   => ['required','string','max:255'],
            'province_name' => ['required','string','max:255'],
            'city_name'     => ['required','string','max:255'],
            'barangay_name' => ['required','string','max:255'],

            'street'        => ['nullable','string','max:120'],
            'postal_code'   => ['nullable','string','max:10'],

            'contact'       => ['required','regex:/^9\d{9}$/', Rule::unique('clients','contact')],
            'talent'        => ['nullable','string','max:255'],

            'email'         => [
                'required','email','max:255',
                'regex:/^[A-Za-z0-9._%+\-]+@gmail\.com$/i',
                Rule::unique('clients','email'),
                function ($attribute, $value, $fail) {
                    // Prevent duplicate with coaches (if model exists)
                    if (class_exists(\App\Models\Coach::class) && \App\Models\Coach::where('email', $value)->exists()) {
                        $fail('This email is already registered as a coach.');
                    }
                }
            ],

            'username'      => [
                'required','string','max:255',
                'regex:/^[A-Za-z0-9._-]{3,20}$/',
                Rule::unique('clients','username')
            ],

            'password'      => [
                'required','string','min:8','confirmed',
                'regex:/^(?=.*[A-Z])(?=.*[!@#$%^&*()_\-+=[\]{};:\'",.<>\/?`~\\|]).{8,}$/'
            ],

            'terms'         => ['accepted'],

            // Require uploaded ID (pdf/jpg/jpeg/png up to 5MB)
            'id_document'   => ['required', File::types(['pdf','jpg','jpeg','png'])->max(5 * 1024)],
        ], [
            'firstname.regex' => 'Firstname may only contain letters, spaces, apostrophes, and dashes.',
            'middlename.regex' => 'Middlename may only contain letters, spaces, apostrophes, and dashes.',
            'lastname.regex' => 'Lastname may only contain letters, spaces, apostrophes, and dashes.',
            'email.regex' => 'Email must be a valid @gmail.com address.',
            'username.regex' => 'Username must be 3–20 chars (letters, numbers, dot, underscore, dash).',
            'password.regex' => 'Password should be at least 8 characters, with an uppercase letter, and special character.',
            'contact.regex' => 'Contact must be a PH mobile number starting with 9 and 10 digits total.',
        ]);

        // Build + validate birthdate
        $birthDate = sprintf('%04d-%02d-%02d', $validated['birth_year'], $validated['birth_month'], $validated['birth_day']);
        if (!checkdate((int)$validated['birth_month'], (int)$validated['birth_day'], (int)$validated['birth_year'])) {
            return back()->withErrors(['birth_date' => 'Invalid birth date.'])->withInput();
        }
        $birth = \Carbon\Carbon::parse($birthDate);
        if ($birth->isFuture()) {
            return back()->withErrors(['birth_date' => 'Birthdate cannot be in the future.'])->withInput();
        }
        if ($birth->diffInYears(now()) < 13) {
            return back()->withErrors(['birth_date' => 'You must be at least 13 years old to register.'])->withInput();
        }

        // Pretty address summary
        $addressSummary = collect([
            $request->street,
            $request->barangay_name,
            $request->city_name,
            $request->province_name,
            $request->region_name,
        ])->filter()->implode(', ');

        // Store the ID file (public disk)
        $validIdPath = $request->file('id_document')->store('ids', 'public');

        // Unique 4-char client_id
        try {
            $clientId = $this->generateUniqueClientId();
        } catch (\Throwable $e) {
            Log::error('Client ID generation failed: '.$e->getMessage());
            return back()->with('error', 'Could not generate client ID. Please try again.')->withInput();
        }

        // Email verification code
        $verificationCode = (string) \Illuminate\Support\Str::uuid();

        try {
            DB::beginTransaction();

            $client = Client::create([
                'client_id' => $clientId,
                'role'      => 'Client',
                'status'    => 'offline',

                'firstname' => $validated['firstname'],
                'middlename'=> $validated['middlename'] ?? null,
                'lastname'  => $validated['lastname'],
                'birthdate' => $birthDate,

                'region_code'   => $validated['region_code'],
                'province_code' => $validated['province_code'],
                'city_code'     => $validated['city_code'],
                'barangay_code' => $validated['barangay_code'],
                'region_name'   => $validated['region_name'],
                'province_name' => $validated['province_name'],
                'city_name'     => $validated['city_name'],
                'barangay_name' => $validated['barangay_name'],
                'street'        => $validated['street'] ?? null,
                'postal_code'   => $validated['postal_code'] ?? null,

                'address'  => $addressSummary,
                'barangay' => $validated['barangay_name'],

                'contact'  => $validated['contact'],
                'talent'   => $request->input('talent', 'N/A'),

                'email'    => $validated['email'],
                'username' => $validated['username'],
                'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),

                'terms_accepted'           => true,
                'email_verification_code'  => $verificationCode,
                'email_verified'           => false,

                'valid_id_path'            => $validIdPath,

                // defaults for admin approval
                'account_verified' => false,
                'approved_at'      => null,
                'approved_by'      => null,
            ]);

            // Send verification email (if available)
            try {
                if (class_exists(\App\Notifications\VerifyEmail::class)) {
                    $client->notify(new \App\Notifications\VerifyEmail($verificationCode, 'client'));
                } else {
                    Log::info('VerifyEmail notification class not found. Skipping email send.');
                }
            } catch (\Throwable $mailEx) {
                Log::warning('Failed to send verification email: ' . $mailEx->getMessage());
            }

            // Notify admins (if available)
            try {
                if (class_exists(\App\Models\Admin::class) && class_exists(\App\Notifications\GrooveNotification::class)) {
                    foreach (\App\Models\Admin::all() as $admin) {
                        $admin->notify(new \App\Notifications\GrooveNotification(
                            'New Client Registered',
                            $client->firstname . ' ' . $client->lastname . ' has joined.'
                        ));
                    }
                }
            } catch (\Throwable $adminEx) {
                Log::warning('Failed to notify admins: ' . $adminEx->getMessage());
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Client registration failed: '.$e->getMessage());
            return back()->with('error', 'Registration failed. Please try again.')->withInput();
        }

        // Clear one-time address selection
        $request->session()->forget('selected_address');

        return back()->with('emailSent', true)->with('clientName', $request->firstname);
    }

    /**
     * Generate a unique 4-character client_id (A–Z, 0–9).
     */


    /**
     * Verify email using the stored verification code.
     */
    public function verifyEmail(string $code)
    {
        $client = Client::where('email_verification_code', $code)->first();

        if (!$client) {
            return redirect()->route('login')->with('error', 'Invalid or expired verification link.');
        }

        $client->email_verified = true;
        $client->email_verification_code = null;
        $client->save();

        return redirect()->route('login')->with('success', 'Email verified. You can now sign in.');
    }


    /**
     * Generate a unique zero-padded 4-digit ID.
     */
    private function generateUniqueClientId(): string
    {
        for ($i = 0; $i < 20; $i++) {
            $id = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            if (!Client::where('client_id', $id)->exists()) {
                return $id;
            }
        }

        $last = Client::orderBy('client_id', 'desc')->value('client_id');
        $next = ($last !== null) ? ((int) $last + 1) % 10000 : 0;
        return str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }



  public function verifyByAdmin(Request $request, Client $client)
    {
        // Already verified? exit early
        if ($client->account_verified) {
            return back()->with('info', 'Client already verified.');
        }

        // Authenticated admin (guard: admin)
        $adminId = auth('admin')->id(); // null if no admin session

        $client->account_verified = true;
        $client->approved_at      = now();
        $client->approved_by      = $adminId; // FK to admins.id (nullable ok)

        // If email is already verified, activate the account
        if ($client->email_verified || $client->email_verified_at) {
            $client->status = 'active';
        }

        $client->save();

        return back()->with('success', 'Client account approved.');
    }



    public function updateProfile(Request $request)
    {
        /** @var \App\Models\Client $client */
        $client = auth('client')->user();

        $rules = [];

        if ($request->has('client_id')) {
            $rules['client_id'] = 'required|string|min:3|max:10|unique:clients,client_id,' . $client->client_id . ',client_id';
        }

        if ($request->has('firstname') || $request->has('lastname')) {
            $rules['firstname'] = 'required|string|max:255';
            $rules['middlename'] = 'nullable|string|max:255';
            $rules['lastname'] = 'required|string|max:255';
            $rules['bio'] = 'nullable|string|max:1000';
        }

        if ($request->hasFile('photo')) {
            $rules['photo'] = 'image|max:2048';
        }

        if ($request->has('email')) {
            $rules['email'] = 'required|email|unique:clients,email,' . $client->client_id . ',client_id';
            $rules['contact'] = 'nullable|string|max:20';
            $rules['address'] = 'nullable|string|max:255';
            $rules['barangay'] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('photo')) {
            if ($client->photo && Storage::exists('public/' . $client->photo)) {
                Storage::delete('public/' . $client->photo);
            }
            $client->photo = $request->file('photo')->store('avatars', 'public');
        }

        if ($request->filled('client_id')) $client->client_id = $request->client_id;
        if ($request->filled('firstname')) $client->firstname = $request->firstname;
        if ($request->has('middlename')) $client->middlename = $request->middlename;
        if ($request->filled('lastname')) $client->lastname = $request->lastname;
        if ($request->has('bio')) $client->bio = $request->bio;
        if ($request->filled('email')) $client->email = $request->email;
        if ($request->filled('contact')) $client->contact = $request->contact;
        if ($request->filled('address')) $client->address = $request->address;
        if ($request->filled('barangay')) $client->barangay = $request->barangay;

        $client->save();

        return back()->with('success', 'Profile updated successfully!');
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:2048',
        ]);

        /** @var \App\Models\Client $client */
        $client = auth('client')->user();

        if ($client->photo && Storage::exists('public/' . $client->photo)) {
            Storage::delete('public/' . $client->photo);
        }

        $path = $request->file('photo')->store('avatars', 'public');
        $client->photo = $path;
        $client->save();

        return response()->json(['photo_url' => asset('storage/' . $path)]);
    }

  public function dashboard()
{
    /** @var \App\Models\Client $client */
    $client = auth('client')->user();

    // ✅ Fetch latest admin announcement
    $latestAnnouncement = Announcement::latest()->first();

    if ($latestAnnouncement && !session()->has('latest_announcement_shown')) {
        // Prevent showing the same announcement multiple times
        session()->put('latest_announcement_shown', $latestAnnouncement->id);

        // Notify the logged-in client
        $client->notify(new GrooveNotification(
            $latestAnnouncement->title ?? 'Announcement',
            $latestAnnouncement->message
        ));
    }

    // ✅ Trending coaches
    $trendingCoaches = Coach::where('status', 'Approved')
        ->latest()
        ->take(6)
        ->get();

    // ✅ Top coaches
    $topCoaches = Coach::where('status', 'Approved')
        ->orderBy('created_at', 'desc')
        ->take(4)
        ->get();

    // ✅ Recent client activities (example only, can later come from DB)
    $activities = [
        ['message' => 'You booked a session with Coach James', 'time' => '2 hours ago'],
        ['message' => 'You registered as a client', 'time' => '1 day ago'],
        ['message' => 'You followed Coach Lara', 'time' => '3 days ago'],
    ];

    // ✅ Return the view with all data including latest announcement
    return view('Client.home', compact(
        'client',
        'trendingCoaches',
        'topCoaches',
        'activities',
        'latestAnnouncement'
    ));
}




    public function updatePhoto(Request $request)
    {
        /** @var \App\Models\Client $client */
        $client = auth()->guard('client')->user();

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $filename = time() . '_' . $photo->getClientOriginalName();
            $path = $photo->storeAs('client_photos', $filename, 'public');

            if ($client->photo) {
                Storage::disk('public')->delete($client->photo);
            }

            $client->photo = $path;
            $client->save();

            return response()->json([
                'success' => true,
                'photo_path' => asset('storage/' . $path),
            ]);
        }

        return response()->json(['success' => false], 400);
    }

    public function fetchPhoto(Request $request)
    {
        /** @var \App\Models\Client $client */
        $client = auth()->guard('client')->user();

        return response()->json([
            'photo_url' => $client->photo ? asset('storage/' . $client->photo) : '',
        ]);
    }

    public function approve($id)
    {
        /** @var \App\Models\Client $client */
        $client = Client::findOrFail($id);
        $client->status = 'Approved';
        $client->save();

        $client->notify(new GrooveNotification(
            'Profile Approved',
            'Your client profile has been approved and is now visible on the platform.'
        ));

        return redirect()->back()->with('success', 'Client approved and notified successfully.');
    }

    public function updateStatus(Request $request)
    {
        $client = auth('client')->user();

        $validated = $request->validate([
            'status' => 'required|in:Online,Busy,Away,Offline',
        ]);

        /** @var \App\Models\Client $client */
        $client->status = ucfirst($validated['status']);
        $client->save();

        return response()->json([
            'success' => true,
            'status' => ucfirst($client->status),
        ]);
    }


}
    