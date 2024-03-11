@extends('layouts.admin')

@section('title', __('user.User_List'))
@section('content-header', __('user.User_List'))
@section('content-actions')
    <a href="{{ route('users.create') }}" class="btn btn-primary">{{ __('user.Create_User') }}</a>
@endsection
@section('css')
    <link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection
@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])
    <div class="card user-list">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('user.ID') }}</th>
                        <th>{{ __('user.Name') }}</th>
                        <th>{{ 'Type' }}</th>
                        <th>{{ __('user.eMail') }}</th>
                        <th>{{ __('user.Shops') }}</th>
                        <th>{{ __('user.Created_At') }}</th>
                        <th>{{ __('user.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->first_name }}/ {{ $user->last_name }}</td>
                            <td>
                                <span
                                    class="right badge badge-{{ $user->type ? 'success' : 'danger' }}">{{ $user->type }}</span>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if ($user->type == 'cashier')
                                    @foreach ($user->shops as $shop)
                                        {{ $shop->name }} ,
                                    @endforeach
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $user->created_at }}</td>
                            <td>
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-primary"><i
                                        class="fas fa-edit"></i></a>
                                <button class="btn btn-danger btn-delete" data-url="{{ route('users.destroy', $user) }}"><i
                                        class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $users->render() }}
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script type="module">
        $(document).ready(function() {
            $(document).on('click', '.btn-delete', function() {
                var $this = $(this);
                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: 'btn btn-success',
                        cancelButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                })

                swalWithBootstrapButtons.fire({
                    title: {{ __('user.sure') }},
                    text: {{ __('user.really_delete') }},
                    icon: {{ __('user.Create_User') }} 'warning',
                    showCancelButton: true,
                    confirmButtonText: {{ __('user.yes_delete') }},
                    cancelButtonText: {{ __('user.No') }},
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        $.post($this.data('url'), {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        }, function(res) {
                            $this.closest('tr').fadeOut(500, function() {
                                $(this).remove();
                            })
                        })
                    }
                })
            })
        })
    </script>
@endsection
