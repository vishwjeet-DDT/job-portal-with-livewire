@extends('layouts.frontend.app')

@section('content')
    <div class="container">
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div class="details_container">
                                <h3 class="sub_headings mt-0">{{ $job->job_title }}</h3>
                                @if (!$job->hide_company)
                                    <div class="d-flex align-content-center flex-wrap gap-2">
                                        <span class="company_nams">{{ $job->company->company_name }}</span>
                                    </div>
                                @endif
                                <div class="experience_locations d-flex mt-2">

                                    <span class="exp_ sets d-flex gap-2">
                                        <i class="ri-shopping-bag-line"></i>
                                        <span>{{ (int)$job->experience_level }}
                                            {{ Str::plural('yr', (int)$job->experience_level) }}
                                        </span>
                                    </span>
                                    <span class="sets">₹
                                        {{ $job->hide_salary ? 'Not Disclosed' : $job->min_salary . ' - ' . $job->max_salary }}
                                    </span>
                                    <span class="d-flex gap-2 sets">
                                        <i class="ri-map-pin-2-line"></i>

                                        <span>
                                            {{ $job->cities->pluck('name')->join(', ') ?: 'Not specified' }}

                                        </span>
                                    </span>

                                </div>

                                <div class="degree mt-2">
                                    <p class="text-capitalize text-muted mb-0">
                                        <i class="ri-book-2-line"></i>
                                        {{ str::limit(strip_tags($job->description), 75)}}
                                    </p>
                                    <p class="text-capitalize text-muted test">{{ $job?->job_category?->name }}
                                    </p>
                                </div>
                            </div>
                            @if (!$job->hide_company)
                                <span>
                                    <img class="campany_logo"
                                        src="{{ asset('storage/'.$job->company->logo ?? 'assets/frontend/logoipsums.png')}}">
                                </span>
                            @endif
                        </div>
                        <hr>
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <p class="text-muted m-0 mb-2 ">
                                Posted: <span class="text-dark openingTime">
                                    {{ $job->created_at->diffForHumans() }}</span>
                                Openings: <span class="text-dark openingTime">{{$job->number_of_vacancy}}</span>
                                Applicants: <span
                                    class="text-dark openingTime">{{ $job->applications ? $job->applications->count() : 0 }}</span>
                            </p>
                            <div class="d-flex justify-content-end gap-2 apply_post">

                               <livewire:job.apply :jobId="$job->id" />

                            </div>
                        </div>
                    </div>
                </div>

                <!-- job discreptions start-->
                <div class="card mt-3">
                    <div class="card-body">

                        <div class="job_descriptions mt-4">
                            <h3 class="sub_headings">Job description</h3>

                            <div class="fs14 text-muted text-justify">
                                {!! nl2br(e(html_entity_decode(strip_tags($job->description)))) !!}

                            </div>

                            <div class="key_skill mt-3">
                                <h4 class="sub2_heading">Key Skills</h4>
                                <ul class="keySkillstype list-unstyled d-flex flex-wrap m-0 ">
                                    @php
                                        $skills = $job->skills;
                                    @endphp

                                    @forelse($skills as $skill)
                                        <li class="me-2">{{ $skill->name }}</li>
                                    @empty
                                        <li>No skills listed</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- job discreptions end-->

                <!--- about company start-->
                <div class="card mt-3">
                    <div class="card-body">
                        <h3 class="sub_headings">About company</h3>
                        <p class="fs14 text-muted">
                            {{ strip_tags($job->company->description) }}
                        </p>
                        <div class="companyinfo mt-2">
                            <h3 class="sub_headings">Company Info</h3>
                            <div>
                                <span class="textdark">Link: </span>
                                <a href="{{ $job->company->website }}" class="text-decoration-none">website</a>
                            </div>

                            <div>
                                <span class="textdark">Address: </span>
                                <span class="text-muted fs14">{{ $job->company->location }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!--- about company end-->
            </div>
            <!--- similar job start-->
            <div class="col-md-4">
                <div class="card stickytop" style="top: 0;">
                    <div class="card-body">
                        @foreach($recommendedJobs as $job)
                            <div class="related_job">
                                <a href="{{ route('jobs.show', $job->slug) }}" class="text-dark text-decoration-none">
                                    <div class="d-flex justify-content-between">
                                        <div class="details_container">
                                            <h3 class="sub_headings2 ">
                                                {{ $job->job_title }}
                                            </h3>
                                            <div class="d-flex align-content-center flex-wrap gap-2">
                                                <span class="company_nams">{{ $job->company->company_name ?? 'N/A' }}

                                                </span>

                                            </div>

                                            <div class="experience_locations2 d-flex gap-3 flex-wrap">

                                                <div class="exp_ sets d-flex gap-2 flex-wrap mt-2">
                                                    <i class="ri-shopping-bag-line"></i>
                                                    <span>{{(int) $job->experience_level }}
                                                        {{ Str::plural('yr', (int) $job->experience_level) }}
                                                    </span>
                                                </div>

                                                <div class="d-flex gap-2 sets mt-2">
                                                    <i class="ri-map-pin-2-line"></i>
                                                    {{ $job->cities->pluck('name')->join(', ') ?: 'Not specified' }}
                                                </div>
                                            </div>
                                        </div>
                                        <span>
                                            <img class="campany_logo"
                                                src="{{ asset('storage/' . $job->company->logo ?? 'assets/frontend/logoipsums.png') }}">
                                        </span>
                                    </div>
                                </a>
                            </div>
                            @if(!$loop->last)
                                <hr>
                            @endif
                        @endforeach

                    </div>
                </div>

            </div>
            <!--- similar job end-->
        </div>
    </div>
@endsection
