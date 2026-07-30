@auth
    @if(Auth::user()?->company_id)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const companyId = {{ Auth::user()->company_id }};

                // Synthesize audio chime using Web Audio API (zero external asset dependency)
                function playNotificationSound() {
                    try {
                        const AudioCtx = window.AudioContext || window.webkitAudioContext;
                        if (!AudioCtx) return;
                        const ctx = new AudioCtx();
                        
                        const osc1 = ctx.createOscillator();
                        const osc2 = ctx.createOscillator();
                        const gain = ctx.createGain();

                        osc1.type = 'sine';
                        osc2.type = 'sine';
                        
                        osc1.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
                        osc2.frequency.setValueAtTime(880.00, ctx.currentTime); // A5

                        gain.gain.setValueAtTime(0.15, ctx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);

                        osc1.connect(gain);
                        osc2.connect(gain);
                        gain.connect(ctx.destination);

                        osc1.start(ctx.currentTime);
                        osc2.start(ctx.currentTime + 0.08);
                        osc1.stop(ctx.currentTime + 0.4);
                        osc2.stop(ctx.currentTime + 0.4);
                    } catch (e) {
                        console.warn('Audio chime playback blocked or not supported:', e);
                    }
                }

                window.playNotificationChime = playNotificationSound;

                // Request desktop notification permission helper
                window.requestNotificationPermission = function (callback) {
                    if (!("Notification" in window)) {
                        alert("Desktop notifications are not supported by your web browser.");
                        return;
                    }

                    if (Notification.permission === "granted") {
                        if (callback) callback(true);
                        return;
                    }

                    Notification.requestPermission().then(function (permission) {
                        const isGranted = permission === "granted";
                        if (isGranted) {
                            playNotificationSound();
                        }
                        if (callback) callback(isGranted);
                    });
                };

                // Show Desktop Notification Popup
                function showDesktopNotification(data) {
                    if (!("Notification" in window) || Notification.permission !== "granted") {
                        return;
                    }

                    const sender = data.sender_name || data.phone_number || 'Contact';
                    const preview = data.message_preview || 'Sent a message';
                    const conversationId = data.conversation_id;

                    const notification = new Notification(`💬 WhatsApp: ${sender}`, {
                        body: preview,
                        icon: '/favicon.svg',
                        tag: `conversation-${conversationId}`,
                        renotify: true,
                    });

                    notification.onclick = function (event) {
                        event.preventDefault();
                        window.focus();
                        notification.close();

                        if (conversationId) {
                            const targetUrl = `/chats?conversation=${conversationId}`;
                            if (window.Livewire) {
                                window.Livewire.navigate(targetUrl);
                            } else {
                                window.location.href = targetUrl;
                            }
                        }
                    };
                }

                // Subscribe to Pusher / Echo channel
                if (window.Echo) {
                    window.Echo.private(`company.${companyId}.chats`)
                        .listen('.chat.inbound.received', function (e) {
                            playNotificationSound();
                            showDesktopNotification(e);
                        });
                }
            });
        </script>
    @endif
@endauth
