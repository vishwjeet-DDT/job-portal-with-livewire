@extends('layouts.frontend.app')

@section('content')
@php
$user = auth()->user();
@endphp
    <div class="container">
        <div class="row mt-4">
            <div class="col-md-12 mb-3">
                <livewire:user.profile :user="$user"/>
            </div>

            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-3 mobile_dnone">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="headings">Recommended Jobs for you</h3>
                                <hr>
                                <div>
                                    <ul class="add_detals">

                                        <li>
                                            <span> Resume </span>
                                            <label for="resume-upload" class="text-primary"
                                                style="font-weight: 400; cursor: pointer;">
                                                Upload
                                            </label>
                                        </li>



                                        <li>
                                            <span>Skills </span>
                                            <button class="text-primary border-0 bg-transparent" onclick="clickButtion('openSkillModal')">
                                                Add
                                            </button>
                                        </li>
                                        <li>
                                            <span> Employment </span>
                                            <button class="text-primary border-0 bg-transparent" onclick="clickButtion('openEmModal')">
                                                Add
                                            </button>
                                        </li>
                                        <li>
                                            <span> Education </span>
                                            <button class="text-primary border-0 bg-transparent" onclick="clickButtion('openModal')">
                                                Add
                                            </button>
                                        </li>

                                        <li> <span> Profile summary </span>
                                            <button class="text-primary border-0 bg-transparent" onclick="clickButtion('openPsModal')">
                                                Add
                                            </button>
                                        </li>

                                        <li>
                                            <span> Personal details</span>
                                            <button class="text-primary border-0 bg-transparent" onclick="clickButtion('openPdModal')">
                                                Add
                                            </button>
                                        </li>

                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <livewire:user.resume-upload :user="$user" />
                        <livewire:user.skill :user="$user"/>
                        <livewire:user.education  :user="$user"/>
                        <livewire:user.employment  :user="$user"/>
                        <livewire:user.profile-summary :user="$user"/>
                        @livewire('user.details',['user'=>$user])

                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('js')

        <script>
            function clickButtion(butt) {
                Livewire.dispatch(butt);
            }
        </script>
    @endpush
@endsection
