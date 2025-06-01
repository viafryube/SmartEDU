@extends('layout.template.mainTemplate')

@section('container')
    {{-- Cek peran pengguna --}}
    @if (Auth()->user()->roles_id == 1)
        @include('menu.admin.adminHelper')
    @endif

    {{-- Navigasi Breadcrumb --}}
    <div class="col-12 ps-4 pe-4 mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-white">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">
                    <a
                        href="{{ route('viewKelasMapel', ['mapel' => $mapel['id'], 'token' => encrypt($kelasId), 'mapel_id' => $mapel['id']]) }}">
                        {{ $mapel['name'] }}
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Update Pengumuman</li>
            </ol>
        </nav>
    </div>

    {{-- Judul Halaman --}}
    <div class="ps-4 pe-4 mt-4  pt-4">
        <h2 class="display-6 fw-bold">
            <a
                href="{{ route('viewKelasMapel', ['mapel' => $mapel['id'], 'token' => encrypt($kelasId), 'mapel_id' => $mapel['id']]) }}">
                <button type="button" class="btn btn-outline-secondary rounded-circle">
                    <i class="fa-solid fa-arrow-left"></i>
                </button>
            </a> Update Pengumuman
        </h2>
    </div>

    {{-- Formulir Update Pengumuman --}}
    <div class="">
        <div class="row p-4">
            <h4 class="fw-bold text-primary"><i class="fa-solid fa-pen"></i> Data Pengumuman</h4>
            <div class="col-12 col-lg-12 bg-white rounded-2">
                <div class="mt-4">
                    <div class="p-4">
                        <form action="{{ route('updatePengumuman') }}" id="form-main" method="GET"
                            enctype="multipart/form-data">
                            @csrf
                            {{-- Status Open / Close --}}
                            <div class="mb-3 row">
                                <div class="col-8 col-lg-4">
                                    <label for="opened" class="form-label d-block">Aktif<span class="small">(apakah
                                            sudah bisa diakses?)</span></label>
                                </div>
                                <div class="col-4 col-lg form-check form-switch">
                                    <input class="form-check-input" name="opened" type="checkbox" role="switch"
                                        id="opened" @if ($pengumuman->isHidden == 0) checked @endif>
                                </div>
                            </div>
                            {{-- Nama Pengumuman --}}
                            <div class="mb-3">
                                <label for="nama" class="form-label">Judul Pengumuman</label>
                                <input type="hidden" name="kelasId" value="{{ encrypt($kelasId) }}" readonly>
                                <input type="hidden" name="pengumumanId" value="{{ encrypt($pengumuman['id']) }}" readonly>
                                <input type="hidden" name="mapelId" value="{{ $mapel['id'] }}" readonly>
                                <input type="text" class="form-control" id="nama" name="name"
                                    placeholder="Inputkan judul pengumuman..." value="{{ old('name', $pengumuman['name']) }}"
                                    required>
                                @error('name')
                                    <div class="text-danger small">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            {{-- Konten Pengumuman --}}
                            <div class="mb-3">
                                <label for="nama" class="form-label">Konten <span
                                        class="small text-info">(Opsional)</span></label>
                                <textarea id="tinymce" name="content">
                                    {{ $pengumuman['content'] }}
                                </textarea>
                                @error('content')
                                    <div class="text-danger small">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            {{-- Tombol Submit --}}
                            <div class="">
                                <button type="submit" id="btnSimpan" class="btn-lg btn btn-primary w-100">Simpan dan
                                    Lanjutkan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>



    {{-- Script yang dibutuhkan --}}
<script src="https://cdn.tiny.cloud/1/1dcn6y89gj7jtaawstjd7qt5nddl47py62pg67ihnxq6vyoa/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>;
    <script src="{{ url('/asset/js/rich-text-editor.js') }}"></script>
    <script>

        // Fungsi untuk memeriksa apakah konten TinyMCE tidak kosong
        function validateTinyMCE() {
            var content = tinymce.get("tinymce").getContent();
            if (!content.trim()) {
                alert("Konten tidak boleh kosong.");
                return false; // Membatalkan pengiriman formulir jika konten kosong
            }
            return true; // Lanjutkan pengiriman formulir jika konten tidak kosong
        }

        $("form").on("submit", function(e) {
            e.preventDefault(); // Mencegah form melakukan submit default

            // Memeriksa validasi TinyMCE
            if (!validateTinyMCE()) {
                return false; // Membatalkan pengiriman formulir jika konten kosong
            }

            // Mengambil data form
            var formData = new FormData(this);

            // Menggunakan AJAX untuk mengirim data ke server
            $.ajax({
                type: 'POST',
                url: $(this).attr('action'),
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // Berhasil, lakukan pengalihan (redirect)
                    window.location.href = "{{ route('redirectBack', ['kelasId' => $kelasId, 'mapelId' => $mapel['id'], 'message' => 'Tambah']) }}";
                },
                error: function(error) {
                    // Terjadi kesalahan, tangani kesalahan jika diperlukan
                    console.log(error);
                    // Di sini Anda dapat menambahkan logika lain atau menampilkan pesan kesalahan kepada pengguna.
                }
            });
        });
    </script>
@endsection
