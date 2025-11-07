<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Services\User\UserService;
use GuzzleHttp\Psr7\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use PhpParser\Node\Stmt\Return_;

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

        $users = $q->orderBy($sort, $direction)
            ->paginate(8)
            ->appends(['sort' => $sort, 'direction' => $direction, 'type' => $type]);

        return view('admin.users.index', compact('users'));
    }
    public function store(Request $request)
    {
        try {
        } catch (\Exception $e) {
        }
    }
}
