<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Coach;

class ChatbotController extends Controller
{
    public function index()
    {
        // make sure your Blade includes: <meta name="csrf-token" content="{{ csrf_token() }}">
        return view('chatbot');
    }

   public function sendMessage(Request $request)
    {
        $request->validate([
            'message'  => ['required','string','max:5000'],
            'coach_id' => ['required','string'],
        ]);

        // Load the coach from DB (source of truth)
        /** @var \App\Models\Coach|null $coach */
        $coach = Coach::where('coach_id', $request->coach_id)->first();

        if (!$coach) {
            return response()->json(['error' => 'Coach not found.'], 404);
        }

        // Optional hard gate: only allow AI when coach is not available
        $status = strtolower((string) $coach->status);
        if (!in_array($status, ['offline', 'busy', 'away'], true)) {
            return response()->json([
                'error' => 'The coach is available now. Please message the coach directly.'
            ], 409);
        }

        try {
            $answer = $this->askDeepSeek(
                userPrompt: (string) $request->message,
                coachInfo:  $this->buildCoachSnapshotFromModel($coach)
            );
            return response()->json(['answer' => $answer]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /* =========================
       Coach snapshot (from model)
       ========================= */
    private function buildCoachSnapshotFromModel(Coach $c): string
    {
        // Combine address lines if present
        $address = collect([
            $c->street,
            $c->barangay_name ?: $c->barangay,
            $c->city_name     ?: $c->city,
            $c->province_name ?: $c->province,
            $c->region_name   ?: $c->region,
        ])->filter()->implode(', ');
        if ($c->postal_code) {
            $address = $address ? ($address.' — '.$c->postal_code) : $c->postal_code;
        }

        return implode("\n", [
            "Coach Details:",
            "Full Name: " . ($c->full_name ?? trim($c->firstname.' '.($c->middlename ? $c->middlename.' ' : '').$c->lastname)),
            "Role: " . ($c->role ?: '(not provided)'),
            "Talent(s): " . ($c->talents ?: '(not provided)'),
            "Genre(s): " . ($c->genres ?: '(not provided)'),
            "About: " . ($c->bio ?: '(not provided)'),
            "Appointment Price / Service Fee: " . (isset($c->service_fee) ? (string) $c->service_fee : '(not provided)'),
            "Session Duration: " . ($c->duration ?: '(not provided)'),
            "Payment Method: " . ($c->payment ?: '(not provided)'),
            "Notice Requirement: " . ((string) ($c->notice_hours ?? 0)) . " hours / " . ((string) ($c->notice_days ?? 0)) . " days",
            "Cancellation Method: " . ($c->method ?: '(not provided)'),
            "Address: " . ($address ?: '(not provided)'),
            "Contact: " . ($c->contact ?: '(not provided)'),
            "Email: " . ($c->email ?: '(not provided)'),
            "Status: " . ($c->status ?: '(not provided)'),
        ]);
    }

    /* =========================
       DeepSeek (OpenRouter) call
       ========================= */
    private function askDeepSeek(string $userPrompt, string $coachInfo): string
    {
        $style = <<<MD
You are Groove Assistant, a performing-arts AI for singing, dancing, acting, choreography, and musical performance.
Write like a friendly professional coach: warm, concise, confidence-building. Use short, clear sentences.
Match the user’s language (English/Tagalog/Taglish). Reply in Markdown.
If the user asks about this coach, use ONLY the coach data provided. If a detail is missing, write “(not provided)”.
When giving tips, use a short numbered list (1–6), one sentence each. Avoid fluff and repetition.
If the topic is outside performing arts, gently say you specialize in that area and offer a relevant alternative.
MD;

        $payload = [
            'model'       => config('services.openrouter.model'),
            'messages'    => [
                ['role' => 'system', 'content' => $style . "\n\n" . $coachInfo],
                ['role' => 'user',   'content' => $userPrompt],
            ],
            'temperature' => 0.2,
            'max_tokens'  => 512,
        ];

        $response = Http::withHeaders([
                'Authorization' => 'Bearer '.config('services.openrouter.key'),
                'HTTP-Referer'  => config('services.openrouter.referer'),
                'X-Title'       => config('services.openrouter.title'),
            ])
            ->baseUrl(config('services.openrouter.base'))
            ->timeout(120)
            ->withOptions(['connect_timeout' => 20])
            ->retry(3, 1500, function ($exception) {
                if ($exception instanceof ConnectionException) return true;
                if ($exception instanceof RequestException && $exception->response) {
                    $st = $exception->response->status();
                    return $st === 429 || $exception->response->serverError();
                }
                return false;
            })
            ->post('/chat/completions', $payload);

        if ($response->status() === 404) {
            throw new \RuntimeException('No matching endpoint. Check model slug or disable "ZDR Endpoints Only" in OpenRouter privacy settings.');
        }
        if ($response->status() === 429) {
            throw new \RuntimeException('Rate-limited by provider. Try again shortly or switch model.');
        }
        if ($response->failed()) {
            $msg = $response->json('error.message') ?? $response->body();
            throw new \RuntimeException("OpenRouter error ({$response->status()}): ".$msg);
        }

        $data = $response->json();
        return $data['choices'][0]['message']['content'] ?? 'No response content received.';
    }
}