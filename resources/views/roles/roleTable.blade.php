@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}

    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Roles') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('messages.Roles List') }}</a></li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('messages.Roles List') }}</h4>
                            @if(auth()->user()->can('Role: Create Role'))
                                <button type="submit" class="btn btn-danger shadow btn-xs me-1 add_record" style="float: right;" data-toggle="modal"
                                    data-target="#add_role">
                                    {{ __('messages.Add Role') }}
                                </button>
                            @endif
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="roleDataTable" style="width: 100% !important">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.Role Name') }}</th>
                                            <th>{{ __('messages.Remark') }}</th>
                                            <th>{{ __('messages.Create Date') }}</th>
                                            @if( auth()->user()->can('Edit/Update Role') || auth()->user()->can('Channel: Delete Channel') )
                                                <th>{{ __('messages.Action') }}</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('roles.addRole');

    <!-- /Edit Expense Modal -->
    <!-- Edit Expense Modal -->
    <div id="edit_role" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Edit Role') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="edit_role_append">

                </div>
            </div>
        </div>
    </div>
    <!-- /Edit Expense Modal -->

    <!-- Delete User Modal -->
    <div class="modal custom-modal fade" id="delete_role" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>{{ __('messages.Delete Role') }}</h3>
                        <p>{{ __('messages.Are you sure want to delete?') }}</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <form action="{{ route('Channel: Delete Channel') }}" method="POST">
                            @csrf
                            <input type="hidden" id="e_id" name="id">
                            <div class="row">
                                <div class="col-6">
                                @if(auth()->user()->can('Channel: Delete Channel'))
                                    <button type="submit"
                                        class="btn btn-primary-cus continue-btn submit-btn">{{ __('messages.Delete') }}</button>
                                @endif
                                </div>
                                <div class="col-6">
                                    <a href="javascript:void(0);" data-dismiss="modal"
                                        class="btn btn-primary-cus cancel-btn">{{ __('messages.Cancel') }}</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Delete User Modal -->
    @section('script')
        <script>
            function editRoleAppend(id){
                const xhttp = new XMLHttpRequest();
                xhttp.onload = function() {
                    document.getElementById("edit_role_append").innerHTML =
                    this.responseText;
                }
                xhttp.open("GET", "{{ url('admin/roles') }}/"+id);
                xhttp.send();
            }
        </script>

        <script>
            // datatables
            $(function () {
                $('#roleDataTable').DataTable({
                    rowReorder: {
                        selector: 'td:nth-child(2)'
                    },
                    responsive: true,
                    processing: true,
                    serverSide: true,
                    order: [[ 2, "desc" ]],
                    ajax: {
                        url: "{{ route('Role: View Role') }}",
                        error: function (jqXHR) {
                            if (jqXHR && jqXHR.status == 401) {location.reload()}
                        },
                    },
                    columns: [
                        {data: 'name'},
                        {data: 'remarks'},
                        {data: 'created_at'},
                        {data: 'action'},
                    ],
                    columnDefs: [
                        { responsivePriority: 1, targets: 0 },
                        { responsivePriority: 2, targets: 3 },
                    ],
                    language: {
                        search: "{{ trans('messages.Search') }}",
                        info: "_START_ - _END_ {{ trans('messages.of') }} _TOTAL_",
                        infoEmpty: "0 - 0 {{ trans('messages.of') }} 0",
                        lengthMenu: "{{ trans('messages.records per page') }} _MENU_",
                        infoFiltered: "({{ trans('messages.filtered from') }} _MAX_ {{ trans('messages.results') }})",
                        zeroRecords: "{{ trans('messages.No matching records found') }}",
                        loadingRecords: "{{ trans('messages.Loading') }}",
                        paginate: {
                            previous: "<",
                            next: ">",
                        },
                        emptyTable: "{{ trans('messages.No data available in the table') }}",
                        processing: "{{ trans('messages.processing') }}",
                    },
                    dom: 'frt<"bottom"ipl><"clear">',
                });
            });

            // delete
            $(document).on('click', '.delete_user', function() {
                $('#e_id').val($(this).data('id'));
            });
        </script>
    @endsection
@endsection
