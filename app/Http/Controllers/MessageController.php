<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Message;
use App\Models\Client;
use App\Models\Coach;
use App\Models\Agreement;
use App\Notifications\GrooveNotification;

class MessageController extends Controller
{
    /**
     * Get the authenticated user (Client or Coach) or redirect to login.
     */
    private function currentUserOrRedirect()
    {
        $client = auth()->guard('client')->user();
        $coach  = auth()->guard('coach')->user();
        return $client ?: $coach;
    }

    /**
     * Safe way to read a model's PK even if $primaryKey isn't "id".
     */
    private function modelKey($model): ?int
    {
        if (!$model) return null;
        $k = $model->getKey();
        if (is_numeric($k)) return (int) $k;

        // Fallbacks for apps that also keep business codes
        foreach (['id','client_id','coach_id','pk','client_pk','coach_pk'] as $alt) {
            if (isset($model->{$alt}) && is_numeric($model->{$alt})) {
                return (int) $model->{$alt};
            }
        }
        return null;
    }

    /**
     * Get key name for a class (e.g., 'client_id' or 'id').
     */
    private function keyName(string $class): string
    {
        /** @var \Illuminate\Database\Eloquent\Model $inst */
        $inst = new $class;
        return $inst->getKeyName(); // respects custom PKs
    }

    /**
     * Return fully qualified model class from 'client'|'coach'.
     */
    private function classFromType(string $type): ?string
    {
        $type = strtolower($type);
        if ($type === 'client') return Client::class;
        if ($type === 'coach')  return Coach::class;
        return null;
    }

    /**
     * Resolve a receiver record by either PK (true PK) or by business code (client_id/coach_id).
     * Returns [model, numericPk].
     */
    private function resolveReceiver(string $type, $incomingId): array
    {
        $class = $this->classFromType($type);
        if (!$class || !class_exists($class)) return [null, null];

        // Try by the model's real PK
        $pkName = $this->keyName($class); // e.g., 'client_id' or 'id'
        $byPk   = $class::where($pkName, $incomingId)->first();
        if ($byPk) return [$byPk, $this->modelKey($byPk)];

        // Optionally try business code columns if different from pkName
        if ($class === Client::class && Schema::hasColumn('clients', 'client_id')) {
            $byCode = Client::where('client_id', $incomingId)->first();
            return [$byCode, $this->modelKey($byCode)];
        }
        if ($class === Coach::class && Schema::hasColumn('coaches', 'coach_id')) {
            $byCode = Coach::where('coach_id', $incomingId)->first();
            return [$byCode, $this->modelKey($byCode)];
        }

        return [null, null];
    }

    /**
     * Display chat interface
     */
    public function index(Request $request)
    {
        $authUser = $this->currentUserOrRedirect();
        if (!$authUser) {
            return redirect()->route('login');
        }

        $authRole = $authUser instanceof Coach ? 'coach' : 'client';
        $authType = get_class($authUser);
        $authPk   = $this->modelKey($authUser); // numeric PK used in messages table

        // Build list of conversation counterparts from messages (using polymorphic ids)
        $pairs = Message::select('sender_id','sender_type','receiver_id','receiver_type')
            ->where(function($q) use ($authPk, $authType) {
                $q->where('sender_id', $authPk)->where('sender_type', $authType);
            })
            ->orWhere(function($q) use ($authPk, $authType) {
                $q->where('receiver_id', $authPk)->where('receiver_type', $authType);
            })
            ->whereNull('messages.deleted_at')
            ->get()
            ->flatMap(function($msg) use ($authPk, $authType) {
                if ($msg->sender_id == $authPk && $msg->sender_type == $authType) {
                    return [['id' => $msg->receiver_id, 'type' => $msg->receiver_type]];
                }
                return [['id' => $msg->sender_id, 'type' => $msg->sender_type]];
            })
            ->unique(fn ($row) => ($row['id']??'').'|'.($row['type']??''))
            ->values();

        // Load users using each model's PK name (no hard-coded "id")
        $clients = collect();
        $coaches = collect();

        $clientIds = $pairs->where('type', Client::class)->pluck('id')->filter();
        if ($clientIds->isNotEmpty()) {
            $clients = Client::whereIn((new Client)->getKeyName(), $clientIds)->get();
        }

        $coachIds = $pairs->where('type', Coach::class)->pluck('id')->filter();
        if ($coachIds->isNotEmpty()) {
            $coaches = Coach::whereIn((new Coach)->getKeyName(), $coachIds)->get();
        }

        $users = $clients->merge($coaches)->sortBy('firstname')->values();

        // Determine chat partner (from query or default)
        $chatWith  = null;
        $messages  = collect();
        $agreement = null;

        if ($request->filled('with_id') && $request->filled('with_type')) {
            [$chatWith, $partnerPk] = $this->resolveReceiver($request->input('with_type'), $request->input('with_id'));
        }

        if (!$chatWith && $users->count() > 0) {
            $chatWith  = $users->first();
        }

        if ($chatWith) {
            $partnerType = get_class($chatWith);
            $partnerPk   = $this->modelKey($chatWith);

            $messages = Message::where(function ($q) use ($authPk, $partnerPk, $authType, $partnerType) {
                    $q->where('sender_id', $authPk)
                        ->where('sender_type', $authType)
                        ->where('receiver_id', $partnerPk)
                        ->where('receiver_type', $partnerType);
                })
                ->orWhere(function ($q) use ($authPk, $partnerPk, $authType, $partnerType) {
                    $q->where('sender_id', $partnerPk)
                        ->where('sender_type', $partnerType)
                        ->where('receiver_id', $authPk)
                        ->where('receiver_type', $authType);
                })
                ->orderBy('created_at', 'asc')
                ->get();

            // Agreement lookup (uses business codes)
            if ($authRole === 'client' && $chatWith instanceof Coach) {
                $agreement = Agreement::where('coach_id', $chatWith->coach_id ?? null)
                    ->where('client_id', $authUser->client_id ?? null)
                    ->first();
            } elseif ($authRole === 'coach' && $chatWith instanceof Client) {
                $agreement = Agreement::where('coach_id', $authUser->coach_id ?? null)
                    ->where('client_id', $chatWith->client_id ?? null)
                    ->first();
            }
        }

        // If you now use a unified Blade, change view name here:
        $viewName = $authRole === 'coach' ? 'Coach.Messenger' : 'Client.messenger';

        $isCoachUnavailable = $chatWith instanceof Coach && !($chatWith->is_available ?? false);
        $coachBusinessId    = $authUser instanceof Coach ? $authUser->coach_id : null;

        return view($viewName, [
            'client'            => $authUser instanceof Client ? $authUser : null,
            'coach'             => $authUser instanceof Coach ? $authUser : null,
            'authUser'          => $authUser,
            'authType'          => $authType,
            'authRole'          => $authRole,
            'users'             => $users,
            'chatWith'          => $chatWith,
            'messages'          => $messages,
            'agreement'         => $agreement,
            'isCoachUnavailable'=> $isCoachUnavailable,
            'coachId'           => $coachBusinessId,
            'loggedInId'        => $authPk,
        ]);
    }

    /**
     * Store a new message (form POST)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiver_id'          => 'required',
            'receiver_type'        => 'required|in:client,coach',
            'message'              => 'nullable|string|max:5000',
            'media.*'              => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi,webm,pdf,mp3,wav,ogg|max:20480',
            'location_url'         => 'nullable|string',
            'agreement'            => 'nullable|boolean',
            'preferred_date'       => 'nullable|date',
            'special_requests'     => 'nullable|string|max:1000',
            'notes'                => 'nullable|string|max:1000',
            // incoming signatures from modal
            'coach_signature_data' => 'nullable|string',
            'client_signature_data'=> 'nullable|string',
        ]);

        $sender = $this->currentUserOrRedirect();
        if (!$sender) {
            return redirect()->route('login')->withErrors(['sender' => 'Authenticated user not found']);
        }

        $senderType = get_class($sender);
        $senderPk   = $this->modelKey($sender);

        // Resolve the receiver (works with business codes)
        [$receiverModel, $receiverPk] = $this->resolveReceiver($validated['receiver_type'], $validated['receiver_id']);
        if (!$receiverModel || !$receiverPk) {
            return back()->withErrors(['receiver' => 'Receiver not found.']);
        }
        $receiverType = get_class($receiverModel);

        // Identify coach & client models regardless of who sent
        /** @var Coach|null $coach */
        $coach  = $sender instanceof Coach ? $sender : ($receiverModel instanceof Coach ? $receiverModel : null);
        /** @var Client|null $client */
        $client = $sender instanceof Client ? $sender : ($receiverModel instanceof Client ? $receiverModel : null);

        $created = collect();

        // ------------- SIGNATURES (both sides) -------------
        $coachSignaturePath      = null;
        $coachSignatureDataUrl   = $request->input('coach_signature_data');  // for PDF inline
        $clientSignaturePath     = null;
        $clientSignatureDataUrl  = $request->input('client_signature_data'); // for PDF inline

        // Save COACH signature if provided
        if ($coach && $client && $coachSignatureDataUrl && preg_match('/^data:image\/\w+;base64,/', $coachSignatureDataUrl)) {
            $raw = substr($coachSignatureDataUrl, strpos($coachSignatureDataUrl, ',') + 1);
            $bin = base64_decode($raw);

            $folder   = "agreements/signatures/coaches/{$coach->coach_id}";
            $filename = 'sig_'.time().'_coach.png';
            $coachSignaturePath = $folder.'/'.$filename;

            Storage::disk('public')->put($coachSignaturePath, $bin);
        }

        // Save CLIENT signature if provided
        if ($coach && $client && $clientSignatureDataUrl && preg_match('/^data:image\/\w+;base64,/', $clientSignatureDataUrl)) {
            $raw = substr($clientSignatureDataUrl, strpos($clientSignatureDataUrl, ',') + 1);
            $bin = base64_decode($raw);

            $folder   = "agreements/signatures/clients/{$client->client_id}";
            $filename = 'sig_'.time().'_client.png';
            $clientSignaturePath = $folder.'/'.$filename;

            Storage::disk('public')->put($clientSignaturePath, $bin);
        }

        // Upsert Agreement using business ids (char(4)),
        // carefully merging fields so we don't clobber the other party's signature.
        if ($coach && $client && ($coachSignaturePath || $clientSignaturePath)) {
            $agreement = Agreement::firstOrNew([
                'client_id' => $client->client_id ?? null,
                'coach_id'  => $coach->coach_id  ?? null,
            ]);

            // Keep terms in sync from the coach profile (safe to overwrite each sign)
            $agreement->agreement_date     = $agreement->agreement_date ?: now()->toDateString();
            $agreement->appointment_price  = $coach->appointment_price ?? $coach->service_fee ?? $agreement->appointment_price;
            $agreement->session_duration   = $coach->session_duration ?? $coach->duration ?? $agreement->session_duration;
            $agreement->payment_method     = $coach->payment_method ?? $coach->payment ?? $agreement->payment_method;
            $agreement->notice_hours       = $coach->notice_hours ?? $agreement->notice_hours;
            $agreement->notice_days        = $coach->notice_days  ?? $agreement->notice_days;
            $agreement->cancellation_method= $coach->cancellation_method ?? $coach->method ?? $agreement->cancellation_method;

            if ($coachSignaturePath) {
                $agreement->coach_signature = $coachSignaturePath;
            }
            if ($clientSignaturePath) {
                $agreement->client_signature = $clientSignaturePath;
            }

            $agreement->save();
        }

        // ------------- AGREEMENT PDF (optional) -------------
        if (!empty($validated['agreement'])) {
            $pdf = Pdf::loadView('contracts.AgreementForm', [
                'coach'                      => $coach,
                'client'                     => $client,
                'date'                       => now()->format('F d, Y'),

                // Coach signature (data-URL preferred, else storage)
                'coachSignatureDataUrl'      => $coachSignatureDataUrl,
                'coachSignatureStorageUrl'   => $coachSignaturePath ? asset('storage/'.$coachSignaturePath) : null,

                // Client signature (data-URL preferred, else storage)
                'clientSignatureDataUrl'     => $clientSignatureDataUrl,
                'clientSignatureStorageUrl'  => $clientSignaturePath ? asset('storage/'.$clientSignaturePath) : null,
            ]);

            $pdfPath = 'messages/agreements/' . time() . '_agreement.pdf';
            Storage::disk('public')->put($pdfPath, $pdf->output());

            $msg = Message::create([
                'sender_id'     => $senderPk,
                'sender_type'   => $senderType,
                'receiver_id'   => $receiverPk,
                'receiver_type' => $receiverType,
                'message'       => $validated['message'] ?? 'Sent Agreement PDF / Terms',
                'media_path'    => $pdfPath,
                'location_url'  => $validated['location_url'] ?? null,
                'edited_at'     => null,
            ]);

            $created->push($msg);
        }

        // ------------- MEDIA uploads -------------
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $file->store('messages/media', 'public');
                $msg  = Message::create([
                    'sender_id'     => $senderPk,
                    'sender_type'   => $senderType,
                    'receiver_id'   => $receiverPk,
                    'receiver_type' => $receiverType,
                    'message'       => $validated['message'] ?? null,
                    'media_path'    => $path,
                    'location_url'  => $validated['location_url'] ?? null,
                ]);
                $created->push($msg);
            }
        }

        // ------------- Plain text / location only -------------
        if ($created->isEmpty()) {
            $msg = Message::create([
                'sender_id'     => $senderPk,
                'sender_type'   => $senderType,
                'receiver_id'   => $receiverPk,
                'receiver_type' => $receiverType,
                'message'       => $validated['message'] ?? null,
                'location_url'  => $validated['location_url'] ?? null,
            ]);
            $created->push($msg);
        }

        // ------------- Notify receiver -------------
        foreach ($created as $msg) {
            $receiver = $msg->receiver_type::where((new $receiverType)->getKeyName(), $msg->receiver_id)->first();
            if ($receiver) {
                $senderName = $sender->firstname ?? class_basename($senderType);
                $withId = $receiverModel instanceof Client ? ($receiverModel->client_id ?? $receiverPk)
                                                           : ($receiverModel->coach_id  ?? $receiverPk);

                $chatUrl = route('messages.index', [
                    'with_id'   => $withId,
                    'with_type' => strtolower(class_basename($receiverType)),
                ]);

                $title = "New Message from {$senderName}";
                $text  = $msg->message ?: "Sent you a new media message.";
                $receiver->notify(new GrooveNotification($title, $text, $chatUrl));
            }
        }

        return redirect()->route('messages.index', [
            'with_id'   => $validated['receiver_id'],
            'with_type' => $validated['receiver_type'],
        ])->with('success', 'Message sent successfully!');
    }

    /**
     * Store via AJAX (optional)
     */
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'receiver_id'   => 'required',
            'receiver_type' => 'required|in:client,coach',
            'message'       => 'nullable|string|max:5000',
            'media.*'       => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi,webm,pdf,mp3,wav,ogg|max:20480',
        ]);

        $sender = $this->currentUserOrRedirect();
        if (!$sender) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $senderType = get_class($sender);
        $senderPk   = $this->modelKey($sender);

        [$receiverModel, $receiverPk] = $this->resolveReceiver($validated['receiver_type'], $validated['receiver_id']);
        if (!$receiverModel || !$receiverPk) {
            return response()->json(['error' => 'Receiver not found'], 422);
        }
        $receiverType = get_class($receiverModel);

        $created = collect();

        if (!empty($validated['message'])) {
            $created->push(Message::create([
                'sender_id'     => $senderPk,
                'sender_type'   => $senderType,
                'receiver_id'   => $receiverPk,
                'receiver_type' => $receiverType,
                'message'       => $validated['message'],
            ]));
        }

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $file->store('messages/media', 'public');
                $created->push(Message::create([
                    'sender_id'     => $senderPk,
                    'sender_type'   => $senderType,
                    'receiver_id'   => $receiverPk,
                    'receiver_type' => $receiverType,
                    'media_path'    => $path,
                ]));
            }
        }

        foreach ($created as $msg) {
            $receiver = $msg->receiver_type::where((new $receiverType)->getKeyName(), $msg->receiver_id)->first();
            if ($receiver) {
                $senderName  = $sender->firstname ?? 'Sender';
                $title       = "New Message from {$senderName}";
                $messageText = $msg->message ?? "Sent you a media message.";
                $receiver->notify(new GrooveNotification($title, $messageText));
            }
        }

        return response()->json([
            'success'  => true,
            'messages' => $created,
        ]);
    }

    /**
     * Fetch messages
     */
    public function fetchMessages($receiverType, $receiverId)
    {
        $sender = $this->currentUserOrRedirect();
        if (!$sender) {
            return response()->json([], 401);
        }

        $senderType = get_class($sender);
        $senderPk   = $this->modelKey($sender);

        [$receiverModel, $receiverPk] = $this->resolveReceiver($receiverType, $receiverId);
        if (!$receiverModel || !$receiverPk) {
            return response()->json([], 404);
        }
        $receiverFqcn = get_class($receiverModel);

        $messages = Message::where(function($q) use ($senderPk, $receiverPk, $receiverFqcn, $senderType) {
                $q->where('sender_id', $senderPk)
                    ->where('sender_type', $senderType)
                    ->where('receiver_id', $receiverPk)
                    ->where('receiver_type', $receiverFqcn);
            })
            ->orWhere(function($q) use ($senderPk, $receiverPk, $receiverFqcn, $senderType) {
                $q->where('sender_id', $receiverPk)
                    ->where('sender_type', $receiverFqcn)
                    ->where('receiver_id', $senderPk)
                    ->where('receiver_type', $senderType);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    public function edit($id)
    {
        $message = Message::findOrFail($id);
        return view('messages.edit', compact('message'));
    }

    public function update(Request $request, $id)
    {
        $request->validate(['message' => 'required|string|max:5000']);
        $message = Message::findOrFail($id);
        $message->message = $request->message;
        $message->edited_at = Carbon::now();
        $message->save();

        return back()->with('success', 'Message updated successfully.');
    }

    public function destroy($id)
    {
        $message = Message::findOrFail($id);

        $authUser = $this->currentUserOrRedirect();
        $authType = get_class($authUser);
        $authPk   = $this->modelKey($authUser);

        if ($message->sender_id !== $authPk || $message->sender_type !== $authType) {
            return back()->with('error', 'Unauthorized action.');
        }

        if ($message->media_path && Storage::disk('public')->exists($message->media_path)) {
            Storage::disk('public')->delete($message->media_path);
        }

        $message->delete();

        return back()->with('success', 'Message unsent.');
    }
}
