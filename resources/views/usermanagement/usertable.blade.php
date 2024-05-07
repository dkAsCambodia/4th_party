@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}

    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li>
                    <li class="breadcrumb-item"> {{ __('messages.Users List') }} </li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('messages.Users List') }}</h4>
                            @if (auth()->user()->can('User: Add User'))
                                <button type="submit" class="btn btn-danger shadow btn-xs me-1 add_record" id="add-user"
                                    style="float: right;" data-toggle="modal" data-target="#add_user">
                                    {{ __('messages.Add User') }}
                                </button>
                            @endif
                        </div>

                        <div class="card-body row">
                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Search') }}</label>
                                <input id="search" type="search" class="form-control">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Roles') }}</label>
                                <select id="filterRole" class="form-control filterTable">
                                    <option value="" selected>{{ __('messages.All') }}</option>
                                    @foreach ($roles as $rl)
                                        <option value="{{ $rl->id }}">{{ $rl->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="userDataTable" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.User Name') }}</th>
                                            <th>{{ __('messages.Nick Name') }}</th>
                                            <th>{{ __('messages.Role Name') }}</th>
                                            <th>{{ __('messages.Status') }}</th>
                                            <th>{{ __('messages.Created Time') }}</th>
                                            <th>{{ __('messages.Action') }}</th>
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
    <?php $validMsg = __('messages.Please fill out this field'); ?>
    <!-- Add User Modal -->
    <div id="add_user" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Add User') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="add_user_form" action="{{ route('User: Add User') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.User Name') }}</label>
                                <input type="text" class="form-control" name="user_name" id="username">
                                <span class="text-danger user_name_err"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Nick Name') }}</label>
                                <input type="text" class="form-control" name="nick_name" id="nick_name">
                                <span class="text-danger nick_name_err"></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Role') }}</label>
                                <select class="form-control" name="role" id="role">
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger role_err"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Password') }}</label>
                                <input type="password" class="form-control" name="password" id="password">
                                <span class="text-danger password_err"></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Email') }}</label>
                                <input type="text" class="form-control" name="email">
                                <span class="text-danger email_err"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Mobile Number') }}</label>
                                <input type="text" class="form-control" name="mobile_number">
                                <span class="text-danger mobile_number_err"></span>
                            </div>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label class="form-label">{{ __('messages.Timezone') }}</label>
                            <select name="timezone" id="timezone" class="form-control" aria-label="Default select example">
                                <option value="">{{ __('messages.Select') }}</option>
                                @foreach ($timezones as $tz)
                                    <option value="{{ $tz->id }}">{{ __('messages.'.$tz->timezone) }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger timezone_err"></span>
                        </div>
                        <div class="submit-section">
                            <button type="submit"
                                class="btn btn-danger shadow btn-xs me-1 add_record">{{ __('messages.Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Add User Modal -->

    <!-- Edit User Modal -->
    <div id="edit_user" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Edit User') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="edit_user_form" action="{{ route('User: Update User') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.User Name') }}</label>
                                <input type="text" class="form-control" name="user_name" id="e_user_name">
                                <span class="text-danger user_name_err"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Nick Name') }}</label>
                                <input type="text" class="form-control" name="nick_name" id="e_nick_name">
                                <input type="hidden" value="" id="e_user_id" name="userId">
                                <span class="text-danger nick_name_err"></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Role') }}</label>
                                <select class="form-control" name="role" id="role_assign_edit">
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger role_err"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Password') }}</label>
                                <input type="password" class="form-control" name="password">
                                <span class="text-danger password_err"></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Email') }}</label>
                                <input type="text" class="form-control" name="email" id="e_email">
                                <span class="text-danger email_err"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Mobile Number') }}</label>
                                <input type="text" class="form-control" name="mobile_number" id="e_mobile_number">
                                <span class="text-danger mobile_number_err"></span>
                            </div>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label">{{ __('messages.Timezone') }}</label>
                            <select name="timezone" id="e_timezone" class="form-control" aria-label="Default select example">
                                <option value="">{{ __('messages.Select') }}</option>
                                @foreach ($timezones as $tz)
                                    <option value="{{ $tz->id }}">{{ __('messages.'.$tz->timezone) }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger timezone_err"></span>
                        </div>
                        <div class="submit-section">
                            <button type="submit"
                                class="btn btn-danger shadow btn-xs me-1 add_record">{{ __('messages.Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Edit User Modal -->

    <!-- Delete User Modal -->
    <div class="modal custom-modal fade" id="delete_user" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>{{ __('messages.Delete User') }}</h3>
                        <p>{{ __('messages.Are you sure want to delete?') }}</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <form action="{{ route('User: Delete User') }}" method="POST">
                            @csrf
                            <input type="hidden" id="e_id" name="id">
                            <div class="row">
                                <div class="col-6">
                                    <button type="submit"
                                        class="btn btn-primary-cus continue-btn submit-btn">{{ __('messages.Delete') }}</button>
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
        $(document).on('click', '#add-user', function() {
            var _this = $(this).parents('tr');
            $.ajax({
                url: "roleLists",
                type: 'GET',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(data) {
                    let merchantDatas = data.data
                    if (merchantDatas && merchantDatas.length > 0) {
                        merchantDatas.forEach(element => {
                            let agentSelection = $("#role_assign");
                            agentSelection.append('<option value="' + element.id + '">' +
                                element.name + '</option>');
                        });
                    }
                },
            })
        });
    </script>

    <script>
        // DataTables
        $(function() {
            var table = $('#userDataTable').DataTable({
                rowReorder: {
                    selector: 'td:nth-child(2)'
                },
                responsive: true,
                processing: true,
                serverSide: true,
                order: [
                    [4, "desc"]
                ],
                ajax: {
                    url: "{{ route('User: View All User') }}",
                    data: function(d) {
                        d.filterRole = $('#filterRole').val()
                        d.search = $('#search').val()
                    },
                    error: function (jqXHR) {
                        if (jqXHR && jqXHR.status == 401) {location.reload()}
                    },
                },
                columns: [{
                        data: 'user_name'
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'role_name',
                        searchable: false,
                        sortable: false
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'action',
                        searchable: false,
                        sortable: false
                    },
                ],
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: 5 },
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
                dom: 'rt<"bottom"ipl><"clear">',
            });

            // filter by dropdown
            $('.filterTable').change(function() {
                table.draw();
            });

            // search
            document.getElementById('search').addEventListener('input', (e) => {
                table.draw();
            })
        });

        // add new user
        $("#add_user_form").on('submit', function(e) {
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('User: Add User') }}",
                    type: 'POST',
                    data: $('#add_user_form').serialize(),
                    success: function(data) {
                        if ($.isEmptyObject(data.error)) {
                            location.reload();
                        } else {
                            printErrorMsg(data);
                        }
                    }
                });
            }
        });

        // edit user
        $("#edit_user_form").on('submit', function(e) {
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('User: Update User') }}",
                    type: 'POST',
                    data: $('#edit_user_form').serialize(),
                    success: function(data) {
                        if ($.isEmptyObject(data.error)) {
                            location.reload();
                        } else {
                            printErrorMsg(data);
                        }
                    }
                });
            }
        });

        // get edit user
        $(document).on('click', '.edit_user', function() {
            $("label.error").hide();
            removeErrorMessage();
            $('#e_user_id').val($(this).data('id'));
            $('#e_user_name').val($(this).data('user_name'));
            $('#e_nick_name').val($(this).data('nick_name'));
            $('#e_email').val($(this).data('email'));
            $('#e_timezone').val($(this).data('timezone'));
            $('#e_mobile_number').val($(this).data('mobile_number'));
            $('#e_created_at').val($(this).data('created_at'));
            $('#role_assign_edit').val($(this).data('role_id'));
        });

        //print error message
        function printErrorMsg(msg) {
            $('.text-danger').text('');
            $.each(msg.error, function(key, value) {
                $('.' + key + '_err').text(value[0]);
            });
        }

        //Remove Error Message
        $(document).on('click', '#add-user', function() {
            $("label.error").hide();
            $('#username').val('');
            $('#nick_name').val('');
            $('#role').val('');
            $('#password').val('');
            $('#email').val('');
            $('#timezone').val('');
            $('#mobile_number').val('');
            removeErrorMessage();
        });

        //remove error message
        function removeErrorMessage() {
            $('.user_name_err').text('');
            $('.nick_name_err').text('');
            $('.role_err').text('');
            $('.password_err').text('');
            $('.email_err').text('');
            $('.timezone_err').text('');
            $('.mobile_number_err').text('');
            $('.source_id_err').text('');
        }

        //Delete User
        $(document).on('click', '.delete_user', function() {
            $('#e_id').val($(this).data('id'));
        });
    </script>
    {{-- Jquery Validation --}}
    <style>
        label.error {
            color: #dc3545;
            font-size: 14px;
        }
    </style>
    <script>
        $.validator.addMethod("nospace", function(value, element) {
            return value.indexOf(" ") < 0 && value != "";
        });
        $.validator.addMethod("mobile", function(value, element) {
            return this.optional(element) || /\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/.test(value);
        });

        //Add Form Validation
        $("#add_user_form").validate({
            rules: {
                user_name: {
                    required: true,
                    nospace: true
                },
                nick_name: {
                    required: true
                },
                role: {
                    required: true
                },
                password: {
                    required: true
                },
                timezone: {
                    required: true
                },
                email: {
                    required: true,
                    email: true
                },
                mobile_number: {
                    required: true,
                    mobile: true
                }
            }
        })

        //Edit Form Validation
        $("#edit_user_form").validate({
            rules: {
                user_name: {
                    required: true,
                    nospace: true
                },
                nick_name: {
                    required: true
                },
                role: {
                    required: true
                },
                password: {
                    required: true
                },
                timezone: {
                    required: true
                },
                email: {
                    required: true,
                    email: true
                },
                mobile_number: {
                    required: true,
                    mobile: true
                }
            }
        })
    </script>
@endsection
@endsection
