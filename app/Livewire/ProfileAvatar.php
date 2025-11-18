<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileAvatar extends Component
{
    use WithFileUploads;

    public $photo;

    public function updatedPhoto()
    {
        // Validación
        $this->validate([
            'photo' => 'image|max:2048|mimes:jpeg,png,jpg,gif',
        ]);

        try {
            // Guardar la imagen
            $path = $this->photo->store('avatars', 'public');

            // Actualizar el usuario
            Auth::user()->update(['avatar' => $path]);

            // Mensaje de éxito
            $this->dispatch('notify', message: '¡Foto actualizada con éxito!');

            // Limpiar el input
            $this->photo = null;

        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.profile-avatar');
    }
}
