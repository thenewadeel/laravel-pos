<div class="card">
    <div class="card-header text-lg font-bold">
        User Profile
    </div>
    {{-- {{ $photo }} --}}
    {{-- Care about people's approval and you will be their prisoner. --}}
    <div
        class="flex flex-row  cursor-pointer border border-gray-300 rounded-xl w-max mx-auto items-center p-2 px-3 m-2 hover:shadow-sm hover:shadow-slate-400 transition-shadow duration-200">
        {{-- {{ $user }}
        {{ $status }}
        {{ $photo }} --}}
        @if ($editing)
            <div class="flex flex-wrap max-w-lg items-center align-middle ">

                <div class="form-group m-2">
                    <label for="first_name">{{ __('user.FName') }}</label>
                    <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                        id="first_name" placeholder="{{ __('user.FName') }}" wire:model="first_name">
                    @error('first_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group m-2">
                    <label for="last_name">{{ __('user.LName') }}</label>
                    <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                        id="last_name" placeholder="{{ __('user.LName') }}" wire:model="last_name">
                    @error('last_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>


                <div class="font-[sans-serif] max-w-md mx-auto">
                    <label class="text-base text-gray-500 font-semibold mb-2 block">{{ __('user.Choose_file') }}</label>
                    <input type="file"
                        class="w-full text-gray-400 font-semibold text-sm bg-white border file:cursor-pointer cursor-pointer file:border-0 file:py-3 file:px-4 file:mr-4 file:bg-gray-100 file:hover:bg-gray-200 file:text-gray-500 rounded"
                        wire:model="photo_file" />
                    <p class="text-xs text-gray-400 mt-2">PNG, JPG, WEBP, and GIF are Allowed.</p>
                    @error('photo_file')
                        <span class="error">{{ $message }}</span>
                    @enderror

                    <div wire:loading wire:target="photo_file" class="font-bold text-lg text-red-300">Uploading...</div>
                </div>
                <div wire:click="update" class="btn btn-primary btn-sm btn-block mx-4">
                    Update
                </div>
            </div>
        @else
            {{-- <div                > --}}
            <img src="{{ Storage::url($user->image) }}" alt="Profile Photo"
                class="w-16 h-16 rounded-lg hover:shadow-md hover:shadow-slate-300 transition-shadow duration-200" />
            <div class="px-2 mx-2 flex flex-col items-center">
                <p class="text-lg text-gray-800 font-semibold ">
                    {{ $user->first_name }}
                    {{ $user->last_name }}
                </p>
                <p class="text-md text-gray-400 -mt-4">{{ $user->email }}</p>
            </div>
            <div class="btn btn-sm" wire:click="toggleEditing">
                <i class="fas fa-pencil-alt text-sm text-slate-300 hover:text-red-500 transition-all duration-300"></i>
            </div>
            {{-- </div> --}}
        @endif
    </div>
</div>
