<div>
    <div class="chat-history" style="position: relative; height: 500px; overflow-y: scroll">
        <ul class="m-b-0">
            @foreach ($komentars as $komentar)
                <li class="clearfix">
                    <div class="message-data text-{{ $komentar->user_id == Auth::id() ? 'right' : 'left' }}">
                        {{ $komentar->user->name }}
                        <img src="{{ $komentar->user->gambar ?  asset('storage/file/img-upload/' . $komentar->user->gambar) : 'https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-chat/ava6-bg.webp' }}" alt="avatar">
                    </div>
                    <div class="message-data {{ $komentar->user_id == Auth::id() ? 'text-right' : '' }}">
                        <span class="message-data-time">{{ $komentar->created_at->format('h:i A, d M Y') }}</span>
                    </div>
                    <div
                        class="message {{ $komentar->user_id == Auth::id() ? 'other-message float-right' : 'my-message' }}">
                        {{ $komentar->pesan }}
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="chat-message clearfix">
        <form wire:submit.prevent="submit">
            <div class="input-group mb-0">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa fa-send"></i></span>
                </div>
                <input type="text" class="form-control" placeholder="Enter text here..." wire:model="pesan">
            </div>
        </form>
    </div>
</div>
