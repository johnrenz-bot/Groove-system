<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Chat with DeepSeek V3.1</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
    body { font-family: system-ui, sans-serif; max-width: 760px; margin: 2rem auto; }
    #chat-box { border: 1px solid #ddd; border-radius: 8px; padding: 12px; height: 360px; overflow-y: auto; }
    .user { color: #2563eb; margin: .25rem 0; }
    .bot  { color: #059669; margin: .25rem 0; }
    .error { color: #b91c1c; margin: .25rem 0; }
    form { display: flex; gap: .5rem; margin-top: .75rem; }
    input[type=text] { flex: 1; padding: .6rem .7rem; }
    button { padding: .6rem .9rem; cursor: pointer; }
  </style>
</head>
<body>
  <h1>Chat with DeepSeek V3.1</h1>

  <div id="chat-box"></div>

  <form id="chat-form">
    @csrf
    <input id="message-input" type="text" name="message" autocomplete="off" placeholder="Type your message…" required>
    <button type="submit">Send</button>
  </form>

  <script>
    const chat = document.getElementById('chat-box');
    const input = document.getElementById('message-input');
    const form  = document.getElementById('chat-form');

    function appendMessage(who, message, cls) {
      const p = document.createElement('p');
      p.className = cls;
      p.textContent = who + ': ' + message;
      chat.appendChild(p);
      chat.scrollTop = chat.scrollHeight;
    }

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const text = input.value.trim();
      if (!text) return;

      appendMessage('You', text, 'user');
      input.value = '';

      try {
        const resp = await fetch('{{ route('chatbot.send') }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({ message: text })
        });

        const data = await resp.json();
        if (resp.ok && data.reply) {
          appendMessage('Bot', data.reply, 'bot');
        } else {
          appendMessage('Bot', 'Error: ' + (data.error || 'Unknown error'), 'error');
        }
      } catch (err) {
        appendMessage('Bot', 'Request failed: ' + err.message, 'error');
      }
    });
  </script>
</body>
</html>
