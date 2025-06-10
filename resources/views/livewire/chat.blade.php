<div wire:poll.3s>
    <div class="row">
        <div class="col-md-6 col-lg-5 col-xl-4 mb-4 mb-md-0">
            <div class="p-3">
                <input type="text" wire:model.debounce.500ms="search" class="form-control mb-3" placeholder="Cari nama user...">
                <div class="pt-3 pe-3" style="position: relative; height: 400px; overflow-y: scroll;" wire:poll.3s>
                    <ul class="list-unstyled mb-0">
                        @foreach ($this->users as $user)
                        @php
                            $sent = $user->sentMessages->first();
                            $received = $user->receivedMessages->first();
                            $latestMessage = $sent && $received
                                ? ($sent->created_at > $received->created_at ? $sent : $received)
                                : ($sent ?: $received);
                        @endphp

                        <li class="p-2 border-bottom">
                            <a href="#" wire:click.prevent="selectUser({{ $user->id }})" class="d-flex justify-content-between">
                                <div class="d-flex flex-row">
                                    <div>
                                        <img src="{{ $user->gambar ?  asset('storage/file/img-upload/' . $user->gambar) : 'https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-chat/ava6-bg.webp' }}"
                                            alt="avatar" class="d-flex align-self-center me-3" style="border-radius: 50%" width="60">
                                        <span class="badge bg-success badge-dot"></span>
                                    </div>
                                    <div class="pt-1">
                                        <p class="fw-bold mb-0">{{ $user->name }}</p>
                                        <p class="small text-muted">{{ $latestMessage ? $latestMessage->message : 'No messages yet' }}</p>
                                    </div>
                                </div>
                                <div class="pt-1">
                                    <p class="small text-muted mb-1">{{ $latestMessage ? $latestMessage->created_at->diffForHumans() : '' }}</p>
                                    @if (isset($unreadMessagesCount[$user->id]))
                                        <span class="badge bg-danger rounded-pill float-end">{{ $unreadMessagesCount[$user->id] }}</span>
                                    @endif
                                </div>
                            </a>
                        </li>
                        @endforeach
                        @if ($this->users->isEmpty())
                            <li class="p-2 text-muted text-center">Tidak ada user ditemukan.</li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-7 col-xl-8">
            <div class="pt-3 pe-3" style="position: relative; height: 400px; overflow-y: scroll;">
                @foreach ($messages as $message)
                    <div class="d-flex flex-row {{ $message->from_user_id == Auth::id() ? 'justify-content-end' : 'justify-content-start' }}">
                        @if ($message->from_user_id != Auth::id())
                            <img src="{{ $message->fromUser->gambar ? asset('storage/file/img-upload/' . $message->fromUser->gambar) : 'https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-chat/ava6-bg.webp' }}" alt="avatar 1" style="width: 45px; height: 100%; border-radius: 50%;">
                        @endif
                        <div>
                            <p class="small p-2 ms-3 mb-1 rounded-3 {{ $message->from_user_id == Auth::id() ? 'bg-primary text-white' : 'bg-light text-dark' }}"
                            style=" word-break: break-word; overflow-wrap: break-word;">
                                {!! nl2br(e($message->message)) !!}
                            </p>
                            <p class="small ms-3 mb-3 rounded-3 text-muted">{{ $message->created_at->format('h:i A | M d') }}</p>
                        </div>
                        @if ($message->from_user_id == Auth::id())
                            <img src="{{ $message->fromUser->gambar ? asset('storage/file/img-upload/' . $message->fromUser->gambar) : 'https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-chat/ava1-bg.webp' }}" alt="avatar 1" style="width: 45px; height: 100%; border-radius: 50%;">
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="text-muted d-flex justify-content-start align-items-center pe-3 pt-3 mt-2">
                <img src="https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-chat/ava6-bg.webp" alt="avatar 3" style="width: 40px; height: 100%;">
                {{-- <input type="text" class="form-control form-control-lg" wire:model="messageText" placeholder="Type message"> --}}
                <!-- Textarea -->
<textarea
    class="form-control form-control-lg"
    wire:model="messageText"
    id="messageInput"
    placeholder="Type message"
    rows="1"
    style="resize: none; overflow:hidden"
    onkeydown="checkEnter(event)"
></textarea>

                <a class="ms-3" href="#" wire:click.prevent="sendMessage"><button class="btn"><i class="fas fa-paper-plane"></i></button></a>
            </div>
        </div>
    </div>
</div>
<script>
    function checkEnter(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault(); // Mencegah newline
            @this.call('sendMessage'); // Memanggil Livewire method
        }
    }
</script>

<script>
    document.addEventListener('livewire:load', function () {
        Livewire.hook('message.processed', (message, component) => {
            const chatContainer = document.querySelector('[wire\\:poll]');
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        });
    });
</script>
