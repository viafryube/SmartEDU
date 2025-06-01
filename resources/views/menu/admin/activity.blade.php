@extends('layout.template.mainTemplate')

@section('container')
    {{-- Judul Halaman --}}
    <div class="ps-4 pe-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="display-6 fw-bold">
                <a href="{{ route('dashboard') }}">
                    <button type="button" class="btn btn-outline-dark rounded-circle">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                </a> Semua Aktivitas
            </h1>
        </div>
    </div>

    {{-- Section Pengumuman --}}
    <div class="row ps-4 pe-4 mb-4" id="pengumuman">
        <div class="col-lg-12 col-md-12">
            <h3 class="fw-bold text-primary"><i class="fa-solid fa-bullhorn"></i> Pengumuman</h3>
            <div class="p-4 bg-white rounded-3">
                <div class="table-responsive col-12">
                    @if (count($pengumuman) > 0)
                        <table id="table" class="table table-striped table-hover table-lg p-3">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nama Pengumuman</th>
                                    <th scope="col">Pembuat</th>
                                    <th scope="col">Tanggal</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pengumuman as $key)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $key->name }}</td>
                                        @if (isset($editors[$key->kelas_mapel_id]) && $editors[$key->kelas_mapel_id] !== null)
                                            <td> {{ $editors[$key->kelas_mapel_id]['name'] }}</td>
                                        @else
                                            <td>Editor: Tidak ada</td>
                                        @endif
                                        <td>{{ $key->created_at->format('d F Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('viewPengumuman', ['token' => encrypt($key->id), 'kelasMapelId' => encrypt($key['kelas_mapel_id']), 'mapelId' => $key->mapel_id]) }}"
                                                class="badge bg-info p-2 mb-1 animate-btn-small">
                                                <i class="fa-regular fa-eye fa-xl"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center">
                            <img src="{{ url('/asset/img/not-found.png') }}" alt="" class="img-fluid w-50"
                                style="filter: saturate(0);" srcset="">
                            <br>
                            <Strong>Belum ada Pengumuman</Strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Section Materi --}}
    <div class="row ps-4 pe-4 mb-4" id="materi">
        <div class="col-lg-12 col-md-12">
            <h3 class="fw-bold text-primary"><i class="fa-solid fa-book"></i> Materi</h3>
            <div class="p-4 bg-white rounded-3">
                <div class="table-responsive col-12">
                    @if (count($materi) > 0)
                        <table id="table" class="table table-striped table-hover table-lg p-3">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nama Materi</th>
                                    <th scope="col">Pembuat</th>
                                    <th scope="col">Tanggal</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($materi as $key)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $key->name }}</td>
                                        @if (isset($editors[$key->kelas_mapel_id]) && $editors[$key->kelas_mapel_id] !== null)
                                            <td> {{ $editors[$key->kelas_mapel_id]['name'] }}</td>
                                        @else
                                            <td>Editor: Tidak ada</td>
                                        @endif
                                        <td>{{ $key->created_at->format('d F Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('viewMateri', ['token' => encrypt($key->id), 'kelasMapelId' => encrypt($key['kelas_mapel_id']), 'mapelId' => $key->mapel_id]) }}"
                                                class="badge bg-info p-2 mb-1 animate-btn-small">
                                                <i class="fa-regular fa-eye fa-xl"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center">
                            <img src="{{ url('/asset/img/not-found.png') }}" alt="" class="img-fluid w-50"
                                style="filter: saturate(0);" srcset="">
                            <br>
                            <Strong>Belum ada Materi</Strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Section Diskusi --}}
    <div class="row ps-4 pe-4 mb-4" id="diskusi">
        <div class="col-lg-12 col-md-12">
            <h3 class="fw-bold text-primary"><i class="fa-solid fa-comments"></i> Diskusi</h3>
            <div class="p-4 bg-white rounded-3">
                <div class="table-responsive col-12">
                    @if (count($diskusi) > 0)
                        <table id="table" class="table table-striped table-hover table-lg p-3">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nama Diskusi</th>
                                    <th scope="col">Pembuat</th>
                                    <th scope="col">Tanggal</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($diskusi as $key)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $key->name }}</td>
                                        @if (isset($editors[$key->kelas_mapel_id]) && $editors[$key->kelas_mapel_id] !== null)
                                            <td> {{ $editors[$key->kelas_mapel_id]['name'] }}</td>
                                        @else
                                            <td>Editor: Tidak ada</td>
                                        @endif
                                        <td>{{ $key->created_at->format('d F Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('viewDiskusi', ['token' => encrypt($key->id), 'kelasMapelId' => encrypt($key['kelas_mapel_id']), 'mapelId' => $key->mapel_id]) }}"
                                                class="badge bg-info p-2 mb-1 animate-btn-small">
                                                <i class="fa-regular fa-eye fa-xl"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center">
                            <img src="{{ url('/asset/img/not-found.png') }}" alt="" class="img-fluid w-50"
                                style="filter: saturate(0);" srcset="">
                            <br>
                            <Strong>Belum ada Diskusi</Strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Section Rekomendasi --}}
    <div class="row ps-4 pe-4 mb-4" id="rekomendasi">
        <div class="col-lg-12 col-md-12">
            <h3 class="fw-bold text-primary"><i class="fa-solid fa-bookmark"></i> Rekomendasi</h3>
            <div class="p-4 bg-white rounded-3">
                <div class="table-responsive col-12">
                    @if (count($rekomendasi) > 0)
                        <table id="table" class="table table-striped table-hover table-lg p-3">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nama Rekomendasi</th>
                                    <th scope="col">Pembuat</th>
                                    <th scope="col">Tanggal</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rekomendasi as $key)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $key->name }}</td>
                                        @if (isset($editors[$key->kelas_mapel_id]) && $editors[$key->kelas_mapel_id] !== null)
                                            <td> {{ $editors[$key->kelas_mapel_id]['name'] }}</td>
                                        @else
                                            <td>Editor: Tidak ada</td>
                                        @endif
                                        <td>{{ $key->created_at->format('d F Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('viewRekomendasi', ['token' => encrypt($key->id), 'kelasMapelId' => encrypt($key['kelas_mapel_id']), 'mapelId' => $key->mapel_id]) }}"
                                                class="badge bg-info p-2 mb-1 animate-btn-small">
                                                <i class="fa-regular fa-eye fa-xl"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center">
                            <img src="{{ url('/asset/img/not-found.png') }}" alt="" class="img-fluid w-50"
                                style="filter: saturate(0);" srcset="">
                            <br>
                            <Strong>Belum ada Rekomendasi</Strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Section Tugas --}}
    <div class="row ps-4 pe-4 mb-4" id="tugas">
        <div class="col-lg-12 col-md-12">
            <h3 class="fw-bold text-primary"><i class="fa-solid fa-pen"></i> Tugas</h3>
            <div class="p-4 bg-white rounded-3">
                <div class="table-responsive col-12">
                    @if (count($tugas) > 0)
                        <table id="table" class="table table-striped table-hover table-lg p-3">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nama Tugas</th>
                                    <th scope="col">Due Date</th>
                                    <th scope="col">Pembuat</th>
                                    <th scope="col">Tanggal</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tugas as $key)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $key->name }}</td>
                                        <td>
                                            @php
                                                $dueDate = \Carbon\Carbon::parse($key->due);
                                                $now = \Carbon\Carbon::now();
                                                $daysUntilDue = $dueDate->diffInDays($now);
                                            @endphp
                                            @if ($dueDate->isPast())
                                                <span class="badge badge-secondary">Selesai</span>
                                            @else
                                                @if ($daysUntilDue == 0)
                                                    <span class="badge badge-primary">Mendekati Deadline</span>
                                                @else
                                                    <span class="badge badge-primary">{{ $daysUntilDue }} hari lagi</span>
                                                @endif
                                            @endif
                                        </td>
                                        @if (isset($editors[$key->kelas_mapel_id]) && $editors[$key->kelas_mapel_id] !== null)
                                            <td> {{ $editors[$key->kelas_mapel_id]['name'] }}</td>
                                        @else
                                            <td>Editor: Tidak ada</td>
                                        @endif
                                        <td>{{ $key->created_at->format('d F Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('viewTugasAdmin', ['token' => encrypt($key->id), 'kelasMapelId' => encrypt($key['kelas_mapel_id']), 'mapelId' => $key->mapel_id]) }}"
                                                class="badge bg-info p-2 mb-1 animate-btn-small">
                                                <i class="fa-regular fa-eye fa-xl"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center">
                            <img src="{{ url('/asset/img/not-found.png') }}" alt="" class="img-fluid w-50"
                                style="filter: saturate(0);" srcset="">
                            <br>
                            <Strong>Belum ada Tugas</Strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>


    {{-- Section Ujian --}}
    <div class="mb-4 ps-4 pe-4" id="ujian">
        <h3 class="text-primary fw-bold"><i class="fa-solid fa-newspaper"></i> Ujian
        </h3>
        <div class="p-4 bg-white rounded-3">
            <div class="table-responsive col-12">
                @if (count($ujian) > 0)
                    <table id="table" class="table table-hover table-striped table-lg">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nama Ujian</th>
                                <th scope="col">Pembuat</th>
                                <th scope="col">Time</th>
                                <th scope="col">Tipe Soal</th>
                                <th scope="col">Jumlah Soal</th>
                                <th scope="col">Due Date</th>
                                @if (Auth()->User()->roles_id == 1)
                                    <th scope="col">Tanggal</th>
                                @endif
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ujian as $key)
                                @if ($key->isHidden != 1 || Auth()->User()->roles_id == 1)
                                    <tr class="@if ($key->isHidden == 1) opacity-50 @endif">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $key->name }}
                                            @if ($key->isHidden == 1)
                                                <i class="fa-solid fa-eye-slash fa-bounce text-danger"></i>
                                            @endif
                                        </td>
                                        @if (isset($editors[$key->kelas_mapel_id]) && $editors[$key->kelas_mapel_id] !== null)
                                            <td> {{ $editors[$key->kelas_mapel_id]['name'] }}</td>
                                        @else
                                            <td>Editor: Tidak ada</td>
                                        @endif
                                        <td>{{ $key->time }} Menit</td>
                                        @if ($key->tipe == 'multiple')
                                            <td><span class="badge p-2 badge-dark">Pilihan Ganda</span></td>
                                            <td>{{ count($key->SoalUjianMultiple) }}</td>
                                        @else
                                            <td><span class="badge p-2 badge-dark">Essay</span></td>
                                            <td>{{ count($key->SoalUjianEssay) }}</td>
                                        @endif
                                        <td>
                                            @php
                                                $dueDate = \Carbon\Carbon::parse($key->due);
                                                $now = \Carbon\Carbon::now();
                                                $daysUntilDue = $dueDate->diffInDays($now);
                                            @endphp
                                            @if ($dueDate->isPast())
                                                <span class="badge badge-secondary">Selesai</span>
                                            @else
                                                @if ($daysUntilDue == 0)
                                                    <span class="badge badge-primary">Mendekati Deadline</span>
                                                @else
                                                    <span class="badge badge-primary">{{ $daysUntilDue }} hari lagi</span>
                                                @endif
                                            @endif
                                        </td>
                                        @if (Auth()->User()->roles_id == 1)
                                            <td>{{ $key->created_at->format('d F Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('viewUjian', ['token' => encrypt($key->id), 'kelasMapelId' => encrypt($key['kelas_mapel_id']), 'mapelId' => $key->mapel_id]) }}"
                                                    class="badge badge-info p-2 mb-1 animate-btn-small">
                                                    <i class="fa-regular fa-eye fa-xl"></i>
                                                </a>
                                            </td>
                                        @endif
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center">
                        <img src="{{ url('/asset/img/not-found.png') }}" alt="" class="img-fluid w-25"
                            style="filter: saturate(0);" srcset="">
                        <br>
                        <Strong>Belum ada Ujian</Strong>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
