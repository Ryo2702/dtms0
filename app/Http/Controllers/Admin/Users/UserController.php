<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserRequest;
use App\Models\Department;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    use AuthorizesRequests;

    protected $userIdGenerateService;

    public function __construct(UserService $userIdGenerateService)
    {
        $this->userIdGenerateService = $userIdGenerateService;
    }

    public function index()
    {
        $sort = request()->get('sort', 'name');
        $direction = request()->get('direction', 'asc');
        $type = request()->get('type', '');

        $allowedSorts = ['name', 'email', 'type', 'status', 'department_id'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'name';
        }

        $q = User::query();

        if ($type) {
            $q->where('type', $type);
        }

        $users = $q->where('name', 'not like', '%Admin%')
            ->orderBy($sort, $direction)
            ->paginate(8)
            ->appends(['sort' => $sort, 'direction' => $direction, 'type' => $type]);


        $availableDepartments = $this->getAvailableDepartments();

        return view('admin.users.index', compact('users', 'availableDepartments'));
    }

    /**
     * @param UserRequest|\Illuminate\Http\Request $request
     */
    public function store(UserRequest $request)
    {
        try {
            $validated = $request->validated();

            $department = $validated['department_id' ?? null]
                ? Department::findOrFail($validated['department_id'])
                : null;

            $type = 'Head';

            $userId = $this->userIdGenerateService->generate($department, $type);

            $data = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'department_id' => $validated['department_id'] ?? null,
                'type' => $type,
                'employee_id' => $userId,
                'status' => isset($validated['status']) ? 1 : 0,
            ];
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('users/avatars', 'public');
                $data['avatar'] = $avatarPath;
            }

            User::create($data);

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User created successfully with ID: ' . $userId);
        } catch (\Exception $e) {
            Log::error('User creation failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'User creation failed: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $user = User::with('department')->findOrFail($id);

        $availableDepartments = $this->getAvailableDepartments($id);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'employee_id' => $user->employee_id,
            'department_id' => $user->department_id,
            'department_name' => $user->department_name,
            'type' => $user->type,
            'status' => $user->status,
            'avatar' => $user->avatar,
            'avatar_url' => $user->avatar ? asset('storage/' . $user->avatar) : asset('images/default-avatar.png'),
            'is_online' => $user->isOnline(),
            'last_seen' => $user->last_seen,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'available_departments' => $availableDepartments
        ]);
    }

    public function getAvailableDepartments($excludeUserId = null)
    {
        $query  = Department::where('status', 1)
            ->where('name', 'not like', '%Admin%');

        if ($excludeUserId) {
            $user = User::find($excludeUserId);

            $query->where(function ($q) use ($user) {
                $q->whereDoesntHave('users')
                    ->orWhere(function ($subQ) use ($user) {
                        if ($user && $user->department_id) {
                            $subQ->where('id', $user->department_id)
                                ->whereHas('users', function ($userQ) use ($user) {
                                    $userQ->where('id', $user->id);
                                });
                        }
                    });
            });
        } else {
            $query->whereDoesntHave('users');
        }
        return $query->select('id', 'name', 'code')->get();
    }
    /**
     * @param UserRequest|\Illuminate\Http\Request $request
     * @param int $id
     */
    public function update(UserRequest $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validated = $request->validated();

            $department = isset($validated['department_id']) && $validated['department_id'] ? Department::findOrFail($validated['department_id'])
                : null;

            $regenerateId = ($user->department_id !== ($validated['department_id']));


            if ($regenerateId) {
                $userId = $this->userIdGenerateService->generate($department, $user->employee_id);
            } else {
                $userId = $user->employee_id;
            }


            $data = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'department_id' => $validated['department_id'] ?? null,
                'employee_id' => $userId,
                'status' => isset($validated['status']) ? 1 : 0
            ];

            if (!empty($validated['password'])) {
                $data['password'] = Hash::make($validated['password']);
            }

            if ($request->hasFile('avatar')) {
                if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
                    \Storage::disk('public')->delete($user->avatar);
                }

                $avatarPath = $request->file('avatar')->store('users/avatar', 'public');

                $data['avatar'] = $avatarPath;
            }

            $user->update($data);

            return redirect()->route('admin.users.index')->with('success', 'User Updated Successfully');
        } catch (\Exception $e) {

            Log::error("User update Failed: " . $e->getMessage());

            return back()->withInput()->with('error', 'User update failed' . $e->getMessage());
        }
    }
}
