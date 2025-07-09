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
        preview.style.display = preview.style.display === 'none' ? 'block' : 'none';
    }

    function closeChat() {
        document.getElementById('chatPreview').style.display = 'none';
    }

    function openMiniChat() {
        fetch("{{ route('user.chat.popup') }}")
            .then(res => res.text())
            .then(html => {
                const popupContainer = document.createElement('div');
                popupContainer.id = 'inlineMiniChat';
                popupContainer.innerHTML = html;
                document.body.appendChild(popupContainer);
            });
    }


    function openFullChat() {
        window.location.href = "{{ route('user.chat.full') }}";
    }

    window.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            document.getElementById('chatPreview').style.display = 'block';
        }, 5000);
    });
</script>
