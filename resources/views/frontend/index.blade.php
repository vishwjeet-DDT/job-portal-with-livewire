@extends('layouts.frontend.app')

@section('content')
    <div class="container">
        <div class="row mt-4">
            @if (auth()->user())

            @php
              $user = auth()->user();
            @endphp
                <div class="col-lg-3 home_profile">
                    <div class="card sticky-top" style="top: 80px;">
                        <div class="card-body">

                            <div class="profile_image d-flex justify-content-center">
                                <img src="/assets/default_user.webp" class="candidate_img" alt="user img">
                            </div>

                            <h3 class="text-center candidateName pt-3 pb-1 mb-0">{{$user->name}}</h3>
                            <p class="text-center designations m-0 p-0">No employment record</p>
                            <p class="text-uppercase text-center pt-2 company mb-1"> No company record</p>

                            <p class="online_day text-center text-black">Last update :
                                 {{ $user->updated_at->diffForHumans() }}
                            </p>
                            <div class="d-flex justify-content-center ">
                                <div class="d-flex justify-content-center">
                                    <a class="view_profile" href="{{ route('profile.edit') }}">View
                                        profile</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="col-lg-{{ auth()->user() ? 6 : 9 }}">
                <livewire:job.job-list  col="6"/>
            </div>
            <div class="col-lg-3 sticky-top" style="top: 0;">

                <div class="card sticky-top" style="top: 80px;">
                    <img src="https://media.istockphoto.com/id/1413735503/photo/social-media-social-media-marketing-thailand-social-media-engagement-post-structure.jpg?s=612x612&amp;w=0&amp;k=20&amp;c=7Y4Bdom9c7paYa67nSCvwSuFoppYxJIh-CTYqe6J4Js="
                        class="card-img-top" alt="...">
                    <div class="card-body">
                        <p class="company">Some quick example text to build on the card title and make up the bulk of
                            the card’s content.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
