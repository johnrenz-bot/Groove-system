<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class PublicTicketController extends Controller
{
    public function store(Request $req)
    {
        try {
            $data = $req->validate([
                'client_id' => 'nullable|string|max:10',
                'coach_id'  => 'nullable|string|max:10',
                'name'      => 'required|string|max:120',
                'email'     => 'required|email',
                'subject'   => 'required|string|max:160',
                'message'   => 'required|string|max:5000',
                'attachment'=> 'nullable|image|max:5120', // ~5MB
            ]);

            // Create DB record first
            $ticket = new Ticket([
                'client_id' => $data['client_id'] ?? null,
                'coach_id'  => $data['coach_id'] ?? null,
                'name'      => $data['name'],
                'email'     => $data['email'],
                'subject'   => $data['subject'],
                'message'   => $data['message'],
                'status'    => 'open',
                'priority'  => 'normal',
            ]);

            // Optional attachment
            if ($req->hasFile('attachment')) {
                $path = $req->file('attachment')->store('tickets', 'public');
                $ticket->attachment_path = $path;
                $ticket->attachment_name = $req->file('attachment')->getClientOriginalName();
                $ticket->attachment_mime = $req->file('attachment')->getClientMimeType();
                $ticket->attachment_size = $req->file('attachment')->getSize();
                $ticket->attachment_count = 1;
            }
            $ticket->save();

            // Build email HTML (make sure the view below exists)
            $html = view('emails.ticket', ['ticket' => $ticket])->render();

            // Where to send
            $toAddress = config('support.tickets_to', config('mail.from.address'));
            $fromAddr  = config('mail.from.address');
            $fromName  = config('mail.from.name', 'Groove');

            try {
                Mail::html($html, function ($m) use ($ticket, $toAddress, $fromAddr, $fromName) {
                    $m->to($toAddress)
                      ->subject('[Groove] ' . $ticket->subject . ' — from ' . $ticket->email);

                    if ($fromAddr) {
                        $m->from($fromAddr, $fromName);
                    }
                    $m->replyTo($ticket->email, $ticket->name);

                    if ($ticket->attachment_path) {
                        $m->attach(
                            Storage::disk('public')->path($ticket->attachment_path),
                            ['as' => $ticket->attachment_name, 'mime' => $ticket->attachment_mime]
                        );
                    }
                });
            } catch (Throwable $mailErr) {
                // Don’t fail the request—just log it.
                Log::warning('ticket_mail_failed: '.$mailErr->getMessage());
            }

            return response()->json(['ok' => true, 'id' => $ticket->id]);
        } catch (ValidationException $e) {
            return response()->json([
                'ok'     => false,
                'reason' => 'validation_error',
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            Log::error('ticket_submit_failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'ok'     => false,
                'reason' => 'server_error',
                'msg'    => config('app.debug') ? $e->getMessage() : 'Server error',
            ], 500);
        }
    }
}
