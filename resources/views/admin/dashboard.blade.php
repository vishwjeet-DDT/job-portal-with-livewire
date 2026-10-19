@extends('layouts.admin')


@section('content')

    <div class="row">
        <div class="col-md-4 col-sm-6 col-12">
            <div class="card">
                <a href="{{ route('admin.candidates.index') }}">
                    <div class="card-body d-flex  justify-content-between align-items-center">
                        <span class="btn btn-xl btnlights btn-circle d-flex align-items-center justify-content-center">
                            <i class="ri-creative-commons-by-line"></i>
                        </span>
                        <div class="d-flex justify-content-end">
                            <span>
                                <h1 class="mt-3 pt-1 text-end dashboardText"> {{$total['candidate'] ?? 0}} </h1>
                                <h6 class="text-muted mb-0 fw-normal">Total Condidates</h6>
                            </span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 col-12">
            <div class="card">
                <a href="{{ route('admin.employers.index') }}">
                    <div class="card-body d-flex  justify-content-between align-items-center">
                        <span class="btn btn-xl btnlights btn-circle d-flex align-items-center justify-content-center">
                            <i class="ri-user-6-line"></i>
                        </span>
                        <div class="d-flex justify-content-end">
                            <span>
                                <h1 class="mt-3 pt-1 text-end dashboardText"> {{ $total['employer'] ?? 0 }} </h1>
                                <h6 class="text-muted mb-0 fw-normal">Total Active Employers</h6>
                            </span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 col-12">
            <div class="card">
                <a href="{{ route('admin.jobs.index') }}">
                    <div class="card-body d-flex  justify-content-between align-items-center">
                        <span class="btn btn-xl btnlights btn-circle d-flex align-items-center justify-content-center">
                            <i class="ri-briefcase-line"></i>
                        </span>
                        <div class="d-flex justify-content-end">
                            <span>
                                <h1 class="mt-3 pt-1 text-end dashboardText"> {{ $total['jobs'] ?? 0}} </h1>
                                <h6 class="text-muted mb-0 fw-normal">Total Active Jobs</h6>
                            </span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        {{-- <div class="col-md-4 col-sm-6 col-12">
            <div class="card">
                <a href="{{ route('admin.job.approved-list') }}" target="_blank">
                    <div class="card-body d-flex  justify-content-between align-items-center">
                        <span class="btn btn-xl btnlights btn-circle d-flex align-items-center justify-content-center">
                            <i class="ri-group-line"></i>
                        </span>
                        <div class="d-flex justify-content-end">
                            <span>
                                <h1 class="mt-3 pt-1 text-end dashboardText"> {{ $shortListCount ?? 0 }} </h1>
                                <h6 class="text-muted mb-0 fw-normal">Shortlisted Candidates</h6>
                            </span>
                        </div>
                    </div>
                </a>
            </div>
        </div>--}}


    </div>
@endsection
