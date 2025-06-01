@extends('layout.template.mainTemplate')

@section('container')
    @if (Auth()->user()->roles_id == 1)
        @include('menu.admin.adminHelper')
    @endif

    <div class="col-12 ps-4 pe-4 mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-white">
                <li class="breadcrumb-item"><a href="{{ route('viewSurvey') }}">Data Survey</a></li>
                <li class="breadcrumb-item"><a href="{{ route('viewListSurvey', ['survey' => $survey->id]) }}">List Responden Survey</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail Survey</li>
            </ol>
        </nav>
    </div>

    <div class="ps-4 pe-4 mt-4 pt-4">
        <h2 class="display-6 fw-bold">
            <a href="{{ route('viewListSurvey', ['survey' => $survey->id]) }}">
                <button type="button" class="btn btn-outline-secondary rounded-circle">
                    <i class="fa-solid fa-arrow-left"></i>
                </button>
            </a> 
            Detail Survey Siswa: {{ $student->name }}
        </h2>
    </div>

    <div class="row p-4">
        <div class="col-12 col-lg-12 bg-white rounded-2">
            <div class="mt-4 p-4">
                <h4 class="fw-bold text-primary"><i class="fa-solid fa-question-circle"></i> Pertanyaan dan Jawaban</h4>

                @if ($responses->count() > 0)
                    <div class="table-responsive col-12">
                        <table id="table" class="table table-striped table-lg">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Pertanyaan</th>
                                    <th scope="col">Jawaban</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($responses as $response)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $response->question->question }}</td>
                                        <td>{{ $response->response }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center">
                        <img src="{{ url('/asset/img/not-found.png') }}" alt="" class="img-fluid w-50 mb-2">
                        <br>
                        <strong>Belum ada jawaban untuk survey ini.</strong>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
