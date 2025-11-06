@extends('layouts.index')

@section('content')
<div class="page-content-wrapper border">
    <!-- Title -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-2 mb-sm-0 text-end">الإعدادات</h1>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Settings Form -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary"><i class="fas fa-cog me-2"></i>إعدادات البريد الإلكتروني</h5>
                            
                            <div class="mb-3">
                                <label for="support_email" class="form-label">البريد الإلكتروني للدعم</label>
                                <input type="email" class="form-control @error('support_email') is-invalid @enderror" id="support_email" name="support_email" 
                                       value="{{ old('support_email', $supportEmail) }}" required 
                                       placeholder="support@example.com">
                                @error('support_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">سيتم إرسال إشعارات الحصص إلى هذا البريد الإلكتروني</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>حفظ الإعدادات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Support Users Management -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>إدارة حسابات الدعم</h5>
                    <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addSupportUserModal">
                        <i class="fas fa-plus me-1"></i>إضافة حساب دعم
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>اسم المستخدم</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>تاريخ الإنشاء</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($supportUsers as $user)
                                    <tr>
                                        <td>{{ $user->user_name }}</td>
                                        <td>{{ $user->email ?? 'غير محدد' }}</td>
                                        <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning" onclick="editSupportUser({{ $user->id }}, '{{ $user->user_name }}', '{{ $user->email ?? '' }}')">
                                                <i class="fas fa-edit"></i> تعديل
                                            </button>
                                            <a href="{{ route('admin.settings.deleteSupportUser', $user->id) }}" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا الحساب؟')">
                                                <i class="fas fa-trash"></i> حذف
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">لا توجد حسابات دعم</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Support User Modal -->
<div class="modal fade" id="addSupportUserModal" tabindex="-1" aria-labelledby="addSupportUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addSupportUserModalLabel">إضافة حساب دعم</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.settings.storeSupportUser') }}" method="POST" id="addSupportUserForm">
                @csrf
                <div class="modal-body">
                    @if($errors->has('user_name') || $errors->has('email') || $errors->has('password'))
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @if($errors->has('user_name'))
                                    <li>{{ $errors->first('user_name') }}</li>
                                @endif
                                @if($errors->has('email'))
                                    <li>{{ $errors->first('email') }}</li>
                                @endif
                                @if($errors->has('password'))
                                    <li>{{ $errors->first('password') }}</li>
                                @endif
                            </ul>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <label for="user_name" class="form-label">اسم المستخدم</label>
                        <input type="text" class="form-control @error('user_name') is-invalid @enderror" id="user_name" name="user_name" value="{{ old('user_name') }}" required>
                        @error('user_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">البريد الإلكتروني</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">كلمة المرور</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required minlength="6">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">يجب أن تكون كلمة المرور 6 أحرف على الأقل</small>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">تأكيد كلمة المرور</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">إضافة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Support User Modal -->
<div class="modal fade" id="editSupportUserModal" tabindex="-1" aria-labelledby="editSupportUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editSupportUserModalLabel">تعديل حساب دعم</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSupportUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    @if($errors->has('user_name') || $errors->has('email') || $errors->has('password'))
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @if($errors->has('user_name'))
                                    <li>{{ $errors->first('user_name') }}</li>
                                @endif
                                @if($errors->has('email'))
                                    <li>{{ $errors->first('email') }}</li>
                                @endif
                                @if($errors->has('password'))
                                    <li>{{ $errors->first('password') }}</li>
                                @endif
                            </ul>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <label for="edit_user_name" class="form-label">اسم المستخدم</label>
                        <input type="text" class="form-control @error('user_name') is-invalid @enderror" id="edit_user_name" name="user_name" required>
                        @error('user_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">البريد الإلكتروني</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="edit_email" name="email" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">كلمة المرور الجديدة (اتركها فارغة إذا لم تريد التغيير)</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="edit_password" name="password" minlength="6">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">اتركها فارغة إذا لم تريد تغيير كلمة المرور</small>
                    </div>
                    <div class="mb-3">
                        <label for="edit_password_confirmation" class="form-label">تأكيد كلمة المرور الجديدة</label>
                        <input type="password" class="form-control" id="edit_password_confirmation" name="password_confirmation">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning">تحديث</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editSupportUser(id, userName, email) {
    document.getElementById('editSupportUserForm').action = '{{ route("admin.settings.updateSupportUser", "") }}/' + id;
    document.getElementById('edit_user_name').value = userName;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_password').value = '';
    document.getElementById('edit_password_confirmation').value = '';
    new bootstrap.Modal(document.getElementById('editSupportUserModal')).show();
}

// Auto-open modal if there are errors or if session says to open it
@if(session('open_modal') || ($errors->has('user_name') || $errors->has('email') || $errors->has('password')))
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('open_modal') == 'addSupportUserModal' || ($errors->has('user_name') || $errors->has('email') || $errors->has('password')))
            var modal = new bootstrap.Modal(document.getElementById('addSupportUserModal'));
            modal.show();
        @endif
    });
@endif
</script>
@endsection

