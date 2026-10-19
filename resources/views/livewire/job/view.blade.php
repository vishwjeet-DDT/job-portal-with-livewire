<div>
    @if($job)
    <div class="row">

        <div class="col-md-6 mb-3">
            <strong>Title:</strong> {{ $job->title }}
        </div>

        <div class="col-md-6 mb-3">
            <strong>Employment Type:</strong> {{ \App\Models\Job::EMPLOYEMENT_TYPE[$job->employment_type] ?? '' }}
        </div>

        <div class="col-md-6 mb-3">
            <strong>Work Mode:</strong> {{ \App\Models\Job::WORK_MODE[$job->work_mode] ?? '' }}
        </div>

        <div class="col-md-6 mb-3">
            <strong>Gender:</strong> {{ ucfirst($job->gender) }}
        </div>

        <div class="col-md-6 mb-3">
            <strong>Languages:</strong>
            {{ $job->getLanguages()->pluck('name')->join(', ') }}
        </div>

        <div class="col-md-6 mb-3">
            <strong>Country:</strong> {{ optional($job->country)->name }}
        </div>

        <div class="col-md-6 mb-3">
            <strong>State:</strong> {{ optional($job->state)->name }}
        </div>

        <div class="col-md-6 mb-3">
            <strong>City:</strong>
            {{ $job->cities->pluck('name')->join(', ') }}
        </div>

        <div class="col-md-6 mb-3">
            <strong>Shift:</strong> {{ \App\Models\Job::SHIFT_TYPES[$job->shift] ?? '' }}
        </div>

         <div class="col-md-6 mb-3">
            <strong>Salary:</strong>
            {{ $job->min_salary }} - {{ $job->max_salary }} {{ $job->currency }}
        </div>

        <div class="col-md-12 mb-3">
            <strong>Description:</strong>
            <p>{!! nl2br($job->description) !!}</p>
        </div>



        <div class="col-md-6 mb-3">
            <strong>Experience Level:</strong> {{ $job->experience_level }}
        </div>

        <div class="col-md-6 mb-3">
            <strong>Qualification:</strong> {{ $job->qualification }}
        </div>

        <div class="col-md-6 mb-3">
            <strong>Vacancies:</strong> {{ $job->number_of_vacancy }}
        </div>

        <div class="col-md-6 mb-3">
            <strong>Deadline:</strong> {{ $job->deadline ? \Carbon\Carbon::parse($job->deadline)->format('d M Y') : '' }}
        </div>

        <div class="col-md-6 mb-3">
            <strong>Status:</strong> {{ \App\Models\Job::STATUSES[$job->status] ?? '' }}
        </div>

    </div>
    @endif
</div>
