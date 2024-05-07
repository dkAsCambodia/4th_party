<style>
    .treeview {
        margin: 0;
        padding: 0;
    }

    ul {
        list-style: none;
    }

    .treeview li {
        padding: 2px 0 2px 0px;
    }

    .treeview .parent-list-li {
        position: relative;
    }

    .treeview .parent-list-li .dropdown-arrow {
        position: absolute;
        left: 5px;
        top: 12px;
        content: '';
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 5px solid #888;
    }

    .treeview .parent-list-li .dropdown-arrow.collapsed {
        position: absolute;
        left: 5px;
        top: 12px;
        content: '';
        width: 0;
        height: 0;
        border-top: 5px solid transparent;
        border-bottom: 5px solid transparent;
        border-left: 5px solid #888;
    }

    .treeview .child-list {
        padding-left: 40px;
        margin-top: 10px;
    }

    .treeview>li:first-child>label {
        /* style for the root element - IE8 supports :first-child
    but not :last-child ..... */
    }

    .treeview li.last {
        background-position: 0 -1766px;
    }

    .treeview li>input {
        height: 16px;
        width: 16px;
        /* hide the inputs but keep them in the layout with events (use opacity) */
        opacity: 0;
        filter: alpha(opacity=0);
        /* internet explorer */
        -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(opacity=0)";
        /*IE8*/
    }

    .treeview li>label {
        background: url(https://www.thecssninja.com/demo/css_custom-forms/gr_custom-inputs.png) 0 -1px no-repeat;
        /* move left to cover the original checkbox area */
        margin-left: -20px;
        /* pad the text to make room for image */
        padding-left: 20px;
    }

    .bg-part {
        position: relative;
        padding: 5px 0;
        padding-left: 23px;
        background: #f3f3f3;
        border-radius: 5px;
    }

    /* Unchecked styles */

    .treeview .custom-unchecked {
        background-position: 0 -1px;
    }

    .treeview .custom-unchecked:hover {
        background-position: 0 -21px;
    }

    /* Checked styles */

    .treeview .custom-checked {
        background-position: 0 -81px;
    }

    .treeview .custom-checked:hover {
        background-position: 0 -101px;
    }

    /* Indeterminate styles */

    .treeview .custom-indeterminate {
        background-position: 0 -141px;
    }

    .treeview .custom-indeterminate:hover {
        background-position: 0 -121px;
    }
</style>
<!-- Edit Expense Modal -->
<div id="add_role" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                 <h5 class="modal-title">{{ __('messages.Add Role') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
             </div>
            <div class="modal-body">
                <form action="{{ route('Role: Create Role') }}" method="POST" id="add_role_form">
                    @csrf
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label class="form-label">{{ __('messages.Role Name') }}</label>
                            <input type="text" class="form-control" name="name">
                            <span class="name_err text-danger" role="alert"></span>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label">{{ __('messages.Remark') }}</label>
                            <input type="text" class="form-control" name="remarks">
                        </div>
                    </div>

                    <div class="row">
                        <span class="permissions_err text-danger" role="alert"></span>
                        <div class="mb-3 col-md-12">
                            {{-- <label class="form-label">{{ __('messages.Role Name') }}</label>
                                <input required type="chech" class="form-control" name="permissions[]"> --}}

                            <div>
                                <ul class="treeview">
                                    @foreach ($permissions as $key => $pItems)
                                        <li class="parent-list-li">
                                            <div class="bg-part">
                                                <span class="dropdown-arrow" data-bs-toggle="collapse"
                                                    data-bs-target="#openpermissions{!! $key !!}"
                                                    aria-expanded="false"
                                                    aria-controls="openpermissions{!! $key !!}"></span>
                                                <input type="checkbox" id="permissions{!! $key !!}" />
                                                <label for="permissions{!! $key !!}"
                                                    class="custom-unchecked mb-0">{!! $key !!}</label>

                                                <ul class="child-list collapse"
                                                    id="openpermissions{!! $key !!}">
                                                    @foreach ($pItems as $pI)
                                                        <li class="child-list-li">
                                                            <input type="checkbox" name="permissions[]"
                                                                id="permissions{{ $pI->id }}"
                                                                value='{{ $pI->id }}' />
                                                            <label for="permissions{{ $pI->id }}"
                                                                class="custom-unchecked">{!! preg_replace('/\W\w+\s*(\W*)$/', '$1', $pI->name) !!}</label>
                                                        </li>
                                                    @endforeach

                                                </ul>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="submit-section">
                        <button type="submit"
                            class="btn btn-danger shadow btn-xs me-1">{{ __('messages.Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(function() {
        $('input[type="checkbox"]').change(checkboxChanged);

        function checkboxChanged() {
            var $this = $(this), // The clicked upon checkbox
                checked = $this.prop("checked"), // The new state of the checbox (true or false)
                container = $this.parent(); // The li container of the checkbox

            container
                .find('input[type="checkbox"]') // 1. Get all the child checkboxes of the container
                .prop({
                    // 2. Change the properties of all such checkboxes
                    indeterminate: false,
                    checked: checked,
                })
                .siblings("label") // 3. Get their corresponding labels
                .removeClass(
                    "custom-checked custom-unchecked custom-indeterminate") // 4. Change their CSS classes
                .addClass(checked ? "custom-checked" : "custom-unchecked");

            checkSiblings(container, checked); // Check the siblings of the container
        }

        function checkSiblings($el, checked) {
            // $el is a li
            var parent = $el.parent().parent(), // parent is the containing li element
                all = true,
                indeterminate = false;

            $el.siblings().each(function() {
                // for each li sibling of the current element
                all =
                    all &&
                    $(this).children('input[type="checkbox"]').prop("checked") ===
                    checked;
            });

            if (all && checked) {
                parent
                    .children('input[type="checkbox"]')
                    .prop({
                        indeterminate: false,
                        checked: checked,
                    })
                    .siblings("label")
                    .removeClass(
                        "custom-checked custom-unchecked custom-indeterminate"
                    )
                    .addClass(checked ? "custom-checked" : "custom-unchecked");

                checkSiblings(parent, checked);
            } else if (all && !checked) {
                numChecked = parent
                    .children("ul")
                    .find('input[type="checkbox"]:checked').length;

                indeterminate = numChecked > 0;

                parent
                    .children('input[type="checkbox"]')
                    .prop("checked", checked)
                    .prop("indeterminate", indeterminate)
                    .siblings("label")
                    .removeClass(
                        "custom-checked custom-unchecked custom-indeterminate"
                    )
                    .addClass(
                        indeterminate ?
                        "custom-indeterminate" :
                        checked ?
                        "custom-checked" :
                        "custom-unchecked"
                    );

                checkSiblings(parent, checked);
            } else {
                $el
                    .parents("li")
                    .children('input[type="checkbox"]')
                    .prop({
                        indeterminate: true,
                        checked: false,
                    })
                    .siblings("label")
                    .removeClass(
                        "custom-checked custom-unchecked custom-indeterminate"
                    )
                    .addClass("custom-indeterminate");
            }
        }
    });

    $("#add_role_form").on('submit', function(e){
        e.preventDefault();
        $.ajax({
            url: "{{ route('Role: Create Role') }}",
            type: 'POST',
            data: $('#add_role_form').serialize(),
            success: function(data) {
                if ($.isEmptyObject(data.error)) {
                    location.reload();
                } else {
                    printErrorMsg(data);
                }
            },
        });
    });

    // print error message
    function printErrorMsg(msg) {
        $.each(msg.error, function(key, value) {
            $('.' + key + '_err').text(value[0]);
        });
    }

    // remove error message
    function removeErrorMessage() {
        $('.name_err').text('');
        $('.permissions_err').text('');
    }

    // show add role model
    $(document).on('click', '#add_role', function() {
        removeErrorMessage();
    });
</script>
