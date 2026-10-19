<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ url('assets/backend/css/styles.css') }}">

    <link href="{{ url('assets/backend/dist/css/style.min.css') }} " rel="stylesheet" />


    <link href="{{ url('assets/backend/libs/select2/select2.min.css') }} " rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />

    <style>
        #dataTable tbody tr:hover {
            background-color: #f9f9f9;
        }

        /* Pagination styling */
        .dataTables_wrapper .dataTables_paginate {
            display: flex;
            justify-content: center;
            gap: 5px;
            align-items: center;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border: 1px solid #dee2e6;
            color: #6c757d !important;
            padding: 6px 12px;
            border-radius: 5px;
            background: #fff;
            transition: all 0.2s;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background-color: #f1f1f1;
            color: #000 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background-color: #0d1b48 !important;
            /* dark navy color */
            color: #fff !important;
            border-color: #0d1b48 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        #dataTable th:last-child,
        #dataTable td:last-child {
            text-align: right !important;
        }

        .modal-xxl {
            max-width: 70%;
            /* or any width you want */
        }

        .tableStyle tbody tr td {
            padding: 6px 1px !important;
        }

        .select2-container--default .select2-selection--single {
            height: 38px !important;
            padding: 6px 12px;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
            right: 10px;
        }
    </style>
    @stack('css')
</head>

<body>
    @livewireStyles
    @wireUiScripts
    <div id="main-wrapper">
        @include('admin.layouts.header')
        @include('admin.sidebar')
        <div class="page-wrapper">
            <div class="page-titles" style="padding-bottom: 0px !important;">
                <div class="row">
                    <div class="col-8 align-self-center">
                        <nav aria-label="breadcrumb">
                            <h3 class="mb-0 fw-bold">
                                @yield('page_heading')
                            </h3>
                        </nav>
                    </div>
                    <div class="col-4 d-flex align-items-center justify-content-end">
                        @yield('top_buttion')
                        <!-- <a href="{{ url()->previous() }}" class="btn btn-sm d-flex align-items-center ms-3 addButton">
                <i class="ri-arrow-go-back-line"></i>   Back
              </a> -->
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                @yield('content')

                @include('components.job-bot')
                 <!-- @include('jobbot::components.job-bot') -->
            </div>
        </div>
    </div>

    @include('layouts.partials.footer')
    @stack('skill_form')
    @livewireScripts
</body>

@stack('js')

<script>
    function confirmetion_popup(formData, e) {
        e.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                formData.submit();
            }
        });
    }

    const BaseTableColumns = {
        id: {
            data: null,
            render: function (data, type, row, meta) {
                return meta.row + 1;
            },
            orderable: false,
            searchable: false
        },
        action: {
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false
        }
    };

    function generateDataTableColumn(columnNames) {
        const dynamicColumns = columnNames.map(name => {
            if (name === 'id') {
                return BaseTableColumns.id
            } else if (name === 'checkbox') {
                return {
                    data: name,
                    name: name,
                    orderable: false,
                    searchable: false
                };
            } else if (name === 'action') {
                return BaseTableColumns.action
            } else {
                return {
                    data: name,
                    name: name,
                };
            }
        });
        return [...dynamicColumns];
    }


    //create table with dynamic columns
    function initializeDataTable(tableId, ajaxUrl, columnNames, order = [
        [1, 'asc']
    ], paging = true) {
        const columnsConfig = generateDataTableColumn(columnNames);

        $(document).ready(function () {
            const table = $(tableId).DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"],
                paging: paging,
                ajax: {
                    url: ajaxUrl,
                    type: 'GET',
                },
                columns: columnsConfig,
                order: order,
                dom: 'Blftip',
                language: {
                    search: "",
                    searchPlaceholder: "Search...",
                    lengthMenu: "_MENU_",
                    paginate: {
                        previous: "Previous",
                        next: "Next"
                    }
                },
                initComplete: function () {
                    $(`${tableId}_filter input`).addClass('form-control form-control-md');
                    $(`${tableId}_length select`).addClass('form-control form-control-md');
                    const topRow = $('<div class="d-flex align-items-center"></div>');
                    const lengthDiv = $(`${tableId}_length`).addClass(
                        'col-md-6 d-flex align-items-center');
                    const filterDiv = $(`${tableId}_filter`).addClass(
                        'col-md-6 d-flex justify-content-end');

                    topRow.append(lengthDiv, filterDiv);
                    $(tableId).before(topRow);
                    const bottomRow = $('<div class="d-flex align-items-center mt-2"></div>');
                    const infoDiv = $(`${tableId}_info`).addClass('col-md-6');
                    const paginateDiv = $(`${tableId}_paginate`).addClass(
                        'col-md-6 d-flex justify-content-end');

                    bottomRow.append(infoDiv, paginateDiv);
                    $(tableId).after(bottomRow);
                }
            });
        });
    }



    function openEditModal(id) {
        Livewire.dispatch('setData', [{
            id: id
        }]);
    }

    function openViewModal(id) {
        Livewire.dispatch('viewJob', [{
            id: id
        }]);
    }

    document.addEventListener('modal-hide', function (event) {
        let modalId = event.detail.id;
        $('#' + modalId).modal('hide');
    });

    document.addEventListener('modal-show', function (event) {
        let modalId = event.detail.id;
        $('#' + modalId).modal('show');
    });

    document.addEventListener('refreshTable', function (event) {
        let tableId = event.detail.id;
        if (tableId) {
            $('#' + tableId).DataTable().ajax.reload(null, false);
        }
    });



    $(document).on('click', '.delete-button', function () {
        let url = $(this).data('url');
        let tableId = $(this).data('table');
        Swal.fire({
            title: "Are you sure?",
            text: "This action cannot be undone!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        _method: "DELETE",
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        Swal.fire("Deleted!", response.message ?? "Item deleted successfully.",
                            "success");
                        if (tableId) {
                            $('#' + tableId).DataTable().ajax.reload(null, false);
                        } else {
                            location.reload();
                        }
                    },
                    error: function (xhr) {
                        if (tableId) {
                            $('#' + tableId).DataTable().ajax.reload(null, false);
                        } else {
                            location.reload();
                        }
                        Swal.fire("Error!", xhr.responseJSON?.message ??
                            "Something went wrong.", "error");
                    }
                });
            }
        });
    });


    window.addEventListener('success', event => {
        // Swal.fire({
        //     icon: 'success',
        //     title: 'Success!',
        //     text: event.detail.message || 'Action completed successfully!',
        //     showConfirmButton: false,
        //     timer: 2000
        // });

        const message = event.detail.message || 'Action completed successfully!';
        const jobUrl = event.detail.url;

        if (jobUrl) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                html: `
                <p>${message}</p>
                <input type="text" id="jobUrl"
                       value="${jobUrl}"
                       class="swal2-input" readonly>
            `,
                confirmButtonText: 'Copy URL',
                showCancelButton: true,
                cancelButtonText: 'Close'
            }).then((result) => {

                if (result.isConfirmed) {
                    navigator.clipboard.writeText(jobUrl).then(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Copied!',
                            text: 'URL copied to clipboard',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    });
                }

            });
        } else {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: message,
                showConfirmButton: false,
                timer: 2000
            });
        }
    });


    $(document).on('change', '.toggle-data', function () {
        let checkbox = $(this);
        let id = checkbox.data('id');
        let field = checkbox.data('field');
        let route = checkbox.data('route');
        let newStatus = checkbox.is(':checked') ? 1 : 0;
        let oldStatus = newStatus === 1 ? 0 : 1;
        let showTitle = newStatus === 1 ? 'Activate' : 'Deactivate';
        console.log(route);

        Swal.fire({
            title: 'Are you sure?',
            text: `Do you want ${showTitle} this job?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, update it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: route,
                    type: "POST",
                    data: {
                        id: id,
                        field: field,
                        value: newStatus,
                        _token: $('meta[name="csrf-token"]').attr("content")
                    },
                    success: function (res) {
                        if (res.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Error', res.message, 'error');
                            checkbox.prop('checked', oldStatus === 1);
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Something went wrong.', 'error');
                        checkbox.prop('checked', oldStatus === 1);
                    }
                });
            } else {
                checkbox.prop('checked', oldStatus === 1);
            }
        });
    });


    $(document).on('change', '.toggle-status', function () {
        let checkbox = $(this);
        let id = checkbox.data('id');
        let newStatus = checkbox.is(':checked') ? 'active' : 'inactive';
        let oldStatus = newStatus === 'active' ? 'inactive' : 'active';
        Swal.fire({
            title: 'Are you sure?',
            text: `Do you want to set this user as ${newStatus.toUpperCase()}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, update it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('admin.users.updateStatus') }}",
                    type: "POST",
                    data: {
                        id: id,
                        status: newStatus,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (res) {
                        if (res.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Error', res.message, 'error');
                            checkbox.prop('checked', oldStatus === 'active');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Something went wrong.', 'error');
                        checkbox.prop('checked', oldStatus === 'active');
                    }
                });
            } else {
                checkbox.prop('checked', oldStatus === 'active');
            }
        });
    });

    window.addEventListener('refresh-select2', (event) => {
        let modal_id = event.detail.id;

        setTimeout(() => {
            console.log('test3 - refreshing select2 for:', modal_id);

            $(`#${modal_id} .select2`).select2({
                dropdownParent: $(`#${modal_id}`),
                width: '100%'
            });
        }, 500);
    });
    window.addEventListener('refresh-select3', (event) => {
        console.log('refresh-select3');
        setTimeout(() => {
            $(`.select2`).select2();
        }, 500);
    });
</script>

</html>
