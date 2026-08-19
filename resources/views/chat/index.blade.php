@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto flex flex-col h-[calc(100vh-12rem)] min-h-[550px] animate-fade-in">
    
    <!-- Chat Header -->
    <div class="bg-white/90 dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 backdrop-blur-xl rounded-t-3xl p-5 sm:p-6 flex items-center justify-between shadow-xl">
        <div class="flex items-center space-x-3.5">
            <div class="relative">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-purple-600 flex items-center justify-center text-white text-xl shadow-lg shadow-blue-500/25">
                    🤖
                </div>
                <span class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-emerald-500 border-2 border-white dark:border-slate-900 rounded-full"></span>
            </div>
            <div>
                <h1 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2 font-display">
                    DreamPC AI Hardware Assistant
                    <span class="text-[9px] bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 px-2 py-0.5 rounded-full font-bold uppercase tracking-wider font-mono">NLP Engine</span>
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Ask about custom builds, socket compatibility, or component specs</p>
            </div>
        </div>
        <div class="hidden sm:flex items-center text-xs text-slate-600 dark:text-slate-400 gap-1.5 bg-slate-100 dark:bg-slate-800/80 px-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700/50 shadow-sm font-mono">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Online & Ready</span>
        </div>
    </div>

    <!-- Messages Container -->
    <div id="chat-messages" class="flex-grow bg-slate-50/70 dark:bg-slate-950/60 border-x border-slate-200/80 dark:border-slate-800/80 p-4 sm:p-6 overflow-y-auto space-y-4 shadow-inner">
        
        <!-- Welcome Message -->
        <div class="flex items-start space-x-3">
            <div class="w-8 h-8 rounded-xl bg-indigo-500/10 dark:bg-indigo-600/30 border border-indigo-500/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm flex-shrink-0">
                🤖
            </div>
            <div class="bg-white dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 rounded-3xl rounded-tl-none p-4 sm:p-5 text-slate-800 dark:text-slate-200 text-xs sm:text-sm max-w-[85%] shadow-md leading-relaxed">
                👋 Hello! I am your <strong>DreamPC AI Hardware Assistant</strong>. I can configure full 100% compatible 7-part PC builds, verify Socket & RAM generations, check PSU wattage headroom, or advise on individual GPUs & CPUs. What would you like to build or check today?
                <div class="mt-2 text-[10px] text-slate-400 text-right font-mono">System</div>
            </div>
        </div>

        <!-- Render Database Session Messages -->
        @foreach($messages as $msg)
            @if($msg->sender === 'user')
                <div class="flex items-start justify-end space-x-3">
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-3xl rounded-tr-none p-4 text-xs sm:text-sm max-w-[85%] shadow-lg shadow-blue-500/15 leading-relaxed">
                        {{ $msg->message_text }}
                        <div class="mt-1.5 text-[10px] text-blue-200 text-right font-mono">{{ $msg->created_at->format('H:i') }}</div>
                    </div>
                    <div class="w-8 h-8 rounded-xl bg-blue-500/10 dark:bg-blue-600/20 border border-blue-500/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs font-bold flex-shrink-0">
                        👤
                    </div>
                </div>
            @else
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 rounded-xl bg-indigo-500/10 dark:bg-indigo-600/30 border border-indigo-500/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm flex-shrink-0">
                        🤖
                    </div>
                    <div class="bg-white dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 rounded-3xl rounded-tl-none p-4 sm:p-5 text-slate-800 dark:text-slate-200 text-xs sm:text-sm max-w-[85%] shadow-md leading-relaxed">
                        {!! nl2br(e($msg->message_text)) !!}
                        
                        @if(!empty($msg->json_payload['card_html']))
                            {!! $msg->json_payload['card_html'] !!}
                        @elseif(!empty($msg->json_payload['suggested_products']))
                            <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800/80 space-y-2">
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-400">Suggested Components:</span>
                                <div class="grid grid-cols-1 gap-2">
                                    @foreach($msg->json_payload['suggested_products'] as $item)
                                        <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 px-3 py-2 rounded-xl text-xs">
                                            <span class="text-slate-800 dark:text-slate-200 font-bold truncate">{{ $item['name'] }}</span>
                                            <span class="text-emerald-600 dark:text-emerald-400 font-black font-mono ml-2">{{ $item['price'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        <div class="mt-2 text-[10px] text-slate-400 text-right font-mono">{{ $msg->created_at->format('H:i') }}</div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <!-- Quick Prompt Pills -->
    <div class="bg-slate-100 dark:bg-slate-950 border-x border-t border-slate-200/80 dark:border-slate-800/80 px-4 py-2.5 flex items-center gap-2 overflow-x-auto text-xs no-scrollbar">
        <span class="text-slate-500 dark:text-slate-400 text-[11px] font-bold uppercase font-mono flex-shrink-0">Suggested:</span>
        <button type="button" onclick="sendQuickPrompt('Build a high performance gaming PC under $1500')" class="whitespace-nowrap bg-white dark:bg-slate-900 hover:bg-blue-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 px-3.5 py-1.5 rounded-full transition hover:text-blue-600 dark:hover:text-white shadow-sm">
            💡 Build gaming PC under $1500
        </button>
        <button type="button" onclick="sendQuickPrompt('How does the compatibility engine work?')" class="whitespace-nowrap bg-white dark:bg-slate-900 hover:bg-blue-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 px-3.5 py-1.5 rounded-full transition hover:text-blue-600 dark:hover:text-white shadow-sm">
            ⚙️ How compatibility check works
        </button>
        <button type="button" onclick="sendQuickPrompt('Recommend a GPU for 1440p gaming')" class="whitespace-nowrap bg-white dark:bg-slate-900 hover:bg-blue-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 px-3.5 py-1.5 rounded-full transition hover:text-blue-600 dark:hover:text-white shadow-sm">
            🎮 1440p GPU advice
        </button>
    </div>

    <!-- Chat Input Area -->
    <div class="bg-white/90 dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 rounded-b-3xl p-3.5 sm:p-5 shadow-2xl backdrop-blur-xl">
        <form id="chat-form" class="flex items-center space-x-2">
            @csrf
            <input type="text" id="message-input" placeholder="Type your build request or hardware question..." 
                   class="flex-grow bg-slate-50 dark:bg-slate-950/90 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm rounded-2xl px-4 py-3.5 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-slate-400 transition" required>
            <button type="submit" id="send-btn" 
                    class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white px-6 py-3.5 rounded-2xl font-bold text-xs sm:text-sm transition shadow-lg shadow-blue-500/20 flex items-center space-x-2 flex-shrink-0 disabled:opacity-50 hover:scale-105 transform">
                <span>Send</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chat-form');
    const messageInput = document.getElementById('message-input');
    const sendBtn = document.getElementById('send-btn');
    const chatMessages = document.getElementById('chat-messages');

    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    scrollToBottom();

    window.sendQuickPrompt = function(text) {
        messageInput.value = text;
        chatForm.dispatchEvent(new Event('submit'));
    };

    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const text = messageInput.value.trim();
        if (!text) return;

        messageInput.value = '';
        sendBtn.disabled = true;

        const currentTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        const userBubbleHtml = `
            <div class="flex items-start justify-end space-x-3 animate-fade-in">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-3xl rounded-tr-none p-4 text-xs sm:text-sm max-w-[85%] shadow-lg shadow-blue-500/15 leading-relaxed">
                    ${escapeHtml(text)}
                    <div class="mt-1.5 text-[10px] text-blue-200 text-right font-mono">${currentTime}</div>
                </div>
                <div class="w-8 h-8 rounded-xl bg-blue-500/10 dark:bg-blue-600/20 border border-blue-500/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs font-bold flex-shrink-0">
                    👤
                </div>
            </div>
        `;
        chatMessages.insertAdjacentHTML('beforeend', userBubbleHtml);
        scrollToBottom();

        const spinnerId = 'spinner-' + Date.now();
        const spinnerHtml = `
            <div id="${spinnerId}" class="flex items-start space-x-3 animate-fade-in">
                <div class="w-8 h-8 rounded-xl bg-indigo-500/10 dark:bg-indigo-600/30 border border-indigo-500/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm flex-shrink-0 animate-spin">
                    🌀
                </div>
                <div class="bg-white dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 rounded-3xl rounded-tl-none p-4 text-slate-500 dark:text-slate-400 text-xs shadow-md flex items-center space-x-2">
                    <div class="flex space-x-1">
                        <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce [animation-delay:-0.15s]"></div>
                        <div class="w-2 h-2 bg-purple-500 rounded-full animate-bounce [animation-delay:-0.3s]"></div>
                    </div>
                    <span class="text-xs font-semibold ml-2">AI Assistant is assembling specs...</span>
                </div>
            </div>
        `;
        chatMessages.insertAdjacentHTML('beforeend', spinnerHtml);
        scrollToBottom();

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch('/chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ message: text })
            });

            const data = await response.json();
            document.getElementById(spinnerId)?.remove();

            if (data.status === 'success' && data.bot_message) {
                let cardHtml = '';
                if (data.bot_message.payload && data.bot_message.payload.card_html) {
                    cardHtml = data.bot_message.payload.card_html;
                } else if (data.bot_message.payload && data.bot_message.payload.suggested_products) {
                    const items = data.bot_message.payload.suggested_products.map(p => `
                        <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 px-3 py-2 rounded-xl text-xs">
                            <span class="text-slate-800 dark:text-slate-200 font-bold truncate">${escapeHtml(p.name)}</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-black font-mono ml-2">${p.price}</span>
                        </div>
                    `).join('');
                    cardHtml = `
                        <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800/80 space-y-2">
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-400">Suggested Components:</span>
                            <div class="grid grid-cols-1 gap-2">${items}</div>
                        </div>
                    `;
                }

                const botBubbleHtml = `
                    <div class="flex items-start space-x-3 animate-fade-in">
                        <div class="w-8 h-8 rounded-xl bg-indigo-500/10 dark:bg-indigo-600/30 border border-indigo-500/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm flex-shrink-0">
                            🤖
                        </div>
                        <div class="bg-white dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 rounded-3xl rounded-tl-none p-4 sm:p-5 text-slate-800 dark:text-slate-200 text-xs sm:text-sm max-w-[85%] shadow-md leading-relaxed">
                            ${escapeHtml(data.bot_message.text).replace(/\n/g, '<br>')}
                            ${cardHtml}
                            <div class="mt-2 text-[10px] text-slate-400 text-right font-mono">${data.bot_message.created_at}</div>
                        </div>
                    </div>
                `;
                chatMessages.insertAdjacentHTML('beforeend', botBubbleHtml);
            }
        } catch (error) {
            console.error(error);
            document.getElementById(spinnerId)?.remove();
            const errorHtml = `
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 rounded-xl bg-red-500/10 border border-red-500/30 text-red-500 flex items-center justify-center text-xs flex-shrink-0">
                        ⚠️
                    </div>
                    <div class="bg-red-500/10 border border-red-500/20 rounded-3xl rounded-tl-none p-4 text-red-700 dark:text-red-300 text-xs">
                        Sorry, an error occurred while connecting to the assistant. Please try again.
                    </div>
                </div>
            `;
            chatMessages.insertAdjacentHTML('beforeend', errorHtml);
        } finally {
            sendBtn.disabled = false;
            scrollToBottom();
        }
    });

    function escapeHtml(str) {
        return str.replace(/&/g, "&amp;")
                  .replace(/</g, "&lt;")
                  .replace(/>/g, "&gt;")
                  .replace(/"/g, "&quot;")
                  .replace(/'/g, "&#039;");
    }
});
</script>
@endsection
