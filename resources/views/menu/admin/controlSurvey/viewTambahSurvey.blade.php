@extends('layout.template.mainTemplate')

@section('container')
    @if (Auth()->user()->roles_id == 1)
        @include('menu.admin.adminHelper')
    @endif

    <div class="col-12 ps-4 pe-4 mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-white">
                <li class="breadcrumb-item"><a href="{{ route('viewSurvey') }}">Data Survey</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah Survey</li>
            </ol>
        </nav>
    </div>

    <div class="ps-4 pe-4 mt-4  pt-4">
        <h2 class="display-6 fw-bold"><a href="{{ route('viewSurvey') }}"><button type="button"
                    class="btn btn-outline-secondary rounded-circle">
                    <i class="fa-solid fa-arrow-left"></i></button></a> Tambah Survey</h2>

        <nav style="" aria-label="breadcrumb">
            <ol class="breadcrumb bg-light">
                <li class="breadcrumb-item text-info" aria-current="page">Step 1</li>
                <li class="breadcrumb-item ">Step 2</li>
            </ol>
        </nav>
    </div>

    <div class="">
        <div class="row p-4  ">
            <h4 class="fw-bold text-primary"><i class="fa-solid fa-pen"></i> Data Survey</h4>
            <div class="col-12 col-lg-12 bg-white rounded-2">
                <div class="mt-4">
                    <div class=" p-4">
                        <form action="{{ route('tambahSurvey') }}" method="POST">
                            @csrf
                            {{-- Nama Guru --}}
                            <div class="mb-3">
                                <label for="namaGuru" class="form-label">Nama Guru</label>
                                <select class="form-select" name="user_id" aria-label="Default select example" required>
                                    <option value="" disabled selected>Pilih Guru</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                                </select>
                                @error('guru')
                                    <div class="text-danger small">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            {{-- Kelas --}}
                            <div class="mb-3">
                                <label for="kelas" class="form-label">Kelas</label>
                                <select class="form-select" name="kelas_id" aria-label="Default select example" required>
                                    <option value="" disabled selected>Pilih Kelas</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                                @error('kelas')
                                    <div class="text-danger small">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="">
                                <button type="submit" class="btn-lg btn btn-primary w-100">Simpan dan Lanjutkan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ url('/asset/js/lottie.js') }}"></script>
    <script src="{{ url('/asset/js/customJS/simpleAnim.js') }}"></script>
@endsection
