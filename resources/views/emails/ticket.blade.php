<!doctype html>
<html>
  <head><meta charset="utf-8"><title>New Support Ticket</title></head>
  <body style="font-family:Arial, Helvetica, sans-serif; color:#111;">
    <h2>New Support Ticket</h2>
    <p><strong>From:</strong> {{ $ticket->name }} &lt;{{ $ticket->email }}&gt;</p>
    @if($ticket->client_id || $ticket->coach_id)
      <p>
        @if($ticket->client_id)<strong>Client ID:</strong> {{ $ticket->client_id }}<br>@endif
        @if($ticket->coach_id)<strong>Coach ID:</strong> {{ $ticket->coach_id }}<br>@endif
      </p>
    @endif
    <p><strong>Subject:</strong> {{ $ticket->subject }}</p>
    <hr>
    <div>{!! nl2br(e($ticket->message)) !!}</div>
    <hr>
    @if($ticket->attachment_name)
      <p><strong>Attachment:</strong> {{ $ticket->attachment_name }} ({{ $ticket->attachment_mime }})</p>
    @endif
    <p style="color:#777">Ticket #{{ $ticket->id }} • {{ $ticket->created_at->toDayDateTimeString() }}</p>
  </body>
</html>
