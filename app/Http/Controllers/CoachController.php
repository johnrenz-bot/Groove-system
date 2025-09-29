<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coach;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Validator;

class CoachController extends Controller
{
    /**
     * Show the coach registration form.
     */

      public function index(Request $request)
    {
        // Retrieve query parameters
        $talent   = trim((string) $request->query('talent', ''));
        $genre    = trim((string) $request->query('genre', ''));
        $location = trim((string) $request->query('location', ''));
        $feeMax   = $request->filled('fee_max') ? (int) $request->query('fee_max') : null;

        // Build the query with optional filters
        $coaches = Coach::query()
            ->when($talent !== '', function ($query) use ($talent) {
                $query->where('talents', 'LIKE', "%{$talent}%");
            })
            ->when($genre !== '', function ($query) use ($genre) {
                $query->where('genres', 'LIKE', "%{$genre}%");
            })
            ->when($location !== '', function ($query) use ($location) {
                $query->where(function ($q) use ($location) {
                    $q->where('barangay', 'LIKE', "%{$location}%")
                      ->orWhere('barangay_name', 'LIKE', "%{$location}%")
                      ->orWhere('city', 'LIKE', "%{$location}%")
                      ->orWhere('city_name', 'LIKE', "%{$location}%");
                });
            })
            ->when(is_int($feeMax), function ($query) use ($feeMax) {
                // NEW: filter by maximum service fee
                $query->where('service_fee', '<=', $feeMax);
            })
            ->orderBy('firstname')
            ->get(); // or ->paginate(24) if you need pagination

        // Return the view with all coaches
        return view('coaches.index', compact('coaches'));
    }


    
    public function CoachRegister(Request $request)
    {
        $request->session()->put('address_return', 'coach');

        $selected = $request->session()->get('selected_address');
        $summary  = $selected
            ? collect([
                $selected['street'] ?? null,
                $selected['barangay_name'] ?? null,
                $selected['city_name'] ?? null,
                $selected['province_name'] ?? null,
                $selected['region_name'] ?? null,
            ])->filter()->implode(', ')
              . (!empty($selected['postal_code']) ? ' — '.$selected['postal_code'] : '')
            : null;

        return view('Coach.register', compact('selected', 'summary'));
    }

    public function Talents()
    {
        $coaches = Coach::query()->get();
        return view('Coach.Talent', compact('coaches'));
    }

    /**
     * Store a new coach and send email verification notification.
     */
  public function CoachStore(Request $request)
{
    $birth_year  = (int) $request->input('birth_year');
    $birth_month = (int) $request->input('birth_month');
    $birth_day   = (int) $request->input('birth_day');
    $birthdate   = ($birth_year && $birth_month && $birth_day) ? sprintf('%04d-%02d-%02d', $birth_year, $birth_month, $birth_day) : null;

    $talentsJson   = $request->input('talents_json');
    $genresMapJson = $request->input('genres_map_json');
    $talentsCsv = trim((string) $request->input('talents', ''));
    $genresCsv  = trim((string) $request->input('genres',  ''));
    if ($talentsJson) {
        try {
            $arr = json_decode($talentsJson, true, 512, JSON_THROW_ON_ERROR);
            if (is_array($arr)) $talentsCsv = implode(', ', array_values(array_unique(array_map('strval', $arr))));
        } catch (\Throwable $e) {}
    }
    if ($genresMapJson) {
        try {
            $map = json_decode($genresMapJson, true, 512, JSON_THROW_ON_ERROR);
            if (is_array($map)) {
                $chunks = [];
                foreach ($map as $talent => $genres) {
                    $g = is_array($genres) ? implode(', ', array_values(array_unique(array_map('strval', $genres)))) : '';
                    $chunks[] = trim($talent).': '.trim($g);
                }
                $genresCsv = implode(' | ', array_filter($chunks));
            }
        } catch (\Throwable $e) {}
    }

    $rawContact = preg_replace('/\D+/', '', (string) $request->input('contact', ''));
    if (preg_match('/^9\d{9}$/', $rawContact)) {
        $normalizedContact = '+63'.$rawContact;
    } elseif (preg_match('/^09\d{9}$/', $rawContact)) {
        $normalizedContact = '+63'.substr($rawContact, 1);
    } elseif (preg_match('/^639\d{9}$/', $rawContact)) {
        $normalizedContact = '+'.$rawContact;
    } else {
        $normalizedContact = $rawContact;
    }

    $rules = [
        'firstname'   => ['required','string','max:80'],
        'middlename'  => ['nullable','string','max:80'],
        'lastname'    => ['required','string','max:80'],
        'suffix'      => ['nullable','string','max:10'],
        'birth_year'  => ['required','integer'],
        'birth_month' => ['required','integer','between:1,12'],
        'birth_day'   => ['required','integer','between:1,31'],
        'region_code'   => ['nullable','string','max:10'],
        'province_code' => ['nullable','string','max:10'],
        'city_code'     => ['nullable','string','max:10'],
        'barangay_code' => ['nullable','string','max:10'],
        'region_name'   => ['nullable','string','max:120'],
        'province_name' => ['nullable','string','max:120'],
        'city_name'     => ['nullable','string','max:120'],
        'barangay_name' => ['nullable','string','max:120'],
        'street'        => ['nullable','string','max:160'],
        'postal_code'   => ['nullable','string','max:12'],
        'contact'    => ['required','string'],
        'email'      => ['required','string','email','max:255','regex:/^[a-z0-9._%+\-]+@gmail\.com$/i', Rule::unique('coaches','email')],
        'username'   => ['required','string','min:3','max:60', Rule::unique('coaches','username')],
        'password'   => ['required','string','min:8','confirmed'],
        'about'      => ['required','string','min:10'],
        'talents'    => ['required','string','max:255'],
        'genres'     => ['nullable','string'],
        'role'         => ['required','string','max:40'],
        'service_fee'  => ['required','integer','min:1','max:10000'],
        'duration'     => ['required','string','max:40'],
        'payment'      => ['required', Rule::in(['cash','online'])],
        'notice_hours' => ['nullable','integer','min:0','max:99'],
        'notice_days'  => ['nullable','integer','min:0','max:30'],
        'method'       => ['required','string','regex:/^[a-z0-9._%+\-]+@gmail\.com$/i'],
        'terms'        => ['accepted'],
        'payment_provider' => [Rule::requiredIf(fn() => $request->input('payment') === 'online'), 'nullable','string','in:gcash,maya,paypal,bank'],
        'payment_handle'   => [Rule::requiredIf(fn() => $request->input('payment') === 'online'), 'nullable','string','max:120'],
        'portfolio' => ['required','file','mimes:pdf,jpg,jpeg,png','max:10240'],
        'valid_id'  => ['required','image','mimes:jpg,jpeg,png','max:8192'],
        'id_selfie' => ['required','image','mimes:jpg,jpeg,png','max:8192'],
    ];

    $messages = [
        'terms.accepted' => 'You must accept the Terms and Conditions.',
        'email.unique'   => 'This Gmail address is already registered.',
        'username.unique'=> 'This username is already taken.',
    ];

    $data = $request->all();
    $data['contact'] = $normalizedContact;
    $data['talents'] = $talentsCsv;
    $data['genres']  = $genresCsv;

    $validator = Validator::make($data, $rules, $messages);

    $validator->after(function ($v) use ($birthdate, $data) {
        if (!$birthdate || !\DateTime::createFromFormat('Y-m-d', $birthdate)) {
            $v->errors()->add('birth_day', 'Enter a valid birthdate.');
        } else {
            $dob = new \DateTime($birthdate);
            $today = new \DateTime('today');
            if ($dob->diff($today)->y < 13) $v->errors()->add('birth_day', 'You must be at least 13 years old to register.');
        }
        if (!preg_match('/^\+639\d{9}$/', (string) $data['contact'])) {
            $v->errors()->add('contact', 'Contact must be in +639XXXXXXXXX format.');
        }
        if (($data['payment'] ?? null) === 'online') {
            $provider = $data['payment_provider'] ?? '';
            $handle   = trim((string) ($data['payment_handle'] ?? ''));
            if (!$provider) {
                $v->errors()->add('payment_provider', 'Please choose a provider.');
            } else {
                if (in_array($provider, ['gcash','maya'], true)) {
                    if (!preg_match('/^09\d{9}$/', $handle)) $v->errors()->add('payment_handle', 'Enter a valid PH mobile (09XXXXXXXXX).');
                } elseif ($provider === 'paypal') {
                    if (!filter_var($handle, FILTER_VALIDATE_EMAIL)) $v->errors()->add('payment_handle', 'Enter a valid PayPal email.');
                } elseif ($provider === 'bank') {
                    if (!preg_match('/^\d{8,}$/', preg_replace('/\s+/', '', $handle))) $v->errors()->add('payment_handle', 'Enter a valid bank account number.');
                }
            }
        }
        if (!strlen(trim((string) ($data['talents'] ?? '')))) {
            $v->errors()->add('talents', 'Add at least one talent and pick genres.');
        }
    });

    $validator->validate();

    $coach = new \App\Models\Coach();
    $coach->fill([
        'firstname' => $request->string('firstname'),
        'middlename'=> $request->filled('middlename') ? $request->string('middlename') : null,
        'lastname'  => $request->string('lastname'),
        'suffix'    => $request->filled('suffix') ? $request->string('suffix') : null,
        'birthdate' => $birthdate,
        'region_code'   => $request->input('region_code'),
        'province_code' => $request->input('province_code'),
        'city_code'     => $request->input('city_code'),
        'barangay_code' => $request->input('barangay_code'),
        'region_name'   => $request->input('region_name'),
        'province_name' => $request->input('province_name'),
        'city_name'     => $request->input('city_name'),
        'barangay_name' => $request->input('barangay_name'),
        'street'        => $request->input('street'),
        'postal_code'   => $request->input('postal_code'),
        'contact'   => $normalizedContact,
        'email'     => $request->string('email'),
        'username'  => $request->string('username'),
        'password'  => $request->string('password'),
        'bio'       => $request->string('about'),
        'talents'   => $talentsCsv,
        'genres'    => $genresCsv ?: null,
        'status'    => 'pending',
        'role'      => $request->string('role'),
        'service_fee'  => (int) $request->input('service_fee'),
        'duration'     => $request->string('duration'),
        'payment'      => $request->string('payment'),
        'notice_hours' => $request->filled('notice_hours') ? (int) $request->input('notice_hours') : null,
        'notice_days'  => $request->filled('notice_days')  ? (int) $request->input('notice_days')  : null,
        'method'       => $request->string('method'),
        'terms_accepted' => true,
    ]);

    if ($coach->payment === 'online') {
        $coach->payment_provider = $request->string('payment_provider');
        $coach->payment_handle   = $request->string('payment_handle');
    }

    $coach->save();

    $baseDir = "coaches/{$coach->coach_id}";
    if ($request->hasFile('portfolio')) $coach->portfolio_path = $request->file('portfolio')->store($baseDir, 'public');
    if ($request->hasFile('valid_id'))  $coach->valid_id_path  = $request->file('valid_id')->store($baseDir, 'public');
    if ($request->hasFile('id_selfie')) $coach->id_selfie_path = $request->file('id_selfie')->store($baseDir, 'public');
    $coach->save();

    $coach->notify(new \App\Notifications\VerifyEmail($coach->email_verification_code, 'coach'));

    return back()->with('emailSent', true)->with('coachName', $coach->firstname)->with('success', 'Registration received! Please check your email to verify.');
}

    /**
     * Verify coach email using the code.s
     */
    public function verifyCoach(string $code)
    {
        $coach = Coach::where('email_verification_code', $code)->first();

        if (!$coach) {
            return redirect()->route('login')->with('error', 'Invalid verification link.');
        }

        $coach->email_verified = true;
        $coach->email_verification_code = null;
        $coach->status = 'active';
        $coach->save();

        return redirect()->route('login')->with('success', 'Email verified! You can now sign in.');
    }

public function verifyByAdmin(Request $request, Coach $coach)
{
    // If already approved, bail early.
    if ($coach->account_verified) {
        return back()->with('info', 'Coach already verified.');
    }

    // Pull the currently authenticated admin id (guard: admin)
    $adminId = auth('admin')->id(); // will be null if somehow no admin session

    $coach->account_verified = true;
    $coach->approved_at = now();
    $coach->approved_by = $adminId; // FK now points to admins.id

    if ($coach->email_verified) {
        $coach->status = 'active';
    }

    $coach->save();

    return back()->with('success', 'Coach account approved.');
}



    /**
     * Edit screen for the coach.
     */
    public function profileEdit(Request $request)
    {
        $coach = Auth::guard('coach')->user();
        abort_unless($coach, 403);

        return view('coach.profileedit', [
            'coach' => $coach,
        ]);
    }

    /**
     * Unified updater for all tabs in profileedit.blade.php.
     * Detects which subset of fields is present and validates accordingly.
     */
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\Coach $coach */
        $coach = Auth::guard('coach')->user();
        abort_unless($coach, 403);

        // Which tab? Infer by fields present.
        $isProfile = $request->hasAny(['firstname','middlename','lastname','about','photo']);
        $isAccount = $request->has('coach_id') && !$isProfile && !$request->hasAny(['email','contact','address','barangay']);
        $isInfo    = $request->hasAny(['email','contact','address','barangay']) && !$isProfile && !$isAccount;
        $isRate    = $request->hasAny(['appointment_price','duration','payment','notice_hours','notice_days']) && !$isProfile && !$isAccount;

        try {
            if ($isProfile) {
                $data = $request->validate([
                    'firstname' => ['required','string','max:80'],
                    'middlename'=> ['nullable','string','max:80'],
                    'lastname'  => ['required','string','max:80'],
                    'about'     => ['nullable','string','max:2000'],
                    'photo'     => ['nullable','image','max:2048'],
                ]);

                // Optional avatar update (same as uploadPhoto but synchronous here)
                if ($request->hasFile('photo')) {
                    if ($coach->photo && Storage::disk('public')->exists($coach->photo)) {
                        Storage::disk('public')->delete($coach->photo);
                    }
                    $path = $request->file('photo')->store('avatars', 'public');
                    $coach->photo = $path;
                }

                // Some installs use "about" vs "bio"
                if (isset($data['about'])) {
                    $coach->about = $data['about']; // if you have 'about' column
                    $coach->bio   = $data['about']; // keep in sync if you only persist 'bio'
                }

                $coach->firstname = $data['firstname'];
                $coach->middlename = $data['middlename'] ?? null;
                $coach->lastname = $data['lastname'];
                $coach->save();

                return back()->with('success', 'Profile updated.');
            }

            if ($isAccount) {
                $data = $request->validate([
                    'coach_id' => [
                        'required','string','max:60',
                        Rule::unique('coaches','coach_id')->ignore($coach->id),
                    ],
                ]);

                $coach->coach_id = $data['coach_id'];
                $coach->save();

                return back()->with('success', 'Coach ID updated.');
            }

            if ($isInfo) {
                $data = $request->validate([
                    'email'   => ['required','email','max:255', Rule::unique('coaches','email')->ignore($coach->id)],
                    // Keep your +639XXXXXXXXX pattern; allow optional + only if you want:
                    'contact' => ['nullable','string','regex:/^\+639\d{9}$/'],
                    'address' => ['nullable','string','max:255'],
                    'barangay'=> ['nullable','string','max:120'],
                ], [
                    'contact.regex' => 'Contact must be in +63XXXXXXXXXX format.',
                ]);

                $coach->email    = $data['email'];
                $coach->contact  = $data['contact'] ?? null;
                $coach->address  = $data['address'] ?? null;
                $coach->barangay = $data['barangay'] ?? null;
                $coach->save();

                return back()->with('success', 'Personal information updated.');
            }

            if ($isRate) {
                $data = $request->validate([
                    // Your UI uses "appointment_price" – map this to service_fee in DB
                    'appointment_price' => ['required','numeric','min:1','max:1000000'],
                    'duration'          => ['required','string','max:40'],
                    'payment'           => ['required', Rule::in(['cash','online'])],
                    'notice_hours'      => ['nullable','integer','min:0','max:99'],
                    'notice_days'       => ['nullable','integer','min:0','max:30'],
                ]);

                $coach->service_fee  = (int) round($data['appointment_price']); // store as int if column is int
                $coach->duration     = $data['duration'];
                $coach->payment      = $data['payment'];
                $coach->notice_hours = $data['notice_hours'] ?? null;
                $coach->notice_days  = $data['notice_days'] ?? null;
                $coach->save();

                return back()->with('success', 'Rate settings updated.');
            }

            // Fallback: nothing matched (empty submit or unexpected fields)
            return back()->with('info', 'No changes detected.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Let Laravel handle redirect+errors automatically,
            // but log for good measure:
            Log::warning('Coach update validation failed', [
                'coach_id' => $coach->id,
                'errors'   => $e->errors(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Coach update failed: '.$e->getMessage(), ['coach_id' => $coach->id]);
            return back()->with('error', 'Something went wrong while saving changes.');
        }
    }

    /* ---------------- Optional extras you kept ---------------- */

    public function updateStatus(Request $request)
    {
        $request->validate(['status' => 'required|in:Online,Away,Busy,Offline']);
        try {
            /** @var \App\Models\Coach $coach */
            $coach = Auth::guard('coach')->user();
            $coach->status = $request->status;
            $coach->save();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Status update failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to update status'], 500);
        }
    }

    public function fetchPhoto()
    {
        try {
            $coach = auth('coach')->user();
            if (!$coach) return response()->json(['error' => 'Coach not found'], 404);
            return response()->json(['photo' => $coach->photo ? asset('storage/' . $coach->photo) : null]);
        } catch (\Exception $e) {
            Log::error('Error fetching coach photo: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch photo'], 500);
        }
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate(['photo' => 'required|image|max:2048']);

        try {
            $coach = auth('coach')->user();
            if ($coach->photo && Storage::disk('public')->exists($coach->photo)) {
                Storage::disk('public')->delete($coach->photo);
            }
            $path = $request->file('photo')->store('avatars', 'public');
            /** @var \App\Models\Coach $coach */
            $coach->photo = $path;
            $coach->save();

            return response()->json(['success' => true, 'photo' => asset('storage/' . $path)]);
        } catch (\Exception $e) {
            Log::error('Error uploading photo: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Upload failed'], 500);
        }
    }
}
