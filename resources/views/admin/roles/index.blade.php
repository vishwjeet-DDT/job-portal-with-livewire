@extends('layouts.admin')

@section('page_title', 'Roles Management')

@section('page_heading')
 Roles List
@endsection

@can('roles.create')
@section('top_buttion')
 <button type="button" class="btn addButton" data-bs-toggle="modal" data-bs-target="#createModal">
        Add Role
    </button>
@endsection
@endcan

@section('content')
<section class="content">
    <div class="card borderRadius">
        <div class="card-body p-0 px-3">
            <table class="table table-lg tableStyle mb-0 dataTable align-middle" id="dataTable" width="100%">
                <thead>
                    <tr>
                        <th scope="col">S.No</th>
                        <th>Name</th>
                        <th>Label</th>
                        <th scope="col" align-end>Actions</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
</section>

<x-livewire-modal id="createModal" title="Create Role">
    <livewire:role.create />
</x-livewire-modal>

<x-livewire-modal id="editModal" title="Edit Role">
    <livewire:role.edit :role="$role ?? null" />
</x-livewire-modal>


@endsection

@push('js')
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        let url = "/admin/roles";
        initializeDataTable('#dataTable', url, ['id','name','label','action'], [[1, 'desc']], true);
    });
</script>
@endpush


