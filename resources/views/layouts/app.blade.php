<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Science Literacy Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --accent: #3b82f6;
            --accent-hover: #2563eb;
            --success: #10b981;
            --error: #ef4444;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .navbar {
            width: 100%;
            padding: 15px 40px;
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(10px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .nav-links a {
            color: var(--text-main);
            text-decoration: none;
            margin-left: 20px;
            font-weight: 600;
            transition: color 0.3s;
        }
        .nav-links a:hover {
            color: var(--accent);
        }

        .container {
            width: 100%;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }

        .glass-panel {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.8s ease-out;
        }

        h1, h2, h3 {
            font-weight: 800;
            margin-bottom: 20px;
            background: -webkit-linear-gradient(45deg, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        h1 { font-size: 2.5rem; text-align: center; }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .alert-error { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid var(--error); }
        .alert-success { background: rgba(16, 185, 129, 0.2); color: #6ee7b7; border: 1px solid var(--success); }

        .btn {
            background: var(--accent);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
            text-decoration: none;
            text-align: center;
        }

        .btn:hover {
            background: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.2);
            background: rgba(0,0,0,0.2);
            color: white;
            font-size: 1rem;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="{{ route('home') }}" style="color: white; text-decoration: none; font-weight: 800; font-size: 1.2rem;">Sci-Literacy</a>
        <div class="nav-links">
            <a href="{{ route('leaderboard') }}">Leaderboard</a>
            <a href="{{ route('myths.index') }}">Mythbusters</a>
            @auth
                <a href="{{ route('dashboard') }}">Dashboard</a>
                @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.questions.index') }}" style="color: var(--success);">Admin Panel</a>
                @endif
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" style="background:none; border:none; color: var(--text-main); margin-left: 20px; font-weight: 600; cursor: pointer; font-family: 'Inter', sans-serif;">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}" class="btn" style="padding: 8px 16px;">Sign Up</a>
            @endauth
        </div>
    </nav>
    <div class="container">
        @yield('content')
    </div>

    @auth
    <!-- AI Tutor Chat Widget -->
    <style>
        #chat-widget {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 350px;
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(167, 139, 250, 0.3);
            border-radius: 16px;
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.6);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transform: translateY(120%);
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 1000;
        }
        #chat-widget.open {
            transform: translateY(0);
        }
        #chat-header {
            background: linear-gradient(135deg, #60a5fa, #a78bfa);
            padding: 15px 20px;
            color: white;
            font-weight: 800;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }
        #chat-body {
            height: 300px;
            overflow-y: auto;
            padding: 15px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .chat-bubble {
            max-width: 85%;
            padding: 10px 15px;
            border-radius: 12px;
            font-size: 0.9rem;
            line-height: 1.4;
            animation: fadeIn 0.3s ease;
        }
        .chat-bot {
            background: rgba(167, 139, 250, 0.15);
            color: #e2e8f0;
            border-bottom-left-radius: 4px;
            align-self: flex-start;
        }
        .chat-user {
            background: var(--accent);
            color: white;
            border-bottom-right-radius: 4px;
            align-self: flex-end;
        }
        #chat-input-container {
            display: flex;
            padding: 10px;
            border-top: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.2);
        }
        #chat-input {
            flex: 1;
            background: transparent;
            border: none;
            color: white;
            padding: 8px 12px;
            font-family: 'Inter', sans-serif;
        }
        #chat-input:focus { outline: none; }
        #chat-submit {
            background: none;
            border: none;
            color: #a78bfa;
            cursor: pointer;
            padding: 8px;
            font-weight: bold;
        }
        #chat-toggle {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #60a5fa, #a78bfa);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 10px 25px -5px rgba(96, 165, 250, 0.5);
            z-index: 999;
            border: none;
            transition: transform 0.3s;
        }
        #chat-toggle:hover { transform: scale(1.1); }
    </style>

    <button id="chat-toggle">💬</button>
    
    <div id="chat-widget">
        <div id="chat-header">
            <span>✨ AI Science Tutor</span>
            <span id="chat-close" style="font-size: 1.2rem; line-height: 1;">&times;</span>
        </div>
        <div id="chat-body">
            <div class="chat-bubble chat-bot">Hello! I'm your AI science tutor. Ask me any science-related question!</div>
        </div>
        <div id="chat-input-container">
            <input type="text" id="chat-input" placeholder="Ask a question..." autocomplete="off">
            <button id="chat-submit">Send</button>
        </div>
    </div>

    <script>
        const chatWidget = document.getElementById('chat-widget');
        const chatToggle = document.getElementById('chat-toggle');
        const chatClose = document.getElementById('chat-close');
        const chatBody = document.getElementById('chat-body');
        const chatInput = document.getElementById('chat-input');
        const chatSubmit = document.getElementById('chat-submit');

        let historyLoaded = false;

        async function loadHistory() {
            if (historyLoaded) return;
            try {
                const response = await fetch('{{ route('chat.history') }}');
                const result = await response.json();
                if (result.success && result.history && result.history.length > 0) {
                    chatBody.innerHTML = ''; // Clear default greeting
                    result.history.forEach(log => {
                        addMessage(log.message, 'user');
                        addMessage(log.reply, 'bot');
                    });
                }
                historyLoaded = true;
            } catch (e) {
                console.error("Failed to load chat history", e);
            }
        }

        chatToggle.addEventListener('click', () => {
            chatWidget.classList.add('open');
            chatToggle.style.display = 'none';
            loadHistory();
        });

        chatClose.addEventListener('click', () => {
            chatWidget.classList.remove('open');
            setTimeout(() => { chatToggle.style.display = 'flex'; }, 300);
        });

        chatHeader = document.getElementById('chat-header');
        chatHeader.addEventListener('click', (e) => {
            if(e.target !== chatClose) {
                chatWidget.classList.remove('open');
                setTimeout(() => { chatToggle.style.display = 'flex'; }, 300);
            }
        });

        function addMessage(text, sender) {
            const el = document.createElement('div');
            el.className = `chat-bubble chat-${sender}`;
            el.innerText = text;
            chatBody.appendChild(el);
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        async function sendMessage() {
            const text = chatInput.value.trim();
            if(!text) return;
            
            addMessage(text, 'user');
            chatInput.value = '';
            
            // Show typing indicator
            const typingEl = document.createElement('div');
            typingEl.className = `chat-bubble chat-bot`;
            typingEl.innerText = 'Thinking...';
            chatBody.appendChild(typingEl);
            chatBody.scrollTop = chatBody.scrollHeight;

            try {
                const response = await fetch('{{ route('chat.send') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ message: text })
                });
                const result = await response.json();
                chatBody.removeChild(typingEl);
                if(result.success) {
                    addMessage(result.reply, 'bot');
                } else {
                    addMessage('Sorry, I encountered an error.', 'bot');
                }
            } catch(e) {
                chatBody.removeChild(typingEl);
                addMessage('Network error.', 'bot');
            }
        }

        chatSubmit.addEventListener('click', sendMessage);
        chatInput.addEventListener('keypress', (e) => {
            if(e.key === 'Enter') sendMessage();
        });
    </script>
    @endauth
</body>
</html>
