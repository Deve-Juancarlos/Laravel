@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
@endphp

<style>
    .profile-avatar-container {
            position: relative;
    } 

    .profile-avatar, .profile-avatar-img {
            width: 120px;
            height: 120px;
            border-radius: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 700;
            color: white;
            border: 5px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    } 

    .profile-avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-avatar-edit {
            position: absolute;
            bottom: -10px;
            right: -10px;
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
    } 

    .profile-avatar-edit:hover {
            transform: scale(1.1);
            background: var(--primary);
            color: white;
    } 
</style>

<div>
    <div class="profile-avatar-container">
        @if(Auth::user()->avatar && \Storage::disk('public')->exists(Auth::user()->avatar))
            <img src="{{ Storage::url(Auth::user()->avatar) }}" alt="Avatar" class="profile-avatar-img">
        @else
            <div class="profile-avatar">
                {{ strtoupper(substr(Auth::user()->usuario ?? 'U', 0, 1)) }}
            </div>
        @endif

        <label class="profile-avatar-edit" title="Cambiar foto de perfil">
            <i class="fas fa-camera"></i>
            <input type="file" wire:model="photo" accept="image/*" style="display:none;">
        </label>
    </div>

    @if(session('notify'))
        <div class="alert alert-success mt-3">
            {{ session('notify') }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('notify', (event) => {
            alert(event.message);
        });
    });
</script>
@endpush