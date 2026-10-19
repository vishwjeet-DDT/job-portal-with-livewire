@props(['col' => 3])
<div class="card p-3">

    <!-- GLOBAL SEARCH -->
    <div class="mb-1 row justify-content-end">
        <div class="col-md-{{ $col ?? 3 }}">
            <input type="text" wire:model.live.debounce.400ms="search" class="form-control border rounded px-3"
                placeholder="Search job titles, categories, cities..." style="background:#f4f7fa;">
        </div>
    </div>

    <!-- JOB LIST -->
    <div class="space-y-4">

        @forelse ($jobs as $job)
            <div class="card mb-2">
                <div class="card-body">
                    <a href="{{ route('jobs.show',$job->slug) }}" class="text-decoration-none text-black">
                        <div class="d-flex justify-content-between">
                            <div class="details_container">
                                <h3 class="sub_headings mt-0">{{ $job->job_title }}</h3>
                                @if (!$job->hide_company && $job->company)
                                    <div class="d-flex align-content-center flex-wrap gap-2">
                                        <span class="company_name">{{ $job->company->company_name }}</span>
                                    </div>
                                @endif
                                <div class="experience_locations d-flex mt-2">
                                    <span class="exp_ sets d-flex gap-2">
                                        <i class="ri-shopping-bag-line"></i>
                                        <span>{{ $job->min_experience }}
                                            {{ Str::plural('yr', $job->min_experience) }}</span>
                                    </span>
                                    <span
                                        class="sets">₹{{ $job->hide_salary ? 'Not Disclosed' : $job->min_salary . ' - ' . $job->max_salary }}</span>
                                    <span class="d-flex gap-2 sets">
                                        <i class="ri-map-pin-2-line"></i>
                                        {{ $job->cities->pluck('name')->join(', ') ?: 'Not specified' }}
                                    </span>
                                </div>
                                <div class="degree mt-2">
                                    <p class="text-capitalize text-muted mb-0">
                                        <i class="ri-book-2-line"></i>
                                        {{ Str::limit(strip_tags($job->description), 75) }}
                                    </p>
                                    <p class="text-capitalize text-muted">
                                        {{ $job?->job_category?->name }}
                                    </p>
                                </div>
                                <p class="online_day mb-0">
                                    {{ $job->created_at->diffForHumans(['short' => true]) }}
                                </p>
                            </div>
                            @if (!$job->hide_company)
                                <span>
                                    <img class="campany_logo"
                                        src="{{ asset('storage/' . $job->company->logo ?? 'assets/frontend/logoipsums.png') }}">
                                </span>
                            @endif
                        </div>
                    </a>
                </div>
            </div>
        @empty

            <div class="text-center py-10 text-gray-500">
                No jobs found.
            </div>

        @endforelse

    </div>
    <div class="mt-4">

        {{ $jobs->links() }}
    </div>
</div>
