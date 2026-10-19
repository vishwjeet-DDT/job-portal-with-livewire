<x-guest-layout>
     @section('guest_heading',"Login")
    <x-auth-session-status class="mb-4" :status="session('status')" />
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="block mt-1 w-full form-control" type="email" name="email"
                        :value="old('email')" required autofocus autocomplete="username" />
                    <!-- <x-input-error :messages="$errors->get('email')" class="mt-2" /> -->
                    @error('email')
                        <div class="text-danger text-sm">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password" :value="__('Password')" />

                    <x-text-input id="password" class="block mt-1 w-full form-control" type="password" name="password"
                        required autocomplete="current-password" />

                    <!-- <x-input-error :messages="$errors->get('password')" class="mt-2" /> -->
                     @error('password')
                        <div class="text-danger text-sm">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="block mt-4">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            name="remember">
                        <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-4">
                    @if (Route::has('password.request'))
                        <div class="d-flex justify-content-end mt-2 mb-2">
                            <a class="text-decoration-none text-muted" href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        </div>
                    @endif

                    <div class="form-group d-flex justify-content-center mt-3 mb-3">
                        <x-primary-button class="sign_inButton w-75 text-center text-decoration-none">
                            {{ __('Sign In') }}
                        </x-primary-button>
                    </div>
                </div>
            </form>
            <p class="text-center">Don't have an account? <a type="button" id="createNew_account"href="{{ route('register') }}"
                    class="text-decoration-none">Create new account →</a></p>
        </div>
    </div>
</x-guest-layout>
