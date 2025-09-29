<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coach;
use App\Models\Agreement;
use App\Models\Message;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ContractController extends Controller
{
    public function showFinalAgreement()
    {
        $client = auth('client')->user();

        $agreement = Agreement::where('client_id', $client->client_id)
                              ->latest()
                              ->first();

        if (!$agreement) {
            return redirect()->back()->with('error', 'No agreement found for this client.');
        }

        $coach = Coach::where('coach_id', $agreement->coach_id)->firstOrFail();

        // 🔹 Convert signatures to base64 for PDF
        if ($agreement->client_signature && Storage::disk('public')->exists($agreement->client_signature)) {
            $path = storage_path('app/public/' . $agreement->client_signature);
            $agreement->client_signature = 'data:image/png;base64,' . base64_encode(file_get_contents($path));
        }

        if ($agreement->coach_signature && Storage::disk('public')->exists($agreement->coach_signature)) {
            $path = storage_path('app/public/' . $agreement->coach_signature);
            $agreement->coach_signature = 'data:image/png;base64,' . base64_encode(file_get_contents($path));
        }

        $client_name = trim(
            $client->firstname . ' ' . ($client->middlename ? $client->middlename . ' ' : '') . $client->lastname
        );

        $date = now()->format('F d, Y');

        return view('contracts.FINALAgreementForm', compact('coach', 'agreement', 'client_name', 'date'));
    }

    public function clientAgree($coach_id)
    {
        $coach = Coach::findOrFail($coach_id);
        $client = auth('client')->user();

        $client_name = $client 
            ? trim($client->firstname . ' ' . ($client->middlename ? $client->middlename . ' ' : '') . $client->lastname)
            : 'Client';

        $date = now()->format('F d, Y');

        return view('contracts.ClientAgree', compact('coach', 'client_name', 'date'));
    }

    public function storeAgreement(Request $request, $coach_id)
    {
        $client = Auth::guard('client')->user();
        $coach = Coach::findOrFail($coach_id);

        // Check existing agreement
        $existing = Agreement::where('client_id', $client->client_id)
                             ->where('coach_id', $coach_id)
                             ->first();

        if ($existing) {
            return redirect()->route('messages.index')
                             ->with('error', 'You have already submitted an agreement for this coach.');
        }

        $request->validate([
            'client_signature' => 'required|string',
        ]);

        // 🔹 Save client signature as PNG
        $clientSignaturePath = null;
        if ($request->filled('client_signature') && str_starts_with($request->client_signature, 'data:image')) {
            $image = $request->client_signature;
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $filename = uniqid() . '.png';
            $path = 'signatures/' . $filename;

            Storage::disk('public')->put($path, base64_decode($image));
            $clientSignaturePath = $path;
        }

        // 🔹 Create Agreement
        $agreement = Agreement::create([
            'client_id'          => $client->client_id,
            'coach_id'           => $coach_id,
            'agreement_date'     => now(),
            'appointment_price'  => $request->input('appointment_price'),
            'session_duration'   => $request->input('session_duration'),
            'payment_method'     => $request->input('payment_method'),
            'notice_hours'       => $request->input('notice_hours'),
            'notice_days'        => $request->input('notice_days'),
            'cancellation_method'=> $request->input('cancellation_method'),
            'client_signature'   => $clientSignaturePath,
            'coach_signature'    => $request->input('coach_signature'),
        ]);

        // 🔹 Convert signatures to base64 for PDF
        if ($agreement->client_signature && Storage::disk('public')->exists($agreement->client_signature)) {
            $path = storage_path('app/public/' . $agreement->client_signature);
            $agreement->client_signature = 'data:image/png;base64,' . base64_encode(file_get_contents($path));
        }

        if ($agreement->coach_signature && Storage::disk('public')->exists($agreement->coach_signature)) {
            $path = storage_path('app/public/' . $agreement->coach_signature);
            $agreement->coach_signature = 'data:image/png;base64,' . base64_encode(file_get_contents($path));
        }

        // 🔹 Generate PDF
        $client_name = trim($client->firstname . ' ' . ($client->middlename ? $client->middlename . ' ' : '') . $client->lastname);
        $date = now()->format('F d, Y');

        $pdfFileName = 'agreement_' . uniqid() . '.pdf';
        $pdfPath = 'agreements/' . $pdfFileName;

        $pdf = Pdf::loadView('contracts.FINALAgreementForm', [
            'coach'        => $coach,
            'agreement'    => $agreement,
            'client_name'  => $client_name,
            'date'         => $date,
        ]);

        Storage::disk('public')->put($pdfPath, $pdf->output());

        Message::create([
            'sender_id'     => $client->client_id,
            'sender_type'   => \App\Models\Client::class,
            'receiver_id'   => $coach_id,
            'receiver_type' => \App\Models\Coach::class,
            'message'       => "A new Agreement Contract is ready for your review and signature.",
            'media_path'    => $pdfPath,
        ]);

        return redirect()->route('messages.index', [
                            'with_id'   => $coach->coach_id,
                            'with_type' => 'coach'
                        ])
                        ->with('success', 'Agreement submitted and sent to your coach!');
    }
}
