<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Storage;
use App\Traits\ListOf;

class UsersController extends Controller
{
    use ListOf;

    protected function getModel(): string
    {
        return User::class;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = new User();
        if ($request->search) {
            $users = $users->where('name', 'LIKE', "%{$request->search}%");
        }
        $itemsPerPage = 10;
        if ($request->itemCount) {
            $itemsPerPage = $request->itemCount;
        }
        $users = $users->with(['shops'])->latest()->paginate($itemsPerPage);
        if (request()->wantsJson()) {
            return UserResource::collection($users);
        }
        return view('users.index')->with('users', $users);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $image_path = '';

        if ($request->hasFile('image')) {
            $image_path = $request->file('image')->store('users', 'public');
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'image' => $image_path,
            'password' => $request->password,
            'type' => $request->type,
        ]);

        if (!$user) {
            return redirect()->back()->with('error', __('user.error_creating'));
        }
        return redirect()->route('users.index')->with('success', __('user.success_creating'));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('users.edit')->with('user', $user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // Validation (essential for security and data integrity)
        $this->validate($request, [
            'first_name' => 'sometimes|nullable|string|max:255',
            'last_name' => 'sometimes|nullable|string|max:255',
            'email' => 'sometimes|nullable|unique:users,email,' . $user->id,
            'type' => 'sometimes|nullable|string|in:user,admin,cashier',
            'password' => 'sometimes|nullable|string|min:8|confirmed',
            'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Example image validation
        ]);

        // Update only provided attributes
        $user->update([
            'first_name' => $request->input('first_name', $user->first_name),
            'last_name' => $request->input('last_name', $user->last_name),
            'email' => $request->input('email', $user->email),
            'type' => $request->input('type', $user->type),
            'password' => $request->input('password') ? bcrypt($request->password) : $user->password,
        ]);

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            // Delete old image
            if ($user->image) {
                Storage::delete($user->image);
            }
            // Store new image
            $image_path = $request->file('image')->store('users', 'public');
            $user->image = $image_path;
        }

        // Save changes
        if ($user->save()) {
            return redirect()->route('users.index')->with('success', __('user.success_updating'));
        }

        return redirect()->back()->with('error', __('user.error_updating'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
    public function export()
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }
}
