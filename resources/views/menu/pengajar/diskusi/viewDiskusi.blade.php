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
                @if (Auth()->user()->roles_id == 1)
                @else
                    <li class="breadcrumb-item">
                        <a
                            href="{{ route('viewKelasMapel', ['mapel' => $mapel['id'], 'token' => encrypt($kelas['id']), 'mapel_id' => $mapel['id']]) }}">
                            {{ $mapel['name'] }}
                        </a>
                    </li>
                @endif
                <li class="breadcrumb-item active" aria-current="page"> Diskusi</li>
            </ol>
        </nav>
    </div>

    {{-- Judul Halaman --}}
    <div class="ps-4 pe-4 mt-4  pt-4">
        <h2 class="display-6 fw-bold">
            @if (Auth()->user()->roles_id == 1)
                <a href="{{ route('activity') }}">
                    <button type="button" class="btn btn-outline-secondary rounded-circle">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                </a> Diskusi
            @else
                <a
                    href="{{ route('viewKelasMapel', ['mapel' => $mapel['id'], 'token' => encrypt($kelas['id']), 'mapel_id' => $mapel['id']]) }}">
                    <button type="button" class="btn btn-outline-secondary rounded-circle">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                </a> Diskusi
            @endif
        </h2>
    </div>

    {{-- Baris utama --}}
    <div class="col-12 ps-4 pe-4 mb-4">
        <div class="row">
            {{-- Bagian Kiri --}}
            <div class="col-xl-9 col-lg-12 col-md-12">
                <div class="row">
                    {{-- Tampilan Diskusi --}}
                    <div class="col-12 mb-4">
                        <div class="p-4 bg-white rounded-4">
                            <div class="h-100 p-4">
                                <h2 class="fw-bold text-primary">
                                    {{ $diskusi->name }}
                                    @if ($diskusi->isHidden == 1)
                                        <i class="fa-solid fa-lock fa-bounce text-danger"></i>
                                    @endif
                                </h2>
                                <hr>
                                <p>
                                    {!! $diskusi->content !!}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="container">
                    <div class="row clearfix">
                        <div class="col-lg-12">
                            <div class="card chat-app">
                                <div class="chat">
                                    <livewire:diskusi :diskusiId="$diskusi->id" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>

            {{-- Bagian Kanan --}}
            <div class="col-xl-3 col-lg-12 col-md-12">
                {{-- Info Pengajar --}}
                <div class="mb-4 p-4 bg-white rounded-4">
                    <div class="h-100 p-4">
                        <h4 class="fw-bold mb-2">Pengajar</h4>
                        <hr>
                        <div class="row">
                            <div class="col-lg-4 d-none d-lg-none d-xl-block">
                                @if ($editor->gambar == null)
                                    <img src="/asset/icons/profile-women.svg" class="rounded-circle  img-fluid"
                                        alt="">
                                @else
                                    <img src="{{ asset('storage/file/img-upload/' . $editor->gambar) }}" alt="placeholder"
                                        class="rounded-circle  img-fluid">
                                @endif
                            </div>
                            <div class="col-lg-8">
                                <a href="{{ route('viewProfilePengajar', ['token' => encrypt($editor['id'])]) }}">
                                    {{ $editor['name'] }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>


              
            </div>
        </div>
    </div>

    {{-- Script untuk mengatur gambar agar responsif --}}
    <script>
        var img = document.querySelectorAll('img');

        img.forEach(function(element) {
            element.classList.add('img-fluid');
        });
    </script>

    {{-- Script tambahan jika diperlukan --}}
    <script src="{{ url('/asset/js/lottie.js') }}"></script>
    <script src="{{ url('/asset/js/customJS/simpleAnim.js') }}"></script>
    <script></script>
@endsection
