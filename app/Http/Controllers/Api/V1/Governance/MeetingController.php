<?php

namespace App\Http\Controllers\Api\V1\Governance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Governance\CreateMeetingRequest;
use App\Http\Resources\Governance\MeetingResource;
use App\Models\MeetingAgenda;
use App\Models\MeetingAttendee;
use App\Models\MeetingSession;
use App\Models\OrgMandate;
use App\Services\Governance\MeetingLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Meeting API Controller (v1)
 *
 * REST API untuk Governance Meeting.
 * Semua aksi berdasarkan Mandate, BUKAN User.
 *
 * PRINSIP:
 * - Business logic TIDAK di controller
 * - Semua lifecycle transition via MeetingLifecycleService
 * - Authorization via MeetingPolicy
 * - Request validation via Form Request
 * - Response via API Resource
 */
class MeetingController extends Controller
{
    public function __construct(
        private MeetingLifecycleService $lifecycle
    ) {}

    /**
     * GET /api/v1/meetings
     * List meetings (scoped by territory via mandate)
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', MeetingSession::class);

        $query = MeetingSession::with([
            'chairpersonMandate.nodePosition.position',
            'chairpersonMandate.user.profil',
            'secretaryMandate.nodePosition.position',
            'secretaryMandate.user.profil',
            'node',
            'agendas',
            'attendees',
        ]);

        // Territory scoping via mandate
        $mandate = $request->get('_mandate');
        if ($mandate instanceof OrgMandate) {
            $territoryCode = $mandate->nodePosition?->node?->territory_code;
            if ($territoryCode) {
                $query->where(function ($q) use ($territoryCode) {
                    $q->where('territory_id', $territoryCode)
                      ->orWhere('territory_id', 'like', $territoryCode . '.%');
                });
            }
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('meeting_type')) {
            $query->where('meeting_type', $request->meeting_type);
        }
        if ($request->filled('node_id')) {
            $query->where('node_id', $request->node_id);
        }
        if ($request->filled('from_date')) {
            $query->where('scheduled_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('scheduled_at', '<=', $request->to_date);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('meeting_number', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->input('sort_by', 'scheduled_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowedSorts = ['scheduled_at', 'created_at', 'title', 'status'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $meetings = $query->paginate($request->input('per_page', 15))->withQueryString();

        return MeetingResource::collection($meetings);
    }

    /**
     * POST /api/v1/meetings
     * Create meeting
     */
    public function store(CreateMeetingRequest $request): JsonResponse
    {
        $this->authorize('create', MeetingSession::class);

        $mandate = $request->get('_mandate');
        if (!$mandate instanceof OrgMandate) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki mandat aktif.',
            ], 403);
        }

        $meeting = $this->lifecycle->createMeeting($request->validated(), $mandate);

        return (new MeetingResource($meeting->load([
            'chairpersonMandate.user.profil',
            'secretaryMandate.user.profil',
            'node',
            'attendees.mandate.user.profil',
        ])))->response()->setStatusCode(201);
    }

    /**
     * GET /api/v1/meetings/{meeting}
     * Show meeting detail
     */
    public function show(MeetingSession $meeting): MeetingResource
    {
        $this->authorize('view', $meeting);

        $meeting->load([
            'chairpersonMandate.nodePosition.position',
            'chairpersonMandate.user.profil',
            'chairpersonMandate.sk',
            'secretaryMandate.nodePosition.position',
            'secretaryMandate.user.profil',
            'approvedByMandate.user.profil',
            'node',
            'agendas.presenterMandate.user.profil',
            'agendas.votes',
            'attendees.mandate.nodePosition.position',
            'attendees.mandate.user.profil',
            'minutes.preparedByMandate.user.profil',
            'minutes.reviewedByMandate.user.profil',
            'minutes.approvedByMandate.user.profil',
        ]);

        return new MeetingResource($meeting);
    }

    /**
     * PUT /api/v1/meetings/{meeting}
     * Update meeting (only draft/scheduled)
     */
    public function update(Request $request, MeetingSession $meeting): MeetingResource
    {
        $this->authorize('update', $meeting);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'meeting_type' => ['sometimes', 'in:pleno,rapat_kerja,rapat_koordinasi,rapat_darurat,khusus'],
            'venue' => ['nullable', 'string', 'max:255'],
            'venue_type' => ['nullable', 'in:offline,online,hybrid'],
            'quorum_required' => ['nullable', 'integer', 'min:0'],
        ]);

        $mandate = $request->get('_mandate');
        if ($mandate instanceof OrgMandate) {
            $validated['updated_by_mandate_id'] = $mandate->id;
        }

        $meeting->update($validated);

        return new MeetingResource($meeting->fresh()->load('node'));
    }

    /**
     * POST /api/v1/meetings/{meeting}/schedule
     * Draft → Scheduled
     */
    public function schedule(Request $request, MeetingSession $meeting): MeetingResource
    {
        $this->authorize('schedule', $meeting);

        $validated = $request->validate([
            'scheduled_at' => ['required', 'date', 'after:now'],
            'venue' => ['nullable', 'string', 'max:255'],
            'venue_type' => ['nullable', 'in:offline,online,hybrid'],
        ]);

        $mandate = $request->get('_mandate');
        $meeting = $this->lifecycle->schedule($meeting, $validated, $mandate);

        return new MeetingResource($meeting);
    }

    /**
     * POST /api/v1/meetings/{meeting}/invite
     * Scheduled → Invitation
     */
    public function invite(Request $request, MeetingSession $meeting): MeetingResource
    {
        $this->authorize('invite', $meeting);

        $mandate = $request->get('_mandate');
        $meeting = $this->lifecycle->sendInvitations($meeting, $mandate);

        return new MeetingResource($meeting);
    }

    /**
     * POST /api/v1/meetings/{meeting}/open
     * Invitation → Running (with quorum check)
     */
    public function open(Request $request, MeetingSession $meeting): MeetingResource
    {
        $this->authorize('open', $meeting);

        $mandate = $request->get('_mandate');
        $meeting = $this->lifecycle->openMeeting($meeting, $mandate);

        return new MeetingResource($meeting);
    }

    /**
     * POST /api/v1/meetings/{meeting}/start-voting
     * Running → Voting
     */
    public function startVoting(Request $request, MeetingSession $meeting): MeetingResource
    {
        $this->authorize('open', $meeting);

        $mandate = $request->get('_mandate');
        $meeting = $this->lifecycle->startVoting($meeting, $mandate);

        return new MeetingResource($meeting);
    }

    /**
     * POST /api/v1/meetings/{meeting}/close-voting
     * Voting → Decision
     */
    public function closeVoting(Request $request, MeetingSession $meeting): MeetingResource
    {
        $this->authorize('close', $meeting);

        $mandate = $request->get('_mandate');
        $meeting = $this->lifecycle->closeVoting($meeting, $mandate);

        return new MeetingResource($meeting->load('agendas.votes'));
    }

    /**
     * POST /api/v1/meetings/{meeting}/generate-minutes
     * Decision → Minutes
     */
    public function generateMinutes(Request $request, MeetingSession $meeting): JsonResponse
    {
        $this->authorize('close', $meeting);

        $validated = $request->validate([
            'content' => ['required', 'string'],
            'summary' => ['nullable', 'string'],
        ]);

        $mandate = $request->get('_mandate');
        $minutes = $this->lifecycle->generateMinutes(
            $meeting, $mandate, $validated['content'], $validated['summary'] ?? null
        );

        return response()->json([
            'success' => true,
            'data' => [
                'meeting' => new MeetingResource($meeting->fresh()),
                'minutes_id' => $minutes->id,
            ],
        ]);
    }

    /**
     * POST /api/v1/meetings/{meeting}/close
     * Minutes → Closed
     */
    public function close(Request $request, MeetingSession $meeting): MeetingResource
    {
        $this->authorize('close', $meeting);

        $mandate = $request->get('_mandate');
        $meeting = $this->lifecycle->closeMeeting($meeting, $mandate);

        return new MeetingResource($meeting);
    }

    /**
     * POST /api/v1/meetings/{meeting}/cancel
     */
    public function cancel(Request $request, MeetingSession $meeting): MeetingResource
    {
        $this->authorize('cancel', $meeting);

        $mandate = $request->get('_mandate');
        $meeting = $this->lifecycle->cancelMeeting($meeting, $mandate);

        return new MeetingResource($meeting);
    }

    // ===================================================================
    // AGENDA ENDPOINTS
    // ===================================================================

    /**
     * GET /api/v1/meetings/{meeting}/agendas
     */
    public function agendaIndex(MeetingSession $meeting): JsonResponse
    {
        $this->authorize('view', $meeting);

        return response()->json([
            'success' => true,
            'data' => $meeting->agendas()->with('presenterMandate.user.profil', 'votes')->get(),
        ]);
    }

    /**
     * POST /api/v1/meetings/{meeting}/agendas
     */
    public function agendaStore(Request $request, MeetingSession $meeting): JsonResponse
    {
        $this->authorize('update', $meeting);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'presenter_mandate_id' => ['nullable', 'integer', 'exists:org_mandates,id'],
        ]);

        $mandate = $request->get('_mandate');
        $agenda = $this->lifecycle->addAgenda($meeting, $validated, $mandate);

        return response()->json([
            'success' => true,
            'data' => $agenda,
        ], 201);
    }

    /**
     * PUT /api/v1/meetings/{meeting}/agendas/{agenda}
     */
    public function agendaUpdate(Request $request, MeetingSession $meeting, MeetingAgenda $agenda): JsonResponse
    {
        $this->authorize('update', $meeting);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'presenter_mandate_id' => ['nullable', 'integer', 'exists:org_mandates,id'],
            'resolution' => ['nullable', 'string'],
        ]);

        $mandate = $request->get('_mandate');
        if ($mandate instanceof OrgMandate) {
            $validated['updated_by_mandate_id'] = $mandate->id;
        }

        $agenda->update($validated);

        return response()->json([
            'success' => true,
            'data' => $agenda->fresh(),
        ]);
    }

    /**
     * DELETE /api/v1/meetings/{meeting}/agendas/{agenda}
     */
    public function agendaDestroy(MeetingSession $meeting, MeetingAgenda $agenda): JsonResponse
    {
        $this->authorize('update', $meeting);

        if ($agenda->votes()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Agenda yang sudah memiliki vote tidak dapat dihapus.',
            ], 422);
        }

        $agenda->delete();

        return response()->json(['success' => true, 'message' => 'Agenda dihapus.']);
    }

    // ===================================================================
    // ATTENDEE ENDPOINTS
    // ===================================================================

    /**
     * POST /api/v1/meetings/{meeting}/attendees
     */
    public function attendeeStore(Request $request, MeetingSession $meeting): JsonResponse
    {
        $this->authorize('update', $meeting);

        $validated = $request->validate([
            'mandate_id' => ['required', 'integer', 'exists:org_mandates,id'],
            'role_in_meeting' => ['nullable', 'in:chairperson,secretary,presenter,voter,observer'],
            'has_voting_right' => ['nullable', 'boolean'],
        ]);

        $mandate = $request->get('_mandate');
        $attendee = $this->lifecycle->addAttendee(
            $meeting,
            $validated['mandate_id'],
            $validated['role_in_meeting'] ?? 'voter',
            $validated['has_voting_right'] ?? false,
            $mandate
        );

        return response()->json([
            'success' => true,
            'data' => $attendee->load('mandate.user.profil'),
        ], 201);
    }

    /**
     * POST /api/v1/meetings/{meeting}/attendees/{attendee}/check-in
     */
    public function attendeeCheckIn(Request $request, MeetingSession $meeting, MeetingAttendee $attendee): JsonResponse
    {
        $this->authorize('open', $meeting);

        $mandate = $request->get('_mandate');
        $attendee = $this->lifecycle->checkInAttendee($attendee, $mandate);

        return response()->json([
            'success' => true,
            'data' => $attendee,
        ]);
    }

    /**
     * DELETE /api/v1/meetings/{meeting}/attendees/{attendee}
     */
    public function attendeeDestroy(MeetingSession $meeting, MeetingAttendee $attendee): JsonResponse
    {
        $this->authorize('update', $meeting);

        if ($meeting->status !== MeetingSession::STATUS_DRAFT) {
            return response()->json([
                'success' => false,
                'message' => 'Peserta hanya bisa dihapus saat meeting berstatus draft.',
            ], 422);
        }

        $attendee->delete();

        return response()->json(['success' => true, 'message' => 'Peserta dihapus.']);
    }

    // ===================================================================
    // VOTE ENDPOINTS
    // ===================================================================

    /**
     * POST /api/v1/meetings/{meeting}/agendas/{agenda}/vote
     */
    public function vote(Request $request, MeetingSession $meeting, MeetingAgenda $agenda): JsonResponse
    {
        $this->authorize('vote', $meeting);

        $validated = $request->validate([
            'vote' => ['required', 'in:approve,reject,abstain'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $mandate = $request->get('_mandate');
        $voteRecord = $this->lifecycle->castVote(
            $meeting, $agenda, $mandate, $validated['vote'], $validated['reason'] ?? null
        );

        return response()->json([
            'success' => true,
            'data' => $voteRecord,
        ], 201);
    }

    /**
     * GET /api/v1/meetings/{meeting}/agendas/{agenda}/results
     */
    public function voteResults(MeetingSession $meeting, MeetingAgenda $agenda): JsonResponse
    {
        $this->authorize('view', $meeting);

        return response()->json([
            'success' => true,
            'data' => [
                'agenda_id' => $agenda->id,
                'agenda_title' => $agenda->title,
                'results' => $agenda->voteResults(),
                'votes' => $agenda->votes()->with('voterMandate.user.profil')->get()->map(fn($v) => [
                    'vote' => $v->vote,
                    'reason' => $v->reason,
                    'voted_at' => $v->voted_at?->toIso8601String(),
                    'voter_position_snapshot' => $v->voter_position_snapshot,
                    'voter_sk_snapshot' => $v->voter_sk_snapshot,
                ]),
            ],
        ]);
    }
}
