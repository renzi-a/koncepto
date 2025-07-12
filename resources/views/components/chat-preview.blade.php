<button
    id="chatToggleBtn"
    onclick="openMiniChat()"
    class="fixed bottom-6 right-6 bg-[#56AB2F] text-white p-3 rounded-full shadow-xl z-40"
>
    <img src="{{ asset('images/chat.png') }}" alt="Chat" class="w-6 h-6" />
</button>

<script>
    function toggleChatPreview() {
        const preview = document.getElementById('chatPreview');
        if (preview) {
            preview.style.display = preview.style.display === 'none' ? 'block' : 'none';
        }
    }

    function closeChat() {
        const preview = document.getElementById('chatPreview');
        if (preview) {
            preview.style.display = 'none';
        }
    }

    function openMiniChat() {
        const existing = document.getElementById('inlineMiniChat');
        if (existing) existing.remove();

        fetch("{{ route('user.chat.popup') }}")
            .then(res => res.text())
            .then(html => {
                const popupContainer = document.createElement('div');
                popupContainer.id = 'inlineMiniChat';
                popupContainer.className = 'fixed bottom-24 right-6 w-80 max-h-[70vh] bg-white rounded-lg shadow-xl border border-gray-200 z-50 overflow-hidden flex flex-col relative';

                popupContainer.innerHTML = html;

                const maximizeBtn = document.createElement('button');
                maximizeBtn.innerHTML = '⛶';
                maximizeBtn.title = 'Open Full Chat';
                maximizeBtn.className = 'absolute top-2 right-2 text-gray-600 hover:text-black text-xs font-bold bg-white border border-gray-300 rounded px-1';
                maximizeBtn.onclick = openFullChat;

                popupContainer.appendChild(maximizeBtn);
                document.body.appendChild(popupContainer);
            });
    }

    function openFullChat() {
        window.location.href = "{{ route('user.chat.full') }}";
    }

    window.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            const preview = document.getElementById('chatPreview');
            if (preview) preview.style.display = 'block';
        }, 5000);
    });
</script>
