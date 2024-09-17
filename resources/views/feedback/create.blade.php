@extends('layouts.admin')

@section('title', __('feedback.feedback_get'))
@section('content-header', __('feedback.feedback_get'))

@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])

    <div class="card">
        <div class="card-body">

            <form action="{{ route('orders.storeFeedback', $order) }}" method="POST" enctype="multipart/form-data"
                class="flex flex-col">
                @csrf

                {{-- <div class="form-group">
                    <label for="first_name">{{ __('feedback.FName') }}</label>
                    <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                        id="first_name" placeholder="{{ __('feedback.FName') }}" value="{{ old('first_name') }}">
                    @error('first_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div> --}}
                <div class="flex flex-col md:flex-row">
                    <fieldset class="border border-red-500 rounded-md shadow-md p-4 mx-2 md:w-1/2">
                        <legend>{{ __('feedback.Customer_Info') }}</legend>
                        <div class="form-group">
                            <label for="name">{{ __('feedback.Name') }}</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                id="name" placeholder="{{ __('feedback.Name') }}" value="{{ old('name') }}">
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="email">{{ __('feedback.Email') }}</label>
                            <input type="text" name="email" class="form-control @error('email') is-invalid @enderror"
                                id="email" placeholder="{{ __('feedback.Email') }}" value="{{ old('email') }}">
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="phone">{{ __('feedback.Phone') }}</label>
                            <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                id="phone" placeholder="{{ __('feedback.Phone') }}" value="{{ old('phone') }}">
                            @error('phone')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </fieldset>

                    <fieldset class="mx-2 border  rounded-md shadow-md p-4 md:w-1/2">
                        <legend>{{ __('feedback.Rating') }}</legend>
                        @foreach (['presentation_and_plating', 'taste_and_quality', 'friendliness', 'service', 'knowledge_and_recommendations', 'atmosphere', 'cleanliness', 'overall_experience'] as $field)
                            <div
                                class="form-group flex flex-col md:flex-row justify-between border border-black shdow-sm px-4 pb-2 pt-3 rounded-md align-middle">
                                <label for="{{ $field }}">{{ __('feedback.' . $field) }}</label>
                                <div class="rating">
                                    <input type="radio" name="{{ $field }}" value="1"
                                        id="{{ $field }}-1" class="@error($field) is-invalid @enderror">
                                    <label for="{{ $field }}-1">1</label>
                                    <input type="radio" name="{{ $field }}" value="2"
                                        id="{{ $field }}-2" class="@error($field) is-invalid @enderror">
                                    <label for="{{ $field }}-2">2</label>
                                    <input type="radio" name="{{ $field }}" value="3"
                                        id="{{ $field }}-3" class="@error($field) is-invalid @enderror">
                                    <label for="{{ $field }}-3">3</label>
                                    <input type="radio" name="{{ $field }}" value="4"
                                        id="{{ $field }}-4" class="@error($field) is-invalid @enderror">
                                    <label for="{{ $field }}-4">4</label>
                                    <input type="radio" name="{{ $field }}" value="5"
                                        id="{{ $field }}-5" class="@error($field) is-invalid @enderror">
                                    <label for="{{ $field }}-5">5</label>
                                </div>
                                @error($field)
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        @endforeach
                    </fieldset>

                </div>

                <div class="form-group m-2 border  rounded-md shadow-md p-4">
                    <label for="comments">{{ __('feedback.Comments') }}</label>
                    <textarea name="comments" class="form-control @error('comments') is-invalid @enderror" id="comments" rows="3"></textarea>
                    @error('comments')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <input type="hidden" name="user_id" value="{{ Auth::user()->id }}">
                <input type="hidden" name="order_id" value="{{ $order->id }}">

                <button class="btn btn-success btn-block p-2 text-lg font-bold"
                    type="submit">{{ __('feedback.Submit') }}</button>
            </form>
        </div>
    @endsection

    {{--
    //FEEDBACK FORM
            //Customer Info
            'name'
            'email'
            'phone'

            // Food:
            'presentation_and_plating'
            'taste_and_quality'

            // Service:
            'friendliness'
            'service'
            'knowledge_and_recommendations'

            // Ambiance:
            'atmosphere'
            'cleanliness'

            // Value for Money:
            'overall_experience'

            // Comments
            'comments' --}}
