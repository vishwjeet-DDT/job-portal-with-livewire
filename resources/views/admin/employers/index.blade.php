@extends('layouts.admin')
@section('page_heading', 'Employers List')
@can('employers.create')
@section('top_buttion')
    <a href="{{ route('admin.employers.create') }}" class="btn addButton" >
        Add Employer
    </a>
@endsection
@endcan
@section('content')

<div class="card borderRadius">
    <div class="card-body table-responsive p-0 px-3">
        <table class="table tableStyle mb-0 dataTable align-middle" id="dataTable" width="100%">
                <thead>
                    <tr>
                        <th>S.N</th>
                        <th>Employer Name</th>
                        <th>Company Name</th>
                        <th>Established Year</th>
                        <th>OwnerShip / Size</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
</div>

@endsection

@push('js')
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    let url = "/admin/employers";
    initializeDataTable('#dataTable', url, ['id','user.name','company_name','established_year','ownership_type','location','status','action'], [[1, 'desc']], true);
});
</script>
@endpush
