<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AdminSecurityIncidentController extends Controller
{
    /**
     * 보안 사고 목록
     * GET /admin/auth/security-incident
     */
    public function index(Request $request)
    {
        $query = DB::table('auth_security_incidents')
            ->leftJoin('users as reporter', 'auth_security_incidents.reported_by', '=', 'reporter.id')
            ->leftJoin('users as resolver', 'auth_security_incidents.resolved_by', '=', 'resolver.id')
            ->select(
                'auth_security_incidents.*',
                'reporter.name as reporter_name',
                'resolver.name as resolver_name'
            );
        
        // 상태 필터
        if ($request->has('status')) {
            $query->where('auth_security_incidents.status', $request->status);
        }
        
        // 심각도 필터
        if ($request->has('severity')) {
            $query->where('auth_security_incidents.severity', $request->severity);
        }
        
        // 유형 필터
        if ($request->has('type')) {
            $query->where('auth_security_incidents.type', $request->type);
        }
        
        $incidents = $query->orderBy('auth_security_incidents.created_at', 'desc')
            ->paginate(20);
        
        // 통계
        $stats = [
            'total' => DB::table('auth_security_incidents')->count(),
            'open' => DB::table('auth_security_incidents')->where('status', 'open')->count(),
            'investigating' => DB::table('auth_security_incidents')->where('status', 'investigating')->count(),
            'resolved' => DB::table('auth_security_incidents')->where('status', 'resolved')->count(),
            'by_severity' => DB::table('auth_security_incidents')
                ->select('severity', DB::raw('COUNT(*) as count'))
                ->groupBy('severity')
                ->get()
        ];
        
        return view('jiny-auth::admin.security-incident.index', compact('incidents', 'stats'));
    }
    
    /**
     * 보안 사고 등록
     * POST /admin/auth/security-incident
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:breach,attack,vulnerability,suspicious,other',
            'severity' => 'required|in:low,medium,high,critical',
            'description' => 'required|string',
            'affected_users' => 'nullable|array',
            'affected_users.*' => 'exists:users,id',
            'affected_systems' => 'nullable|array',
            'immediate_action' => 'nullable|string'
        ]);
        
        DB::beginTransaction();
        try {
            // 사고 등록
            $incidentId = DB::table('auth_security_incidents')->insertGetId([
                'title' => $request->title,
                'type' => $request->type,
                'severity' => $request->severity,
                'status' => 'open',
                'description' => $request->description,
                'affected_systems' => json_encode($request->affected_systems ?? []),
                'reported_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // 영향받은 사용자 기록
            if ($request->has('affected_users')) {
                foreach ($request->affected_users as $userId) {
                    DB::table('auth_incident_affected_users')->insert([
                        'incident_id' => $incidentId,
                        'user_id' => $userId,
                        'created_at' => now()
                    ]);
                }
            }
            
            // 즉각 조치사항이 있는 경우
            if ($request->immediate_action) {
                DB::table('auth_incident_actions')->insert([
                    'incident_id' => $incidentId,
                    'action' => $request->immediate_action,
                    'action_type' => 'immediate',
                    'performed_by' => auth()->id(),
                    'created_at' => now()
                ]);
            }
            
            // 심각도가 high 이상인 경우 자동 대응
            if (in_array($request->severity, ['high', 'critical'])) {
                $this->performAutomaticResponse($incidentId, $request->all());
            }
            
            // 관리자들에게 알림
            $this->notifyAdmins($incidentId, $request->all());
            
            DB::commit();
            
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'incident_id' => $incidentId,
                    'type' => $request->type,
                    'severity' => $request->severity
                ])
                ->log('보안 사고 등록');
            
            return response()->json([
                'success' => true,
                'message' => '보안 사고가 등록되었습니다.',
                'incident_id' => $incidentId
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => '사고 등록 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 사고 상세 조회
     * GET /admin/auth/security-incident/{id}
     */
    public function show($id)
    {
        $incident = DB::table('auth_security_incidents')
            ->leftJoin('users as reporter', 'auth_security_incidents.reported_by', '=', 'reporter.id')
            ->leftJoin('users as resolver', 'auth_security_incidents.resolved_by', '=', 'resolver.id')
            ->select(
                'auth_security_incidents.*',
                'reporter.name as reporter_name',
                'reporter.email as reporter_email',
                'resolver.name as resolver_name',
                'resolver.email as resolver_email'
            )
            ->where('auth_security_incidents.id', $id)
            ->first();
        
        if (!$incident) {
            return response()->json([
                'success' => false,
                'message' => '사고 정보를 찾을 수 없습니다.'
            ], 404);
        }
        
        // 영향받은 사용자
        $affectedUsers = DB::table('auth_incident_affected_users')
            ->join('users', 'auth_incident_affected_users.user_id', '=', 'users.id')
            ->where('auth_incident_affected_users.incident_id', $id)
            ->select('users.id', 'users.name', 'users.email')
            ->get();
        
        // 조치 이력
        $actions = DB::table('auth_incident_actions')
            ->leftJoin('users', 'auth_incident_actions.performed_by', '=', 'users.id')
            ->where('auth_incident_actions.incident_id', $id)
            ->select(
                'auth_incident_actions.*',
                'users.name as performed_by_name'
            )
            ->orderBy('auth_incident_actions.created_at', 'desc')
            ->get();
        
        // 타임라인
        $timeline = DB::table('auth_incident_timeline')
            ->where('incident_id', $id)
            ->orderBy('occurred_at', 'asc')
            ->get();
        
        return view('jiny-auth::admin.security-incident.show', compact(
            'incident',
            'affectedUsers',
            'actions',
            'timeline'
        ));
    }
    
    /**
     * 사고 업데이트
     * PUT /admin/auth/security-incident/{id}
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:open,investigating,contained,resolved,closed',
            'severity' => 'required|in:low,medium,high,critical',
            'update_note' => 'required|string'
        ]);
        
        $incident = DB::table('auth_security_incidents')->find($id);
        
        if (!$incident) {
            return response()->json([
                'success' => false,
                'message' => '사고 정보를 찾을 수 없습니다.'
            ], 404);
        }
        
        DB::beginTransaction();
        try {
            // 사고 정보 업데이트
            DB::table('auth_security_incidents')
                ->where('id', $id)
                ->update([
                    'status' => $request->status,
                    'severity' => $request->severity,
                    'updated_at' => now()
                ]);
            
            // 업데이트 노트 추가
            DB::table('auth_incident_actions')->insert([
                'incident_id' => $id,
                'action' => $request->update_note,
                'action_type' => 'update',
                'performed_by' => auth()->id(),
                'created_at' => now()
            ]);
            
            // 타임라인에 추가
            DB::table('auth_incident_timeline')->insert([
                'incident_id' => $id,
                'event' => "상태 변경: {$incident->status} → {$request->status}",
                'description' => $request->update_note,
                'performed_by' => auth()->id(),
                'occurred_at' => now()
            ]);
            
            DB::commit();
            
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'incident_id' => $id,
                    'new_status' => $request->status,
                    'new_severity' => $request->severity
                ])
                ->log('보안 사고 업데이트');
            
            return response()->json([
                'success' => true,
                'message' => '사고 정보가 업데이트되었습니다.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => '업데이트 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 사고 해결
     * POST /admin/auth/security-incident/{id}/resolve
     */
    public function resolve(Request $request, $id)
    {
        $request->validate([
            'resolution' => 'required|string',
            'root_cause' => 'required|string',
            'preventive_measures' => 'required|string',
            'lessons_learned' => 'nullable|string'
        ]);
        
        $incident = DB::table('auth_security_incidents')->find($id);
        
        if (!$incident) {
            return response()->json([
                'success' => false,
                'message' => '사고 정보를 찾을 수 없습니다.'
            ], 404);
        }
        
        DB::beginTransaction();
        try {
            // 사고 해결 처리
            DB::table('auth_security_incidents')
                ->where('id', $id)
                ->update([
                    'status' => 'resolved',
                    'resolution' => $request->resolution,
                    'root_cause' => $request->root_cause,
                    'preventive_measures' => $request->preventive_measures,
                    'lessons_learned' => $request->lessons_learned,
                    'resolved_by' => auth()->id(),
                    'resolved_at' => now(),
                    'updated_at' => now()
                ]);
            
            // 해결 조치 기록
            DB::table('auth_incident_actions')->insert([
                'incident_id' => $id,
                'action' => $request->resolution,
                'action_type' => 'resolution',
                'performed_by' => auth()->id(),
                'created_at' => now()
            ]);
            
            // 타임라인에 추가
            DB::table('auth_incident_timeline')->insert([
                'incident_id' => $id,
                'event' => '사고 해결',
                'description' => $request->resolution,
                'performed_by' => auth()->id(),
                'occurred_at' => now()
            ]);
            
            // 영향받은 사용자들에게 알림
            $this->notifyAffectedUsers($id, $request->resolution);
            
            // 사고 보고서 생성
            $this->generateIncidentReport($id);
            
            DB::commit();
            
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'incident_id' => $id,
                    'resolution' => $request->resolution
                ])
                ->log('보안 사고 해결');
            
            return response()->json([
                'success' => true,
                'message' => '사고가 해결 처리되었습니다.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => '해결 처리 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 조치 추가
     * POST /admin/auth/security-incident/{id}/action
     */
    public function addAction(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|string',
            'action_type' => 'required|in:investigation,mitigation,containment,recovery,other'
        ]);
        
        $incident = DB::table('auth_security_incidents')->find($id);
        
        if (!$incident) {
            return response()->json([
                'success' => false,
                'message' => '사고 정보를 찾을 수 없습니다.'
            ], 404);
        }
        
        // 조치 추가
        $actionId = DB::table('auth_incident_actions')->insertGetId([
            'incident_id' => $id,
            'action' => $request->action,
            'action_type' => $request->action_type,
            'performed_by' => auth()->id(),
            'created_at' => now()
        ]);
        
        // 타임라인에 추가
        DB::table('auth_incident_timeline')->insert([
            'incident_id' => $id,
            'event' => "조치 추가 ({$request->action_type})",
            'description' => $request->action,
            'performed_by' => auth()->id(),
            'occurred_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => '조치가 추가되었습니다.',
            'action_id' => $actionId
        ]);
    }
    
    /**
     * 자동 대응 수행
     */
    private function performAutomaticResponse($incidentId, $data)
    {
        $actions = [];
        
        switch ($data['type']) {
            case 'breach':
                // 데이터 유출 대응
                $actions[] = '모든 세션 종료';
                $actions[] = '비밀번호 재설정 요구';
                $actions[] = '2FA 강제 활성화';
                
                // 세션 종료
                DB::table('sessions')->truncate();
                
                // 비밀번호 재설정 플래그
                if (!empty($data['affected_users'])) {
                    \App\Models\User::whereIn('id', $data['affected_users'])
                        ->update(['password_force_change' => true]);
                }
                break;
                
            case 'attack':
                // 공격 대응
                $actions[] = 'IP 차단 목록 업데이트';
                $actions[] = '로그인 시도 제한 강화';
                $actions[] = 'CAPTCHA 활성화';
                
                // CAPTCHA 자동 활성화
                \Jiny\Auth\App\Services\AuthSettingsService::set('captcha', 'enable_captcha', true, 'boolean');
                break;
                
            case 'suspicious':
                // 의심스러운 활동 대응
                $actions[] = '활동 모니터링 강화';
                $actions[] = '로그 레벨 상승';
                break;
        }
        
        // 자동 대응 조치 기록
        foreach ($actions as $action) {
            DB::table('auth_incident_actions')->insert([
                'incident_id' => $incidentId,
                'action' => $action,
                'action_type' => 'automatic',
                'performed_by' => null, // 시스템 자동
                'created_at' => now()
            ]);
        }
    }
    
    /**
     * 관리자 알림
     */
    private function notifyAdmins($incidentId, $data)
    {
        $admins = \App\Models\User::where('is_admin', true)->get();
        
        foreach ($admins as $admin) {
            Mail::to($admin->email)->queue(new \App\Mail\SecurityIncidentAlert(
                $incidentId,
                $data['title'],
                $data['severity'],
                $data['description']
            ));
        }
    }
    
    /**
     * 영향받은 사용자 알림
     */
    private function notifyAffectedUsers($incidentId, $resolution)
    {
        $users = DB::table('auth_incident_affected_users')
            ->join('users', 'auth_incident_affected_users.user_id', '=', 'users.id')
            ->where('auth_incident_affected_users.incident_id', $incidentId)
            ->select('users.*')
            ->get();
        
        foreach ($users as $user) {
            Mail::to($user->email)->queue(new \App\Mail\SecurityIncidentResolved(
                $incidentId,
                $resolution
            ));
        }
    }
    
    /**
     * 사고 보고서 생성
     */
    private function generateIncidentReport($incidentId)
    {
        // 보고서 데이터 수집
        $incident = DB::table('auth_security_incidents')->find($incidentId);
        $actions = DB::table('auth_incident_actions')->where('incident_id', $incidentId)->get();
        $timeline = DB::table('auth_incident_timeline')->where('incident_id', $incidentId)->get();
        
        $report = [
            'incident' => $incident,
            'actions' => $actions,
            'timeline' => $timeline,
            'generated_at' => now()
        ];
        
        // PDF 생성 (별도 패키지 필요)
        // $pdf = PDF::loadView('reports.incident', $report);
        // $pdf->save(storage_path("reports/incident_{$incidentId}.pdf"));
        
        // JSON 형식으로 저장
        \Storage::disk('local')->put(
            "reports/incident_{$incidentId}.json",
            json_encode($report, JSON_PRETTY_PRINT)
        );
    }
}