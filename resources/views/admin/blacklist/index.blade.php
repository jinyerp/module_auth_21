@extends('jiny-auth::layouts.admin')

@section('title', '블랙리스트 관리')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3">블랙리스트 관리</h1>
                <div>
                    <a href="{{ route('admin.auth.blacklist.whitelist') }}" class="btn btn-outline-info">
                        <i class="fas fa-shield-alt me-2"></i>화이트리스트
                    </a>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="fas fa-plus me-2"></i>항목 추가
                    </button>
                </div>
            </div>

            <!-- 통계 카드 -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $statistics['total'] }}</h4>
                            <small class="text-muted">전체 항목</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $statistics['active'] }}</h4>
                            <small class="text-muted">활성 항목</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $statistics['email'] }}</h4>
                            <small class="text-muted">이메일</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $statistics['ip'] }}</h4>
                            <small class="text-muted">IP 주소</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $statistics['domain'] }}</h4>
                            <small class="text-muted">도메인</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <h4 class="mb-0 text-danger">{{ $statistics['recent_blocks'] }}</h4>
                            <small class="text-muted">24시간 차단</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 검색 및 필터 -->
            <div class="card mb-3">
                <div class="card-body">
                    <form action="{{ route('admin.auth.blacklist') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="검색..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="type">
                                <option value="">모든 타입</option>
                                <option value="email" {{ request('type') == 'email' ? 'selected' : '' }}>이메일</option>
                                <option value="ip" {{ request('type') == 'ip' ? 'selected' : '' }}>IP</option>
                                <option value="domain" {{ request('type') == 'domain' ? 'selected' : '' }}>도메인</option>
                                <option value="phone" {{ request('type') == 'phone' ? 'selected' : '' }}>전화번호</option>
                                <option value="keyword" {{ request('type') == 'keyword' ? 'selected' : '' }}>키워드</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="is_active">
                                <option value="">모든 상태</option>
                                <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>활성</option>
                                <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>비활성</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="expired">
                                <option value="">만료 상태</option>
                                <option value="no" {{ request('expired') == 'no' ? 'selected' : '' }}>유효</option>
                                <option value="yes" {{ request('expired') == 'yes' ? 'selected' : '' }}>만료됨</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-warning w-100" onclick="bulkAdd()">
                                <i class="fas fa-file-import me-1"></i>일괄 추가
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 블랙리스트 목록 -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th>타입</th>
                                    <th>값</th>
                                    <th>이유</th>
                                    <th>상태</th>
                                    <th>매칭</th>
                                    <th>만료일</th>
                                    <th>등록일</th>
                                    <th>작업</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($blacklists as $blacklist)
                                    <tr class="{{ !$blacklist->is_active ? 'table-secondary' : '' }}">
                                        <td>
                                            <input type="checkbox" class="item-checkbox" value="{{ $blacklist->id }}">
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $blacklist->type == 'email' ? 'primary' : 
                                                ($blacklist->type == 'ip' ? 'info' : 
                                                ($blacklist->type == 'domain' ? 'success' : 
                                                ($blacklist->type == 'phone' ? 'warning' : 'secondary'))) 
                                            }}">
                                                {{ strtoupper($blacklist->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <code>{{ $blacklist->value }}</code>
                                        </td>
                                        <td>
                                            {{ $blacklist->reason }}
                                            @if($blacklist->description)
                                                <i class="fas fa-info-circle text-muted" 
                                                   data-bs-toggle="tooltip" 
                                                   title="{{ $blacklist->description }}"></i>
                                            @endif
                                        </td>
                                        <td>
                                            @if($blacklist->is_active)
                                                <span class="badge bg-success">활성</span>
                                            @else
                                                <span class="badge bg-secondary">비활성</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($blacklist->match_count > 0)
                                                <span class="badge bg-danger">{{ $blacklist->match_count }}</span>
                                                @if($blacklist->last_matched_at)
                                                    <br><small class="text-muted">
                                                        {{ \Carbon\Carbon::parse($blacklist->last_matched_at)->diffForHumans() }}
                                                    </small>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($blacklist->expires_at)
                                                @if(\Carbon\Carbon::parse($blacklist->expires_at)->isPast())
                                                    <span class="text-danger">만료됨</span>
                                                @else
                                                    <small>{{ \Carbon\Carbon::parse($blacklist->expires_at)->format('Y-m-d') }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">무제한</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ \Carbon\Carbon::parse($blacklist->created_at)->format('Y-m-d') }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button class="btn btn-outline-warning" 
                                                        onclick="editItem({{ $blacklist->id }})" title="수정">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" 
                                                        onclick="deleteItem({{ $blacklist->id }})" title="삭제">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="fas fa-ban fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">블랙리스트가 비어 있습니다.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- 페이지네이션 -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button class="btn btn-danger" onclick="bulkRemove()">
                            <i class="fas fa-trash me-2"></i>선택 삭제
                        </button>
                        {{ $blacklists->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 추가 모달 -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">블랙리스트 추가</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addForm">
                    <div class="mb-3">
                        <label for="type" class="form-label">타입</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="email">이메일</option>
                            <option value="ip">IP 주소</option>
                            <option value="domain">도메인</option>
                            <option value="phone">전화번호</option>
                            <option value="keyword">키워드</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="value" class="form-label">값</label>
                        <input type="text" class="form-control" id="value" name="value" required>
                        <small class="text-muted">
                            IP: 단일 IP, CIDR(192.168.1.0/24), 범위(192.168.1.1-192.168.1.255)
                        </small>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">차단 이유</label>
                        <input type="text" class="form-control" id="reason" name="reason" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">설명 (선택)</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="expires_at" class="form-label">만료일 (선택)</label>
                        <input type="datetime-local" class="form-control" id="expires_at" name="expires_at">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-primary" onclick="saveItem()">추가</button>
            </div>
        </div>
    </div>
</div>

<!-- 일괄 추가 모달 -->
<div class="modal fade" id="bulkAddModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">일괄 블랙리스트 추가</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulkAddForm">
                    <div class="mb-3">
                        <label for="bulk_type" class="form-label">타입</label>
                        <select class="form-select" id="bulk_type" name="type" required>
                            <option value="email">이메일</option>
                            <option value="ip">IP 주소</option>
                            <option value="domain">도메인</option>
                            <option value="phone">전화번호</option>
                            <option value="keyword">키워드</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="bulk_values" class="form-label">값 (한 줄에 하나씩)</label>
                        <textarea class="form-control" id="bulk_values" name="values" rows="10" required 
                                  placeholder="example1@email.com&#10;example2@email.com&#10;..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="bulk_reason" class="form-label">차단 이유</label>
                        <input type="text" class="form-control" id="bulk_reason" name="reason" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-primary" onclick="saveBulk()">일괄 추가</button>
            </div>
        </div>
    </div>
</div>

<script>
// 전체 선택
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
});

// 단일 항목 추가
function saveItem() {
    const form = document.getElementById('addForm');
    const formData = new FormData(form);
    const type = formData.get('type');
    
    fetch(`/admin/auth/blacklist/${type}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || '오류가 발생했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('처리 중 오류가 발생했습니다.');
    });
}

// 일괄 추가
function bulkAdd() {
    new bootstrap.Modal(document.getElementById('bulkAddModal')).show();
}

function saveBulk() {
    const form = document.getElementById('bulkAddForm');
    const formData = new FormData(form);
    
    fetch('/admin/auth/blacklist/bulk-add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            if (data.errors && data.errors.length > 0) {
                console.log('Errors:', data.errors);
            }
            location.reload();
        } else {
            alert(data.message || '오류가 발생했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('처리 중 오류가 발생했습니다.');
    });
}

// 일괄 삭제
function bulkRemove() {
    const selected = Array.from(document.querySelectorAll('.item-checkbox:checked'))
        .map(cb => cb.value);
    
    if (selected.length === 0) {
        alert('삭제할 항목을 선택하세요.');
        return;
    }
    
    if (!confirm(`선택한 ${selected.length}개 항목을 삭제하시겠습니까?`)) return;
    
    fetch('/admin/auth/blacklist/bulk-remove', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ ids: selected })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || '오류가 발생했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('처리 중 오류가 발생했습니다.');
    });
}

// 항목 삭제
function deleteItem(id) {
    if (!confirm('이 항목을 삭제하시겠습니까?')) return;
    
    fetch(`/admin/auth/blacklist/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || '오류가 발생했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('처리 중 오류가 발생했습니다.');
    });
}

// 툴팁 초기화
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
})
</script>
@endsection