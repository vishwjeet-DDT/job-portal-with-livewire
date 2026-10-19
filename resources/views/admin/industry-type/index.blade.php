@extends('layouts.admin')

@section('page_heading', 'Industries List')
@can('industry-type.create')
@section('top_buttion')
    <button type="button" class="btn addButton" data-bs-toggle="modal" data-bs-target="#createModal">
        Add Industry Type
    </button>
@endsection
@endcan
@section('content')
<div class="card borderRadius">
    <div class="card-body table-responsive p-0 px-3">
        <table class="table tableStyle mb-0 dataTable align-middle" id="dataTable" width="100%">
            <thead>
                <tr>
                    <th scope="col" style="width: 50px;">S.N</th>
                    <th scope="col">Name</th>
                    <th scope="col" style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<x-livewire-modal id="createModal" title="Create Industry Type" modal_size="md">
    <livewire:industry-type.create />
</x-livewire-modal>
<x-livewire-modal id="editModal" title="Edit Industry Type" modal_size="md">
    <livewire:industry-type.edit />
</x-livewire-modal>
@endsection

@push('js')
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        let url = "/admin/industry-type";
        initializeDataTable('#dataTable', url, ['id','name','action'], [[1, 'desc']], true);
    });
</script>
@endpush
