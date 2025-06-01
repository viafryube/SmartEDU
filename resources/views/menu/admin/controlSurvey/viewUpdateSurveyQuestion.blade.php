@extends('layout.template.mainTemplate')

@section('container')
    @if (Auth()->user()->roles_id == 1)
        @include('menu.admin.adminHelper')
    @endif

    <div class="col-12 ps-4 pe-4 mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-white">
                <li class="breadcrumb-item"><a href="{{ route('viewSurveyQuestions') }}">Pertanyaan Survey</a></li>
                <li class="breadcrumb-item active" aria-current="page">Update Pertanyaan</li>
            </ol>
        </nav>
    </div>

    <div class="ps-4 pe-4 mt-4 pt-4">
        <h2 class="display-6 fw-bold"><a href="{{ route('viewSurveyQuestions') }}"><button type="button"
                    class="btn btn-outline-secondary rounded-circle">
                    <i class="fa-solid fa-arrow-left"></i></button></a> Update Pertanyaan</h2>
    </div>

    <div class="">
        <div class="row p-4">
            <div class="col-12 col-lg-12 bg-white rounded-2">
                <div class="mt-4">
                    <div class="p-4">
                        <form action="{{ route('updateSurveyQuestion') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{ $question->id }}">
                            <div class="mb-3">
                                <label for="question" class="form-label">Pertanyaan</label>
                                <input type="text" class="form-control" id="question" name="question" value="{{ $question->question }}" required>
                                @error('question')
                                    <div class="text-danger small">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="">
                                <button type="submit" class="btn-lg btn btn-primary w-100">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
