<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'resume' => 'required|mimes:pdf,doc,docx,rtf',
            'email' => ['required','string','email', 'max:255','unique:' . User::class],
            'mobile_number' => ['required', 'digits_between:7,15'],
            'country_id' => ['required', 'exists:countries,id'],
            'state_id' => ['required', 'exists:states,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'industry_type' => ['required', 'in:it,non_it'],
            'experience_type' => ['required', 'in:experienced,fresher'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'profile_img' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'role' => 'nullable|exists:roles,name',
        ]);

         DB::beginTransaction();

    try {
        $validated['name'] = trim($request->first_name . ' ' . $request->last_name);
        if ($request->hasFile('profile_img')) {
            $file = $request->file('profile_img');
            $path = $file->store('uploads/profile_images', 'public');
            $validated['profile_img'] = $path;
        }


        unset($validated['role']);
        $user = User::create($validated);
        $role = $request->role ?? 'Candidates';
        $user->assignRole($role);

        if($request->hasFile('resume'))
            {
                $directory = public_path('storage/resumes');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            $filename = time() . '_' . $request->resume->getClientOriginalName();
            $tempPath = $request->resume->getRealPath();
            $destinationPath = $directory . '/' . $filename;
            copy($tempPath, $destinationPath);


            // $user->profile_img = $filePath;
            $candidate = $user->candidate()->firstOrCreate([
                'user_id' => $user->id
            ]);


            $candidate->resume = 'resumes/'.$filename;
            $candidate->save();
        }
        event(new Registered($user));
        Auth::login($user);

        DB::commit();
        return redirect()->intended(route('index', absolute: false));
    } catch (\Exception $e) {
        DB::rollBack();
        if (isset($path) && \Storage::disk('public')->exists($path)) {
            \Storage::disk('public')->delete($path);
        }

        return back()->withErrors(['error' => 'Registration failed: ' . $e->getMessage()]);
    }
    }
}
