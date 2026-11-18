@extends('layouts.employee-app')

@section('title', 'គណនីរបស់ខ្ញុំ')

@section('content')

    @if ($employee)
        <div class="app-card profile-hero mb-3">
            <div class="card-body">
                <div class="profile-hero-top">
                    @if ($employee->photo)
                        <img src="{{ asset('files/' . $employee->photo) }}" alt="រូបបុគ្គលិក" class="profile-avatar">
                    @else
                        <div class="profile-avatar profile-avatar-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif

                    <div class="profile-hero-info">
                        <div class="profile-name">{{ $employee->full_name }}</div>
                        <div class="profile-code">{{ $employee->employee_code }}</div>
                    </div>

                    @if ($employee->status === 'active')
                        <span class="status-pill status-pill-done"><i class="fas fa-check me-1"></i>កំពុងធ្វើការ</span>
                    @else
                        <span class="status-pill status-pill-idle"><i class="fas fa-pause me-1"></i>ផ្អាក</span>
                    @endif
                </div>

                <div class="profile-hero-divider"></div>

                <div class="profile-since">
                    <i class="fas fa-calendar-days me-1"></i>ចូលរួមតាំងពី {{ $employee->created_at->format('m/Y') }}
                </div>
            </div>
        </div>

        <details class="app-card profile-edit mb-3" @if($errors->has('phone') || $errors->has('photo') || $errors->has('email')) open @endif>
            <summary>កែព័ត៌មានគណនី <span aria-hidden="true">✎</span></summary>
            <form method="POST" action="{{ route('employee.profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <label for="profile-photo">រូបគណនី</label>
                <div class="profile-photo-editor">
                    <img id="profile-photo-preview" src="{{ $employee->photo ? asset('files/' . $employee->photo) : asset('images/pizzahappyfamily-logo.jpg') }}" width="64" height="64" alt="រូបគណនីដែលបានជ្រើស">
                    <input id="profile-photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp" aria-describedby="profile-photo-help">
                </div>
                <small id="profile-photo-help">JPG, PNG, WebP · រហូតដល់ 10 MB</small>
                @error('photo')<p class="profile-field-error" role="alert">{{ $message }}</p>@enderror
                <label for="profile-phone">លេខទូរស័ព្ទ</label>
                <input id="profile-phone" type="tel" name="phone" value="{{ old('phone', $employee->phone) }}" maxlength="30" autocomplete="tel" placeholder="012 345 678" @error('phone') aria-invalid="true" @enderror>
                @error('phone')<p class="profile-field-error" role="alert">{{ $message }}</p>@enderror
                <label for="profile-email">អ៊ីមែល</label>
                <input id="profile-email" type="email" name="email" value="{{ old('email', $employee->email ?: $user->email) }}" required maxlength="255" autocomplete="email" autocapitalize="none" spellcheck="false" placeholder="name@example.com" @error('email') aria-invalid="true" @enderror>
                @error('email')<p class="profile-field-error" role="alert">{{ $message }}</p>@enderror
                <button type="submit">រក្សាទុក</button>
            </form>
        </details>

        <div class="section-title" style="margin-top:0;">ទំនាក់ទំនង</div>
        <div class="app-card mb-3">
            <div class="card-body p-0">
                <div class="profile-row">
                    <i class="fas fa-envelope profile-row-icon"></i>
                    <div>
                        <div class="info-label">អ៊ីមែល</div>
                        <div class="info-value">{{ $employee->email ?? 'មិនទាន់កំណត់' }}</div>
                    </div>
                </div>
                <div class="profile-row profile-row-border-top">
                    <i class="fas fa-phone profile-row-icon"></i>
                    <div>
                        <div class="info-label">លេខទូរស័ព្ទ</div>
                        <div class="info-value">{{ $employee->phone ?? 'មិនទាន់កំណត់' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-title">ព័ត៌មានការងារ</div>
        <div class="app-card mb-3">
            <div class="card-body p-0">
                <div class="profile-row">
                    <i class="fas fa-building profile-row-icon"></i>
                    <div>
                        <div class="info-label">ផ្នែក</div>
                        <div class="info-value">{{ $employee->department->name ?? 'មិនទាន់កំណត់' }}</div>
                    </div>
                </div>
                <div class="profile-row profile-row-border-top">
                    <i class="fas fa-briefcase profile-row-icon"></i>
                    <div>
                        <div class="info-label">តួនាទី</div>
                        <div class="info-value">{{ $employee->position ?? 'មិនទាន់កំណត់' }}</div>
                    </div>
                </div>
                <div class="profile-row profile-row-border-top">
                    <i class="fas fa-user-clock profile-row-icon"></i>
                    <div>
                        <div class="info-label">វេនការងារ</div>
                        <div class="info-value">{{ $employee->attendance_mode === 'flexible' ? 'ម៉ោងបត់បែន' : ($employee->shift->name ?? 'មិនទាន់កំណត់') }}</div>
                    </div>
                </div>
                @if ($employee->attendance_mode === 'flexible')
                    <div class="profile-row profile-row-border-top">
                        <i class="fas fa-clock profile-row-icon" aria-hidden="true"></i>
                        <div><div class="info-label">ម៉ោងធ្វើការ</div><div class="info-value">មិនកំណត់ម៉ោងថេរ</div></div>
                    </div>
                @elseif ($employee->shift)
                    <div class="profile-row profile-row-border-top">
                        <i class="fas fa-clock profile-row-icon"></i>
                        <div>
                            <div class="info-label">ម៉ោងធ្វើការ</div>
                            <div class="info-value">
                                {{ \Carbon\Carbon::parse($employee->shift->start_time)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($employee->shift->end_time)->format('H:i') }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="app-card">
            <div class="card-body text-center text-muted py-4">
                <i class="fas fa-triangle-exclamation mb-2" style="font-size:1.5rem;"></i>
                <div>រកមិនឃើញព័ត៌មានបុគ្គលិក។</div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf
        <button type="submit" class="btn-logout"><i class="fas fa-sign-out-alt" aria-hidden="true"></i> ចាកចេញពីគណនី</button>
    </form>
@endsection

@push('scripts')
<script>
    (() => {
        const input = document.getElementById('profile-photo');
        const preview = document.getElementById('profile-photo-preview');
        if (!input || !preview) return;
        let previewUrl;
        input.addEventListener('change', () => {
            const file = input.files[0];
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            if (file && ['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                previewUrl = URL.createObjectURL(file);
                preview.src = previewUrl;
            }
        });
        window.addEventListener('pagehide', () => { if (previewUrl) URL.revokeObjectURL(previewUrl); });
    })();
</script>
@endpush

@push('styles')
<style>
    .profile-edit summary{display:flex;justify-content:space-between;align-items:center;padding:16px 18px;font-size:14px;font-weight:600;cursor:pointer;color:#a63c29;list-style:none}
    .profile-edit summary::-webkit-details-marker{display:none}
    .profile-edit form{padding:0 18px 18px;border-top:1px solid #edf0f5}
    .profile-edit label{display:block;font-size:13px;font-weight:600;margin:18px 0 9px}
    .profile-photo-editor{display:flex;align-items:center;gap:14px;min-width:0}
    .profile-photo-editor img{width:64px;height:64px;object-fit:cover;border-radius:50%;flex-shrink:0}
    .profile-photo-editor input{min-width:0;max-width:100%;font-size:12px}
    .profile-edit small{display:block;font-size:11px;color:#6b7b91;margin-top:9px}
    .profile-edit input[type=tel],.profile-edit input[type=email]{width:100%;min-height:48px;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;font-size:16px;color:#24364e;background:#fff}
    .profile-edit button{width:100%;min-height:48px;border:0;border-radius:10px;background:#b84129;color:#fff;font-size:14px;font-weight:600;margin-top:18px}
    .profile-edit :is(input,button,summary):focus-visible{outline:3px solid #b84129;outline-offset:3px}
    .profile-field-error{font-size:12px;color:#b91c1c;line-height:1.7;margin:8px 0 0}
    .profile-hero { background: linear-gradient(160deg, #ffffff 0%, #fef7f7 100%); }

    .profile-hero .card-body {
        padding: 16px 18px;
    }

    .profile-hero-top {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .profile-hero-info {
        flex: 1;
        min-width: 0;
    }

    .profile-avatar {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .profile-avatar-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: #94a3b8;
        font-size: 1.4rem;
    }

    .profile-name {
        font-size: 1rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .profile-code {
        font-size: 12px;
        color: var(--text-soft);
        font-weight: 600;
        line-height: 1.3;
    }

    .profile-hero-divider {
        height: 1px;
        background: var(--border-soft);
        margin: 14px 0 10px;
    }

    .profile-since {
        font-size: 12px;
        color: var(--text-soft);
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.4;
        flex-shrink: 0;
    }

    .status-pill-done { background: #dcfce7; color: #166534; }
    .status-pill-idle { background: #f1f5f9; color: #64748b; }

    .profile-row {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
    }

    .profile-row-border-top { border-top: 1px solid var(--border-soft); }

    .profile-row-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #f1f5f9;
        color: var(--brand-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }

    .info-label {
        font-size: 11.5px;
        color: var(--text-soft);
        font-weight: 600;
        margin-bottom: 3px;
    }

    .info-value { font-weight: 700; font-size: 14px; }

    .btn-logout {
        width: 100%;
        border: 1px solid #fecaca;
        background: #fff;
        color: var(--brand-primary);
        font-weight: 700;
        font-size: 14px;
        padding: 12px;
        border-radius: var(--radius-md);
        transition: background 0.15s ease;
    }

    .btn-logout:hover, .btn-logout:active {
        background: #fef2f2;
    }
</style>
@endpush
