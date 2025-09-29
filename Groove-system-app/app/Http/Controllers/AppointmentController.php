<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Notifications\GrooveNotification;
use App\Models\Client;
use App\Models\Coach;
use App\Models\Admin; 

use Carbon\Carbon;


class AppointmentController extends Controller
{
    public function show()
    {
        $coach = auth('coach')->user();
        if (!$coach) abort(403, 'You are not logged in as a coach.');

        $appointments = Appointment::with('client')
            ->where('coach_id', $coach->coach_id)
            ->orderByDesc('created_at')
            ->get();

        return view('appointments.appointmentdata', compact('appointments'));
    }

    public function index()
    {
        $appointments = Appointment::with(['client', 'coach'])
            ->orderByDesc('created_at')
            ->paginate(5);

        return view('appointments.appointmentdata', compact('appointments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'      => 'required|exists:clients,client_id',
            'coach_id'       => 'required|exists:coaches,coach_id',
            'firstname'      => 'required|string|max:255',
            'middlename'     => 'nullable|string|max:255',
            'lastname'       => 'required|string|max:255',
            'talent'         => 'nullable|string|max:255',
            'email'          => 'required|email|max:255',
            'contact'        => 'required|string|max:20',
            'address'        => 'required|string|max:255',
            'date'           => 'required|date',
            'start_time'     => 'required|string|max:20',
            'end_time'       => 'required|string|max:20',
            'session_type'   => 'in:F2F',
            'experience'     => 'required|string|max:255',
            'purpose'        => 'required|string|max:255',
            'message'        => 'nullable|string',
        ]);

        // Compose display name for record
        $validated['name'] = $validated['firstname']
            . ' '
            . ($validated['middlename'] ? $validated['middlename'] . ' ' : '')
            . $validated['lastname'];

        unset($validated['firstname'], $validated['middlename'], $validated['lastname']);
        $validated['session_type'] = 'F2F';

        // 1-per coach pending/confirmed constraint
        $existing = Appointment::where('client_id', $request->client_id)
            ->where('coach_id', $request->coach_id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'You already have a pending or confirmed appointment with this coach.');
        }

        // Generate unique 5-digit appointment_id
        do {
            $randomId = random_int(10000, 99999);
        } while (Appointment::where('appointment_id', $randomId)->exists());

        $validated['appointment_id'] = $randomId;
        $validated['status'] = 'pending';

        $appointment = Appointment::create($validated);

        // Notify coach
        if ($coach = Coach::find($appointment->coach_id)) {
            $coach->notify(new GrooveNotification(
                'New Appointment Request',
                "You have a new appointment request from client ID: {$appointment->client_id}"
            ));
        }

        // Notify client
        if ($client = Client::find($appointment->client_id)) {
            $client->notify(new GrooveNotification(
                'Appointment Submitted',
                "Your appointment request with coach ID {$appointment->coach_id} has been submitted."
            ));
        }

        // Notify admins
        foreach (\App\Models\Admin::all() as $admin) {
            $admin->notify(new GrooveNotification(
                'New Appointment Created',
                "Appointment #{$appointment->appointment_id} created between client ID {$appointment->client_id} and coach ID {$appointment->coach_id}."
            ));
        }

        return redirect()->back()->with('success', 'Appointment booked successfully.');
    }

  public function decline(Appointment $appointment)
{
    $coach = auth('coach')->user();
    if (! $coach) abort(403, 'Not logged in as coach.');

    // Ensure THIS coach owns the appointment
    if ((int) $appointment->coach_id !== (int) $coach->coach_id) {
        abort(403, 'You can only decline your own appointments.');
    }

    if ($appointment->status !== 'pending') {
        return back()->with('error', 'Only pending appointments can be declined.');
    }

    // Update by public key to avoid stale lookups
    Appointment::where('appointment_id', $appointment->appointment_id)
        ->update(['status' => 'declined']);

    $appointment->refresh();

    // Notify client
    if ($appointment->client) {
        $appointment->client->notify(new GrooveNotification(
            'Appointment Declined',
            "Your appointment #{$appointment->appointment_id} was declined by Coach {$coach->full_name}."
        ));
    }

    // Notify admins
    foreach (\App\Models\Admin::all() as $admin) {
        $admin->notify(new GrooveNotification(
            'Appointment Declined',
            "Appointment #{$appointment->appointment_id} was declined by Coach {$coach->full_name}."
        ));
    }

    return back()->with('success', 'Appointment declined. Client and admins have been notified.');
}

// app/Http/Controllers/AppointmentController.php

public function cancel(Appointment $appointment)
{
    // Client must be logged in
    $client = auth('client')->user();
    if (! $client) {
        abort(403, 'Not logged in as client.');
    }

    // Client can only cancel their own appointment
    if ((int) $appointment->client_id !== (int) $client->client_id) {
        abort(403, 'You can only cancel your own appointments.');
    }

    // Only pending or confirmed can be cancelled
    if (! in_array($appointment->status, ['pending', 'confirmed'], true)) {
        return back()->with('error', 'Only pending or confirmed appointments can be cancelled.');
    }

    // Set status to cancelled
    Appointment::where('appointment_id', $appointment->appointment_id)
        ->update(['status' => 'cancelled']);

    $appointment->refresh();

    // Notify coach
    if ($coach = Coach::where('coach_id', $appointment->coach_id)->first()) {
        $coach->notify(new GrooveNotification(
            'Appointment Cancelled by Client',
            "Client cancelled appointment #{$appointment->appointment_id}."
        ));
    }

// Notify client
    /** @var \App\Models\Client $client */
$client->notify(new GrooveNotification(
    'Appointment Cancelled',
    "You cancelled appointment #{$appointment->appointment_id}."
));

    // Notify all admins
    foreach (Admin::all() as $admin) {
        $admin->notify(new GrooveNotification(
            'Appointment Cancelled',
            "Appointment #{$appointment->appointment_id} has been cancelled by client."
        ));
    }

    return back()->with('success', 'Appointment cancelled successfully.');
}


    public function complete(Appointment $appointment)
    {
        if ($appointment->status !== 'confirmed') {
            return back()->with('error', 'Only confirmed appointments can be marked as completed.');
        }

        Appointment::where('appointment_id', $appointment->appointment_id)
            ->update(['status' => 'completed']);

        $appointment->refresh();

        $appointment->client?->notify(new GrooveNotification(
            'Appointment Completed',
            'Your appointment ID ' . $appointment->appointment_id . ' has been marked as completed.'
        ));

        return back()->with('success', 'Appointment marked as completed.');
    }

    public function submitFeedback(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        $client = auth('client')->user();
        if (!$client || $appointment->client_id !== $client->client_id) {
            abort(403, 'You are not authorized to submit feedback for this appointment.');
        }

        $request->validate([
            'feedback' => 'required|string|max:1000',
            'rating'   => 'required|integer|min:1|max:5',
        ]);

        $appointment->feedback = $request->feedback;
        $appointment->rating   = $request->rating;
        $appointment->save();

        if ($coach = Coach::where('coach_id', $appointment->coach_id)->first()) {
            $coach->notify(new GrooveNotification(
                'New Feedback Received',
                'You received new feedback for appointment ID ' . $appointment->appointment_id
            ));
        }

        return redirect()->back()->with('success', 'Feedback and rating submitted successfully. Thank you!');
    }

    public function confirm(Appointment $appointment)
    {
        $coach = auth('coach')->user();
        if (! $coach) abort(403, 'Not logged in as coach.');

        if ($appointment->status !== 'pending') {
            return back()->with('error', 'Only pending appointments can be confirmed.');
        }

        Appointment::where('appointment_id', $appointment->appointment_id)
            ->update(['status' => 'confirmed']);

        $appointment->refresh();

        if ($appointment->client) {
            $appointment->client->notify(new GrooveNotification(
                'Appointment Confirmed',
                "Your appointment #{$appointment->appointment_id} has been confirmed by Coach {$coach->full_name}."
            ));
        }

        foreach (\App\Models\Admin::all() as $admin) {
            $admin->notify(new GrooveNotification(
                'Appointment Confirmed',
                "Appointment #{$appointment->appointment_id} has been confirmed by Coach {$coach->full_name}."
            ));
        }

        return back()->with('success', 'Appointment confirmed. Client and admins have been notified.');
    }

    public function approveProfile($clientId)
    {
        $client = Client::findOrFail($clientId);
        $client->status = 'approved';
        $client->save();

        $client->notify(new GrooveNotification(
            'Profile Approved',
            'Your client profile has been approved and is now visible on the platform.'
        ));

        return back()->with('success', 'Client profile approved. Notification sent.');
    }

public function calendar()
{
    $client = auth('client')->user();
    $coach  = auth('coach')->user();

    $events = [];
    $appointments = collect();
    $now = now();

    if ($client) {
        $appointments = Appointment::where('client_id', $client->client_id)
            ->with('coach')
            ->orderByDesc('date')
            ->get();

        foreach ($appointments as $appointment) {
            // Build end & start using Carbon (no string concatenation)
            $endDateTime = $appointment->end_at
                ?? Carbon::parse((string) $appointment->date)
                         ->setTimeFromTimeString((string) $appointment->end_time);

            if ($appointment->status === 'cancelled' || $endDateTime->lt($now)) {
                continue;
            }

            $startDateTime = $appointment->start_at
                ?? Carbon::parse((string) $appointment->date)
                         ->setTimeFromTimeString((string) $appointment->start_time);

            $events[] = [
                'id'    => $appointment->appointment_id,
                'title' => $appointment->name . ' - ' . ($appointment->talent ?? ''),
                // FullCalendar-friendly ISO 8601
                'start' => $startDateTime->format('Y-m-d\TH:i:s'),
                'end'   => $endDateTime->format('Y-m-d\TH:i:s'),
                'extendedProps' => [
                    'status' => $appointment->status,
                    'coach'  => $appointment->coach ? $appointment->coach->full_name : 'Unknown Coach',
                ],
            ];
        }

        // View exists at resources/views/Client/calendar.blade.php
        return view('Client.calendar', compact('events', 'appointments'));

    } elseif ($coach) {
        $appointments = Appointment::where('coach_id', $coach->coach_id)
            ->with('client')
            ->orderByDesc('date')
            ->get();

        foreach ($appointments as $appointment) {
            $endDateTime = $appointment->end_at
                ?? Carbon::parse((string) $appointment->date)
                         ->setTimeFromTimeString((string) $appointment->end_time);

            if ($appointment->status === 'cancelled' || $endDateTime->lt($now)) {
                continue;
            }

            $startDateTime = $appointment->start_at
                ?? Carbon::parse((string) $appointment->date)
                         ->setTimeFromTimeString((string) $appointment->start_time);

            $clientName = $appointment->client
                ? $appointment->client->firstname . ' ' . $appointment->client->lastname
                : 'Unknown Client';

            $events[] = [
                'id'    => $appointment->appointment_id,
                'title' => $clientName . ' - ' . ($appointment->talent ?? ''),
                'start' => $startDateTime->format('Y-m-d\TH:i:s'),
                'end'   => $endDateTime->format('Y-m-d\TH:i:s'),
                'extendedProps' => [
                    'status' => $appointment->status,
                    'client' => $clientName,
                ],
            ];
        }

        // View exists at resources/views/Coach/calendar.blade.php
        return view('Coach.calendar', compact('events', 'appointments'));
    }

    abort(403, 'Unauthorized access to calendar');
}

    public function showCoachAppointment($coachId)
    {
        $user = Coach::findOrFail($coachId);

        $client = auth('client')->user();
        if (!$client) abort(403, 'You must be logged in as a client.');

        $appointment = Appointment::where('client_id', $client->client_id)
            ->where('coach_id', $user->coach_id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->latest()
            ->first();

        return view('Client.appointment', ['user' => $user, 'appointment' => $appointment]);
    }
}
