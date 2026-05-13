<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Models\Announcement;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\AnnouncementAttachment;
use App\Notifications\AnnouncementPublishedNotification;

class AnnouncementApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $me = Auth::user();
        $canManage = $me->can('create announcement');

        $announcements = Announcement::with('attachments', 'departments', 'units', 'targetedUsers')
            ->get()
            ->filter(function ($announcement) use ($me, $canManage) {
                // Admins/creators see everything
                if ($canManage) return true;

                $hasUnitTarget  = $announcement->units->isNotEmpty();
                $hasDeptTarget  = $announcement->departments->isNotEmpty();
                $hasUserTarget  = $announcement->targetedUsers->isNotEmpty();

                // No targeting at all → visible to everyone
                if (!$hasUnitTarget && !$hasDeptTarget && !$hasUserTarget) return true;

                // Unit match
                if ($hasUnitTarget && $announcement->units->contains('id', $me->unit_id)) return true;

                // Department match
                if ($hasDeptTarget && $announcement->departments->contains('id', $me->department_id)) return true;

                // Explicit employee match
                if ($hasUserTarget && $announcement->targetedUsers->contains('id', $me->id)) return true;

                return false;
            })
            ->values();

        $announcements->transform(function ($announcement) {
            $announcement->author_name = User::find($announcement->author)?->firstname;
            return $announcement;
        });

        return response()->json($announcements);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Creating new announcement', ['data' => $request->all()]);

        $request->validate([
            'subject' => 'required|string',
            'description' => 'required|string',
            'attachments.*' => 'sometimes|file|max:10240', // 10MB max
        ]);

        $status = $request->input('action') === 'publish' ? 'published' : 'draft';
        $publishDate = now();

        $isActive = false;
        if ($status === 'published') {
            $expirationDate = $request->input('expiration_date');
            // Only active if published AND not expired
            $isActive = $expirationDate ? now()->lessThan($expirationDate) : true;
        }

        $isActive = $isActive ? 1 : 0;

        $announcement = Announcement::create([
            'subject' => $request->input('subject'),
            'description' => $request->input('description'),
            'author' => auth()->user()->id,
            'publish_date' => $publishDate,
            'expiration_date' => $request->input('expiration_date'),
            'is_active' => $isActive,
            'attachment' => $request->input('attachment'),
            'priority' => $request->input('priority'),
            'status' => $status,
        ]);
        Log::info('Announcement created', ['announcement_id' => $announcement->id]);
        // Handle file attachments if any
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('attachments', $filename, 'public');

                Log::info('File stored successfully', [
                    'filename' => $filename,
                    'path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);

                $announcement->attachments()->create([
                    'filename' => $filename,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        Log::info('Announcement created successfully', ['announcement_id' => $announcement->id]);


        // if ($status === 'published') {
        //     $unitId = Auth::user()->unit_id;

        //     // Check if departments were selected
        //     if ($request->has('department_ids') && !empty($request->input('department_ids'))) {
        //         $departmentIds = $request->input('department_ids');

        //         // Get users who belong to selected departments AND have the same unit_id
        //         $users = User::whereHas('department', function($query) use ($departmentIds) {
        //             $query->whereIn('department_id', $departmentIds);
        //         })
        //         ->where('is_enabled', true)
        //         ->where('unit_id', $unitId)
        //         ->get();

        //         Log::info('Sending notifications to users in selected departments and same unit', [
        //             'unit_id' => $unitId,
        //             'department_ids' => $departmentIds,
        //             'user_count' => $users->count()
        //         ]);

        //         Notification::send($users, new AnnouncementPublishedNotification($announcement));
        //     } else {
        //         // No departments specified, so only filter by unit_id
        //         $users = User::where('is_enabled', true)
        //                     ->where('unit_id', $unitId)
        //                     ->get();

        //         Notification::send($users, new AnnouncementPublishedNotification($announcement));
        //     }
        // }

        if ($status === 'published') {
            $sendEmail = filter_var($request->input('send_email'), FILTER_VALIDATE_BOOLEAN);
            $users = $this->resolveAudience($request, Auth::user()->unit_id);
            Notification::send($users, new AnnouncementPublishedNotification($announcement, $sendEmail));
        }

        if ($request->has('department_ids')) {
            $announcement->departments()->sync($request->input('department_ids'));
        }

        if ($request->has('unit_ids')) {
            $announcement->units()->sync($request->input('unit_ids'));
        }

        if ($request->has('user_ids')) {
            $announcement->targetedUsers()->sync($request->input('user_ids'));
        }

        return response()->json([
            'message' => 'Announcement created successfully',
            'data' => $announcement->load('attachments'),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $announcement = Announcement::findOrFail($id);

        $status = $request->input('action') === 'publish' ? 'published' : 'draft';


        $data = $request->except(['status', 'action', 'publish_date', 'author']);

        // Make sure is_active is saved as an integer (0 or 1)
        if (isset($data['is_active'])) {
            $data['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        }

        $announcement->update($data);

        $announcement->status = $status;

        if ($status === 'published') {
            $announcement->publish_date = now();
        }

        if ($status === 'draft') {
            $announcement->is_active = false;
        } else {
            $announcement->is_active = $announcement->expiration_date ?
                now()->lessThan($announcement->expiration_date) :
                true;
        }

        $announcement->save();

        // if ($status === 'published') {
        //     $unitId = Auth::user()->unit_id;

        //     // Check if departments were selected
        //     if ($request->has('department_ids') && !empty($request->input('department_ids'))) {
        //         $departmentIds = $request->input('department_ids');

        //         // Get users who belong to selected departments AND have the same unit_id
        //         $users = User::whereHas('department', function($query) use ($departmentIds) {
        //             $query->whereIn('department_id', $departmentIds);
        //         })
        //         ->where('is_enabled', true)
        //         ->where('unit_id', $unitId)
        //         ->get();

        //         Log::info('Sending notifications to users in selected departments and same unit', [
        //             'unit_id' => $unitId,
        //             'department_ids' => $departmentIds,
        //             'user_count' => $users->count()
        //         ]);

        //         Notification::send($users, new AnnouncementPublishedNotification($announcement));
        //     } else {
        //         // No departments specified, so only filter by unit_id
        //         $users = User::where('is_enabled', true)
        //                     ->where('unit_id', $unitId)
        //                     ->get();

        //         Notification::send($users, new AnnouncementPublishedNotification($announcement));
        //     }
        // }

        if ($status === 'published') {
            $sendEmail = filter_var($request->input('send_email'), FILTER_VALIDATE_BOOLEAN);
            $users = $this->resolveAudience($request, Auth::user()->unit_id);
            Notification::send($users, new AnnouncementPublishedNotification($announcement, $sendEmail));
        }

        // Handle attachments (if any)
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('attachments', $filename, 'public');

                $announcement->attachments()->create([
                    'filename' => $filename,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }
        if ($request->has('department_ids')) {
            $announcement->departments()->sync($request->input('department_ids'));
        }

        if ($request->has('unit_ids')) {
            $announcement->units()->sync($request->input('unit_ids'));
        }

        if ($request->has('user_ids')) {
            $announcement->targetedUsers()->sync($request->input('user_ids'));
        }

        return response()->json($announcement, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();

        Log::info('Announcement deleted successfully', ['announcement_id' => $id]);

        return response()->json([
            'message' => 'Announcement deleted successfully',
        ], 200);

    }

    private function resolveAudience(Request $request, int $authorUnitId): \Illuminate\Database\Eloquent\Collection
    {
        $unitIds   = $request->input('unit_ids', []);
        $deptIds   = $request->input('department_ids', []);
        $userIds   = $request->input('user_ids', []);

        // If specific employees are chosen, notify exactly those people
        if (!empty($userIds)) {
            return User::where('is_enabled', true)
                ->where('email', '!=', 'brian.omondi@boxleocourier.com')
                ->whereIn('id', $userIds)
                ->get();
        }

        $query = User::where('is_enabled', true)
            ->where('email', '!=', 'brian.omondi@boxleocourier.com');

        if (!empty($unitIds)) {
            $query->whereIn('unit_id', $unitIds);
        } else {
            $query->where('unit_id', $authorUnitId);
        }

        if (!empty($deptIds)) {
            $query->whereHas('department', fn($q) => $q->whereIn('department_id', $deptIds));
        }

        return $query->get();
    }

    public function downloadAttachment($id)
    {
        $attachment = AnnouncementAttachment::findOrFail($id);
        $path = storage_path('app/public/' . $attachment->file_path);

        if (!file_exists($path)) {
            abort(404, 'File not found');
        }

        return response()->download($path, $attachment->original_filename);
    }

}
