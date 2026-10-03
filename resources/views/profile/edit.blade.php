@extends('layouts.app')

@section('title', 'Manage Profile')
@section('page-title', 'My Profile')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<style>
    .cropper-view-box,
    .cropper-face {
        border-radius: 50%;
    }
    .cropper-view-box {
        outline: 2px solid #ffffff;
        outline-color: rgba(255, 255, 255, 0.95);
    }
</style>
@endpush

@section('content')
<div class="space-y-4">

    {{-- Sleek Top Summary Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white border border-gray-200 rounded px-5 py-3 shadow-xs">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-full bg-[#2D2D2D] text-[#F5F5F5] flex items-center justify-center text-sm font-bold shrink-0 overflow-hidden border border-gray-200 shadow-xs">
                @if($user->avatar_url)
                    <img id="overviewAvatarImg" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                @else
                    <span id="overviewAvatarInitials">{{ $user->initials }}</span>
                @endif
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-sm sm:text-base font-bold text-[#2D2D2D]">{{ $user->name }}</h2>
                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-[#2D2D2D] text-white uppercase tracking-wider">
                        {{ $user->role_title }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-0.5">{{ $user->email }} &middot; {{ $user->phone ?: 'No phone registered' }}</p>
            </div>
        </div>
        <div class="text-xs text-gray-500 font-medium shrink-0 flex sm:flex-col items-center sm:items-end gap-1 sm:gap-0.5">
            <span class="inline-flex items-center gap-1.5 text-emerald-700 font-semibold"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Active Account</span>
            <span class="text-[11px] text-gray-400">Member since {{ $user->created_at ? $user->created_at->format('M Y') : 'Oct 2026' }}</span>
        </div>
    </div>

    {{-- Main 3-Column Profile Grid --}}
    <form id="profileForm" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="avatar_cropped" id="avatarCroppedInput">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 items-stretch">
            
            {{-- Column 1: Personal Details & Photo --}}
            <div class="bg-white border border-gray-200 rounded overflow-hidden flex flex-col shadow-xs">
                <div class="px-4 py-3 bg-gray-50/70 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-[#2D2D2D] flex items-center gap-2">
                        <i class="fas fa-user text-gray-600"></i>
                        <span>Personal Information</span>
                    </h3>
                    <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">Identity</span>
                </div>
                <div class="p-4 space-y-3.5 text-xs flex-1 flex flex-col justify-between">
                    <div class="space-y-3">
                        {{-- Profile Photo Upload Compact Row --}}
                        <div>
                            <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1.5">Profile Photo</label>
                            <div class="flex items-center justify-between p-2.5 bg-gray-50 border border-gray-200 rounded">
                                <div class="flex items-center gap-2.5">
                                    <div class="relative w-9 h-9 rounded-full bg-[#2D2D2D] text-[#F5F5F5] flex items-center justify-center text-xs font-bold shrink-0 overflow-hidden border border-gray-300">
                                        <img id="avatarPreview" src="{{ $user->avatar_url ?: '' }}" alt="{{ $user->name }}" class="w-full h-full object-cover {{ $user->avatar_url ? '' : 'hidden' }}">
                                        <span id="avatarFallback" class="{{ $user->avatar_url ? 'hidden' : '' }}">{{ $user->initials }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <label for="avatarInput" class="cursor-pointer px-2.5 py-1 bg-white border border-gray-300 rounded font-semibold text-[11px] text-[#2D2D2D] hover:bg-gray-100 flex items-center gap-1.5 transition-colors">
                                            <i class="fas fa-camera text-gray-500 text-[10px]"></i>
                                            <span>Choose Photo</span>
                                        </label>
                                        <input type="file" name="avatar" id="avatarInput" accept="image/png,image/jpeg,image/webp,image/gif" class="hidden">

                                        @if($user->avatar)
                                        <label class="cursor-pointer px-2 py-1 bg-white border border-red-200 rounded font-semibold text-[11px] text-red-600 hover:bg-red-50 flex items-center gap-1 transition-colors">
                                            <input type="checkbox" name="remove_avatar" value="1" id="removeAvatarCheck" class="rounded text-red-600 focus:ring-0 w-3 h-3">
                                            <span>Remove</span>
                                        </label>
                                        @endif
                                    </div>
                                </div>
                                <span class="text-[10px] text-gray-400 hidden xl:inline">PNG, JPG (Max 2MB)</span>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Full Name *</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        </div>

                        <div>
                            <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Phone Number</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+91 98765 43210" class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        </div>

                        <div>
                            <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Official Email *</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-[10px] text-gray-400">Updates personal details</span>
                        <button type="submit" class="px-4 py-1.5 bg-[#2D2D2D] text-white rounded font-semibold text-xs hover:bg-[#1a1a1a] flex items-center gap-1.5 shadow-xs transition-colors cursor-pointer">
                            <i class="fas fa-save text-[11px]"></i> Save Profile
                        </button>
                    </div>
                </div>
            </div>

            {{-- Column 2: Role Details & Bio --}}
            <div class="bg-white border border-gray-200 rounded overflow-hidden flex flex-col shadow-xs">
                <div class="px-4 py-3 bg-gray-50/70 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-[#2D2D2D] flex items-center gap-2">
                        <i class="fas fa-id-badge text-gray-600"></i>
                        <span>Role & Bio</span>
                    </h3>
                    <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">Access</span>
                </div>
                <div class="p-4 space-y-3.5 text-xs flex-1 flex flex-col justify-between">
                    <div class="space-y-3">
                        <div>
                            <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Role / Job Title *</label>
                            <input type="text" name="role_title" value="{{ old('role_title', $user->role_title) }}" required class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        </div>

                        <div>
                            <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Bio / Notes</label>
                            <textarea name="bio" rows="4" placeholder="Brief personal description or notes..." class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D] resize-none">{{ old('bio', $user->bio) }}</textarea>
                        </div>

                        <div class="p-3 bg-gray-50 border border-gray-200 rounded space-y-1.5">
                            <span class="block text-[11px] font-semibold text-[#2D2D2D] flex items-center gap-1.5">
                                <i class="fas fa-shield-alt text-[#2D2D2D]"></i>
                                <span>Access Privileges</span>
                            </span>
                            <p class="text-[11px] text-gray-500 leading-relaxed">
                                @if($user->isSuperAdmin())
                                    Full unrestricted administrative authority across staff, payrolls, leave approvals, and system settings.
                                @else
                                    Assigned Role: <strong class="text-[#2D2D2D]">{{ $user->role ? $user->role->name : $user->role_title }}</strong> with modular permissions.
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-[10px] text-gray-400">Save changes</span>
                        <button type="submit" class="px-4 py-1.5 bg-[#2D2D2D] text-white rounded font-semibold text-xs hover:bg-[#1a1a1a] flex items-center gap-1.5 shadow-xs transition-colors cursor-pointer">
                            <i class="fas fa-save text-[11px]"></i> Save Profile
                        </button>
                    </div>
                </div>
            </div>

            {{-- Column 3: Security & Password --}}
            <div class="bg-white border border-gray-200 rounded overflow-hidden flex flex-col shadow-xs">
                <div class="px-4 py-3 bg-gray-50/70 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-[#2D2D2D] flex items-center gap-2">
                        <i class="fas fa-key text-gray-600"></i>
                        <span>Security & Password</span>
                    </h3>
                    <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">Credentials</span>
                </div>

                <div class="p-4 space-y-3.5 text-xs flex-1 flex flex-col justify-between">
                    <div class="space-y-3">
                        <div>
                            <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Current Password *</label>
                            <input type="password" name="current_password" form="passwordForm" required placeholder="Enter current password" class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        </div>

                        <div>
                            <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">New Password *</label>
                            <input type="password" name="password" form="passwordForm" required placeholder="At least 6 characters" class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        </div>

                        <div>
                            <label class="block font-semibold text-[11px] text-[#2D2D2D] mb-1">Confirm New Password *</label>
                            <input type="password" name="password_confirmation" form="passwordForm" required placeholder="Repeat new password" class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        </div>

                        <div class="p-2.5 bg-gray-50 border border-gray-200 rounded text-[11px] text-gray-500 flex items-start gap-2">
                            <i class="fas fa-info-circle text-gray-400 text-xs mt-0.5 shrink-0"></i>
                            <span>Default password was <code>admin123</code>. Minimum 6 characters required.</span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-[10px] text-gray-400 flex items-center gap-1"><i class="fas fa-lock text-[9px]"></i> Encrypted</span>
                        <button type="submit" form="passwordForm" class="px-4 py-1.5 bg-[#2D2D2D] text-white rounded font-semibold text-xs hover:bg-[#1a1a1a] flex items-center gap-1.5 shadow-xs transition-colors cursor-pointer">
                            <i class="fas fa-shield-alt text-[11px]"></i> Update Password
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>

    <form id="passwordForm" action="{{ route('profile.password') }}" method="POST" class="hidden">
        @csrf
        @method('PUT')
    </form>
</div>



    {{-- Interactive Profile Photo Crop Modal --}}
    <div id="cropModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded border border-gray-300 shadow-2xl max-w-lg w-full overflow-hidden flex flex-col max-h-[92vh]">
            {{-- Modal Header --}}
            <div class="px-5 py-3.5 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                <div class="flex items-center gap-2">
                    <i class="fas fa-crop-alt text-[#2D2D2D]"></i>
                    <h3 class="text-sm font-bold text-[#2D2D2D]">Adjust Profile Photo Area</h3>
                </div>
                <button type="button" id="closeCropModalBtn" class="text-gray-400 hover:text-[#2D2D2D] text-lg font-bold leading-none p-1 focus:outline-none">&times;</button>
            </div>

            {{-- Cropper Viewport Container --}}
            <div class="p-4 flex-1 overflow-hidden flex flex-col items-center bg-gray-900/5">
                <div class="w-full h-72 sm:h-80 flex items-center justify-center overflow-hidden rounded bg-black/20 border border-gray-200">
                    <img id="cropperImage" class="max-w-full block" alt="Profile crop preview">
                </div>
                <p class="text-[11px] text-gray-500 mt-2.5 flex items-center gap-1.5 text-center">
                    <i class="fas fa-info-circle text-xs text-gray-400"></i>
                    <span>Drag inside the circle to reposition &middot; Drag corners to adjust the zoom</span>
                </p>
            </div>

            {{-- Zoom & Rotate Controls Toolbar --}}
            <div class="px-4 py-2 bg-gray-50 border-t border-gray-200 flex items-center justify-center gap-2 flex-wrap text-xs">
                <button type="button" id="cropZoomIn" class="px-2.5 py-1.5 bg-white border border-gray-300 rounded text-[#2D2D2D] hover:bg-gray-100 flex items-center gap-1 font-semibold" title="Zoom In">
                    <i class="fas fa-search-plus text-xs"></i> <span>Zoom In</span>
                </button>
                <button type="button" id="cropZoomOut" class="px-2.5 py-1.5 bg-white border border-gray-300 rounded text-[#2D2D2D] hover:bg-gray-100 flex items-center gap-1 font-semibold" title="Zoom Out">
                    <i class="fas fa-search-minus text-xs"></i> <span>Zoom Out</span>
                </button>
                <button type="button" id="cropRotateLeft" class="px-2.5 py-1.5 bg-white border border-gray-300 rounded text-[#2D2D2D] hover:bg-gray-100 flex items-center gap-1 font-semibold" title="Rotate Left">
                    <i class="fas fa-undo text-xs"></i> <span>Rotate</span>
                </button>
                <button type="button" id="cropRotateRight" class="px-2.5 py-1.5 bg-white border border-gray-300 rounded text-[#2D2D2D] hover:bg-gray-100 flex items-center gap-1 font-semibold" title="Rotate Right">
                    <i class="fas fa-redo text-xs"></i>
                </button>
                <button type="button" id="cropReset" class="px-2.5 py-1.5 bg-white border border-gray-300 rounded text-[#2D2D2D] hover:bg-gray-100 flex items-center gap-1 font-semibold" title="Reset Crop">
                    <i class="fas fa-sync-alt text-xs"></i> <span>Reset</span>
                </button>
            </div>

            {{-- Modal Actions --}}
            <div class="px-5 py-3 border-t border-gray-200 flex items-center justify-end gap-2.5 bg-white">
                <button type="button" id="cancelCropBtn" class="px-4 py-2 border border-gray-300 rounded font-semibold text-xs text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="button" id="applyCropBtn" class="px-5 py-2 bg-[#2D2D2D] text-white rounded font-semibold text-xs hover:bg-[#1a1a1a] flex items-center gap-1.5 transition-colors">
                    <i class="fas fa-check"></i>
                    <span>Set Profile Photo</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const avatarInput = document.getElementById('avatarInput');
        const avatarCroppedInput = document.getElementById('avatarCroppedInput');
        const avatarPreview = document.getElementById('avatarPreview');
        const avatarFallback = document.getElementById('avatarFallback');
        const removeAvatarCheck = document.getElementById('removeAvatarCheck');
        const overviewAvatarImg = document.getElementById('overviewAvatarImg');
        const overviewAvatarInitials = document.getElementById('overviewAvatarInitials');

        // Modal & Cropper elements
        const cropModal = document.getElementById('cropModal');
        const cropperImage = document.getElementById('cropperImage');
        const closeCropModalBtn = document.getElementById('closeCropModalBtn');
        const cancelCropBtn = document.getElementById('cancelCropBtn');
        const applyCropBtn = document.getElementById('applyCropBtn');
        const cropZoomIn = document.getElementById('cropZoomIn');
        const cropZoomOut = document.getElementById('cropZoomOut');
        const cropRotateLeft = document.getElementById('cropRotateLeft');
        const cropRotateRight = document.getElementById('cropRotateRight');
        const cropReset = document.getElementById('cropReset');

        let cropper = null;

        const openModal = () => {
            if (cropModal) {
                cropModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
        };

        const closeModal = () => {
            if (cropModal) {
                cropModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
        };

        // When user selects a file
        if (avatarInput) {
            avatarInput.addEventListener('change', function(e) {
                const file = e.target.files && e.target.files[0];
                if (!file) return;

                if (!file.type.match(/^image\//)) {
                    alert('Please select a valid image file (PNG, JPG, WEBP, or GIF).');
                    avatarInput.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(evt) {
                    cropperImage.src = evt.target.result;
                    openModal();

                    if (cropper) {
                        cropper.destroy();
                        cropper = null;
                    }

                    // Initialize Cropper once image is set
                    setTimeout(() => {
                        cropper = new Cropper(cropperImage, {
                            aspectRatio: 1,
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 0.85,
                            restore: false,
                            guides: true,
                            center: true,
                            highlight: false,
                            cropBoxMovable: true,
                            cropBoxResizable: true,
                            toggleDragModeOnDblclick: false,
                            background: false,
                            minCropBoxWidth: 60,
                            minCropBoxHeight: 60
                        });
                    }, 100);
                };
                reader.readAsDataURL(file);
            });
        }

        // Toolbar buttons
        if (cropZoomIn) {
            cropZoomIn.addEventListener('click', () => { if (cropper) cropper.zoom(0.1); });
        }
        if (cropZoomOut) {
            cropZoomOut.addEventListener('click', () => { if (cropper) cropper.zoom(-0.1); });
        }
        if (cropRotateLeft) {
            cropRotateLeft.addEventListener('click', () => { if (cropper) cropper.rotate(-90); });
        }
        if (cropRotateRight) {
            cropRotateRight.addEventListener('click', () => { if (cropper) cropper.rotate(90); });
        }
        if (cropReset) {
            cropReset.addEventListener('click', () => { if (cropper) cropper.reset(); });
        }

        // Cancel / Close
        if (closeCropModalBtn) {
            closeCropModalBtn.addEventListener('click', () => {
                closeModal();
                if (avatarInput) avatarInput.value = '';
            });
        }
        if (cancelCropBtn) {
            cancelCropBtn.addEventListener('click', () => {
                closeModal();
                if (avatarInput) avatarInput.value = '';
            });
        }

        // Apply Crop
        if (applyCropBtn) {
            applyCropBtn.addEventListener('click', () => {
                if (!cropper) return;

                const canvas = cropper.getCroppedCanvas({
                    width: 400,
                    height: 400,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high'
                });

                if (!canvas) return;

                const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.92);

                // Set hidden input value for form submission
                if (avatarCroppedInput) {
                    avatarCroppedInput.value = croppedDataUrl;
                }

                // Update live previews
                if (avatarPreview) {
                    avatarPreview.src = croppedDataUrl;
                    avatarPreview.classList.remove('hidden');
                }
                if (avatarFallback) {
                    avatarFallback.classList.add('hidden');
                }
                if (overviewAvatarImg) {
                    overviewAvatarImg.src = croppedDataUrl;
                }
                if (removeAvatarCheck) {
                    removeAvatarCheck.checked = false;
                }

                closeModal();
            });
        }

        // Remove avatar checkbox
        if (removeAvatarCheck) {
            removeAvatarCheck.addEventListener('change', function() {
                if (this.checked) {
                    if (avatarPreview) avatarPreview.classList.add('hidden');
                    if (avatarFallback) avatarFallback.classList.remove('hidden');
                    if (avatarInput) avatarInput.value = '';
                    if (avatarCroppedInput) avatarCroppedInput.value = '';
                }
            });
        }
    });
</script>
@endpush


