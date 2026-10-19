@extends('layouts.admin')

@section('page_title', 'Permission Management')

@section('page_heading')
 Permission List
@endsection

@can('permissions.create')
@section('top_buttion')
 <button type="button" class="btn addButton" data-bs-toggle="modal" data-bs-target="#createModal">
    Add Permission
</button>
@endsection
@endcan

@section('content')

<section class="content">
    <div class="card borderRadius">
        <div class="card-body p-0 px-3">
            <table class="table table-bordered tableStyle table-striped" id="dataTable" width="100%">
                <thead>
                    <tr>
                        <th scope="col">S.No</th>
                        <th>label</th>
                        <th>Access Key</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
</section>

<x-livewire-modal id="createModal" title="Create Permission">
    <livewire:permission.create />
</x-livewire-modal>

<x-livewire-modal id="editModal" title="Edit Permission">
    <livewire:permission.edit />
</x-livewire-modal>


@endsection

@push('js')
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
 <!-- DataTables CSS -->

<script type="text/javascript">
    $(document).ready(function() {
        let url = "/admin/permissions";
        initializeDataTable('#dataTable', url, ['id','label','name','action'], true);
    });
</script>
@endpush
