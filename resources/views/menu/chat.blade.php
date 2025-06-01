@extends('layout.template.mainTemplate')

@section('container')
    <style>
        #chat3 .form-control {
            border-color: transparent;
        }

        #chat3 .form-control:focus {
            border-color: transparent;
            box-shadow: inset 0px 0px 0px 1px transparent;
        }

        .badge-dot {
            border-radius: 50%;
            height: 10px;
            width: 10px;
            margin-left: 2.9rem;
            margin-top: -.75rem;
        }
    </style>
    <section>
        <div class="container py-5">
            <div class="row">
                <div class="col-md-12">
                    <div class="card" id="chat3" style="border-radius: 15px;">
                        <div class="card-body">
                            <livewire:chat />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
