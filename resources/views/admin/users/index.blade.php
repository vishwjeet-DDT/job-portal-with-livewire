@extends('layouts.admin')
@section('page_heading')
    {{ $roleName ?? "Users" }}
@endsection

@section('top_buttion')
      <button type="button" class="btn addButton" data-bs-toggle="modal" data-bs-target="#createModal">
        Add {{$roleName}}
    </button>
@endsection
@section('content')

    <div class="col-12">
        <div class="card borderRadius">
            <div class="card-body p-0 px-3">
                <table class="table table-lg tableStyle mb-0 dataTable align-middle" id="dataTable" width="100%">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 50px;">S.N</th>
                            <th scope="col">Name</th>
                            <th scope="col" >Phone</th>
                            <th>Status</th>
                            <th scope="col" style="text-align: right;">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
@php
    if($roleName == 'Candidates'){
      $class = 'xxl';
    }else{
     $class = 'lg';
    }

@endphp
<x-livewire-modal id="createModal" :title="'Create ' . $roleName" :modal_size="$class">
    <livewire:user.create :usertype="$roleName ?? 'admin'" />
</x-livewire-modal>

<x-livewire-modal id="editModal" :title="'Edit ' . $roleName" :modal_size="$class">
    <livewire:user.edit  :usertype="$roleName ?? 'admin'" />
</x-livewire-modal>
@endsection

@push('js')




  <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
 <!-- DataTables CSS -->

<script type="text/javascript">
    $(document).ready(function() {
        let url = "/admin/user/{{ $roleName }}";
        initializeDataTable('#dataTable', url, ['id','name','mobile_number','status','action'], [[1, 'desc']], true);



    });
</script>
@endpush
