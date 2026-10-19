@extends('layouts.admin')
@section('page_heading')
    {{ $roleName ?? "Users" }}
@endsection

@can('candidates.create')
@section('top_buttion')
      <button type="button" class="btn addButton" data-bs-toggle="modal" data-bs-target="#createModal">
        Add {{$roleName}}
    </button>
@endsection
@endcan
@section('content')

    <div class="col-12">
        <div class="card borderRadius">
            <div class="card-body p-0 px-3">
                <table class="table table-lg tableStyle mb-0 dataTable align-middle" id="dataTable" width="100%">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 50px;">S.N</th>
                            <th scope="col">Name</th>
                            <th>Skills</th>
                            <th>Job Title</th>
                            <th>Current Company</th>
                            <th scope="col" >Phone</th>
                            <th>Experienc</th>
                            <th>Position</th>
                            <th scope="col" style="text-align: right;">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

<x-livewire-modal id="createModal" title="Create Candidate" modal_size="xxl">
    <livewire:user.create usertype="Candidates" />
</x-livewire-modal>

<x-livewire-modal id="editModal" title="Edit Candidate" modal_size="xxl">
    <livewire:user.edit  usertype="Candidates" />
</x-livewire-modal>
@endsection

@push('js')
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
 <!-- DataTables CSS -->

<script type="text/javascript">
    $(document).ready(function() {
        let url = "/admin/candidates";
        initializeDataTable('#dataTable', url, ['id','name','skills.name','employments.job_title','employments.company_name','mobile_number','experience_type','position','action'], [], true);
    });
</script>
@endpush
