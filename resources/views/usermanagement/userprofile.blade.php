
@extends('layouts.master')
@section('content')
@include('sidebar.sidebar')
{{-- message --}}
{!! Toastr::message() !!}
<div class="content-body">
    <div class="container-fluid">
        <div class="row page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li>
                <li class="breadcrumb-item"> {{ __('messages.Profile') }} </li>
            </ol>
        </div>
        <!-- row -->
        <div class="row">
            <div class="col-lg-12">
                <div class="profile card card-body px-3 pt-3 pb-0">
                    <div class="profile-head">
                        <div class="photo-content">
                            <div class="cover-photo rounded"></div>
                        </div>
                        <div class="profile-info">
                            <div class="profile-photo">
                                <img src="{{ URL::to('assets/images/'.Session::get('avatar')) }}" class="img-fluid rounded-circle" alt="">
                            </div>
                            <div class="profile-details">
                                <div class="profile-name px-3 pt-2">
                                    <h4 class="text-primary mb-0">{{ Session::get('name') }}</h4>
                                    <p>{{ Session::get('position') }}</p>
                                </div>
                                <div class="profile-email px-2 pt-2">
                                    <h4 class="text-muted mb-0">{{ Session::get('email') }}</h4>
                                    <p>{{ __('messages.Email') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <div class="profile-tab">
                            <div class="custom-tab-1">
                                <ul class="nav nav-tabs">
                                    <li class="nav-item"><a href="#my-posts" data-bs-toggle="tab" class="nav-link active show">{{ __('messages.Posts') }}</a>
                                    </li>
                                    <li class="nav-item"><a href="#about-me" data-bs-toggle="tab" class="nav-link">{{ __('messages.About Me') }}</a>
                                    </li>
                                    <li class="nav-item"><a href="#profile-settings" data-bs-toggle="tab" class="nav-link">{{ __('messages.Setting') }}</a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div id="my-posts" class="tab-pane fade active show">
                                        <div class="my-post-content pt-3">
                                            <div class="post-input">
                                                <textarea name="textarea" id="textarea" cols="30" rows="5" class="form-control bg-transparent" placeholder="Please type what you want...."></textarea>
                                                <a href="javascript:void(0);" class="btn btn-primary light me-1 px-3" data-bs-toggle="modal" data-bs-target="#linkModal"><i class="fa fa-link m-0"></i> </a>
                                                <!-- Modal -->
                                                <div class="modal fade" id="linkModal">
                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">{{ __('messages.Social Links') }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal">
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <a class="btn-social facebook" href="javascript:void(0)"><i class="fab fa-facebook-f"></i></a>
                                                                <a class="btn-social google-plus" href="javascript:void(0)"><i class="fab fa-google-plus-g"></i></a>
                                                                <a class="btn-social linkedin" href="javascript:void(0)"><i class="fab fa-linkedin"></i></a>
                                                                <a class="btn-social instagram" href="javascript:void(0)"><i class="fab fa-instagram"></i></a>
                                                                <a class="btn-social twitter" href="javascript:void(0)"><i class="fab fa-twitter"></i></a>
                                                                <a class="btn-social youtube" href="javascript:void(0)"><i class="fab fa-youtube"></i></a>
                                                                <a class="btn-social whatsapp" href="javascript:void(0)"><i class="fab fa-whatsapp"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <a href="javascript:void(0);" class="btn btn-primary light me-1 px-3" data-bs-toggle="modal" data-bs-target="#cameraModal"><i class="fa fa-camera m-0"></i> </a>
                                                <!-- Modal -->
                                                <div class="modal fade" id="cameraModal">
                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">{{ __('messages.Upload images') }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal">
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="input-group mb-3">
                                                                    <span class="input-group-text">{{ __('messages.Upload') }}</span>
                                                                    <div class="form-file">
                                                                        <input type="file" class="form-file-input form-control">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#postModal">{{ __('messages.Post') }}</a>
                                                <!-- Modal -->
                                                <div class="modal fade" id="postModal">
                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">{{ __('messages.Post') }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal">
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <textarea name="textarea" id="textarea2" cols="30" rows="5" class="form-control bg-transparent" placeholder="Please type what you want...."></textarea>
                                                                <a class="btn btn-primary btn-rounded" href="javascript:void(0)">{{ __('messages.Post') }}</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="about-me" class="tab-pane fade">
                                        <div class="profile-about-me">
                                            <div class="pt-4 border-bottom-1 pb-3">
                                                <h4 class="text-primary">{{ __('messages.About Me') }}</h4>
                                                <p class="mb-2">{{ __('messages.Advance Code, There are websites such as HTML, CSS, Javascript, PHP, Framework Laravel,') }}<br>
                                                    {{ __('messages.How to download Website Design Template, Free source code.') }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="profile-skills mb-5">
                                            <h4 class="text-primary mb-2">{{ __('messages.Skills') }}</h4>
                                            <a href="javascript:void(0);" class="btn btn-primary light btn-xs mb-1">{{ __('messages.Admin') }}</a>
                                            <a href="javascript:void(0);" class="btn btn-primary light btn-xs mb-1">{{ __('messages.Dashboard') }}</a>
                                            <a href="javascript:void(0);" class="btn btn-primary light btn-xs mb-1">{{ __('messages.Photoshop') }}</a>
                                            <a href="javascript:void(0);" class="btn btn-primary light btn-xs mb-1">{{ __('messages.Bootstrap') }}</a>
                                        </div>
                                        <div class="profile-lang  mb-5">
                                            <h4 class="text-primary mb-2">{{ __('messages.Language') }}</h4>
                                            <a href="javascript:void(0);" class="text-muted pe-3 f-s-16"><i class="flag-icon flag-icon-us"></i> {{ __('messages.English') }}</a>
                                            <a href="javascript:void(0);" class="text-muted pe-3 f-s-16"><i class="flag-icon flag-icon-fr"></i> {{ __('messages.Khmer') }}</a>
                                        </div>
                                        <div class="profile-personal-info">
                                            <h4 class="text-primary mb-4">{{ __('messages.Personal Information') }}</h4>
                                            <div class="row mb-2">
                                                <div class="col-sm-3 col-5">
                                                    <h5 class="f-w-500">{{ __('messages.Name') }} <span class="pull-end">:</span>
                                                    </h5>
                                                </div>
                                                <div class="col-sm-9 col-7"><span>{{ Session::get('name') }}</span>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-sm-3 col-5">
                                                    <h5 class="f-w-500">{{ __('messages.Email') }} <span class="pull-end">:</span>
                                                    </h5>
                                                </div>
                                                <div class="col-sm-9 col-7"><span>{{ Session::get('email') }}</span>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-sm-3 col-5">
                                                    <h5 class="f-w-500">{{ __('messages.Availability') }} <span class="pull-end">:</span></h5>
                                                </div>
                                                <div class="col-sm-9 col-7"><span>{{ __('messages.Full Time (Free Lancer)') }}</span>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-sm-3 col-5">
                                                    <h5 class="f-w-500">{{ __('messages.Age') }} <span class="pull-end">:</span>
                                                    </h5>
                                                </div>
                                                <div class="col-sm-9 col-7"><span>27</span>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-sm-3 col-5">
                                                    <h5 class="f-w-500">{{ __('messages.Location') }} <span class="pull-end">:</span></h5>
                                                </div>
                                                <div class="col-sm-9 col-7"><span>{{ __('messages.Cambodia') }}</span>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-sm-3 col-5">
                                                    <h5 class="f-w-500">{{ __('messages.Year Experience') }}<span class="pull-end">:</span></h5>
                                                </div>
                                                <div class="col-sm-9 col-7"><span>{{ __('messages.07 Year Experiences') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="profile-settings" class="tab-pane fade">
                                        <div class="pt-3">
                                            <div class="settings-form">
                                                <h4 class="text-primary">{{ __('messages.Account Setting') }}</h4>
                                                <form>
                                                    <div class="row">
                                                        <div class="mb-3 col-md-6">
                                                            <label class="form-label">{{ __('messages.Name') }}</label>
                                                            <input type="text" placeholder="Name" class="form-control" name="name" value="{{ Session::get('name') }}">
                                                        </div>
                                                        <div class="mb-3 col-md-6">
                                                            <label class="form-label">{{ __('messages.User ID') }}</label>
                                                            <input type="text" class="form-control" name="user_id" readonly value="{{ Session::get('user_id') }}">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="mb-3 col-md-6">
                                                            <label class="form-label">{{ __('messages.Email') }}</label>
                                                            <input type="email" placeholder="Email" class="form-control" name="email" value="{{ Session::get('email') }}">
                                                        </div>
                                                        <div class="mb-3 col-md-6">
                                                            <label class="form-label">{{ __('messages.Join Date') }}</label>
                                                            <input type="text" class="form-control" name="join_date" readonly value="{{ Session::get('join_date') }}">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="mb-3 col-md-6">
                                                            <label class="form-label">{{ __('messages.Phone Number') }}</label>
                                                            <input type="tel" placeholder="Phone Number" class="form-control" name="phone_number" value="{{ Session::get('phone_number') }}">
                                                        </div>
                                                        <div class="mb-3 col-md-6">
                                                            <label class="form-label">{{ __('messages.Role Name') }}</label>
                                                            <input type="text" class="form-control" name="role_name" readonly value="{{ Session::get('role_name') }}">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="mb-3 col-md-6">
                                                            <label class="form-label">{{ __('messages.Status') }}</label>
                                                            <input type="tel" class="form-control" name="status" readonly value="{{ Session::get('status') }}">
                                                        </div>
                                                        <div class="mb-3 col-md-6">
                                                            <label class="form-label">{{ __('messages.Position') }}</label>
                                                            <input type="text" class="form-control" name="position" readonly value="{{ Session::get('position') }}">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="mb-3 col-md-6">
                                                            <label class="form-label">{{ __('messages.Department') }}</label>
                                                            <input type="text" class="form-control" name="agent_id" readonly value="{{ Session::get('agent_id') }}">
                                                        </div>
                                                    </div>
                                                    <button class="btn btn-primary" type="submit">{{ __('messages.Update') }}</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Modal -->
                            <div class="modal fade" id="replyModal">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ __('messages.Post Reply') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form>
                                                <textarea class="form-control" rows="4">{{ __('messages.Message') }}</textarea>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">{{ __('messages.Close') }}</button>
                                            <button type="button" class="btn btn-primary">{{ __('messages.Reply') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Expense Modal -->
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
                <form action="{{ route('user/update') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="mb-3 col-md-12">
                            <label class="form-label">{{ __('messages.User ID') }}</label>
                            <input type="text" class="form-control" id="e_user_id" name="user_id" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label class="form-label">{{ __('messages.Name') }}</label>
                            <input type="text" class="form-control" id="e_name" name="name">
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label">{{ __('messages.Email') }}</label>
                            <input type="email" class="form-control" id="e_email" name="email">
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label class="form-label">{{ __('messages.Mobile') }}</label>
                            <input type="tel" class="form-control" id="e_phone_number" name="phone_number">
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label">{{ __('messages.Status') }}</label>
                            <input type="text" class="form-control" id="e_status" name="status" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label class="form-label">{{ __('messages.Role Name') }}</label>
                            <input type="text" class="form-control" id="e_role_name" name="role_name" readonly>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label">{{ __('messages.Join Date') }}</label>
                            <input type="text" class="form-control" id="e_join_date" name="join_date" readonly>
                        </div>
                    </div>
                    <div class="submit-section">
                        <button type="submit" class="btn btn-danger shadow btn-xs me-1 add_record">{{ __('messages.Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- /Edit Expense Modal -->

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
                    <form action="{{ route('user/delete') }}" method="POST">
                        @csrf
                        <input type="hidden" id="e_id" name="id">
                        <div class="row">
                            <div class="col-6">
                                <button type="submit" class="btn btn-primary-cus continue-btn submit-btn">{{ __('messages.Delete') }}</button>
                            </div>
                            <div class="col-6">
                                <a href="javascript:void(0);" data-dismiss="modal" class="btn btn-primary-cus cancel-btn">{{ __('messages.Cancel') }}</a>
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
    <!-- Bootstrap Core JS -->
    <script src="{{URL::to('assets/js/bootstrap.min.js')}}"></script>

    {{-- show data on model or edit --}}
    <script>
        $(document).on('click','.edit_user',function()
        {
            var _this = $(this).parents('tr');
            $('#e_user_id').val(_this.find('.user_id').text());
            $('#e_name').val(_this.find('.name').text());
            $('#e_email').val(_this.find('.email').text());
            $('#e_phone_number').val(_this.find('.phone_number').text());
            $('#e_status').val(_this.find('.status').text());
            $('#e_role_name').val(_this.find('.role_name').text());
            $('#e_join_date').val(_this.find('.join_date').text());
        });
    </script>

    {{-- delete user --}}
    <script>
        $(document).on('click','.delete_user',function()
        {
            var _this = $(this).parents('tr');
            $('#e_id').val(_this.find('.user_id').text());
        });
    </script>

@endsection
@endsection
