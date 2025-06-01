@extends('layout.template.mainTemplate')

@section('container')
    @if (Auth()->user()->roles_id == 1)
        @include('menu.admin.adminHelper')
    @endif

    <div class="col-12 ps-4 pe-4 mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-white">
                <li class="breadcrumb-item"><a href="{{ route('viewSurvey') }}">Data Survey</a></li>
                <li class="breadcrumb-item active" aria-current="page">List Responden Survey</li>
            </ol>
        </nav>
    </div>

    <div class="ps-4 pe-4 mt-4 pt-4">
        <h2 class="display-6 fw-bold">
            <a href="{{ route('viewSurvey') }}">
                <button type="button" class="btn btn-outline-secondary rounded-circle">
                    <i class="fa-solid fa-arrow-left"></i>
                </button>
            </a> 
            List Responden Survey
        </h2>
    </div>

    <div class="row p-4">
        <div class="col-12 col-lg-12 bg-white rounded-2">
            <div class="mt-4 p-4">
                <h4 class="fw-bold text-primary"><i class="fa-solid fa-list"></i> Daftar Siswa yang Mengisi Survey</h4>

                @if ($responses->count() > 0)
                    <div class="table-responsive col-12">
                        <table id="table" class="table table-striped table-lg">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nama Siswa</th>
                                    <th scope="col">Kelas</th>
                                    <th scope="col">Tanggal Pengisian</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($responses as $userId => $userResponses)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $userResponses->first()->user->name }}</td>
                                        <td>{{ $survey->kelas->name }}</td>
                                        <td>{{ $userResponses->first()->created_at->format('d-m-Y H:i:s') }}</td>
                                        <td>
                                            <a href="{{ route('viewDetailSurvey', ['survey' => $survey->id, 'user' => $userId]) }}" class="badge bg-info p-2 mb-1 animate-btn-small"><i class="fa-regular fa-eye fa-xl mb-1"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center">
                        <img src="{{ url('/asset/img/not-found.png') }}" alt="" class="img-fluid w-50 mb-2">
                        <br>
                        <strong>Belum ada siswa yang mengisi survey.</strong>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
