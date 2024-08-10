<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class UserProfile extends Component
{
    use WithFileUploads;

    public $user;
    public $photo, $photo_file;
    public $first_name;
    public $last_name;
    // email":"admin@qcl.pos"
    public $rules = [
        'first_name' => 'sometimes|string|max:255',
        'last_name' => 'sometimes|string|max:255',
        // 'email' => 'sometimes|nullable|unique:users,email,' . $user->id,
        // 'type' => 'sometimes|nullable|string|in:user,admin,cashier,accountant,order-taker,chef,stockBoy',
        // 'password' => 'sometimes|nullable|string|min:8|confirmed',
        'photo' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Example image validation
    ];
    public $editing = false;
    public $status;
    public function mount($user = null)
    {
        if ($user != null) {
            $this->user = $user;
        } else {
            $this->user = auth()->user();
        }
        $this->photo = $this->user->image;
        $this->first_name = $this->user->first_name;
        $this->last_name = $this->user->last_name;
    }
    public function toggleEditing()
    {
        $this->editing = !$this->editing;
    }
    public function render()
    {
        return view('livewire.user-profile');
    }
    public function update()
    {

        $user = User::find($this->user->id);
        // Update only provided attributes
        // dd($this->photo_file);
        $userfields = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
        ];
        // Handle image upload if provided
        if ($this->photo_file) {
            // Delete old image
            if ($user->image) {
                Storage::delete($user->image);
            }
            // Store new image
            $image_path = $this->photo_file->store('users', 'public');
            $user->image = $image_path;
            // dd($image_path);
            $userfields['image'] = $image_path;
        }
        $user->update($userfields);

        // Save changes
        if ($user->save()) {
            $this->status = 'green';
            $this->editing = false;
            $this->mount($user);
        } else {
            $this->status = 'red';
        }
    }
}
