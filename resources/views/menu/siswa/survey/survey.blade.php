@extends('layout.template.mainTemplate')

@section('container')
<div class="container">
    <h1>Survey Guru</h1>
    <form action="{{ route('submitSurveyMurid') }}" method="POST">
        @csrf
        @foreach($questions as $question)
        <div class="mb-3">
            <label class="form-label">{{ $question->question }}</label>
            <div>
                <label><input type="radio" name="responses[{{ $question->id }}]" value="Tidak Pernah"> Tidak Pernah</label>
                <label><input type="radio" name="responses[{{ $question->id }}]" value="Kadang-kadang"> Kadang-kadang</label>
                <label><input type="radio" name="responses[{{ $question->id }}]" value="Sering"> Sering</label>
            </div>
        </div>
        @endforeach
        <button type="submit" class="btn btn-primary">Kirim Survey</button>
    </form>
</div>
@endsection
