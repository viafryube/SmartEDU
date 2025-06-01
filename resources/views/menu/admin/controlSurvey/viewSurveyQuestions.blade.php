@extends('layout.template.mainTemplate')

@section('container')
    @if (Auth()->user()->roles_id == 1)
        @include('menu.admin.adminHelper')
    @endif

    <div class="col-12 ps-4 pe-4 mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-white">
                <li class="breadcrumb-item active" aria-current="page">Data Survey / Pertanyaan Survey</li>
            </ol>
        </nav>
    </div>

    <div class="ps-4 pe-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="display-6 fw-bold">Data Pertanyaan Survey
                <a href="{{ route('viewTambahSurveyQuestion') }}">
                    <button class="btn btn-primary animate-btn-small">+ Tambah Pertanyaan</button>
                </a>
            </h1>
        </div>
    </div>

    <div class="">
        <div class="row p-4">
            <div class="col-12 col-lg-12 bg-white rounded-2">
                <div class="mt-4">
                    <div class="p-4">
                        @if (session()->has('delete-success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('delete-success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif
                        @if ($questions->count() > 0)
                            <div id="tableContent">

                                {{-- Loading --}}
                                <div id="loadingIndicator" class="d-none">
                                    <div class="spinner-border" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>

                                Jumlah Pertanyaan: {{ $questions->total() }}
                                <div class="table-responsive col-12">
                                    <table id="table" class="table table-striped table-lg">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Pertanyaan</th>
                                                <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($questions as $key)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $key->question }}</td>
                                                    @if (Auth()->user()->roles_id == 1)
                                                        <td>
                                                            <!-- Tambahkan aksi edit dan hapus di sini jika diperlukan -->
                                                            <a href="{{ route('viewUpdateSurveyQuestion', ['question' => $key->id]) }}"
                                                                class="badge bg-info p-2 mb-1 animate-btn-small"><i
                                                                    class="fa-solid fa-pen-to-square fa-xl mb-1"></i>
                                                            </a>
                                                            <a href="#table"
                                                                class="badge bg-secondary p-2 animate-btn-small">
                                                                <i class="fa-solid fa-xl fa-trash mb-1"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#deleteConfirmationModal"
                                                                    onclick="changeQuestionValue('{{ $key->id }}');"></i>
                                                            </a>
                                                        </td>
                                                    @else
                                                        <td></td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div class="d-flex justify-content-center" id="pagination-container">
                                        {{ $questions->links('pagination::bootstrap-5') }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center">
                                <img src="{{ url('/asset/img/not-found.png') }}" alt="" class="img-fluid w-50 mb-2">
                                <br>
                                <strong>Data belum ditambahkan</strong>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmationModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus pertanyaan ini?
                </div>
                <div class="modal-footer">
                    <form action="{{ route('destroySurveyQuestion') }}" method="post">
                        @csrf
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <input type="hidden" name="idHapus" id="deleteQuestionButton" value="">
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fungsi untuk mengubah nilai tombol hapus
        function changeQuestionValue(questionId) {
            const deleteButton = document.getElementById('deleteQuestionButton');
            deleteButton.setAttribute('value', questionId);
        }
    </script>

    <script src="{{ url('/asset/js/customJS/s-survey.js') }}"></script>
@endsection
